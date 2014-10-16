<?php
/*******************************************************************************
* customer.php
* customer information management interface

* Function Desc
	customer management

* div:
				divNav				show management function list
				formDiv				show add contact form
				grid				show contact grid
				msgZone				show action result
				divCopyright		show copyright
				formCustomerInfo	show customer detail
				formContactInfo		show contact detail
				formNoteInfo		show note detail
				divActive			show import and export button
				exportForm          记录导出数据的sql语句

* button
				btnImport
				btnExport

* javascript function:

				init				page onload function

* Revision 0.045  2007/10/18 14:07:00  modified by solo
* Desc: comment added

* Revision 0.0443  2007/09/29 12:55:00  modified by solo
* Desc: create page
* 描述: 建立
********************************************************************************/

require_once('customer.server.php');
require_once('customer.common.php');
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

		function ckbAllOnClick(objCkb){
			var ockb = document.getElementsByName('ckb[]');
			for(i=0;i<ockb.length;i++) {
				if (ockb[i].checked != objCkb.checked){
					ockb[i].checked = objCkb.checked;
				}
			}
		}

		function setCampaign(){
			groupid = document.getElementById("groupid").value;
			//if (groupid == '')
			//	return;
			//清空campaignid
			document.getElementById("campaignid").options.length=0
			xajax_setCampaign(groupid);
		}

		function dial(phonenum,first,myvalue,dtmf){
			myFormValue = xajax.getFormValues("myForm");
			dialnum = phonenum;
			firststr = first;

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

			xajax_dial(dialnum,firststr,myFormValue,dtmfstr);
		}

		function addSchedulerDial(){
			xajax_addSchedulerDial(xajax.$("trAddSchedulerDial").style.display,'');
		}

		function saveSchedulerDial(){
			xajax_saveSchedulerDial(xajax.$("sDialNum").value,xajax.$("curCampaignid").value,xajax.$("sDialtime").value);
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

		function addTicket(customerid) {
			xajax_addTicket(customerid);
		}
		function relateByCategory() {
			xajax_relateByCategory(document.getElementById('ticketcategoryid').value);
		}

		function relateBycategoryID(Fid,state) {
			if(state == 'edit') {
				xajax_relateByCategoryId(Fid,document.getElementById('curTicketid').value);
			} else {
				xajax_relateByCategoryId(Fid);
			}
		}
		function AllTicketOfMyself(Cid) {
			xajax_AllTicketOfMy(Cid,'customer_ticket');
		}
		function showMyTicketsGrid(id,Ctype,start,limit,filter,content,order,divName,ordering,stype) {
			xajax_showMyTickets(id,Ctype,start,limit,filter,content,order,divName,ordering,stype);
		}
		function AllTicketOfMyGrid(cid,Ctype,start,limit,filter,content,order,divName,ordering,stype) {
			xajax_AllTicketOfMy(cid,Ctype,start,limit,filter,content,order,divName,ordering,stype);
		}
		//-->
		</SCRIPT>

		<script type="text/javascript" src="js/astercrm.js"></script>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>

		<script type="text/javascript" src="js/ajax.js"></script>
		<script type="text/javascript" src="js/ajax-dynamic-list.js"></script>
		<script language="JavaScript" src="js/dhtmlgoodies_calendar.js"></script>
		<LINK href="js/dhtmlgoodies_calendar.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
<?php if ($config['system']['enable_external_crm'] == false && $config['google-map']['key'] != ''){
	if($_SESSION['curuser']['country'] == 'cn')
		$map_locate = 'ditu';
	else
		$map_locate = 'maps';
?>
	<script src="http://<?php echo $map_locate;?>.google.com/maps?file=api&v=2&key=<?php echo $config['google-map']['key'];?>" type="text/javascript"></script>
<?php}
?>
	</head>
	<body onload="init();">
	<div id="divNav"></div>

	<div id="divActive" name="divActive">
		<input type="button" value="" id="btnContact" name="btnContact" onClick="window.location='contact.php';" />
		<input type="button" value="" id="btnNote" name="btnNote" onClick="window.location='note.php';" />
		<input type="button" value="" id="btnCustomerLead" name="btnCustomerLead" onClick="window.location='customer_leads.php';" />
	</div>
	<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
		<tr>
			<td style="padding: 0px;">
				<fieldset>
					<span id="customerLeadAction"></span>
					<div id="surveyDiv"  class="formDiv drsElement"
						style="left: 20px; top: 20px;width:700px;"></div>
					<div id="formDiv"  class="formDiv drsElement"
						style="left: 450px; top: 50px;width:500px;"></div>
					<div id="formCustomerInfo" class="formDiv drsElement"
						style="left: 20px; top: 50px; width: 650px"></div>
					<div id="formContactInfo" class="formDiv drsElement"
						style="left: 20px; top: 330px;width: 600px"></div>
					<div id="formCdr" class="formDiv drsElement"
						style="left: 20px; top: 330px; width: 850px"></div>
					<div id="formDiallist" class="formDiv drsElement"
						style="left: 20px; top: 330px; width: 800px"></div>
					<div id="formRecords" class="formDiv drsElement"
						style="left: 20px; top: 330px; width: 800px"></div>
					<div id="formNoteInfo" class="formDiv  drsElement"
						style="left: 450px; top: 330px;width: 500px;z-index:5;"></div>
					<div id="formEditInfo" class="formDiv drsElement"
						style="left: 450px; top: 50px;width: 500px"></div>
					<div id="grid" align="center"></div>
					<div id="msgZone" name="msgZone" align="left"> </div>
					<div id="formDiallist" class="formDiv drsElement"
						style="left: 20px; top: 330px; width: 800px"></div>
					<div id="formaddDiallistInfo"  class="formDiv drsElement"
						style="left: 450px; top: 50px;width: 500px"></div>
					<div id="formeditDiallistInfo"  class="formDiv drsElement"
						style="left: 450px; top: 50px;width: 500px"></div>
					<div id="formTicketDetailDiv"  class="formDiv drsElement"
						style="left: 600px; top: 300px;width: 490px"></div>
					<div id="formMyTickets"  class="formDiv drsElement"
						style="left: 500px; top: 150px;width: 800px"></div>
					<div id="formplaymonitor"  class="formDiv drsElement"
						style="left: 450px; top: 50px;width: 350px; z-index:999"></div>
				</fieldset>
			</td>
		</tr>
	</table>
	<form name="exportForm" id="exportForm" action="dataexport.php" >
		<input type="hidden" value="" id="hidSql" name="hidSql" />
		<input type="hidden" value="" id="maintable" name="maintable" />
		<input type="hidden" value="export" id="exporttype" name="exporttype" />
		<input type="hidden" name="dndlist_campaignid" id="dndlist_campaignid" value="0" />
	</form>
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
	<div id="divCopyright"></div>
	</body>
</html>