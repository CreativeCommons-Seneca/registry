#include <list>
#include <vector>
#include <forward_list>
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

#define SOCKET_PATH "/tmp/searcher.sock"
#define CONNECTION_QUEUE_SIZE 10

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
    unsigned threadNum;
    uint64_t queryHash;
    int maxDistance;
    std::list<Node>* nodeList;
};

// This is set as a command-line parameter and is used to configure the
// number of search threads (and associated things).
unsigned GBLnumCores;
// Array of lists of nodes to search. One list per thread for efficiency.
std::list<Node>* GBLnodeLists;
// Array of threads to do the searching of the lists above.
pthread_t* GBLsearchThreads;
// Search results
std::vector<Match> GBLsearchResults;
// Mutex for the vector above since it's updated from multiple threads
pthread_mutex_t GBLsearchResultsMutex = PTHREAD_MUTEX_INITIALIZER;

void loadList()
{
    // This function doesn't belong here. The sql querying should be in another
    // process, sending the results into this program via a named pipe.
    // For now fill the lists up with 100 million random records for testing.
    unsigned numFakeRecords = 100000000;
    unsigned numRecordsPerList = numFakeRecords / GBLnumCores;
    
    for (unsigned nodeListNum = 0; nodeListNum < GBLnumCores; nodeListNum++)
    {
        printf("Loading list #%d... ", nodeListNum);fflush(NULL);
        for (unsigned recordNum = 0; recordNum < numRecordsPerList; recordNum++)
        {
            // Duplicate dbIds are ok for testing here
            GBLnodeLists[nodeListNum].emplace_front(recordNum, random());
        }
        printf("done.\n");fflush(NULL);
    }
}

/**
 * Complain about bad parameters and exit.
 */
void printUsageAndExit()
{
    printf("Bad parameters. Usage:\n"
           "searcher -c NUM_CORES\n");
    exit(1);
}

/**
 * Go through a list of Nodes and for each one - do a hamming distance
 * calculation.
 */
void* search(void* threadParam)
{
    unsigned threadNum = ((ThreadParam*)threadParam)->threadNum;
    uint64_t queryHash = ((ThreadParam*)threadParam)->queryHash;
    int maxDistance = ((ThreadParam*)threadParam)->maxDistance;
    std::list<Node>* nodeList = ((ThreadParam*)threadParam)->nodeList;
    
    printf("Thread number %d is now working\n", threadNum);fflush(NULL);
    
    // BEGIN PERFORMANCE-CRITICAL SECTION
    for (std::list<Node>::iterator it = nodeList->begin(); it != nodeList->end(); it++)
    {
        // The next two lines are the ones that need to be optimised
        uint64_t bitsToCount = queryHash ^ it->pHash;
        int distance = __builtin_popcountl(bitsToCount);
        
        if (distance <= maxDistance)
        {
            //printf("Ditance %d between 0x%lX and 0x%lX (bits 0x%lX)\n",
                   //distance, queryHash, it->pHash, bitsToCount);
            
            pthread_mutex_lock(&GBLsearchResultsMutex);
            GBLsearchResults.emplace_back(it->dbId, distance);
            pthread_mutex_unlock(&GBLsearchResultsMutex);
        }
    }
    // END PERFORMANCE-CRITICAL SECTION
    
    printf("Thread number %d is done\n", threadNum);fflush(NULL);
    
    pthread_exit(NULL);
}

int main(int argc, char** argv)
{
    int c;
    int rc;
    
    //!! used for debugging, delete it
    srand(time(NULL));
    
    // Parse arguments to figure out how many threads to have
    while ((c = getopt (argc, argv, "c:")) != -1)
    {
        if (c == 'c')
        {
            int rc;
            rc = sscanf(optarg, "%d", &GBLnumCores);
            if (rc != 1 || GBLnumCores < 1)
                printUsageAndExit();
        }
        else if (c == '?')
            printUsageAndExit();
    }
    
    // Allocate the array of lists, one list per core
    GBLnodeLists = new std::list<Node>[GBLnumCores];
    
    // Allocate the array of threads for searching, one thread per core
    GBLsearchThreads = new pthread_t[GBLnumCores];
    
    // Load fake values
    loadList();
    
    // BEGIN set up listening socket
    int listeningSocket;
    int len;
    struct sockaddr_un listeningAddr;
    
    listeningSocket = socket(AF_UNIX, SOCK_STREAM, 0);
    if (listeningSocket == -1)
    {
        perror("Couldn't create server socket: ");
        exit(3);
    }
    
    listeningAddr.sun_family = AF_UNIX;
    strcpy(listeningAddr.sun_path, SOCKET_PATH);
    unlink(listeningAddr.sun_path);
    len = strlen(listeningAddr.sun_path) + sizeof(listeningAddr.sun_family);
    rc = bind(listeningSocket, (struct sockaddr *)&listeningAddr, len);
    if (rc == -1)
    {
        perror("Couldn't bind socket: ");
        exit(4);
    }
    
    rc = listen(listeningSocket, CONNECTION_QUEUE_SIZE);
    if (rc == -1)
    {
        perror("Couldn't bind socket: ");
        exit(5);
    }
    // END set up listening socket
    
    // BEGIN MAIN loop accepting connections and doing the work
    while (true)
    {
        int acceptedSocket;
        struct sockaddr_un remoteAddr;
        socklen_t remoteAddrLen;
        
        // Wait for a connection
        remoteAddrLen = sizeof(remoteAddr);
        acceptedSocket = accept(listeningSocket, 
                                (struct sockaddr *)&remoteAddr, &remoteAddrLen);
        if (acceptedSocket == -1)
            continue;
        
        /**
         * Get command from client. One command per connection. Valid commands:
         * 
         * "match uint64_in_hex\n" (max 0xFFFFFFFFFFFFFFFF, that's a total of
         *                          25 characters including the newline)
         * Returns newline-separated list of pairs of dbId and distance:
         * "uint64_in_decimal uint8_in_decimal\n"
         */
        char command[100];
        int numBytesRead;
        numBytesRead = recv(acceptedSocket, command, 100, 0);
        if (numBytesRead == -1)
        {
            perror("Error reading command from client: ");
            close(acceptedSocket);
            continue;
        }
        if (numBytesRead > 25 || strstr(command, "\n") == NULL)
        {
            fprintf(stderr, "Bad command from client\n");
            close(acceptedSocket);
            continue;
        }
        
        uint64_t hash;
        
        // See if this is a match command
        rc = sscanf(command, "match %" SCNx64 "\n", &hash);
        if (rc == 1)
        {
            printf("Will search for %" PRIx64 "\n", hash);
            GBLsearchResults.clear();
            
            ThreadParam* threadParam = new ThreadParam[GBLnumCores];
            
            // Start the search on all threads.
            for (unsigned i = 0; i < GBLnumCores; i++)
            {
                threadParam[i].threadNum = i;
                threadParam[i].queryHash = hash;
                threadParam[i].maxDistance = 4;
                threadParam[i].nodeList = &(GBLnodeLists[i]);
                int rc = pthread_create(&GBLsearchThreads[i], NULL, search, 
                                        (void *)(&threadParam[i]));
                if (rc != 0)
                {
                    printf("Error: pthread_create() returned %d\n", rc);
                    exit(2);
                }
            }
            
            // Wait for all the threads to finish. That should happen at about
            // the same time.
            for (unsigned i = 0; i < GBLnumCores; i++)
                pthread_join(GBLsearchThreads[i], NULL);
            
            // The results (if any) are all in GBLsearchResults. Send them back.
            for (unsigned i = 0; i < GBLsearchResults.size(); i++)
            {
                char response[100];
                unsigned responseLen, numBytesSent;
                responseLen = sprintf(response, "%" PRIu64 " %u\n", 
                        GBLsearchResults[i].dbId, GBLsearchResults[i].distance);
                
                numBytesSent = send(acceptedSocket, response, responseLen, MSG_NOSIGNAL);
                if (numBytesSent != responseLen)
                {
                    fprintf(stderr, "Couldn't send response to client\n");
                    break;
                }
            }
        } // if match command
        
        close(acceptedSocket);
    }
    // END MAIN loop accepting connections and doing the work
    
    return 0;
}
