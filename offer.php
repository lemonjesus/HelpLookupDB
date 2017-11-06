<?php
  //Are we already logged in?
  require_once("lib/authlib.php");
  if(!assertLoggedIn()) {
    header("Location: /login.php");
    die();
  }
  if($_SESSION["isProf"]) {
    header("Location: /proffer.php");
    die();
  }
  require_once("lib/sqllib.php");
  DB::$user = 'apiaccess';
  DB::$password = 'OBLi6xKs7Fb3QJ8iWuoq';
  DB::$dbName = 'helpdb';
  DB::$host = 'localhost';
  $userdata = DB::queryFirstRow("SELECT * FROM Student WHERE email=%s",$_SESSION["username"]);
  $results = DB::query("CALL offerPageList(%s);",$_SESSION["username"]);
  $aresults = DB::query("SELECT * FROM HasHelpHours WHERE tutor_email = %s ORDER BY FIELD(day, 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY');",$_SESSION["username"]);

  //from https://www.phpro.org/examples/Days-Of-Week-Dropdown.html
  function dayDropdown($name="day", $selected=null) {
    $wd = '<select name="'.$name.'" id="'.$name.'">';
    $days = array(
      1 => 'Monday',
      2 => 'Tuesday',
      3 => 'Wednesday',
      4 => 'Thursday',
      5 => 'Friday',
      6 => 'Saturday',
      7 => 'Sunday');
    $selected = is_null($selected) ? date('N', time()) : $selected;
    for ($i = 1; $i <= 7; $i++) {
      $wd .= '<option value="'.$i.'"';
      if ($i == $selected) $wd .= ' selected';
      $wd .= '>'.$days[$i].'</option>';
    }
    $wd .= '</select>';
    return $wd;
}
?>
<html>
<head>
  <title>Offer Some Help - Rose Hulman Help Lookup</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/bootstrap-slider.min.css">
  <link rel="stylesheet" href="css/jquery.timeentry.css">
  <style>
    html, body {
      margin: 0px;
      padding: 0px;
      font-family: sans-serif;
    }
    .slider-selection {
      background: #BABABA;
    }
    #savefloat {
      position: fixed;
      bottom:0px;
      right:0px;
      margin:10px;
    }
    .glyphicon-ok-sign {
      color: green;
    }
  </style>

  <script src="js/jquery-3.2.1.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-slider.min.js"></script>
  <script src="js/jquery.plugin.min.js"></script>
  <script src="js/jquery.timeentry.min.js"></script>
  <script src="js/clickload.js"></script>
  <script src="js/moment.min.js"></script>
</head>
<body>
  <?php require("lib/header.php"); ?>
  <div class="container">
    <?php if(empty($results)) { ?>
    <div class="jumbotron">
      <h1>Import Your Classes</h1>
      <p>In order to choose which classes you can help with, we can automatically import the classes you've taken.</p>
      <p><button class="btn btn-primary btn-lg" id="import">Import Now</button></p>
    </div>
    <?php } else { ?>
      <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading">Info About You</div>
        <div class="panel-body">
          <p>Here you can enter information about yourself. Yeah, it's important.</p>
        </div>
        <!-- List group -->
        <ul class="list-group">
          <li class="list-group-item"><input class="form-control updatable" id="name" name="SName" placeholder="Your Name" value="<?php echo $userdata["SName"]; ?>"></li>
          <li class="list-group-item"><input class="form-control updatable" id="location" name="room_location" placeholder="Where You Live" value="<?php echo $userdata["room_location"]; ?>"></li>
          <li class="list-group-item"><input class="form-control updatable" id="classof" name="class_of" placeholder="When You Graduate" value="<?php echo $userdata["class_of"]; ?>"></li>
        </ul>
      </div>

      <?php if($_SESSION["isTutor"]) { ?>
        <div class="panel panel-default">
          <!-- Default panel contents -->
          <div class="panel-heading">When You Can Help</div>
          <div class="panel-body">
            <p>You were marked as a TA for a course. Let people know when you can help! You're listed as a tutor for:
              <?php
                //from http://stackoverflow.com/a/1320156/1461223
                function flatten(array $array) {
                  $return = array();
                  array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
                  return $return;
                }
                $tasfor = DB::query("CALL displayTAClass(%s);",$_SESSION["username"]);
                echo implode(", ",flatten($tasfor));
              ?>            
            </p>
          </div>

          <table class="table table-striped table-bordered" id="available_table">
            <thead><td>Day</td><td>Start Time</td><td>End Time</td><td>Actions</td></thead>
            <?php
              foreach($aresults as $row) {
                echo "<tr><td>".$row["day"]."</td><td>".$row["start_time"]."</td><td>".$row["end_time"]."</td>";
                echo "<td id=\"".$row["id"]."\"><span class=\"glyphicon glyphicon-pencil\"></span><span class=\"glyphicon glyphicon-remove\"></span></td></tr>";
              }
            ?>
            <tr><td><?php echo dayDropdown("daydropdown"); ?></td><td><input id="newstarttime" type="text"></td><td><input id="newendtime" type="text"></td><td><span class="glyphicon glyphicon-plus" id="addnewtime"></span></td></tr>
          </table>
        </div>
      <?php } ?>

      <table class="table table-striped table-bordered">
        <thead><td>Course</td><td>Name</td><td>School Year Taken</td><td>Willingness</td></thead>
        <?php
          foreach($results as $row) {
            echo "<tr><td>".$row["Class"]."</td><td>".$row["Name"]."</td><td>".$row["Year"]."</td><td style=\"padding-left: 20px;\">";
            echo "<input class=\"willingness\" id=\"".$row["id"]."\" data-slider-id=\"slider".$row["id"]."\" type=\"text\" data-slider-min=\"1\" data-slider-max=\"5\" data-slider-step=\"1\" data-slider-value=\"".$row["Willingness"]."\"/></td></tr>";
          }
        ?>
      </table>
      <div class="col-md-12" style="text-align:center;">
        <button class="btn btn-primary" id="import">Update your classes</button>
      </div>
    <?php } ?>
    </div>
    <div id="savefloat" data-toggle="tooltip" data-placement="left" title="Save Status">
      <span class="glyphicon glyphicon-ok-sign" id="savefloaticon"></span>
    </div>
  <script>
  //$(document).ready(function() {
    $("#import").clickload({
      during:"Importing...",
      after:"Imported!",
      url:"/api/importclasses.php",
      method:"GET",
      success: function(data) {
        window.location.reload();
      },
      error: function() {return "Error!";}
    });
    $(".updatable").focusout(function() {
      $("#savefloaticon").removeClass("glyphicon-ok-sign").addClass("glyphicon-cloud-upload");
      $.post("/api/updatefield.php",{field:$(this).attr("name"),value:$(this).val()},showSaved);
    });
    $(".willingness").slider();
    $(".willingness").change(function() {
      $("#savefloaticon").removeClass("glyphicon-ok-sign").addClass("glyphicon-cloud-upload");
      $.post("/api/updatefield.php",{field:"willingness",course:$(this).attr("name"),value:$(this).val()},showSaved);
    });
    function showSaved() {
      $("#savefloaticon").addClass("glyphicon-ok-sign").removeClass("glyphicon-cloud-upload");
    };
    $("#savefloat").tooltip();
    $("#newstarttime").timeEntry()
    $("#newendtime").timeEntry();
    $("#addnewtime").click(function() {
      var n = $(this);
      $("#savefloaticon").removeClass("glyphicon-ok-sign").addClass("glyphicon-cloud-upload");
      if($("#newstarttime").val()=="" || $("#newendtime").val()=="") {
        alert("Please enter a start and end time before trying to add an availability slot");
        return;
      }
      var starttime = moment($("#newstarttime").val(),"hh:mmAA");
      var endtime = moment($("#newendtime").val(),"hh:mmAA");
      if(!starttime.isValid()||!endtime.isValid()||!starttime.isBefore(endtime)) {
        alert("The end time must be after the start time.");
        return;
      }

      $.post("/api/updateavailability.php",{"action":"create","day":$("#daydropdown option:selected").text(),"start_time":starttime.format("HH:mm:ss"),"end_time":endtime.format("HH:mm:ss")},function(data) {
        if(data=="error") {
          alert("There was a problem adding this entry.");
          return;
        }
        console.log(n.parent().parent());
        n.parent().parent().before("<tr><td>"+$("#daydropdown option:selected").text()+"</td><td>"+starttime.format("HH:mm:ss")+"</td><td>"+endtime.format("HH:mm:ss")+"</td><td id=\""+data+"\"><span class=\"glyphicon glyphicon-pencil\"></span><span class=\"glyphicon glyphicon-remove\"></span></td></tr>");
        $("#newstarttime").val("");
        $("#newendtime").val("");
        showSaved();
      });
    });
    $("#available_table").on("click",".glyphicon-remove",function() {
      var n = $(this);
      $("#savefloaticon").removeClass("glyphicon-ok-sign").addClass("glyphicon-cloud-upload");
      $.post("/api/updateavailability.php",{"action":"delete","id":n.parent().attr("id")},function(data) {
        n.parent().parent().remove();
        showSaved();
      });
    });
    $("#available_table").on("click",".glyphicon-pencil",function() {
      var n = $(this);
      var day = n.parent().parent().children().first().text();
      var start = unparseTime(n.parent().parent().children().slice(1).first().text());
      var end = unparseTime(n.parent().parent().children().slice(2).first().text());
      $("#daydropdown").val(nameToNumber(day));
      $("#newstarttime").val(start);
      $("#newendtime").val(end);
      $.post("/api/updateavailability.php",{"action":"delete","id":n.parent().attr("id")},function(data) {
        n.parent().parent().remove();
      });
    });
    function nameToNumber(name) {
      switch(name) {
        case "Monday": return 1; break;
        case "Tuesday": return 2; break;
        case "Wednesday": return 3; break;
        case "Thursday": return 4; break;
        case "Friday": return 5; break;
        case "Saturday": return 6; break;
        case "Sunday": return 7; break;
      }
    }
  //});
  </script>
</body>
</html>
