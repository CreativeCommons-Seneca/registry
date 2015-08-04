<?php
// Flickr API 
require_once("phpflickr-master/phpFlickr.php");
$filename = NULL;
$database = NULL;
$error = false;

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
$username = "ccommons";
$password = "CC@Seneca1";
$dbname = "hashes";


$myfile = fopen($filename, "r") or die("Unable to open file!");
$errorfile = fopen("readerror.txt", "w") or die("Unable to open file!");
$counterinserted = 0;
$counterduplicated = 0;
$comment = "";

// Create DB connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
} 

$f = new phpFlickr("dd5266efb4a0e67238c32f8b8cfa2f92");
if ($myfile) {
	while (($line = fgets($myfile)) !== false) {
		$comment = " ";
		$error = false;
		$message = "";
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
		$extension = explode('.',$imagefile);
		$ext = $extension[1];
		mkdir($dir);
		echo "\n\n DIR: ".$dir;
		echo "\n\n EXT: ".$ext;
		$photo = file_get_contents($imagefile);
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

				if(empty($photo)){
					$dlAttempt++;
				} else {
					$dlAttempt=5;
				}
	
		}  
		
		} // close else

		$sql = "select * from hashes.IMG where name='{$name}';";
		$resulttest = $conn->query($sql);

		if (!mysqli_query($conn,$sql)) {
			die('Error: ' . mysqli_error($conn));
		}
		$rows = $resulttest->num_rows;
		if ($rows > 0) {
			echo "\n\n\nFILE EXISTS IN THE DATABASE!!! ";
			$error = true;
			$message = "Duplicate";
			$counterduplicated++;
		}else{

			if((strcasecmp("jpg",$ext) != 0) && (strcasecmp("jpeg",$ext) != 0)){
				echo "\n\n EXTENSION: ".$ext;

				echo "\n\n : ".strcasecmp("jpg",$ext);
					echo "\n\n : ".strcasecmp("jpeg",$ext);


				try{

					$exstring = 'convert '.$imagefile." ".$extension[0].".jpg";
					echo "\n\n EXEC STRING: ".$exstring;
				  $convert = exec($exstring);
				  $comment = "Converted from ".$ext;
				  $imagefile = $extension[0].".jpg";

				} catch (Exception $e){
					echo "\n\nCould not Convert ".$e;
				}
	    } 			
   
			try{
				$hash = exec('./phash '.$imagefile);
				if(strcmp($hash,"ph_dct_imageash returned < 0") === true){
					echo "\npHash error";
					$error = true;
					$message = "pHash Error";
					$hash = "";
				}else{
					try{
						$str = 'convert '.escapeshellarg($imagefile).' -resize 200 '.escapeshellarg($dir."/".$name);
						$thumb = exec($str);
					}
					catch (Exception $e){
						echo "\nRESIZE ERROR\n".$imagefile;
					}
				}
			}catch (Exception $e) {
				echo "\n\n HASH ERROR: ".$e."\n\n";
			}
			try{
				//$mhash = exec('./phashmh '.$imagefile);
				$mhash = "No Mhash";
			}
			catch (Exception $e){
				echo "\n\n MPH HASH EXCEPTION!!! ".$e."\n\n";
			}
			if(empty($hash) || empty($imageinfo) || empty($mhash) || empty($photo)){

				$message .= empty($hash)? "Empty Phash" : "";
				$message .= empty($imageinfo)? "Info" : "";
				$message .= empty($mhash)? "mhash" : "";
				$message .= empty($photo)? "noFileContents" : "";

				echo "\n\n EMPTY!! \n";
				$error = true;
				$message.= "something is Empty";
			}else {
				try{
          //If that file doesn't exist, insert into database.
					$author = mysqli_real_escape_string($conn, $author);
					$encoded = mb_convert_encoding($title, "UTF-8", mb_detect_encoding($title));
					$title = mysqli_real_escape_string($conn, $title);

                        // MYSql Insert Statement
					$sql = "INSERT INTO IMG(phash,mhash,name,title, directory,author, license, url, imageurl, source, dateuploaded, dateuploadu, comments) 
					VALUES('$hash','$mhash','$name', '$title','$imagefile', '$author','$license','$url', '$imageurl', 'Flickr', '$uploaddate', '$uploaddateunix','$comment')";
					if ($conn->query($sql) === TRUE) {
						echo "New records created successfully";
						$counterinserted++;
					} else {
						echo "Error: <br>" . $conn->error;
						$error = true;
						$message = "Not Inserted";
					}
				}

				catch (Exception $e){
					echo "\n\nDB ERROR!! ".$e;

				}
			}
    if ($error == true){
    	$errormsg = $url.$delim.$imageurl.$delim.$name.$delim.$message."\n";
    	fwrite($errorfile,$errormsg);
    	$message = "";
    	$error = false;

    }
		} // close if not duplicate
 }// close while
 echo "COUNTER: \n".$counter."\n";
 echo "\n\nCOUNTE INSERTED: ".$counterinserted;
 echo "\n\nCOUNTER DUPLICATED: ".$counterduplicated;
 fclose($myfile);
 fclose($errorfile);
} else {
	echo 'error opening the file';
} 
