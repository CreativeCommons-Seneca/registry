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
        printf("expected: \"hasher direcory_with_images_to_hash\"\n");
        exit(1);
    }
    
    DIR* dir;
    struct dirent* ent;
    
    // Open directory where all the files to hash are
    dir = opendir(argv[1]);
    if (dir == NULL)
    {
        printf("failed to open directory '%s' for reading\n", argv[1]);
        exit(2);
    }
    
    chdir(argv[1]);
    
    // Open a file to record the results
    FILE* results = fopen("results.txt", "w");
    if (results == NULL)
    {
        printf("failed to open 'results.txt' for writing\n");
        exit(2);
    }
    
    while ((ent = readdir(dir)) != NULL)
    {
        if (strcmp(ent->d_name, ".") == 0 ||
            strcmp(ent->d_name, "..") == 0 )
        {
            continue;
        }
        
        ulong64 hash;
        int rc;
        
        rc = ph_dct_imagehash(ent->d_name, hash);
        if (rc < 0)
        {
            printf("ph_dct_imagehash returned < 0 for '%s', moving on to next file.\n", ent->d_name);fflush(NULL);
            // Imagemagick (used by phash) creates temporary files and doesn't
            // seem to delete them in case of an error, so this is a crappy 
            // way to do it myself:
            // The files go into those directories because before I run this program
            // I `export MAGICK_TMPDIR=/tmp/magick1 && mkdir -p $MAGICK_TMPDIR`
            system("rm -f /tmp/magick?/*");
            continue;
        }
        fprintf(results, "%s\n%llu\n", ent->d_name, hash);fflush(NULL);
    }
    
    fclose(results);
    closedir(dir);
    
    return 0;
}


