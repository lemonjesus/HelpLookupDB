<?php
require_once("../lib/sqllib.php");

DB::$user = 'apiaccess';
DB::$password = 'OBLi6xKs7Fb3QJ8iWuoq';
DB::$dbName = 'helpdb';
DB::$host = 'localhost';

$query = strtolower($_GET["q"])."%";
if(empty($query)) die();

//echo "SELECT * FROM Autocomplete WHERE string LIKE '".$query."%%'";
$result = DB::queryFirstRow("SELECT * FROM Autocomplete WHERE LOWER(string) LIKE %s",$query);
echo substr($result["string"],strlen($query)-1);

?>
