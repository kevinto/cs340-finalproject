// File: stocks.js


// GENERIC HELPER FUNCTIONS ------------------------------------------

/*
* Adds a table for the database records. The table will contain
* header columns based off the JSON property names.
* @param {string} targetDiv - id of the Div you want to insert the
*                                              table into.
* @param {array} dispObjArray - an array of JSON objects containing
*                                                     database tuples
*/
function addTable(targetDiv, dispObjArray) {

  var myTableDiv = document.getElementById(targetDiv);

  var table = document.createElement('table');
  table.border='1';

  // Create the header columns
  var headersAlreadyCreated = false
  for (var i = 0; i < dispObjArray.length; i++){
    if (!headersAlreadyCreated) {
      var tr = document.createElement('tr');
      table.appendChild(tr);
      for (var property in dispObjArray[i]) {
        if (dispObjArray[i].hasOwnProperty(property)) {

          var th = document.createElement('th');
          th.appendChild(document.createTextNode(property));
          tr.appendChild(th);
        }
      }

      headersAlreadyCreated = true;
    }

    // Create the rows for the data
    tr = document.createElement('tr');
    table.appendChild(tr);
    for (var property in dispObjArray[i]) {
      if (dispObjArray[i].hasOwnProperty(property)) {

        var td = document.createElement('td');
        td.appendChild(document.createTextNode(dispObjArray[i][property]));
        tr.appendChild(td);
      }
    }
  }

  myTableDiv.appendChild(table);
}

/*
* Calls the backend PHP code
* @param {string} phpFuncName - action you want the backend PHP
*                                                       to perform
* @param {object} returnFunc - function that is executed after PHP
*                                                  function is done executing
* @param {object} optionalParams - optional params you want to pass
*                                                         to the PHP backend
*/
// Here optional parameters is supposed to be an array
function callStockPhp(phpFuncName, returnFunc, optionalParams) {
  if (typeof(optionalParams) === 'undefined') {
    optionalParams = '';
  }

  var request = new XMLHttpRequest();
  var url = 'stocks.php?' + phpFuncName + '=true';

  // Need to find a way to iterate through the properties of the JS
  if (optionalParams.length !== 0) {
    for (var property in optionalParams) {
      if (optionalParams.hasOwnProperty(property)) {
        url += '&' + property + '=' + optionalParams[property];
      }
    }
  }

  if (!request){
    return false;
  }

  request.onreadystatechange = returnFunc(request);
  request.open('GET', url, true);
  request.send(null);
  return request;
}

// VIEW DATA FUNCTIONS: ----------------------------------

/*
* Populate all combo boxes that need database information
*/
function populateComboBoxes() {
  popAllCustomerComboBoxes();
  popAllStockSymbolComboBoxes();
  popAllStockNameComboBoxes();
}

/*
* Displays all 'OPEN' stock orders
*/
function populateOpenOrders() {

  // Define Async return function
  var popOpenOrdersFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var containerId = 'openStockOrdersView';
        var container = document.getElementById(containerId);

        var resultObj = JSON.parse(request.responseText);

        // Clear out div if no data is found
        if (request.responseText === 'noRecords') {
          container.innerText = 'No data returned';
          return;
        }

        // Clear out empty message
        container.innerText = '';

        // Create an array of JSON objects
        var tableParamObj = new Array();
        for (var i = 0; i < resultObj.length; i++) {
          tableParamObj.push(JSON.parse(resultObj[i]));
        }

        addTable(containerId, tableParamObj);
      }
    }
  };

  callStockPhp('getAllOpenOrders', popOpenOrdersFunc);

  return false;
}

/*
* Populate all combo boxes that require all the stocks names in the
* database
*/
function popAllStockNameComboBoxes() {
  var popStkNameComBoFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        // Create an array of all select elements that require options
        //    with stock names
        var stkContainers = new Array();
        stkContainers.push(document.getElementsByName('viewStockNameSelect'));
        stkContainers.push(document.getElementsByName('updStockPriceSelect'));

        // For each select element, add the appropriate stock options
        for (var i = 0; i < stkContainers.length; i++) {
          var objArray = JSON.parse(request.responseText);
          var currentItem;

          // Add individual stock options
          for(var j = 0; j < objArray.length; j++) {
            currentItem = JSON.parse(objArray[j]);
            stkSym = currentItem.stock_symbol;
            stkName = currentItem.stock_name;

            stkContainers[i][0].options[stkContainers[i][0].options.length] = new Option(stkName, stkSym);
          }
        }
      }
    }
  };

  callStockPhp('getAllStockNames', popStkNameComBoFunc);
}

/*
* Populate all combo boxes that require all the stocks symbols in the
* database
*/
function popAllStockSymbolComboBoxes() {

  // Create return function
  var popStkSymFunc = function(request){
    return function() {
      if(request.readyState == 4) {

        // Create an array of all select elements that require options
        var stkContainers = new Array();
        stkContainers.push(document.getElementsByName('ordStockSelectInsert'));
        stkContainers.push(document.getElementsByName('stkForStockOwnInsert'));

        // For each select element, add the appropriate stock options
        for (var i = 0; i < stkContainers.length; i++) {
          var objArray = JSON.parse(request.responseText);
          var currentItem;

          // Add individual stock options
          for(var j = 0; j < objArray.length; j++) {
            currentItem = JSON.parse(objArray[j]);
            stkSym = currentItem.stock_symbol;

            // stkContainers is an array of arrays of the select element
            stkContainers[i][0].options[stkContainers[i][0].options.length] = new Option(stkSym, stkSym);
          }
        }
      }
    }
  };
  callStockPhp('getAllStockSym', popStkSymFunc);
}

/*
* Populate all combo boxes that require all the customers in the
* database
*/
function popAllCustomerComboBoxes() {
  var popCustComBoFunc = function(request){
    return function() {
      if(request.readyState == 4) {

        // Create an array of all select elements that require options
        var custContainers = new Array();
        custContainers.push(document.getElementsByName('viewCustSelect'));
        custContainers.push(document.getElementsByName('addFeeCustSelect'));
        custContainers.push(document.getElementsByName('addCustBankSelect'));
        custContainers.push(document.getElementsByName('ordCustSelectInsert'));
        custContainers.push(document.getElementsByName('custForStockOwnInsert'));
        custContainers.push(document.getElementsByName('viewCustFeeSelect'));
        custContainers.push(document.getElementsByName('viewCustStockOwnSelect'));
        custContainers.push(document.getElementsByName('viewCustBankSelect'));
        custContainers.push(document.getElementsByName('delCustBankSelect'));

        // For each select element, add the appropriate customer options
        for (var i = 0; i < custContainers.length; i++) {
          for (var i = 0; i < custContainers.length; i++) {
            var objArray = JSON.parse(request.responseText);
            var currentItem;
            var fullName;

            // Add individual customer options
            for(var j = 0; j < objArray.length; j++) {
              currentItem = JSON.parse(objArray[j]);
              fullName = currentItem.first_name + ' ' + currentItem.last_name;

              custContainers[i][0].options[custContainers[i][0].options.length] = new Option(fullName, currentItem.social_security_num);
            }
          }
        }
      }
    }
  };

  callStockPhp('getAllCustInfo', popCustComBoFunc);
}

/*
* Finds all the stocks that a specific customer owns
*/
function findStkOwnershipInfo() {

  // Define a return function
  var viewCustStkOwnFunc = function(request){
    return function() {
      if(request.readyState == 4) {

        // Get div to populate with data
        var containerId = 'custOwnedStocksView';
        var container = document.getElementById(containerId);

        // If no data is returned, exit the function
        if (request.responseText === 'noRecords') {
          container.innerText = 'No data returned';
          return;
        }

        // Result is an array of JSON objects
        var resultObj = JSON.parse(request.responseText);

        // Clear out the empty message
        container.innerText = '';

        // Create an array of JSON objects that we will make rows
        //    out of
        var tableParamObj = new Array();
        for (var i = 0; i < resultObj.length; i++) {
          tableParamObj.push(JSON.parse(resultObj[i]));
        }

        // Generate a table
        addTable(containerId, tableParamObj);
      }
    }
  };

  // Create object that holds the SQL query parameters
  var selectedOption = document.getElementsByName('viewCustStockOwnSelect');
  selectedOption = selectedOption[0];
  var ownParams = {
    custSsNum: selectedOption.options[selectedOption.selectedIndex].value
  };

  callStockPhp('getCustStkOwnInfo', viewCustStkOwnFunc, ownParams);

  return false;
}

/*
* Finds all the fees for a specific customer
*/
function findFeeInfo() {

  // Define return function
  var viewCustFeesFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var containerId = 'custFeeView';
        var container = document.getElementById(containerId);

        // Exit, if no records found
        if (request.responseText === 'noRecords') {
          container.innerText = 'No data returned';
          return;
        }

        var resultObj = JSON.parse(request.responseText);

        // Clear out empty message
        container.innerText = '';

        // Create array of JSON database tuples
        var tableParamObj = new Array();
        for (var i = 0; i < resultObj.length; i++) {
          tableParamObj.push(JSON.parse(resultObj[i]));
        }

        addTable(containerId, tableParamObj);
      }
    }
  };

  // Create object that holds the SQL query
  var selectedOption = document.getElementsByName('viewCustFeeSelect');
  selectedOption = selectedOption[0];
  var feeParams = {
    custSsNum: selectedOption.options[selectedOption.selectedIndex].value
  };

  callStockPhp('getCustFeeInfo', viewCustFeesFunc, feeParams);

  return false;
}

/*
* Finds information on an individual stock
*/
function findStkInfo() {

  // Define return function
  var viewOneStkFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var containerId = 'stocksView';
        var container = document.getElementById(containerId);

        var resultObj = JSON.parse(request.responseText);

        // Clear out empty message
        container.innerText = '';

        // Generate the table with the results obtained
        var tableParamObj = new Array();
        tableParamObj.push(resultObj);
        addTable(containerId, tableParamObj);
      }
    }
  };

  // Create object that holds the SQL query parameters
  var selectedOption = document.getElementsByName('viewStockNameSelect');
  selectedOption = selectedOption[0];
  var stkParams = {
    stkSym: selectedOption.options[selectedOption.selectedIndex].value
  };

  callStockPhp('getOneStkInfo', viewOneStkFunc, stkParams);

  return false;
}

/*
* Finds the individual customer info
*/
function findCustInfo() {

  // Define return function
  var viewOneCustFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var containerId = 'custView';
        var container = document.getElementById(containerId);

        var resultObj = JSON.parse(request.responseText);

        // Clear out empty message
        container.innerText = '';

        // Generates table
        var tableParamObj = new Array();
        tableParamObj.push(resultObj);
        addTable(containerId, tableParamObj);
      }
    }
  };

  // Create object that holds the SQL query parameters
  var selectedOption = document.getElementsByName('viewCustSelect');
  selectedOption = selectedOption[0];
  var custParams = {
    ssNum: selectedOption.options[selectedOption.selectedIndex].value
  };

  callStockPhp('getOneCustInfo', viewOneCustFunc, custParams);

  return false;
}

/*
* Finds the bank account info
*/
function findBankAcctInfo() {

  // Define return function
  var viewCustBankFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var containerId = 'custBankAcctView';
        var container = document.getElementById(containerId);

        // Exit, if no data found
        if (request.responseText === 'noRecords') {
          container.innerText = 'No data returned';
          return;
        }

        var resultObj = JSON.parse(request.responseText);

        // Clear out empty message
        container.innerText = '';

        // Create array containing JSON objects of database tuples
        var tableParamObj = new Array();
        for (var i = 0; i < resultObj.length; i++) {
          tableParamObj.push(JSON.parse(resultObj[i]));
        }

        // Generate table
        addTable(containerId, tableParamObj);
      }
    }
  };

  // Create object that holds the SQL query parameters
  var selectedOption = document.getElementsByName('viewCustBankSelect');
  selectedOption = selectedOption[0];
  var custParams = {
    ssNum: selectedOption.options[selectedOption.selectedIndex].value
  };

  callStockPhp('getCustBankInfo', viewCustBankFunc, custParams);

  return false;
}

// INSERT DATA FUNCTIONS ----------------------------------

/*
* Associates a customer with how much stock they own
*/
function insertNewStockOwn() {
  // Get form values
  var custSsNum = document.getElementsByName('custForStockOwnInsert');
  custSsNum = custSsNum[0].options[custSsNum[0].selectedIndex].value;

  var stkSym = document.getElementsByName('stkForStockOwnInsert');
  stkSym = stkSym[0].options[stkSym[0].selectedIndex].value;

  var qtyOwned = document.getElementById('qtyOwnedInsert').value;

  // Create Return function
  var insertNewStkOwnFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        if (request.responseText === 'EmptyParams') {
          alert('One of the values you entered for a new customer owned stock insertion was empty. Please fill out the required fields and resubmit.');
        }
        else {
          alert('Customer Owned Stock Add Successful');
        }

        location.reload();
      }
    }
  };

  // Create Php parameters
  var stkOwnedParams = {
    custSsNum: custSsNum,
    stkSym: stkSym,
    qtyOwned: qtyOwned
  };

  callStockPhp('insertNewStockOwn', insertNewStkOwnFunc, stkOwnedParams);

  return false;
}

/*
* Inserts a new stock order into the database
*/
function insertNewStockOrder() {
  // Get form values
  var ordStatus = document.getElementsByName('orderStatusSelectInsert');
  ordStatus = ordStatus[0].options[ordStatus[0].selectedIndex].value;

  var ordType = document.getElementsByName('orderTypeSelectInsert');
  ordType = ordType[0].options[ordType[0].selectedIndex].value;

  var qtyOrdered = document.getElementById('qtyOrdInsert').value;
  var ordStartDate = document.getElementById('ordStartDateInsert').value;

  var stkSym = document.getElementsByName('ordStockSelectInsert');
  stkSym = stkSym[0].options[stkSym[0].selectedIndex].value;

  var custSsId = document.getElementsByName('ordCustSelectInsert');
  custSsId = custSsId[0].options[custSsId[0].selectedIndex].value;

  // Create Return function
  var insertStockOrderFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        if (request.responseText === 'EmptyParams') {
          alert('One of the values you entered for a new stock order insertion was empty. Please fill out the required fields and resubmit.');
        }
        else {
          alert('Stock Order Add Successful');
        }

        location.reload();
      }
    }
  };

  // Create Php parameters
  var stkOrderParams = {
    ordStatus: ordStatus,
    ordType: ordType,
    qtyOrdered: qtyOrdered,
    ordStartDate: ordStartDate,
    stkSym: stkSym,
    custSsId: custSsId
  };

  callStockPhp('insertNewStockOrder', insertStockOrderFunc, stkOrderParams);

  return false;
}

/*
* Inserts a new customer bank account into the database
*/
function insertNewBankAcct() {
  // Get form values
  var bankName = document.getElementById('bankNameInsert').value;
  var bankAcctNum = document.getElementById('bankAcctNumInsert').value;
  var custSsId = document.getElementsByName('addCustBankSelect');
  custSsId = custSsId[0].options[custSsId[0].selectedIndex].value;

  // Create Return function
  var insertNewBankAcctFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        if (request.responseText === 'EmptyParams') {
          alert('One of the values you entered for a new bank account insertion was empty. Please fill out the required fields and resubmit.');
        }
        else {
          alert('Bank Account Add Successful');
        }

        location.reload();
      }
    }
  };

  // Create Php parameters
  var bankParams = {
    bankName: bankName,
    bankAcctNum: bankAcctNum,
    custSsId: custSsId
  };

  callStockPhp('insertNewBankAcct', insertNewBankAcctFunc, bankParams);

  return false;
}

/*
* Inserts a new fee into the database
*/
function insertNewFee() {
  // Get form values
  var feeName = document.getElementById('feeNameInsert').value;
  var feeAmt = document.getElementById('feeAmtInsert').value;
  var custSsId = document.getElementsByName('addFeeCustSelect');
  custSsId = custSsId[0].options[custSsId[0].selectedIndex].value;

  // Create Return function
  var insertNewFeeFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        if (request.responseText === 'EmptyParams') {
          alert('One of the values you entered for a new fee insertion was empty. Please fill out the required fields and resubmit.');
        }
        else {
          alert('Fee Add Successful');
        }

        location.reload();
      }
    }
  };

  // Create Php parameters
  var feeParams = {
    feeName: feeName,
    feeAmt: feeAmt,
    custSsId: custSsId
  };

  callStockPhp('insertNewFee', insertNewFeeFunc, feeParams);

  return false;
}

/*
* Inserts a new stock into the database
*/
function insertNewStock() {
  // Get form values
  var stkName = document.getElementById('stockNameInsert').value;
  var stkSym = document.getElementById('stockSymbolInsert').value;
  var stkPrice = document.getElementById('stockPriceInsert').value;

  // Create Return function
  var insertNewStkFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        if (request.responseText === 'EmptyParams') {
          alert('One of the values you entered for a new stock insertion was empty. Please fill out the required fields and resubmit.');
        }
        else {
          alert('Stock Add Successful');
        }

        location.reload();
      }
    }
  };

  // Create Php parameters
  var stockParams = {
    stkName: stkName,
    stkSym: stkSym,
    stkPrice: stkPrice
  };

  callStockPhp('insertNewStk', insertNewStkFunc, stockParams);

  return false;
}

/*
* Inserts a new customer into the database
*/
function insertNewCust() {
  // Get form values
  var firstName = document.getElementById('firstNameInsert').value;
  var lastName = document.getElementById('lastNameInsert').value;
  var soSecNum = document.getElementById('soSecNumInsert').value;

  // Create Return function
  var insertNewCustFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        if (request.responseText === 'EmptyParams') {
          alert('One of the values you entered for a new customer insertion was empty. Please fill out the required fields and resubmit.');
        }
        else {
          alert('Customer Add Successful');
        }

        location.reload();
      }
    }
  };

  // Create Php parameters
  var custParams = {
    firstName: firstName,
    lastName: lastName,
    soSecNum: soSecNum
  };

  callStockPhp('insertNewCust', insertNewCustFunc, custParams);

  return false;
}

// DELETE DATA FUNCTIONS ----------------------------------

/*
* Deletes a customer from the database
*/
function deleteCustomer() {
  // Get form values
  var custSsNum = document.getElementsByName('delCustBankSelect');
  custSsNum = custSsNum[0].options[custSsNum[0].selectedIndex].value;

  // Create Return function
  var deleteCustFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        if (request.responseText === 'deleteFailed') {
          alert('Delete failed.');
        }
        else {
          alert('Customer Delete Successful');
        }

        location.reload();
      }
    }
  };

  // Create Php parameters
  var custParams = {
    custSsNum: custSsNum
  };

  callStockPhp('deleteCustomer', deleteCustFunc, custParams);

  return false;
}

// UPDATE DATA FUNCTIONS ----------------------------------

/*
* Updates a stock price
*/
function updateStockPrice() {
  // Get form values
  var newStkPrice = document.getElementById('updatedStkPrice').value;
  var stkSym = document.getElementsByName('updStockPriceSelect');
  stkSym = stkSym[0].options[stkSym[0].selectedIndex].value;

  // Create Return function
  var updateStkPriceFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        if (request.responseText === 'updateFailed') {
          alert('Stock Price Update Failed');
        }
        else {
          alert('Stock Price Update Successful');
        }

        location.reload();
      }
    }
  };

  // Create Php parameters
  var stkParams = {
    newStkPrice: newStkPrice,
    stkSym: stkSym
  };

  callStockPhp('updateStockPrice', updateStkPriceFunc, stkParams);

  return false;
}
