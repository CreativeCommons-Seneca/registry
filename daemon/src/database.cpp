/*
 * database.cpp
 *
 *  Created on: Jul 7, 2015
 *      Author: hosung
 */

#include <iostream>

#include <cppconn/driver.h>
#include <cppconn/exception.h>
#include <cppconn/resultset.h>
#include <cppconn/statement.h>

#include "database.h"
#include "config.h"
#include "error.h"

#include <string>
using namespace std;

/**
 * Manages DB connection information from given config ini file
 */
typedef struct _dbcon{
	sql::Driver *driver;
	sql::Connection *con;

	std::string lastErrorString;

	char hostName[128];
	char userName[64];
	char password[64];
	char schemaName[64];
	char tableName[64];
	char hashKey[64];
	char hashValue[64];

	_dbcon(): driver(NULL), con(NULL) {
		*hostName = '\0';
		*userName = '\0';
		*password = '\0';
		*schemaName = '\0';
		*tableName = '\0';
		*hashKey = '\0';
		*hashValue = '\0';
		lastErrorString = "";

		//Open Config file from CONFIG_FILENAME for db information and store them into object members.
		hConfig cf = openConfig(CONFIG_FILENAME);
		if (!cf){
			std::cerr << "# ERR: Opening regdaemon.ini failed" << std::endl;
			return;
		}

		getConfigValue(cf, CONFIG_SEC_DB, CONFIG_DB_HOST, hostName);
		getConfigValue(cf, CONFIG_SEC_DB, CONFIG_DB_USER, userName);
		getConfigValue(cf, CONFIG_SEC_DB, CONFIG_DB_PW, password);
		getConfigValue(cf, CONFIG_SEC_DB, CONFIG_DB_SCHEMA, schemaName);
		getConfigValue(cf, CONFIG_SEC_DB, CONFIG_DB_TABLE, tableName);
		getConfigValue(cf, CONFIG_SEC_DB, CONFIG_DB_HASHKEY, hashKey);
		getConfigValue(cf, CONFIG_SEC_DB, CONFIG_DB_HASHVALUE, hashValue);

		closeConfig(cf);
	}

	bool isConnected(){
		return driver && con && !con->isClosed();
	}
} dbcon;

/**
 * works as simple singleton for db connection
 */
static dbcon *db = NULL;
static const string NullString("\0");

const std::string &getLastDBErrorString(){
	if (db)
		return db->lastErrorString;
	else
		return NullString;
}

/**
 * Close database connection object
 */
void closeConnection(){
	if (db){
		if (db->con){
			//db->con->close();
			delete db->con;
			db->con = NULL;
		}
		if (db->driver){
			db->driver = NULL;
		}

		delete db;
		db = NULL;
	}
}

/**
 * Get database connection object.
 * Return RCODE - error code in error.h
 */
static RCODE getConnection(dbcon **pCon){

	RCODE rCode = ERR_DAEMON_UNDEFINED;

	if (db == NULL){
		db = new dbcon();
	}

	if (db != NULL){
		try{
			if (db->isConnected()){
				*pCon = db;
				return RCODE_SUCCESS;
			}

			sql::Driver *driver = get_driver_instance();
			if (!driver){
				db->lastErrorString = getErrorString(ERR_DAEMON_MYSQL_DRIVER);
				return ERR_DAEMON_MYSQL_DRIVER;
			}

			db->driver = driver;

			if (!*db->hostName || !*db->userName || !*db->password){
				db->lastErrorString = getErrorString(ERR_DAEMON_INI_INVALID);
				return ERR_DAEMON_INI_INVALID;
			}

			sql::Connection *con = driver->connect(db->hostName, db->userName, db->password);
			if (con && !con->isClosed()){
				db->con = con;
				*pCon = db;
				rCode = RCODE_SUCCESS;
			}
			else{
				db->lastErrorString = getErrorString(ERR_DAEMON_MYSQL_CONNECT);
				return ERR_DAEMON_MYSQL_CONNECT;
			}

		}catch(sql::SQLException &e) {
			rCode = e.getErrorCode();
			db->lastErrorString = e.what();

			std::cerr << "# ERR: SQLException in " << __FILE__;
			std::cerr << "(" << __FUNCTION__ << ") on line " << __LINE__ << std::endl;
			std::cerr << "# ERR: " << e.what();
			std::cerr << " (MySQL error code: " << e.getErrorCode();
			std::cerr << ", SQLState: " << e.getSQLState() << " )" << std::endl;

			closeConnection();
		}
	}

	return rCode;
}

/**
 * From database load all id,hash and add them to the memory using ADDFUNCTION function
 * Adds only values that are marked not deleted (deleted='N')
 * OUT - added : added item count
 * Return RCODE - error code
 */
RCODE addFromDatabase(ADDFUNCTION add, int *added){

	RCODE rCode = ERR_DAEMON_UNDEFINED;
	int addcount = 0;

	if (add == NULL){
		return ERR_DAEMON_INVALID_INPUT;
	}

	sql::Statement *stmt = NULL;
	sql::ResultSet *res = NULL;

	try{
		dbcon *con = NULL;
		if (RCODE_FAILED(rCode = getConnection(&con)))
			return rCode;

		/* Connect to the MySQL test database */
		con->con->setSchema(db->schemaName);
		stmt = con->con->createStatement();

		//buffer size enough.
		char sqlText[256]; sqlText[0] = '\0';
		sprintf(sqlText, "select %s, %s from %s where deleted=\'N\'", db->hashKey, db->hashValue, db->tableName);

		res = stmt->executeQuery(sqlText);

		while (res->next()){
			string keyString = res->getString(db->hashKey);
			string hashString = res->getString(db->hashValue);
			//uint64_t
			if (keyString.length() > 0 && hashString.length() > 0){
				uint64_t llkey = strtoull(keyString.c_str(), NULL, 0);
				uint64_t llvalue = strtoull(hashString.c_str(), NULL, 0);

				add(llkey, llvalue);
				addcount++;
			}
		}

		if (res) {	delete res;		res = NULL;	}
		if (stmt){	delete stmt;	stmt = NULL;}

		*added = addcount;

	}catch(sql::SQLException &e) {
		rCode = e.getErrorCode();
		db->lastErrorString = e.what();

		std::cerr << "# ERR: SQLException in " << __FILE__;
		std::cerr << "(" << __FUNCTION__ << ") on line " << __LINE__ << std::endl;
		std::cerr << "# ERR: " << e.what();
		std::cerr << " (MySQL error code: " << e.getErrorCode();
		std::cerr << ", SQLState: " << e.getSQLState() << " )" << std::endl;

		if (res) {	delete res;		res = NULL;	}
		if (stmt){	delete stmt;	stmt = NULL;}
	}

	return rCode;
}

/**
 * Mark the record's 'deleted' field to 'y'
 * Return RCODE - error code
 */
RCODE markDeleted(TCmdMap map){
	sql::Statement *stmt = NULL;
	sql::ResultSet *res = NULL;

	RCODE rCode = ERR_DAEMON_UNDEFINED;

	if (map.empty())
		return ERR_DAEMON_INVALID_INPUT;

	try{
		dbcon *con = NULL;
		if (RCODE_FAILED(rCode = getConnection(&con)))
			return rCode;

		/* Connect to the MySQL test database */
		con->con->setSchema(db->schemaName);
		stmt = con->con->createStatement();

		std::cout << map["id"] << std::endl;
		std::cout << map["imagename"] << std::endl;

		std::string sqlText = "UPDATE " + std::string(db->tableName) + " SET deleted='y' where id=" + map["id"];

		//updated count return
		if (stmt->executeUpdate(sqlText) > 0){
			rCode = RCODE_SUCCESS;
		}

		if (res) {	delete res;		res = NULL;	}
		if (stmt){	delete stmt;	stmt = NULL;}

		rCode = RCODE_SUCCESS; //when the record is already marked deleted update count is 0.

	}catch(sql::SQLException &e) {
		rCode = e.getErrorCode();
		db->lastErrorString = e.what();

		std::cerr << "# ERR: SQLException in " << __FILE__;
		std::cerr << "(" << __FUNCTION__ << ") on line " << __LINE__ << std::endl;
		std::cerr << "# ERR: " << e.what();
		std::cerr << " (MySQL error code: " << e.getErrorCode();
		std::cerr << ", SQLState: " << e.getSQLState() << " )" << std::endl;

		if (res) {	delete res;		res = NULL;	}
		if (stmt){	delete stmt;	stmt = NULL;}
	}

	return rCode;
}

/**
 * Insert new content to database.
 * Return RCODE - error code
 */
RCODE addNewContent(TCmdMap map, uint64_t *cid){
	RCODE rCode = ERR_DAEMON_UNDEFINED;

	sql::Statement *stmt = NULL;
	sql::ResultSet *res = NULL;

	if (map.empty())
		return 0;

	try{
		dbcon *con = NULL;
		if (RCODE_FAILED(rCode = getConnection(&con)))
			return rCode;

		/* Connect to the MySQL test database */
		con->con->setSchema(db->schemaName);
		stmt = con->con->createStatement();

		std::string sqlText = "INSERT INTO IMG(";
		std::string sqlText2 = "VALUES(";

		for(TCmdMap::iterator p = map.begin(); p!=map.end(); ++p)
		{
			sqlText += p->first + ", ";
			sqlText2 += "\"" + p->second + "\", ";
		}

		//delete last ", "
		sqlText.pop_back();		sqlText.pop_back();			//INSERT INTO IMG(a, b, c,
		sqlText2.pop_back();	sqlText2.pop_back();		//VALUES(a1, b2, c1,

		sqlText += ") " + sqlText2 + ")";					//INSERT INTO IMG(a, b, c) VALUES(a1, b2, c1)

		std::cout << sqlText << std::endl;

		//updated count return
		if (stmt->executeUpdate(sqlText) == 1){
			res = stmt->executeQuery("SELECT LAST_INSERT_ID();");
			if(res->next()) {
				*cid = res->getUInt64(1);
				rCode = RCODE_SUCCESS;
			}
		}

		std::cout << "updated index : " << cid << std::endl;

		if (res) {	delete res;		res = NULL;	}
		if (stmt){	delete stmt;	stmt = NULL;}

	}catch(sql::SQLException &e) {
		rCode = e.getErrorCode();
		db->lastErrorString = e.what();

		std::cerr << "# ERR: SQLException in " << __FILE__;
		std::cerr << "(" << __FUNCTION__ << ") on line " << __LINE__ << std::endl;
		std::cerr << "# ERR: " << e.what();
		std::cerr << " (MySQL error code: " << e.getErrorCode();
		std::cerr << ", SQLState: " << e.getSQLState() << " )" << std::endl;

		if (res) {	delete res;		res = NULL;	}
		if (stmt){	delete stmt;	stmt = NULL;}
	}

	return rCode;
}
