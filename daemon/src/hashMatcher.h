// See hashMatcher.cpp for comments

#ifndef hashMatcher_h
#define hashMatcher_h

void add(uint64_t hash, uint64_t dbId);
void printUsageAndExit();
void processCommand(const char* command);
void readCommands(int socket);
void search(uint64_t hash, unsigned char maxDistance);
void* searchThread(void* threadParam);

#endif

