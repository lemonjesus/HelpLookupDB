<?php
require_once("../lib/authlib.php");
require_once("../lib/sqllib.php");

if(!assertLoggedIn()) die();
if(!$_SESSION["isProf"] && !$_SESSION["isTutor"]) die();

DB::$user = 'apiaccess';
DB::$password = 'OBLi6xKs7Fb3QJ8iWuoq';
DB::$dbName = 'helpdb';
DB::$host = 'localhost';

if($_POST["action"]=="create") {
  $id = md5(microtime());
  DB::insert('HasHelpHours', array("id"=>$id,"tutor_email"=>$_SESSION["username"],"day"=>$_POST["day"],"start_time"=>$_POST["start_time"],"end_time"=>$_POST["end_time"]));
  die($id);
} else if($_POST["action"]=="edit") {
  DB::update('HasHelpHours', array("day"=>$_POST["day"],"start_time"=>$_POST["start_time"],"end_time"=>$_POST["end_time"]), "id=%s", $_POST["id"]);
} else if($_POST["action"]=="delete") {
  DB::delete("HasHelpHours", "id=%s", $_POST["id"]);
} else if($_POST["action"]=="canhelp") {
  DB::query("CALL canHelpUpdate(%s,%s,%s)",$_SESSION["username"],$_POST["canhelp"],$_POST["id"]);
}
?>
