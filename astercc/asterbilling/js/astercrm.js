var timerShowStatus,timerShowChannelsInfo;
function showStatus(){
	xajax_showStatus(xajax.$('curid').value);
	setTimeout("showStatus()", 2000);
}

function clearHistory(objId){
	document.getElementById(objId).innerHTML = '';
}

function checkOut(channelid){
	xajax_checkOut(channelid);
}

function putCurrentTime(objId,initSec){
	var now=new Date();
	now = new Date(now.getTime() - initSec * 1000);
	if (document.getElementById(objId).value == '')
		document.getElementById(objId).value = now ;//- initSec * 1000;
}

function trim(stringToTrim) {
return stringToTrim.replace(/^\s+|\s+$/g,"");
}

function openWindow(url){
	window.open(url);
}

function MM_preloadImages() { //v3.0
  var d=document; if(d.images){ if(!d.MM_p) d.MM_p=new Array();
    var i,j=d.MM_p.length,a=MM_preloadImages.arguments; for(i=0; i<a.length; i++)
    if (a[i].indexOf("#")!=0){ d.MM_p[j]=new Image; d.MM_p[j++].src=a[i];}}
}
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}
function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}

function hideObj(objname) {
	var obj = document.getElementsByName(objname);

	for(i=0;i<obj.length;i++) {
		obj[i].style.display="none";
	}
}

function showObj(objname) {
	var obj = document.getElementsByName(objname);
	for(i=0;i<obj.length;i++) {
		obj[i].style.display="block";
	}
}

function deleteRow(i){
    document.getElementById('tblCallbackTable').deleteRow(i);
}

function setCurrency(s){
	if (isNaN(s) == true) return;
	s = Math.round(s*100)/100;

	isNegative = false;
	s = String(s);
	if (s.indexOf('-') == 0){
		isNegative = true;
		s = s.substring(1);
	}
	if(/[^0-9\.]/.test(s)) return "invalid value";
	s=s.replace(/^(\d*)$/,"$1.");
	s=(s+"00").replace(/(\d*\.\d\d)\d*/,"$1");
	s=s.replace(".",",");
	var re=/(\d)(\d{3},)/;
	while(re.test(s))
		s=s.replace(re,"$1,$2");
	s=s.replace(/,(\d\d)$/,".$1");
	s = s.replace(/^\./,"0.");
	if (isNegative){
		s = "-"+s;
	}
	return s;
}

function calculateBalance(divId){
	credit = document.getElementById(divId + '-iptCredit').value;
	unbilled = parseFloat(document.getElementById(divId + '-unbilled').innerHTML);
	customerid = document.getElementById(divId + '-CustomerId').value;

	if (credit == ''){
		credit = 0.00;
	}else{
		credit = parseFloat(credit);
		//alert(document.getElementById(divId+'-CustomerId').value);
		if( customerid != '' ){
			discount = 1 + parseFloat(document.getElementById(divId+'-CustomerDiscount').value);
			credit = credit*discount;
		}
	}
	lock_clid = 0;
	if (document.getElementById(divId+'-ckbCredit').checked && document.getElementById('creditlimittype').value == 'balance' && (unbilled - credit)  >= -0.001 )
	{
	//		alert('warning: the credit should be greater than unbilled');
		document.getElementById(divId + '-balance').style.backgroundColor="red";
		lock_clid = 1;
		//document.getElementById(divId+'-iptCredit').readOnly = false;
	}else{
		document.getElementById(divId + '-balance').style.backgroundColor="";
	}
	document.getElementById(divId + '-balance').innerHTML = setCurrency(credit - unbilled);
	// update clid locked field
	// alter table `clid` add locked tinyint(4) default '0' after `curcredit`;
	xajax_setLocked(divId,lock_clid);
}

function removeTr(divId){
	tbody = document.getElementById(divId + '-calllog-tbody');
	for (i = tbody.rows.length; i>0 ; i-- )
	{
		tbody.deleteRow(0); 
	}
}


function btnCDROnClick(divId){
	window.open("checkout.php?peer=" + divId ,"CheckOutPage");
}

function btnReceiptOnClick(divId,customereable){
	//alert(customereable);return;
	if(customereable != 0 && customereable != 'undefined'){
		customerid = document.getElementById(divId + '-CustomerId').value;
		customername = document.getElementById(divId + '-CustomerName').value;
		discount = document.getElementById(divId + '-CustomerDiscount').value;
	}else{
		customerid = 0;
		customername = '';
		discount = 0;
	}
	window.open ("receipt.php?peer="+divId+"&customername="+customername+"&discount="+discount+"", 'Receipt', 'height=300, width=600, toolbar=no, menubar=no, scrollbars=yes, resizable=yes, location=no, status=no');
}

function hangup(channel){
	if (channel != ''){
		xajax_hangup(channel);
	}
}

function setBillsec(trId){
	var answertime = trim(document.getElementById(trId + '-localanswertime').value);
	var now = new Date();
	var billsec = 0;
	if (answertime != ''){
		answertime = new Date(answertime);
		billsec = parseInt((now.getTime() - answertime.getTime()) / 1000 + 0.9999);
		document.getElementById(trId + '-billsec').value = billsec;
		hours = parseInt(billsec/3600);
		minutes =   parseInt( (billsec - hours*3600)/60);
		seconds = billsec - hours * 3600 - minutes * 60
		document.getElementById(trId + '-duration').innerHTML = hours + ':' + minutes + ':' + seconds;
	}
	return billsec;
}

function checkHangup(){
//	setTimeout("checkHangup()", 900);
//	return;
	oDivList = document.getElementsByName("divList[]");
	for(i=0;i<oDivList.length;i++) {
		trId = oDivList[i].value;
		channel = document.getElementById(trId + '-channel').value;

		//if (odivList[i].checked){	// locked
		//	hangup(channel);
		//	document.getElementById(trId + '-lock').style.backgroundColor="red";
		//}else{
		//	document.getElementById(trId + '-lock').style.backgroundColor="";
		//}
	
		if (channel != ''){
			// check if set credit limit
			if (document.getElementById(trId + "-ckbCredit").checked){
				if (document.getElementById(trId + "-limitstatus").value == ""){
						document.getElementById(trId + "-limitstatus").value = "setting";
						if (document.getElementById("creditlimittype").value == 'balance')
						{
							xajax_setCreditLimit(trId,channel,document.getElementById(trId + "-balance").innerHTML);
						}else{
							xajax_setCreditLimit(trId,channel,document.getElementById(trId + "-iptCredit").value);
						}
						//alert("setting");
				}
			}else{

			}
			// set credit limit
		}

//		oCkbCredit = document.getElementById(trId + "-ckbCredit");
		var billsec = setBillsec(trId);
		var legbBillsec = setBillsec(trId + '-legb');
//
//		//setPrice(trId,billsec);
//		//setPrice(trId + '-legb',legbBillsec);
//
//		var rateinitial = Number(document.getElementById(trId + '-rateinitial').innerHTML);
//		var initblock = Number(document.getElementById(trId + '-initblock').innerHTML);
//		var billingblock = Number(document.getElementById(trId + '-billingblock').innerHTML);
//		var connectcharge = Number(document.getElementById(trId + '-connectcharge').innerHTML);
//
//		if (rateinitial){
//
//			if (oCkbCredit.checked){
//				calculateBalance(trId);
//				var balance = document.getElementById(trId + '-balance').innerHTML;
//
//				balance = balance - connectcharge + 0.0001;
//				if (balance < 0){
//					hangup(channel);
//				}
//
//				var limitsec = 0;
//
//				limitsec = initblock + parseInt(balance / (billingblock * rateinitial/60))*billingblock;
//
//				if ( billsec > limitsec - 1 ){
//					hangup(channel);
//				}
//				document.getElementById(trId + '-totalsec').innerHTML = limitsec;
//			}
//		}
	}
	setTimeout("checkHangup()", 900);
}

function clearCurchannel(divId){
	//Local/84754138-legb-localanswertime
	//alert("ok");
	niftyplayer('hangup-beep').play();
	document.getElementById(divId + '-phone').innerHTML = '&nbsp;';
	document.getElementById(divId + '-startat').innerHTML = '';
	document.getElementById(divId + '-duration').innerHTML = '';
	document.getElementById(divId + '-rateinitial').innerHTML = '-';
	document.getElementById(divId + '-initblock').innerHTML = '-';
	document.getElementById(divId + '-billingblock').innerHTML = '-';
	document.getElementById(divId + '-connectcharge').innerHTML = '-';
	document.getElementById(divId + '-channel').value = '';
	document.getElementById(divId + '-price').innerHTML = '';
	document.getElementById(divId + '-billsec').value = 0;
	document.getElementById(divId + '-localanswertime').value = '';
	document.getElementById(divId + '-totalsec').innerHTML = '-';
	document.getElementById(divId + '-limitstatus').value = '';	
}



function invite(){
	src = trim(xajax.$('iptLegB').value);
	dest = trim(xajax.$('iptLegA').value);
	creditLimit = trim(xajax.$('creditLimit').value);

	if (src == '' || dest == '')
		return false;

	trId = "Local/" + src;
	//check if legB div exsit
	if (document.getElementById(trId + '-divContainer') == null){
		addDiv("divMainContainer",trId,creditLimit,0);
		xajax_addUnbilled(src,dest);
	} else {
		if (creditLimit != ''){
			document.getElementById(trId + '-ckbCredit').checked = true;
			document.getElementById(trId + '-iptCredit').value = creditLimit;
			document.getElementById(trId + '-balance').innerHTML = creditLimit;
			document.getElementById(trId + '-iptCredit').readOnly = true;
		} else {
			document.getElementById(trId + '-ckbCredit').checked = false;
			document.getElementById(trId + '-iptCredit').value = '';
			document.getElementById(trId + '-balance').innerHTML = '';
			document.getElementById(trId + '-iptCredit').readOnly = false;
		}
	}
	//			alert(document.getElementById(trId));
//			alert(trId);
	//legB

	xajax_invite(src,dest,creditLimit);
}