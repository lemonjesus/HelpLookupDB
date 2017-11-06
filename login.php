<?php
  //Are we already logged in?
  require_once("lib/authlib.php");
  if(assertLoggedIn()) {
    header("Location: /offer.php");
    die();
  }
?>
<html>
<head>
  <title>Login - Rose Hulman Help Lookup</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <style>
    html, body {
      margin: 0px;
      padding: 0px;
      font-family: sans-serif;
    }
  </style>

  <script src="js/jquery-3.2.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/clickload.js"></script>
  <script>
    $(document).ready(function() {
      $("#login").clickload({
        during:"Logging in...",
        after:"Redirecting...",
        url:"/api/login.php",
        method:"POST",
        data:function() {return {auth:btoa($("#inputUsername1").val()+":"+$("#inputPassword1").val())};},
        success: function(data) {
          if(data.response=="OK") window.location.href = "/offer.php";
          else return false;
          return true;
        },
        error: function() {return "Try Again";}
      });
    });
  </script>
</head>
<body>
  <?php require("lib/header.php"); ?>
  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title">Log In</h3>
          </div>
          <form>
          <div class="panel-body">
            <div class="form-group">
              <label for="inputUsername1">Username</label>
              <input type="text" class="form-control" id="inputUsername1" placeholder="Username">
            </div>
            <div class="form-group">
              <label for="inputPassword1">Password</label>
              <input type="password" class="form-control" id="inputPassword1" placeholder="Password">
            </div>
          </div>
          <div class="panel-footer">
            <input type="submit" class="btn btn-primary" id="login" value="Log In">
          </div>
        </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
