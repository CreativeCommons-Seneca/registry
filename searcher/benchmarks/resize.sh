F=$1

# 64 results in distances of up to 6
# 128 results in distances of up to 4
for SIZE in 64 128 256 512 1024 1536 2048
do
    echo $SIZE
    convert -resize $SIZE "$F" "`basename "$F" .jpg`-resize${SIZE}.jpg"
done
