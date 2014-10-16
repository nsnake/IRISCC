<?
/*******************************************************************************
* profile.php

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

* Revision 0.0057  2009/03/28 15:47:00  last modified by donnie
* Desc: page created

********************************************************************************/

require_once('profile.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/');?>
		<?php	
				$get = '';
				//print_r($_GET);exit;
				if($_GET['action'] != '' ){
					foreach($_GET as $key => $value){
						$get .= $key.':'.$value.',';
					}
				}
		?>
		<meta http-equiv="Content-Language" content="utf-8" />
		<SCRIPT LANGUAGE="JavaScript">
		<!--

			function init(){
				post = "<?php echo $get; ?>"
				xajax_init(post);
				dragresize.apply(document);
			}

			function rechargeByPaypal(){
				if(confirm("Are sure to payment?")){

					if(typeof(xajax.$('amount')) == 'undefined'){
						alert("<?echo $locate->Translate('Please select amount');?>");
						return false;
					}else{
						if(xajax.$('amount').value == ''){
							alert("<?echo $locate->Translate('Please select amount');?>");
							return false;
						}
					}

					xajax_rechargeByPaypal(xajax.$('amount').value);
					return false;
				}else{
					return false;
				}
			}

			function refreshRechargeInfo(){
				xajax_refreshRechargeInfo();
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
	<body onload="init();" id="profile">
		<div id="divNav"></div><br>
<center>
	<div id="info"></div>

	<div id="paymentInfo"></div>
	<div id="formDiv"  class="formDiv drsElement" 
	style="left: 450px; top: 50px;width:500px;"></div>

	<div id="rechargeInfo"></div>
	<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="600">
	  <tr>
		<td width="25%" height="39" class="td font" align="center">
			
		</td>
		<td width="75%" class="td font" align="center">&nbsp;</td>
	  </tr>
	</table>
</center>
		<div id="divCopyright"></div>
</body>
</html>