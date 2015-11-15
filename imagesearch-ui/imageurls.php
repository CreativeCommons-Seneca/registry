<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>CC Image Search - Test Image List</title>
	<!-- StyleSheets-->
	<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
 
<?php include("navigate.php");
activate($filen); ?>
<div class="container">
  <h1 style="text-align: center;">Test Image List</h1>
  <div class="col-xs-8 center-block" style="float:none; margin-bottom: 15px;">
    <p>The images in this list have their hashes in the database. You can use any of these URLs to test the service.</p>
    <p>Since it's unreasonable to show 1094940 URLs in one page (the list itself is 150MB in size) this is a random sample of 100 URLs. You can download the full list <a href="imageurls-fulllist.txt.gz">here</a>.</p>
    <ul>
<?php
$file = fopen("imageurls-fulllist.txt", "r");
if ($file === FALSE)
  echo "<li>Couldn't open the list of images</li>";
$NUM_LINES_IN_FILE = 1094940;
$NUM_LINES_TO_DISPLAY = 1000;
$firstLineNum = rand(0, $NUM_LINES_IN_FILE - $NUM_LINES_TO_DISPLAY);
# Skip the part of the file before the random chunk I want:
for ($i = 0; $i < $firstLineNum; $i++)
    fscanf($file, "%s", $unused);
# The lines I do want:
for ($i = 0; $i < $NUM_LINES_TO_DISPLAY; $i++)
{
    $currLineNum = $firstLineNum + $i;
    $url = "";
    $rc = fscanf($file, "%s", $url);
    echo "<li>$currLineNum: <a href='$url'>$url</a></li>";
}
fclose($file);
?>
    </ul>
  </div>
</div>

<script src="jquery-1.11.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>	

</body>
</html>
