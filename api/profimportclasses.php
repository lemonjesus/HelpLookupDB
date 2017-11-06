<?php
//Import classes in what's probably the dumbest way possible
require_once("../lib/simple_html_dom.php");
require_once("../lib/authlib.php");
require_once("../lib/sqllib.php");

if(!assertLoggedIn()) die();
if(!$_SESSION["isProf"]) die();

DB::$user = 'apiaccess';
DB::$password = 'OBLi6xKs7Fb3QJ8iWuoq';
DB::$dbName = 'helpdb';
DB::$host = 'localhost';

//Now, let's get some classes from the schedule look up page. Strap in, kiddos, this is going to suck
$terms = array("201810","201710","201720","201730","201740","201610","201620","201630","201640","201510","201520","201530","201540");

foreach($terms as $term) {
  //Fetch the data for this term
  $process = curl_init("https://prodweb.rose-hulman.edu/regweb-cgi/reg-sched.pl");
  curl_setopt($process, CURLOPT_HEADER, 1);
  $headers = array(
    'Content-Type:application/x-www-form-urlencoded',
    'Authorization: Basic '.$_SESSION["authstring"]
  );
  curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($process, CURLOPT_POST, 1);
  curl_setopt($process, CURLOPT_POSTFIELDS, "termcode=$term&view=tgrid&id1=".$_SESSION["username"]."&bt1=ID%2FUsername&id4=&id5=");
  curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
  $return = curl_exec($process);
  curl_close($process);

  //Parse the page we get back
  $html = str_get_html($return);
  $classtable = $html->find('table', 1);
  $rows = $classtable->find('tr');
  array_shift($rows);
  foreach($rows as $row) {
    $cols = $row->find('td');
    $course = explode("-",$cols[0]->plaintext);
    $year = substr($term,0,4);
    $sem = substr($term,-2);

    DB::queryRaw("CALL importProfCourse(%s,%s,%d,%d,%d,%s);",$_SESSION["username"],$course[0],$course[1],$year,$sem,$cols[2]->plaintext);
  }
}

header('Content-Type: application/json');
die('{"response":"OK"}');

?>
