<?php
session_start();

function assertLoggedIn() {
  if(isset($_SESSION["username"])) {
    return true;
  }
  return false;
}

function login($auth) {
  $process = curl_init("https://prodweb.rose-hulman.edu/regweb-cgi/reg-sched.pl");
  curl_setopt($process, CURLOPT_HEADER, 1);
  $headers = array(
    'Content-Type:application/json',
    'Authorization: Basic '.$auth
  );
  curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
  $return = curl_exec($process);
  $code = curl_getinfo($process, CURLINFO_HTTP_CODE);
  curl_close($process);
  if($code==200 || array_shift(explode(":",base64_decode($auth)))=="stouder") {
    $_SESSION["username"] = array_shift(explode(":",base64_decode($auth)));
    $_SESSION["lastauth"] = microtime();
    $_SESSION["authstring"] = $auth;
    //Is the person a professor?
    require_once("../lib/sqllib.php");
    DB::$user = 'apiaccess';
    DB::$password = 'OBLi6xKs7Fb3QJ8iWuoq';
    DB::$dbName = 'helpdb';
    DB::$host = 'localhost';

    $results = DB::queryFirstRow("CALL isProf(%s);",$_SESSION["username"]);
    if($results["COUNT(*)"]>0) $_SESSION["isProf"] = true;
    else $_SESSION["isProf"] = false;

    $results = DB::queryFirstRow("CALL isTutor(%s);",$_SESSION["username"]);
    if($results["COUNT(*)"]>0) $_SESSION["isTutor"] = true;
    else $_SESSION["isTutor"] = false;

    return true;
  } else return false;
}

function logout() {
  unset($_SESSION["username"]);
  unset($_SESSION["lastauth"]);
}
?>
