<?php
/*******************************************************************************
* user_type.php

* 账户类型界面文件
* user_type management interface

* Function Desc
	provide an account usertype management interface

* 功能描述
	提供帐户类型管理界面

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

require_once('user_types.common.php');
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
			function ckviewAllOnClick(objCkb){
				var ockb = document.getElementsByName('ckview[]');
				for(i=0;i<ockb.length;i++) {				
					if (ockb[i].checked != objCkb.checked){
						ockb[i].checked = objCkb.checked;
					}
				}
			}

			function ckeditAllOnClick(objCkb) {
				var ockb = document.getElementsByName('ckedit[]');
				for(i=0;i<ockb.length;i++) {				
					if (ockb[i].checked != objCkb.checked){
						ockb[i].checked = objCkb.checked;
					}
					var ockbcurId = ockb[i].id;
					var curPage = ockbcurId.replace(/edit/g, "view");
					var delPage = ockbcurId.replace(/edit/g, "delete");
					
					if(ockb[i].checked) {
						document.getElementById(curPage).checked = true;
						document.getElementById(curPage).disabled = true;
					} else {
						if(document.getElementById(delPage).checked == false) {
							document.getElementById(curPage).disabled = false;
						}
					}
				}
				if(objCkb.checked) {
					document.getElementById('checkAll_view').checked = true;
					document.getElementById('checkAll_view').disabled = true;
				} else {
					if(document.getElementById('checkAll_delete').checked == false) {
						document.getElementById('checkAll_view').disabled = false;
					}
				}
			}

			function ckdeleteAllOnClick(objCkb) {
				var ockb = document.getElementsByName('ckdelete[]');
				for(i=0;i<ockb.length;i++) {				
					if (ockb[i].checked != objCkb.checked){
						ockb[i].checked = objCkb.checked;
					}
					var ockbcurId = ockb[i].id;
					var curPage = ockbcurId.replace(/delete/g, "view");
					var editPage = ockbcurId.replace(/delete/g, "edit");
					
					if(ockb[i].checked) {
						document.getElementById(curPage).checked = true;
						document.getElementById(curPage).disabled = true;
					} else {
						if(document.getElementById(editPage).checked == false) {
							document.getElementById(curPage).disabled = false;
						}
					}
				}
				if(objCkb.checked) {
					document.getElementById('checkAll_view').checked = true;
					document.getElementById('checkAll_view').disabled = true;
				} else {
					if(document.getElementById('checkAll_edit').checked == false) {
						document.getElementById('checkAll_view').disabled = false;
					}
				}
			}
			
			function singleViewChk(){
				var ockb = document.getElementsByName('ckview[]');
				var checkedNum=0;
				for(i=0;i<ockb.length;i++) {
					if (ockb[i].checked == false){
						document.getElementById('checkAll_view').checked = false;
					} else {
						checkedNum ++;
					}
				}
				if(checkedNum == ockb.length) {
					document.getElementById('checkAll_view').checked = true;
				}
			}

			function singleEditChk(curObj){
				var ockb = document.getElementsByName('ckedit[]');
				var checkedNum=0;
				for(i=0;i<ockb.length;i++) {
					if (ockb[i].checked == false){
						document.getElementById('checkAll_edit').checked = false;
					} else {
						checkedNum ++;
					}
				}
				var ockbcurId = curObj.id;
				var curPage = ockbcurId.replace(/edit/g, "view");
				var delPage = ockbcurId.replace(/edit/g, "delete");
				if(curObj.checked) {
					document.getElementById(curPage).checked = true;
					document.getElementById(curPage).disabled = true;
				} else {
					if(document.getElementById(delPage).checked == false) {
						document.getElementById(curPage).checked = true;
						document.getElementById(curPage).disabled = false;
					}
				}
				if(checkedNum == ockb.length) {
					document.getElementById('checkAll_edit').checked = true;
				}
			}

			function singleDelChk(curObj){
				var ockb = document.getElementsByName('ckdelete[]');
				var checkedNum=0;
				for(i=0;i<ockb.length;i++) {
					if (ockb[i].checked == false){
						document.getElementById('checkAll_delete').checked = false;
					} else {
						checkedNum ++;
					}
				}
				if(checkedNum == ockb.length) {
					document.getElementById('checkAll_delete').checked = true;
				}
				var ockbcurId = curObj.id;
				var viewPage = ockbcurId.replace(/delete/g, "view");
				var editPage = ockbcurId.replace(/delete/g, "edit");
				if(curObj.checked) {
					document.getElementById(viewPage).checked = true;
					document.getElementById(viewPage).disabled = true;
				} else {
					if(document.getElementById(editPage).checked == false) {
						document.getElementById(viewPage).checked = true;
						document.getElementById(viewPage).disabled = false;
					}
				}
			}
			

			function update(){
				var f= {};
				f['Id'] = document.getElementById('id').value;
				f['usertype_name'] = document.getElementById('usertype_name').value;
				f['memo'] = document.getElementById('memo').value;
				
				var ockView = document.getElementsByName('ckview[]');
				var ockViewStr = '';
				for(var i=0;i<ockView.length;i++) {
					if(ockView[i].checked) {
						ockViewStr += ockView[i].id+'='+ockView[i].checked+',';
					}
				}
				f['chkView'] = ockViewStr;

				var ockEdit = document.getElementsByName('ckedit[]');
				var ockEditStr= '';
				for(var j=0;j<ockEdit.length;j++) {
					if(ockEdit[j].checked) {
						ockEditStr += ockEdit[j].id+'='+ockEdit[j].checked+',';
					}
				}
				f['chkEdit'] = ockEditStr;

				
				var ockDel = document.getElementsByName('ckdelete[]');
				var ockDelStr = '';
				for(var k=0;k<ockDel.length;k++) {
					if(ockDel[k].checked) {
						ockDelStr += ockDel[k].id+'='+ockDel[k].checked+',';
					}
				}
				f['ckdelete'] = ockDelStr;
				xajax_updateUserTypeRecord(f);
			}
			
			function editPageCheckbox(){
				var ockView = document.getElementsByName('ckview[]');
				var ockedit = document.getElementsByName('ckedit[]');
				var ockdelete = document.getElementsByName('ckdelete[]');
				
				var viewTotal = 0;
				var editTotal = 0;
				var delTotal = 0;
				for(var i=0;i<ockView.length;i++) {
					var editId = ockView[i].id.replace(/view/g, "edit");
					var delId = ockView[i].id.replace(/view/g, "delete");
					var editChk = document.getElementById(editId).checked;
					var deleteChk = document.getElementById(delId).checked;
					if(editChk || deleteChk) {
						ockView[i].disabled = true;
					} else {
						ockView[i].disabled = false;
					}
					if(ockView[i].checked) viewTotal ++;
					if(editChk) editTotal ++;
					if(deleteChk) delTotal ++;
				}

				if(viewTotal == ockView.length) {
					document.getElementById('checkAll_view').checked = true;
				}
				if(editTotal == ockedit.length) {
					document.getElementById('checkAll_edit').checked = true;
					document.getElementById('checkAll_view').checked = true;
					document.getElementById('checkAll_view').disabled = true;
				}
				if(delTotal == ockdelete.length) {
					document.getElementById('checkAll_delete').checked = true;
					document.getElementById('checkAll_view').checked = true;
					document.getElementById('checkAll_view').disabled = true;
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
