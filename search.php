<?php
//Find people willing to help and present them in a table here
require_once("lib/sqllib.php");

//First, let's find out if we were given a complete query or not. Everything
//valid will be in the autocomplete table.
DB::$user = 'apiaccess';
DB::$password = 'OBLi6xKs7Fb3QJ8iWuoq';
DB::$dbName = 'helpdb';
DB::$host = 'localhost';

$purpose = 0;
$query = strtolower(urldecode($_GET["q"]));
if(empty($query)) header("Location: /");

$result = DB::queryFirstRow("SELECT string FROM Autocomplete WHERE string=%s",$query);
if(empty($result)) {
  //Now what we need to to do is show all of the classes that could match because
  //someone was stupid and thought they could cheat the system
  $result = DB::query("SELECT * FROM Autocomplete WHERE LOWER(string) LIKE %ss ORDER BY string",$query);
  $purpose = 1;
} else {
  $result = DB::query("CALL checkForStudentsTakenClass(%s);",urldecode($result["string"]));
}

?>

<html>
<head>
  <title><?php echo $_GET["q"]; ?> - Rose Hulman Help Lookup</title>
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
  <script>

  </script>
</head>
<body>
  <?php require("lib/header.php"); ?>
  <div class="container">
    <?php if($purpose===1) { ?>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">Which course did you want to look up?</h3>
        </div>
        <div class="panel-body">
          The search you entered wasn't specific enough. What did you want help with?
        </div>
        <ul class="list-group">
          <?php
            foreach($result as $row) {
              echo '<li class="list-group-item"><a href="/search.php?q='.urlencode($row["string"]).'">'.$row["string"].'</a></li>';
            }
          ?>
        </ul>
      </div>
    <?php } else { ?>
    <table class="table table-striped table-bordered">
      <thead><td>Name</td><td>Willingness</td><td>Year Taken</td><td>Availability</td><td>Location</td></thead>
      <?php
        foreach($result as $row) {
          echo "<tr><td><a href=\"mailto:".$row["email"]."@rose-hulman.edu\">".$row["Name"]."</a></td><td>";
          if($row["willingness"]<=5) echo "<div class=\"progress\"><div class=\"progress-bar\" role=\"progressbar\" style=\"width: ".($row["willingness"]*20)."%;\">".$row["willingness"]."</div></div>";
          else if($row["willingness"]==7) echo "<div class=\"progress\"><div class=\"progress-bar progress-bar-info progress-bar-striped\" role=\"progressbar\" style=\"width: 100%;\">TA</div></div>";
          else if($row["willingness"]==10) echo "<div class=\"progress\"><div class=\"progress-bar progress-bar-success progress-bar-striped\" role=\"progressbar\" style=\"width: 100%;\">Professor</div></div>";
          echo "</td><td>".$row["Year"]."</td><td>".$row["Availability"]."</td><td>".$row["room_location"]."</td></tr>";
        }
      ?>
    </table>
    <?php } ?>
  </div>
</body>
</html>
