<?php
  //Are we already logged in?
  require_once("lib/authlib.php");
  if(!assertLoggedIn()) {
    header("Location: /login.php");
    die();
  }
  if(!$_SESSION["isProf"]) {
    header("Location: /offer.php");
    die();
  }
  require_once("lib/sqllib.php");
  DB::$user = 'apiaccess';
  DB::$password = 'OBLi6xKs7Fb3QJ8iWuoq';
  DB::$dbName = 'helpdb';
  DB::$host = 'localhost';
  $userdata = DB::queryFirstRow("SELECT * FROM Teacher WHERE email=%s",$_SESSION["username"]);
  $results = DB::query("CALL profferPageList(%s);",$_SESSION["username"]);
  $aresults = DB::query("SELECT * FROM HasHelpHours WHERE tutor_email = %s ORDER BY FIELD(day, 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY');",$_SESSION["username"]);
  $taresults = DB::query("CALL getTAsByTeacher(%s);",$_SESSION["username"]);

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
          <li class="list-group-item"><input class="form-control updatable" id="name" name="TName" placeholder="Your Name" value="<?php echo $userdata["TName"]; ?>"></li>
          <li class="list-group-item"><input class="form-control updatable" id="location" name="office_location" placeholder="Where You Work" value="<?php echo $userdata["office_location"]; ?>"></li>
        </ul>
      </div>
      <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading">When You Can Help</div>
        <div class="panel-body">
          <p>Students will see this when searching for a class you teach or have taught.</p>
        </div>

        <table class="table table-striped table-bordered" id="available_table">
          <thead><td>Day</td><td>Start Time</td><td>End Time</td><td>Actions</td></thead>
          <?php
            foreach($aresults as $row) {
              echo "<tr><td>".$row["day"]."</td><td>".$row["start_time"]."</td><td>".$row["end_time"]."</td>";
              echo "<td id=\"".$row["id"]."\"><span class=\"glyphicon glyphicon-pencil\"></span><span class=\"glyphicon glyphicon-remove availability_remove\"></span></td></tr>";
            }
          ?>
          <tr><td><?php echo dayDropdown("daydropdown"); ?></td><td><input id="newstarttime" type="text"></td><td><input id="newendtime" type="text"></td><td><span class="glyphicon glyphicon-plus" id="addnewtime"></span></td></tr>
        </table>
      </div>
      <div class="panel panel-default">
        <!-- Default panel contents -->
        <div class="panel-heading">Who's helping you?</div>
        <div class="panel-body">
          <p>Everyone needs help. Even those with PhDs. <small><i>Especially those with PhDs.</i></small> Put your TAs below and they can specify their availability! To add a TA, click the name of a specific section in the list of courses you teach.</p>
        </div>
        <table class="table table-striped table-bordered" id="ta_table">
          <thead><td>Username</td><td>Course</td><td>Quarter</td><td>Remove</td></thead>
          <?php
          function quarterToName($quarter="00") {
            switch($quarter) {
              case "10": return "Fall";
              case "20": return "Winter";
              case "30": return "Spring";
              case "40": return "Summer";
              default: return "At some point during";
            }
          }
          foreach($taresults as $row) {
            echo "<tr><td>".$row["student_email"]."</td><td>".$row["Class"]."-".sprintf("%02d", $row["Section"])."</td><td>".quarterToName($row["Quarter"])." ".$row["Year"]."</td><td><span id=\"".$row["id"].$row["student_email"]."\" data-class=\"".$row["id"]."\" data-username=\"".$row["student_email"]."\" class=\"glyphicon glyphicon-remove ta_remove\"></span></td></tr>";
          }
          ?>
        </table>
      </div>
      <table class="table table-striped table-bordered">
        <thead><td>Course</td><td>Name</td><td>Quarter Taught</td><td>Can Help With Course</td></thead>
        <?php
        foreach($results as $row) {
          echo "<tr><td>".$row["Class"]."-".sprintf("%02d", $row["Section"])."</td><td class=\"coursename\" data-id=\"".$row["id"]."\">".$row["Name"]."</td><td>".quarterToName($row["Quarter"])." ".$row["Year"]."</td>";
          echo "<td style=\"text-align:center;\"><input type=\"checkbox\" class=\"canhelpbox\" data-about=\"".$row["Class"]."\" id=\"".$row["id"]."\" ".(($row["canHelp"]==0)?"checked":"")."></td></tr>";
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
      url:"/api/profimportclasses.php",
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
    $("#available_table").on("click",".availability_remove",function() {
      var n = $(this);
      $("#savefloaticon").removeClass("glyphicon-ok-sign").addClass("glyphicon-cloud-upload");
      $.post("/api/updateavailability.php",{"action":"delete","id":n.parent().attr("id")},function(data) {
        n.parent().parent().remove();
        showSaved();
      });
    });
    $("#ta_table").on("click",".ta_remove",function() {
      var n = $(this);
      $("#savefloaticon").removeClass("glyphicon-ok-sign").addClass("glyphicon-cloud-upload");
      $.post("/api/updateta.php",{"action":"delete","student_email":$(this).attr("data-username"),"course":$(this).attr("data-class")},function(data) {
        n.parent().parent().remove();
        showSaved();
      });
    });
    $(".coursename").click(function() {
      var username = prompt("What is the username for the TA?");
      var n = $(this);
      $("#savefloaticon").removeClass("glyphicon-ok-sign").addClass("glyphicon-cloud-upload");
      $.post("/api/updateta.php",{"action":"create","student_email":username,"course":$(this).attr("data-id")},function() {
        $("#ta_table").append("<tr><td>"+username+"</td><td>"+n.parent().children().first().text()+"</td><td>"+n.parent().children().slice(2).first().text()+"</td><td><span id=\""+n.attr("data-id")+username+"\" data-class=\""+n.attr("data-id")+"\" data-username=\""+username+"\" class=\"glyphicon glyphicon-remove ta_remove\"></span></td></tr>");
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
    $(".canhelpbox").change(function() {
      $("#savefloaticon").removeClass("glyphicon-ok-sign").addClass("glyphicon-cloud-upload");
      var checked = this.checked;
      $(".canhelpbox[data-about="+$(this).attr("data-about")+"]").prop("checked",checked);
      $.post("/api/updateavailability.php",{"action":"canhelp","id":$(this).attr("id"),"canhelp":checked?"0":"1"},function(data) {
        showSaved();
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
