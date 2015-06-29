<?php

// Flickr API 
require_once("phpflickr-master/phpFlickr.php");

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

if (file_exists($date."-".$date2."_flickrdownload.txt")) {
  $myfile = fopen($date."-".$date2."_flickrdownload.txt", 'a') or die("Unable to open errorfile file!");
  echo "\n\nFILE EXISTS!";
  $interrupt = true;
   $f = fopen($date."-".$date2."_flickrdownload.txt", 'rb');
    $lines = 0;
    while (!feof($f)) {
        $lines += substr_count(fread($f, 8192), "\n");
    }
    fclose($f);
    echo "\n\nLINES: ".$lines;

    while ($lines >= 500){
      $startpage++;
      $lines=$lines-500;
      echo "\nLINES : ".$lines;
    }

    echo "\n\nINTER PAGE: ".$startpage;

} else {
  $myfile = fopen($date."-".$date2."_flickrdownload.txt", 'w') or die("Unable to open errorfile file!");
}
// Files
$errorfile = fopen($date."-".$date2."_flickrerrors.txt", "w") or die("Unable to open errorfile file!");

//Flickr File
$f = new phpFlickr("dd5266efb4a0e67238c32f8b8cfa2f92");
$dir = $date."-".$date2."_pics";
mkdir($dir);
$sqlite_timestamp = date(DATE_RFC3339);

while (strtotime($date) <= strtotime($date2)) {
  echo "$date\n";

  try{
  $beforet = ($date." 00:00:00");
  $beforet = new DateTime($beforet);
  $before =$beforet->format('Y-m-d H:i:sP');

  echo "\n\nBEFORE TIME: ".$beforet->format('Y-m-d H:i:sP');

  $aftert = ($date2." 07:00:00");
  $aftert = new DateTime($aftert);
  $after = $aftert->format('Y-m-d H:i:sP');

  $start = microtime(true);

  $photos = $f->photos_search(array("max_upload_date"=>$after,"min_upload_date"=>$before,"per_page"=>"500","license"=>"1,2,3,4,5,6,7","extras"=>"url_o,owner_name, license, date_upload"));
  echo "\nTOTAL: ".$photos['total'];
  echo "\nPAGES: ".$photos['pages'];
  $pages = $photos['pages'];
  //print_r($photos);

  for($page=$startpage; $page <= $pages; $page++){
    $counter = 0;
    echo "\nFETCHING FOR PAGE: ".$page;
    $photos = $f->photos_search(array("max_upload_date"=>$after,"min_upload_date"=>$before,"per_page"=>"500","license"=>"1,2,3,4,5,6,7","page"=>$page,"per_page"=>"500","extras"=>"url_o,owner_name, license, date_upload"));


    $i = 1;

    foreach ($photos['photo'] as $photo) {

      if($interrupt == false || ($interrupt == true && $lines < $i)){
        echo ($interrupt == true) ? "INTERRUPT " : "NO INTERRUPT";
        echo "\n LINES: ".$lines;
        echo "\n\n i: ".$i;

        $url = $photo['url_o'];
        $namefile = explode("/",$url);
        $filename = $dir."/".$namefile[4];
        $crediturl = "https://flickr.com/photos/".$photo['owner']."/".$photo['id'];

        echo "\n\n\n DATE UPLOADED!: ".$photo['dateupload']." \n";

        $epoch = $photo['dateupload']; 
        $dt = new DateTime("@$epoch");  // convert UNIX timestamp to PHP DateTime
        echo $dt->format('Y-m-d H:i:sP');
        echo $dt->format('Y-m-d H:i:sP');
        echo "\n\n  MIN DATE: :";

        echo $before;

        echo "\n\n  MAX DATE: :";
        echo $after;
          try{
            //file_put_contents($filename, file_get_contents($url));


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

            echo "\n\n LICENSE: ".$license;

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
              $photograph = file_get_contents($filename);
            }catch (Exception $e){
              echo "\n\n GET FILE ERROR: ".$e;
            }

            $string = str_replace(array("\n", "\r"), ' ', $string);

            $authorname = str_replace(array("\n", "\r"), ' ', $photo['ownername']);
            $title = (empty($photo['title'])) ? "No Title" : str_replace(array("\n", "\r"), ' ', $photo['title']);

          }catch (Exception $e){
            echo "\n\n DOWNLOAD ERROR: ".$e."\n\n";
          }

          //Check for empty fields
          if(empty($license) || empty($photograph) || empty($title)
            || empty($authorname) || empty($url) || empty($crediturl)){

            $errorname = (empty($photos))? "No RESPONSE" : "";
   
            $errorname = "page: ".$del.$page." date: ".$del.$date.$del." ".$counter;
            $errorname.= (empty($photograph)) ? " no PHOTO" : ""; 
            $errorname.= (empty($title)) ? " no TITLE" : ""; 
            $errorname.=(empty($url)) ? "NO URL" : $url;
            $errorname.="ID: ".$photo['id'];
       
            $errorname.= $del.date(DATE_RFC3339);
            fwrite($errorfile, $errorname);
            $errorcount++; 
        }else{
    
          $str = $license.$del.$title.$del.$authorname.$del.$url.$del.$sqlite_timestamp.$del.$namefile[4].$del.$filename.$del.$crediturl.$del.$epoch.$del.$dt->format('Y-m-d H:i:s')."\n";
          fwrite($myfile, $str);
      }
     
      $counter++;
      echo "\n ** COUNTER: ".$counter;
  }// close the if interrupt statement
  $i++;
    } // close foreach photo
  }


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
?>
