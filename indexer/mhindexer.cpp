//============================================================================
// Name        : mhindexer.cpp
// Author      : Hosung Hwang ho-sung.hwang@senecacollege.ca
// Version     : 0.1
// Copyright   : Your copyright notice
// Description : hash indexer from mysql hash database
//============================================================================

#include <stdio.h>
#include <stdlib.h>

#include <iostream>

#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>

#include "pHash.h"

extern "C"{
	#include "mvptree.h"
}

using namespace std;


#define HASHLEN 72

#define MVP_BRANCHFACTOR 2
#define MVP_PATHLENGTH   5
#define MVP_LEAFCAP     25


//static unsigned int nbcalcs = 0;
//static int searching = 0;

float distance(MVPDP *pointA, MVPDP *pointB){
	if (!pointA || !pointB)
		return -1.0f;

    double dist = ph_hammingdistance2((uint8_t*)pointA->data, pointA->datalen, (uint8_t*)pointB->data, pointB->datalen);
/*
    if (searching == 1){
    	printf("COMPARE dist(%lf) %s\n", dist, pointB->id);
    }

    nbcalcs++;
*/
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


/**
 * connect to db. get all id and mhash
 * make index from it and save it to file
 *
 * mhindexer hostName userName password schema table key value filename
 *
 * hostName : mysql hostname
 * userName : mysql username
 * password : mysql password
 * schema : db name
 * table : table name
 * key : image id field name in the table
 * value : hash field name in the table
 * filename : mvp tree file name
 *
 */
int main(int argc, char **argv) {
	if (argc < 9){
		cout << "Usage :" << endl
				<< "\t mhindexer hostName userName password schema table key value treeFilename" << endl
				<< "\t hostName : mysql hostname" << endl
				<< "\t userName : mysql username" << endl
				<< "\t password : mysql password" << endl
				<< "\t schema : db name" << endl
				<< "\t table : table name" << endl
				<< "\t key : image id field name in the table" << endl
				<< "\t value : hash field name in the table" << endl
				<< "\t treeFilename : mvp tree file name" << endl
				<< "Output :" << endl
				<< "\t treeFilename,datapointCount,elapsedSeconds" << endl;
		return 1;
	}

	//cout << "Start MH Hash Indexer from database" << endl;

	const char *hostName = argv[1];
	const char *userName = argv[2];
	const char *password = argv[3];
	const char *schemaName = argv[4];
	const char *tableName = argv[5];
	const char *key = argv[6];
	const char *hash = argv[7];
	const char *filename = argv[8];

	CmpFunc distance_func = distance;

	sql::Driver *driver = NULL;
	sql::Connection *con = NULL;
	sql::Statement *stmt = NULL;
	sql::ResultSet *res = NULL;
	MVPTree *tree = NULL;

	try{
		clock_t begin = clock();

		/* Create a connection */
		driver = get_driver_instance();
		con = driver->connect(hostName, userName, password);
		/* Connect to the MySQL test database */
		con->setSchema(schemaName);
		stmt = con->createStatement();

		//string sqlText = "select " + key + ", " + hash + " from " + tableName;
		//string sqlText =
		char sqlText[256]; sqlText[0] = '\0';
		sprintf(sqlText, "select %s, %s from %s", key, hash, tableName);

		res = stmt->executeQuery(sqlText); // replace with your statement

		tree = mvptree_alloc(NULL, distance_func, MVP_BRANCHFACTOR, MVP_PATHLENGTH, MVP_LEAFCAP);
		if (tree == NULL){
			throw string("MVP Error : tree allocation error");
		}

		int hashcount = 0;

		while (res->next()) {
			//cout << res->getString(key) << " : " << res->getString(hash) << endl;

			string keyString = res->getString(key);
			string hashString = res->getString(hash);

			if (hashString.length() != (HASHLEN * 2))
				continue;

			uint8_t hashBin[HASHLEN+1] = {0x00};

    	    for (int i = 0; i < HASHLEN; i++) {
    	        sscanf(hashString.substr(2*i, 2).c_str(), "%02x", (unsigned int*)&hashBin[i]);
    	    }

			MVPDP *pdp = makePoint(keyString.c_str() ,hashBin , HASHLEN);

    	    if (pdp != NULL){
    	    	MVPError err = mvptree_add(tree, &pdp, 1);
    	    	if (err != MVP_SUCCESS){
    	    		throw string("MVP Error : ") + mvp_errstr(err);
    	    	}
    	    }

    	    hashcount++;
		}

		//write file
		{
			MVPError err = mvptree_write(tree, filename, 00755);
	    	if (err != MVP_SUCCESS){
	    		throw string("MVP Error : ") + mvp_errstr(err);
	    	}
		}

		clock_t end = clock();
		double elapsed_secs = double(end - begin) / CLOCKS_PER_SEC;

		cout << filename << "," << hashcount << "," << std::fixed << elapsed_secs << endl;

	}catch(sql::SQLException &e) {
		cout << "# ERR: SQLException in " << __FILE__;
		cout << "(" << __FUNCTION__ << ") on line " << __LINE__ << endl;
		cout << "# ERR: " << e.what();
		cout << " (MySQL error code: " << e.getErrorCode();
		cout << ", SQLState: " << e.getSQLState() << " )" << endl;
	}catch(const string& e){
		cout << e << endl;
	}

	if (res) {	delete res;		res = NULL;	}
	if (stmt){	delete stmt;	stmt = NULL;}
	if (con) {	delete con;		con = NULL; }

	if (tree){
		mvptree_clear(tree, free);
		free(tree);		tree = NULL;
	}

	return 0;
}
