#include <stdio.h>
#include <dirent.h>
#include <errno.h>
#include <vector>
#include <algorithm>
#include "pHash.h"

using namespace std;

int main(int argc, char **argv){

    if (argc < 2)
    {
        printf("no input args\n");
        printf("expected: \"phash filename1 filename2\"\n");
        exit(1);
    }
    
    const char* filename = argv[1];
    errno = 0;
    int i = 0;
    ulong64 tmphash;
    int rc;
    rc = ph_dct_imagehash(filename, tmphash);
    if (rc < 0)
    {
        printf("ph_dct_imagehash returned < 0\n");
        exit(1);
    }
    
    printf("%llu\n", tmphash);
    
    return 0;
}

