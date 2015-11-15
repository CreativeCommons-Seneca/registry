PHASH_A=../pHash-0.9.6/src/.libs/libpHash.a

phash: phash.cpp Makefile
	g++  -O2 -ffast-math -O3  -L/usr/lib -o phash phash.cpp ${PHASH_A} -lpthread -lmpg123 -lsamplerate -lsndfile -lswscale -lavformat -lavutil -lavcodec -lpng -ljpeg -lfftw3
