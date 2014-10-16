function  addOption(objId,optionVal,optionText)  {
	objSelect = document.getElementById(objId);
	var _o = document.createElement("OPTION");
	_o.text = optionText;
	_o.value = optionVal;
//	alert(objSelect.length);
	objSelect.options.add(_o);
} 
