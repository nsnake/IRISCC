<?php
/*******************************************************************************
* dialedlist.php
* 拨号结果信息管理界面
* dialedlist information management interface
* 功能描述
	 提供对调查信息进行管理的功能

* Function Desc
	dialedlist management

* Page elements
* div:							
									formDiv			-> add/edit form div in xgrid
									grid				-> main div
									msgZone		-> message from xgrid class
* javascript function:		
									init	


* Revision 0.0461  2008/2/2 12:55:00  modified by solo
* Desc: create page
* 描述: 建立
********************************************************************************/

require_once('dialedlist.common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<?php $xajax->printJavascript('include/'); ?>
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
			//make div draggable
			CampaignDialedlist();
			dragresize.apply(document);
		}
		
		function recycle(){
			xajax_recycle(xajax.getFormValues('delGrid'));
		}
		
		function ckbAllOnClick(objCkb){
			var ockb = document.getElementsByName('ckb[]');
			for(i=0;i<ockb.length;i++) {				
				if (ockb[i].checked != objCkb.checked){
					ockb[i].checked = objCkb.checked;
				}
			}			
		}

		function CampaignDialedlist(){
			xajax_getReport(xajax.getFormValues("formCampaign"));
		}
		//-->
		</SCRIPT>
		<script language="JavaScript" src="js/astercrm.js"></script>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>
		<script language="JavaScript" src="js/dhtmlgoodies_calendar.js"></script>

	<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
	<LINK href="js/dhtmlgoodies_calendar.css" type=text/css rel=stylesheet>
	</head>
	<body onload="init();">
	<div id="divNav"></div>
	<div id="divActive" name="divActive">
		<input type="button" value="" id="btnDial" name="btnDial" onClick="window.location='diallist.php';" />
		<input type="button" value="" id="btnCampaign" name="btnCampaign" onClick="window.location='campaign.php';" />
		<input type="button" value="<?php echo $locate->Translate("Worktime packages")?>" id="btnWorktime" name="btnWorktime" onClick="window.location='worktimepackages.php';" />
	</div>
	<form id="formCampaign" name="formCampaign">
		<div style="margin:3px auto 5px 10px;">
			<a href="javascript:void(null);" onclick="xajax_speedDate('td')"><?php echo $locate->Translate("Today")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('tw')"><?php echo $locate->Translate("This week")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('tm')"><?php echo $locate->Translate("This month")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('l3m')"><?php echo $locate->Translate("Last 3 months")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('ty')"><?php echo $locate->Translate("This year")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('ly')"><?php echo $locate->Translate("Last year")?></a>
			
			<?php echo $locate->Translate("From")?>: <input type="text" name="sdate" id="sdate" size="20" value="<?php echo date("Y-m-d H:i",time()-86400);?>" readonly>
			<input onclick="displayCalendar(document.forms[0].sdate,'yyyy-mm-dd hh:ii',this,true)" type="button" value="<?php echo $locate->Translate("Cal")?>">
			<?php echo $locate->Translate("To")?>:<input type="text" name="edate" id="edate" size="20" value="<?php echo date("Y-m-d H:i",time());?>" readonly>
			<input onclick="displayCalendar(document.forms[0].edate,'yyyy-mm-dd hh:ii',this,true)" type="button" value="<?php echo $locate->Translate("Cal")?>">
			<input type="button" onclick="CampaignDialedlist();return false;" value="<?php echo $locate->Translate("Query")?>">&nbsp;
		</div>
		<div id="campaignReport"></div>
	</form>
	<table width="100%" border="0" style="background: #F9F9F9; padding: 0px;">
		<tr>
			<td style="padding: 0px;">
				<fieldset>
					<input type="button" value="<?php echo $locate->Translate("recycle")?>" name="btnRecycle" id="btnRecycle" onclick="recycle();">
					<span id="spanRecycleUp" name="spanRecycle"></span>
					<div id="formDiv"  class="formDiv drsElement" 
						style="left: 450px; top: 50px;width: 500px;"></div>
					<div id="optionDiv"  class="formDiv drsElement" 
						style="left: 450px; top: 80px;"></div>
					<div id="grid" align="center"> </div>
					<div id="msgZone" name="msgZone" align="left"> </div>
					<input type="button" value="<?php echo $locate->Translate("recycle")?>" name="btnRecycle" id="btnRecycle" onclick="recycle();">
					<span id="spanRecycleDown" name="spanRecycle"></span>
				</fieldset>
			</td>
		</tr>
	</table>
	<form name="exportForm" id="exportForm" action="dataexport.php" >
		<input type="hidden" value="" id="hidSql" name="hidSql" />
		<input type="hidden" value="dialedlist" id="maintable" name="maintable" />
		<input type="hidden" value="export" id="exporttype" name="exporttype" />
	</form>
	<div id="divCopyright"></div>
	</body>
</html>