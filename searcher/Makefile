CXX=g++
CFLAGS=-std=c++11 -Wall -pthread -g

hashMatcher: Makefile hashMatcher.cpp
	$(CXX) $(CFLAGS) hashMatcher.cpp -o hashMatcher

hashMatcherFortranTest: Makefile hashMatcher.f
	gfortran -fopenmp -mcmodel=medium hashMatcher.f -o hashMatcherFortranTest
