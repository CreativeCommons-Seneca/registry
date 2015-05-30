DIR=$1
MAX_DIST=$2

for HASH1 in $DIR/*txt
#for HASH1 in $DIR/01abc.jpg.txt
do
    ls $DIR/*.txt | grep -v "$HASH1" | while read HASH2
    do
        DIST=$(./hamming.pl `cat "$HASH1"` `cat "$HASH2"`)
        if [ "$DIST" -le "$MAX_DIST" ]
        then
            echo "$HASH1 -> $HASH2 -> $DIST"
        fi
    done
done
