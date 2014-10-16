<?php
/*******************************************************************************
* systemstatus_simple.php
* 系统状态文件
* systerm status interface
* 功能描述
	 显示分机状态和正在进行的通话

TO-DO

1.增加 print invoice 的button
2.asterrc 增加一级计费引擎
3.每个booth可以自定义名称
4.callshop 可以显示自己的信息

* Function Desc


* javascript function:		
						showStatus				show sip extension status
						showChannelsInfo		show asterisk channels information
						init					initialize function after page loaded

* Revision 0.045  2007/10/18 17:55:00  last modified by solo
* Desc: page created
********************************************************************************/

require_once('systemstatus_simple.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/'); ?>
		<meta http-equiv="Content-Language" content="utf-8" />
		<SCRIPT LANGUAGE="JavaScript">
		<!--
function addDiv(containerId,divId,creditLimit,num,status,displayname,customereable){
	//alert(containerId+' -- '+divId+' -- '+creditLimit+' -- '+num+' -- '+status+' -- '+displayname+' -- '+customereable);
	var container = document.getElementById(containerId);
	if (displayname == '')
	{
		displayname = divId;
	}
	//检查是否已经存在该id

	if (document.getElementById(divId + '-divContainer') != null){
		return ;
	}


	var divContainer = document.createElement("div");
	if(customereable != 0){
		divContainer.className="float";
	}else{
		divContainer.className="floatnomember";
	}
	divContainer.id = divId + '-divContainer';

	// add title div
	var div = document.createElement("div");
	div.className = "lable";
	if (num != '') {
		div.innerHTML += "<span id=\""+divId+"-displayname\">"+displayname+"</span>";
	} else {
		div.innerHTML += '<input type="button" value="D" onclick="removeLocalDiv(\'' + divId + '\');return false;">'+"<span id=\"" + divId + "-displayname\">" + divId + '</span>';
	}
	divContainer.appendChild(div);

	var div = document.createElement("div");
	div.className = "lable_registered";
	if (num != '') {
		div.innerHTML += "<span id=\""+divId+"-peer-status\"></span>";
	} else {
		div.innerHTML += '';
	}
	divContainer.appendChild(div);

	var div = document.createElement("div");
	div.className = "lable_call";
	div.id = divId+"-phone";
	div.innerHTML += "";
	divContainer.appendChild(div);

	var div = document.createElement("div");
	div.className = "lable_unbilled";
	div.innerHTML += "<span id=\""+divId+"-price\">0</span>";
	div.innerHTML += "<form action=\"\" name=\"" + divId + "-form\" id=\"" + divId + "-form\" style=\"display:none;\">" +
						"<table width=\"500\" class=\"calllog\">" +
							"<tbody id=\"" + divId + "-calllog-tbody\">"+"<input id=\"" + divId + "-CustomerId\" name=\"" + divId + "-CustomerId\" type=\"hidden\" value=\"\">"+ "<input id=\"" + divId + "-CustomerDiscount\" name=\"" + divId + "-CustomerDiscount\" type=\"hidden\" value=\"0\">" +
							"</tbody>"+"</table>" +"</form>";
	divContainer.appendChild(div);

	var div = document.createElement("div");
	div.className = "lable_credit";
	
	if (status == -1){
		div.innerHTML += "<input type=\"checkbox\" onclick=\"ckbCreditOnClick(this);\" value=\""+divId+"\" name=\""+divId+"-ckbCredit\" id=\""+divId+"-ckbCredit\"><span id=\""+divId+"-spanLimit\">Limit</span>: <input type=\"text\" onkeyup=\"filedFilter(document.getElementById('"+divId+"-iptCredit'),'numeric');\" maxlength=\"7\" size=\"9\" value=\"\" name=\""+divId+"-iptCredit\" id=\""+divId+"-iptCredit\" /><input type=\"hidden\" value=\""+divId+"\" name=\"divList[]\" id=\"divList[]\"><input checked type=\"checkbox\" onclick=\"setStatus('"+divId+"',this.checked);\" value=\""+divId+"\" name=\""+divId+"-ckbLock\" id=\""+divId+"-ckbLock\"><span id=\""+divId+"-lock\" style=\"background-color: red;\">Lock</span> ";
	}else{
		div.innerHTML += "<input type=\"checkbox\" onclick=\"ckbCreditOnClick(this);\" value=\""+divId+"\" name=\""+divId+"-ckbCredit\" id=\""+divId+"-ckbCredit\"><span id=\""+divId+"-spanLimit\">Limit</span>: <input type=\"text\" onkeyup=\"filedFilter(document.getElementById('"+divId+"-iptCredit'),'numeric');\" maxlength=\"7\" size=\"9\" value=\"\" name=\""+divId+"-iptCredit\" id=\""+divId+"-iptCredit\" /><input type=\"hidden\" value=\""+divId+"\" name=\"divList[]\" id=\"divList[]\"><input type=\"checkbox\" onclick=\"setStatus('"+divId+"',this.checked);\" value=\""+divId+"\" name=\""+divId+"-ckbLock\" id=\""+divId+"-ckbLock\"><span id=\""+divId+"-lock\">Lock</span> ";
	}
	divContainer.appendChild(div);

	var div = document.createElement("div");
	div.className = "lable_img";
	div.innerHTML += "<span><a href=\"#\" onclick=\"hangupOnClick('"+divId+"');return false;\"><img src=\"images/hangup.png\"></a></span>&nbsp;&nbsp;<span><a href=\"#\" onclick=\"btnReceiptOnClick('"+divId+"',0);return false;\"><img src=\"images/receipt.png\"></a></span>&nbsp;&nbsp;<span><a href=\"#\" onclick=\"btnClearOnClick('"+divId+"','');return false;\"><img src=\"images/clear.png\"></a></span>";
	divContainer.appendChild(div);

	div.innerHTML += "<span style=\"display:none;\" id=\"" + divId + "-unbilled\">0</span>";
	div.innerHTML += "<span style=\"display:none;\" id='" + divId + "-balance' name=\"" + divId + "-balance\">0.00</span";
	div.innerHTML += "<span style=\"display:none;\"id=\"" + divId + "-duration\"> </span>";
	div.innerHTML += "<input id=\"" + divId + "-CustomerId\" name=\"" + divId + "-CustomerId\" type=\"hidden\" value=\"\">";
	div.innerHTML += "<input type=\"hidden\" id=\"" + divId + "-channel\" name=\"" + divId + "-channel\" value=''>";
	div.innerHTML += "<input type=\"hidden\" id=\"" + divId + "-legb-channel\" name=\"" + divId + "-legb-channel\" value=''>";
	div.innerHTML += "<input type=\"hidden\" id=\"" + divId + "-dstchan\" name=\"" + divId + "-dstchan\" value=''>";
	div.innerHTML += '<input type="hidden" id="' + divId + '-localanswertime" name="' + divId + '-localanswertime" value="">';
	div.innerHTML += '<input type="hidden" id="' + divId + '-legb-localanswertime" name="' + divId + '-localanswertime" value="">';
	div.innerHTML += '<input type="hidden" size="4" id="' + divId + '-billsec" name="' + divId + '-billsec" value="0">';
	div.innerHTML += '<input type="hidden" size="4" id="' + divId + '-legb-billsec" name="' + divId + '-billsec" value="0">';
	div.innerHTML += '<input type="hidden" size="4" id="' + divId + '-limitstatus" name="' + divId + '-limitstatus" value="">';
	/*div.innerHTML += "&nbsp;&nbsp;<a href=\"?\" onclick=\"hangupOnClick('" + divId + "');return false;\"><?echo $locate->Translate("Hangup");?></a>";
	div.innerHTML += "&nbsp;&nbsp;<a href=\"?\" onclick=\"btnClearOnClick('" + divId + "','');return false;\"><?echo $locate->Translate("Clear");?></a>";
	div.innerHTML += "&nbsp;&nbsp;<a href=\"?\" onclick=\"btnCDROnClick('" + divId + "');return false;\"><?echo $locate->Translate("Cdr");?></a>";
	div.innerHTML += "&nbsp;&nbsp;<a href=\"?\" onclick=\"btnReceiptOnClick('"+divId+"',"+customereable+");return false;\"><?echo $locate->Translate("Receipt");?></a>";*/

	divContainer.appendChild(div);

	container.appendChild(divContainer);
}

function addSimpleDiv(containerId,divId,creditLimit,num,status,displayname){
	var container = document.getElementById(containerId);

	if (displayname == '')
	{
		displayname = divId;
	}
	//检查是否已经存在该id

	if (document.getElementById(divId + '-divContainer') != null){
		return ;
	}


	var divContainer = document.createElement("div");
	//divContainer.className="simpleFloat";
	divContainer.id = divId + '-divContainer';

	// add title div
	var div = document.createElement("div");
	div.className = "lable";
	
	if (num != '')
	{
		div.innerHTML += "<span id=\"" + divId + "-displayname\">" + "&nbsp;No." + num + ":" +  displayname + "</span>&nbsp;&nbsp;<span id=\"" + divId + "-peer-status\"></span>";
	}else{
		div.innerHTML += '<input type="button" value="D" onclick="removeLocalDiv(\'' + divId + '\');return false;">' + divId;
	}
	div.innerHTML += " <span id=\"" + divId + "-status\"></span>";
	divContainer.appendChild(div);

	// add cdr div
	var div = document.createElement("div");
	div.className = "peerstatus";
	div.innerHTML += "<table class=\"peerstatus\" width=\"400\" >" +
						"<tbody id=\"" + divId + "-tbody\">" +
						"<tr>" +
						"<th style=\"width:70px;\"><?echo $locate->Translate("Phone");?></th>" +
						"<th style=\"width:50px;\"><?echo $locate->Translate("Sec");?></th>" +
						"<th style=\"width:100px;\"  nowrap><?echo $locate->Translate("Start At");?></th>" +
						"</tr>" +
						"<tr id=\"trTitle\" class=\"curchannel\">" +
						"<td id=\"" + divId + "-phone\">&nbsp;</td>" +
						"<td id=\"" + divId + "-duration\"> </td>" +
						"<td id=\"" + divId + "-startat\" nowrap> </td>" +
						"</tr>" +
						"</tbody>" +
					"</table>";

	divContainer.appendChild(div);



	//add lock div
	var div = document.createElement("div");
	div.className = "lable";
	div.innerHTML += "<input type=\"hidden\" id=\"divList[]\" name=\"divList[]\" value=\"" + divId + "\">";
	if (status == -1){
		div.innerHTML += "<input checked type=\"checkbox\" id=\"" + divId+ "-ckbLock\" name=\"" + divId+ "-ckbLock\"  onclick=\"setStatus('" + divId + "',this.checked);\"><span id=\"" + divId + "-lock\" style=\"background-color: red;\"><?echo $locate->Translate("Lock");?></span> ";
	}else{
		div.innerHTML += "<input type=\"checkbox\" id=\"" + divId+ "-ckbLock\" name=\"" + divId+ "-ckbLock\" value=\"" + divId + "\" onclick=\"setStatus('" + divId + "',this.checked);\"><span id=\"" + divId + "-lock\"><?echo $locate->Translate("Lock");?></span> ";
	}

	div.innerHTML += "<input type=\"hidden\" id=\"" + divId + "-channel\" name=\"" + divId + "-channel\" value=''>";
	div.innerHTML += "<input type=\"hidden\" id=\"" + divId + "-legb-channel\" name=\"" + divId + "-legb-channel\" value=''>";
	div.innerHTML += '<input type="hidden" id="' + divId + '-localanswertime" name="' + divId + '-localanswertime" value="">';
	div.innerHTML += '<input type="hidden" id="' + divId + '-legb-localanswertime" name="' + divId + '-localanswertime" value="">';
	div.innerHTML += '<input type="hidden" size="4" id="' + divId + '-billsec" name="' + divId + '-billsec" value="0">';
	div.innerHTML += '<input type="hidden" size="4" id="' + divId + '-legb-billsec" name="' + divId + '-billsec" value="0">';
	div.innerHTML += '<input type="hidden" size="4" id="' + divId + '-limitstatus" name="' + divId + '-limitstatus" value="">';
	div.innerHTML += "&nbsp;&nbsp;<a href=\"?\" onclick=\"hangupOnClick('" + divId + "');return false;\"><?echo $locate->Translate("Hangup");?></a>";
	//div.innerHTML += "&nbsp;&nbsp;<a href=\"?\" onclick=\"btnClearOnClick('" + divId + "');return false;\">Clear</a>";
	//div.innerHTML += "&nbsp;&nbsp;<a href=\"?\" onclick=\"btnCDROnClick('" + divId + "');return false;\">Cdr</a>";
	divContainer.appendChild(div);

	container.appendChild(divContainer);
}

function calculateBalance_simple(divId){
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
		lock_clid = 1;
	}else{

	}
	document.getElementById(divId + '-balance').innerHTML = setCurrency(credit - unbilled);
	xajax_setLocked(divId,lock_clid);

}


function ckbCreditOnClick(objCkb){
	if (document.getElementById(objCkb.value+'-iptCredit').value == "") {
		objCkb.checked = false;
		return false;
	}

	if (objCkb.checked){
		if (confirm("<?echo $locate->Translate("select OK to enable credit limit");?>")){
			document.getElementById(objCkb.value+'-iptCredit').readOnly = true;
			objCkb.checked = true;
			document.getElementById(objCkb.value + "-limitstatus").value = "";
			// reset balance
			calculateBalance_simple(objCkb.value);

		}else{
			objCkb.checked = false;
		}
	}else{
		if (confirm("<?echo $locate->Translate("select OK to disable credit limit");?>")){
			document.getElementById(objCkb.value+'-iptCredit').readOnly = false;
			objCkb.checked = false;
			channel = document.getElementById(objCkb.value+'-channel').value;
			if (channel != ''){
				xajax_setCreditLimit(objCkb.value,channel,0);
			}
			//document.getElementById(objCkb.value + '-balance').style.backgroundColor="";
			document.getElementById(objCkb.value + '-iptCredit').value = "";
			calculateBalance_simple(objCkb.value)
		}else{
			objCkb.checked = true;
		}
	}
}



function appendTr(tbodyId,aryValues){
	var tbody = document.getElementById(tbodyId);
    var tr = document.createElement("tr");

	// caller id
    var td = document.createElement("td");
	if(trim(aryValues["billsec"]) == 0){
		td.innerHTML = "<acronym title=\"" + "<?echo $locate->Translate("Destination");?>:" + trim(aryValues["destination"]) + "(" + "<?echo $locate->Translate("Rate");?>:" + trim(aryValues["rate"]) + ")" + "\"><img src='images/noanswer.gif'>" + trim(aryValues["dst"]) + "</acronym>";
	}else{
		if(trim(aryValues["direction"]) == 'inbound')
			td.innerHTML = "<acronym title=\"" + "<?echo $locate->Translate("Destination");?>:" + trim(aryValues["destination"]) + "(" + "<?echo $locate->Translate("Rate");?>:" + trim(aryValues["rate"]) + ")" + "\"><img src='images/inbound.gif'>" + trim(aryValues["dst"]) + "</acronym>";
		else
			td.innerHTML = "<acronym title=\"" + "<?echo $locate->Translate("Destination");?>:" + trim(aryValues["destination"]) + "(" + "<?echo $locate->Translate("Rate");?>:" + trim(aryValues["rate"]) + ")" + "\"><img src='images/outbound.gif'>" + trim(aryValues["dst"]) + "</acronym>";
	}
//	td.innerHTML = trim(aryValues["dst"]);
//	td.style.width = "70px";
//	if(trim(aryValues["direction"]) == 'inbound') td.style.color = "green";
	tr.appendChild(td);
	
 	// duration
   var td = document.createElement("td");
	var hours = parseInt(aryValues["billsec"]/3600);
	var minutes = parseInt( (aryValues["billsec"] - hours*3600)/60);
	var seconds = aryValues["billsec"] - hours * 3600 - minutes * 60
	td.innerHTML = hours + ':' + minutes + ':' + seconds;

//	td.style.width = "20px";
	tr.appendChild(td);

 	// price
   var td = document.createElement("td");
	td.innerHTML = trim(aryValues["price"]);
//	td.style.width = "20px";
	tr.appendChild(td);


	//destination
   var td = document.createElement("td");
	td.innerHTML = trim(aryValues["destination"]);
//	td.style.width = "140px";
	tr.appendChild(td);



 	// rate
   var td = document.createElement("td");
	td.innerHTML = trim(aryValues["rate"]) + "<input type=\"hidden\" id=\"cdrid[]\" name=\"cdrid[]\" value=\"" + aryValues["id"] + "\">";
	td.style.width = "150px";
	tr.appendChild(td);

 	// start at
   var td = document.createElement("td");
	td.innerHTML = trim(aryValues["startat"]);
//	td.style.width = "160px";
	tr.appendChild(td);

	tbody.appendChild(tr);
}


function btnClearOnClick(divId,payment){

	if (!confirm("<?echo $locate->Translate("Are you sure to clear this booth");?>"+"?'"))
	{
		return false;
	}
	form = document.getElementById(divId + "-form");
	xajax_checkOut(xajax.getFormValues(divId + "-form"),divId,payment);
}

function setStatus(trId,status){
	if (status)
	{
		if (confirm("<?echo $locate->Translate("Are you sure to lock booth");?>" + trId + "?"))
		{
			xajax_setStatus(trId,-1);
		}
	}else{
		if (confirm("<?echo $locate->Translate("Are you sure to unlock booth");?>" + trId + "?"))
		{
			xajax_setStatus(trId,1);
		}
	}
}

function init(){
	xajax_init();
	showStatus();
	dragresize.apply(document);
}

function showCallshopStatus(){
	//xajax_setFreeCall('1');
	var myDiv = document.getElementById("divAmount");
	if (myDiv.style.display == 'block'){
		myDiv.style.display = 'none';
	}else{
		xajax_setGroupBalance();
		myDiv.style.display = 'block';
	}
	return false;
}

function hangupOnClick(trId){
	if (confirm("<?echo $locate->Translate("Are you sure to hangup this call");?>"+"?")){
		//alert(document.getElementById( trId + '-channel').value);
		//return false;
//		"Local/84350822-legb-channel"
		hangup(document.getElementById( trId + '-channel').value);
		hangup(document.getElementById( trId + '-dstchan').value);
		hangup(document.getElementById( trId + '-legb-channel').value);
		//alert(document.getElementById( trId + '-channel').value);
		//alert(document.getElementById( trId + '-dstchan').value);
		//alert(document.getElementById( trId + '-legb-channel').value);
	}
	return false;
}

function removeLocalDiv(divId){
	if (confirm("<?echo $locate->Translate("Are you sure to remove this box");?>"+"?"))
	{
		oDiv = document.getElementById(divId + '-divContainer');
		oContainer =  document.getElementById('divMainContainer');
		oContainer.removeChild(oDiv);//
		xajax_removeLocalChannel(divId);
	}
}


function searchRate(){
	var objSearchRate = document.getElementById("iptSearchRate");
	var searchRateType = document.getElementById("searchRateType").value;
	if (objSearchRate.value != '')
		xajax_searchRate(objSearchRate.value,searchRateType);
	else
		document.getElementById("divRate").innerHTML = '';
}
		//-->
		</SCRIPT>
		<script language="JavaScript" src="js/common.js"></script>		
		<script language="JavaScript" src="js/astercrm.js"></script>
		<LINK href="skin/default/css/layout_simple.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>

		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>

	</head>
	<body onload="init();">
		<div id="divTitle" name="divTitle" style="width: 800px; padding:5px 0;vertical-align:bottom;"></div>
		<div id="divPanel" name="divPanel" class="divPanel" style="z-index:999;">
			<ul id="nav">
			<?php if($config['system']['sysstatus_new_window'] == 'yes') $target='_blank'?>
				<li><a href="checkout.php" target="<? echo $target ?>"><?echo $locate->Translate("Admin");?></a></li>
				<li><a href="spsystemstatus.php" target="<? echo $target ?>"><?echo $locate->Translate("Profi");?></a></li>
				<li><a href="manager_login.php" onclick="return confirm('<?echo $locate->Translate("are you sure to exit");?>');"><?echo $locate->Translate("Log Out");?></a></li>
			</ul>
		</div>
		<div id="divMain" style="clear:both; width:100%;">
		<div style="height:1px; background:#f1f1f1; overflow:hidden; width:85%;"></div>
		<div id="divNav"></div>
		<div id="AMIStatudDiv" name="AMIStatudDiv"></div>
		
		&nbsp;<?echo $locate->Translate("Last Refresh Time");?>: <span id="spanLastRefresh" name="spanLastRefresh"></span>&nbsp;&nbsp;&nbsp;&nbsp;<?echo $locate->Translate("Limit Status");?>:&nbsp;<span id="spanLimitStatus" name="spanLimitStatus"></span><br>
		<input type="button" value="<?echo $locate->Translate("Callshop Status");?>" onclick="showCallshopStatus();">

		<div id="divAmount" style="display:none;">
		&nbsp;<?echo $locate->Translate("Amount");?>:&nbsp;<span id="spanAmount" name="spanAmount"></span>&nbsp;&nbsp;&nbsp;&nbsp;<?echo $locate->Translate("Cost");?>:&nbsp;<span id="spanCost" name="spanCost"></span>&nbsp;&nbsp;&nbsp;&nbsp;<?echo $locate->Translate("Limit");?>:&nbsp;<span id="spanLimit" name="spanLimit"> </span>&nbsp;&nbsp;&nbsp;&nbsp;<?echo $locate->Translate("Current Credit");?>:&nbsp;<span id="spancurcredit" name="spancurcredit"></span>&nbsp;&nbsp;&nbsp;&nbsp;<?echo $locate->Translate("Available Balance");?>:&nbsp;<span id="spanbalance" name="spanbalance"></span><br>
		</div>
		<div>
				<input type="text" size="10" name="iptSearchRate" id="iptSearchRate">
				<select id="searchRateType" name="searchRateType">
					<option value="prefix"><?echo $locate->Translate("Prefix");?></option>
					<option value="dest"><?echo $locate->Translate("Destination");?></option>
				</select>
				<input type="button" value="<?echo $locate->Translate("Search Rate");?>" onclick="searchRate();">
				<div id="divRate" name="divRate"></div>	
		</div>
	<?if ($_SESSION['curuser']['allowcallback'] == 'yes'){?>
		<div id="divCallback" name="divCallback" class="formDiv drsElement" style="left: 450px; top: 50px;visibility:visible;width:250px;">
			<table width="100%" border="1" align="center" class="adminlist" >
			<tr class="drsMoveHandle">
				<th align="right" valign="center" >
					&nbsp;
				</th>
			</tr>
			<tr >
			<td>
				<fieldset><legend><?echo $locate->Translate("Callback");?></legend>
			<form action="" method="post">
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td><?echo $locate->Translate("Ori");?>:</td>
					<td><input type="text" size="17" maxlength="17" id="iptLegB" name="iptLegB"></td>
				</tr>
				<tr>
					<td><?echo $locate->Translate("Dest");?>:</td>
					<td><input type="text" size="17" maxlength="17" id="iptLegA" name="iptLegA"></td>
				</tr>
				<tr>
					<td><?echo $locate->Translate("Credit");?>:</td>
					<td><input type="text" size="6" maxlength="6" id="creditLimit" name="creditLimit"></td>
				</tr>
				<tr>
					<td colspan=2>
						<input type="button" onclick="invite();return false;" value="<?echo $locate->Translate("Start");?>" >
					</td>
				</tr>
			</table>
			</form>
				</fieldset>
			</td></tr>
			</table>
		</div>
	<?}?>
		<form method="post" id="peerStatus">
			<div class="container" id="divMainContainer">
				
			</div>
			
		</form>

		<input type="hidden" name="curid" id="curid" value="0"/>
		<input type="hidden" name="creditlimittype" id="creditlimittype" value=""/>
</div>
		
		<div id="divCopyright"></div>
		<script language="JavaScript" src="js/niftyplayer.js"></script>
		<div><object id='hangup-beep' classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"  width="0" height="0" ><param name="movie" value="sound/niftyplayer.swf?file=sound/beep.mp3&as=0"><param name="quality" value="high"><embed src="sound/niftyplayer.swf?file=sound/beep.mp3&as=0" quality="high"  width="0" height="0"  type="application/x-shockwave-flash" swLiveConnect="true" name="hangup-beep" ></embed></object></div>
	</body>
</html>
