<?php
ini_set('precision', 20); 
$hashfile = fopen("hash.txt", 'w') or die("Unable to open errorfile file!");
$badhash = fopen("badhash.txt", 'w') or die("Unable to open errorfile file!");
    //move_uploaded_file(filename, destination)
$counter = 0;
$matches = 0;
$errors = 0;
    if(isset($_POST)){
 
      $hashes = explode(",",$_POST["hashes"]);
  
  
      foreach ($hashes as $hash) {
        echo "\n\nHASH ".$hash;

        $jhash = explode("+",$hash);
        echo "\n\n\n BIN: ".bindec($jhash[2]);
        echo "\n\n IMAGE: ".$jhash[1];
        
        $str = "./phash 2015-03-20-2015-03-20_pics/".$jhash[1];
        echo $str;

        // Get the pHash value
        $phash = exec($str);
        echo "\n\n PHASH: ".$phash;
       echo json_encode($phash);
        //$phashfile = fopen("phash.txt", 'w') or die("Unable to open errorfile file!");
        //fwrite($phashfile,$phash."\n");
        //$jhashfile = fopen("jhash.txt", 'w') or die("Unable to open errorfile file!");
        //fwrite($jhashfile,bindec($jhash[2])."\n");
        //fclose($phashfile);
        //fclose($jhashfile);
        $dist = exec("perl hamming.pl ".$phash." ".bindec($jhash[2]));
          echo "\n\n DISTANCE: ".$dist;
          if($dist <= 4){
            $matches++;
              
          }else{
            $errors++;
            fwrite($badhash,bindec($jhash[2]).",".$jhash[1].",".$phash.",".$dist."\n");
          }
          $counter++;
         
   
      fwrite($hashfile,bindec($jhash[2]).",".$jhash[1].",".$phash.",".$dist."\n");
      }
      fclose($hashfile);
      fclose($badhash);

    }

    //print_r( $_FILES );
echo "\n\n TOTAL: ".$counter;
echo "\n\n MATCHES: ".$matches;
echo "\n\n ERRORS: ".$errors;
    return "This is a response";


?>
