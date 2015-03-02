function populateComboBoxes() {

  // Populate Combo Box in Customer view table section
  var popCustComBoFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var container = document.getElementsByName('viewCustSelect');
        container = container[0];

        container.options[container.options.length] = new Option(request.responseText, 'blah'); 
      }
    }
  };
  callStockPhp('getCustNames',popCustComBoFunc);
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