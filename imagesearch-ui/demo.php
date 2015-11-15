<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CC Image Search </title>
  <!-- StyleSheets-->
  <link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
  .fileUpload {
    position: relative;
    overflow: hidden;
    margin: 10px;
  }
  .fileUpload input.upload {
    position: absolute;
    top: 0;
    right: 0;
    margin: 0;
    padding: 0;
    font-size: 20px;
    cursor: pointer;
    opacity: 0;
    filter: alpha(opacity=0);
  }
  body {
     overflow-x: hidden;
  }
</style>

<body>
  <script src="phash.js"></script>
  <script type="text/javascript">
    var storage;
    var fail;
    var uid;
    try {
      uid = new Date;
      (storage = window.localStorage).setItem(uid, uid);
      fail = storage.getItem(uid) != uid;
      storage.removeItem(uid);
      fail && (storage = false);
    } catch (exception) {}

    oFReader = new FileReader(), oFReader2 = new FileReader(), rFilter = /^(?:image\/bmp|image\/cis\-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x\-cmu\-raster|image\/x\-cmx|image\/x\-icon|image\/x\-portable\-anymap|image\/x\-portable\-bitmap|image\/x\-portable\-graymap|image\/x\-portable\-pixmap|image\/x\-rgb|image\/x\-xbitmap|image\/x\-xpixmap|image\/x\-xwindowdump)$/i;

    oFReader.onload = function (oFREvent) {
      var img=new Image();
      img.onload=function(){
        document.getElementById("originalImg").src=img.src;
        $("#originalImg").width(225);
      } // close img.onload

      img.src=oFREvent.target.result;
      
      try{
        localStorage.orSource = img.src;
      } catch(e) {
        console.log("LIMIT reached!!!");
      }
     
      var canvas=document.createElement("canvas");
      var ctx=canvas.getContext("2d");

      console.log("original width, height");
      console.log(img.width, img.height);
      console.log(img.src);



      /*
      // Resize the image with canvas 
      if(img.width > 1000){
        console.log("MORE THAN!!");
        var ratio = img.width/img.height;
        canvas.width = 1000;
        canvas.height = parseInt(1000/ratio);
      }else{
        canvas.width = img.width;
        canvas.height = img.height;
      }
      */

      // Do not resize
      canvas.width = img.width;
      canvas.height = img.height;

      ctx.drawImage(img,0,0,img.width,img.height,0,0,canvas.width,canvas.height);
      var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
      var src = canvas.toDataURL("image/jpeg");

      console.log(getCookie("imageSource"));
      console.log(src);

      $("#sub").prop('disabled', true);
  
      // Send image info to phash.js phash function
      var hash = phash(imageData, canvas.width, canvas.height);
      $("#status").html("Hash: "+hash + "</br> Checking for Matching Images...</br>");

      var form_data = new FormData(); 
      var orFile = document.getElementById("fileToUpload").files[0]; 

      form_data.append("hashes", hash);
      form_data.append("fname", orFile.name);

      $("#status").append("Looking Up Matches ...");

      // Send ajax to request to the server
      $.ajax({
        url: 'upload.php', // point to server-side PHP script 
        dataType: 'text',  // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,                       
        type: "post",
        success: function(php_script_response){
          var resp = JSON.parse(php_script_response);
          if(resp.api.status == "ok"){
            if(resp.api.total > 0){
              setCookie("demosearchresults", php_script_response,1);
              $("#status").html("</br><b> Found " + resp.api.total+" matches </b></br>");
              // Build matches table
              var table = buildTable(resp);
              $('#imagematch').html(table);
            } else {
              setCookie("demosearchresults", php_script_response,-1);
              $("#status").html("</br><b> No Matches Found in Database </b></br>");
              $('#imagematch').html("");
            }
          } else {
            setCookie("demosearchresults", php_script_response,-1);
            $("#status").html("</br><b> Error: " + resp.api.errormessage+" matches </br>Error Code: "+resp.api.errorcode+"</b></br>");
            $('#imagematch').html("");
          }
        }// close success upon ajax call
      }); // close ajax call
      $("#sub").prop('disabled', false);
      $("#fileToUpload").replaceWith($("#fileToUpload").clone());
      $("#originalImg").css('border', "solid 2px white").css('box-shadow',"0 3px 3px -1px black");  
    } // Close file reader onload

function loadImageFile() {

  $("#myModal").modal('hide');
  setCookie("demourl", "no",1);

  // Set loading image
  document.getElementById("originalImg").src="ajax-loader.gif";
  $("#originalImg").width(16);
  $("#originalImg").css('box-shadow','none');

  if (document.getElementById("fileToUpload").files.length === 0) { return; }
  var oFile = document.getElementById("fileToUpload").files[0];
  if (!rFilter.test(oFile.type)) { alert("You must select a valid image file!"); return; }
  oFReader.readAsDataURL(oFile);

  $("#status").html("Hashing the Image...</br>");
  $('#imagematch').html("");
  // Display hashing the image while the file is loaded and hashed
}

function sendURL() {

  $('#imagematch').html("");
  $('#status').html("");
  var form_data = new FormData(); 
  var orFile = document.getElementById("url").value;
  console.log(orFile);
   
  if(orFile === ""){
    console.log("EMPTY");
    $('#imagematch').html("");
  } else{
    $("#sub").prop('disabled', true);
    $("#originalImg").css('box-shadow','none');
    $("#originalImg").width(16);
    document.getElementById("originalImg").src="ajax-loader.gif";
    $("#status").html("Downloading and hashing the image...</br>");
    form_data.append("url", orFile);

    // Needs refactoring into a separate function
    $.ajax({
      url: 'upload.php', // point to server-side PHP script 
      dataType: 'text',  // what to expect back from the PHP script, if anything
      cache: false,
      contentType: false,
      processData: false,
      data: form_data,                       
      type: "post",
      success: function(php_script_response){
        $("#originalImg").css('border', "solid 2px white").css('box-shadow',"0 3px 3px -1px black");  
        document.getElementById("originalImg").src=orFile;
        $("#originalImg").width(225);
        var resp = JSON.parse(php_script_response);

        console.log(resp.api.status);
        console.log(resp.matches);
        if(resp.api.status == "ok"){
          $("#status").html("</br><b> Found " + resp.api.total+" matches </b></br>");
          console.log(resp.matches);
          if(resp.api.total > 0){
             setCookie("demourl", "yes",1);
              setCookie("file", "no",1);
              setCookie("demosearchresults", php_script_response,1);
              setCookie("src", orFile);

            // Build matches table
            var table = buildTable(resp);

            $('#imagematch').html(table);
          }else{
            setCookie("demosearchresults", php_script_response,-1);
            $('#imagematch').html("");
            $("#status").html("</br><b> No Matches Found in Database </b></br>");
              setCookie("demourl", "yes",-1);
              setCookie("file", "no",-1);
              
           
          }
        }else{
          setCookie("demosearchresults", php_script_response,-1);
          $("#status").html("</br><b> Error: " + resp.api.errormessage+" matches </br>Error Code: "+resp.api.errorcode+"</b></br>");
          $('#imagematch').html("");
        }
        $("#sub").prop('disabled', false);
        console.log(resp);

        $("#fileToUpload").replaceWith($("#fileToUpload").clone());
      }// close success
    });
  }
}


// Building results table
function buildTable(resp){
  var table = $('<div class="col-xs-6 center-block" style="float:none; margin-bottom: 15px;">');
  var row = $('<div class="row"></div>');
  table = table.append(row);

  for(x=0; x < resp.matches.length; x++){
    console.log(resp.matches[x]);
    var rows = $('<div style="text-align: center;  border: 2px solid #337ab7;  box-shadow: 0 3px 3px -1px black; margin: 5px; margin-bottom: 15px; background-color: white; vertical-align: middle;"></div>')
                .html('<table><tr><td style="padding-left: 3px; text-align: left;"><a href="'+resp.matches[x].url+'" ><img width="140" style="border: 2px solid white;" src="'+resp.matches[x].imageurl+'" </a></td><td style="padding-left: 10px; text-align: left;"><b><a href="'+resp.matches[x].url+'">Image URL Link</a></b></br>By: '+resp.matches[x].author+'</br>'+resp.matches[x].licenseLink+'</td></tr>');
    table.append(rows);
  }
  return table;
}

function buttonClick(){
  event.preventDefault();
  console.log("in function");
  $("#upload").show();
  $("#subUpload").click( function(event) {
    $("#matches").append("hello");
  });
}

function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + "; " + expires;
  //console.log(document.cookie);
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i=0; i<ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1);
        if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
    }
    return "";
}

function checkCookie() {
    var user = getCookie("toremember");
    if (user != "") {
        alert("Welcome again " + user);
    } else {
        user = prompt("Please enter your name:", "");
        if (user != "" && user != null) {
            setCookie("username", user, 365);
        }
    }
}

// The Form
</script>
<?php include("navigate.php");
activate($filen); ?>
<div class="container-fluid">
  <h1 style="text-align: center;">Image Search and Licensing Tool</h1>
  <div class="col-xs-8 center-block" style="float:none; margin-bottom: 15px;">
    <form name="matches" method="post">
      <div class="form-group">
        <input type="text" name="url" class="form-control" id="url" onchange="sendURL()" placeholder="Enter Image URL">
      </div>
    </div>
    <div class="col-xs-8 center-block" style="float:none; text-align: center;">  
       <button type="button" class="btn btn-info btn-med" data-toggle="modal" data-target="#myModal">Search URL Demo</button>
       <button type="button" class="btn btn-info btn-med" data-toggle="modal" data-target="#myModal">Search Locally Demo</button> </br>
    </div>
  </form>

</div>
<div style="text-align: center; margin: 5px; margin-bottom: 15px; vertical-align: middle;"><img width="255" style="margin:auto;" id="originalImg"></img><p id="status"></p><div id="loader"></div></div>
<div class="row" id="imagematch"></div>

</div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
  <div class="modal-dialog">  
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Click on the Image to select the URL</h4>
      </div>
      <div class="modal-body">

        <div class = "row" style="margin: 5px">
          <div class="col-xs-3">
            <img class="img-responsive" id="img1" onclick="modalUrl(this)" src="https://upload.wikimedia.org/wikipedia/commons/1/1f/Osaka_Motor_Show_2013_%28216%29_BMW_i8_Concept.JPG"></a>
          </div>
          <div class="col-xs-3">
            <img class="img-responsive" id="img2" onclick="modalUrl(this)" src="https://upload.wikimedia.org/wikipedia/commons/2/29/Cez_motor_logo.jpg"></a>
          </div>
          <div class="col-xs-3">
            <img class="img-responsive" id="img2" onclick="modalUrl(this)" src="https://upload.wikimedia.org/wikipedia/commons/d/d5/Rheintaler-schloss-falkenlust_35x35.jpg"></a>
          </div>
          <div class="col-xs-3">
            <img class="img-responsive" id="img2" onclick="modalUrl(this)" src="https://upload.wikimedia.org/wikipedia/commons/e/e8/Drevnosti_RG_v3_ill145.jpg"></a>
          </div>
        </div>

        <div class = "row" style="margin: 5px; margin-bottom: 17px">
          <div class="col-xs-3">
            <img class="img-responsive"  onclick="modalUrl(this)" src="https://farm8.staticflickr.com/7557/16193404871_04e2e0c031_o.jpg"></a>
          </div>
          <div class="col-xs-3">
            <img class="img-responsive" onclick="modalUrl(this)" src="https://upload.wikimedia.org/wikipedia/commons/3/3b/Osaka_Motor_Show_2013_%2842%29_Renault_DeZir.JPG"></a>
          </div>
          <div class="col-xs-3">
            <img class="img-responsive" onclick="modalUrl(this)" src="https://upload.wikimedia.org/wikipedia/commons/8/8f/Stamps_of_Ecuador%2C_2003-09.jpg"></a>
          </div>
          <div class="col-xs-3">
            <img class="img-responsive"  onclick="modalUrl(this)" src="https://upload.wikimedia.org/wikipedia/commons/9/95/Stamps_of_Ecuador%2C_2003-56.jpg"></a>
          </div>
        </div>
        <div>
        </div>

        <div class="modal-footer">


         <div class="fileUpload btn btn-primary">
          <span>Search Local Images</span>
          <input class="upload" type="file"  name="fileToUpload" id="fileToUpload" onchange="loadImageFile()";>
        </div>

        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<script> 
  $('a[href="#"]').click(function () {
    $(this).preventDefault();
  });

  // Modal onClick
  function modalUrl(source){
    console.log(source);
    $("#myModal").modal('hide');
    $("#url").val(source.src);
    sendURL();
  }

  var result = getCookie("demosearchresults");
  var resultUrl = getCookie("demourl");
  var tryit = getCookie("imageSource");
  console.log(tryit);

  if(result == ""){
    console.log("NOTHING!!!");
  }else{
    if(resultUrl == "yes"){
      console.log(resultUrl);
      var respresult = JSON.parse(result);
      console.log(respresult);
      var table = buildTable(respresult);
      document.getElementById("originalImg").src = getCookie("src");
      $("#originalImg").css('border', "solid 2px white").css('box-shadow',"0 3px 3px -1px black"); 
      $('#imagematch').html(table);
      console.log(result);
    } 
    if(resultUrl == "no"){
      console.log("***** FILE RESULT *******");
      console.log(result);

      var respresult = JSON.parse(result);
      var table = buildTable(respresult);

      if(typeof(Storage) !== "undefined") {
        if (localStorage.orSource) {
          console.log("STORGE");
          console.log(localStorage.orSource);
          document.getElementById("originalImg").src=localStorage.orSource;
          $("#originalImg").css('border', "solid 2px white").css('box-shadow',"0 3px 3px -1px black"); 
        } else {
          localStorage.orSource = img.src;
        }
      } else {
      document.getElementById("result").innerHTML = "Sorry, your browser does not support web storage...";
      }
      $('#imagematch').html(table);
      //console.log(document.getElementById("fileToUpload").files[0]);
      //loadImageFile();
    }
  }
</script>
<script src="jquery-1.11.3.min.js"></script>
<script src="js/bootstrap.min.js"></script>	


  </body>
  </html>
