<?php
/*******************************************************************************
* dialedlist.server.php

* Function Desc
	provide dialedlist management script

* 功能描述
	提供问卷管理脚本

* Function Desc

	showGrid
	export				提交表单, 导出contact数据
	init				初始化页面元素
	createGrid			生成grid的HTML代码
	delete
	edit
	editField
	updateField
	showDetail
	add
	save


* Revision 0.045  2007/10/18 15:38:00  last modified by solo
* Desc: comment added

********************************************************************************/
require_once ("db_connect.php");
require_once ("dialedlist.common.php");
require_once ('dialedlist.grid.inc.php');
require_once ('include/asterevent.class.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');


function showGrid($start = 0, $limit = 1,$filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$action='',$campaign_id=0){

	$html = createGrid($start, $limit,$filter, $content, $order, $divName, $ordering,$stype=array(),$action,$campaign_id);
	$objResponse = new xajaxResponse();
	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign($divName, "innerHTML", $html);
	
	return $objResponse->getXML();
}

function init($post=''){
	global $locate;//,$config,$db;
	
	$aciton = '';
	$campaign_id = 0;
	if($post != ''){
		$post = explode(',',$post);
		foreach($post as $key => $value){
			if($value != ''){
				$v = explode(':',$value);
				if($v['0'] == 'cid'){
					if(is_numeric($v['1'])){
						$campaign_id = $v['1'];
					}					
				}elseif($v['0'] == 'action'){
					$aciton = $v['1'];
				}
			}
		}
	}

	$objResponse = new xajaxResponse();

	
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("btnDial","value",$locate->Translate("Dial list"));
	$objResponse->addAssign("btnCampaign","value",$locate->Translate("Campaign"));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','','grid','','".$aciton."',".$campaign_id.")");

	$noanswer = Customer::getNoanswerCallsNumber();
	$objResponse->addAssign("spanRecycleUp","innerHTML",$locate->Translate("No answer calls and never recycle").": $noanswer");
	$objResponse->addAssign("spanRecycleDown","innerHTML",$locate->Translate("No answer calls and never recycle").": $noanswer");

	return $objResponse;
}

function recycle($f){
	global $locate;
	$objResponse = new xajaxResponse();
	$num = 0;
	if(is_array($f['ckb'])){
		foreach($f['ckb'] as $value){
			Customer::recycleDialedlistById($value);
			$num ++;
		}
	}else{
		$num = Customer::recycleDialedlist();
	}
	$objResponse->addALert($num." ".$locate->Translate("number have been recycled"));
	$objResponse->addScript("init()");
	return $objResponse;
}

//	create grid
function createGrid($start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$stype=array(),$action='',$campaign_id=0){
	
	if($action == 'abandoned' && $campaign_id > 0){
		$campaignrow = astercrm::getRecordById($campaign_id,'campaign');
		$filter = array('campaigndialedlist.billsec_leg_a','campaigndialedlist.billsec','campaignname');
		$content = array(0,0,$campaignrow['campaignname']);
		$stype = array('more','equal','equal');
	}
	global $locate;
	$_SESSION['ordering'] = $ordering;
	
	if($filter == null or $content == null or $content == 'Array' or $filter == 'Array'){
		$numRows =& Customer::getNumRows();
		$arreglo =& Customer::getAllRecords($start,$limit,$order);
		$content = null;
		$filter = null;
	}else{
		foreach($content as $value){
			if(trim($value) != ""){  //搜索内容有值
				$flag = "1";
				break;
			}
		}
		foreach($filter as $value){
			if(trim($value) != ""){  //搜索条件有值
				$flag2 = "1";
				break;
			}
		}
		foreach($stype as $value){
			if(trim($value) != ""){  //搜索方式有值
				$flag3 = "1";
				break;
			}
		}
		if($flag != "1" || $flag2 != "1"){  //无值
			if(is_array($order) || $order == '') $order = null;
			$numRows =& Customer::getNumRows($_SESSION['curuser']['groupid']);
			$arreglo =& Customer::getAllRecords($start,$limit,$order,$_SESSION['curuser']['groupid']);
		}elseif($flag3 != 1){
			$order = "id";
			$numRows =& Customer::getNumRowsMore($filter, $content,"campaigndialedlist");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"campaigndialedlist");
		}else{
			$order = "id";
			$numRows =& Customer::getNumRowsMorewithstype($filter, $content,$stype,$table);
			$arreglo =& Customer::getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table);
		}
	}

	// Select Box: type table.
	$typeFromSearch = array();
	$typeFromSearch[] = 'like';
	$typeFromSearch[] = 'equal';
	$typeFromSearch[] = 'more';
	$typeFromSearch[] = 'less';

	// Selecct Box: Labels showed on searchtype select box.
	$typeFromSearchShowAs = array();
	$typeFromSearchShowAs[] = $locate->Translate("like");
	$typeFromSearchShowAs[] = '=';
	$typeFromSearchShowAs[] = '>';
	$typeFromSearchShowAs[] = '<';


	// Editable zone


	// Databse Table: fields
	$fields = array();
	$fields[] = 'dialednumber';
	$fields[] = 'answertime';
	$fields[] = 'duration';
	$fields[] = 'callresult';
	$fields[] = 'billsec';
	$fields[] = 'billsec_leg_a';
	$fields[] = 'customer';
	$fields[] = 'customername';
//	$fields[] = 'uniqueid';
	$fields[] = 'campaignresult';
	$fields[] = 'response';
	$fields[] = 'detect';
	$fields[] = 'transfertime';
	$fields[] = 'transfertarget';
	$fields[] = 'resultby';
	$fields[] = 'dialedby';
//	$fields[] = 'groupname';
	$fields[] = 'recycles';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ALL")."<input type='checkbox' onclick=\"ckbAllOnClick(this);\"><BR \>";
	$headers[] = $locate->Translate("Dialed Number");
//	$headers[] = $locate->Translate("Answer Time");
	$headers[] = $locate->Translate("Duration");
	$headers[] = $locate->Translate("Billsec");
	$headers[] = $locate->Translate("Total Billsec");
	$headers[] = $locate->Translate("Call Result");
	$headers[] = $locate->Translate("Customer");
	$headers[] = $locate->Translate("Name");
//	$headers[] = $locate->Translate("Uniqueid");
	$headers[] = $locate->Translate("Campaign Result");
	$headers[] = $locate->Translate("Response");
	$headers[] = $locate->Translate("Detect");
	$headers[] = $locate->Translate("Transfertime");
	$headers[] = $locate->Translate("Transfertarget");
	$headers[] = $locate->Translate("Result By");
	$headers[] = $locate->Translate("Tried");
	$headers[] = $locate->Translate("Dialed Time");
//	$headers[] = $locate->Translate("Group");
	$headers[] = $locate->Translate("Campaign");
	$headers[] = $locate->Translate("Recycles");

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
//	$attribsHeader[] = 'width=""';

	// HTML Table: columns attributes
	$attribsCols = array();
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
//	$attribsCols[] = 'style="text-align: left"';
//	$attribsCols[] = 'style="text-align: left"';
//	$attribsCols[] = 'style="text-align: left"';

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= '';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","dialednumber","'.$divName.'","ORDERING");return false;\'';
//	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","answertime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","duration","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","billsec","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","billsec_leg_a","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","callresult","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customername","'.$divName.'","ORDERING");return false;\'';
//	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","uniqueid","'.$divName.'","ORDERING");return false;\'';	
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","campaignresult","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","response","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","detect","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","transfertime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","transfertarget","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","resultby","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","trytime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","dialedtime","'.$divName.'","ORDERING");return false;\'';
//	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","campaignname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","recycles","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'dialednumber';
	//$fieldsFromSearch[] = 'answertime';
	$fieldsFromSearch[] = 'duration';
	$fieldsFromSearch[] = 'campaigndialedlist.billsec';
	$fieldsFromSearch[] = 'campaigndialedlist.billsec_leg_a';
	$fieldsFromSearch[] = 'callresult';
	$fieldsFromSearch[] = 'customer';
	$fieldsFromSearch[] = 'customername';
	$fieldsFromSearch[] = 'uniqueid';
	$fieldsFromSearch[] = 'response';
	$fieldsFromSearch[] = 'detect';
	$fieldsFromSearch[] = 'campaignresult';
	$fieldsFromSearch[] = 'transfertarget';
	$fieldsFromSearch[] = 'resultby';
	$fieldsFromSearch[] = 'dialedby';
	$fieldsFromSearch[] = 'trytime';
	$fieldsFromSearch[] = 'dialedtime';
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'campaignname';
	$fieldsFromSearch[] = 'recycles';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("Dialed Number");
	//$fieldsFromSearchShowAs[] = $locate->Translate("Answer Time");
	$fieldsFromSearchShowAs[] = $locate->Translate("Duration");
	$fieldsFromSearchShowAs[] = $locate->Translate("Billsec");
	$fieldsFromSearchShowAs[] = $locate->Translate("Total Billsec");
	$fieldsFromSearchShowAs[] = $locate->Translate("Call Result");
	$fieldsFromSearchShowAs[] = $locate->Translate("Customer");
	$fieldsFromSearchShowAs[] = $locate->Translate("Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Uniqueid");
	$fieldsFromSearchShowAs[] = $locate->Translate("Response");
	$fieldsFromSearchShowAs[] = $locate->Translate("Detect");
	$fieldsFromSearchShowAs[] = $locate->Translate("Campaign Result");
	$fieldsFromSearchShowAs[] = $locate->Translate("Transfertarget");
	$fieldsFromSearchShowAs[] = $locate->Translate("Result By");
	$fieldsFromSearchShowAs[] = $locate->Translate("Dialed By");
	$fieldsFromSearchShowAs[] = $locate->Translate("Tried");
	$fieldsFromSearchShowAs[] = $locate->Translate("Dialed time");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group");
	$fieldsFromSearchShowAs[] = $locate->Translate("Campaign");
	$fieldsFromSearchShowAs[] = $locate->Translate("Recycles");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	$table->exportFlag = '2';//对导出标记进行赋值
	$table->deleteFlag = '1';//对删除标记进行赋值
	$table->ordering = $ordering;

	$editFlag = 1;
	$deleteFlag = 1;
	$deleteBtnFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['dialedlist']['delete']) {
			$deleteFlag = 1;
			$table->deleteFlag = '1';
			$deleteBtnFlag = 1;
		} else {
			$deleteFlag = 0;
			$table->deleteFlag = '0';
			$deleteBtnFlag = 0;
		}
		if($_SESSION['curuser']['privileges']['dialedlist']['edit']) {
			$editFlag = 1;
		}else {
			$editFlag = 0;
		}
	}
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,0,$deleteFlag,0);
	$table->setAttribsCols($attribsCols);
	$table->addRowSearchMore("campaigndialedlist",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$deleteBtnFlag,$typeFromSearch,$typeFromSearchShowAs,$stype);
	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc['select_id'] = $row['id'];
		$rowc[] = $row['dialednumber'];
//		$rowc[] = $row['answertime'];
		$rowc[] = astercrm::FormatSec($row['duration']);
		$rowc[] = astercrm::FormatSec($row['billsec']);
		$rowc[] = astercrm::FormatSec($row['billsec_leg_a']);
		$rowc[] = $row['callresult'];
		$rowc[] = $row['customer'];
		$rowc[] = $row['customername'];
//		$rowc[] = $row['uniqueid'];		
		$rowc[] = $row['campaignresult'];
		$rowc[] = $row['response'];
		$rowc[] = $row['detect'];
		$rowc[] = $row['transfertime'];
		$rowc[] = $row['transfertarget'];
		$rowc[] = $row['resultby'];
		$rowc[] = $row['trytime'];
		$rowc[] = $row['dialedtime'];
//		$rowc[] = $row['groupname'];
		$rowc[] = $row['campaignname'];
		$rowc[] = $row['recycles'];
		$table->addRow("campaigndialedlist",$rowc,0,$deleteFlag,0,$divName,$fields);
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render('delGrid');
 	
 	return $html;
}

function searchFormSubmit($searchFormValue,$numRows = null,$limit = null,$id = null,$type = null){
	global $locate,$db,$config;
	//根据后台设置导出的customer字段，来导出diallist关联的customer数据
	$customerField = '';
	if($config['system']['export_customer_fields_in_dialedlist'] != '') {
		$relateCustomerFieldArr = explode(',',$config['system']['export_customer_fields_in_dialedlist']);
		foreach($relateCustomerFieldArr as $tmp) {
			$customerField .= 'customer.'.$tmp.',';
		}
	}

	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$exportFlag = $searchFormValue['exportFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];
	$ordering = $searchFormValue['ordering'];
	$order = $searchFormValue['order'];
	$divName = "grid";
	if($optionFlag == "export" || $optionFlag == "exportcsv"){
		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,'campaigndialedlist');
		$joinstr=ltrim($joinstr,'AND');
		if($customerField != '') {
			$sql = "SELECT campaigndialedlist.dialednumber,customer.customer,campaigndialedlist.customername,campaigndialedlist.dialtime,campaigndialedlist.answertime,campaigndialedlist.duration,campaigndialedlist.billsec,campaigndialedlist.billsec_leg_a as total_billsec,campaigndialedlist.campaignresult,campaigndialedlist.response,campaigndialedlist.detect,campaigndialedlist.transfertime,campaigndialedlist.transfertarget,campaigndialedlist.resultby,campaigndialedlist.dialedby, groupname, campaignname,campaigndialedlist.dialedtime,".rtrim($customerField,',')." FROM campaigndialedlist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaigndialedlist.groupid LEFT JOIN campaign ON campaign.id = campaigndialedlist.campaignid LEFT JOIN customer ON customer.id = campaigndialedlist.customerid ";
		} else {
			$sql = "SELECT campaigndialedlist.dialednumber,customer.customer,campaigndialedlist.customername,campaigndialedlist.dialtime,campaigndialedlist.answertime,campaigndialedlist.duration,campaigndialedlist.billsec,campaigndialedlist.billsec_leg_a as total_billsec,campaigndialedlist.campaignresult,campaigndialedlist.response,campaigndialedlist.detect,campaigndialedlist.transfertime,campaigndialedlist.transfertarget,campaigndialedlist.resultby,campaigndialedlist.dialedby, groupname, campaignname,campaigndialedlist.dialedtime FROM campaigndialedlist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaigndialedlist.groupid LEFT JOIN campaign ON campaign.id = campaigndialedlist.campaignid LEFT JOIN customer ON customer.id = campaigndialedlist.customerid ";
		}
		
		if($joinstr != '') $sql .= " WHERE ".$joinstr;
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("maintable", "value", 'campaigndialedlist'); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($optionFlag == "delete"){
		astercrm::deletefromsearch($searchContent,$searchField,$searchType,'campaigndialedlist');
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','','',$divName,"",'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}elseif($optionFlag == "recycle"){
		$num = Customer::recyclefromsearch($searchContent,$searchField,$searchType,'campaigndialedlist');
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','',$order,$divName,$ordering,'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addALert($num." ".$locate->Translate("number have been recycled"));
		$objResponse->addAssign($divName, "innerHTML", $html);
		$noanswer = Customer::getNoanswerCallsNumber();
		$objResponse->addAssign("spanRecycleUp","innerHTML","No answer calls and never recycle: $noanswer");
		$objResponse->addAssign("spanRecycleDown","innerHTML","No answer calls and never recycle: $noanswer");
	}else{
		if($type == "delete"){
			$res = Customer::deleteRecord($id,'campaigndialedlist');
			if ($res){
				$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $order, $divName, $ordering,$searchType);
				$objResponse = new xajaxResponse();
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
			}else{
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
			}
		}else{
			$html = createGrid($numRows, $limit,$searchField, $searchContent, $order, $divName, $ordering,$searchType);
		}
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}
	return $objResponse->getXML();
}

function deleteByButton($f,$searchFormValue){
	$objResponse = new xajaxResponse();
	if(is_array($f['ckb'])){
		foreach($f['ckb'] as $vaule){
			$res_customer = astercrm::deleteRecord($vaule,'campaigndialedlist');
		}
	}
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$numRows = $searchFormValue['numRows'];
	$limit = $searchFormValue['limit'];     
	$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField,'grid');
	$objResponse->addAssign('grid', "innerHTML", $html);
	return $objResponse->getXML();
}

function speedDate($date_type){
	switch($date_type){
		case "td":
			$start_date = date("Y-m-d")." 00:00";
			$end_date = date("Y-m-d")." 23:59";
			break;
		case "tw":
			$date = date("Y-m-d");
			$end_date = date("Y-m-d",strtotime("$date Sunday"))." 23:59";
			$start_date = date("Y-m-d",strtotime("$end_date -6 days"))." 00:00";
			break;
		case "tm":
			$date = date("Y-m-d");
			$start_date = date("Y-m-01",strtotime($date))." 00:00";
			$end_date = date("Y-m-d",strtotime("$start_date +1 month -1 day"))." 23:59";
			break;
		case "l3m":
			$date = date("Y-m-d");
			$start_date = date("Y-m-01",strtotime("$date - 2 month"))." 00:00";	
			$date = date("Y-m-01");
			$end_date = date("Y-m-d",strtotime("$date +1 month -1 day"))." 23:59";
			break;
		case "ty":
			$start_date = date("Y-01-01")." 00:00";
			$end_date = date("Y-12-31")." 23:59";
			break;
		case "ly":
			$year = date("Y") - 1;
			$start_date = date("$year-01-01")." 00:00";
			$end_date = date("$year-12-31")." 23:59";			
			break;
			
	}

	$objResponse = new xajaxResponse();
	if(isset($start_date)) $objResponse->addAssign("sdate","value",$start_date);

	if(isset($end_date)) $objResponse->addAssign("edate","value",$end_date);
	$objResponse->addScript("CampaignDialedlist();");
	return $objResponse;
}

function getReport($aFormValues){
	global $locate;
	
	$objResponse = new xajaxResponse();
		
	list ($syear,$smonth,$sday,$stime) = split("[ -]",$aFormValues['sdate']);
	$syear = (int)$syear;
	$smonth = (int)$smonth;
	$sday = (int)$sday;
	list($shours,$smins) = split("[ :]",$stime);
	$shours = (int)$shours;
	if($shours == 0) $shours = '00';
	$smins = (int)$smins;
	if($smins == 0) $smins = '00';

	list ($eyear,$emonth,$eday,$etime) = split("[ -]",$aFormValues['edate']);
	$eyear = (int)$eyear;
	$emonth = (int)$emonth;
	$eday = (int)$eday;
	list($ehours,$emins) = split("[ :]",$etime);
	$ehours = (int)$ehours;
	if($ehours == 0) $ehours = '00';
	$emins = (int)$emins;
	if($emins == 0) $emins = '00';

	$ary = array();
    $aFormValues['sdate']=$syear."-".$smonth."-".$sday.' '.$shours.':'.$smins;
    $aFormValues['edate']=$eyear."-".$emonth."-".$eday.' '.$ehours.':'.$emins;
	
	$tableHtml = Customer::getCampaignReport($aFormValues);
	$objResponse->addAssign('campaignReport','innerHTML',$tableHtml);
	return $objResponse;
}

$xajax->processRequests();

?>