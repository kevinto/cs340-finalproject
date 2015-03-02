<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

include 'storedInfo.php';

// Test MYSQL connection
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", $myUsername, $myPassword, $myUsername);
if ($mysqli->connect_errno) {
  echo "Failed to connect to MYSQL <br>";
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && count($_GET) > 0) {
  if (isset($_GET['getCustNames']) && $_GET['getCustNames'] == true) {
    // Get list of all customer names

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT first_name, last_name, social_security_num FROM customer;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    if (!$stmt->execute()) {
      echo "Execute failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    
    if (!($res = $stmt->get_result())) {
      echo "Getting result set failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    // No results were returned, exit.
    if ($res->num_rows === 0) {
      return;
    } 

    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      $first_name = $row['first_name']; 
      $last_name = $row['last_name']; 
      $so_sec_num = $row['social_security_num'];
      $full_name = $first_name . ' ' . $last_name;

      // echo "<option value=\"$so_sec_num\">$full_name</option>\n";
      // Need to return JSON
      $json_return = json_encode($row);
      var_dump(json_return);
  }
}
?>