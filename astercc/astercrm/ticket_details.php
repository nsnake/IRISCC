<?php
/*******************************************************************************
* ticket_details.php

* 功能描述
	任务计划


* Page elements

* div:							
				divNav				show management function list
				formDiv				show add/edit account form
				grid				show accout grid
				msgZone				show action result
				divCopyright		show copyright
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
require_once('ticket_details.common.php');
//get post parm
$clientDst = $_REQUEST['clientdst'];
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
				dragresize.apply(document);
			}
			function ckbAllOnClick(objCkb){
				var ockb = document.getElementsByName('ckb[]');
				for(i=0;i<ockb.length;i++) {				
					if (ockb[i].checked != objCkb.checked){
						ockb[i].checked = objCkb.checked;
					}
				}			
			}
			function relateBycategoryID(Fid,state) {
				if(state == 'edit') {
					xajax_relateByCategoryId(Fid,document.getElementById('curTicketid').value);
				} else {
					xajax_relateByCategoryId(Fid);
				}
			}

			function relateByGroup(Gid){
				xajax_relateByGroup(Gid);
			}
		-->
		</SCRIPT>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>
		<script type="text/javascript" src="js/astercrm.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<script type="text/javascript" src="js/ajax-dynamic-list.js"></script>
		<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>

	</head>
	<body onload="init();">
	<div id="divNav"></div>
	<div id="divActive" name="divActive">
		<input type="button" value="TicketCategory" id="btnTicketCategory" name="btnTicketCategory" onClick="window.location='ticketcategory.php';" /> 
		<input type="button" value="TicketOplogs" id="btnTicketOplogs" name="btnTicketOplogs" onClick="window.location='ticket_op_logs.php';" />
	</div>
	<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
		<tr>
			<td style="padding: 0px;">
				<fieldset>
					<div id="formDiv"  class="formDiv drsElement" 
						style="left: 450px; top: 50px;width:500px;"></div>
					<div id="grid" name="grid" align="center"> </div>
					<div id="msgZone" name="msgZone" align="left"> </div>
					<div id="formTicketDiv"  class="formDiv drsElement" 
						style="left: 350px; top: 150px;width:500px;"></div>
					<div id="formSubordinateTicketDiv"  class="formDiv drsElement" 
						style="left: 200px; top: 200px;width:800px;"></div>
				</fieldset>
			</td>
		</tr>
	</table>
	<form name="exportForm" id="exportForm" action="dataexport.php" >
		<input type="hidden" value="" id="hidSql" name="hidSql" />
		<input type="hidden" value="" id="maintable" name="maintable" />
		<input type="hidden" value="export" id="exporttype" name="exporttype" />
	</form>
		<div id="divCopyright"></div>
	</body>
</html>