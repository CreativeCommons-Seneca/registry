<?php session_start();?>
<DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>CC Images </title>
	<!-- StyleSheets-->
	<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
	<?php include("navigate.php");
	activate($filen); ?>
	<div class="container">
		<h1>Image Search and Licensing Tool</h1>
		<div class="row">
			<form action="upload7.php" method="post" enctype="multipart/form-data">
				<div class="form-group">
					<label for="exampleInputEmail1">Image url</label>
					<input type="text" name="url" class="form-control" id="url" placeholder="Enter Image URL">
				</div>
				<div class="form-group">
					<label for="fileToUpload">File input</label>
					<input type="file"  name="fileToUpload" id="fileToUpload">
					<p class="help-block">Browse your PC to Select Image</p>
				</div>
				<div class="checkbox">
					<label>
						<input type="checkbox"> Add Image to Registry
					</label>
				</div>
				<button type="submit" class="btn btn-primary" value="Upload Image" name="submit" >Submit</button>
			</form>
		</div>

<?php
if(isset($_SESSION['results'])){
	$idstring=$_SESSION['results'];
 	echo '<div class="row"><div class="col-sm-3" style="padding: 0px">
 		  <h2>Original Image</h2>';

 	echo '<img style="border: 2px solid black;" width="200" src="uploads/'.$_SESSION['original'].'" ?></br></div>';	
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

$sql = "SELECT * FROM IMG where id IN(".$idstring.");";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo '<div class="col-sm-9"><h2>'.$_SESSION['total'].' Matches</h2>';
    while($row = $result->fetch_assoc()) {
        $pattern = "/CC-BY-SA\w*/i";
        $pattern2 = "/CC-BY\w*/i";
        $pattern_pd = "/PD\w*/i";

        preg_match($pattern, $row['license'], $matches, PREG_OFFSET_CAPTURE);
        preg_match($pattern2, $row['license'], $matches_by, PREG_OFFSET_CAPTURE);
        preg_match($pattern_pd, $row['license'], $matches_pd, PREG_OFFSET_CAPTURE);
     
        echo '<div class="row"  style = "border: 2px solid black;" ><div class="col-sm-3">
        	  <img width="200" style="margin: auto;" src="data:image/jpeg;base64,' . base64_encode( $row['image'] ) . '" /></div>';
        echo "\n\n";
        if(!empty($matches))
        {
            

            echo '<div class="col-sm-9">
            	 <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">
                 <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />
                 This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/">Creative Commons Attribution-ShareAlike 4.0 International License</a>';
                    
        }else if (!empty($matches_by)){


            echo '<div class="col-sm-9"><a rel="license" href="http://creativecommons.org/licenses/by/4.0/">
                  <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/88x31.png" /></a><br />
                  This work is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">
                  Creative Commons Attribution 4.0 International License</a>';
                  
        } else if (!empty($matches_pd)){

            echo '<div class="col-sm-9"><a rel="license" href="http://creativecommons.org/licenses/pdm/4.0/">
                    <img alt="Creative Commons Licence" style="border-width:0" src="https://i.creativecommons.org/p/mark/1.0/88x31.png" /></a><br />
                    This work is licensed under a <a rel="license" href="https://creativecommons.org/publicdomain/">
                    Creative Commons Public Domain Mark</a>';

        }

        echo '<p>AUTHOR: '.$row['authorname'].'</p><p> <a href="'.$row[url].'"target="_blank">IMAGE URL</a></p></div></div>';
    }
    echo '</div>';
} else {
    echo "0 results";
}
} else {

	echo "\n\n ERROR ".$_SESSION['message'];
	} ?>
</div>
	<script src="jquery-1.11.3.min.js"></script>
	<script src="js/bootstrap.min.js"></script>	
</body>
</html>
