# Daemon Interface

* C++ Daemon opens unix domain socket in "/var/cc/cc.daemon.sock"
* Daemon gets request from php for add/delete/match.
* Using netcat utility, protocol can be tested.
* All values are base64 encoded

```
echo "m hash:MTE3ODgwNDM1NzY4Nzg5ODUzNjQ=" | netcat -U /var/cc/cc.daemon.sock
echo "d id:NjQ=" | netcat -U /var/cc/cc.daemon.sock
```

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

