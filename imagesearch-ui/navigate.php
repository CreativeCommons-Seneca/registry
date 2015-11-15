
<?php
function activate($filename){
  $file = basename($filename, ".php");
  if ($file == 'index'){
    $file = "home";
  }
  ?>
 
<script src="jquery-1.11.3.min.js"></script>
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
      <a class="navbar-brand" href="index.php">CC Image Search</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul id="navigate" class="nav navbar-nav">
        <li id="CreativeCommons"><a href="http://creativecommons.com" >Creative Commons</a></li>
        
        <li id="Search"><a href="index.php">Image Search</a></li>
        <li id="Demo"><a href="demo.php">Demo Image Search</a></li>
        <li id="About"><a href="about.php">About</a></li>
        <li id="API"><a href="api.php">API</a></li>

      </ul>

    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
 <script>



  </script>
<?php
}?>
