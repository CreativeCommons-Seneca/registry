<?php
session_start(); 
$target_dir = "uploads/";
$encoded = mb_convert_encoding(basename($_FILES["fileToUpload"]["name"]), "UTF-8", mb_detect_encoding(basename($_FILES["fileToUpload"]["name"])));
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
if(isset($_POST['fileToUpload'])){
    $_POST['url'] = '';
}

// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    if(isset($_POST["url"])){
        $url = $_POST["url"];
    }
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    /*
    if(isset($_POST["url"])){
        $url = $_POST['url'];
        echo "\n\nURL:  ".$url."\n\n";
    }
    */
    if($check !== false) {
        $message = "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        $message = "File is not an image.";
        $uploadOk = 0;
    }
}


/*// Check if file already exists
if (file_exists($target_file)) {
    $message = "Sorry, file already exists.";
    $uploadOk = 0;
}
/*
// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
*/

if (empty($url)){
    // Allow certain file formats
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "JPG" && $imageFileType != "jpeg"
    && $imageFileType != "gif" ) {
        $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
        unset($_SESSION['results']);
        $_SESSION['type'] = $imageFileType;
        $_SESSION['mime'] = $check['mime'];
        $_SESSION['message'] = $message;
        $_SESSION['file'] = $_FILES;
//        header('Location: index.php');

    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            //session_start();
            //$_SESSION['results'] = '2,3,5';
            //header('Location: index.php');
            //echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.\n\n POSTS!! *************";

            $str =  "./dcthash/phashhex /var/www/html/ui/uploads/".$_FILES["fileToUpload"]["name"];
            $hexHash = exec($str);
            

            $results = file_get_contents('http://localhost/api/api.php?request=match&hash='.$hexHash);
            //$results = file_get_contents('http://localhost/api/api.php?request=match&hash=F7E2ED121819B6B');
            
            $obj = json_decode($results);

            $idstring = '';
            
            if ($obj->status == 'ok'){
                $matches = $obj->matches;
                foreach($matches as $match){
                    $idstring = $idstring . $match->id . ", ";                
                }
                $idstring = substr($idstring, 0, -2);
                session_start();
                $_SESSION['file'] = $_FILES;
                $_SESSION['results'] = $idstring;
                $_SESSION['original'] = $_FILES["fileToUpload"]["name"];
                header('Location: index.php');
            }
            else{
                unset($_SESSION['results']);
                $_SESSION['error'] = $ids[1];
                header('Location: index.php');
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

if(!empty($url)){
//http://stackoverflow.com/questions/22155882/php-curl-download-file
//php5-curl should be installed
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSLVERSION,3);
    $data = curl_exec ($ch);
    $error = curl_error($ch); 
    curl_close ($ch);
    $destination = "./uploads/flower.jpg";
    $file = fopen($destination, "w+");
    fputs($file, $data);
    fclose($file);

    $str =  "./dcthash/phashhex /var/www/html/ui/uploads/flower.jpg";
    $hexHash = exec($str);
    $results = file_get_contents('http://localhost/api/api.php?request=match&hash='.$hexHash);
    //$results = exec("./mhexe/mhsearcher mhexe/tree.mh /var/www/html/cc/uploads/flower.jpg 0.2");
    $_SESSION['original'] = 'flower.jpg';
    $obj = json_decode($results);
    //echo $obj;

    $idstring = '';
    
    if ($obj->status == 'ok'){
        $matches = $obj->matches;
        foreach($matches as $match){
            $idstring = $idstring . $match->id . ", ";                
        }
        $total = $obj->total;
        $idstring = substr($idstring, 0, -2);
        session_start();
        $_SESSION['file'] = $_FILES;
        $_SESSION['results'] = $idstring;
        $_SESSION['original'] = 'flower.jpg';
        header('Location: index.php');
    }
    else{
        unset($_SESSION['results']);
        $_SESSION['error'] = $ids[1];
        header('Location: index.php');
    }


}

?>