## Introduction

* api.php is Creative Commons License server api that has add/match/delete image license
* Gets request from client using Get/Post
* Request add/match to Daemon through domain socket
* API can be tested using following command line or using browser

```
curl -X GET 'http://localhost/api.php?request=match&hash=11788043576878985364'
curl -X GET 'http://localhost/api.php?request=add&phash=11415224608216087807&mhash=none&name=Jane&author=Tom&license=CC&url=http://google.com&imageurl=http://google.com/test.jpg&source=flickr&title=Hello'
curl -X GET 'http://localhost/api.php?request=delete&id=1'
```

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

## Interface between api.php and daemon

* see daemon/interface.md

