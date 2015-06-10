<?php
/* PHP Program to Parse Images and XML files found in Wiki Grab Image Dumps
   Uses wiki API to make a function call to verify if the URL exists and get updated information

   By: Anna Fatsevych
*/
    
$path = "";

$directory = NULL;
foreach ($argv as $arg) {
    $e=explode("=",$arg);
    if(count($e)==2){
        $_GET[$e[0]]=$e[1];

    }else
        $_GET[]=$e[0];        
}
$directory = $_GET[1];

// Database stats
$servername = "localhost";
$username = "anna";
$password = "password";
$dbname = "hashes";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// Open the file to read image names and xml from
if(!$directory){
    echo $directory;
    $myfile = fopen($directory."zz.txt", "r") or die("Unable to open file!");
} 
else{
    $myfile = fopen($directory."zz.txt", "r") or die("Unable to open file!");
}

if ($myfile) {
    while (($line = fgets($myfile)) !== false) {
        $counter++;
        $name = trim($line,"./");
        $name = trim($name);

        $image = trim($name,".xml");
        $photo = substr($name,0,-4);

        // For now, Select all image files
        $newstring = substr($photo, -3);
        $newstring = strtolower($newstring);
        $photo = addcslashes($photo, "()<>$&'\"");
        $filename = $directory.$photo;

        if((strcmp($newstring, 'jpg') == 0)||(strcmp($newstring,'png') == 0)){            
            // Hashes
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
            try{
                $bhash = exec('./blockhash '.$filename);
            }
            catch (Exception $e){
                echo "\n\n BLOCK HASH EXCEPTION!!! ".$e."\n\n";
            }

            // Resize the image to thumbnail after hashing
            try{
                $str = 'convert '.$filename.' -resize 200 '.$filename;
                $thumb = exec($str);
            }
            catch (Exception $e){
                echo "\nRESIZE ERROR\n";
            }

            try{
                $photograph = file_get_contents($directory.$photo);
                $photograph = addslashes($photograph); 
                $time_pre = microtime(true);
            } catch (Exception $e){
                echo "\n\n FILENAME ERROR: \n".$e."\n";

            }
            $encoded = mb_convert_encoding($filename, "UTF-8", mb_detect_encoding($filename));
            $xmlfile = $filename.'.xml';
            $fp = fopen($xmlfile, 'r');

            $xmldata = fread($fp, filesize($xmlfile));
            $xml = simplexml_load_string($xmldata);

            // Parse the xml
            $title = $xml->page->title;
            $title = str_replace(' ','_',$title);
            $title = addcslashes($title, "()<>$&'\"");

            $opts = array('http' =>
                array(
                    'user_agent' => 'MyBot/1.0 (http://www.mysite.com/)'
                )
            );
            $context = stream_context_create($opts);

            try{

                $url = 'http://tools.wmflabs.org/magnus-toolserver/commonsapi.php?image='.$title.'&versions&meta';
                $response = simplexml_load_string(file_get_contents($url, FALSE, $context));
                $str = (string)$response->file->author;
                $res = array();

                preg_match_all("/<a.*?href\s*=\s*['\"](.*?)['\"]/", $str, $res);

                $doc = new DOMDocument();
                $doc->loadHTML($response->file->author);
                $links = $doc->getElementsByTagName('a');

                //Extract author name from the html
                foreach ($links as $link){
                    echo $link->nodeValue;
                    echo $link->getAttribute('href'), '<br>';
                    $authorhtml = $link->getAttribute('title');
                }
                if(empty($hash) || empty($response->licenses->license->name) || empty($photograph) || empty($photo)
                    || empty($authorhtml) || empty($response->versions->version[0]->descriptionurl) || empty($mhash)){

                }else {
                    try{
                        // MYSql Insert Statement
                        $sql = "INSERT INTO IMG(phash,license,image, imagename, authorname, url, mhash) 
                                VALUES('$hash','{$response->licenses->license->name}','$photograph', '$photo', 
                                       '{$authorhtml}', '{$response->versions->version[0]->descriptionurl}','{$mhash}')";
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

                $time_post = microtime(true);
                $exec_time = $time_post - $time_pre;   
                echo "\n\n TIME: ".$exec_time."\n";

          } catch (Exception $e) {
            echo 'Caught exception: '. $filename."    ".$e."    \n";
        }
    }
}
    echo "CONTER: \n".$counter."\n";
    fclose($myfile);
} else {
    echo 'error opening the file';
} 
?>
