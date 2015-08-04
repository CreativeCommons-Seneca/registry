#!/bin/bash

# connect to db. get all id and mhash
# make index from it and save it to file

# mhindexer hostName userName password schema table key value filename

# hostName : mysql hostname
# userName : mysql username
# password : mysql password
# schema : db name
# table : table name
# key : image id field name in the table
# value : hash field name in the table
# filename : mvp tree file name

/usr/bin/time -f ",%U" ./mhindexer localhost root 555qwe555 hashes IMG id mhash tree.mh

#./mhindexer localhost root hosung hashes IMG id mhash tree.mh
