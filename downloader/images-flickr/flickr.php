<?php

// Flickr API 
require_once("phpflickr-master/phpFlickr.php");
//Flickr File
ini_set("memory_limit","600M");
date_default_timezone_set('Greenwich');

$date = NULL;
$date2 = NULL;


// Set the line or GET args as download date 
// or date range if second arg is present
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
  $date2 = $date;
}

// Set the types of licenses
$license1 = "CC-BY-NC-SA-2.0";
$license2 = "CC-BY-NC-2.0";
$license3 = "CC-BY-NC-ND-2.0";
$license4 = "CC-BY-2.0";
$license5 = "CC-BY-SA-2.0";
$license6 = "CC-BY-ND-2.0";
$license7 = "CC-Zero";

// Check if it is an interrupted download
$interrupt = false;
$startpage = 1;

$errorcount=0;
$errorname = "";
$del = '\',\'';

$duplicate = false;
$interrupinterval = 1;

// While in date range
while (strtotime($date) <= strtotime($date2)) {

  // If file exists, find the interval, page and place and continue
  if (file_exists($date."_flickrdownload.txt")) {

    $myfile = fopen($date."_flickrdownload.txt", 'a') or die("Unable to open errorfile file!");
    $lastline= read_last_line($date."_flickrdownload.txt");
    $interruptedfile = (explode($del,$lastline));
    print_r($interruptedfile);
    $interruptdate = $interruptedfile[10];
    $interrupinterval = $interruptedfile[11];
    $startpage = $interruptedfile[12];
    $interrupt = true;
    $f = fopen($date."_flickrdownload.txt", 'rb');
    
    $lines = $interruptedfile[13];
    echo "\n\nINTER PAGE: ".$startpage;
    echo "\n\nINTERRUPUTED INTERVAL: ".$interrupinterval;

  } else {
    $myfile = fopen($date."_flickrdownload.txt", 'w') or die("Unable to open errorfile file!");
  }
  if (file_exists($date."_flickrerrors.txt")) {
    $errorfile = fopen($date."_flickrerrors.txt", "a") or die("Unable to open errorfile file!");
  } else {
    $errorfile = fopen($date."_flickrerrors.txt", "w") or die("Unable to open errorfile file!");
  }
  if (file_exists($date."_duplicates.txt")) {
    $duplicates = fopen($date."_duplicates.txt", "a") or die("Unable to open errorfile file!");
  } else {
    $duplicates = fopen($date."_duplicates.txt", "w") or die("Unable to open errorfile file!");
  }

  // Create directory for downloaded images 
  // $date _pics "2015-01-10_pics"  
  $dir = $date."_pics";
  mkdir($dir);
  $beforet = new DateTime($date);

  try{
    // 48 time intervals of 30 minutes to maximize downloads
    for($x = $interrupinterval; $x <= 48; $x++){
      $bef = ($date." 00:00:00");
      $beforet = new DateTime($bef);

      if($x == 48){
        $interval = new DateInterval('PT29M59S');
      } else{
        $interval = new DateInterval('PT30M');
      }
      $beforeinterval = new DateInterval('PT'.($x-1)*(30).'M');
      $beforet->add($beforeinterval);
      $before =$beforet->format('Y-m-d H:i:sP');

      $aftert = new DateTime($before);
      $aftert->add($interval);
      $after = $aftert->format('Y-m-d H:i:sP');

      $start = microtime(true);

      // Set the interval and startpage
      if (($interrupt == false )|| (($interrupt == true) && (strcmp($date, $interruptdate) === 0) && ($x == $interrupinterval))){
        if($interrupt === false){
          $starpage = 1;
        } 
        $f = new phpFlickr("dd5266efb4a0e67238c32f8b8cfa2f92");

        // Get the total photos for the date interval
        $photos = $f->photos_search(array("max_upload_date"=>$after,"min_upload_date"=>$before,"per_page"=>"500","license"=>"1,2,3,4,5,6,7","extras"=>"url_o,owner_name, license, date_upload"));
        $pages = $photos['pages'];

        //echo "\nTOTAL ************************** : ".$photos['total'];
        //echo "\nPAGES ************************** : ".$photos['pages'];
        //echo "\n\nFOR DATE ********************* : ".$date;
        //echo "\n\n STARTPAGE ".$startpage;

        // For each page, fetch the photos from flickr
        for($page=$startpage; $page <= $pages; $page++){
 
          $counter = 0;
          $photos = $f->photos_search(array("max_upload_date"=>$after,"min_upload_date"=>$before,"per_page"=>"500","license"=>"1,2,3,4,5,6,7","page"=>$page,"extras"=>"url_o,owner_name, license, date_upload"));

          $i = 1;

          // Download each fetched photo, or if interrupted, find the place, then download
          foreach ($photos['photo'] as $photo) {
            if($page > $startpage){
              $interrupt = false;
            }

            $duplicate = false;

            if($interrupt === false || ($interrupt === true && $lines < $i)){

              $url = $photo['url_o'];
              $namefile = explode("/",$url);
              $filename = $dir."/".$namefile[4];
              $crediturl = "https://flickr.com/photos/".$photo['owner']."/".$photo['id'];
              $epoch = $photo['dateupload']; 
              $dt = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime

              try{

                if(file_exists($filename) && ($lines + 1) != $i){
                  echo "\n\n DUPLICATE\n";
                  $duplicate = true;
                }else{ 
                  // file doesn't exits
                  $duplicate = false;
                  if(!empty($lines)) {echo "\n\n LINES ".$lines;}
                  echo "\n\n i ".$i;

                  // Download the file with cUrl
                  $ch = curl_init($url);
                  curl_setopt($ch, CURLOPT_HEADER, 0);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                  $rawdata=curl_exec ($ch);
                  curl_close ($ch);
                  $fp = fopen($filename,'w');
                  fwrite($fp, $rawdata); 
                  fclose($fp);

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
                    case "7":
                      $license = $license7;
                      break;
                    default:
                      echo $license;
                  } // close license switch

                  try{
                    $photograph = file_get_contents($filename);
                  }catch (Exception $e){
                    echo "\n\n GET FILE ERROR: ".$e;
                  }
                  $authorname = str_replace(array("\n", "\r"), ' ', $photo['ownername']);
                  $title = (empty($photo['title'])) ? "No Title" : str_replace(array("\n", "\r"), ' ', $photo['title']);
                } // close else-if file exists
              }catch (Exception $e){
                echo "\n\n DOWNLOAD ERROR: ".$e."\n\n";
              }

              $sqlite_timestamp = date(DATE_RFC3339);

              //Check for empty fields
              if(empty($license) || empty($photograph) || empty($title)|| empty($authorname) || empty($url) || empty($crediturl) || ($duplicate === true)){

                // If duplicate write to duplicate file
                if($duplicate === true){
                  $duperror = $namefile[4].$del.$filename.$del.$crediturl.$del.$epoch.$del.$dt->format('Y-m-d H:i:s').$del.$date.$del.$x.$del.$page.$del.$i.$before.$del.$after."\n";
                  fwrite($duplicates, $errorname);
                }else{
                  // If not duplicate write to the error file
                  $errorname = (empty($photos))? "No RESPONSE\n" : "";
                  $errorname = $license.$del.$title.$del.$authorname.$del.$url.$del.$sqlite_timestamp.$del.$namefile[4].$del.$filename.$del.$crediturl.$del.$epoch.$del.$dt->format('Y-m-d H:i:s').$del.$date.$del.$x.$del.$page.$del.$i.$del;
                  $errorname.= (empty($photograph)) ? " no PHOTO" : ""; 
                  $errorname.= (empty($title)) ? " no TITLE" : ""; 
                  $errorname.=(empty($url)) ? "NO URL" : "";
                  $errorname.="\n";
                  fwrite($errorfile, $errorname);
                } // close if-else duplicates
              $errorcount++; 
              }else{
                // If not empty -- write to the downloaded file
                $str = $license.$del.$title.$del.$authorname.$del.$url.$del.$sqlite_timestamp.$del.$namefile[4].$del.$filename.$del.$crediturl.$del.$epoch.$del.$dt->format('Y-m-d H:i:s').$del.$date.$del.$x.$del.$page.$del.$i."\n";
                fwrite($myfile, $str);
                $interrupt = false;
              } // close if-else no empty

              $counter = $i; 
              echo "\n ** COUNTER: ".$counter;    
              $counter++;
            } // close if interrupt
            $i++;
          } // close foreach photo
        } // close for each page
      } // close if interrupt
    } // close for interval
  } catch (Exception $e){
    echo "\n Connection error\n";
  } // close try and catch
  $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
}// close while loop

echo "\n\nERRORS: ".$errorcount;
$time_elapsed_secs = microtime(true) - $start;
echo "\n\nTIME TAKEN:  ".$time_elapsed_secs;
echo "\n\n";

// Close the files
fclose($myfile);
fclose($errorfile);

// Function to read last line of a file
function read_last_line ($file_path){
  $line = '';
  $f = fopen($file_path, 'r');
  $cursor = -1;

  fseek($f, $cursor, SEEK_END);
  $char = fgetc($f);
 
  //Trim trailing newline chars of the file
  while ($char === "\n" || $char === "\r") {
    fseek($f, $cursor--, SEEK_END);
    $char = fgetc($f);
  }
  //Read until the start of file or first newline char
  while ($char !== false && $char !== "\n" && $char !== "\r") {
 
    $line = $char . $line;
    fseek($f, $cursor--, SEEK_END);
    $char = fgetc($f);
  }
  return $line;
} // close read_last_line function
?>
