function populateComboBoxes() {

  // Populate Combo Box in Customer view table section
  var popCustComBoFunc = function(request){
    return function() {
      if(request.readyState == 4) {
        var container = document.getElementById('custView');
        container.innerHTML = request.responseText;
      }
    }
  };
  callStockPhp(popCustComBoFunc);

}

function callStockPhp(returnFunc) {
  var request = new XMLHttpRequest();
  var url = 'stocks.php';
  if (!request){
    return false;
  }
 
  request.onreadystatechange = returnFunc(request);
  request.open('GET', url, true);
  request.send(null);
  return request;
} 