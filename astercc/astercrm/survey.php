<?php
/*******************************************************************************
* survey.php
* 调查信息管理界面
* note information management interface
* 功能描述
	 提供对调查信息进行管理的功能

* Function Desc
	survey management

* Page elements
* div:							
									formDiv			-> add/edit form div in xgrid
									grid				-> main div
									msgZone		-> message from xgrid class
* javascript function:		
									init	


* Revision 0.045  2007/10/1 12:55:00  modified by solo
* Desc: create page
* 描述: 建立
********************************************************************************/

require_once('survey.common.php');
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
		
		function addOption(formName,optionid){
			if (optionid == 0){
				xajax_save(xajax.getFormValues(formName));
			}else{
				xajax_updateOption(xajax.getFormValues(formName),optionid);
			}
		}

		function  addSltOption(objId,optionVal,optionText)  {
			objSelect = document.getElementById(objId);
			var _o = document.createElement("OPTION");
			_o.text = optionText;
			_o.value = optionVal;
		//	alert(objSelect.length);
			objSelect.options.add(_o);
		} 

		function showItem(optionid){
			xajax_showItem(optionid);
		}

		function setCampaign(){
			groupid = document.getElementById("groupid").value;
			if (groupid == '')
				return;
			//清空campaignid
			document.getElementById("campaignid").options.length=0
			xajax_setCampaign(groupid);
		}

		function addItem(optionid){
			xajax_addItem(xajax.getFormValues('fItem'));
		}

		function deleteOption(optionid,nameRow){
			if (confirm("<?php echo $locate->Translate("are you sure to delete this option");?>"+"?")){
				xajax_delete(optionid,'surveyoptions');
				var myRowIndex = document.getElementById(nameRow).rowIndex;
				document.getElementById('tblSurvey').deleteRow(myRowIndex+1);
				document.getElementById('tblSurvey').deleteRow(myRowIndex);
			}
		}

		function deleteItem(itemid,optionid){
				xajax_delete(itemid,'surveyoptionitems');
				showItem(optionid);
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

	<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
		<tr>
			<td style="padding: 0px;">
				<fieldset>
		<div id="formDiv"  class="formDiv drsElement" 
			style="left: 450px; top: 50px;width: 500px"></div>
		<div id="itemDiv"  class="formDiv drsElement" 
			style="left: 350px; top: 80px;width: 500px"></div>
					<div id="grid" align="center"> </div>
					<div id="msgZone" name="msgZone" align="left"> </div>
					<div id="divSurveyStatistc" align="divSurveyStatistc"> </div>
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