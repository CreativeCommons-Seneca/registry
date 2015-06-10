//============================================================================
// Name        : MHHashTree.cpp
// Author      : Hosung Hwang
// Version     :
// Copyright   : Your copyright notice
// Description : Hello World in C++, Ansi-style
//============================================================================

#include <iostream>
#include <stdio.h>
#include <dirent.h>
#include <errno.h>
#include <vector>
#include <algorithm>
#include <assert.h>
#include "pHash.h"
#include "math.h"

extern "C"{
#include "mvptree.h"
}
//for functions in mvptree.c

#define HASHLEN 72

using namespace std;

static unsigned int nbcalcs = 0;
static int searching = 0;

float distance(MVPDP *pointA, MVPDP *pointB){
	if (!pointA || !pointB)
		return -1.0f;

    double dist = ph_hammingdistance2((uint8_t*)pointA->data, pointA->datalen, (uint8_t*)pointB->data, pointB->datalen);

    if (searching == 1){
    	printf("COMPARE dist(%lf) %s\n", dist, pointB->id);
    }

    nbcalcs++;

    return (float)dist;
}

/**
 * make MVPDP from hash value
 */
MVPDP *makePoint(const char *name, const uint8_t *hash, const unsigned int dp_length){
	MVPDP *newpnt = dp_alloc(MVP_BYTEARRAY);
	if (newpnt == NULL)
		return NULL;

	newpnt->data = malloc(dp_length*sizeof(uint8_t));
	if (newpnt->data == NULL) {
		free (newpnt);
		return NULL;
	}

	newpnt->id = strdup(name);
	if (newpnt->id == NULL){
		free(newpnt);
		free(newpnt->data);
		return NULL;
	}

	memcpy(newpnt->data, hash, dp_length);
	newpnt->datalen = dp_length;

	return newpnt;
}

void printHash(MVPDP *dp){
	unsigned int len;
	for (len = 0; len < dp->datalen; len++)
		fprintf(stdout,"%02x", ((uint8_t *)dp->data)[len]);

	fprintf(stdout,"\n");
}

#define MVP_BRANCHFACTOR 2
#define MVP_PATHLENGTH   5
#define MVP_LEAFCAP     25


int main(int argc, char **argv){
	if (argc != 4 && argc != 7){
		std::cout << "Usage :" << endl
				<< "\tMHHashTree drectory filename radius" << endl
				<< "\t  directory : a directory that contains .hashmh files that will be in the MVP-tree" << endl
				<< "\t  filename : a .hashmh file to search from the tree" << endl
				<< "\t  radius : radius to search eg. 0.0001, 0.1, 1.0, 4.0" << endl
				<< "\tMHHashTree drectory filename radius BranchFactor PathLength LeafCap" << endl
				<< "\t  BranchFactor : tree branch factor - default 2" << endl
				<< "\t  PathLength : path length to use for each data point - default 5" << endl
				<< "\t  LeafCap : leaf capacity of each leaf node - maximum number of datapoints - default 25" << endl;

		return 1;
	}

	CmpFunc distance_func = distance;

	const char *dir_name = argv[1];
	const char *target_file = argv[2];
	const float radious = atof(argv[3]);

	int branchFactor = MVP_BRANCHFACTOR;
	int pathLength = MVP_PATHLENGTH;
	int leafCap = MVP_LEAFCAP;

	if (argc == 7){
		branchFactor = atoi(argv[4]);
		pathLength = atoi(argv[5]);
		leafCap = atoi(argv[6]);
	}

	MVPTree *tree = mvptree_alloc(NULL, distance_func, branchFactor, pathLength, leafCap);
	assert(tree);

	//read hash from files in the folder
    struct dirent *dir_entry;
    DIR *dir = opendir(dir_name);

    if (!dir){
		printf("unable to open directory\n");
		exit(1);
	}
    int cnt = 0;
	char path[1024];
	path[0] = '\0';

	MVPDP *test = NULL;

	FILE *fp;
	char line[256];
	while((dir_entry = readdir(dir)) != 0){
		path[0] = 0x00;
        if (strcmp(dir_entry->d_name,".") && strcmp(dir_entry->d_name,"..")){
            strcat(path, dir_name);
            strcat(path, "/");
            strcat(path, dir_entry->d_name);
            if (strcmp(path + (strlen(path) - 7), ".hashmh") == 0){
            	fp = fopen(path, "r");
            	if (fp != NULL){
            		fgets(line, sizeof(line), fp);
            		line[256 - 1] = 0x00;

            		uint8_t hash[HASHLEN+1] = {0x00};

            	    for (int i = 0; i < HASHLEN; i++) {
            	        sscanf(line + 2*i, "%02x", (unsigned int*)&hash[i]);
            	    }

            	    MVPDP *pdp = makePoint(dir_entry->d_name, hash, HASHLEN);

            	    if (pdp != NULL){
            	    	MVPError err = mvptree_add(tree, &pdp, 1);
            	    	if (err != MVP_SUCCESS){
            	    		fprintf(stdout,"Unable to add cluster to tree - %s\n", mvp_errstr(err));
            	    	}
            	    }

            	    if (strcmp(dir_entry->d_name, target_file) == 0){
            	    	test = makePoint(dir_entry->d_name, hash, HASHLEN);
            	    	printf("(*) %s   : %s",  dir_entry->d_name, line);
            	    }
            		//printf("%s : %s", dir_entry->d_name, line);

            		fclose(fp);
            	}
                cnt++;
            }
        }
	}

	{
		const char *testfile = "testfile.mvp";
		fprintf(stdout,"Write tree to file - %s.\n", testfile);
		MVPError err = mvptree_write(tree, testfile, 00755);

		fprintf(stdout,"Read tree from file - %s\n", testfile);
		tree = mvptree_read(testfile, distance_func, branchFactor, pathLength, leafCap,&err);
		assert(tree);
	}

	//mvptree_print(stdout, tree);

	{
		nbcalcs = 0;
		MVPError err;
		unsigned int nbresults = 0;
		searching = 1;
		MVPDP **results = mvptree_retrieve(tree, test, cnt, radious, &nbresults, &err);
		if (!results || err != MVP_SUCCESS){
			fprintf(stdout,"No results found - %s\n", mvp_errstr(err));
		}

		fprintf(stdout,"------------------Results %d (%d calcs)---------\n",nbresults,nbcalcs);
		unsigned int i;
		for (i = 0;i < nbresults;i++){
			fprintf(stdout,"(%d) %s   : ", i, results[i]->id);

			printHash(results[i]);
		}
		fprintf(stdout,"------------------------------------------------\n\n");
		free(results);
	}

	mvptree_clear(tree, free);
	free(tree);

	return 0;
}
