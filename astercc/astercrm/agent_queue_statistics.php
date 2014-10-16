<?php
/*******************************************************************************
* report.php
* 座席通话统计

* Function Desc

* javascript function:		

* Revision asterCC 0.01  2007/11/21 17:55:00  last modified by solo
* Desc: page created
********************************************************************************/

require_once('agent_queue_statistics.common.php');
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
			listReport();
		}

		function listReport(){
			//判断groupid select框是不是有数据
			if(document.getElementById('groupid').options.length <= 1 && document.getElementById('groupid').value == 0)
				return;
			xajax_listReport(xajax.getFormValues("frmFilter"));
		}

		function getAgent(){
			var groupid = xajax.$('groupid').value;
			if (groupid == '')
				return;
			listReport();
			xajax_getAgent(document.getElementById('groupid').value);
		}
		function  addOption(objId,optionVal,optionText,optionSelected)  {
			objSelect = document.getElementById(objId);
			var _o = document.createElement("OPTION");
			_o.text = optionText;
			_o.value = optionVal;
			_o.selected = optionSelected;
			objSelect.options.add(_o);
		} 
		//-->
		</SCRIPT>

		<script language="JavaScript" src="js/astercrm.js"></script>
		<script language="JavaScript" src="js/dhtmlgoodies_calendar.js"></script>
        <script type="text/javascript">
</script>

       
		<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
		<LINK href="js/dhtmlgoodies_calendar.css" type=text/css rel=stylesheet>

	</head>
	<body onload="init();" id="report">
		<div id="divNav"></div>
		<form name="frmFilter" id="frmFilter" method="post">
		<br>
		<div style="margin-left: 30px; margin-right: auto;">
			<SELECT id="groupid" name="groupid" onchange="getAgent();"></SELECT>
			<SELECT id="agent_username" name="agent_username" onchange="listReport();"></SELECT>&nbsp;

			<a href="javascript:void(null);" onclick="xajax_speedDate('td')"><?php echo $locate->Translate("Today")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('tw')"><?php echo $locate->Translate("This week")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('tm')"><?php echo $locate->Translate("This month")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('l3m')"><?php echo $locate->Translate("Last 3 months")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('ty')"><?php echo $locate->Translate("This year")?></a>&nbsp;|
			<a href="javascript:void(null);" onclick="xajax_speedDate('ly')"><?php echo $locate->Translate("Last year")?></a>
			<br />
			<?php echo $locate->Translate("From")?>: <input type="text" name="sdate" id="sdate" size="20" value="<?php echo date("Y-m-d H:i",time()-86400);?>" readonly>
			<INPUT onclick="displayCalendar(document.forms[0].sdate,'yyyy-mm-dd hh:ii',this,true)" type="button" value="<?php echo $locate->Translate("Cal")?>">
			<?php echo $locate->Translate("To")?>:<input type="text" name="edate" id="edate" size="20" value="<?php echo date("Y-m-d H:i",time());?>" readonly>
			<INPUT onclick="displayCalendar(document.forms[0].edate,'yyyy-mm-dd hh:ii',this,true)" type="button" value="<?php echo $locate->Translate("Cal")?>">
			
			<input type="radio" value="text" name="reporttype" checked><?php echo $locate->Translate("Text")?>&nbsp;
			<!--<input type="radio" value="flash" name="reporttype"><?php echo $locate->Translate("Flash")?>-->
			<input type="button" onclick="listReport();return false;" value="<?php echo $locate->Translate("List")?>">&nbsp;
			<span id="exportlist"></span><input type="hidden" value="report" id="maintable" name="maintable" />			
		</div>
		</form>
		<br>
		<div id="divMsg">Processing, please wait ...</div>
		<div id="divGeneralList" style="margin-left: 30px; margin-right: auto;"></div>
	<div id="divCopyright"></div>
	</body>
</html>