# API between PHP and Daemon

## ADD
### request
* a id:123 auther:John ...
* values are base64 encoded

### response
* success : 0,id
* failed  : errorcode,errormsg

## match
### request
* m hash:hashvalue

### response
* success : 0,count,id,...
* failed  : errorcode,errormsg

## delete
### request
* d id:123

### response
* success : 0
* failed  : errorcode,errormsg


http://localhost/api.php?request=match&hash=11788043576878985364
echo "m hash:MTE3ODgwNDM1NzY4Nzg5ODUzNjQ=" | netcat -U /tmp/cc.daemon.sock
