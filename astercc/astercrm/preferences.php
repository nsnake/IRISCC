<?php /*******************************************************************************
* preferences.php

* 配置文件管理文件
* config management interface

* Function Desc
	provide an config management interface

* 功能描述
	提供配置管理界面

* Page elements

* div:
				divNav				show management function list
				divCopyright		show copyright

* javascript function:
				init				page onload function


* Revision 0.0456  2007/11/12 15:44:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once('preferences.common.php');
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

			function display(id){

				var traget=document.getElementById(id);
				 if(traget.style.display=="none"){
						 traget.style.display="";
				 }else{
						 traget.style.display="none";
			   }
			}

			function savePreferences(){
				xajax_savePreferences(xajax.getFormValues("formPreferences"));
			}

			function saveLicence(){
				if (document.getElementById('asterccKey').value.length >0 ){
					if (confirm("<?php echo $locate->Translate("click ok to update your astercc license")?>"))
						xajax_saveLicence(xajax.getFormValues("formLicence"));
				}
			}

			function checkDb(){
				xajax_checkDb(xajax.getFormValues("formPreferences"));
			}

			function checkAMI(){
				xajax_checkAMI(xajax.getFormValues("formPreferences"));
			}

			function checkSys(){
				xajax_checkSys(xajax.getFormValues("formPreferences"));
			}

			function systemAction(type){
				if(type == 'reload'){
					var msg = "<?php echo $locate->Translate('Are you sure to')?> <?php echo $locate->Translate('reload')?>?";
				}else if(type == 'restart'){
					var msg = "<?php echo $locate->Translate('Are you sure to')?> <?php echo $locate->Translate('restart')?>?";
				}else if(type == 'reboot'){
					var msg = "<?php echo $locate->Translate('Are you sure to')?> <?php echo $locate->Translate('reboot')?>?";
				}else if(type == 'shutdown'){
					var msg = "<?php echo $locate->Translate('Are you sure to')?> <?php echo $locate->Translate('shutdown')?>?";
				}
				if(confirm(msg)){
					xajax_systemAction(type);
				}else{
					return false;
				}
			}

			function AutoNotePopupOperate(curval){
				if(curval == 0) {
					document.getElementById('iptSysLastest_priority_note').checked = false;
					document.getElementById('iptSysHighest_priority_note').checked = false;
					document.getElementById('iptSysLastest_priority_note').disabled = true;
					document.getElementById('iptSysHighest_priority_note').disabled = true;
				} else {
					document.getElementById('iptSysLastest_priority_note').disabled = false;
					document.getElementById('iptSysHighest_priority_note').disabled = false;
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
<form name="formPreferences" id="formPreferences" method="post">
<center>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" width="780">
  <tr>
    <td height="39" class="td font" id="Database" name="Database" align="left">
		&nbsp;&nbsp;&nbsp;<?php echo $locate->Translate('Database')?>
        <input type="button" onclick="display('menu')"  value="+"/>
		<input type="button" onclick="checkDb();return false;"  value="<?php echo $locate->Translate('Check')?>"/>
		<div name="divDbMsg" id="divDbMsg"></div>
    </td>
  </tr>
    <tr><td height="10" class="td"></td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="780">
  <tr>
    <td width="230" align="left" valign="top"  id="DbDbtype" name="DbDbtype">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dbtype</td>
    <td width="200" align="left" valign="top" >
		<select id="iptDbDbtype" name="iptDbDbtype">
			<option value="mysql">mysql</option>
		</select>
    </td>
    <td align="left" valign="top" >
		<div id="divDbDbtype" name="divDbDbtype">
		</div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" bgcolor="#F7F7F7" id="DbDbhost" name="DbDbhost">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dbhost</td>
    <td width="200" align="left" valign="top" bgcolor="#F7F7F7">
		<input type="text" size="30" size="30" id="iptDbDbhost" name="iptDbDbhost" />
	</td>
    <td align="left" valign="top" bgcolor="#F7F7F7">
		<div id="divDbDbhost" name="divDbDbhost"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top"  id="DbDbname" name="DbDbname">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dbname</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" size="30" id="iptDbDbname" name="iptDbDbname" />
	</td>
    <td align="left" valign="top" >
		<div id="divDbDbname" name="divDbDbname"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" bgcolor="#F7F7F7" id="DbUsername" name="DbUsername">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;username</td>
    <td width="200" align="left" valign="top" bgcolor="#F7F7F7"><input type="text" size="30" id="iptDbUsername" name="iptDbUsername" /></td>
    <td align="left" valign="top" bgcolor="#F7F7F7"><div id="divDbUsername" name="divDbUsername"></div></td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top"  id="DBPassWord" name="DbPassword">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;password</td>
    <td width="200" align="left" valign="top" ><input type="text" size="30" id="iptDbPassword" name="iptDbPassword" /></td>
    <td align="left" valign="top" ><div id="divDbPassword" name="divDbPassword"></div></td>
  </tr>
</table>


<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" width="780">
  <tr>
    <td height="39" class="td font" id="Asterisk" name="Asterisk"  align="left">
		&nbsp;&nbsp;&nbsp;Asterisk
		<input type="button" onclick="display('menu1')"  value="+"/>
		<input type="button" onclick="checkAMI();return false;"  value="<?php echo $locate->Translate('check');?>"/>
		<input type="button" value="<?php echo $locate->Translate('Set multi servers');?>" onclick="window.location='servers.php'">
		<input type="button" onclick="systemAction('reload');return false;"  value="<?php echo $locate->Translate('reload');?>"/>
		<input type="button" onclick="systemAction('restart');return false;"  value="<?php echo $locate->Translate('restart');?>"/>
		<div name="divAsMsg" id="divAsMsg"></div>
    </td>
  </tr>
  <tr>
    <td height="10" class="td"></td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" id="menu1" width="780">
  <tr>
    <td width="230" align="left" valign="top" id="AsServer" name="AsServer">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;server
	</td>
    <td width="200" align="left" valign="top" >
      <input type="text" size="30" id="iptAsServer" name="iptAsServer" />
	</td>
    <td align="left" valign="top" >
		<div id="divAsServer" name="divAsServer">
		</div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" bgcolor="#F7F7F7" id="AsPort" name="AsPort">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;port</td>
    <td width="200" align="left" valign="top" bgcolor="#F7F7F7">
		<input type="text" size="30" id="iptAsPort" name="iptAsPort" />
	</td>
    <td align="left" valign="top" bgcolor="#F7F7F7">
		<div id="divAsPort" name="divAsPort"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top"  id="AsUsername" name="AsUsername">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;username</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptAsUsername" name="iptAsUsername" />
	</td>
    <td align="left" valign="top" >
		<div id="divAsUsername" name="divAsUsername"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" bgcolor="#F7F7F7" id="AsSecret" name="AsSecret">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;secret</td>
    <td width="200" align="left" valign="top" bgcolor="#F7F7F7">
		<input type="text" size="30" id="iptAsSecret" name="iptAsSecret" />
	</td>
    <td align="left" valign="top" bgcolor="#F7F7F7">
		<div id="divAsSecret" name="divAsSecret"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top"  id="AsMonitorpath" name="AsMonitorpath">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;monitorpath</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptAsMonitorpath" name="iptAsMonitorpath" />
	</td>
    <td align="left" valign="top" >
		<div id="divAsMonitorpath" name="divAsMonitorpath"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" bgcolor="#F7F7F7" id="AsMonitorformat" name="AsMonitorformat">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;monitorformat</td>
    <td width="200" align="left" valign="top" bgcolor="#F7F7F7">
		<select id="iptAsMonitorformat" name="iptAsMonitorformat">
			<option value="gsm">gsm</option>
			<option value="wav">wav</option>
			<option value="wav49">wav49</option>
		</select>
 	</td>
    <td align="left" valign="top" bgcolor="#F7F7F7">
		<div id="divAsMonitorformat" name="divAsMonitorformat"></div>
	</td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" width="780">
  <tr>
    <td height="39" class="td font" id="System" name="System"  align="left">
		&nbsp;&nbsp;&nbsp;<?php echo $locate->Translate('System');?>
      <input type="button" onclick="display('menu2')"  value="+"/>
		<input type="button" onclick="checkSys();return false;"  value="<?php echo $locate->Translate('check');?>"/>
		<!--<input type="button" onclick="systemAction('reboot');return false;"  value="<?php echo $locate->Translate('reboot');?>"/>
		<input type="button" onclick="systemAction('shutdown');return false;"  value="<?php echo $locate->Translate('shutdown');?>"/>-->
		<div name="divSysMsg" id="divSysMsg"></div>

	</td>
  </tr>
  <tr>
    <td height="10" class="td"></td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" id="menu2" width="780">
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top"  id="SysEventtype" name="SysEventtype">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;eventtype
	</td>
    <td width="200" align="left" valign="top" >
        <select name="iptSyseventtype" id="iptSyseventtype">
          <option value="curcdr">curcdr</option>
          <option value="event">event</option>
        </select>
	</td>
    <td align="left" valign="top" >
		<div id="divSyseventtype" name="divSyseventtype"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top"  id="SysLogEnabled" name="SysLogEnabled">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;log_enabled
	</td>
    <td width="200" align="left" valign="top" >
        <select name="iptSysLogEnabled" id="iptSysLogEnabled">
          <option value="0">0</option>
          <option value="1">1</option>
        </select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysLogEnabled" name="divSysLogEnabled"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" bgcolor="#F7F7F7" id="SysLogFilePath" name="SysLogFilePath">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;log_file_path
	</td>
    <td width="200" align="left" valign="top" bgcolor="#F7F7F7">
		<input type="text" size="30" id="iptSysLogFilePath" name="iptSysLogFilePath" />
	</td>
    <td align="left" valign="top" bgcolor="#F7F7F7">
		<div id="divSysLogFilePath" name="divSysLogFilePath"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" id="SysAsterccConfPath" name="SysAsterccConfPath">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;astercc_path
	</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysAsterccPath" name="iptSysAsterccPath" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysAsterccPath" name="divSysAsterccPath"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top"  id="SysOutcontext" name="SysOutcontext">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;outcontext
	</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysOutcontext" name="iptSysOutcontext" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysOutcontext" name="divSysOutcontext"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top"  id="SysIncontext" name="SysIncontext">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;incontext
	</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysIncontext" name="iptSysIncontext" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysIncontext" name="divSysIncontext"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top"  id="SysStop_work_verify" name="SysStop_work_verify">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;stop_work_verify
	</td>
    <td width="200" align="left" valign="top" >
		<select id="iptSysStop_work_verify" name="iptSysStop_work_verify">
			<option value="0">0</option>
            <option value="1">1</option>
        </select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysStop_work_verify" name="divSysStop_work_verify"></div>
	</td>
  </tr>
  <!--<tr>
    <td width="230" align="left" valign="top" bgcolor="#F7F7F7" id="SysPredialerExtension" name="SysPredialerExtension">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;preDialer_extension
	</td>
    <td width="200" align="left" valign="top" bgcolor="#F7F7F7"><input type="text" size="30" id="iptSysPredialerExtension" name="iptSysPredialerExtension" /></td>
    <td align="left" valign="top" bgcolor="#F7F7F7">
		<div id="divSysPredialerExtension" name="divSysPredialerExtension"></div>
	</td>
  </tr>-->
  <tr>
    <td width="230" align="left" valign="top" id="SysPhoneNumberLength" name="SysPhoneNumberLength">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;phone_number_length
	</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysPhoneNumberLength" name="iptSysPhoneNumberLength" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysPhoneNumberLength" name="divSysPhoneNumberLength"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top" id="SysSmartMatchRemove" name="SysSmartMatchRemove">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;smart_match_remove
	</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysSmartMatchRemove" name="iptSysSmartMatchRemove" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysSmartMatchRemove" name="divSysSmartMatchRemove"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" id="SysStatusCheckInterval" name="SysStatusCheckInterval">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;status_check_interval
	</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysStatusCheckInterval" name="iptSysStatusCheckInterval" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysStatusCheckInterval" name="divSysStatusCheckInterval"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top" id="SysTrimPrefix" name="SysTrimPrefix">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;trim_prefix
	</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysTrimPrefix" name="iptSysTrimPrefix" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysTrimPrefix" name="divSysTrimPrefix"></div>
	</td>
  </tr>
  <tr >
    <td width="230" align="left" valign="top"  id="SysAllowDropcall" name="SysAllowDropcall" >
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;allow_dropcall</td>
    <td width="200" align="left" valign="top" >
		<select name="iptSysAllowDropcall" id="iptSysAllowDropcall" >
			<option value="0">0</option>
			<option value="1">1</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysAllowDropcall" name="divSysAllowDropcall"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top" id="SysAllowSameDate" name="SysAllowSameDate">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;allow_same_data</td>
    <td width="200" align="left" valign="top" >
		<select name="iptSysAllowSameData" id="iptSysAllowSameData">
			<option value="0">0</option>
			<option value="1">1</option>
        </select>
	</td>
    <td align="left" valign="top">
		<div id="divSysAllowSameData" name="divSysAllowSameData"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top"  id="SysPortalDisplayType" name="SysPortalDisplayType" >
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;portal_display_type</td>
    <td width="200" align="left" valign="top" >
		<select id="iptSysPortalDisplayType" name="iptSysPortalDisplayType">
			<option value="customer">customer</option>
			<option value="note">note</option>
		</select>
	</td>
    <td align="left" valign="top" ><div id="divSysPortalDisplayType" name="divSysPortalDisplayType"></div></td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top"  id="SysPortalDisplayType" name="SysPortalDisplayType" >
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;agent_pannel_setting</td>
    <td width="200" align="left" valign="top" >
		<table>
			<tr><td>extension_pannel:&nbsp;&nbsp;</td><td><select id="iptSysExtensionPannel" name="iptSysExtensionPannel"><option value="On">On</option><option value="Off">Off</option></select></td></tr>
			<tr><td>transfer_pannel:&nbsp;&nbsp;</td><td><select id="iptSysTransferPannel" name="iptSysTransferPannel"><option value="On">On</option><option value="Off">Off</option></select></td></tr>
			<tr><td>monitor_pannel:&nbsp;&nbsp;</td><td><select id="iptSysMonitorPannel" name="iptSysMonitorPannel"><option value="On">On</option><option value="Off">Off</option></select></td></tr>
			<tr><td>dial_pannel:&nbsp;&nbsp;</td><td><select id="iptSysDialPannel" name="iptSysDialPannel"><option value="On">On</option><option value="Off">Off</option></select></td></tr>
			<tr><td>mission_pannel:&nbsp;&nbsp;</td><td><select id="iptSysMissionPannel" name="iptSysMissionPannel"><option value="On">On</option><option value="Off">Off</option></select></td></tr>
			<tr><td>diallist_pannel:&nbsp;&nbsp;</td><td><select id="iptSysDiallistPannel" name="iptSysDiallistPannel"><option value="On">On</option><option value="Off">Off</option></select></td></tr>
		</table>
	</td>
    <td align="left" valign="top" ><div id="divSysAgentPannelSetting" name="divSysAgentPannelSetting"></div></td>
  </tr>
  <tr >
    <td width="230" align="left" valign="top"  id="SysEnableContact" name="SysEnableContact">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;detail_level
	</td>
    <td width="200" align="left" valign="top" >
			<select name="iptSysDetailLevel" id="iptSysDetailLevel">
				<option value="all">all</option>
				<option value="group">group</option>
			</select>
		</td>
    <td align="left" valign="top" >
			<div id="divSysDetailLevel" name="divSysDetailLevel"></div>
		</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top"  id="SysPopUpWhenDialIn" name="SysPopUpWhenDialIn">
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pop_up_when_dial_in
	</td>
    <td width="200" align="left" valign="top" ><select name="iptSysPopUpWhenDialIn" id="iptSysPopUpWhenDialIn">
      <option value="0">0</option>
      <option value="1">1</option>
    </select></td>
    <td align="left" valign="top" >
		<div id="divSysPopUpWhenDialIn" name="divSysPopUpWhenDialIn"></div>
	</td>
  </tr>
  <tr >
    <td width="230" align="left" valign="top" id="SysPopUpWhenDialOut" name="SysPopUpWhenDialOut">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;pop_up_when_dial_out</td>
    <td width="200" align="left" valign="top">
		<select name="iptSysPopUpWhenDialOut" id="iptSysPopUpWhenDialOut">
		  <option value="0">0</option>
		  <option value="1">1</option>
		</select>
	</td>
    <td align="left" valign="top">
		<div id="divSysPopUpWhenDialOut" name="divSysPopUpWhenDialOut"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top"  id="SysBrowserMaximizeWhenPopUp" name="SysBrowserMaximizeWhenPopUp">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;browser_maximize_when_pop_up
	</td>
    <td width="200" align="left" valign="top">
		<select name="iptSysBrowserMaximizeWhenPopUp" id="iptSysBrowserMaximizeWhenPopUp">
		  <option value="0">0</option>
		  <option value="1">1</option>
		</select>
	</td>
    <td align="left" valign="top">
		<div id="divSysBrowserMaximizeWhenPopUp" name="divSysBrowserMaximizeWhenPopUp"></div>
	</td>
  </tr>

  <tr >
    <td width="230" align="left" valign="top"  id="SysFirstring" name="SysFirstring"  >
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;firstring
	</td>
    <td width="200" align="left" valign="top" >
		<select id="iptSysFirstring" name="iptSysFirstring">
			<option value="caller">caller</option>
			<option value="callee">callee</option>
		</select>
    </td>
    <td align="left" valign="top" >
		<div id="divSysFirstring" name="divSysFirstring"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top"  id="SysUploadExcelPath" name="SysUploadExcelPath">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;upload_file_path</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysUploadFilePath" name="iptSysUploadFilePath" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysUploadFilePath" name="divSysUploadFilePath"></div>
	</td>
  </tr>

  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top"  id="SysAuto_note_popup" name="SysAuto_note_popup">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;auto_note_popup</td>
    <td width="200" align="left" valign="top" >
		<select name="iptSysAuto_note_popup" id="iptSysAuto_note_popup" onchange="AutoNotePopupOperate(this.value)">
			<option value="0">0</option>
			<option value="1">1</option>
		</select>
		<br />
		<input type="checkbox" name="iptSysHighest_priority_note" id="iptSysHighest_priority_note" /><span id="divSysHighest_priority_note">highest_priority_note</span>
		<br/>
		<input type="checkbox" name="iptSysLastest_priority_note" id="iptSysLastest_priority_note" /><span id="divSysLastest_priority_note">lastest_priority_note</span>
	</td>
    <td align="left" valign="top" >
		<div id="divSysAuto_note_popup" name="divSysAuto_note_popup"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top"  id="SysDefault_share_note" name="SysDefault_share_note">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;default_share_note</td>
    <td width="200" align="left" valign="top" >
		<select name="iptSysDefault_share_note" id="iptSysDefault_share_note">
			<option value="0">0</option>
			<option value="1">1</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysDefault_share_note" name="divSysDefault_share_note"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysCustomer_leads"
	name="SysCustomer_leads">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;customer_leads</td> 	<td width="200" align="left" valign="top" >
		<select name="iptSysCustomer_leads" id="iptSysCustomer_leads">
			<option value="move">move</option>
			<option value="copy">copy</option>
			<option value="default_move">default_move</option>
			<option value="default_copy">default_copy</option>
			<option value="disabled">disabled</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysCustomer_leads" name="divSysCustomer_leads"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysEnableCode"
	name="SysEnableCode">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;enable_code</td> 	<td width="200" align="left" valign="top" >
		<select name="iptSysEnableCode" id="iptSysEnableCode">
			<option value="0">0</option>
			<option value="1">1</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysEnableCode" name="divSysEnableCode"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysUpdateOnlineInterval"
	name="SysUpdateOnlineInterval">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;update_online_interval</td> 	<td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysUpdateOnlineInterval" name="iptSysUpdateOnlineInterval" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysUpdateOnlineInterval" name="divSysUpdateOnlineInterval"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysEnableSMS"
	name="SysEnableSMS">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;enable_sms</td> 	<td width="200" align="left" valign="top" >
		<select name="iptSysEnableSMS" id="iptSysEnableSMS">
			<option value="disabled">disabled</option>
			<option value="callerid">callerid</option>
			<option value="campaign_number">campaign number</option>
			<option value="trunk_number">trunk number</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysEnableCode" name="divSysEnableCode"></div>
	</td>
  </tr>
   <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysRequireReasonWhenPause"
	name="SysRequireReasonWhenPause">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;require_reason_when_pause</td> 	<td width="200" align="left" valign="top" >
		<select name="iptSysRequireReasonWhenPause" id="iptSysRequireReasonWhenPause">
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysRequireReasonWhenPause" name="divSysRequireReasonWhenPause"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysCreateTicket"
	name="SysCreateTicket">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;create_ticket</td> 	<td width="200" align="left" valign="top" >
		<select name="iptSysCreateTicket" id="iptSysCreateTicket">
			<option value="default">default</option>
			<option value="system">system</option>
			<option value="group">group</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysCreateTicket" name="divSysCreateTicket"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysEnableSocket"
	name="SysEnableSocket">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;enable_socket</td>
	<td width="200" align="left" valign="top" >
		<select name="iptSysEnableSocket" id="iptSysEnableSocket">
			<option value="no">no</option>
			<option value="yes">yes</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysEnableSocket" name="divSysEnableSocket"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysFixPort"
	name="SysFixPort">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;fix_port</td>
	<td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysFixPort" name="iptSysFixPort" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysFixPort" name="divSysFixPort"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysSocketUrl"
	name="SysSocketUrl">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;socket_url</td>
	<td width="200" align="left" valign="top" >
		<textarea cols="35" rows="5" id="iptSysSocketUrl" name="iptSysSocketUrl"></textarea>
		<!--<input type="text" size="30" id="iptSysSocketUrl" name="iptSysSocketUrl" />-->
	</td>
    <td align="left" valign="top" >
		<div id="divSysSocketUrl" name="divSysSocketUrl"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysExportCustomerFieldsInDialedlist"
	name="SysExportCustomerFieldsInDialedlist">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;export_customer_fields_in_dialedlist</td>
	<td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysExportCustomerFieldsInDialedlist" name="iptSysExportCustomerFieldsInDialedlist" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysExportCustomerFieldsInDialedlist" name="divSysExportCustomerFieldsInDialedlist"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysAllowPopupWhenAlreadyPopup"
	name="SysAllowPopupWhenAlreadyPopup">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;allow_popup_when_already_popup</td>
	<td width="200" align="left" valign="top" >
		<select name="iptSysAllowPopupWhenAlreadyPopup" id="iptSysAllowPopupWhenAlreadyPopup">
			<option value="0">0</option>
			<option value="1">1</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysAllowPopupWhenAlreadyPopup" name="divSysAllowPopupWhenAlreadyPopup"></div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
	<td width="230" align="left" valign="top"  id="SysEnableFormAddPopup"
	name="SysEnableFormAddPopup">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;enable_formadd_popup</td>
	<td width="200" align="left" valign="top" >
		<select name="iptSysEnableFormAddPopup" id="iptSysEnableFormAddPopup">
			<option value="0">0</option>
			<option value="1">1</option>
		</select>
	</td>
    <td align="left" valign="top" >
		<div id="divSysEnableFormAddPopup" name="divSysEnableFormAddPopup"></div>
	</td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" width="780">
  <tr>
    <td height="39" class="td font" id="ExternalCRM" name="ExternalCRM"  align="left">
		&nbsp;&nbsp;&nbsp;<?php echo $locate->Translate('External CRM');?>
      <input type="button" onclick="display('menu3')"  value="+"/>
    </td>
  </tr>
  <tr>
    <td height="10" class="td"></td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" id="menu3" width="780">
  <tr>
    <td width="230" align="left" valign="top" id="SysEnableExternalCrm" name="SysEnableExternalCrm">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;enable_external_crm</td>
    <td width="200" align="left" valign="top" >
		<select name="iptSysEnableExternalCrm" id="iptSysEnableExternalCrm">
		  <option value="0">0</option>
		  <option value="1">1</option>
		</select></td>
    <td align="left" valign="top" >
		<div id="divSysEnableExternalCrm" name="divSysEnableExternalCrm"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" bgcolor="#F7F7F7" id="SysOpenNewWindow" name="SysOpenNewWindow">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;open_new_window</td>
    <td width="200" align="left" valign="top" bgcolor="#F7F7F7">
		<select name="iptSysOpenNewWindow" id="iptSysOpenNewWindow">
		  <option value="internal">internal</option>
		  <option value="external">external</option>
		  <option value="both">both</option>
		</select>
	</td>
    <td align="left" valign="top" bgcolor="#F7F7F7">
		<div id="divSysOpenNewWindow" name="divSysOpenNewWindow"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top"  id="SysExternalCrmDefaultUrl" name="SysExternalCrmDefaultUrl">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;external_crm_default_url</td>
    <td width="200" align="left" valign="top" >
		<input type="text" size="30" id="iptSysExternalCrmDefaultUrl" name="iptSysExternalCrmDefaultUrl" />
	</td>
    <td align="left" valign="top" >
		<div id="divSysExternalCrmDefaultUrl" name="divSysExternalCrmDefaultUrl"></div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" bgcolor="#F7F7F7" id="SysExternalCrmUrl" name="SysExternalCrmUrl">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;external_crm_url</td>
    <td width="200" align="left" valign="top" bgcolor="#F7F7F7">
		<input type="text" size="30" id="iptSysExternalCrmUrl" name="iptSysExternalCrmUrl" />
	</td>
    <td align="left" valign="top" bgcolor="#F7F7F7">
		<div id="divSysExternalCrmUrl" name="divSysExternalCrmUrl"></div>
	</td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" width="780">
  <tr>
    <td height="39" class="td font" id="survey" name="survey"  align="left">
		&nbsp;&nbsp;&nbsp;<?php echo $locate->Translate('Survey');?>
      <input type="button" onclick="display('menu5')"  value="+"/>
    </td>
  </tr>
  <tr>
    <td height="10" class="td"></td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" id="menu5" width="780">
  <tr >
    <td width="230" align="left" valign="top" id="enable_surveynote" name="enable_surveynote">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;enable_surveynote</td>
    <td width="200" align="left" valign="top" >

		<select name="iptEnable_surveynote" id="iptEnable_surveynote">
          <option value="0">0</option>
          <option value="1">1</option>
        </select>
	</td>
    <td align="left" valign="top" >
		<div id="divEnable_surveynote" name="divEnable_surveynote">&nbsp;</div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top" id="close_popup_after_survey" name="close_popup_after_survey">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;close_popup_after_survey</td>
    <td width="200" align="left" valign="top" >
		<select name="iptClose_popup_after_survey" id="iptClose_popup_after_survey">
          <option value="0">0</option>
          <option value="1">1</option>
        </select>
		</td>
    <td align="left" valign="top" >
		<div id="divClose_popup_after_survey" name="divClose_popup_after_survey">&nbsp;</div>
	</td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" width="780">
  <tr>
    <td height="39" class="td font" id="diallist" name="diallist"  align="left">
		&nbsp;&nbsp;&nbsp;<?php echo $locate->Translate('Diallist');?>
      <input type="button" onclick="display('menu6')"  value="+"/>
    </td>
  </tr>
  <tr>
    <td height="10" class="td"></td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" id="menu6" width="780">
  <tr>
    <td width="230" align="left" valign="top" id="popup_diallist" name="popup_diallist">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;popup_diallist</td>
    <td width="200" align="left" valign="top" >
		<select name="iptPopup_diallist" id="iptPopup_diallist">
          <option value="0">0</option>
          <option value="1">1</option>
        </select>
		</td>
    <td align="left" valign="top" >
		<div id="divPopup_diallist" name="divPopup_diallist">&nbsp;</div>
	</td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" width="780">
  <tr>
    <td height="39" class="td font" id="others" name="others"  align="left">
		&nbsp;&nbsp;&nbsp;<?php echo $locate->Translate('Others');?>
      <input type="button" onclick="display('menu4')"  value="+"/>
    </td>
  </tr>
  <tr>
    <td height="10" class="td"></td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" id="menu4" width="780">
  <tr>
    <td width="230" align="left" valign="top" id="googlemapkey" name="googlemapkey">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;google map key</td>
    <td width="200" align="left" valign="top" >
			<input type="text" size="30" id="iptGooglemapkey" name="iptGooglemapkey" />
		</td>
    <td align="left" valign="top" >
		<div id="divGooglemapkey" name="divGooglemapkey">&nbsp;</div>
	</td>
  </tr>
  <tr>
	<td width="230" align="left" valign="top" id="popup_diallist" name="popup_diallist">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $locate->Translate('error_report_level')?></td>
	<td width="200" align="left" valign="top" >
		<input type="text" id="iptErrorReportLevel" name="iptErrorReportLevel" size="4"/>
	</td>
	<td align="left" valign="top" >
		<div id="divErrorReportLevel" name="divErrorReportLevel">&nbsp;</div>
	</td>
  </tr>
</table>
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" width="780">
  <tr>
    <td height="39" class="td font" align="left">
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="btnSave" id="btnSave"   value="<?php echo $locate->Translate("Save")?>" onclick="savePreferences();return false;"/>
    </td>
  </tr>
  <tr>
    <td height="10" class="td">&nbsp;</td>
  </tr>
</table>
</form>
<form name="formLicence" id="formLicence" method="post">
<table border="0" align="center" cellpadding="0" cellspacing="1" bgcolor="#F0F0F0" width="780">
  <tr>
    <td height="39" class="td font" align="left" colspan="3">
      &nbsp;&nbsp;&nbsp;<?php echo $locate->Translate("Licence")?>
    </td>
  </tr>
  <tr>
    <td height="10" class="td" colspan="3"></td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" id="licenceto" name="licenceto">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Licence to</td>
    <td width="200" align="left" valign="top" >
			<input type="text" size="30" id="asterccLicenceto" name="asterccLicenceto" />
	</td>
    <td align="left" valign="top" >
		<div id="divLicenceto" name="divLicenceto">&nbsp;</div>
	</td>
  </tr>
  <tr bgcolor="#F7F7F7">
    <td width="230" align="left" valign="top" id="channels" name="channels">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Channels</td>
    <td width="200" align="left" valign="top" >
			<input type="text" size="30" id="asterccChannels" name="asterccChannels" />
	</td>
    <td align="left" valign="top" >
		<div id="divChannels" name="divChannels">&nbsp;</div>
	</td>
  </tr>
  <tr>
    <td width="230" align="left" valign="top" id="key" name="key">
	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Key</td>
    <td width="200" align="left" valign="top" >
			<input type="text" size="50" id="asterccKey" name="asterccKey" />
	</td>
    <td align="left" valign="top" >
		<div id="divKey" name="divKey">&nbsp;</div>
	</td>
  </tr>
  <tr>
    <td height="39" class="td font" align="left" colspan="3">
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" name="btnSaveLicence" id="btnSaveLicence"  value="<?php echo $locate->Translate("Update Licence")?>" onclick="saveLicence();return false;"/>
    </td>
  </tr>
</table>
</form>
</center>
		<div id="divCopyright"></div>
</body>
</html>