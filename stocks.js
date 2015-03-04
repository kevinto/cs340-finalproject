function findCustInfo() {

  // Define Async return function
  var viewOneCustFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var containerId = 'custView';
        var container = document.getElementById(containerId);

        var resultObj = JSON.parse(request.responseText);
        // container.innerText = resultObj.first_name + ' ' + resultObj.last_name + ' ' + resultObj.social_security_num;
        var tableParamObj = new Array();
        tableParamObj.push(resultObj);
        addTable(containerId, tableParamObj);
      }
    }
  };

  // Create object that holds the SQL query
  var selectedOption = document.getElementsByName('viewCustSelect');
  selectedOption = selectedOption[0];
  var custParams = {
    ssNum: selectedOption.options[selectedOption.selectedIndex].value
  };

  callStockPhp('getOneCustInfo', viewOneCustFunc, custParams);

  return false;
}

function addTable(targetDiv, dispObjArray) {
      
  var myTableDiv = document.getElementById(targetDiv);
    
  var table = document.createElement('table');
  table.border='1';
  
  for (var i = 0; i < dispObjArray.length; i++){
    // Create the headers

   // Create the actual rows 

    var tr = document.createElement('tr');
    table.appendChild(tr);

    for (var j=0; j<4; j++){
       var td = document.createElement('td');
       td.width='75';
       td.appendChild(document.createTextNode("Cell " + i + "," + j));
       tr.appendChild(td);
    }
  }

  myTableDiv.appendChild(table);
}

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

function populateComboBoxes() {
  popAllCustomerComboBoxes();
}

// Populate Combo Box in Customer view table section
function popAllCustomerComboBoxes() {
  var popCustComBoFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var container = document.getElementsByName('viewCustSelect');
        container = container[0];

        var objArray = JSON.parse(request.responseText);
        var currentItem;
        var fullName;
        var i;
        for(i = 0; i < objArray.length; i++) {
          currentItem = JSON.parse(objArray[i]);
          fullName = currentItem.first_name + ' ' + currentItem.last_name;
          container.options[container.options.length] = new Option(fullName, currentItem.social_security_num);
        }
      }
    }
  };
  callStockPhp('getAllCustInfo',popCustComBoFunc);
}
