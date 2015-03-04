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
    if (isset($_GET['getAllCustInfo'])) {
      getAllCustomerRecords();
    }

    if (isset($_GET['getOneCustInfo']) && isset($_GET['ssNum'])) {
      getSingleCustomerInfo();
    }
  }

  function getSingleCustomerInfo() {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT first_name, last_name, social_security_num FROM customer WHERE social_security_num=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    $ssNum = $_GET['ssNum'];
    if (!$stmt->bind_param("i", $ssNum)) {
          echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
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

    $res->data_seek(0);
    $row = $res->fetch_assoc();

    $finaljson = json_encode($row);
    echo $finaljson;
  }

  function getAllCustomerRecords() {
    global $mysqli;

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

      $customerArr[] = json_encode($row);
    }

    $finaljson = json_encode($customerArr);
    echo $finaljson;
  }
?>
