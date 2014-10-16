<?
/*******************************************************************************
* system.php

* 配置文件管理文件
* system management interface

* Function Desc
	provide an system management interface

* 功能描述
	提供配置管理界面

* Page elements

* div:							
				divNav				show management function list
				divCopyright		show copyright

* javascript function:		
				init				page onload function			 

* Revision 0.0057  2009/03/28 15:47:00  last modified by donnie
* Desc: page created

********************************************************************************/

require_once('system.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/');?>		
		<meta http-equiv="Content-Language" content="utf-8" />
		<SCRIPT LANGUAGE="JavaScript">
		<!--

			function init(){
				xajax_init();
				dragresize.apply(document);
			}			

			function systemAction(type){
				if(type == 'reload'){
					var msg = "<?echo $locate->Translate('Are you sure to')?> <? echo $locate->Translate('reload')?>?";
				}else if(type == 'restart'){
					var msg = "<?echo $locate->Translate('Are you sure to')?> <? echo $locate->Translate('restart')?>?";
				}else if(type == 'restartasterrc'){
					var msg = "<?echo $locate->Translate('Are you sure to')?> <? echo $locate->Translate('restart asterrc')?>?";
				}else if(type == 'reboot'){
					var msg = "<?echo $locate->Translate('Are you sure to')?> <? echo $locate->Translate('reboot')?>?";
				}else if(type == 'shutdown'){
					var msg = "<?echo $locate->Translate('Are you sure to')?> <? echo $locate->Translate('shutdown')?>?";
				}
				if(confirm(msg)){
					xajax_systemAction(type);
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
		<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>

	</head>
	<body onload="init();" id="system">
		<div id="divNav"></div><br>
<center>
	<div id="info"></div>
	<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="650">
	  <tr>
		<td width="25%" height="39" class="td font" align="left">
			<? echo $locate->Translate('System');?>
		</td>
		<td width="75%" class="td font" align="center"><div id="divmsg"></div></td>
	  </tr>
	</table>
	<table border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="650"> 

				<tr bgcolor="#F7F7F7">
					<td width="25%" align="center" valign="center" height="30"><input type="button" onclick="systemAction('restartasterrc');return false;"  value="<?echo $locate->Translate('Restart asterrc');?>" style="width:110px;"/></td>
					<td  align="left" valign="center" height="30">&nbsp;&nbsp;- <?echo $locate->Translate('Restart asterrc daemon');?></td>					
				  </tr>
				  <tr bgcolor="#F7F7F7">
					<td width="25%" align="center" valign="center" height="30"><input type="button" onclick="systemAction('reload');return false;"  value="<?echo $locate->Translate('Reload asterisk');?>" style="width:110px;"/></td>
					<td  align="left" valign="center" height="30">&nbsp;&nbsp;<?echo $locate->Translate('reload info');?></td>					
				  </tr>
				  <tr bgcolor="#F7F7F7">
					<td  align="center" valign="center" height="30"><input type="button" onclick="systemAction('restart');return false;"  value="<?echo $locate->Translate('Restart asterisk');?>" style="width:110px;"/></td>
					<td align="left" valign="center" height="30" >&nbsp;&nbsp;<?echo $locate->Translate('restart info');?></td>					
				  </tr>
				  <!--<tr bgcolor="#F7F7F7">
					<td  align="center" valign="center" height="30"><input type="button" onclick="systemAction('reboot');return false;"  value=" <?echo $locate->Translate('Reboot asterccBox');?>" style="width:130px;"/></td>
					<td  align="left" valign="center" height="30">&nbsp;&nbsp;<?echo $locate->Translate('reboot info');?></td>					
				  </tr>
				  <tr bgcolor="#F7F7F7">
					<td  align="center" valign="center" height="30"><input type="button" onclick="systemAction('shutdown');return false;"  value="<?echo $locate->Translate('Shutdown asterccBox');?>" style="width:130px;"/></td>
					<td  align="left" valign="center" height="30" >&nbsp;&nbsp;<?echo $locate->Translate('shutdown info');?></td>					
				  </tr>-->
	</table>
	
	<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="650">
	  <tr>
		<td width="40%" height="39" class="td font" align="left">
			<? echo $locate->Translate('Current Channels');?> (<a href="javascript:void(null)" onclick="xajax_hangupchnnel('')"><?echo $locate->Translate('refresh');?></a>)
		</td>
		<td width="60%" class="td font" align="center">&nbsp;</td>
	  </tr>
	</table>
	<div id='curchanels'>
	<table border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="650"> 
		<tr bgcolor="#F7F7F7">
			<td  align="center" valign="center" height="30"></td>
			<td  align="center" valign="center" height="30"></td>				
		 </tr>
	</table>
	</div>
	<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="650">
	  <tr>
		<td width="25%" height="39" class="td font" align="left">			
		</td>
		<td width="75%" class="td font" align="center">&nbsp;</td>
	  </tr>
	</table>
</center>
		<div id="divCopyright"></div>
</body>
</html>