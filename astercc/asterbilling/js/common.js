function  addOption(objId,optionVal,optionText)  {
	//alert(objId);
	objSelect = document.getElementById(objId);
	var _o = document.createElement("OPTION");
	_o.text = optionText;
	_o.value = optionVal;
//	alert(objSelect.length);
	objSelect.options.add(_o);
} 

function filedFilter(srcObj,valueType){
	if (valueType == 'numeric'){
		if (srcObj.value.indexOf('-') == 0) {
			srcObj.value = "-" + srcObj.value.replace(/[^\d\.]/g,'');
		}else{
			srcObj.value = srcObj.value.replace(/[^\d\.]/g,'');
		}
	}else if (valueType == 'word'){
		srcObj.value = srcObj.value.replace(/[^\w\s]/g,'');
	}else if (valueType == 'phone'){
		srcObj.value = srcObj.value.replace(/[^\d\-\(\)]/g,'');
	}else if (valueType == 'address'){
		srcObj.value = srcObj.value.replace(/[^\w\s\d\-\(\)\#]/g,'');
	}else if (valueType == 'content'){
		srcObj.value = srcObj.value.replace(/[^\w\s\d\-\(\)\#]/g,'');
	}else if (valueType == 'username'){
		srcObj.value = srcObj.value.replace(/[^\w\s\d\-\.\_]/g,'');
	}
}
