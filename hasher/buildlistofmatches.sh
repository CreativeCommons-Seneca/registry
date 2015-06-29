#!/bin/bash

# Results:
# Matching one: 
# Matching zero: 
# Matching more than one: 

rm -f reallybadduplicates.txt
echo '<html>'  > duplicates.html
echo '<body>'  >> duplicates.html
echo '<table>'  >> duplicates.html

#echo "273|0x2f1cadb0f6d51243" | while read LINE
sqlite3 phashes.db "select id,hash from Image" | while read LINE
do
  DBID=`echo $LINE | cut -d'|' -f 1`
  HASH=`echo $LINE | cut -d'|' -f 2`
  HASH=`printf '%x\n' $HASH`
  echo "match 0x$HASH 4" | netcat -U /tmp/searcher.sock > /tmp/currentmatch.txt
  NUMMATCHES=`cat /tmp/currentmatch.txt | wc -l`
  if [ $NUMMATCHES -eq 1 ]
  then
    true
    #echo ONE
  elif [ $NUMMATCHES -eq 0 ]
  then
    true
    #echo ZERO
  else
    if [ $NUMMATCHES -gt 10 ]
    then
      #echo TOOMANY
      echo $NUMMATCHES matches for $DBID >> reallybadduplicates.txt
    else
      #echo MANY
      echo '<tr>' >> duplicates.html
      cat /tmp/currentmatch.txt | while read LINE2
      do
        DBID2=`echo $LINE2 | cut -d' ' -f 1`
        FILENAME=`sqlite3 phashes.db "select filename from image where id=$DBID2"`
        #convert $FILENAME -resize 500x500\> thumb/$FILENAME
        echo '<td><a href="'$FILENAME'"><img width="250" height="250" src="'$FILENAME'" /></a></td>' >> duplicates.html
      done
      echo '</tr>' >> duplicates.html
    fi
  fi
done

echo '</table>'  >> duplicates.html
echo '</body>'  >> duplicates.html
echo '</html>'  >> duplicates.html
