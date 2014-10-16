<?php
/*******************************************************************************
* queue.php
* 队列管理界面文件
* queue interface

* Function Desc

* div
* span
* hidden
* javascript functions
				init

* Revision 0.046  2007/11/7 16:05:00  last modified by solo
* Desc: page created
********************************************************************************/

require_once('queue.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/'); ?>
		<meta http-equiv="Content-Language" content="utf-8" />
		<SCRIPT LANGUAGE="JavaScript">
		<!--
		function init(){
			xajax_init();
		}
		//-->
		</SCRIPT>
		<script language="JavaScript" src="js/astercrm.js"></script>

	<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>

	</head>
	<body onload="init();">
		<div id="divNav"></div>
		<div id="divAMIStatus"></div>
		<div>
			<input type="button" value="queue" onclick="xajax_showQueuesStatus();">
		</div>
		<div id="divQueue" name="divQueue">123456</div>
		<div id="divCopyright"></div>
	</body>
</html>
