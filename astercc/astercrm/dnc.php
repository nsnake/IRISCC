<?php
/*******************************************************************************
* diallist.php
* 拨号列表管理界面//
* dnc  management interface
* 功能描述
	 提供dnc信息管理的功能

* Function Desc
	dnc management

* Page elements
* div:							
									formDiv			-> add/edit form div in xgrid
									grid				-> main div
									msgZone		-> message from xgrid class
									exportForm   记录导出数据的sql语句
* javascript function:		
									init	


* Revision 0.0443  2007/09/29 12:55:00  modified by solo
* Desc: create page
* 描述: 建立
********************************************************************************/

require_once('dnc.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/'); ?>
		<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
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

		function  addOption(objId,optionVal,optionText)  {
			objSelect = document.getElementById(objId);
			var _o = document.createElement("OPTION");
			_o.text = optionText;
			_o.value = optionVal;
		//	alert(objSelect.length);
			objSelect.options.add(_o);
		} 

		function importCsv(){
			xajax_importCsv();
		}

		function setCampaign(){
			groupid = document.getElementById("groupid").value;
			if (groupid == '')
				return;
			//清空campaignid
			document.getElementById("campaignid").options.length=0
			xajax_setCampaign(groupid);
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
		<script language="JavaScript" src="js/dhtmlgoodies_calendar.js"></script>
		<LINK href="js/dhtmlgoodies_calendar.css" type=text/css rel=stylesheet>

	<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
	</head>
	<body onload="init();">
	<div id="divNav"></div>

	<div id="divActive" name="divActive">
		<input type="button" value="<?php echo $locate->Translate("DialList")?>" id="btnDiallist" name="btnDiallist" onClick="window.location='diallist.php';" />
		<input type="button" value="" id="btnDialed" name="btnDialed" onClick="window.location='dialedlist.php';" />

	</div>
	<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
		<tr>
			<td style="padding: 0px;">
				<fieldset>
		<div id="formDiv"  class="formDiv drsElement" 
			style="left: 450px; top: 50px;width: 500px;"></div>
					<div id="grid" align="center"> </div>
					<div id="msgZone" name="msgZone" align="left"> </div>
				</fieldset>
			</td>
		</tr>
	</table>
	<div id="divCopyright"></div>
	<form name="exportForm" id="exportForm" action="dataexport.php" >
		<input type="hidden" value="" id="hidSql" name="hidSql" />
		<input type="hidden" value="diallist" id="maintable" name="maintable" />
		<input type="hidden" value="export" id="exporttype" name="exporttype" />
	</form>
	</body>
</html>