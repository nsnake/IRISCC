<?php
/*******************************************************************************
* predictivedialer.php
* 预拨号器界面文件
* predictivedialer interface

* Function Desc
		拨号器控制: 开始/停止
		最大通道控制

* div
				divNav
				divAMIStatus					show error message if AMI is error
				divActiveCalls					show active calls number
				divPredictiveDialerMsg			
				divPredictiveDialer				show predictive dialer
				channels						show asterisk channels
				divCopyright
* span
				spanTotalRecords				records in diallist

* hidden
				predictiveDialerStatus			dialer status: idle | busy


* javascript functions

				init
				btnDialOnClick
				startDial
				stopDial
				trim
				isNumber

* Revision 0.045  2007/10/18 20:12:00  last modified by solo
* Desc: change div id from AMIStatusDiv to divAMIStatus 

* Revision 0.045  2007/10/18 17:55:00  last modified by solo
* Desc: page created
********************************************************************************/

require_once('predictivedialer.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/'); ?>
		<meta http-equiv="Content-Language" content="utf-8" />
		<SCRIPT LANGUAGE="JavaScript">
		<!--
			var timerPredictiveDialer;


			function init(){
				xajax_init();
				startDial();
			}

			function setStatus(obj){
				campaignid = obj.id.split("-");
				var status = '';
				if (obj.checked){
					status = 'busy';
				}else{
					status = 'idle';
				}

				xajax_setStatus(campaignid[0],"status",status);
			}

			function setMaxChannel(obj){
				campaignid = obj.id.split("-");
				xajax_setStatus(campaignid[0],"max_channel",obj.value);
			}

			function setQueueRate(obj){
				campaignid = obj.id.split("-");
				xajax_setStatus(campaignid[0],"queue_increasement",obj.value);
			}

			function setLimitType(obj){
				campaignid = obj.id.split("-");
				xajax_setStatus(campaignid[0],"limit_type",obj.value);
			}

			function get_radio_value(field){ 
				if (field && field.length){
						for (var i = 0; i < field.length; i++){ 
								if (field[i].checked){
										return field[i].value; 
								} 
						} 
				}else{ 
						return;     
				} 
			}

			function startDial(){
				xajax_predictiveDialer(xajax.getFormValues("f"));
			}
			
			function stopDial(){
				clearTimeout(timerPredictiveDialer);
				xajax.$('predictiveDialerStatus').value = "idle";
				xajax.$('divPredictiveDialerMsg').innerHTML = xajax.$('btnDialerStoppedMsg').value;
				xajax.$('btnDial').value = xajax.$('btnDialMsg').value;	
			}

			function trim(stringToTrim) {
				return stringToTrim.replace(/^\s+|\s+$/g,"");
			}
	
		   function isNumber(oNum){
				if(!oNum) return false;
				var strP=/^\d+(\.\d+)?$/;
				if(!strP.test(oNum)) return false;
				try{
					if(parseFloat(oNum)!=oNum) return false;
				}
				catch(ex)
				{
					return false;
				}
				return true;
			}

		//-->
		</SCRIPT>
		<script language="JavaScript" src="js/astercrm.js"></script>

	<LINK href="skin/default/css/dragresize.css" type="text/css" rel="stylesheet"/>
	<LINK href="skin/default/css/style.css" type="text/css" rel="stylesheet"/>
	<LINK href="skin/default/css/dialer.css" type="text/css" rel="stylesheet" />

	</head>
	<body onload="init();">
		<div id="divNav"></div>
		
		<div id="divAMIStatus" name="divAMIStatus"></div>

		<form action="" method="post" name="f" id="f">
		<div class="groups_tittle"><?php echo $locate->Translate("Group")?></div>
		<div id="divMain" name="divMain"></div>
		<?if($_SESSION['curuser']['usertype'] == 'admin')
		echo '<div class="groups_tittle2">'.$locate->Translate("System").'</div>
		<div class="groupsystem_channel" id="idvUnknowChannels" ></div>'?>
		</form>

		<div id="divCopyright"></div>

	</body>
</html>
