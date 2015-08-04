
<?php
function activate($filename){
  $file = basename($filename, ".php");
  if ($file == 'index'){
    $file = "home";
  }
  ?>
 
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">CC Image Search</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul id="navigate" class="nav navbar-nav">
        <li id="CC"><a href="index.php">CC<span class="sr-only">(current)</span></a></li>
        <li id="CreativeCommons"><a href="http://creativecommons.com">Creative Commons</a></li>
        
        <li id="Search"><a href="index.php">Search By URL</a></li>
        <li id="Upload"><a href="index.php">Upload</a></li>
        <li id="Add"><a href="index.php">Add To Registry</a></li>
      </ul>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
 <script>
if ($('#navigate> li#<?php echo $file ?>').length) {
     console.log("EXItst");
     $(<?php echo $file ?>).addClass( 'active');
}



  </script>
<?php
}?>
