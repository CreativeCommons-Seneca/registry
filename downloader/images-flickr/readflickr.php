<?php
// Flickr API 
require_once("phpflickr-master/phpFlickr.php");
$filename = NULL;
$database = NULL;

foreach ($argv as $arg) {
  $e=explode("=",$arg);
  if(count($e)==2){
    $_GET[$e[0]]=$e[1];
  }else
  $_GET[]=$e[0];        
}

// Get filename from commandline
$filename = $_GET[1];


// Database stats
$counter = 0;
$servername = "localhost";
$username = "anna";
$password = "password";
$dbname = "hashes";


$myfile = fopen($filename, "r") or die("Unable to open file!");

// Create DB connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 

$f = new phpFlickr("dd5266efb4a0e67238c32f8b8cfa2f92");
if ($myfile) {
  while (($line = fgets($myfile)) !== false) {
    $counter++;
    $delim = '\',\'';
    $imageinfo = explode($delim,$line);
    print_r($imageinfo);


    //Get file contents
    $license = $imageinfo[0];
    $title = $imageinfo[1];
    $author = $imageinfo[2];
    $imageurl = $imageinfo[3];
    $imagefile = $imageinfo[6];
    $name = $imageinfo[5];
    $url = $imageinfo[7];
    $uploaddateunix = $imageinfo[8];
    $uploaddate = $imageinfo[9];
    $dir = explode('/',$imagefile);
    $dir = $dir[0].'/thumbs';
    mkdir($dir);
    echo "\n\n DIR: ".$dir;

    if(strpos($filename, 'error') === false){


    }else{

      // If it is error file, download the image again.
      $dlAttempt = 0;
      while ($dlAttempt < 5) {
        echo "\n\n DL ATTEMPTS: ".$dlAttempt;
        $ch = curl_init($imageurl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $rawdata=curl_exec ($ch);
        curl_close ($ch);

        $fp = fopen($imagefile,'w');
        fwrite($fp, $rawdata); 
        fclose($fp);

        if(empty(file_get_contents($imagefile))){
          $dlAttempt++;
        } else {
          $dlAttempt=5;
        }

      }  

    }

    try{
      $hash = exec('./phash '.$imagefile);
    }catch (Exception $e) {
      echo "\n\n HASH ERROR: ".$e."\n\n";
    }
    try{
      $mhash = exec('./phashmh '.$imagefile);
    }
    catch (Exception $e){
      echo "\n\n MPH HASH EXCEPTION!!! ".$e."\n\n";
    }

            // Resize the image to thumbnail after hashing
    try{
      $str = 'convert '.escapeshellarg($imagefile).' -resize 200 '.escapeshellarg($dir."/".$name);
      $thumb = exec($str);
    }
    catch (Exception $e){
      echo "\nRESIZE ERROR\n".$imagefile;

    }
    if(empty($hash) || empty($imageinfo) || empty($mhash) || empty(file_get_contents($imagefile))){
      echo "\n\n EMPTY!! \n";

    }else {

      try{
        // See if that name exists.
        $sql = "select * from hashes.IMG33 where name='{$name}';";
        $resulttest = $conn->query($sql);

        if (!mysqli_query($conn,$sql)) {
          die('Error: ' . mysqli_error($conn));
        }
        $rows = $resulttest->num_rows;
        if ($rows > 0) {
          echo "\n\n\nFILE EXISTS IN THE DATABASE!!! ";
        }else{

          //If that file doesn't exist, insert into database.
          $author = mysqli_real_escape_string($conn, $author);
          $title = mysqli_real_escape_string($conn, $title);

                        // MYSql Insert Statement
          $sql = "INSERT INTO IMG33(phash,mhash,name,title, directory,author, license, url, imageurl, source, dateuploaded, dateuploadu) 
          VALUES('$hash','$mhash','$name', '$title','$imagefile', '$author','$license','$url', '$imageurl', 'Flickr', '$uploaddate', '$uploaddateunix')";
          if ($conn->query($sql) === TRUE) {
            echo "New records created successfully";
          } else {
            echo "Error: <br>" . $conn->error;
          }
        }
      }
      catch (Exception $e){
        echo "\n\nDB ERROR!! ".$e;

      }
    }

  }
  echo "COUNTER: \n".$counter."\n";
  fclose($myfile);
} else {
  echo 'error opening the file';
} 
