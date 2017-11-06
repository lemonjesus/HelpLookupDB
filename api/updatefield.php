<?php
require_once("../lib/authlib.php");
require_once("../lib/sqllib.php");

if(!assertLoggedIn()) die();

DB::$user = 'apiaccess';
DB::$password = 'OBLi6xKs7Fb3QJ8iWuoq';
DB::$dbName = 'helpdb';
DB::$host = 'localhost';

if($_POST["field"]=="willingness") {
  DB::update('Takes', array("willingness" => $_POST["value"]), "student_email=%s AND course=%s", $_SESSION["username"], $_POST["course"]);
} else {
  DB::query("CALL updateInfo(%s,%s,%s)",$_SESSION["username"],$_POST["field"],$_POST["value"]);
}
?>
