<?php
/*******************************************************************************
* portal.php

* 座席界面文件
* agent portal interface

* Function Desc
	provide agent interface

* 功能描述
	点击呼叫
	主动呼叫
	电话转接
	电话挂断
	来电/去电弹屏


* Page elements

* div:
				divNav				show management function list
				formDiv				show add/edit account form
				grid				show accout grid
				msgZone				show action result
				divCopyright		show copyright
				divMonitor			show monitor button
				divExtension		list extensions
				divPanel			list functions
				divUserMsg			show username and user extension
				divDialList			show if there're calls assigned to the agent]
				divCrm				show 3rd party crm if user dont use internal crm
				myevents			show system status
				click2dial			show input box allow agent enter phone number to dial
				...

* span:
				spanTransfer		show transfer option list when call link
				spanMonitor			show monitor description
				spanMonitorStatus	show system monitor status
				...

* hidden:
				extensionStatus			extension status: idle | link | hangup
				username
				exenstion
				uniqueid				uniqueid if there's a call
				callerid
				mycallerid				store callerid
				curid					current id in events table
				callerChannel
				calleeChannel
				direction				dialout or dialin
				popup					if "yes" then pop-up when there's a call

* javascript function:

				init							page onload function
				monitor							start/stop monitor
				dial							dial a phone
				showProcessingMessage
				hideProcessingMessage
				btnGetAPhoneNumberOnClick
				updateEvents					check database for asterisk events


* Revision 0.0456  2007/1/16 14:16:00  last modified by solo
* Desc: when there's aleady a call, dial and invite function would be disabled

* Revision 0.0456  2007/10/31 9:46:00  last modified by solo
* Desc: add divHangup

* Revision 0.0456  2007/10/29 21:31:00  last modified by solo
* Desc: add div divSearchContact


* Revision 0.045  2007/10/19 15:05:00  last modified by solo
* Desc: make the following div draggable:
			formDiv
			formCustomerInfo
			formContactInfo
			formNoteInfo
			formEditInfo

* Revision 0.045  2007/10/18 15:05:00  last modified by solo
* Desc: comment added

********************************************************************************/
require_once('config.php');
require_once('portal.common.php');
//get post parm
$clientDst = $_REQUEST['clientdst'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8">
		<?php $xajax->printJavascript('include/'); ?>

	<script type="text/javascript" src="js/astercrm.js"></script>
	<script type="text/javascript" src="js/dragresize.js"></script>
	<script type="text/javascript" src="js/dragresizeInit.js"></script>
	<script type="text/javascript" src="js/common.js"></script>
	<script type="text/javascript" src="include/xajax_js/xajax.js"></script>

	<script language="JavaScript" src="js/dhtmlgoodies_calendar.js"></script>
	<LINK href="js/dhtmlgoodies_calendar.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>

		<script type="text/javascript">

		var intervalID = 0; //for stop setInterval of autoDial
		var countCheNum = 0;//for enable/disable the dial button
		var clientDst = "<?php echo $clientDst ?>";
		var enableExternalCrm = "<?php echo $config['system']['enable_external_crm'] ?>";
		var settimeNum = 0;
		var popupToclear;
		if(clientDst != ''){
			//document.getElementById("iptCallerid").value=clentDst;
			ShowProcessingDiv();//show the processing div
			getContact(clientDst);
		}



		function hangup(){
			//alert (xajax.$('callerChannel').value);
			//alert (xajax.$('calleeChannel').value);
			//xajax.$("divMsg").style.visibility = 'visible';
			//xajax.$("divMsg").innerHTML = "Hangup";
			callerChan = xajax.$('callerChannel').value;
			calleeChan = xajax.$('calleeChannel').value;

			setTimeout("xajax_hangup(callerChan)",1000);
			setTimeout("xajax_hangup(calleeChan)",1000);
		}

		function showProcessingMessage(){
			xajax.$('processingMessage').style.display='block';
		}

		function hideProcessingMessage(){
			xajax.$('processingMessage').style.display = 'none';
		}

		//启用加载进度条
		function ShowProcessingDiv(){
			xajax.loadingFunction = showProcessingMessage();
			xajax.doneLoadingFunction = hideProcessingMessage;
		}

		//长连接这取消加载进度条,调用此函数
		function CancelLoading(){
			xajax.loadingFunction = function(){xajax.$('processingMessage').style.display = 'none';};
			xajax.doneLoadingFunction = function(){xajax.$('processingMessage').style.display = 'none';};
		}

		function dial(phonenum,first,myvalue,dtmf,diallistid){
			myFormValue = xajax.getFormValues("myForm");
			dialnum = phonenum;
			firststr = first;
			curdiallistid = diallistid;

			if(typeof(first) != 'undefined'){
				firststr = first;
			}else{
				firststr = '';
			}

			if(typeof(dtmf) != 'undefined'){
				dtmfstr = dtmf;
			}else{
				dtmfstr = '';
			}
			CancelLoading();
			//ShowProcessingDiv();
			if (document.getElementById("uniqueid").value != '')
				return false;
			//xajax.$("divMsg").style.visibility = 'visible';
			//xajax.$("divMsg").innerHTML = "<?php echo $locate->Translate("Dialing to");?>"+" "+phonenum;
			setTimeout("xajax_dial(dialnum,firststr,myFormValue,dtmfstr,curdiallistid)",1000);
		}

		function btnGetAPhoneNumberOnClick(){
			ShowProcessingDiv();
			xajax_addWithPhoneNumber();
		}

		function knowledgechange(knowledgeid){
			if(knowledgeid != ''){
				ShowProcessingDiv();
				xajax_knowledgechange(knowledgeid);
			}
		}

		function updateEvents(){
			myFormValue = xajax.getFormValues("myForm");

			CancelLoading();

			xajax_listenCalls(myFormValue);

			//xajax_listenCalls(myFormValue);
				// dont pop new window when there already a window exsits
				if (xajax.$('formDiv') != null){
					if (xajax.$('formDiv').style.visibility == 'visible')
						xajax.$('popup').value = 'no';
					else
						xajax.$('popup').value = 'yes';
				}else{
					xajax.$('popup').value = 'yes';
				}

				//if(xajax.$('formDiallistPannel').innerHTML == '' )
				//	xajax.$('dpnShow').value = 0;
				//else
				//	xajax.$('dpnShow').value = 1;
			setTimeout("updateEvents()", xajax.$('checkInterval').value);
		}

		function getMsgInCampaign(){
			myFormValue = xajax.getFormValues("myForm");

			CancelLoading();

			xajax_getMsgInCampaign(myFormValue);
			setTimeout("getMsgInCampaign()", 6000);
			return;
		}

		function monitor(){
			//alert(xajax.$('chkMonitor').value);
			ShowProcessingDiv();
			callerChannel = xajax.$('callerChannel').value.toUpperCase();
			calleeChannel = xajax.$('calleeChannel').value.toUpperCase();
			direction = xajax.$('direction').value.toUpperCase();
			//alert(direction);
			//alert(callerChannel);
			if (calleeChannel.indexOf("local") < 0 && calleeChannel.indexOf("local") < 0)
				channel = calleeChannel;
			else
				channel = callerChannel;

			callerid = xajax.$('callerid').value;
			if (xajax.$('btnMonitorStatus').value == 'recording')
				xajax_monitor(channel,callerid,'stop');
			else
				xajax_monitor(channel,callerid,'start',document.getElementById("uniqueid").value,xajax.$('curid').value,direction);
			return false;
		}

		function queuePaused(){
			ShowProcessingDiv();
			if (xajax.$('breakStatus').value == 1)
				xajax_queuePaused(0);
			else
				xajax_queuePaused(1);
			return false;
		}

		function showSurvey(surveyid){
			ShowProcessingDiv();

			customer = document.getElementById("customerid");
			contact = document.getElementById("customerid");

			customerid = customer.value;
			contactid = contact.value;

			if (customerid == 0 && contactid == 0){
				alert("<?php echo $locate->Translate("No customer or contact selected");?>");
			}
			xajax_showSurvey(surveyid);
		}

		function init(){
			xajax_init();
			//ShowProcessingDiv();
			//setTimeout(function(){
				updateEvents();
				xajax_checkworkexten();
				//make div draggable
				//dragresize = new DragResize('dragresize', { minWidth: 50, minHeight: 50, minLeft: 20, minTop: 20, maxLeft: window.screen.width-50, maxTop: window.screen.height + 300 ,skipH:1});
				dragresize.apply(document);
			//},200);
		}

		function invite(){
			if (document.getElementById("uniqueid").value != '')
				return false;

			src = trim(xajax.$('iptSrcNumber').value);
			dest = trim(xajax.$('iptDestNumber').value);

			if (src == '' && dest == '')
				return false;
			if (src == ''){
				return false;
				/*xajax.$('iptSrcNumber').value = xajax.$('extension').value;
				src = xajax.$('extension').value;
				xajax.$("divMsg").style.visibility = 'visible';
				xajax.$("divMsg").innerHTML = "<?php echo $locate->Translate("Dialing to");?>" + " " + src;*/
			}else {
				//xajax.$("divMsg").style.visibility = 'visible';
				//xajax.$("divMsg").innerHTML = "<?php echo $locate->Translate("Dialing to");?>" + " " + src;
			}

			if (dest == ''){
				xajax.$('iptDestNumber').value = xajax.$('extension').value;
				dest = xajax.$('extension').value;
			}
			ShowProcessingDiv();
			//xajax.$('btnDial').disabled = true;

			if(enableExternalCrm != 1) {
				xajax_getContact(src,0);
			}

			setTimeout("xajax_invite(src,dest)",1000);
			checkExtensionStatus('extensionStatus');
		}

		//检测通话状态，处理呼叫(Dial)按钮
		function checkExtensionStatus(objectId) {
			extensionStatus = xajax.$('extensionStatus').value;
			if(extensionStatus == '' || extensionStatus == 'idle') {
				if(countCheNum < 10) {
					countCheNum ++;
					var setTimeout_Dial = setTimeout("checkExtensionStatus('extensionStatus')",1000);
				} else {
					clearTimeout(setTimeout_Dial);
					countCheNum = 0;
					xajax.$('btnDial').disabled = false;
				}
			} else {
				clearTimeout(setTimeout_Dial);
				countCheNum = 0;
			}
		}

		function transfer(target){
			if (target == ''){
				if (xajax.$("iptTtansfer").value != ''){
					target = xajax.$("iptTtansfer").value;
				}else{
					target = xajax.$("sltExten").value;
				}
			}else{
				xajax.$("iptTtansfer").value = target;
			}

			if (target == ''){
				return false;
			}
			ShowProcessingDiv();
			//xajax.$("divMsg").style.visibility = 'visible';
			//xajax.$("divMsg").innerHTML = "<?php echo $locate->Translate("Transfering to");?>" + " " + target;
			setTimeout("xajax_transfer(xajax.getFormValues('myForm'))",500);
			return false;
		}

		function trim(stringToTrim) {
			return stringToTrim.replace(/^\s+|\s+$/g,"");
		}

		function setCampaign(){
			ShowProcessingDiv();
			groupid = document.getElementById("groupid").value;
			if (groupid == '')
				return;
			//清空campaignid
			document.getElementById("campaignid").options.length=0
			xajax_setCampaign(groupid);
		}

		function workctrl(aciton){
			ShowProcessingDiv();
			if (aciton == 'stop'){
				xajax_workoffcheck();
			}
			if(aciton == 'check'){
				xajax_showWorkoff();
			}
			if(aciton == 'start'){
				if(xajax.$("workingextenflag").value != 'yes' ){
					if(xajax.$("workingextenstatus").value != 'ok'){
						if(confirm(xajax.$("workingextenstatus").value)){
							xajax.$("workingextenflag").value = 'yes'
							xajax_workstart();
						}
					}else{
						xajax_workstart();
					}
				}else{
					xajax_workstart();
				}
			}
			return false;
		}

		function autoDial(interval){
			if(interval == '') interval = 30;
			if(interval == 0 ) interval = 30;
			xajax.$("divWork").innerHTML = interval;
			intervalID = setInterval("showsec(xajax.$('divWork').innerHTML)",1000);
		}

		function showsec(i) {
			if(xajax.$('btnWorkStatus').value == '') {
				clearInterval(intervalID);
				xajax.$("divWork").innerHTML = '';
				return false;
			}
			if(i == 0){
				//xajax.$("divWork").innerHTML = 'dialing';
				clearInterval(intervalID);
				workctrl('start');
			}else{
				xajax.$("divWork").innerHTML = i-1;
			}
		}

		function bargeInvite(exten){
			if (document.getElementById("callerChannel").value == '' || document.getElementById("callerChannel").value == 'calleeChannel')
				return false;

			srcchan = trim(xajax.$('callerChannel').value);
			dstchan = trim(xajax.$('calleeChannel').value);
			inviteExten = exten;

			//xajax.$("divMsg").style.visibility = 'visible';
			//xajax.$("divMsg").innerHTML = "<?php echo $locate->Translate("Inviting ");?>" + " " + exten;

			setTimeout("xajax_bargeInvite(srcchan,dstchan,inviteExten)",1000);
		}

		function addSchedulerDial(customerid){
			ShowProcessingDiv();
			xajax_addSchedulerDial(xajax.$("trAddSchedulerDial").style.display,xajax.$("callerid").value,customerid);
		}

		function saveSchedulerDial(customerid){
			ShowProcessingDiv();
			xajax_saveSchedulerDial(xajax.$("sDialNum").value,xajax.$("curCampaignid").value,xajax.$("sDialtime").value,customerid);
		}

		function menuFix() {
			var sfEls = document.getElementById("divExtension").getElementsByTagName("li");
			for (var i=0; i<sfEls.length; i++) {
				sfEls[i].onmouseover=function() {
					this.className+=(this.className.length>0? " ": "") + "sfhover";
				}
				sfEls[i].onMouseDown=function() {
					this.className+=(this.className.length>0? " ": "") + "sfhover";
				}
				sfEls[i].onMouseUp=function() {
					this.className+=(this.className.length>0? " ": "") + "sfhover";
				}
				sfEls[i].onmouseout=function() {
					this.className=this.className.replace(new RegExp("( ?|^)sfhover\\b"),"");
				}
			}
		}

		var ticketNoticDivShow = '',smartMatchDivShow = '';//

		var divTop,divLeft,divWidth,divHeight,docHeight,docWidth,objTimer,i = 0;

		function getSmartMatchMsg() {
			var noticTicketDivHeight = 0;
			if(document.getElementById("ticketNoticDiv").style.visibility == 'visible') {
				ticketNoticDivShow = 'first';
				smartMatchDivShow = 'second';
				noticTicketDivHeight = document.getElementById("ticketNoticDiv").offsetHeight;
			} else {
				smartMatchDivShow = 'first';
				ticketNoticDivShow = 'second';
			}

			try{
				divTop = parseInt(document.getElementById("SmartMatchDiv").style.top,10);
				divLeft = parseInt(document.getElementById("SmartMatchDiv").style.left,10);
				divHeight = parseInt(document.getElementById("SmartMatchDiv").offsetHeight,10);
				divWidth = parseInt(document.getElementById("SmartMatchDiv").offsetWidth,10);
				docWidth = document.documentElement.clientWidth;
				docHeight = document.documentElement.clientHeight;
				document.getElementById("SmartMatchDiv").style.top = parseInt(document.documentElement.scrollTop,10) + docHeight - noticTicketDivHeight + 10 +'px';// divHeight
				document.getElementById("SmartMatchDiv").style.left = parseInt(document.documentElement.scrollLeft,10) + docWidth - divWidth +'px' ;
				//document.getElementById("SmartMatchDiv").style.display="" ;
				document.getElementById("SmartMatchDiv").style.visibility="visible";
				objTimer = window.setInterval("moveDiv()",10) ;
			}
			catch(e){}
		}

		function resizeDiv() {
			i+=1
			//if(i>300) closeDiv() //自动消失
			try{
				divHeight = parseInt(document.getElementById("SmartMatchDiv").offsetHeight,10);
				divWidth = parseInt(document.getElementById("SmartMatchDiv").offsetWidth,10);
				docWidth = document.documentElement.clientWidth;
				docHeight = document.documentElement.clientHeight;

				if(ticketNoticDivShow == 'first') {
					noticTicketDivHeight = document.getElementById("ticketNoticDiv").offsetHeight;

					document.getElementById("SmartMatchDiv").style.top = docHeight - divHeight + parseInt(document.documentElement.scrollTop,10) + 5 - noticTicketDivHeight +'px';
				} else {
					document.getElementById("SmartMatchDiv").style.top = docHeight - divHeight + parseInt(document.documentElement.scrollTop,10) +'px';
				}

				document.getElementById("SmartMatchDiv").style.left = docWidth - divWidth + parseInt(document.documentElement.scrollLeft,10) + 'px';
			}
			catch(e){}
		}

		function moveDiv() {
			try {
				if(parseInt(document.getElementById("SmartMatchDiv").style.top,10) <= (docHeight - divHeight + parseInt(document.documentElement.scrollTop,10))) {
					window.clearInterval(objTimer);
					objTimer = window.setInterval("resizeDiv()",1);
				}
				divTop = parseInt(document.getElementById("SmartMatchDiv").style.top,10) ;

				if(ticketNoticDivShow == 'first') {
					noticTicketDivHeight = document.getElementById("ticketNoticDiv").offsetHeight;

					document.getElementById("SmartMatchDiv").style.top = divTop - 1 - noticTicketDivHeight +'px';
				} else {
					document.getElementById("SmartMatchDiv").style.top = divTop - 1 +'px';
				}
			}
			catch(e){}
		}

		function closeSmartMatch() {
			if(ticketNoticDivShow != '') {
				ticketNoticDivShow = 'first';
				smartMatchDivShow = ''
			}
			//document.getElementById("SmartMatchDiv").style.display="none";
			document.getElementById("SmartMatchDiv").style.visibility="hidden";
			if(objTimer) window.clearInterval(objTimer);
		}

		var divTicketTop,divTicketLeft,divTicketWidth,divTicketHeight,docTicketHeight,docTicketWidth,objTicketTimer,t_i = 0;
		function getTicketNoticeMsg() {
			document.getElementById("ticketNoticDiv").style.visibility="hidden";
			if(objTicketTimer) window.clearInterval(objTicketTimer);

			if(document.getElementById("SmartMatchDiv").style.visibility == 'visible') {
				smartMatchDivShow = 'first';
				ticketNoticDivShow = 'second';
				noticTicketDivHeight = document.getElementById("SmartMatchDiv").offsetHeight;
			} else {
				ticketNoticDivShow = 'first';
				smartMatchDivShow = 'second';
			}
			try{
				divTicketTop = parseInt(document.getElementById("ticketNoticDiv").style.top,10);
				divTicketLeft = parseInt(document.getElementById("ticketNoticDiv").style.left,10);
				divTicketHeight = parseInt(document.getElementById("ticketNoticDiv").offsetHeight,10);
				divTicketWidth = parseInt(document.getElementById("ticketNoticDiv").offsetWidth,10);
				docTicketWidth = document.documentElement.clientWidth;
				docTicketHeight = document.documentElement.clientHeight;
				document.getElementById("ticketNoticDiv").style.top = parseInt(document.documentElement.scrollTop,10) + docTicketHeight + 10 +'px';// divHeight
				document.getElementById("ticketNoticDiv").style.left = parseInt(document.documentElement.scrollLeft,10) + docTicketWidth - divTicketWidth +'px';
				document.getElementById("ticketNoticDiv").style.visibility="visible";
				objTicketTimer = window.setInterval("moveTicketNoticeDiv()",10) ;
			}
			catch(e){}
		}

		function resizeTicketNoticeDiv() {
			t_i+=1
			//if(i>600) closeTicketNotice(); //自动消失
			try{
				divTicketHeight = parseInt(document.getElementById("ticketNoticDiv").offsetHeight,10);
				divTicketWidth = parseInt(document.getElementById("ticketNoticDiv").offsetWidth,10);
				docTicketWidth = document.documentElement.clientWidth;
				docTicketHeight = document.documentElement.clientHeight;

				if(smartMatchDivShow == 'first') {
					noticTicketDivHeight = document.getElementById("SmartMatchDiv").offsetHeight;

					document.getElementById("ticketNoticDiv").style.top = docTicketHeight - divTicketHeight + parseInt(document.documentElement.scrollTop,10) - noticTicketDivHeight +'px';
				} else {
					document.getElementById("ticketNoticDiv").style.top = docTicketHeight - divTicketHeight + parseInt(document.documentElement.scrollTop,10) +'px';
				}

				document.getElementById("ticketNoticDiv").style.left = docTicketWidth - divTicketWidth + parseInt(document.documentElement.scrollLeft,10) + 'px';
			}
			catch(e){}
		}

		function moveTicketNoticeDiv() {
			try {
				if(parseInt(document.getElementById("ticketNoticDiv").style.top,10) <= (docTicketHeight - divTicketHeight + parseInt(document.documentElement.scrollTop,10))) {
					window.clearInterval(objTicketTimer);
					objTicketTimer = window.setInterval("resizeTicketNoticeDiv()",1);
				}

				divTicketTop = parseInt(document.getElementById("ticketNoticDiv").style.top,10) ;
				if(smartMatchDivShow == 'first') {
					noticTicketDivHeight = document.getElementById("SmartMatchDiv").offsetHeight;

					document.getElementById("ticketNoticDiv").style.top = divTicketTop - 1 +'px';
				} else {
					document.getElementById("ticketNoticDiv").style.top = divTicketTop - 1 +'px';
				}
			}
			catch(e){}
		}

		function closeTicketNotice() {
			if(smartMatchDivShow != '') {
				smartMatchDivShow = 'first';
				ticketNoticDivShow = ''
			}
			document.getElementById("ticketNoticDiv").style.visibility="hidden";
			if(objTicketTimer) window.clearInterval(objTicketTimer);
		}

		function showMsgBySmartMatch(msgtype,msg) {
			if (document.getElementById(msgtype)){
				document.getElementById(msgtype).value = msg;
				return true;
			}
			return false;
		}

		function updateCallresult() {
			ShowProcessingDiv();
			result = xajax.$('callresultname').value;
			xajax_updateCallresult(xajax.$('dialedlistid').value,result,xajax.$('tmp60_callerid').value);
			return false;
		}

		function setKnowledge(){
			ShowProcessingDiv();
			xajax_setKnowledge();
		}

		function setSecondCampaignResult(){
			ShowProcessingDiv();
			xajax.$('callresultname').value = xajax.$('fcallresult').options[xajax.$('fcallresult').selectedIndex].text;
			//alert(xajax.$('callresultname').value);
			parentid = document.getElementById("fcallresult").value;
			if (parentid == '')
				return;
			//清空campaignid
			document.getElementById("scallresult").options.length=0
			xajax_setSecondCampaignResult(parentid);
		}

		function setCallresult(obj){
			ShowProcessingDiv();
			id = obj.value;
			//alert(id);
			xajax_setCallresult(id);
		}

		function insertIntoDnc() {
			ShowProcessingDiv();
			campaignId = document.getElementById('dndlist_campaignid').value;
			callerid = document.getElementById('callerid').value;
			xajax_insertIntoDnc(callerid,campaignId);
		}

		function setTimeoutforPopup() {
			if(document.getElementById('clear_popup').value != '0' && document.getElementById('clear_popup').value != '') {
				popupToclear = setTimeout("xajax_clearPopup()",parseInt(document.getElementById('clear_popup').value)*1000);
			}
		}
		function clearSettimePopup() {
			clearTimeout(popupToclear);
		}

		function addTicket(customerid) {
			ShowProcessingDiv();
			xajax_addTicket(customerid);
		}

		function relateByCategory() {
			ShowProcessingDiv();
			xajax_relateByCategory(document.getElementById('ticketcategoryid').value);
		}

		function saveTicket(f) {
			ShowProcessingDiv();
			xajax_saveTicket(f);
		}

		function AllTicketOfMyself(Cid) {
			ShowProcessingDiv();
			xajax_AllTicketOfMy(Cid,'customer_ticket');
		}

		function showMyTickets(Id,State) {
			ShowProcessingDiv();
			xajax_showMyTickets(Id,State);
		}

		function showRecentCdr(Id,cdrtype) {
			ShowProcessingDiv();
			xajax_showRecentCdr(Id,cdrtype);
		}
		function saveDiallistMain(f){
			ShowProcessingDiv();
			xajax_saveDiallistMain(f);
		}
		function getContact(value,customerId) {
			if(value != '') {
				ShowProcessingDiv();
			}
			//if(customerId == 'undefined')
			if(typeof(customerId) == 'undefined') {
				customerId = '';
			}
			xajax_getContact(value,customerId);
		}

		function showDiallist(userexten,customerid,start,limit,filter,content,order,divName,ordering,stype) {
			ShowProcessingDiv();
			xajax_showDiallist(userexten,customerid,start,limit,filter,content,order,divName,ordering,stype);
		}

		function agentWorkstat() {
			ShowProcessingDiv();
			xajax_agentWorkstat();
		}

		function showMyTicketsGrid(id,Ctype,start,limit,filter,content,order,divName,ordering,stype) {
			ShowProcessingDiv();
			xajax_showMyTickets(id,Ctype,start,limit,filter,content,order,divName,ordering,stype);
		}
		function AllTicketOfMyGrid(cid,Ctype,start,limit,filter,content,order,divName,ordering,stype) {
			ShowProcessingDiv();
			xajax_AllTicketOfMy(cid,Ctype,start,limit,filter,content,order,divName,ordering,stype);
		}
		function showGrid(id,start,limit,filter,content,order,divName,ordering,stype) {
			ShowProcessingDiv();
			xajax_showGrid(id,start,limit,filter,content,order,divName,ordering,stype);
		}
		function showRecentCdrGrid(id,cdrtype,start,limit,filter,content,order,divName,ordering,stype) {
			ShowProcessingDiv();
			xajax_showRecentCdr(id,cdrtype,start,limit,filter,content,order,divName,ordering,stype);
		}
		function curTicketDetail(Id) {
			ShowProcessingDiv();
			xajax_curTicketDetail(Id);
		}
		function curCustomerDetail(Id) {
			ShowProcessingDiv();
			xajax_curCustomerDetail(Id);
		}
		function relateBycategoryID(Fid,state) {
			if(state == 'edit') {
				xajax_relateByCategoryId(Fid,document.getElementById('curTicketid').value);
			} else {
				xajax_relateByCategoryId(Fid);
			}
		}

		function searchCdrFormSubmit(searchFormValue,numRows,limit,id,type){
			ShowProcessingDiv();
			xajax_searchCdrFormSubmit(searchFormValue,numRows,limit,id,type);
		}
		function searchDiallistFormSubmit(searchFormValue,numRows,limit,id,type){
			ShowProcessingDiv();
			xajax_searchDiallistFormSubmit(searchFormValue,numRows,limit,id,type);
		}
		function searchRecordsFormSubmit(searchFormValue,numRows,limit,id,type){
			ShowProcessingDiv();
			xajax_searchRecordsFormSubmit(searchFormValue,numRows,limit,id,type);
		}
		function searchTicketsFormSubmit(searchFormValue,numRows,limit,id,type){
			ShowProcessingDiv();
			xajax_searchTicketsFormSubmit(searchFormValue,numRows,limit,id,type);
		}

		function searchMyTicketsFormSubmit(Cid,numRows,limit){
			ShowProcessingDiv();
			xajax_AllTicketOfMy(Cid,'customer_ticket',numRows,limit);
		}

		function edit(parm,table){
			ShowProcessingDiv();
			xajax_edit(parm,table);
		}

		function showDetail(parm){
			ShowProcessingDiv();
			xajax_showDetail(parm);
		}

		function addVirginMobile(){
			ShowProcessingDiv();
			var curid = document.getElementById('curid').value;
			if(curid != '' && typeof(curid) != 'undefined' && curid != null && curid != 0) {
				window.open('clientinformation_agent.php?curid='+curid);
			} else {
				window.open('clientinformation_agent.php');
			}
		}

		function calculateMessage(obj){
			if(obj == '') {
				obj = document.getElementById('SMSmessage');
			}
			var curCode = obj.value;
			if(curCode.length <= 70) {
				var remainCodes = 70-curCode.length;
				document.getElementById('inputcodeLength').innerHTML = remainCodes;
			} else {
				document.getElementById('SMSmessage').value = curCode.substr(0,70);
			}
		}

		function SendSms(f){
			ShowProcessingDiv();
			xajax_SendSMS(f);
		}

		function SendSmsForm(sendtype){
			ShowProcessingDiv();
			var objId = '';
			if(sendtype == 'callerid') {
				objId = document.getElementById('callerid').value;
			} else if(sendtype == 'campaign_number') {
				objId = document.getElementById('dndlist_campaignid').value;
			} else if(sendtype == 'trunk_number') {
				objId = document.getElementById('trunkinfo_number').value;
			}
			xajax_SendSmsForm(sendtype,objId);
		}

		function relateByGroup(Gid){
			xajax_relateByGroup(Gid);
		}

		</script>
<?php if ($config['system']['enable_external_crm'] == false && $config['google-map']['key'] != ''){
	if($_SESSION['curuser']['country'] == 'cn')
		$map_locate = 'ditu';
	else
		$map_locate = 'maps';
?>
	<script src="http://<?php echo $map_locate;?>.google.com/maps?file=api&v=2&key=<?php echo $config['google-map']['key'];?>" type="text/javascript"></script>
<?php }
?>
	</head>
	<body onload="init();" style="PADDING-RIGHT: 20px;PADDING-LEFT: 20px;">
	<form name="myForm" id="myForm">
		<div><span id="divUserMsg" name="divUserMsg"></span>&nbsp;&nbsp;&nbsp;<span id="myevents"></span>&nbsp;&nbsp;&nbsp;<span><input type="button" value="<?php echo $locate->Translate("Hangup")?>" name="btnHangup" id="btnHangup" onclick="hangup();" disabled="true">&nbsp;&nbsp;&nbsp;<input type="button" value="<?php echo $locate->Translate("Clear Screen")?>" onclick="javascript:xajax_clearPopup();clearTimeout(popupToclear);"></span></div><br/>

		<div id="divHangup" name="divHangup">
			<!--&nbsp;&nbsp;&nbsp;<span id="spnPause"><input type="button" value="<?php echo $locate->Translate("Break")?>" name="btnPause" id="btnPause" onclick="queuePaused();" ></span><input id="clkPauseTime" name="clkPauseTime" type="hidden">
			<span id="agentData"></span>-->

			<div id="divTrunkinfo" name="divTrunkinfo"></div>
			<div id="divDIDinfo" name="divDIDinfo"></div>
		</div>

		<div id="divCallresult" name="divCallresult" style="display:none"></div>

		<span id="spanTransfer" name="spanTransfer">
			<SELECT id="sltExten" name="sltExten">
			</SELECT>
			<INPUT TYPE="text" name="iptTtansfer" id="iptTtansfer" size="15">
			<INPUT type="button" value="<?php echo $locate->Translate("Transfer");?>" id="btnTransfer" onclick="transfer('');">
			<span id="spanAttendtran"><input type="checkbox" value="yes" id="attendtran" name="attendtran"><?php echo $locate->Translate("Attended");?></span>
		</span>
		<div id="divHolding" name="divHolding" ></div>


		<div id="divMonitor"><br/>
			<span id="monitorTitle"><?php echo $locate->Translate("monitor")?></span>
			<span id="spanMonitorStatus" name="spanMonitorStatus"></span>
			<input type='button' value='' name="btnMonitor" id="btnMonitor" onclick="monitor();return false;">
			<input type='hidden' value='' name="btnMonitorStatus" id="btnMonitorStatus">
			<input type='checkbox' name='chkMonitor' id="chkMonitor">
			<?php echo $locate->Translate("Always record when connected");?>
		</div>
		<input type="hidden" name="checkInterval" id="checkInterval" value="2000"/>
		<input type="hidden" name="breakStatus" id="breakStatus" value=""/>
		<input type="hidden" name="extensionStatus" id="extensionStatus" value=""/>
		<input type="hidden" name="username" id="username" value=""/>
		<input type="hidden" name="extension" id="extension" value=""/>
		<input type="hidden" name="uniqueid" id="uniqueid" value=""/>
		<input type="hidden" name="callerid" id="callerid" value=""/>
		<input type="hidden" name="curid" id="curid" value="0"/>
		<input type="hidden" name="callerChannel" id="callerChannel" value=""/>
		<input type="hidden" name="calleeChannel" id="calleeChannel" value=""/>
		<input type="hidden" name="direction" id="direction" value=""/>
		<input type="hidden" name="popup" id="popup" value="yes"/>
		<input type="hidden" name="workingextenflag" id="workingextenflag" value=""/>
		<input type="hidden" name="workingextenstatus" id="workingextenstatus" value=""/>
		<input type='hidden' value="" name="btnWorkStatus" id="btnWorkStatus">
		<input type='hidden' value="" name="callResultStatus" id="callResultStatus">
		<input type="hidden" name="dpnShow" id="dpnShow" value="0"/>
		<input type="hidden" name="awsShow" id="awsShow" value="0"/>
		<input type="hidden" name="dndlist_campaignid" id="dndlist_campaignid" value="0" />
		<input type="hidden" name="clear_popup" id="clear_popup" value="0" />
		<input type="hidden" name="trunkinfoStatus" id="trunkinfoStatus" value="0" />
		<input type="hidden" name="trunkinfo_number" id="trunkinfo_number" value="" />
		<input id="clkPauseTime" name="clkPauseTime" type="hidden" value="0">

	</form>
	<input type="hidden" name="mycallerid" id="mycallerid" value=""/>
	<br>
	<div><span id="spanDialList" name="spanDialList"></span>&nbsp;&nbsp;<span id="misson" name="misson"><input type="button" id="btnWork" name="btnWork" value="<?php echo $locate->Translate("Start work")?>"></span>&nbsp;<span id="divWork" name="divWork" align="left" style="font-weight:bold;	"></span></div>

	<div id="processingMessage" name="processingMessage"><div class="UD"></div><div class="vh"><div class="asterLoad"><div class="vZ L4XNt"><span class="v1" id="processingContent"></span></div></div><div class="asterLoad"></div></div><div class="UB"></div></div>
	<!--<div id="processingMessage" name="processingMessage"></div>-->

	<br/>

		<div id="divSearchContact" name="divSearchContact" class="divSearchContact">
			<span id="divInvite">
				<input type="text" value="" name="iptSrcNumber" id="iptSrcNumber" onkeyup="if(xajax.$('iptDestNumber').value == ''){return;} var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode; if (keyCode == 13) {invite();}">&nbsp;<span id="inviteFlag">-></span>&nbsp;<SELECT id="iptDestNumber" name="iptDestNumber" ></SELECT>&nbsp;<input type="button" id="btnDial" name="btnDial" value="<?php echo $locate->Translate("Dial");?>" onclick="invite();">
			</span>&nbsp;&nbsp;&nbsp;
			<span id="sptSearchContact"><input type="text" value="" name="iptCallerid" id="iptCallerid" onkeyup="if(xajax.$('iptCallerid').value == ''){return;} var keyCode = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode; if (keyCode == 13) {getContact(xajax.$('iptCallerid').value);}">&nbsp;<input type="button" id="btnSearchContact" name="btnSearchContact" value="<?php echo $locate->Translate("Search");?>"  onclick="getContact(xajax.$('iptCallerid').value);">&nbsp;&nbsp;</span>
		</div>
		<div id="divMsg" name="divMsg" align="center" class="divMsg"></div>
		<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
			<tr>
				<td style="padding: 0px;">
					<fieldset>
						<div id="formDiv"  class="formDiv drsElement"
							style="left: 450px; top: 50px;width: 510px"></div>
						<div id="formAgentWordStatDiv"  class="formDiv drsElement"
							style="left: 110px; top: 32px;width: 240px;z-index: 999;" ></div>
						<div id="surveyDiv"  class="formDiv drsElement"
							style="left: 20px; top: 20px;width: 500px; z-index: 999;"></div>
						<div id="formCustomerInfo" class="formDiv drsElement"
							style="left: 20px; top: 50px;width: 650px"></div>
						<div id="formContactInfo" class="formDiv drsElement"
							style="left: 20px; top: 330px;width: 600px"></div>
						<div id="formCdr" class="formDiv drsElement"
							style="left: 20px; top: 330px; width: 900px"></div>
						<div id="formRecentCdr" class="formDiv drsElement"
							style="left: 20px; top: 30px; width:750px"></div>
						<div id="formRecords" class="formDiv drsElement"
							style="left: 20px; top: 330px; width: 900px;height:auto"></div>
						<div id="formDiallist" class="formDiv drsElement"
							style="left: 20px; top: 330px; width: 850px"></div>
						<div id="formaddDiallistInfo"  class="formDiv drsElement"
							style="left: 450px; top: 50px;z-index:210"></div>
						<div id="formeditDiallistInfo"  class="formDiv drsElement"
							style="left: 450px; top: 50px;"></div>
						<div id="formNoteInfo" class="formDiv  drsElement"
							style="left: 450px; top: 330px;width: 500px;z-index:5;"></div>
						<div id="formWorkoff" class="formDiv  drsElement"
							style="left: 300px; top: 0px; z-index: 999; "></div>
						<div id="formEditInfo" class="formDiv drsElement"
							style="left: 450px; top: 50px;width: 500px"></div>
						<div id="formplaymonitor"  class="formDiv drsElement"
							style="left: 450px; top: 50px;width: 350px; z-index:999"></div>
						<div id="formDiallistPopup"  class="formDiv drsElement"
							style="left: 450px; top: 50px;width: 350px; z-index:201"></div>
						<div id="formDiallistPannel"  class="formDiv drsElement"
							style="left: 150px; top: 130px;width: 850px; z-index:201;"></div>
						<div id="formKnowlagePannel"  class="formDiv drsElement"
							style="left: 380px; top: 30px;width: 600px; z-index:1"></div>
						<div id="grid" align="center"></div>
						<div id="msgZone" name="msgZone" align="left"> </div>
						<div id="external_crmDiv" style="display:none;"></div>
						<div id="external_crm_openNewDiv" style="display:none;"></div>
						<div id="formTicketDetailDiv"  class="formDiv drsElement"
							style="left: 600px; top: 300px;width: 490px"></div>
						<div id="formMyTickets"  class="formDiv drsElement"
							style="left: 500px; top: 150px;width: 800px"></div>
						<div id="formCurTickets"  class="formDiv drsElement"
							style="left: 300px; top: 300px;width: 800px"></div>
						<div id="formSendSMS"  class="formDiv drsElement"
							style="left: 300px; top: 200px;width: 500px"></div>
						<div id="formHighestProrityNote"  class="formDiv drsElement"
							style="left: 300px; top: 200px;width: 500px"></div>
						<div id="formLastestNote"  class="formDiv drsElement"
							style="left: 350px; top: 150px;width: 500px"></div>
						<div id="formSubordinateTicketDiv"  class="formDiv drsElement"
						style="left: 200px; top: 200px;width:800px;"></div>
						<div id="formRequiredReasionDiv"  class="formDiv drsElement"
						style="left: 250px; top: 50px;width:500px;"></div>
						<div id="formdAddSechedualaraDiv"  class="formDiv drsElement"
						style="left: 300px; top: 200px;width:500px;z-index:800"></div>
					</fieldset>
				</td>
			</tr>
		</table>
	<div id="divCrm" name="divCrm"></div>
	<div id="divPanel" name="divPanel" class="divPanel"></div>

	<div id="divGetMsgInCampaignP" class="drsElement drsMoveHandle" style="left: 500px; top: 20px; position: absolute;z-index:2;text-align: center;border:1px dashed #EAEAEA;color:#006600; background:#fbfbfb;">
		<div width="100%" class="divGetMsgInCampaigntitle"><img src="skin/default/images/movedesc.png" onclick="if(xajax.$('divGetMsgInCampaign').style.display!='none'){xajax.$('divGetMsgInCampaign').style.display='none';this.src='skin/default/images/moveasc.png';xajax.$('divGetMsgInCampaignP').style.height='20px';}else{xajax.$('divGetMsgInCampaign').style.display='';this.src='skin/default/images/movedesc.png';xajax.$('divGetMsgInCampaignP').style.height='';}"><?php echo $locate->Translate("Campaign Pannel")?>(<?php echo $locate->Translate("Queue")?>)</div>
		<div width="100%" id="divGetMsgInCampaign"></div>
	</div>

    <div class="divExtension drsElement drsMoveHandle"
		style="left: 760px; top: 20px;	width: 160px;
				position: absolute;
				z-index:0;
				text-align: center;
				border: 1px dashed #EAEAEA;
				color:#006600; background:#fbfbfb;">

	<div class="divExtensiontitle"><img src="skin/default/images/movedesc.png" onclick="if(xajax.$('divExtension').style.display!='none'){xajax.$('divExtension').style.display='none';this.src='skin/default/images/moveasc.png'}else{xajax.$('divExtension').style.display='';this.src='skin/default/images/movedesc.png'}"><?php echo $locate->Translate("Group Pannel")?> </div>
	<div id="divExtension" name="divExtension" >

	</div>
	</div>

	<div id="divMap" class="drsElement"
		style="left: 450px; top: 20px;	width: 300px;
					position: absolute;
					z-index:0;
					text-align: center;
					border: 1px dashed #EAEAEA;
					color:#006600;
					visibility:hidden;">
		<table width="100%" border="1" align="center" class="adminlist" >
			<tr class="drsMoveHandle">
				<th align="right" valign="center" >
					<img src="skin/default/images/close.png" onClick='javascript: document.getElementById("divMap").style.visibility="hidden";return false;' title="Close Window" style="cursor: pointer; height: 16px;">
				</th>
			</tr>
			<tr>
				<td>
					<fieldset><legend><?php echo $locate->Translate("Google Map")?></legend>
					<div id="map" style="width: 300px; height: 300px"></div>
					</fieldset>
				</td>
			</tr>
		</table>
	</div>
	<div id='SmartMatchDiv' style="position:absolute;z-index:99999; left:0px;visibility:hidden;width: 320px;"><table width="100%" border="1" align="center" class="adminlist">
			<tr>
				<th align="right" valign="center" >
					<img src="skin/default/images/close.png" onClick='closeSmartMatch();return false;' title="Close Window" style="cursor: pointer; height: 16px;">
				</th>
			</tr>
			<tr><td><fieldset><legend><?php echo $locate->Translate("which customer has similar number"); ?>:</legend><div id="smartMsgDiv" style="width: 280px;height:160px;OVERFLOW-y:auto;OVERFLOW-x:auto;"></div></fieldset></td></tr></table>
	</div>
	<div id='ticketNoticDiv' style="position:absolute;z-index:99999; left:0px;visibility:hidden;width: 320px;">
		<table width="100%" border="1" align="center" class="adminlist">
			<tr>
				<th align="right" valign="center" >
					<img src="skin/default/images/close.png" onClick='closeTicketNotice();return false;' title="Close Window" style="cursor: pointer; height: 16px;">
				</th>
			</tr>
			<tr>
				<td><fieldset>
					<legend><?php echo $locate->Translate("tickets notice"); ?>:</legend>
					<div id="noticeTicketMsgDiv" style="width: 280px;height:80px;OVERFLOW-y:auto;OVERFLOW-x:auto;"></div>
				</fieldset></td>
			</tr>
		</table>
	</div>
	<div id="divCopyright"></div>
	</body>
	<script type="text/javascript">
        var Sys = {};
        var ua = navigator.userAgent.toLowerCase();
        var s;
        (s = ua.match(/msie ([\d.]+)/)) ? Sys.ie = s[1] : 0;
        //(s = ua.match(/firefox\/([\d.]+)/)) ? Sys.firefox = s[1] :
        //(s = ua.match(/chrome\/([\d.]+)/)) ? Sys.chrome = s[1] :
        //(s = ua.match(/opera.([\d.]+)/)) ? Sys.opera = s[1] :
        //(s = ua.match(/version\/([\d.]+).*safari/)) ? Sys.safari = s[1] : 0;

        //以下进行测试
        if (Sys.ie == '7.0') xajax.$('divGetMsgInCampaignP').style.right = 250;//document.write('IE: ' + Sys.ie);
        //if (Sys.firefox) document.write('Firefox: ' + Sys.firefox);
        //if (Sys.chrome) document.write('Chrome: ' + Sys.chrome);
        //if (Sys.opera) document.write('Opera: ' + Sys.opera);
        //if (Sys.safari) document.write('Safari: ' + Sys.safari);
    </script>
</html>