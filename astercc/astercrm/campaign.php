<?php
/*******************************************************************************
* campaign.php

* 账户组管理界面文件
* account management interface

* Function Desc
	provide an account management interface

* 功能描述
	提供账户组管理界面

* Page elements

* div:							
				divNav				show management function list
				formDiv				show add/edit account form
				grid				show accout grid
				msgZone				show action result
				divCopyright		show copyright

* javascript function:		

				init				page onload function			 


* Revision 0.045  2007/10/18 11:44:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once('campaign.common.php');
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

		//-->
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
		<input type="button" value="" id="btnDial" name="btnDial" onClick="window.location='diallist.php';" />
		<input type="button" value="" id="btnDialed" name="btnDialed" onClick="window.location='dialedlist.php';" />
		<input type="button" value="<?php echo $locate->Translate("Campaign Result")?>" id="btnCampaignresult" name="btnCampaignresult" onClick="window.location='campaignresult.php';" />
		<input type="button" value="<?php echo $locate->Translate("Worktime packages")?>" id="btnWorktime" name="btnWorktime" onClick="window.location='worktimepackages.php';" />
	</div>
	<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
		<tr>
			<td style="padding: 0px;">
				<fieldset>
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
		<input type="hidden" value="campaign" id="maintable" name="maintable" />
		<input type="hidden" value="export" id="exporttype" name="exporttype" />
	</form>

		<div id="divCopyright"></div>
	</body>
</html>
