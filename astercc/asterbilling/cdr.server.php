<?php
/*******************************************************************************
* cdr.server.php

* CDR 系统后台文件
* cdr showed script

* 功能描述
	提供 cdr 查询脚本

* Function Desc
		init				初始化页面元素
		showGrid			显示grid
		createGrid			生成grid的HTML代码
		searchFormSubmit    根据提交的搜索信息重构显示页面

********************************************************************************/

require_once ("db_connect.php");
require_once ('cdr.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');
require_once ("cdr.common.php");

/**
*  initialize page elements
*
*/

function init($customerid=''){
	global $locate;
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));
	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','','grid','','','".$customerid."')");
	return $objResponse;
}

/**
*  show grid HTML code
*  @param	start		int			record start
*  @param	limit		int			how many records need
*  @param	filter		string		the field need to search
*  @param	content		string		the contect want to match
*  @param	divName		string		which div grid want to be put
*  @param	order		string		data order
*  @param	stype		string		the matching type for search 
*  @return	objResponse	object		xajax response object
*/

function showGrid($start = 0, $limit = 1,$filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$stype = null,$customerid=''){

	$html .= createGrid($start, $limit,$filter, $content, $order, $divName, $ordering,$stype,$customerid);
	$objResponse = new xajaxResponse();
	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign($divName, "innerHTML", $html);

	return $objResponse;
}


/**
*  generate grid HTML code
*  @param	start		int			record start
*  @param	limit		int			how many records need
*  @param	filter		string		the field need to search
*  @param	content		string		the contect want to match
*  @param	stype		string		the matching type for search 
*  @param	divName		string		which div grid want to be put
*  @param	order		string		data order
*  @return	html		string		grid HTML code
*/

function createGrid($start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$stype=array(),$customerid='',$allOrAnswer=null){
	global $locate,$config;
	//print_R($filter);
	//print_r($content);exit;
	if($config['system']['useHistoryCdr'] == 1) $table='historycdr';
	else $table='mycdr';
	//echo $config['system']['useHistoryCdr'];
	//echo $table;exit;
	$_SESSION['ordering'] = $ordering;
	if(is_numeric($customerid) && $customerid != 0 && $_SESSION['curuser']['usertype'] != 'clid'){
		$filter['0'] = 'customerid';
		$content['0'] = $customerid;
		$stype['0'] = 'equal';
	}

	if($filter == null || $content == null || (!is_array($content) && $content == 'Array') || (!is_array(filter) && $filter == 'Array')){
		$content = null;
		$filter = null;
		$numRows =& Customer::getNumRows($table);
		$arreglo =& Customer::getAllRecords($start,$limit,$order,'',$table);
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
		if($flag != "1" || $flag2 != "1" ){  //无值	
			$order = null;
			$numRows =& Customer::getNumRows($table,$allOrAnswer);
			$arreglo =& Customer::getAllRecords($start,$limit,$order,'',$table,$allOrAnswer);
		}elseif($flag3 != 1 ){  //未选择搜索方式
			$order = "calldate";
			$numRows =& Customer::getNumRowsMore($filter, $content,$table,$allOrAnswer);
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,$table,'',$allOrAnswer);
		}else{
			$order = "calldate";
			$numRows =& Customer::getNumRowsMorewithstype($filter, $content,$stype,$table,$allOrAnswer);
			$arreglo =& Customer::getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table,'',$allOrAnswer);
		}
	}	

	// Editable zone

	// Databse Table: fields
	$fields = array();
	$fields[] = 'calldate';
	$fields[] = 'src';
	$fields[] = 'dst';
	$fields[] = 'duration';
	$fields[] = 'billsec';
	$fields[] = 'disposition';
	$fields[] = 'credit';
	$fileds[] = 'destination';
	$fileds[] = 'memo';
	$fileds[] = 'discount';
	$fileds[] = 'note';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("Calldate");
	$headers[] = $locate->Translate("Src");
	$headers[] = $locate->Translate("Dst");
	$headers[] = $locate->Translate("Duration");
	$headers[] = $locate->Translate("Billsec");
	$headers[] = $locate->Translate("Disposition");
	$headers[] = $locate->Translate("credit");
	$headers[] = $locate->Translate("destination");
	$headers[] = $locate->Translate("memo");
	$headers[] = $locate->Translate("discount");
	$headers[] = $locate->Translate("note");

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="13%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="13%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="12%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="12%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="10%"';

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

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","calldate","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","src","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","dst","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","duration","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","billsec","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","disposition","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","credit","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","destination","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","memo","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","discount","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= '';

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

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'src';
	$fieldsFromSearch[] = 'calldate';
	$fieldsFromSearch[] = 'dst';
	$fieldsFromSearch[] = 'billsec';
	$fieldsFromSearch[] = 'disposition';
	$fieldsFromSearch[] = 'credit';
	$fieldsFromSearch[] = 'destination';
	$fieldsFromSearch[] = 'customerid';
	$fieldsFromSearch[] = 'memo';
	$fieldsFromSearch[] = 'discount';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("src");
	$fieldsFromSearchShowAs[] = $locate->Translate("calldate");
	$fieldsFromSearchShowAs[] = $locate->Translate("dst");
	$fieldsFromSearchShowAs[] = $locate->Translate("billsec");
	$fieldsFromSearchShowAs[] = $locate->Translate("disposition");
	$fieldsFromSearchShowAs[] = $locate->Translate("credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("destination");
	$fieldsFromSearchShowAs[] = $locate->Translate("customer id");
	$fieldsFromSearchShowAs[] = $locate->Translate("memo");
	$fieldsFromSearchShowAs[] = $locate->Translate("discount");

	// Create object whit 5 cols and all data arrays set before.
	$specArchive = false;
	if ($_SESSION['curuser']['usertype'] == 'admin') $specArchive = 1;
	$tableGrid = new ScrollTable(9,$start,$limit,$filter,$numRows,$content,$order,$specArchive);
	$tableGrid->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=false,$delete=false,$detail=false);
	$tableGrid->setAttribsCols($attribsCols);
	$tableGrid->exportFlag = '1';//对导出标记进行赋值
	if ($_SESSION['curuser']['usertype'] == 'admin'){
		$tableGrid->deleteFlag = '1';//对导出标记进行赋值	
	}
	$tableGrid->addRowSearchMore($table,$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$typeFromSearch,$typeFromSearchShowAs,$stype,'',$allOrAnswer);

	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$trstyle = '';
		$rowc[] = $row['id'];
		$rowc[] = $row['calldate'];
		$rowc[] = $row['src'];
		$rowc[] = $row['dst'];
		$rowc[] = astercrm::FormatSec($row['duration']);
		$rowc[] = astercrm::FormatSec($row['billsec']);
		$rowc[] = $row['disposition'];
		$rowc[] = $row['credit'];
		$rowc[] = $row['destination'];
		$rowc[] = $row['memo'];
		$rowc[] = $row['discount'];
		$rowc[] = $row['note'];
		if($row['userfield'] == 'UNBILLED') $trstyle = 'style="background:#EED5D2;"';
		if($row['setfreecall'] == 'yes') $trstyle = 'style="background:#d5c59f;"';
		$tableGrid->addRow($table,$rowc,false,false,false,$divName,$fields,$trstyle);
 	}
 	
 	// End Editable Zone
 	
 	$html = $tableGrid->render();
 	
 	return $html;
}


function searchFormSubmit($searchFormValue,$numRows,$limit,$id,$type){
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$searchType = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$exportFlag = $searchFormValue['exportFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];			//搜索方式 数组
	$divName = "grid";

	$allOrAnswer = $searchFormValue['allOrAnswer'];#选中的radio值
	
	if($exportFlag == "1" || $optionFlag == "export"){
		if($config['system']['useHistoryCdr'] == 1) $table='historycdr';
		else $table='mycdr';

		if($searchFormValue['allOrAnswer'] == 'answered'){
			$searchContent[] = '0';
			$searchField[] = 'billsec';
			$searchType[] = 'more';
		}
		
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
			$fieldArray = array('id','calldate','src','dst','srcname','channel','dstchannel','didnumber','duration','billsec','billsec_leg_a','disposition','accountcode','userfield','srcuid','dstuid','queue','calltype','credit','callshopcredit','resellercredit','groupid','resellerid','userid','accountid','destination','monitored','memo','dialstring','dialstatus','children','ischild','processed','customerid','crm_customerid','contactid','discount','payment','note','setfreecall','astercrm_groupid','hangupcause','hangupcausetxt');
		}else if($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$fieldArray = array('id','calldate','src','dst','srcname','channel','dstchannel','didnumber','duration','billsec','billsec_leg_a','disposition','accountcode','userfield','srcuid','dstuid','queue','calltype','credit','callshopcredit','groupid','resellerid','userid','accountid','destination','monitored','memo','dialstring','dialstatus','children','ischild','processed','customerid','crm_customerid','contactid','discount','payment','note','setfreecall','astercrm_groupid','hangupcause','hangupcausetxt');
		}else if($_SESSION['curuser']['usertype'] == 'operator' && $_SESSION['curuser']['usertype'] == 'clid'){
			$fieldArray = array('id','calldate','src','dst','srcname','channel','dstchannel','didnumber','duration','billsec','billsec_leg_a','disposition','accountcode','userfield','srcuid','dstuid','queue','calltype','credit','groupid','resellerid','userid','accountid','destination','monitored','memo','dialstring','dialstatus','children','ischild','processed','customerid','crm_customerid','contactid','discount','payment','note','setfreecall','astercrm_groupid','hangupcause','hangupcausetxt');
		}

		$sql = astercrm::getSql($searchContent,$searchField,$searchType,$table,implode(',',$fieldArray)); //得到要导出的sql语句
		
		$_SESSION['export_sql'] = $sql;
		
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addScript("document.getElementById('exportForm').submit();");
		return $objResponse->getXML();
	}elseif($optionFlag == "delete"){
		if($config['system']['useHistoryCdr'] == 1) $table='historycdr';
		else $table='mycdr';

		if($searchFormValue['allOrAnswer'] == 'answered'){
			$searchContent[] = '0';
			$searchField[] = 'billsec';
			$searchType[] = 'more';
		}

		astercrm::deletefromsearch($searchContent,$searchField,$searchType,$table);
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','','',$divName,"",$searchType,'',$allOrAnswer);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}elseif($type == "delete"){
		$res = '';
		if ($res){
			$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "",$searchType,'',$allOrAnswer);
			$objResponse = new xajaxResponse();
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
		}else{
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
		}
	}else{
		$html = createGrid($numRows, $limit,$searchField, $searchContent,  $searchField[count($searchField)-1], $divName, "",$searchType,'',$allOrAnswer);
	}
	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign($divName, "innerHTML", $html);
	return $objResponse->getXML();
}

function archiveCDR($archiveDate){
	global $db,$locate,$config;
	$objResponse = new xajaxResponse();
	$date = date("Y-m-d");
	$end_date = date("Y-m-d",strtotime("$date - $archiveDate month"));
	
	if($config['system']['useHistoryCdr'] == 1) 
		$table='historycdr';
	else 
		$table='mycdr';
		
	$sql = "SELECT calldate FROM $table WHERE calldate < '".$end_date."' ORDER BY calldate ASC LIMIT 1";
	$start_date = $db->getOne($sql);
	if($start_date == '') {
		$objResponse->addAlert($locate->Translate('no cdr data early than')." ".$archiveDate." ".$locate->Translate('months'));
		$objResponse->addAssign("divMsg","style.visibility","hidden");
		$objResponse->addClear("msgZone", "innerHTML");
		return $objResponse->getXML();
	}

	$file_dir=$config['system']['upload_file_path']."cdr_archive";

	if(!is_dir($file_dir)){
		if(!mkdir($file_dir)){
			$objResponse->addAlert($locate->Translate('cant create archive directory'));
			$objResponse->addAssign("divMsg","style.visibility","hidden");
			$objResponse->addClear("msgZone", "innerHTML");
			return $objResponse->getXML();
		}
	}

	$start_date = split('\ ',$start_date);
	$start_date = $start_date['0'];
	$file_name = $start_date."_to_".$end_date;

	if (!$handle = fopen($file_dir."/".$file_name.".csv", 'x')) {
		$objResponse->addAlert($locate->Translate('cant create archive file'));
		$objResponse->addAssign("divMsg","style.visibility","hidden");
		$objResponse->addClear("msgZone", "innerHTML");
		return $objResponse->getXML();
	}

	$sql = "SELECT * FROM $table WHERE calldate < '".$end_date."' ORDER BY calldate ASC";

	$archiveData = astercrm::exportDataToCSV($sql);	

	if (!fwrite($handle, $archiveData)) {
		$objResponse->addAlert($locate->Translate('cant create archive file'));
		$objResponse->addAssign("divMsg","style.visibility","hidden");
		$objResponse->addClear("msgZone", "innerHTML");
		return $objResponse->getXML();
	}
	fclose($handle);

	system("tar zcf ".$file_dir."/".$file_name.".tar.gz ".$file_dir."/".$file_name.".csv",$r);

	if($r === false ){
		$final_file = $file_dir."/".$file_name.".csv";
	}else{		
		$final_file = $file_dir."/".$file_name.".tar.gz";
		unlink($file_dir."/".$file_name.".csv");
	}

	$objResponse->addAlert($locate->Translate('archive success').", ".$locate->Translate('file save in').": ".$final_file);
		
	$sql = "DELETE FROM $table WHERE calldate < '".$end_date."'";
	$res = $db->query($sql);
	if($res == 1){
		$objResponse->addAlert($locate->Translate('clear cdr date success'));
	}else{ 
		$objResponse->addAlert($locate->Translate('clear cdr date failed'));
	}
	$html = createGrid(0,ROWSXPAGE);
	$objResponse->addAssign("divMsg","style.visibility","hidden");
	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign("grid", "innerHTML", $html);
	return $objResponse->getXML();
    //echo $file_name;exit;
}

$xajax->processRequests();
?>
