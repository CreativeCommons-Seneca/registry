//============================================================================
// Name        : mhsearcher.cpp
// Author      : Hosung Hwang ho-sung.hwang@senecacollege.ca
// Version     : 0.1
// Copyright   : Your copyright notice
// Description : Hello World in C++, Ansi-style
//============================================================================

#include <limits>
#include <iostream>
#include "pHash.h"

extern "C"{
	#include "mvptree.h"
}

using namespace std;


#define HASHLEN 72

#define MVP_BRANCHFACTOR 2
#define MVP_PATHLENGTH   5
#define MVP_LEAFCAP     25


static unsigned int nbcalcs = 0;

float distance(MVPDP *pointA, MVPDP *pointB){
	if (!pointA || !pointB)
		return -1.0f;

    double dist = ph_hammingdistance2((uint8_t*)pointA->data, pointA->datalen, (uint8_t*)pointB->data, pointB->datalen);

    //printf("COMPARE dist(%lf) %s\n", dist, pointB->id);

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


/**
 * mhsearcher treeFilename imageFilename radious
 * output : 0-success, 1-failed
 *  success : count,id,id,id,...
 *  	eg : 0,2,101,9801
 *  failed : error string
 *      eg : 1,MVP Error
 */
int main(int argc, char **argv) {
	if (argc < 4){
		cout << "Usage :" << endl
				<< "\tmhsearcher treeFilename imageFilename radius" << endl
				<< "\teg : mhsearcher tree.mh ./test.jpg 0.0005" << endl
				<< "output : 0-success, 1-failed" << endl
				<< "\tsuccess : 0,count,id,id,id,..." << endl
				<< "\t  eg : 0,2,101,9801 " << endl
				<< "\tfailed : 1,error string" << endl
				<< "\t  eg : 1,MVP Error" << endl;
		return 1;
	}

	MVPTree *tree = NULL;
	CmpFunc distance_func = distance;
	uint8_t* hash = NULL;

	try{

		//read tree file
		{
			const char *treeFilename = argv[1];
			MVPError err = MVP_UNRECOGNIZED;

			tree = mvptree_read(treeFilename, distance_func, MVP_BRANCHFACTOR,MVP_PATHLENGTH,MVP_LEAFCAP,&err);

			if (err != MVP_SUCCESS){
				throw string("MVP Error : ") + mvp_errstr(err);
			}
		}

		//hash from image
		{
			const char *imageFilename = argv[2];
			int alpha = 2;
			int level = 1;
			int hashlen = 0;

			hash = ph_mh_imagehash(imageFilename, hashlen, alpha, level);

			if (hash == NULL){
				throw string("pHash Error : ph_mh_imagehash returned NULL");
			}
		}

		//search hash with radious from tree
		{
			clock_t begin = clock();

			//allocate datapoint from hash for searching
			MVPDP *dp = makePoint("search", hash, HASHLEN);
			if (dp == NULL){
				throw string("MVP Error : allocation data point failed");
			}

			float radious = atof(argv[3]);
			int maximum = 1000;
			unsigned int nbresults = 0;
			MVPError err = MVP_UNRECOGNIZED;

			MVPDP **results = mvptree_retrieve(tree, dp, maximum, radious, &nbresults, &err);
			if (!results){
				throw string("MVPTree retrieve error");
			}
			if (err != MVP_SUCCESS){
				free(results);
				throw string("MVP Error : No results found : ") + mvp_errstr(err);
			}

			clock_t end = clock();
			double elapsed_secs = double(end - begin) / CLOCKS_PER_SEC;

			//successed
			cout << "0," << nbresults;
			for (unsigned int i = 0; i < nbresults; i++){
				cout << "," << results[i]->id;
			}

			//print at the end : radious, calculation, elapsed seconds
			cout << "," << radious << "," << nbcalcs << "," << std::fixed << elapsed_secs << endl;

			free(results);
		}

	}catch(const string &e){
		cout << "1," << e << endl;
	}

	if (tree){
		mvptree_clear(tree, free);
		free(tree);
		tree = NULL;
	}

	if (hash){
		free(hash);
		hash = NULL;
	}

	return 0;
}
