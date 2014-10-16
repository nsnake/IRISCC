<?php
/*******************************************************************************
* ticketcategory.php

* 功能描述
	任务计划


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
require_once('ticketcategory.common.php');
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
			
			function relateByGid(Gid,Id) {
				xajax_relateByGid(Gid,Id);
			}
		-->
		</SCRIPT>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>
		<script type="text/javascript" src="js/astercrm.js"></script>
		<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>

	</head>
	<body onload="init();">
	<div id="divNav"></div>
	<div id="divActive" name="divActive">
		<input type="button" value="Group" id="btnGroup" name="btnGroup" onClick="window.location='group.php';" />
		<input type="button" value="Campaign" id="btnCampaign" name="btnCampaign" onClick="window.location='campaign.php';" />
		<input type="button" value="TicketDetails" id="btnTicketDetails" name="btnTicketDetails" onClick="window.location='ticket_details.php';" />
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