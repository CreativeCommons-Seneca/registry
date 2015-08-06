/*******************************************************************************
 * hashMatcher.cpp
 * 
 * A simple server that stores a giant lot of 64bit hashes in memory (tested 
 * with 100 million) and lets you find all the entries in that list that have 
 * a hamming distance less than X compared with the query hash.
 * 
 * Author: Andrew Smith
 * Licence: AGPL v3
 */

#include <list>
#include <vector>
#include <forward_list>
#include <map>
#include <unistd.h>
#include <stdint.h>
#include <stdlib.h>
#include <cstdio>
#include <time.h>
#include <stdlib.h>
#include <pthread.h>
#include <inttypes.h>
#include <sys/socket.h>
#include <sys/un.h>
#include <sys/time.h>
#include <sys/stat.h>
#include <algorithm>

#include "base64/decode.h"
#include "hashMatcher.h"
#include "database.h"

#define SOCKET_PATH "/var/cc/cc.daemon.sock"
//#define SOCKET_PATH "/var/www/html/cc.daemon.sock"

#define CONNECTION_QUEUE_SIZE 10

#define RELEASE_VERSION "0.003"

/**
 * These hold all the data to compare against. They take up a lot of memory
 * so don't add stuff here willy-nilly.
 */
struct Node
{
    uint64_t dbId;
    uint64_t pHash;
    
    Node(uint64_t dbId, uint64_t pHash)
    {
        this->dbId = dbId;
        this->pHash = pHash;
    }
};

/**
 * These are used to record a result of a search.
 */
struct Match
{
    uint64_t dbId;
    int distance;
    
    Match(uint64_t dbId, int distance)
    {
        this->dbId = dbId;
        this->distance = distance;
    }
};

/**
 * Parameters to pass to a search thread
 */
struct ThreadParam
{
    unsigned threadNum;         // Just for debugging
    uint64_t queryHash;         // My "search string"
    int maxDistance;            // Threshold for results
    std::forward_list<Node>* nodeList;  // The list to search in this thread
};

// This is set as a command-line parameter and is used to configure the
// number of search threads (and associated things).
unsigned GBLnumCores;

// Array of lists of nodes to search. One list per thread for efficiency.
std::forward_list<Node>* GBLnodeLists;
// And the size of each of the lists above, for constant-time querying
int* GBLnodeListSizes;
// Array of threads to do the searching of the lists above.
pthread_t* GBLsearchThreads;
// Search results
std::vector<Match> GBLsearchResults;
// Mutex for the vector above since it's updated from multiple threads
pthread_mutex_t GBLsearchResultsMutex = PTHREAD_MUTEX_INITIALIZER;

/**
 * Add a node to the shortest list (or at least to one that's not the longest)
 */
void add(uint64_t hash, uint64_t dbId)
{
    // Look through all the lists. If the second is shorter than the first: add
    // to that. Else compare second and third, etc. 
    // If all lists are the same length or (very unlikely) longer lists follow
    // shorter ones: add to the first list.
    bool added = false;
    for (unsigned i = 0; i < GBLnumCores - 1 && !added; i++)
    {
        if (GBLnodeListSizes[i+1] < GBLnodeListSizes[i])
        {
            GBLnodeLists[i+1].emplace_front(hash, dbId);
            GBLnodeListSizes[i+1] += 1;
            added = true;
        }
    }
    if (!added)
    {
        GBLnodeLists[0].emplace_front(hash, dbId);
        GBLnodeListSizes[0] += 1;
    }
}

/**
 * Used for remove function
 */
struct matchid{
	uint64_t id;
	matchid(uint64_t dbId) : id(dbId){}
	bool operator() (const Node &node){
		return node.dbId == id;
	}
};

/**
 * Remove a node that the id is the same as requested id
 */
void remove(uint64_t dbId)
{
	bool removed = false;
	for (unsigned i = 0; i < GBLnumCores - 1 && !removed; i++)
	{
		GBLnodeLists[i].remove_if(matchid(dbId));
	}
}

/**
 * Complain about bad parameters and exit.
 */
void printUsageAndExit()
{
    printf("Bad parameters. Usage:\n\n"
           "searcher -c NUM_CORES\n\n"
           "Then connect to the socket %s and send a 'match' or 'add' command\n"
           "match hash_uint64_in_hex max_distance_uint8_in_decimal\n"
           "add dbId_uint64_in_decimal hash_uint64_in_hex\n", SOCKET_PATH);
    exit(1);
}


/**
 * Split string into string vector
 * from http://stackoverflow.com/questions/53849/how-do-i-tokenize-a-string-in-c
 */
std::vector<std::string> split(const char *str, char c = ' ')
{
	std::vector<std::string> result;

    do
    {
        const char *begin = str;

        while(*str != c && *str)
            str++;

        result.push_back(std::string(begin, str));
    } while (0 != *str++);

    return result;
}

/**
 * Parse request string
 * format : m hash:lskjflksjld=
 * put name:value pairs to TCmdMap after decoding base64 encoded value
 */
TCmdMap parseCommand(const char* command){
	TCmdMap cmdMap;

	std::vector<std::string> fields;
	std::vector<std::string> all = split(command, ' ');
	std::vector<std::string>::iterator cur = all.begin();

	while(cur != all.end()){

		std::vector<std::string> internal = split(std::string(*cur).c_str() , ':');

		if (internal.size() == 2){
			//std::cout << internal.at(0) << " : " << internal.at(1) << std::endl;

			std::string value = internal.at(1);

			//decode base64.
			int len = value.length();
		    char *out = (char *)malloc(len); //this is ok. decoded size is always smaller.
		    memset(out, 0x00, len);
			base64::decoder D(len);
			D.decode(value.c_str(), len, out);

			cmdMap.insert(TStrStrPair(internal.at(0), out));

			free(out);
		}

		++cur;
	}

	/* log
	TCmdMap::iterator p;

	for(p = cmdMap.begin(); p!=cmdMap.end(); ++p)
	{
		std::cout << p->first << " : ";
		std::cout << p->second << std::endl;
	}
	*/

	return cmdMap;
}

/**
 * Run command from client. Valid commands:
 * 
 * "match hash_uint64_in_hex max_distance_uint8_in_decimal\n" 
 * (max length of hash is 0xFFFFFFFFFFFFFFFF, 
 *  max_distance is a byte, most likely single, maybe double-digits 
 *  that's a total max of 29 characters including the newline)
 * Returns newline-separated list of pairs of dbId and distance:
 * "uint64_in_decimal uint8_in_decimal\n"
 * 
 * Command format
 *  add : a name:base64(value)
 *  	name : author, name, etc..
 *  match : m name:base64(value)
 *  	name : match
 *  delete : d name:base64(value)
 *  	name : id
 *
 * Return format
 *  add
 *  	success : 0,id
 *  	error : errorcode,errordesc
 *  match
 *		success : 0,number,matched ids
 *		error : errorcode,errordesc
 *	delete
 *		success : 0
 *		error : errorcode,errordesc
 */
void processCommand(int socket, const char* command)
{
    if (!command || !*command)
    	return;

	TCmdMap cmdMap = parseCommand(command);

	std::string response;

	switch (*command){
    case 'a':
    	std::cout << "ADD" << std::endl;
    	//if seccess get added id and hash
		{
			//check mendatory parameters
			auto search = cmdMap.find("phash");
			if(search == cmdMap.end() || search->second.length() == 0){
				printError(ERR_DAEMON_INVALID_REQUEST);
				response = std::to_string(ERR_DAEMON_INVALID_REQUEST) + "," + ERR_DAEMON_INVALID_REQUEST_TEXT;
			}
			else{
				uint64_t id = 0;
				RCODE rCode;
				try{
					if (RCODE_SUCCESSED(rCode = addNewContent(cmdMap, &id))){
						std::string strHash = search->second;
						uint64_t uiHash = std::stoull(strHash);

						add(uiHash, id);
						response = "0," + std::to_string(id);
					}
					else{
						response = std::to_string(rCode);
						std::string errorstring = getLastDBErrorString();
						if (errorstring.length() > 0){
							response += "," + errorstring;
						}
					}
				}catch(std::exception &e){
					std::cerr << "# ERR: " << e.what();
					response = std::to_string(ERR_DAEMON_INVALID_REQUEST) + "," + ERR_DAEMON_INVALID_REQUEST_TEXT + " : " + e.what();
				}
			}
		}

    	break;
    case 'm':
		{
			auto search = cmdMap.find("hash");
			if(search == cmdMap.end()){
				printError(ERR_DAEMON_INVALID_REQUEST);
				response = std::to_string(ERR_DAEMON_INVALID_REQUEST) + "," + ERR_DAEMON_INVALID_REQUEST_TEXT;
			}
			else{
				try{
					std::string strHash = search->second;

					uint64_t uiHash = std::stoull(strHash);
					::search(uiHash, 4);
					response = "0,";
					response += std::to_string(GBLsearchResults.size());

					for (unsigned i = 0; i < GBLsearchResults.size(); i++)
					{
						response += "," + std::to_string(GBLsearchResults[i].dbId);
					}
				}catch(std::exception &e){
					std::cerr << "# ERR: " << e.what();
					response = std::to_string(ERR_DAEMON_INVALID_REQUEST) + "," + ERR_DAEMON_INVALID_REQUEST_TEXT + " : " + e.what();
				}
			}
		}

    	break;
    case 'd':
		{
			//check mendatory parameters
			auto search = cmdMap.find("id");
			if(search == cmdMap.end()){
				printError(ERR_DAEMON_INVALID_REQUEST);
				response = std::to_string(ERR_DAEMON_INVALID_REQUEST) + "," + ERR_DAEMON_INVALID_REQUEST_TEXT;
			}
			else{
				RCODE rCode;
				try{
					if (RCODE_SUCCESSED(rCode = markDeleted(cmdMap))){
						uint64_t uiHash = std::stoull(cmdMap["id"]);
						remove(uiHash);
						//delete from memory
						response = "0,";
					}
					else{
						response = std::to_string(rCode);
						std::string errorstring = getLastDBErrorString();
						if (errorstring.length() > 0){
							response += "," + errorstring;
						}
					}
				}catch(std::exception &e){
					std::cerr << "# ERR: " << e.what();
					response = std::to_string(ERR_DAEMON_INVALID_REQUEST) + "," + ERR_DAEMON_INVALID_REQUEST_TEXT + " : " + e.what();
				}
			}
		}

    	break;
    }

	std::cout << "Response : " << response << std::endl;

	unsigned responseLen = response.length(), numBytesSent;
	numBytesSent = send(socket, response.c_str(), response.length(), MSG_NOSIGNAL);

	if (numBytesSent != responseLen)
		perror("Couldn't send response to client\n");

	std::cout << "Sent" << std::endl;
}

/**
 * Keep reading newline-separated commands from the socket. Then pass each 
 * command to processCommand().
 */
void readCommands(int socket)
{
    const int bufferSize = 1024;
    char buffer[bufferSize + 1]; // more than enough for one command + '\0'
    int numBytesFilled = 0;
    int rc;
    
    while (true)
    {
        rc = recv(socket, buffer + numBytesFilled, 
                  bufferSize - numBytesFilled, 0);
        if (rc == 0)
            break; // connection closed
        if (rc == -1)
        {
        	printError(ERR_DAEMON_READING_COMMAND);
            break;
        }
        numBytesFilled += rc;
        
        char* lineStart = buffer;
        char* lineEnd;
        while ( (lineEnd = (char*)memchr((void*)lineStart, '\n', 
                                        numBytesFilled - (lineStart - buffer))))
        {
            *lineEnd = '\0';
            processCommand(socket, lineStart);
            lineStart = lineEnd + 1;
        }
        
        /* Shift buffer down so the unprocessed data is at the start */
        numBytesFilled -= (lineStart - buffer);
        memmove(buffer, lineStart, numBytesFilled);
        
        if (numBytesFilled == bufferSize)
        {
            fprintf(stderr, "Command too long, closing connection.\n");
            break;
        }
    }
}

/**
 * Start threads to do the search, and wait for them to finish. Results are in
 * the global GBLsearchResults
 */
void search(uint64_t hash, unsigned char maxDistance)
{
    GBLsearchResults.clear();
    
    ThreadParam* threadParam = new ThreadParam[GBLnumCores];
    
    // Start the search on all threads.
    for (unsigned i = 0; i < GBLnumCores; i++)
    {
        threadParam[i].threadNum = i;
        threadParam[i].queryHash = hash;
        threadParam[i].maxDistance = maxDistance;
        threadParam[i].nodeList = &(GBLnodeLists[i]);
        int rc = pthread_create(&GBLsearchThreads[i], NULL, 
                                searchThread, 
                                (void *)(&threadParam[i]));
        if (rc != 0)
        {
            printf("Error: pthread_create() returned %d\n", rc);
            exit(2);
        }
    }
    
    // Wait for all the threads to finish. That should happen at about
    // the same time because the lists are the same size and the operations
    // almost always the same length.
    for (unsigned i = 0; i < GBLnumCores; i++)
        pthread_join(GBLsearchThreads[i], NULL);
}

/**
 * Go through a list of Nodes and for each one - do a hamming distance
 * calculation. Put results into the global GBLsearchResults.
 */
void* searchThread(void* threadParam)
{
    //unsigned threadNum = ;
    uint64_t queryHash = ((ThreadParam*)threadParam)->queryHash;
    int maxDistance = ((ThreadParam*)threadParam)->maxDistance;
    std::forward_list<Node>* nodeList = ((ThreadParam*)threadParam)->nodeList;
    
    //printf("Thread number %d is now working\n", 
             //((ThreadParam*)threadParam)->threadNum);fflush(NULL);
    
    // BEGIN PERFORMANCE-CRITICAL SECTION
    for (std::forward_list<Node>::iterator it = nodeList->begin(); 
         it != nodeList->end(); it++)
    {
        // The next two lines are the ones that need to be optimised
        uint64_t bitsToCount = queryHash ^ it->pHash;
        int distance = __builtin_popcountl(bitsToCount);
        
        if (distance <= maxDistance)
        {
            printf("Ditance %d between 0x%lX and 0x%lX (bits 0x%lX)\n",
                   distance, queryHash, it->pHash, bitsToCount);
            
            pthread_mutex_lock(&GBLsearchResultsMutex);
            GBLsearchResults.emplace_back(it->dbId, distance);
            //!! Set a limit on the number of results and check it here (so I don't run out of memory, etc)
            pthread_mutex_unlock(&GBLsearchResultsMutex);
        }
    }
    // END PERFORMANCE-CRITICAL SECTION
    
    //printf("Thread number %d is done\n", 
            //((ThreadParam*)threadParam)->threadNum);fflush(NULL);
    
    pthread_exit(NULL);
}


int main(int argc, char** argv)
{
    int c;
    int rc;
    
    std::cout << "regdaemon version : " << RELEASE_VERSION << std::endl;

    // Parse arguments to figure out how many threads to have
    bool paramsOk = false;
    while ((c = getopt (argc, argv, "c:")) != -1)
    {
        if (c == 'c')
        {
            int rc;
            rc = sscanf(optarg, "%d", &GBLnumCores);
            if (rc != 1 || GBLnumCores < 1)
                printUsageAndExit();
            else
                paramsOk = true;
        }
        else if (c == '?')
            printUsageAndExit();
    }
    if (!paramsOk)
        printUsageAndExit();
    
    // Allocate the array of lists, one list per core
    GBLnodeLists = new std::forward_list<Node>[GBLnumCores];
    
    // Initialize the linked list size counters
    GBLnodeListSizes = new int[GBLnumCores];
    for (unsigned i = 0; i < GBLnumCores; i++)
        GBLnodeListSizes[i] = 0;

    //load from database
	printf("Loading Database Started\n");
	int count = 0;
	RCODE rCode = addFromDatabase(add, &count);
	if (RCODE_FAILED(rCode)){
		printError(rCode);
		perror(getLastDBErrorString().c_str());
	}
	//int count = addFromDatabase(add);
	printf("Loading Database Finished %d hash added\n", count);

    // Allocate the array of threads for searching, one thread per core
    GBLsearchThreads = new pthread_t[GBLnumCores];
    
    //// Insert a hundred million random records for debugging purposes
    //printf("Loading random data... \n");fflush(NULL);
    //int numNodes = 100000000;
    //for (int i = 0; i < numNodes; i++)
        //add(i, random());
    //printf("Loaded:\n");
    //for (unsigned i = 0; i < GBLnumCores; i++)
        //printf("  List %d: %d nodes\n", i, GBLnodeListSizes[i]);
    
    //// Do a thousand searches for performance testing
    //int numSearches = 10;
    //struct timeval tv1, tv2;
    //gettimeofday(&tv1, NULL);
    //for (int i = 0; i < numSearches; i++)
        //search(12345, 4);
    //gettimeofday(&tv2, NULL);
    //printf("%d searches took %ld.%03ld seconds\n", numSearches,
           //tv2.tv_sec - tv1.tv_sec, tv2.tv_usec - tv1.tv_usec);
    
    // BEGIN set up listening socket
    int listeningSocket;
    int len;
    struct sockaddr_un listeningAddr;
    
    listeningSocket = socket(AF_UNIX, SOCK_STREAM, 0);
    if (listeningSocket == -1)
    {
    	printError(ERR_DAEMON_CREATE_SOCKET);
        exit(3);
    }
    
    listeningAddr.sun_family = AF_UNIX;
    strcpy(listeningAddr.sun_path, SOCKET_PATH);
    unlink(listeningAddr.sun_path);
    len = strlen(listeningAddr.sun_path) + sizeof(listeningAddr.sun_family);
    rc = bind(listeningSocket, (struct sockaddr *)&listeningAddr, len);
    if (rc == -1)
    {
    	printError(ERR_DAEMON_BIND_SOCKET);
        exit(4);
    }
    
    //php cannot access without changing permission
    if (chmod(SOCKET_PATH, 0666) < 0){
    	printError(ERR_DAEMON_SOCKET_PERMISSION);
    }

    rc = listen(listeningSocket, CONNECTION_QUEUE_SIZE);
    if (rc == -1)
    {
    	printError(ERR_DAEMON_SOCKET_LISTEN);
        exit(5);
    }
    // END set up listening socket
    
    // Main loop accepting connections and doing the work
    while (true)
    {
        int acceptedSocket;
        struct sockaddr_un remoteAddr;
        socklen_t remoteAddrLen;
        
        printf("Waiting for a connection.\n");
        remoteAddrLen = sizeof(remoteAddr);
        acceptedSocket = accept(listeningSocket, 
                                (struct sockaddr *)&remoteAddr, &remoteAddrLen);
        if (acceptedSocket == -1)
            continue;
        printf("Connection established, waiting for commands.\n");
        
        readCommands(acceptedSocket);
        printf("Connection closed.\n");
        close(acceptedSocket);
    }
    
    return 0;
}
