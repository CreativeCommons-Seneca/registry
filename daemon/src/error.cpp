/*
 * error.cpp
 *
 *  Created on: Jul 20, 2015
 *      Author: hosung
 */

#include "error.h"
#include <stdio.h>

//Struct for error code and error message
struct MessageStruct
{
    int number;
    const char* text;
};

//define Message Struct Array
const struct MessageStruct messageStructs[] =
{
    { ERR_DAEMON_UNDEFINED, ERR_DAEMON_UNDEFINED_TEXT },
    { ERR_DAEMON_INI_LOADING, ERR_DAEMON_INI_LOADING_TEXT },
    { ERR_DAEMON_INI_INVALID, ERR_DAEMON_INI_INVALID_TEXT },
    { ERR_DAEMON_INVALID_INPUT, ERR_DAEMON_INVALID_INPUT_TEXT },
    { ERR_DAEMON_MYSQL_DRIVER, ERR_DAEMON_MYSQL_DRIVER_TEXT },
    { ERR_DAEMON_CREATE_SOCKET, ERR_DAEMON_CREATE_SOCKET_TEXT },
    { ERR_DAEMON_BIND_SOCKET, ERR_DAEMON_BIND_SOCKET_TEXT },
    { ERR_DAEMON_SOCKET_PERMISSION, ERR_DAEMON_SOCKET_PERMISSION_TEXT },
    { ERR_DAEMON_SOCKET_LISTEN, ERR_DAEMON_SOCKET_LISTEN_TEXT },
    { ERR_DAEMON_READING_COMMAND, ERR_DAEMON_READING_COMMAND_TEXT },
    { ERR_DAEMON_INVALID_REQUEST, ERR_DAEMON_INVALID_REQUEST_TEXT },
    { ERR_DAEMON_CANNOT_DELETE, ERR_DAEMON_CANNOT_DELETE_TEXT },

    { ERR_END, ERR_END_TEXT }
};

/**
 * Return matching error message string from error code
 */
const char* getErrorString(int errorId)
{
    int count;

    for(count = 0; messageStructs[count].number != ERR_END; count++)
    {
        if(messageStructs[count].number == errorId)
            break;
    }

    if(messageStructs[count].number == ERR_END)
        printf("unknown error %d used\n", errorId);fflush(NULL);

    return messageStructs[count].text;
}

/**
 * Print error code and message to stderror
 */
void printError(int errorId)
{
    char buf[256];
    sprintf(buf, "ERROR [%d] %s", errorId, getErrorString(errorId));
    perror(buf);
    fflush(NULL);
}
