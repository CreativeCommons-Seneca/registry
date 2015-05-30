DEST_DIR=all-images

rm -f errors.log

for DIR in $*
do
  ls $DIR | while read FILE
  do
    DEST_FILE=`basename "${FILE%.*}"`.jpg
    
    echo "Processing $DIR/$FILE..."
    
    if echo $FILE | grep -qE ".[jJ][pP][gG]"
    then
      cp "$DIR/$FILE" "$DEST_DIR/$DEST_FILE"
    elif echo $FILE | grep -qE ".[pP][nN][gG]"
    then
      convert "$DIR/$FILE" "$DEST_DIR/$DEST_FILE" 2>&1 | tee -a errors.log
      if [ ${PIPESTATUS[0]} -ne 0 ]
      then
        echo "Convert failed for $DIR/$FILE" | tee -a errors.log
      fi
    elif echo $FILE | grep -qE ".[sS][vV][gG]"
    then
      convert "$DIR/$FILE" "$DEST_DIR/$DEST_FILE" 2>&1 | tee -a errors.log
      if [ ${PIPESTATUS[0]} -ne 0 ]
      then
        echo "Convert failed for $DIR/$FILE" | tee -a errors.log
      fi
    else
      echo "Unknown format for $DIR/$FILE" | tee -a errors.log
    fi
    
  done
done
