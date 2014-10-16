<?php
/*******************************************************************************
* cdr.php

* cdr查询界面文件
* cdr interface

* 功能描述
	提供cdr查询界面

* Page elements

* div:							
				divNav				show management function list
				grid				show CDR grid
				msgZone				show action result
				divCopyright		show copyright

* javascript function:		

				init				page onload function			 

********************************************************************************/

require_once('cdr.common.php');
$customerid = $_REQUEST['customerid'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/'); ?>
		<meta http-equiv="Content-Language" content="utf-8" />
		<SCRIPT LANGUAGE="JavaScript">
		<!--

			function init(){
				customerid = "<? echo $customerid ?>";
				xajax_init(customerid);
				dragresize.apply(document);
			}

		function searchFormSubmit(numRows,limit,id,type){
			//alert(xajax.getFormValues("searchForm"));			
			xajax_searchFormSubmit(xajax.getFormValues("searchForm"),numRows,limit,id,type);
			return false;
		}

		function archiveCDR(){			
			archiveDate=document.getElementById('archiveDate').value;
			if( confirm("<? echo $locate->Translate('are you sure to archive CDR early than');?> "+archiveDate+" <? echo $locate->Translate('months'); ?> ?") ){
				document.getElementById("msgZone").innerHTML = "<b> Processing, please wait ...</b>";
				document.getElementById("divMsg").style.visibility="visible";
				xajax_archiveCDR(archiveDate);
			}else{
				return false;
			}
		}

		//-->
		</SCRIPT>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>
		<script type="text/javascript" src="js/astercrm.js"></script>
		<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>

	</head>
	<body onload="init();" id="cdr">
		<div id="divNav"></div><br>
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
	<div id="divMsg" >
		Processing, please wait ...
	</div>	
		<form name="exportForm" id="exportForm" action="dataexport.php" >
			<input type="hidden" value="" id="hidSql" name="hidSql" />
		</form>
		<div id="divCopyright"></div>
	</body>
</html>
