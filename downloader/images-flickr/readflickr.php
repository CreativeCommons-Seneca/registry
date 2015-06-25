<?php

$filename = NULL;
$database = NULL;

foreach ($argv as $arg) {
  $e=explode("=",$arg);
  if(count($e)==2){
    $_GET[$e[0]]=$e[1];
  }else
    $_GET[]=$e[0];        
  }

  $filename = $_GET[1];
// Database stats
$servername = "localhost";
$username = "anna";
$password = "password";
$dbname = "hashes";


/*
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hashes";
*/

$myfile = fopen($filename, "r") or die("Unable to open file!");

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

if ($myfile) {
    while (($line = fgets($myfile)) !== false) {
        $counter++;
        //$name = trim($line,"./");
        //$name = trim($name);
        $delim = '\',\'';

        print_r (explode($delim,$line));

}
    echo "COUNTER: \n".$counter."\n";
    fclose($myfile);
} else {
    echo 'error opening the file';
} 
