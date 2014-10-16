<?php
/*******************************************************************************
* queuestatus.php
* 系统状态文件
* systerm status interface
* 功能描述
	 显示分机状态和正在进行的通话

* Function Desc


* javascript function:		
						showStatus				show sip extension status
						showChannelsInfo		show asterisk channels information
						init					initialize function after page loaded

* Revision 0.045  2007/10/18 17:55:00  last modified by solo
* Desc: page created
********************************************************************************/

require_once('queuestatus.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/'); ?>
		<meta http-equiv="Content-Language" content="utf-8" />
		<SCRIPT LANGUAGE="JavaScript">
		<!--
			var timerShowStatus,timerShowChannelsInfo;
			function showStatus(){
				var curupdated = xajax.$('updated').value;
				//alert(curupdated);
				xajax_showStatus(curupdated);				
				
				timerShowStatus = setTimeout("showStatus()", xajax.$('check_interval').value);
			}

			function init(){
				xajax_init();
				showStatus();
			}
			
		//-->
		</SCRIPT>
		
		<script language="JavaScript" src="js/astercrm.js"></script>

	<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/queuestatus.css" type="text/css" rel="stylesheet" />

	</head>
	<body onload="init();">
		<div id="divNav"></div>
		<div id="AMIStatudDiv" name="AMIStatudDiv"></div>
		<div id="formRequiredReasionDiv"  class="formDiv drsElement" style="left: 250px; top: 50px;width:500px;"></div>

		<div id="divStatus" align="center"></div>
		<div id="channels" align="left" class="groupsystem_channel"></div>
		<div id="divCopyright"></div>
		<input type="hidden" id="check_interval" name="check_interval" value="2000">
		<input type="hidden" id="updated" name="updated" value="0">
	</body>
</html>