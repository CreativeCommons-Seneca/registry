## Introduction

* api.php is Creative Commons License server api that has add/match/delete image license

## Files

* ccError.php : defines error code and provide getErrorString
* ccDB.php : setting file for database connection information
- $servername : hostname that has image license database
- $username : username to connect db
- $password : password for the user
- $dbname : DB schema
* api.php : api file

## Database

* Structure of database : see database.md

## API Interface

* see API_JSONformat.txt

## Protocol between api.php and regdaemon

* see daemon/api.md

