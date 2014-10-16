<?php
/*******************************************************************************
* import.php
* 上传、导出数据界面
* upload / import management interface
* 功能描述
	 提供上传 导出数据的功能

* Function Desc
	csv,xls file upload and import management
* Page elements
* div:							
									mainform			-> uploade excel file
									divMessage			-> show upload message
									divGrid		-> show uploade excel file
									mainDiv
									divTables
									divMainRight
									divDiallistImport
* javascript function:		
									init
									selectTable
									chkAddOnClick
									chkAssignOnClick
									confirmMsg
									showDivMainRight


* Revision 0.0456  2007/11/6 14:17:00  modified by solo
* Desc: modified function uploadFile

* Revision 0.045  2007/10/22 13:02:00  modified by yunshida
* Desc: modified some element id
		upload -> btnUpload
		upload_excel -> formUpload

* Revision 0.045  2007/10/22 11:35:00  modified by yunshida
* Desc: create page
* 描述: 建立
********************************************************************************/
	require_once('import.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/'); ?>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<script language='javascript'>
			function init(){
				xajax_init(document.getElementById('hidFileName').value);
			}

			function selectTable(tablename){
				if(tablename != ''){
					xajax_selectTable(tablename);
				}else{
					init();
//					return;
				}
			}

			function selectTimecolumn(){
				if(document.getElementById('dialListTime').value != ""){
					document.getElementById('dialtime').value = '';
					document.getElementById('dialtime').disabled = true;
					document.getElementById('cal').disabled = true;
				}else{
					document.getElementById('dialtime').value = '';
					document.getElementById('dialtime').disabled = false;
					document.getElementById('cal').disabled = false;
				}
			}

			function chkAddOnClick(){
				if(document.getElementsByName('chkAdd')[0].checked == true) 
				{ 
					document.getElementById('dialListField').value = "";
					document.getElementById('dialListField').disabled = false;
					document.getElementById('dialListTime').value = "";
					document.getElementById('dialListTime').disabled = false;
					document.getElementById('dialtime').disabled = false;
					document.getElementById('cal').disabled = false;
					document.getElementById('dialListField').style.border = "1px double #000000";
					document.getElementById('dialListField').focus(); 
					document.getElementsByName('chkAssign')[0].disabled = false;
				} 
				else 
				{ 
					document.getElementById('dialListField').value = "";
					document.getElementById('dialListField').disabled = true;
					document.getElementById('dialListTime').value = "";
					document.getElementById('dialListTime').disabled = true;
					document.getElementById('dialtime').disabled = true;
					document.getElementById('cal').disabled = true;
					document.getElementById('dialListField').style.border = "1px double #cccccc";
					document.getElementsByName('chkAssign')[0].disabled = true;
					document.getElementsByName('chkAssign')[0].checked = false;
					document.getElementById('assign').value = "";
					document.getElementById('assign').disabled = true;
					document.getElementById('assign').style.border = "1px double #cccccc";
				}
			}
			function chkAssignOnClick(){
				if(document.getElementsByName('chkAssign')[0].checked == true) 
				{ 
					document.getElementById('assign').value = "";
					document.getElementById('assign').disabled = false;
					document.getElementById('assign').style.border = "1px double #000000";
					document.getElementById('assign').focus(); 
				} 
				else 
				{ 
					document.getElementById('assign').value = "";
					document.getElementById('assign').disabled = true;
					document.getElementById('assign').style.border = "1px double #cccccc";
				}
			}

			function submitFormOnSubmit(){
				if(document.getElementsByName('chkAssign')[0].checked == true){
					if(document.getElementById('assign').value == "")
					{
						alert('<?php echo $locate->Translate("assign_automaticly");?>');
					}
				}

				/*if(document.getElementById('dialListField').value == '') {
					alert('<?php echo $locate->Translate("Must select a column for diallist");?>');
					document.getElementById('dialListField').focus();return;
				}*/
				var a = document.getElementsByName('order[]');
				var b = '';
				for(i=0;i < a.length;i++){
					//alert(a[i].value);
					if(a[i].value != ''){
						b = a[i].value;
						break;
					}
				}
				
				if(b == ''){
					alert("<?php echo $locate->Translate("Must select one or more columns");?>");
					a[0].focus();
					return;
				}
				
				//alert (document.getElementsById('sltTable').);
				//return false;
				//alert("ok");
				xajax.$('btnImportData').disabled=true;
				xajax.$('btnImportData').value= "<?php echo $locate->Translate("data_importing");?>";
				xajax_submitForm(xajax.getFormValues('formImport'));
			}

			function showDivMainRight(filename){
				xajax_showDivMainRight(filename);
			}
			
			function uploadFile()
			{
				if (document.getElementById('excel').value == '' && xajax.$('filelist').value == 0 )
					return false;

				xajax.$('btnUpload').disabled = true;
				xajax.$('btnUpload').value= "<?php echo $locate->Translate("uploading");?>";
				xajax.$('formUpload').submit();
				return false;
			}

			function enableDelete(){
				document.getElementById('spnDel').style.display = '';
				document.getElementById('btnDelete').disabled = false;
				return false;
			}

			function deleteFile(){
				if(xajax.$('filelist').value == 0){
					return false;
				}
				xajax_deleteFile(xajax.$('filelist').value);
				return false;
			}
		
			function  addOption(objId,optionVal,optionText)  {
				objSelect = document.getElementById(objId);
				var _o = document.createElement("OPTION");
				_o.text = optionText;
				_o.value = optionVal;
				objSelect.options.add(_o);
			} 

			function setCampaign(){
				groupid = document.getElementById("groupid").value;
				if (groupid == '')
					return;
				//清空campaignid
				document.getElementById("campaignid").options.length=0
				xajax_setCampaign(groupid);
			}

			function setDiallistCase(){
				if(document.getElementById('sltTable').value == 'diallist'){
					document.getElementById('chkAdd').disabled = true;
					document.getElementById('chkAssign').disabled = false;
				} else {
					document.getElementById('chkAdd').disabled = false;
					document.getElementById('chkAssign').disabled = true;
				}
			}

		</script>
<style>
#divMessage{
	padding:5px 0;
	background-color:#F0F8FF;
	width:auto;
	font-weight:bold;
	width:100%;
	margin-left:auto;
	margin-right:auto;
	margin-top:5px;
	height:30px
}
#mainform{
	font-size:12px;
	font-weight:normal;
	text-align:left;
	padding:10px;
	margin-top:10px;
	margin-left:auto;
	margin-right:auto;
	border:1px double #cccccc;
	width:60%;
	text-align:center;
	margin-bottom:10px;
}
#maintable tr td{
	padding: 6px 6px 6px 12px;
}
</style>
		<script language="JavaScript" src="js/astercrm.js"></script>
		<script language="JavaScript" src="js/dhtmlgoodies_calendar.js"></script>
	<LINK href="js/dhtmlgoodies_calendar.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>

	</head>
	<body onload="init();">
		<div id="divNav"></div><br>
		<center>
			<div id="mainform">
				<form action="upload.php" method="post" enctype="multipart/form-data" id="formUpload" name="formUpload" target="iframeShowExcel" onsubmit="uploadFile();return false;">
					<input type="hidden" name="CHECK" value="1" />
					<?php echo $locate->Translate("please_select_file");?>: <input type="file" name="excel" id="excel" onchange="if (this.value != 0){document.getElementById('filelist').value=0;}"/>&nbsp;<?php echo $locate->Translate("or");?>&nbsp;<select name="filelist" id="filelist" onchange="if (this.value != 0){document.getElementById('excel').value=''; enableDelete();}"></select>
					<br/><br/>
					<input type="submit" value="<?php echo $locate->Translate("upload");?>" id="btnUpload" name="btnUpload" style="width:150px;"/><span id="spnDel" style="display:none"><input type="button" value="<?php echo $locate->Translate("Delete File");?>" id="btnDelete" name="btnDelete" style="width:150px;" onclick="deleteFile();" disabled/></span>
				</form>
			</div>

			<div id="divMessage"></div>

			<table id="maintable">
				<tr>
					<td colspan="2" id="title" align='center'>
						<?php echo $locate->Translate("file_manager");?>
					</td>
				</tr>
			</table>

			<br>
			<table id="mainDiv" name="mainDiv">
				<tr>
					<td width="20%" valign="top">
							<ul style='list-style:none;'>
							<li>
						<div id="divTables" name="divTables" align="left">
						</div>
							</li>
							</ul>
							<div id='divTableFields' name='divTableFields'>
							</div>
					</td>
					<td width="80%" valign="top">
						<form method='post' name='formImport' id='formImport'>
							<input type='hidden' value='' name='hidFileName' id='hidFileName' />
							<input type='hidden' value='' name='hidTableName' id='hidTableName' />
							<input type='hidden' value='' name='hidMaxTableColumnNum' id='hidMaxTableColumnNum' />

							<div name="divGrid" id="divGrid"></div>
							<div name="divDiallistImport" id="divDiallistImport"></div>


				<table cellspacing="0" cellpadding="0" border="0" width="100%" style="text-align:center;">
					<tr>
						<td height="30px">
							<SELECT id="groupid" name="groupid" onchange="setCampaign();">
							</SELECT>
							<SELECT id="campaignid" name="campaignid">
							</SELECT>
						</td>
					</tr>
					<tr>
						<td height="30px">
							<input type="button" id="btnImportData"  name="btnImportData" value="<?php echo $locate->Translate("import");?>" style="border:1px double #cccccc;width:200px" disabled="true" onclick="submitFormOnSubmit();return false;"/>
						</td>
					</tr>
					<tr>
						<td height="30px">
							<div style="width:100%;height:auto;line-height:30px;text-align:left;" id="divResultMsg" name="divResultMsg"></div>
						</td>
					</tr>
				</table>
						</form>
					</td>
				</tr>
			</table>

			<!--
				use a hidden iframe to handle upload
			-->
			<iframe name="iframeShowExcel" id="iframeShowExcel" width="0" height="0" scrolling="no"></iframe>
			
		</center>
		<br />
		<br />
		<br />
		<p><div id="divCopyright"></div></p>
	</body>
</html>