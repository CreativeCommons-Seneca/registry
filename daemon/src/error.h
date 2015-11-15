/*
 * error.h
 *
 *  Created on: Jul 20, 2015
 *      Author: hosung
 */

#ifndef ERROR_H_
#define ERROR_H_

/*
 * Error Code Base
 * mysql error code
 */
#define ERR_BASE_DB1 					1000	// mysql server error code : https://dev.mysql.com/doc/refman/5.5/en/error-messages-server.html
#define ERR_BASE_DB2            		2000	// mysql client error code : https://dev.mysql.com/doc/refman/5.5/en/error-messages-client.html
#define ERR_BASE_API					3000	// CC php API error code
#define ERR_BASE_DAEMON					4000	// CC Daemon error code

/*
 * Error Code Setting
 */
#define ERR_DAEMON_SETCODE(code)		(ERR_BASE_DAEMON + (code))

typedef int RCODE;

#define RCODE_SUCCESS					0
#define RCODE_FAILED(code)				(((code) != RCODE_SUCCESS) ? true : false)
#define RCODE_SUCCESSED(code)			(((code) == RCODE_SUCCESS) ? true : false)

/*
 * Daemon Error Code
 */
#define ERR_DAEMON_UNDEFINED			    ERR_DAEMON_SETCODE(0)
#define ERR_DAEMON_UNDEFINED_TEXT		    "Undefined error"
#define ERR_DAEMON_INI_LOADING			    ERR_DAEMON_SETCODE(1)		//4001 - ini file not found
#define ERR_DAEMON_INI_LOADING_TEXT		    "ini file opening error"
#define ERR_DAEMON_INI_INVALID			    ERR_DAEMON_SETCODE(2)		//4002 - invalid ini file
#define ERR_DAEMON_INI_INVALID_TEXT		    "Invalid ini file"
#define ERR_DAEMON_INVALID_INPUT		    ERR_DAEMON_SETCODE(3)
#define ERR_DAEMON_INVALID_INPUT_TEXT   	"Invalid input value"
#define ERR_DAEMON_MYSQL_DRIVER			    ERR_DAEMON_SETCODE(4)
#define ERR_DAEMON_MYSQL_DRIVER_TEXT	    "Get Mysql Driver Failed"
#define ERR_DAEMON_MYSQL_CONNECT		    ERR_DAEMON_SETCODE(4)
#define ERR_DAEMON_MYSQL_CONNECT_TEXT	    "Mysql Connection Failed"
#define ERR_DAEMON_CREATE_SOCKET		    ERR_DAEMON_SETCODE(5)
#define ERR_DAEMON_CREATE_SOCKET_TEXT   	"Couldn't create server socket"
#define ERR_DAEMON_BIND_SOCKET		    	ERR_DAEMON_SETCODE(6)
#define ERR_DAEMON_BIND_SOCKET_TEXT	    	"Couldn't bind socket"
#define ERR_DAEMON_SOCKET_PERMISSION    	ERR_DAEMON_SETCODE(7)
#define ERR_DAEMON_SOCKET_PERMISSION_TEXT    "Couldn't change domain socket permission"
#define ERR_DAEMON_SOCKET_LISTEN		    ERR_DAEMON_SETCODE(8)
#define ERR_DAEMON_SOCKET_LISTEN_TEXT   	"Couldn't listen socket"
#define ERR_DAEMON_READING_COMMAND	    	ERR_DAEMON_SETCODE(9)
#define ERR_DAEMON_READING_COMMAND_TEXT     "Error reading command from client"
#define ERR_DAEMON_INVALID_REQUEST		    ERR_DAEMON_SETCODE(10)
#define ERR_DAEMON_INVALID_REQUEST_TEXT     "Invalid request format"
#define ERR_DAEMON_CANNOT_DELETE		    ERR_DAEMON_SETCODE(11)
#define ERR_DAEMON_CANNOT_DELETE_TEXT   	"Cannot Delete from DB"
#define ERR_DAEMON_CREATE_SOCKET_DIR        ERR_DAEMON_SETCODE(12)
#define ERR_DAEMON_CREATE_SOCKET_DIR_TEXT   "Cannot create directory for server socket"

#define ERR_END							10000
#define ERR_END_TEXT					"Unable error number"


const char* getErrorString(int errorId);
void printError(int errorId);

#endif /* ERROR_H_ */
