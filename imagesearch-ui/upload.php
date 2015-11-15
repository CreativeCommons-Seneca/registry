<?php

$response = Array();
$matches = Array();
$response["matches"] = Array();


// Check if the hash is set
if(isset($_POST["hashes"])){
    $hexHash = $_POST["hashes"];
}

// TO DO fix if image did not download!!!
// Download the image and hash on the server
if(isset($_POST["url"])){
    $response["url"] = $_POST["url"];

    $url = htmlentities($_POST["url"]);
    
    $str = 'wget '.escapeshellarg($url).'  -O ./uploads/flower.jpg';
    exec($str);

// TO DO if success
    $str =  "./dcthash/phash /var/www/html/ui/uploads/flower.jpg";
    $hexHash = exec($str);

}    

// Use the API to get the results
try{
$results = file_get_contents('http://localhost/api/api.php?request=match&hash='.$hexHash);
}catch (Exception $e){
    echo "ERROR!!!";
}
$obj = json_decode($results);


if ($obj->status == 'ok'){
    $matches = $obj->matches;
    foreach($matches as $match){
        $idstring = $idstring . $match->id . ", ";                
    }
    $idstring = substr($idstring, 0, -2);

    $servername = "localhost";
    $username = "ccommons";
    $password = "CC@Seneca1";
    $dbname = "hashes";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } 

    $sql = "SELECT * FROM IMG where id IN(".$idstring.");";
    $result = $conn->query($sql);

    $response["total"] = $result->num_rows;

    if ($result->num_rows > 0) {

        while($row = $result->fetch_assoc()) {

            $pattern = "/CC-BY-SA\w*/i";
            $pattern2 = "/CC-BY\w*/i";
            $pattern_pd = "/PD\w*/i";

            preg_match($pattern, $row['license'], $matches, PREG_OFFSET_CAPTURE);
            preg_match($pattern2, $row['license'], $matches_by, PREG_OFFSET_CAPTURE);
            preg_match($pattern_pd, $row['license'], $matches_pd, PREG_OFFSET_CAPTURE);
            

            if(!empty($matches)){

                $matches["licenseLink"] = '<a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">
                <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />
                This work is licensed under a </br><a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/" >
                Creative Commons Attribution-ShareAlike 4.0 International License</a>';

            }else if (!empty($matches_by)){

                $matches["licenseLink"] = '<a rel="license" href="http://creativecommons.org/licenses/by/4.0/">
                <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" /></a><br />
                This work is licensed under a </br><a rel="license" href="http://creativecommons.org/licenses/by/4.0/" >
                Creative Commons Attribution 4.0 International License</a>';
             

            } else if (!empty($matches_pd)){

                $matches["licenseLink"] = '<a rel="license" href="https://creativecommons.org/publicdomain/">
                <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/p/mark/1.0/88x31.png" /></a><br />
                This work is licensed under a </br><a rel="license" href="https://creativecommons.org/publicdomain/" >
                Creative Commons Public Domain Mark</a>';

            } else {
                $matches["licenseLink"] = '<a rel="license" href="https://creativecommons.org/publicdomain/">
                <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/p/mark/1.0/88x31.png" /></a><br />
                This work is licensed under a </br><a rel="license" href="https://creativecommons.org/publicdomain/" >
                Creative Commons Public Domain Mark</a>';

            }
            
            $matches["imagename"]=$row['name'];
            $matches["license"]=$row['license'];
            $matches["author"]=$row['author'];
            $matches["url"] = $row['url'];
            $matches["imageurl"]= $row['imageurl'];
         
            array_push($response["matches"],$matches);
            } // close while

        } 
     $response['api'] = $obj;
    } else{

    $response['total'] = -1;    
    $response['error'] = $ids[1];
    $response['api'] = $obj;
    
        
    }

//$response["matches"] = $matches;
//print_r($response["matches"]);
$phash = "This is a response";
echo json_encode($response);



?>
