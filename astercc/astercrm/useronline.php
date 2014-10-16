<?php
/*******************************************************************************
* useronline.php
* useronline information management interface

* Function Desc
	useronline management

* div:							
				divNav				show management function list
				grid				show contact grid
				msgZone				show action result
				divCopyright		show copyright
				formDiv				show add contact form
				formCustomerInfo	show customer detail
				formContactInfo		show contact detail
				formNoteInfo		show note detail
				formEditInfo		show export button
				exportForm          记录要导出的sql语句

********************************************************************************/

require_once('useronline.common.php');
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
			//make div draggable
			dragresize.apply(document);
		}

		function exportCustomer(){
			xajax_export();
		}
		function ckbAllOnClick(objCkb){
			var ockb = document.getElementsByName('ckb[]');
			for(i=0;i<ockb.length;i++) {				
				if (ockb[i].checked != objCkb.checked){
					ockb[i].checked = objCkb.checked;
				}
			}			
		}
		//-->
		</SCRIPT>

		<script language="JavaScript" src="js/astercrm.js"></script>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>

	<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>

	</head>
	<body onload="init();">
	<div id="divNav"></div>
	<div id="divActive" name="divActive">

		<input type="button" value="" id="btnUseronlineReport" name="btnUseronlineReport" onClick="window.location='user_online.php';" />
	</div>
	<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
		<tr>
			<td style="padding: 0px;">
				<fieldset>
					<div id="formDiv"  class="formDiv drsElement drsMoveHandle" 
						style="left: 450px; top: 50px;width: 500px"></div>
					<div id="grid" align="center"></div>
					<div id="msgZone" name="msgZone" align="left"> </div>
				</fieldset>
			</td>
		</tr>
	</table>
	<form name="exportForm" id="exportForm" action="dataexport.php">
		<input type="hidden" value="" id="hidSql" name="hidSql" />
		<input type="hidden" value="export" id="exporttype" name="exporttype" />
		<input type="hidden" value="" id="maintable" name="maintable" />
	</form>
	<div id="divCopyright"></div>
	</body>
</html>