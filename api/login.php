<?php
require_once("../lib/authlib.php");

header('Content-Type: application/json');

$auth = $_POST["auth"];
if(login($auth)) die('{"response":"OK"}');
else die('{"response":"wrong_login"}');
?>
