<?php
  // File: stocks.php
  // Description: This file contains the backend code for the stocks.html file

  error_reporting(E_ALL);
  ini_set('display_errors', 'On');

  include 'storedInfo.php';

  // Test MYSQL connection. The authentication information is in a separate file
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

    // Get JSON object of all stock names
    if (isset($_GET['getAllStockNames'])) {
      getAllStockNames();
      die();
    }

    // Get JSON object of all open stock orders
    if (isset($_GET['getAllOpenOrders'])) {
      getAllOpenOrders();
      die();
    }

    // Get JSON objects for the customer bank accounts
    if (isset($_GET['getCustBankInfo']) && isset($_GET['ssNum'])) {
      getCustBankInfo($_GET['ssNum']);
      die();
    }

    // Get JSON object of one customer
    if (isset($_GET['getOneCustInfo']) && isset($_GET['ssNum'])) {
      getSingleCustomerInfo($_GET['ssNum']);
      die();
    }

    // Get JSON object of one stock
    if (isset($_GET['getOneStkInfo']) && isset($_GET['stkSym'])) {
      getOneStkInfo($_GET['stkSym']);
      die();
    }

    // Get JSON objects of all fees for one customer
    if (isset($_GET['getCustFeeInfo']) && isset($_GET['custSsNum'])) {
      getCustFeeInfo($_GET['custSsNum']);
      die();
    }

    // Get JSON objects of all the stocks the customer owns
    if (isset($_GET['getCustStkOwnInfo']) && isset($_GET['custSsNum'])) {
      getCustStkOwnInfo($_GET['custSsNum']);
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

    // Delete a single customer
    if (isset($_GET['deleteCustomer']) && isset($_GET['custSsNum'])) {
      deleteCustomer($_GET['custSsNum']);
      die();
    }

    // Update a single stock price
    if (isset($_GET['updateStockPrice']) && isset($_GET['newStkPrice']) && isset($_GET['stkSym'])) {
      updateStockPrice($_GET['newStkPrice'], $_GET['stkSym']);
      die();
    }
  }

  /*
  * Purpose: Associates a customer with a stock and sets the quantity
  * @param {string} $custSsNum - customer SS number
  * @param {string} $stkSym - the stock symbol
  * @param {int} $qtyOwned - amount of stock owned
  */
  function insertNewStockOwn($custSsNum, $stkSym, $qtyOwned) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO has_stocks(cust_id, stock_id, amount)
      VALUES ((SELECT id FROM customer WHERE social_security_num=? limit 1),
        (SELECT id FROM stocks WHERE stock_symbol=? limit 1), ?);"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("isi", $custSsNum, $stkSym, $qtyOwned)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  /*
  * Purpose: Inserts a new stock order
  * @param {string} $ordStatus - the order status
  * @param {string} $ordType - the order type
  * @param {int} $qtyOrdered - the quantity ordered
  * @param {dateTime} $ordStartDate - the order start date
  * @param {string} $stkSym - the stock symbol
  * @param {string} $custSsId - the customer social security number
  */
  function insertNewStockOrder($ordStatus, $ordType, $qtyOrdered, $ordStartDate, $stkSym, $custSsId) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO stock_orders(order_status, order_type, qty_ordered, order_start_date, cust_id, stock_id)
      VALUES (?, ?, ?, ?,
        (SELECT id FROM customer WHERE social_security_num=? limit 1),
        (SELECT id FROM stocks WHERE stock_symbol=? limit 1))"))) {
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

  /*
  * Purpose: Inserts a new bank account for a customer
  * @param {string} $bankName - the bank nume
  * @param {int} $bankAcctNum - the bank account number
  * @param {int} $custSsId - the customer social security number
  */
  function insertNewBankAcct($bankName, $bankAcctNum, $custSsId) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO bank_acct(bank_name, account_num, cust_id) 
      VALUES (?, ?, (SELECT id FROM customer WHERE social_security_num=?))"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("sii", $bankName, $bankAcctNum, $custSsId)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }
  }

  /*
  * Purpose: Inserts a new fee for a customer
  * @param {string} $feeName - the fee nume
  * @param {int} $feeAmt - the fee amount
  * @param {int} $custSsId - the customer social security number
  */
  function insertNewFee($feeName, $feeAmt, $custSsId) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO fees(fee_name, fee_amount, cust_id) 
      VALUES (?, ?, (SELECT id FROM customer WHERE social_security_num=?))"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("sii", $feeName, $feeAmt, $custSsId)) {
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
    if (!($stmt = $mysqli->prepare("INSERT INTO stocks(stock_name, stock_symbol, stock_price) 
      VALUES (?, ?, ?)"))) {
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

  /*
  * Purpose: Inserts a new customer
  * @param {string} $firstName - the first nume
  * @param {int} $lastName - the last name
  * @param {int} $soSecNum - the customer social security number
  */
  function insertNewCustomer($firstName, $lastName, $soSecNum) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("INSERT INTO customer(first_name, last_name, social_security_num) 
      VALUES (?, ?, ?)"))) {
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

  /*
  * Purpose: Gets a single customer's information
  * @param {int} $ssNum - the customer social security number
  * @return {object} - a JSON object containing the customer information
  */
  function getSingleCustomerInfo($ssNum) {
    global $mysqli;

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

    // Generate the return object
    $res->data_seek(0);
    $row = $res->fetch_assoc();

    $finaljson = json_encode($row);
    echo $finaljson;
  }

  /*
  * Purpose: Gets information for one stock 
  * @param {string} $stkSym - the stock symbol
  * @return {object} - a JSON object containing the stock information
  */
  function getOneStkInfo($stkSym) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT stock_symbol, stock_name, stock_price 
      FROM stocks WHERE stock_symbol=?;"))) {
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

    // Generate the return object
    $res->data_seek(0);
    $row = $res->fetch_assoc();

    $finaljson = json_encode($row);
    echo $finaljson;
  }

  /*
  * Purpose: Gets all the customer records
  * @return {object} - a JSON object containing the customer information
  */
  function getAllCustomerRecords() {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT first_name, last_name, social_security_num 
      FROM customer;"))) {
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

    // Generate an array of all the customer objects
    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      $customerArr[] = json_encode($row);
    }

    // Return a JSON array of all the customers
    $finaljson = json_encode($customerArr);
    echo $finaljson;
  }

  /*
  * Purpose: Gets all the stock symbols 
  * @return {object} - a JSON object containing the stock symbols 
  */
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

    // Generate the return object
    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      $stocksArr[] = json_encode($row);
    }

    $finaljson = json_encode($stocksArr);
    echo $finaljson;
  }

  /*
  * Purpose: Gets all the stock names and associated symbols
  * @return {object} - a JSON object containing the stock symbols and names 
  */
  function getAllStockNames() {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT stock_symbol, stock_name FROM stocks;"))) {
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

    // Generate return object
    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      $stocksArr[] = json_encode($row);
    }

    $finaljson = json_encode($stocksArr);
    echo $finaljson;
  }

  /*
  * Purpose: Gets all the customer's fees 
  * @param {int} $custSsId - the customer ss id
  * @return {object} - a JSON object all the customer's fees
  */
  function getCustFeeInfo($custSsId) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT fee_name, fee_amount FROM fees WHERE cust_id in
      (SELECT id from customer where social_security_num=?);"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    if (!$stmt->bind_param("i", $custSsId)) {
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
      echo "noRecords";
      return;
    }

    // Generate the return object
    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      $stocksArr[] = json_encode($row);
    }

    $finaljson = json_encode($stocksArr);
    echo $finaljson;
  }
  
  /*
  * Purpose: Gets all the customer's stocks
  * @param {int} $custSsId - the customer ss id
  * @return {object} - a JSON object all the customer's stocks 
  */
  function getCustStkOwnInfo($custSsId) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT s.stock_name, s.stock_symbol, s.stock_price, hs.amount as amount_stock_owned
      FROM customer c INNER JOIN has_stocks hs ON c.id = hs.cust_id
      INNER JOIN stocks s ON hs.stock_id = s.id
      WHERE c.social_security_num = ?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    if (!$stmt->bind_param("i", $custSsId)) {
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
      echo "noRecords";
      return;
    }

    // Generate the return object
    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      $stocksArr[] = json_encode($row);
    }

    $finaljson = json_encode($stocksArr);
    echo $finaljson;
  }

  /*
  * Purpose: Gets stock orders with the 'open' status
  * @return {object} - a JSON object all the open stock orders 
  */
  function getAllOpenOrders() {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT order_status, order_type, qty_ordered,
      order_start_date, CONCAT(c.first_name,' ',c.last_name) as customer_name,
      s.stock_name
      FROM stock_orders so INNER JOIN customer c ON so.cust_id=c.id
      INNER JOIN stocks s ON so.stock_id = s.id WHERE order_status = 'open'"))) {
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
      echo "noRecords";
      return;
    }

    // Generate the return object
    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      $stocksArr[] = json_encode($row);
    }

    $finaljson = json_encode($stocksArr);
    echo $finaljson;
  }

  /*
  * Purpose: Gets the customer's bank information
  * @param {int} $custSsId - the customer ss id
  * @return {object} - a JSON object all the bank information
  */ 
  function getCustBankInfo($custSsId) {
    global $mysqli;

    // Prepare the select statment
    if (!($stmt = $mysqli->prepare("SELECT bank_name, account_num
      from bank_acct ba INNER JOIN customer c ON ba.cust_id = c.id
      WHERE c.social_security_num = ?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    if (!$stmt->bind_param("i", $custSsId)) {
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
      echo "noRecords";
      return;
    }

    // Generate the return object
    for($row_no = ($res->num_rows - 1); $row_no >= 0; $row_no--) {
      $res->data_seek($row_no);
      $row = $res->fetch_assoc();

      $stocksArr[] = json_encode($row);
    }

    $finaljson = json_encode($stocksArr);
    echo $finaljson;
  }

  /*
  * Purpose: deletes a customer
  * @param {int} $custSsId - the customer social security number
  * @return {string} - a string denoting whether delete was successful or not
  */
  function deleteCustomer($custSsId) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("DELETE FROM customer
      WHERE social_security_num=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("i", $custSsId)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    echo "deleteSuccessful";
  }

  /*
  * Purpose: updates a stock price
  * @param {int} $newPrice - the new stock price
  * @param {int} $stkSym - the stock symbol
  * @return {string} - a string denoting whether the update was successful or not
  */
  function updateStockPrice($newPrice, $stkSym) {
    global $mysqli;

    // Prepare the insert statment
    if (!($stmt = $mysqli->prepare("UPDATE stocks SET stock_price=?
      WHERE stock_symbol=?;"))) {
      echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
      die();
    }

    // Add values to SQL insert statement
    if (!$stmt->bind_param("is", $newPrice, $stkSym)) {
      echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    // Execute sql statement
    if (!$stmt->execute()) {
      echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      die();
    }

    echo "updateSuccessful";
  }
?>
