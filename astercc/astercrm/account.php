<?php
/*******************************************************************************
* account.php

* 账户管理界面文件
* account management interface

* Function Desc
	provide an account management interface

* 功能描述
	提供帐户管理界面

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

require_once('account.common.php');
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

			function usertypeChange(obj){
				if(obj.value == '' || obj.value == 'agent' || obj.value == 'groupadmin' || obj.value == 'admin') {
					document.getElementById('usertype_id').value = '0';
					document.getElementById('usertype').value = obj.options[obj.selectedIndex].text;
				} else {
					document.getElementById('usertype_id').value = obj.value;
					document.getElementById('usertype').value = obj.options[obj.selectedIndex].text;
				}
			}

			function chkExtenionClick(curVal,obj){
				if(curVal == "<?php echo $locate->translate('extensions_input_tip'); ?>") {
					obj.value = '';
					obj.style.color = '#000';
				} else {
					obj.style.color = '#000';
				}
			}

			function chkExtenionBlur(curVal,obj){
				if(curVal == "") {
					obj.value = "<?php echo $locate->translate('extensions_input_tip'); ?>";
					obj.style.color = '#BBB';
				} else {
					obj.style.color = '#000';
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
	<body onload="init();">
		<div id="divNav"></div>
	<div id="divActive" name="divActive">
		<input type="button" value="Group" id="btnGroup" name="btnGroup" onClick="window.location='group.php';" />
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
		<input type="hidden" value="" id="maintable" name="maintable" />
		<input type="hidden" value="export" id="exporttype" name="exporttype" />
	</form>

		<div id="divCopyright"></div>
	</body>
</html>
