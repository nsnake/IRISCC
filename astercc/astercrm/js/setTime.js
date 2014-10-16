var _fieldname;
var _listname;
function showTimeList(tl){
	var str = "<div id=\"_contents\" style=\"padding:6px; background-color:#E3E3E3; font-size: 12px; border: 1px solid #777777; position:absolute; left:?px; top:?px; width:?px; height:?px; z-index:1; visibility:hidden\">";
	str += ""+hourShow+"<select name=\"_hour\">";
	for (h = 0; h <= 9; h++) {
	str += "<option value=\"0" + h + "\">0" + h + "</option>";
	}
	for (h = 10; h <= 23; h++) {
	str += "<option value=\"" + h + "\">" + h + "</option>";
	}
	str += "</select> "+minShow+"<select name=\"_minute\">";
	for (m = 0; m <= 9; m++) {
	str += "<option value=\"0" + m + "\">0" + m + "</option>";
	}
	for (m = 10; m <= 59; m++) {
	str += "<option value=\"" + m + "\">" + m + "</option>";
	}
	str += "</select> "+secShow+"<select name=\"_second\">";
	for (s = 0; s <= 9; s++) {
	str += "<option value=\"0" + s + "\">0" + s + "</option>";
	}
	for (s = 10; s <= 59; s++) {
	str += "<option value=\"" + s + "\">" + s + "</option>";
	}
	str += "</select> <input name=\"queding\" type=\"button\" onclick=\"_select()\" value=\""+inputBtn+"\" style=\"font-size:12px\" /></div>";
	document.getElementById(tl).innerHTML= str;
	document.getElementById(tl).style.display= '';
}

function _SetTime(tt) {
_fieldname = tt;
var ttop = tt.offsetTop; //TT控件的定位点高
var thei = tt.clientHeight; //TT控件本身的高
var tleft = tt.offsetLeft; //TT控件的定位点宽
while (tt = tt.offsetParent) {
//ttop += tt.offsetTop;
//tleft += tt.offsetLeft;
}
document.getElementsByTagName("*")._contents.style.top = ttop + thei + 4;
document.getElementsByTagName("*")._contents.style.left = tleft;
document.getElementsByTagName("*")._contents.style.visibility = "visible";
}

function _select() {
_fieldname.value = document.getElementsByTagName("*")._hour.value + ":" + document.getElementsByTagName("*")._minute.value + ":" + document.getElementsByTagName("*")._second.value;
document.getElementsByTagName("*")._contents.style.visibility = "hidden";
}