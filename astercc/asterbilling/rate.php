<?php
/*******************************************************************************
********************************************************************************/

require_once('rate.common.php');
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

		function searchFormSubmit(numRows,limit,id,type){
			//alert(xajax.getFormValues("searchForm"));
			xajax_searchFormSubmit(xajax.getFormValues("searchForm"),numRows,limit,id,type);
			return false;
		}

		function  addOption(objId,optionVal,optionText)  {
			objSelect = document.getElementById(objId);
			var _o = document.createElement("OPTION");
			_o.text = optionText;
			_o.value = optionVal;
			objSelect.options.add(_o);
		} 

		function setGroup(){
			var resellerid = xajax.$('resellerid').value;
			if (resellerid == ''){
				document.getElementById("groupid").options.length = 1;
				return;
			}
			//清空campaignid
			document.getElementById("groupid").options.length = 1;
			if (resellerid != 0)
				xajax_setGroup(resellerid);
		}

		function updateCustomerMultiple(){
			if (confirm("<?echo $locate->Translate("are you sure to update this value");?>")){
				xajax_updateCustomerMultiple(document.getElementById("customer_multiple").value);
			}
		}

		function shortcutUpdateSave(mid){
			newRate = xajax.$(mid+'-mrateinitial').value;
			if(mid > 0) xajax_shortcutUpdateSave(mid,newRate);
			return false;
		}

		//-->
		</SCRIPT>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>
		<script type="text/javascript" src="js/astercrm.js"></script>
		<script type="text/javascript" src="js/common.js"></script>
		<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>

	</head>
	<body onload="init();" id="customerrate">
		<div id="divNav"></div><br>
		<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
			<tr>
				<td style="padding: 0px;">
					<fieldset>
		<?
			// when it's groupadmin we allow him set Customer Billsec Multiple 	
			if ($_SESSION['curuser']['usertype'] == "groupadmin") {
		?>
			<div class="centermain">
				<?echo $locate->Translate("Customer Billsec Multiple");?>
				<input type="text" id="customer_multiple" name="customer_multiple" value="" size="6" maxlength="6" >
				<input type="button" value="Update" onclick="updateCustomerMultiple();">&nbsp;&nbsp;&nbsp;&nbsp;
				<span id="spnShortcutUpdate"></span>&nbsp;&nbsp;&nbsp;&nbsp;
				<span id="spnShortcutMsg"></span>
			</div>
		<?
			}		
		?>
			<div id="formDiv"  class="formDiv drsElement" 
				style="left: 450px; top: 50px;width:500px;"></div>
			<div id="grid" name="grid" align="center"> </div>
			<div id="msgZone" name="msgZone" align="left"> </div>
					</fieldset>
				</td>
			</tr>
		</table>

		<form name="exportForm" id="exportForm" action="dataexport.php" >
			<input type="hidden" value="" id="hidSql" name="hidSql" />
		</form>
		<div id="divCopyright"></div>
	</body>
</html>
