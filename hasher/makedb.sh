#!/bin/bash

DIR=$1
FILENAME=''

# sqlite3 phashes.db "create table Image (id INTEGER PRIMARY KEY,filename TEXT,hash TEXT)"

cat $DIR/results.txt | while read LINE
do
  if [ "x$FILENAME" = "x" ]
  then
    FILENAME=$DIR/$LINE
  else
    HASH=$LINE
    echo Inserting $FILENAME $HASH
    sqlite3 phashes.db  "insert into Image (filename,hash) values ('$FILENAME', '$HASH');"
    FILENAME=''
  fi
done
