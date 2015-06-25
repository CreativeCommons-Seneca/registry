<?php


// Database stats
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hashes";



$myfile = fopen("2015-03-20-2015-03-20_flickrdownload.txt", "r") or die("Unable to open file!");

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
    echo "CONTER: \n".$counter."\n";
    fclose($myfile);
} else {
    echo 'error opening the file';
} 
