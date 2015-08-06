/*
 * config.cpp
 *
 *  Created on: Jul 7, 2015
 *      Author: hosung
 */

#include "config.h"

extern "C"{
	#include "iniparser/iniparser.h"
}

hConfig openConfig(const char *filename){
	return (hConfig)iniparser_load(filename);
}

bool writeConfig(const hConfig con, const char *filename){
	FILE *f = fopen(filename, "w");
	if (f == NULL){
		return false;
	}

	iniparser_dump_ini((dictionary *)con, f);
	fclose(f);

	return true;
}

void closeConfig(hConfig con){
	iniparser_freedict((dictionary *)con);
}

bool getConfigValue(hConfig con, const char *section, const char *name, char *value){
	char key[128];
	sprintf(key, "%s:%s", section, name);

	char *ret = iniparser_getstring((dictionary *)con, key, NULL);
	if (ret){
		strcpy(value, ret);
		return true;
	}

	return false;
}

bool setConfigValue(hConfig con, const char *section, const char *name, const char *value){
	char key[128];
	sprintf(key, "%s:%s", section, name);

	int ret = iniparser_set((dictionary *)con, key, value);

	return ret == 0 ? true : false;
}
