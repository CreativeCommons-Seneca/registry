<?php
ini_set('precision', 20); 
// TO-DO Complete Validation
$servername = "localhost";
$username = "anna";
$password = "password";
$dbname = "hashes";

if(isset($_GET) || isset($_POST)){
	$apiargs = isset($_GET) ? $_GET : $_POST;
	$args = array();
	$request = $apiargs["request"];
	$message = '';
	$error = false;


	if (strcmp($request,"match") === 0){
		$args["hash"] = $apiargs["hash"];
		$pre = "m";	
		$error = empty($args["hash"])? true : false;

	} else if (strcmp($request,"add") === 0){
    // TO-DO complete ADD
		$pre = "a";
	
		/*
		$args["phash"] = $apiargs["phash"];
		$args["mhash"] = b4c5ac0d5e3b66489507cd1b32d55b74a67625db78b69de36ff1f8039dc5d2e9b76565f8b6f5381a6d736e7a78b116da595db36ae5f824d051b49d236d7159b39ec976d1f3abcf86;
	    $args["name"] = $apiargs["name"];
	    $args["directory"] = $directory;
	    $args["author"] = $apiargs["author"];
		$args["license"] = $apiargs["license"];
		$args["url"] = $apiargs["url"];
		$args["imageurl"] = $apiargs["imageurl"];
		$args["source"] = $apiargs["source"];
		$args["dateuploaded"] = $apiargs["dateuploaded"];
		$args["dateuploadedu"] = $epochtime;
		$args["title"] = $apiargs["title"];
		$args["deleted"] = "n";
		$args["reasons"] = "none";
		$args["falsePositives"] = $apiargs["date"];
		*/

		// temporary testing purpose
		$args["phash"] = 11415234608916087887;
		$args["mhash"] = "b4c5ac0d5e3b66489507cd1b32d55b74a67625db78b69de36ff1f8039dc5d2e9b76565f8b6f5381a6d736e7a78b116da595db36ae5f824d051b49d236d7159b39ec976d1f3abcf86";
	    $args["name"] = $apiargs["name"];
	    $args["directory"] = "directory";
	    $args["author"] = "author";
		$args["license"] = "cc-by";
		$args["url"] = "url/here";
		$args["imageurl"] = "img/url.jpg";
		$args["source"] = "flickr";
		$args["dateuploaded"] = "2013-03-20";
		$args["dateuploadu"] = "2013-03-20";
		$args["title"] = "No Name";
		$args["deleted"] = "n";
		$args["reasons"] = "none";
		$args["falsePositives"] = "none";

       
       // echo "\n\n  ARGS:  ".count($args);

	} else if (strcmp($request,"delete") === 0){
		//TO-DO complete DELETE
		$pre = "d";
		$args["id"] = $apiargs["id"];
		$error = empty($args["id"])? true : false;
	} else{
		$message = "Invalid parameters: request must be add, match or delete\n";
		$error = true;
		echo json_encode($message);
	}
	$str = $pre;
	foreach($args as $key=>$value){
		$str.=" ".$key.":".base64_encode($value);
		$error = empty($value)? true : false;

	}
	$str.="\r\n";
	//echo $str."\n\n"."  ERROR:  ";
	//var_dump($error);
    

    if($error === false){
		// Open the socket
		try{
			$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
			socket_connect($socket, "/tmp/cc.daemon.sock");
		// Compose the request
		}catch (Exception $e){
			echo "Could not connect ".$e;
		}
		

		try{
			socket_write($socket, $str, strlen($str));
		}catch (Exception $e){
		echo "Could not write ".$e;
		}

		// Read from socket
		try{
			$chunk = socket_read($socket, 4096);
		}catch (Exception $e){
		echo "Could not read ".$e;
		}

		$ids = (explode(",",$chunk));

		
		if($ids[0] == 0){
		

			if(strcmp($request,"match") === 0){

				$response["match"] = "true";
				$response['total'] = $ids['1'];
				$response['matches']=array();
				array_shift($ids);
				array_shift($ids);
				$idstring = implode(', ',$ids);
				// Create connection
				$conn = new mysqli($servername, $username, $password, $dbname);
				// Check connection
				if ($conn->connect_error) {
					die("Connection failed: " . $conn->connect_error);
				}
				$sql = "SELECT * FROM IMG where id IN(".$idstring.");";
				$result = $conn->query($sql);

				// Create the response result
				if ($result->num_rows > 0) {
					while($row = $result->fetch_assoc()) {
						$match = array();
						$match["id"] = $row["id"];
						$match["name"] = $row["title"];
						$match["url"] = $row["url"];
						$match["author"] = $row["author"];
						$match["license"] = $row["license"];
						$match["date"] = $row["dateuploaded"];
						array_push($response['matches'],$match);
					} 
				} else {
					// echo "No Results";
				}
			} else if (strcmp($request,"add") === 0){
				$response["id"] = $ids[1];
				$response["type"] = "image added";
			
		} else if (strcmp($request,"delete") === 0){
				$response["deleted"] = $ids[0];

			}

		}// close if id is 0 (success);
	else{
		$response["match"] = false;
		$response["total"] = 0;
	}
} // close if error
echo json_encode($response);
}else{
	//echo "NO ARGS";
}

?>
