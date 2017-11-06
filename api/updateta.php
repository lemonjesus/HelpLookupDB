<?php
require_once("../lib/authlib.php");
require_once("../lib/sqllib.php");

if(!assertLoggedIn()) die();
if(!$_SESSION["isProf"]) die();

DB::$user = 'apiaccess';
DB::$password = 'OBLi6xKs7Fb3QJ8iWuoq';
DB::$dbName = 'helpdb';
DB::$host = 'localhost';

if($_POST["action"]=="create") {
  DB::insert('Tutors', array("student_email"=>$_POST["student_email"],"course"=>$_POST["course"]));
} else if($_POST["action"]=="delete") {
  DB::delete("Tutors", "student_email=%s AND course=%s",$_POST["student_email"],$_POST["course"]);
}
?>
