PHASH_A=../searcher/benchmarks/pHash-0.9.6/src/.libs/libpHash.a

hasher: hasher.cpp
	g++  -O2 -ffast-math -O3  -L/usr/lib -o hasher hasher.cpp ${PHASH_A} -lpthread -lmpg123 -lsamplerate -lsndfile -lswscale -lavformat -lavutil -lavcodec -lpng -ljpeg -lfftw3
