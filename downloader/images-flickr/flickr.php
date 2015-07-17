<?php

// Flickr API 
require_once("phpflickr-master/phpFlickr.php");
//Flickr File
ini_set("memory_limit","600M");
date_default_timezone_set('Greenwich');

$date = NULL;
$date2 = NULL;

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

$license1 = "CC-BY-NC-SA-2.0";
$license2 = "CC-BY-NC-2.0";
$license3 = "CC-BY-NC-ND-2.0";
$license4 = "CC-BY-2.0";
$license5 = "CC-BY-SA-2.0";
$license6 = "CC-BY-ND-2.0";
$license7 = "CC-Zero";

$interrupt = false;
$startpage = 1;

$errorcount=0;
$errorname = "";
$del = '\',\'';

$duplicate = false;
$interrupinterval = 1;




while (strtotime($date) <= strtotime($date2)) {
  echo "$date\n";

  if (file_exists($date."_flickrdownload.txt")) {
  $myfile = fopen($date."_flickrdownload.txt", 'a') or die("Unable to open errorfile file!");
  echo "\n\nFILE EXISTS!";

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



$dir = $date."_pics";
mkdir($dir);
$beforet = new DateTime($date);
  try{

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

      echo "\n\nBEFORE TIME: ".$before;

      

      $aftert = new DateTime($before);
      $aftert->add($interval);
      $after = $aftert->format('Y-m-d H:i:sP');

      echo "\n\nAFTER  TIME: ".$after;
      echo "\n\n INTERVAL ".$x;

      $start = microtime(true);
      if (($interrupt == false )|| (($interrupt == true) && (strcmp($date, $interruptdate) === 0) && ($x == $interrupinterval))){
      if($interrupt === false){
        $starpage = 1;
      } 
  
      $f = new phpFlickr("dd5266efb4a0e67238c32f8b8cfa2f92");
      $photos = $f->photos_search(array("max_upload_date"=>$after,"min_upload_date"=>$before,"per_page"=>"500","license"=>"1,2,3,4,5,6,7","extras"=>"url_o,owner_name, license, date_upload"));
      echo "\nTOTAL ************************** : ".$photos['total'];
      echo "\nPAGES ************************** : ".$photos['pages'];
      $pages = $photos['pages'];
      echo "\n\nFOR DATE ********************* : ".$date;
      //print_r($photos);
      echo "\n\n STARTPAGE ".$startpage;



      for($page=$startpage; $page <= $pages; $page++){

        echo "\n\n STARTPAGE ".$startpage;
        echo "\n\n CURRENT PAGE ".$page;
        echo "\n\n TOTAL PAGES ".$pages;
        $counter = 0;
        echo "\nFETCHING FOR PAGE: ".$page;
        echo "\nBEFORE: ".$before;
        echo "\n\nAFTERL ".$after;
        $photos = $f->photos_search(array("max_upload_date"=>$after,"min_upload_date"=>$before,"per_page"=>"500","license"=>"1,2,3,4,5,6,7","page"=>$page,"extras"=>"url_o,owner_name, license, date_upload"));


        $i = 1;

        foreach ($photos['photo'] as $photo) {
          if($page > $startpage){
            $interrupt = false;
          }

          if($interrupt === true){
            echo "\n\n************ INTERRUPT ***************\n";
          }
          echo "\n\n PAGE ".$page; 
          echo "\n\n OF INTERVAL: ".$x;
          $duplicate = false;

          if($interrupt === false || ($interrupt === true && $lines < $i)){
      

            $url = $photo['url_o'];
            $namefile = explode("/",$url);
            $filename = $dir."/".$namefile[4];
            $crediturl = "https://flickr.com/photos/".$photo['owner']."/".$photo['id'];


            $epoch = $photo['dateupload']; 
            $dt = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
            echo "\n\n DOWNLOADED: ".$dt->format('Y-m-d H:i:sP');
     
              try{
                //file_put_contents($filename, file_get_contents($url));

                

                  if(file_exists($filename) && ($lines + 1) != $i){
                    echo "\n\n DUPLICATE\n";
                    $duplicate = true;
                  } else{
                    $duplicate = false;
                    if(!empty($lines)) {echo "\n\n LINES ".$lines;}
                    echo "\n\n i ".$i;
                    echo "\n\n FILE DOESNT EXIST!!";

                  
                  $ch = curl_init($url);
                  curl_setopt($ch, CURLOPT_HEADER, 0);
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
                  $rawdata=curl_exec ($ch);
                  curl_close ($ch);

                  $fp = fopen($filename,'w');
                  fwrite($fp, $rawdata); 
                  fclose($fp);

               

            
               
               //exec("wget ".$url." -O ".$filename);

                $license = $photo['license'];

                //echo "\n\n LICENSE: ".$license;

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
                }

                try{
                  $photograph = file_get_contents($filename);
                }catch (Exception $e){
                  echo "\n\n GET FILE ERROR: ".$e;
                }



                $authorname = str_replace(array("\n", "\r"), ' ', $photo['ownername']);
                $title = (empty($photo['title'])) ? "No Title" : str_replace(array("\n", "\r"), ' ', $photo['title']);
}
              }catch (Exception $e){
                echo "\n\n DOWNLOAD ERROR: ".$e."\n\n";
              }
            
              $sqlite_timestamp = date(DATE_RFC3339);

              //Check for empty fields
              if(empty($license) || empty($photograph) || empty($title)
                || empty($authorname) || empty($url) || empty($crediturl) || ($duplicate === true)){

                echo "\n\n ERROR!!!";
                if($duplicate === true){
                echo "\n\n DUBBB *****************************";
                $duperror = $namefile[4].$del.$filename.$del.$crediturl.$del.$epoch.$del.$dt->format('Y-m-d H:i:s').$del.$date.$del.$x.$del.$page.$del.$i.$before.$del.$after."\n";
                fwrite($duplicates, $errorname);
              }else{

                $errorname = (empty($photos))? "No RESPONSE\n" : "";
       
                $errorname = $license.$del.$title.$del.$authorname.$del.$url.$del.$sqlite_timestamp.$del.$namefile[4].$del.$filename.$del.$crediturl.$del.$epoch.$del.$dt->format('Y-m-d H:i:s').$del.$date.$del.$x.$del.$page.$del.$i.$del;
                $errorname.= (empty($photograph)) ? " no PHOTO" : ""; 
                $errorname.= (empty($title)) ? " no TITLE" : ""; 
                $errorname.=(empty($url)) ? "NO URL" : "";
                $errorname.="\n";
              
                fwrite($errorfile, $errorname);
              }
                $errorcount++; 

            }else{
        
              $str = $license.$del.$title.$del.$authorname.$del.$url.$del.$sqlite_timestamp.$del.$namefile[4].$del.$filename.$del.$crediturl.$del.$epoch.$del.$dt->format('Y-m-d H:i:s').$del.$date.$del.$x.$del.$page.$del.$i."\n";
              fwrite($myfile, $str);
              $interrupt = false;
          }
          $counter = $i; 
          echo "\n ** COUNTER: ".$counter;    
          $counter++;
          
      }// close the if interrupt statement

      $i++;
        } // close foreach photo
      }
      //$beforet = $aftert;
    } // close for loop
} // close interrupt 
  } catch (Exception $e){
    echo "\n Connection error\n";
  }

  $date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
 }

  echo "\n\nERRORS: ".$errorcount;
  $time_elapsed_secs = microtime(true) - $start;
  echo "\n\nTIME TAKEN:  ".$time_elapsed_secs;
  echo "\n\n";


  fclose($myfile);
  fclose($errorfile);

  function read_last_line ($file_path){



$line = '';

$f = fopen($file_path, 'r');
$cursor = -1;

fseek($f, $cursor, SEEK_END);
$char = fgetc($f);

/**
* Trim trailing newline chars of the file
*/
while ($char === "\n" || $char === "\r") {
    fseek($f, $cursor--, SEEK_END);
    $char = fgetc($f);
}

/**
* Read until the start of file or first newline char
*/
while ($char !== false && $char !== "\n" && $char !== "\r") {
    /**
     * 179pend the new char
     */
    $line = $char . $line;
    fseek($f, $cursor--, SEEK_END);
    $char = fgetc($f);
}

return $line;
}
?>
