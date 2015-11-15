<?php

$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);

$ret = socket_connect($socket, "/tmp/cc.daemon.sock");
$args = array("imagename"=>"My Photo.jpg", 
 "id"=>"100");

if (!ret){
echo socket_strerror();
}











$str = "d ";
//$strtest = "d ";
foreach($args as $key=>$value){
    $str.=$key.":".base64_encode($value)." ";
    $strtest.=$key.":".$value." ";
}
$str.="\r\n";

echo $str;
echo $strtest;

socket_write($socket, $str, strlen($str));
 //$line = trim(socket_read($socket, MAXLINE));
//echo $line;

 $chunk = socket_read($socket, 4096);
echo $chunk;
//$out = "";
//while (socket_read ($socket, $out, 2048)) {
//echo $out;
//}
//socket_close($socket);?>


//a url=alskfdjlks== source=asdf
//m hash=alskjdfklajskldjflkasdf==
//d id=aslkjdfl=
