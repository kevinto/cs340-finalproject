<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include 'storedInfo.php';

// Test MYSQL connection
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", $myUsername, $myPassword, $myUsername);
if ($mysqli->connect_errno) {
  echo "Failed to connect to MYSQL <br>";
}

echo "hello12345s";

?>