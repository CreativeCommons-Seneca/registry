<?php

$servername = "localhost";
$username = "anna";
$password = "password";
$dbname = "hashes";

if(isset($_GET) || isset($_POST)){
	$apiargs = isset($_GET) ? $_GET : $_POST;
	$args = array();
	$request = $apiargs["request"];

	if (strcmp($request,"match") === 0){
		$args["hash"] = $apiargs["hash"];
		$pre = "m ";	
	} else if (strcmp($request,"add") === 0){
    // TO-DO complete ADD

	}
	$resp = $_GET['request'];

// Open the socket
	$socket = socket_create(AF_UNIX, SOCK_STREAM, 0);

	socket_connect($socket, "/tmp/cc.daemon.sock");
// Compose the request
	$str = $pre;
	foreach($args as $key=>$value){
		$str.=$key.":".base64_encode($value)." ";

	}
	$str.="\r\n";
	socket_write($socket, $str, strlen($str));

// Read from socket
	$chunk = socket_read($socket, 4096);
 //echo $chunk;

 //temp for testing purposes
	$chunk = '1,2,4,5';
	$ids = (explode(",",$chunk));
	
	if($ids[0] != 0){
		$response['match'] = "true";
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
				$pattern = "/CC-BY-SA\w*/i";
				$pattern2 = "/CC-BY\w*/i";
				$pattern_pd = "/PD\w*/i";
				$match["id"] = $row["id"];
				$match["name"] = $row["title"];
				$match["author"] = $row["author"];
				$match["license"] = $row["license"];
				$match["date"] = $row["dateuploaded"];
				array_push($response['matches'],$match);
			} 
		} else {
    //echo "0 results";
		}
}// if match is successfull;
else{
	$response["match"] = false;
	$response["total"] = 0;
}
echo json_encode($response);
}else{
	//echo "NO ARGS";
}

?>
