<?php
/*******************************************************************************
********************************************************************************/

require_once('delete_rate.common.php');
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

			function restartAsterrc(){
				var msg = "<?echo $locate->Translate('Are you sure to')?> <? echo $locate->Translate('restart asterrc')?>?";
				if(confirm(msg)){
					xajax_restartAsterrc();
				}else{
					return false;
				}
			}
		//-->
		</SCRIPT>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>
		<script type="text/javascript" src="js/astercrm.js"></script>
		<script type="text/javascript" src="js/common.js"></script>
		<LINK href="skin/default/css/style.css" type="text/css" rel="stylesheet">
		<LINK href="skin/default/css/dragresize.css" type="text/css" rel="stylesheet">
	</head>
	<body onload="init();" id="delete_rate">
		<div id="divNav"></div><br>
		<center>
			<form id="deleteRateForm" name="deleteRateForm">
				<?php echo $locate->Translate("Select Rate Table");?>:
				<select id="delTable" name="delTable" onchange="xajax_changeTable(this.value)">
					<option value="">-<?php echo $locate->Translate("Select Table");?>-</option>
					<option value="myrate"><?php echo $locate->Translate("myrate");?></option>
					<option value="callshoprate"><?php echo $locate->Translate("callshoprate");?></option>
					<option value="resellerrate"><?php echo $locate->Translate("resellerrate");?></option>
				</select>
				&nbsp;
				<?php echo $locate->Translate("Delete Type");?>:
				<span id="delTypeBtn">
					<select id="delType" name="delType" onchange="xajax_getObjectData(this.value);">
						<option value="all"><?php echo $locate->Translate("all");?></option>
						<option value="system"><?php echo $locate->Translate("system");?></option>
						<option value="reseller"><?php echo $locate->Translate("reseller");?></option>
						<option value="group"><?php echo $locate->Translate("group");?></option>
					</select>
				</span>
				&nbsp;
				<?php echo $locate->Translate("Object");?>:
				<span id="delObjectBtn">
					<select id="delObject" name="delObject">
						<option value="all"><?php echo $locate->Translate("all")?></option>
					</select>
				</span>
				&nbsp;
				<input type="button" value="<?php echo $locate->Translate("search");?>" onclick="xajax_searchRate(xajax.getFormValues('deleteRateForm'));" />
				
				<span id="delRateBtn"></span>
			</form>
			<br />
			<br />
			<div id="searchRateMsg"></div>
			<div id="searchRateList"></div>
		</center>
		
		<div style="clear:both;"></div>
		<div id="divCopyright"></div>
	</body>
</html>
