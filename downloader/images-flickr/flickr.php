<?php

// Flickr PHP Image Downloader
// By Anna Fatsevych uses phpFlickr.php
// Downloads Commons images for the range of provided dates. 
require_once("phpFlickr.php");

$date = NULL;

foreach ($argv as $arg) {
  $e=explode("=",$arg);
  if(count($e)==2){
    $_GET[$e[0]]=$e[1];
    $_GET[$e[1]]=$e[2];
  }else
    $_GET[]=$e[0];        
  }

$date = $_GET[1];
$date2 = $_GET[2];

if(empty($date2)){
  echo "\nNO DATE 2\n";
  $date2 = $date;
}


//Create SQLite Database
class MyDB extends SQLite3
{
  function __construct($date, $date2)
  {
   $this->open($date."-".$date2.'_flickrdatabase.db');
 }
}

$license1 = "CC-BY-NC-SA-2.0";
$license2 = "CC-BY-NC-2.0";
$license3 = "CC-BY-NC-ND-2.0";
$license4 = "CC-BY-2.0";
$license5 = "CC-BY-SA-2.0";
$license6 = "CC-BY-ND-2.0";
$license7 = "CC-Zero";

$errorcount=0;
$errorname = "";
// Files
$myfile = fopen($date."-".$date2."_flickrdownload.txt", "w") or die("Unable to open file!");
$errorfile = fopen($date."-".$date2."_flickrerrors.txt", "w") or die("Unable to open errorfile file!");

//Flickr File
$f = new phpFlickr("dd5266efb4a0e67238c32f8b8cfa2f92");
$dir = $date."-".$date2."_pics";
mkdir($dir);


while (strtotime($date) <= strtotime($date2)) {
  echo "$date\n";

  try{
    $photos = $f->photos_search(array("min_upload_date"=>$date,"max_upload_date"=>$date,"per_page"=>"500","license"=>"1,2,3,4,5,6", "extras"=>"url_o,owner_name, license"));

    echo "\nTOTAL: ".$photos['total'];
    echo "\nPAGES: ".$photos['pages'];
    $pages = $photos['pages'];
    

    for($page=1; $page <= $pages; $page++){
      $counter = 0;
      echo "\nFETCHING FOR PAGE: ".$page;
      $photos = $f->photos_search(array("min_upload_date"=>$date, "max_upload_date"=>$date,"page"=>$page,"per_page"=>"500","license"=>"1,2,3,4,5,6", "extras"=>"url_o,owner_name, license"));
      
      foreach ($photos['photo'] as $photo) {
        $filename = $dir."/".$photo['owner'].$photo['id'];
        $url = $photo['url_o'];

        try{
          file_put_contents($filename, file_get_contents($url));
        }catch (Exception $e){
          echo "\n\n DOWNLOAD ERROR: ".$e."\n\n";
        }
        try{
          $str = 'convert '.$filename.' -resize 200 '.$filename;
          $thumb = exec($str);
        }
        catch (Exception $e){
          echo "\n\n RESIZE ERROR!  ".$e."\n\n";
        }
        try{
          $hash = exec('./phash '.$filename);

        }catch (Exception $e) {
          echo "\n\n HASH ERROR: ".$e."\n\n";
        }
        try{
          $mhash = exec('./phashmh '.$filename);
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
        $photograph = file_get_contents($filename);
        $authorname = $photo['ownername'];
        $title = $photo['title'];

        if(empty($hash) || empty($license) || empty($photograph) || empty($title)
          || empty($authorname) || empty($url) || empty($mhash)){

          $errorname = (empty($photos))? "No RESPONSE" : "";
 
          $errorname = "page: ".$page." date: ".$date."\n";
          fwrite($errorfile, $errorname);
          $errorcount++;    
      }else{

        $db = new MyDB($date, $date2);
        if(!$db){
          echo $db->lastErrorMsg();
        } else {
          echo "Opened database successfully\n";
        }

        $sql =<<<EOF
        CREATE TABLE IF NOT EXISTS IMG (
          id INTEGER PRIMARY KEY, 
          phash VARCHAR(22) NOT NULL,
          license VARCHAR(45) NOT NULL, 
          image BLOB,
          imagename VARCHAR(1024),
          url VARCHAR(1024),
          local VARCHAR(1024),
          dateuploaded DATE,
          timestamp DATE,
          mhash VARCHAR(75));
EOF;

        $ret = $db->exec($sql);
        if(!$ret){
          echo $db->lastErrorMsg();
        } else {
          echo "Table created successfully\n";
        }

                // Prepare INSERT statement to SQLite3 file db
        $insert = "INSERT INTO IMG (phash, license, image,imagename,  url, mhash, local, dateuploaded, timestamp) 
        VALUES (:phash, :license, :photograph, :imagename, :url, :mhash, :filename, :dateuploaded, :timestamp)";

        $stmt = $db->prepare($insert);
        $sqlite_timestamp = date(DATE_RFC3339);

        // Bind parameters to statement variables
        $stmt->bindParam(':phash', $hash);
        $stmt->bindParam(':license', $license);
        $stmt->bindParam(':imagename', $title);
        $stmt->bindParam(':photograph', $photograph);
        $stmt->bindParam(':url', $url);
        $stmt->bindParam(':mhash', $mhash);
        $stmt->bindParam(':filename', $filename);
        $stmt->bindParam(':dateuploaded', $date);
        $stmt->bindParam(':timestamp', $sqlite_timestamp);

        // Execute statement
        $stmt->execute();
    }

    $counter++;
    } // close foreach photo
  }

  } catch (Exception $e){
    echo "\n Connection error\n";
  }
  $db->close();
  fclose($myfile);
  fclose($errorfile);
  $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
 }
?>