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
    if(isset($_POST["url"])){
        $url = $_POST['url'];
        echo "\n\nURL:  ".$url."\n\n";
    }
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
        $results = exec("./mhexe/mhsearcher mhexe/tree.mh /var/www/html/cc/uploads/".$_FILES["fileToUpload"]["name"]." 0.2");
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
           
            session_start();
            $_SESSION['file'] = $_FILES;
            $_SESSION['results'] = $idstring;
            $_SESSION['original'] = $_FILES["fileToUpload"]["name"];
            header('Location: index.php');
        }
        else {
            unset($_SESSION['results']);
            $_SESSION['error'] = $ids[1];
            header('Location: index.php');
        }

    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

if(!empty($_POST['url'])){
    $ch = curl_init($url);
    $fp = fopen('uploads/flower.jpg', 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);
    $results = exec("./mhexe/mhsearcher mhexe/tree.mh /var/www/html/cc/uploads/flower.jpg 0.2");
    $_SESSION['original'] = 'flower.jpg';
    $ids = (explode(",",$results));

    if($ids[0] == 0){
        //echo "\nNO ERRORS!!\n".count($ids);
        $total = $ids[1];
        array_shift($ids);
        array_shift($ids);
        //echo "\nAFTER!!\n".count($ids);
        print_r($ids);
        $idstring = implode(', ',$ids);
        //echo "\n\nID-STRING  ".$idstring;
       
        session_start();
        $_SESSION['file'] = $_FILES;
        $_SESSION['results'] = $idstring;
        $_SESSION['original'] = 'flower.jpg';
        $_SESSION['total'] = $total;
        header('Location: index.php');
    }


}

?>