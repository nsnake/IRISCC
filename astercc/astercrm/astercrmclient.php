<?php
require_once('config.php');
require_once('astercrmclient.common.php');
$clientUsername = $_GET['username'];
$clientPasswd = $_GET['passwd'];
$clientLang = $_GET['locate'];
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
	<script language="JavaScript" src="js/dhtmlgoodies_calendar.js"></script>
	<LINK href="js/dhtmlgoodies_calendar.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>

		<script type="text/javascript">

		function hangup(){
			//alert (xajax.$('callerChannel').value);
			//alert (xajax.$('calleeChannel').value);
			callerChan = xajax.$('callerChannel').value;
			calleeChan = xajax.$('calleeChannel').value;
			setTimeout("xajax_hangup(callerChan)",1000);
			setTimeout("xajax_hangup(calleeChan)",1000);
		}

		function updateEvents(){
			myFormValue = xajax.getFormValues("myForm");
			xajax_listenCalls(myFormValue);
				// dont pop new window when there already a window exsits
		}

		function monitor(){
			if (xajax.$('callerChannel').value.indexOf("local") < 0 )
				channel = xajax.$('callerChannel').value;
			else
				channel = xajax.$('calleeChannel').value;
			callerid = xajax.$('callerid').value;
			if (xajax.$('btnMonitorStatus').value == 'recording')
				xajax_monitor(channel,callerid,'stop');
			else
				xajax_monitor(channel,callerid,'start',document.getElementById("uniqueid").value);

			return false;
		}

		function init(){
			username = '<?php echo $clientUsername ?>';
			passwd = '<?php echo $clientPasswd ?>';
			lang = '<?php echo $clientLang ?>';
			xajax_init(username,passwd,lang);
			updateEvents();

			//make div draggable
			dragresize.apply(document);
//			xajax.loadingFunction = showProcessingMessage;
//			xajax.doneLoadingFunction = hideProcessingMessage;
		}

		function transfer(){
			setTimeout("xajax_transfer(xajax.getFormValues('myForm'))",1000);
		}

		function trim(stringToTrim) {
			return stringToTrim.replace(/^\s+|\s+$/g,"");
		}

		function invite(){
			//alert('ok');
			if (document.getElementById("uniqueid").value != '')
				return false;
			src = trim(xajax.$('iptSrcNumber').value);
			dest = trim(xajax.$('iptDestNumber').value);

			if (src == '' && dest == '')
				return false;
			if (src == ''){
				xajax.$('iptSrcNumber').value = xajax.$('extension').value;
				src = xajax.$('extension').value;
			}

			if (dest == ''){
				xajax.$('iptDestNumber').value = xajax.$('extension').value;
				dest = xajax.$('extension').value;
			}

			setTimeout("xajax_invite(src,dest)",1000);
		}

		function showportal(dst){
			if(dst == ""){
				window.open('portal.php');
			}else{
				window.open('portal.php?clientdst='+dst);
			}
		}
		</script>

	</head>
	<body onload="init();" style="PADDING-RIGHT: 5px;PADDING-LEFT: 5px;">
	<form name="myForm" id="myForm">
		<div><span id="myevents"></span>&nbsp;<input type="button" value="" id="btnShowPortal" name="btnShowPortal" onclick="showportal('');"></div>

		<div id="divTrunkinfo" name="divTrunkinfo"></div>
		<div id="divDIDinfo" name="divDIDinfo"></div>
		<div id="divCallCtrl">
		<input type="text" value="" name="iptSrcNumber" id="iptSrcNumber" size="10">&nbsp;>&nbsp;<SELECT id="iptDestNumber" name="iptDestNumber"></SELECT>&nbsp;
		<span id="spanCallCtrl" name="spanCallCtrl">
			<input type="button" id="btnCallCtrl" name="btnCallCtrl" value="" onclick="invite();">
		</span>
		</div>
		<br>
		<div id="divTransfer">
		<span id="spanTransfer" name="spanTransfer">
			<SELECT id="sltExten" name="sltExten">
			</SELECT>
			<INPUT TYPE="text" name="iptTtansfer" id="iptTtansfer" size="12">
			<INPUT type="button" value="" id="btnTransfer" onclick="transfer();">
		</span>
		</div>
		<input type="hidden" name="extensionStatus" id="extensionStatus" value=""/>
		<input type="hidden" name="username" id="username" value=""/>
		<input type="hidden" name="extension" id="extension" value=""/>
		<input type="hidden" name="uniqueid" id="uniqueid" value=""/>
		<input type="hidden" name="callerid" id="callerid" value=""/>
		<input type="hidden" name="curid" id="curid" value="0"/>
		<input type="hidden" name="callerChannel" id="callerChannel" value=""/>
		<input type="hidden" name="calleeChannel" id="calleeChannel" value=""/>
		<input type="hidden" name="direction" id="direction" value=""/>
		<input type="hidden" name="mycallerid" id="mycallerid" value=""/>
	</form>
	<br>
	<div id="divMonitor">
			<span id="spanMonitor" name="spanMonitor"></span>:
			<span id="spanMonitorStatus" name="spanMonitorStatus"></span>
			<input type='button' value='' name="btnMonitor" id="btnMonitor" onclick="monitor();return false;">
			<input type='hidden' value='' name="btnMonitorStatus" id="btnMonitorStatus">
	</div>
	<br>
	<div id="divSearchContact" name="divSearchContact" class="divSearchContact">
		<input type="text" value="" name="iptCallerid" id="iptCallerid">&nbsp;<input type="button" id="btnSearchContact" name="btnSearchContact" value="" onclick="showportal(xajax.$('iptCallerid').value);">
	</div>
  </body>
</html>

<?php

?>