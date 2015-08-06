/*
 * database.h
 *
 *  Created on: Jul 6, 2015
 *      Author: Hosung Hwang
 */

#ifndef DATABASE_H_
#define DATABASE_H_

#include "error.h"

//callback function to add element into the memory
typedef void (*ADDFUNCTION)(uint64_t, uint64_t);
typedef std::map<std::string, std::string> TCmdMap;
typedef std::pair<std::string, std::string> TStrStrPair;

RCODE markDeleted(TCmdMap map);
RCODE addNewContent(TCmdMap map, uint64_t *cid);
RCODE addFromDatabase(ADDFUNCTION add, int *added);
const std::string &getLastDBErrorString();

void closeConnection();

#endif /* DATABASE_H_ */
