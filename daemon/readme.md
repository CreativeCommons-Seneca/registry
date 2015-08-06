## depencency

Following library must be installed

* mysqlcppconn
  MySQL Connector/C++ : https://dev.mysql.com/downloads/connector/cpp/1.1.html

* pthread

## Build Instruction

$cd build
$make clean
$make

## ini file

* Contains MySQL Connection information
* Daemon starts and read all hash keys and hash values from database and load them on the memory
* path : the same directory with regdaemon executable
* filename : regdaemon.ini
* format :

```
[database]
hostname=localhost : hostname that has image license database
username=ccommons : username to connect db
password= : password for the user
schema=hashes : DB schema that contains following table
table=IMG : Table name
hashkey=id : Key field name
hashvalue=phash : Hash value field name 
```

## Socket path

* Daemon and php api communicate through UNIX domain socket
* Path : /var/cc/cc.daemon.sock
* '/var/cc' directory should have 'drwxrwxrwx' permission.

```
$mkdir /var/cc
$chmod 777 /var/cc
```

## Run

* number of cores should be used as a commandline argument
  ./regdaemon -c 4

