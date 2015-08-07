<?php
ini_set('precision', 20); 
$hashfile = fopen("hash.txt", 'w') or die("Unable to open errorfile file!");
$badhash = fopen("badhash.txt", 'w') or die("Unable to open errorfile file!");

$counter = 0;
$matches = 0;
$errors = 0;
    if(isset($_POST)){

      $hashes = explode(",",$_POST["hashes"]);
  
      foreach ($hashes as $hash) {
        echo "\n\nHASH ".$hash;

        $jhash = explode("+",$hash);

        
        // Hash the image using the original c++ phash
        $str = "./phashhex 2015-01-07_pics/".$jhash[1];
        echo $str;

        // Get the pHash value
        $phash = exec($str);
        echo "\n\n PHASH: ".$phash;
        echo json_encode($phash);

        // Get the hamming distance and display it
        $dist = exec("./hamminghex ".$phash." ".$jhash[6]);
          echo "\n\n DISTANCE: ".$dist;

          //$dist2 = exec("perl hamming.pl ".$phash." ".bindec($jhash[2]));
          //echo "\n\n DISTANCE PERL: ".$dist2;

          // Count the matches and errors
          if($dist <= 4){
            $matches++;
              
          }else{
            $errors++;
            // Write errors to the file
            fwrite($badhash,$jhash[5].",".$jhash[1].",".$phash.",".$dist."\n");
          }
          $counter++;
         
   
      fwrite($hashfile,$jhash[6].",".$jhash[1].",".$phash.",".$dist."\n");
      }
      fclose($hashfile);
      fclose($badhash);

    }

echo "\n\n TOTAL: ".$counter;
echo "\n\n MATCHES: ".$matches;
echo "\n\n ERRORS: ".$errors;
    return "This is a response";


?>