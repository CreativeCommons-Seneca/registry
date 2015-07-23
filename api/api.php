<?php
include 'ccError.php';

ini_set('precision', 20); 
// TO-DO Complete Validation
$servername = "localhost";
$username = "root";//"anna";
$password = "hosung";//"password";
$dbname = "hashes";

function echoError($code){
	$response["errorcode"] = $code;
	$response["errormessage"] = getErrorString($code);
	echo json_encode($response);
}

if(isset($_GET) || isset($_POST)){
	$apiargs = isset($_GET) ? $_GET : $_POST;
	$args = array();
	$request = $apiargs["request"];
	$message = '';
	$error = false;
	$errorcode = 0;	//true;

	if (strcmp($request,"match") === 0){
		$error = empty($apiargs["hash"])? true : false;

		if ($error){
			$errorcode = ccError::$ERR_API_INVALID_MATCH_PARAM;
		}
		else{
			$args["hash"] = $apiargs["hash"];
			$pre = "m";	
		}
	} else if (strcmp($request,"add") === 0){
    	// TO-DO complete ADD

		// TO-DO validation
		$error = true; 

		if ($error){
			$errorcode = ccError::$ERR_API_INVALID_ADD_PARAM;
		}
		else{
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
			$pre = "a";
		}
       // echo "\n\n  ARGS:  ".count($args);

	} else if (strcmp($request,"delete") === 0){
		//TO-DO complete DELETE
		$error = empty($args["id"])? true : false;
		if ($error){
			$errorcode = ccError::$ERR_API_INVALID_DELETE_PARAM;
		}
		else{
			$pre = "d";
			$args["id"] = $apiargs["id"];
		}
	} else{
		$error = true;
		$errorcode = ccError::$ERR_API_INVALID_PARAM;
	}

	//if parameter has error echo and exit
	if($error === true){
		echoError($errorcode);
		exit();
	}

	//make request string to daemon
	$str = $pre;
	foreach($args as $key=>$value){
		$str.=" ".$key.":".base64_encode($value);
		//$error = empty($value)? true : false;
	}
	$str.="\r\n";
	//echo $str."\n\n"."  ERROR:  ";
	//var_dump($error);
	
	// Socket operation
	$socket = null;
	try{
		if ( ($socket = socket_create(AF_UNIX, SOCK_STREAM, 0)) == false){
			throw new Exception(ccError::$ERR_API_SOCKET_CREATE);
		}

		if ( (socket_connect($socket, "/tmp/cc.daemon.sock")) == false){
			throw new Exception(ccError::$ERR_API_SOCKET_CONNECT);
		}

		if ( (socket_write($socket, $str, strlen($str))) == false){
			throw new Exception(ccError::$ERR_API_SOCKET_WRITE);
		}

		if ( ($chunk = socket_read($socket, 4096)) == false){
			throw new Exception(ccError::$ERR_API_SOCKET_READ);
		}
	}
	catch(Exception $e){
		echoError($e->getMessage());
		exit();
	}

	//split string by ",""
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
				//die("Connection failed: " . $conn->connect_error);
				echoError(ccError::$ERR_API_DB_CONNECT);
				exit();
			}
			$sql = "SELECT * FROM IMG where id IN(".$idstring.");";
			$result = $conn->query($sql);

			// Create the response result
//			if ($result == false){
//				echoError(ccError::$ERR_API_DB_QUERY);
//				exit();
//			}

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
		//$response["total"] = 0;

		$response["errorcode"] = $ids['1'];
		$response["errormessage"] = $ids['2'];
	}

	//seccess case. generate json from response
	echo json_encode($response);
}
else{
	echoError(ccError::$ERR_API_INVALID_PARAM);
	exit();
}



?>

