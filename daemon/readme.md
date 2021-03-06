## depencency

Following library must be installed

* mysqlcppconn
  MySQL Connector/C++ : https://dev.mysql.com/downloads/connector/cpp/1.1.html

```
$ wget http://dev.mysql.com/get/Downloads/Connector-C++/mysql-connector-c++-1.1.6-linux-glibc2.5-x86-64bit.rpm
or
$ wget http://dev.mysql.com/get/Downloads/Connector-C++/mysql-connector-c++-1.1.6-linux-glibc2.5-x86-32bit.rpm

$sudo yum install mysql-connector-c++-1.1.6-linux-glibc2.5-x86-32bit.rpm
```

## Build Instruction

```
$cd build
$make clean
$make
```

## ini file

* Contains MySQL Connection information
* Daemon starts and read all hash keys and hash values from database and load them on the memory
* path : the same directory with regdaemon executable
* filename : regdaemon.ini
* format :

```
[database]
hostname=localhost ; hostname that has image license database
username=ccommons ; username to connect db
password=testpassword ; password for the user
schema=hashes ; DB schema that contains following table
table=IMG ; Table name
hashkey=id ; Key field name
hashvalue=phash ; Hash value field name 
```

## Socket path

* Daemon and php api communicate through UNIX domain socket
* Path : /var/cc/cc.daemon.sock
* '/var/cc' directory should have 'drwxrwxrwx' permission.

```
$mkdir /var/cc
$chmod 777 /var/cc
```

## Interface for request

* See interface.md

## Run

* number of cores should be set with -c flag
```
./regdaemon -c 4
```

## Run automatically on boot

To run the service automatically on boot you can copy the file regdaemon.service to /etc/systemd/system/regdaemon.service, then enable and start it like this:
```
systemctl start regdaemon.service
systemctl enable regdaemon.service
```

