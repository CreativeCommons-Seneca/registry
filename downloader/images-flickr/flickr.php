<?php
// Program that will download Flickr Images and Store them in your database based on Date
// By Anna Fatsevych
// June 11, 2015
// Uses phpFlickr Flickr API for PHP
$date = NULL;
foreach ($argv as $arg) {
    $e=explode("=",$arg);
    if(count($e)==2){
        $_GET[$e[0]]=$e[1];

    }else
        $_GET[]=$e[0];        
}
$date = $_GET[1];

require_once("phpFlickr.php");


// Database stats
$servername = "localhost";
$username = "anna";
$password = "password";
$dbname = "hashes";


$license1 = "CC-BY-NC-SA-2.0";
$license2 = "CC-BY-NC-2.0";
$license3 = "CC-BY-NC-ND-2.0";
$license4 = "CC-BY-2.0";
$license5 = "CC-BY-SA-2.0";
$license6 = "CC-BY-ND-2.0";
$license7 = "CC-Zero";
$errorcount=0;


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

$f = new phpFlickr("API KEY");

$photos = $f->photos_search(array("min_upload_date"=>$date, "max_upload_date"=>$date,"per_page"=>"500","license"=>"3", "extras"=>"url_o,owner_name, license"));

$counter = 0;
foreach ($photos['photo'] as $photo) {

//$owner = $f->photos_getInfo($photo['id']);
$filename = 'pics/'. $photo['owner'].$photo['id'];
$url = $photo['url_o'];

file_put_contents($filename, file_get_contents($url));

try{
    $hash = exec('./phash '.$filename);
    echo $hash;
}catch (Exception $e) {
    echo "\n\n HASH ERROR: ".$e."\n\n";
}
try{
    $mhash = exec('./phashmh '.$filename);
    echo $mhash;
}
catch (Exception $e){
    echo "\n\n MPH HASH EXCEPTION!!! ".$e."\n\n";
}

$license = $photo['license'];
switch ($photo['license']) {
    case "1":
        $license = $license1;
        break;
    case "2":
        $license = $license2;
        break;
    case "3":
        $license = $license3;
        break;
    case "4":
        $license = $license4;
        break;
    case "5":
        $license = $license5;
        break;
    case "6":
        $license = $license6;
        break;
    default:
        echo $license;
}

try{
    $str = 'convert '.$filename.' -resize 200 '.$filename;
    $thumb = exec($str);
    echo "\n\n Resizing ***  \n";
}
catch (Exception $e){
    echo "\n\n RESIZE ERROR!  ".$e."\n\n";
}

$counter++;
//echo "\n ** COUNTER: ".$counter;

$photograph = file_get_contents($filename);
$authorname = $photo['ownername'];
$title = $photo['title'];

 if(empty($hash) || empty($license) || empty($photograph) || empty($title)
            || empty($authorname) || empty($url) || empty($mhash)){

            // Write to File
   
        } /*else {
            try{
                // MYSql Insert Statement
            }
            catch (Exception $e){
                echo "\n\nDB ERROR!! ".$e;         
            }
                } */

}
?>