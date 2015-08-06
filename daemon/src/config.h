/*
 * config.h
 *
 *  Created on: Jul 7, 2015
 *      Author: hosung
 */

#ifndef CONFIG_H_
#define CONFIG_H_

#define CONFIG_FILENAME			"regdaemon.ini"

#define CONFIG_SEC_DB			"database"
#define CONFIG_DB_HOST			"hostname"
#define CONFIG_DB_USER			"username"
#define CONFIG_DB_PW			"password"
#define CONFIG_DB_SCHEMA		"schema"
#define CONFIG_DB_TABLE			"table"
#define CONFIG_DB_HASHKEY		"hashkey"
#define CONFIG_DB_HASHVALUE		"hashvalue"

typedef void * hConfig;

hConfig openConfig(const char *filename);
bool writeConfig(const hConfig con, const char *filename);
void closeConfig(hConfig con);

bool getConfigValue(hConfig con, const char *section, const char *name, char *value);
bool setConfigValue(hConfig con, const char *section, const char *name, const char *value);

#endif /* CONFIG_H_ */
