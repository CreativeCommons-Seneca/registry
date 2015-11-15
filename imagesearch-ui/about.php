<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>CC Images </title>
	<!-- StyleSheets-->
	<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<style>

  img {
    width: 120px;
   
  }


  
</style>
<body>
 
<?php include("navigate.php");
activate($filen); ?>
<div class="container">
  <h1 style="text-align: center;">About This Image Search Tool</h1>
  <div class="col-xs-8 center-block" style="float:none; margin-bottom: 15px;">
    <p> This Image Search tool will allow you easily find the Creative Commons licensed images along with their license types, original image links and author's name.
      You may upload an image from your device, or use a web URL of an already existing image.</p>

    <p> The system uses a background daemon that communicates via API and searches the database for identical images and returns the search result, along with the license, author, and link information about the image.</p>
   
    <h1 style="text-align: center;">Demo How-To</h1>
    <p>To use the Demo, just click on either of the search buttons - "Search By URL" or "Search Locally", and it will take you to a list of images to select from.
    Te demonstarte URL search just click on any image, and the search will be performed on the server using the selected URL.
    To demonstarte Local search, right-click and save the images on your machine first, then click "Search Local Images" button, and the server will search based on the uploaded image.

    Easy to use!</p>
    </div>
</div>

<script src="jquery-1.11.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>	


</body>
</html>
