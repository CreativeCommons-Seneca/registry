<?php
session_start(); 
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    if(isset($_POST["url"])){
        $url = $_POST["url"];

    }
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if(isset($_POST["url"])){
        $url = $_POST['url'];
        echo "\n\nURL:  ".$url."\n\n";
    }
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}
// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}
/*
// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
*/
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "JPG" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
    unset($_SESSION['results']);
    header('Location: index.php');

// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        //session_start();
        //$_SESSION['results'] = '2,3,5';
        //header('Location: index.php');
        //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.\n\n POSTS!! *************";
        //print_r($_POST);

        //echo "\n\n       ". "./mhexe/mhsearcher mhexe/tree.mh /var/www/html/cc/uploads/".$_FILES["fileToUpload"]["name"]." 0.4";
        $results = exec("./mhexe/mhsearcher mhexe/tree.mh /var/www/html/cc/uploads/".$_FILES["fileToUpload"]["name"]." 0.4");
        //echo "\n\nRESULTS:    \n\n".$results;
        //echo "\n\n\n";
        $ids = (explode(",",$results));
        if($ids[0] == 0){
            echo "\nNO ERRORS!!\n".count($ids);
            array_shift($ids);
            array_shift($ids);
            echo "\nAFTER!!\n".count($ids);
            print_r($ids);
            $idstring = implode(', ',$ids);
            //echo "\n\nID-STRING  ".$idstring;
           
            //session_start();
            //$_SESSION['results'] = $idstring;
            //$_SESSION['message'] = "Error Uploading File";
            //header('Location: index.php');
        }

    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

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


//$sql = "SELECT * FROM IMG where id IN (2, 5, 11, 20);";
$sql = "SELECT * FROM IMG where id IN(".$idstring.");";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        //echo "id: " . $row["id"]. $row["phash"]. $row["name"].$row["author"]. $row["bhash"].
          //"\n\n COUNTER".$counter++;

        $pattern = "/CC-BY-SA\w*/i";
        $pattern2 = "/CC-BY\w*/i";
        $pattern_pd = "/PD\w*/i";

        preg_match($pattern, $row['license'], $matches, PREG_OFFSET_CAPTURE);
        preg_match($pattern2, $row['license'], $matches_by, PREG_OFFSET_CAPTURE);
        preg_match($pattern_pd, $row['license'], $matches_pd, PREG_OFFSET_CAPTURE);
     
        echo '<div class="row"><img src="data:image/jpeg;base64,' . base64_encode( $row['image'] ) . '" /></div>';
        echo "\n\n";
        if(!empty($matches))
        {
            
            echo '<div class="row">LICENSE: '.$row['license'].'</div>';
            echo '</br></br><a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">
                 <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />
                 This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>';
            echo '<div class="row"><p>AUTHOR: '.$row['authorname'].'</p><p>URL: <a href="'.$row[url].'"target="_blank">LINK TO IMAGE</a></p></div>';
        }else if (!empty($matches_by)){

            echo '<div class="row">LICENSE: '.$row['license'].'</div>';
            echo '</br></br><a rel="license" href="http://creativecommons.org/licenses/by/4.0/">
                  <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" /></a><br />
                  This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">
                  Creative Commons Attribution 4.0 International License</a>';
            echo '<div class="row"><p>AUTHOR: '.$row['authorname'].'</p><p>URL: <a href="'.$row[url].'"target="_blank">LINK TO IMAGE</a></p></div>';
        } else if (!empty($matches_pd)){
                echo '<div class="row">LICENSE: '.$row['license'].'</div>';
                echo '</br></br><a rel="license" href="http://creativecommons.org/licenses/pdm/4.0/">
                        <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/p/mark/1.0/88x31.png" /></a><br />
                        This work is licensed under a <a rel="license" href="https://creativecommons.org/publicdomain/">
                        Creative Commons Public Domain Mark</a>';
                echo '<div class="row"><p>AUTHOR: '.$row['authorname'].'</p><p>URL: <a href="'.$row[url].'"target="_blank">LINK TO IMAGE</a></p></div>';

        }


    }
} else {
    echo "0 results";
}
$ch = curl_init($url);
$fp = fopen('flower.jpg', 'wb');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_exec($ch);
curl_close($ch);
fclose($fp);
?>