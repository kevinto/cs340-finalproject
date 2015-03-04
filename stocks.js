function findCustInfo() {

  // Define Async return function
  var viewOneCustFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var container = document.getElementById('custView');

        var resultObj = JSON.parse(request.responseText);
        container.innerText = resultObj.first_name + ' ' + resultObj.last_name + ' ' + resultObj.social_security_num;
      }
    }
  };

  callStockPhp('getOneCustInfo', viewOneCustFunc);

  return false;
}

function callStockPhp(phpFuncName, returnFunc) {
  var request = new XMLHttpRequest();
  var url = 'stocks.php?' + phpFuncName + '=true';
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
