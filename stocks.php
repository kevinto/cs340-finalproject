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

    // Get JSON object of all customers
    if (isset($_GET['getAllCustInfo'])) {
      getAllCustomerRecords();
      die();
    }

    // Get JSON object of all stock symbols
    if (isset($_GET['getAllStockSym'])) {
      getAllStockSymbols();
      die();
    }

    // Get view of one customer
    if (isset($_GET['getOneCustInfo']) && isset($_GET['ssNum'])) {
      getSingleCustomerInfo($_GET['ssNum']);
      die();
    }

    // Insert one new customer
    if (isset($_GET['insertNewCust']) && isset($_GET['firstName']) 
      && isset($_GET['lastName']) && isset($_GET['soSecNum'])) {

      // Check if there are any empty parameters
      if ($_GET['firstName'] !== '' && $_GET['lastName'] !== '' 
        && $_GET['soSecNum'] !== '') {

        insertNewCustomer($_GET['firstName'], $_GET['lastName'], $_GET['soSecNum']);
        die();
      }
      else {
        echo 'EmptyParams';
        die();
      }
    }

    // Insert one new stock 
    if (isset($_GET['insertNewStk']) && isset($_GET['stkName']) 
      && isset($_GET['stkSym']) && isset($_GET['stkPrice'])) {

      // Check if there are any empty parameters
      if ($_GET['stkName'] !== '' && $_GET['stkSym'] !== '' 
        && $_GET['stkPrice'] !== '') {

        insertNewStk($_GET['stkName'], $_GET['stkSym'], $_GET['stkPrice']);
        die();
      }
      else {
        echo 'EmptyParams';
        die();
      }
    }

    // Insert one new fee 
    if (isset($_GET['insertNewFee']) && isset($_GET['feeName']) 
      && isset($_GET['feeAmt']) && isset($_GET['custSsId'])) {

      // Check if there are any empty parameters
      if ($_GET['feeName'] !== '' && $_GET['feeAmt'] !== '' 
        && $_GET['custSsId'] !== '') {

        insertNewFee($_GET['feeName'], $_GET['feeAmt'], $_GET['custSsId']);
        die();
      }
      else {
        echo 'EmptyParams';
        die();
      }
    }

    // Insert one new bank account 
    if (isset($_GET['insertNewBankAcct']) && isset($_GET['bankName']) 
      && isset($_GET['bankAcctNum']) && isset($_GET['custSsId'])) {

      // Check if there are any empty parameters
      if ($_GET['bankName'] !== '' && $_GET['bankAcctNum'] !== '' 
        && $_GET['custSsId'] !== '') {

        insertNewBankAcct($_GET['bankName'], $_GET['bankAcctNum'], $_GET['custSsId']);
        die();
      }
      else {
        echo 'EmptyParams';
        die();
      }
    }

    // Insert one new stock order 
    if (isset($_GET['insertNewStockOrder']) && isset($_GET['ordStatus']) 
      && isset($_GET['ordType']) && isset($_GET['qtyOrdered'])
      && isset($_GET['ordStartDate']) && isset($_GET['stkSym'])
      && isset($_GET['custSsId'])) {

      // Check if there are any empty parameters
      if ($_GET['ordStatus'] !== '' && $_GET['ordType'] !== '' 
        && $_GET['qtyOrdered'] !== '' && $_GET['ordStartDate'] !== '' 
        && $_GET['stkSym'] !== '' && $_GET['custSsId'] !== '') {

        insertNewStockOrder($_GET['ordStatus'], $_GET['ordType'], $_GET['qtyOrdered'], 
          $_GET['ordStartDate'], $_GET['stkSym'], $_GET['custSsId']);

        die();
      }
      else {
        echo 'EmptyParams';
        die();
      }
    }

    // Insert one new customer owned stock 
    if (isset($_GET['insertNewStockOwn']) && isset($_GET['custSsNum']) 
      && isset($_GET['stkSym']) && isset($_GET['qtyOwned'])) {

      // Check if there are any empty parameters
      if ($_GET['custSsNum'] !== '' && $_GET['stkSym'] !== '' 
        && $_GET['qtyOwned'] !== '') {

        insertNewStockOwn($_GET['custSsNum'], $_GET['stkSym'], $_GET['qtyOwned']);
        die();
      }
      else {
        echo 'EmptyParams';
        die();
      }
    }
  }

  function insertNewStockOwn($custSsNum, $stkSym, $qtyOwned) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO has_stocks(cust_id, stock_id, amount) 
      VALUES ((SELECT id FROM customer WHERE social_security_num=? limit 1), 
        (SELECT id FROM stocks WHERE stock_symbol=? limit 1), ?)"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("iii", $custSsNum, $stkSym, $qtyOwned)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  function insertNewStockOrder($ordStatus, $ordType, $qtyOrdered, $ordStartDate, $stkSym, $custSsId) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO stock_orders(order_status, order_type, qty_ordered, order_start_date, cust_id, stock_id) 
      VALUES (?, ?, ?, ?, 
        (SELECT id FROM customer WHERE social_security_num=?), 
        (SELECT id FROM stocks WHERE stock_symbol=?))"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("ssisis", $ordStatus, $ordType, $qtyOrdered, $ordStartDate, $custSsId, $stkSym)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  function insertNewBankAcct($bankName, $bankAcctNum, $custSsId) {
    global $mysqli;

    $custId = getCustomerDbKey($custSsId);

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO bank_acct(bank_name, account_num, cust_id) VALUES (?, ?, ?)"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("sii", $bankName, $bankAcctNum, $custId)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  function insertNewFee($feeName, $feeAmt, $custSsId) {
    global $mysqli;

    $custId = getCustomerDbKey($custSsId);

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO fees(fee_name, fee_amount, cust_id) VALUES (?, ?, ?)"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("sii", $feeName, $feeAmt, $custId)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  function insertNewStk($stockName, $stockSymbol, $stockPrice) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO stocks(stock_name, stock_symbol, stock_price) VALUES (?, ?, ?)"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("ssi", $stockName, $stockSymbol, $stockPrice)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  function insertNewCustomer($firstName, $lastName, $soSecNum) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO customer(first_name, last_name, social_security_num) VALUES (?, ?, ?)"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("ssi", $firstName, $lastName, $soSecNum)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  function getCustomerDbKey($ssNum) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT id FROM customer WHERE social_security_num=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

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

    return $row['id'];
  }

  function getSingleCustomerInfo($ssNum) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT first_name, last_name, social_security_num FROM customer WHERE social_security_num=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

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

  function getAllStockSymbols() {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT stock_symbol FROM stocks;"))) {
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

      $stocksArr[] = json_encode($row);
    }

    $finaljson = json_encode($stocksArr);
    echo $finaljson;
  }

  function getStockDbKey($stkSym) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT id FROM stocks WHERE stock_symbol=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    if (!$stmt->bind_param("s", $stkSym)) {
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

    return $row['id'];
  }
?>
