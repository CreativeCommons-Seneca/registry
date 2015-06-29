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
$username = "root";
$password = "";
$dbname = "hashes";


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
        $delim = '\',\'';

        $dir = str_replace("flickrdownload.txt", "pics", $filename);
        $imageinfo = explode($delim,$line);
        $license = $imageinfo[0];
        $title = $imageinfo[1];
        $author = $imageinfo[2];
        $imageurl = $imageinfo[3];
        $imagefile = $imageinfo[6];
        $name = $imageinfo[5];
        $url = $imageinfo[7];
        $uploaddateunix = $imageinfo[8];
        $uploaddate = $imageinfo[9];
        print_r($imageinfo);




        
        
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
                $str = 'convert '.$imagefile.' -resize 200 '.$imagefile;
                $thumb = exec($str);
            }
            catch (Exception $e){
                echo "\nRESIZE ERROR\n".$imagefile;

            }

            $hash = (empty($hash)) ? 14620491339638543539 : $hash;
            $mhash = (empty($mhash)) ? "0c8e470ba1c0e874792da25f0561e1b9904d65c4f26d6ed316070a6ccb8a6466e9b232832d88eda61f2e230ccbc534ae5c5c1fc2f24964e2f8c29c8bc71e5b288fc7e3f1e8fc7e47" : $mhash;
       
      if(empty($hash) || empty($imageinfo) || empty($mhash)){
        echo "\n\n EMPTY!! \n";

                }else {
                    try{
                      $author = mysqli_real_escape_string($conn, $author);
                      $title = mysqli_real_escape_string($conn, $title);

                        // MYSql Insert Statement
                        $sql = "INSERT INTO IMG(phash,mhash,name,title, directory,author, license, url, imageurl, source, dateuploaded, dateuploadu) 
                                VALUES('$hash','$mhash','$name', '$title','$imagefile', '$author','$license','$url', '$imageurl', 'Flickr', '$uploaddate', '$uploaddateunix')";
                        if ($conn->query($sql) === TRUE) {
                            echo "New records created successfully";
                        } else {
                            echo "Error: <br>" . $conn->error;
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
