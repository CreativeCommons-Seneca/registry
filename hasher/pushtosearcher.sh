#!/bin/bash

sqlite3 phashes.db "select id,hash from Image" | while read LINE
do
  DBID=`echo $LINE | cut -d'|' -f 1`
  HASH=`echo $LINE | cut -d'|' -f 2`
  HASH=`printf '%x\n' $HASH`
  echo "add $DBID 0x$HASH" | netcat -U /tmp/searcher.sock
done
