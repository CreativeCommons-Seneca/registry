#include <stdio.h>
#include <dirent.h>
#include <errno.h>
#include <vector>
#include <algorithm>
#include "pHash.h"

int main(int argc, char** argv)
{
    if (argc < 3)
    {
        printf("Usage: %s hash1 hash2 (the hashes are uint64s)\n", argv[0]);
        return 1;
    }
    
    ulong64 hash1 = strtoul(argv[1], NULL, 10);
    ulong64 hash2 = strtoul(argv[2], NULL, 10);
    
    //printf("%s -> %s\n", argv[1], argv[2]);
    //printf("%llu -> %llu -> ", hash1, hash2);
    
    int distance = ph_hamming_distance(hash1, hash2);
    printf("%d\n", distance);
    
    return 0;
}
