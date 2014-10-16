<?php
/*******************************************************************************
* diallist.server.php

* 拨号列表管理系统后台文件
* diallist table background management script

* Function Desc
	provide diallist management script

* 功能描述
	提供帐户管理脚本

* Function Desc
		init				初始化页面元素
		createGrid
		showGrid
		add
		save
		delete
		searchFormSubmit    多条件搜索

* Revision 0.045  2007/10/18 20:43:00  last modified by solo
* Desc: add function add, showGrid, save, delete

* Revision 0.045  2007/10/18 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ("diallist.common.php");
require_once ('include/xajaxGrid.inc.php');
require_once ('diallist.grid.inc.php');
require_once ('include/common.class.php');


function showGrid($start = 0, $limit = 1,$filter = null, $content = null, $order = null, $divName = "grid", $ordering = ""){
	
	$html = createGrid($start, $limit,$filter, $content, $order, $divName, $ordering);
	$objResponse = new xajaxResponse();
	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign($divName, "innerHTML", $html);
	
	return $objResponse->getXML();
}

function init(){
	global $locate;

	$objResponse = new xajaxResponse();

	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("btnDialed","value",$locate->Translate("Dialed"));
	$objResponse->addAssign("btnCampaign","value",$locate->Translate("Campaign"));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','')");
	return $objResponse;
}

//	create grid
function createGrid($start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$stype=array()){
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
			$numRows =& Customer::getNumRows();
			$arreglo =& Customer::getAllRecords($start,$limit,$order);
		}elseif($flag3 != 1){
			$order = "id";
			$numRows =& Customer::getNumRowsMore($filter, $content,"diallist");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"diallist");
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
	$fields[] = 'dialnumber';
	$fields[] = 'customer';
	$fields[] = 'assign';
	$fields[] = 'dialtime';
	$fields[] = 'groupname';
	$fields[] = 'campaignname';
	$fields[] = 'customername';
	$fields[] = 'callOrder';
	$fields[] = 'creby';
	$fields[] = 'memo';
	
	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ALL")."<input type='checkbox' onclick=\"ckbAllOnClick(this);\"><BR \>";
	$headers[] = $locate->Translate("Number");
	$headers[] = $locate->Translate("Customer");
	$headers[] = $locate->Translate("Assign to");
	$headers[] = $locate->Translate("Dialtime");
	$headers[] = $locate->Translate("Group Name");
	$headers[] = $locate->Translate("Campaign Name");
	$headers[] = $locate->Translate("Name");
	$headers[] = $locate->Translate("Call Order");
	$headers[] = $locate->Translate("Create by");
	$headers[] = $locate->Translate("Memo");
	
	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="';
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

	// HTML Table: columns attributes
	$attribsCols = array();
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= '';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","dialnumber","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","assign","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","dialtime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","campaignname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customername","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","callOrder","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","creby","'.$divName.'","ORDERING");return false;\'';
	

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'diallist.dialnumber';
	$fieldsFromSearch[] = 'customer';
	$fieldsFromSearch[] = 'diallist.assign';
	$fieldsFromSearch[] = 'diallist.dialtime';
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'campaignname';
	$fieldsFromSearch[] = 'customername';
	$fieldsFromSearch[] = 'diallist.cretime';
	$fieldsFromSearch[] = 'diallist.creby';


	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("number");
	$fieldsFromSearchShowAs[] = $locate->Translate("Customer");
	$fieldsFromSearchShowAs[] = $locate->Translate("assign_to");
	$fieldsFromSearchShowAs[] = $locate->Translate("dialtime");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Campaign Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Create By");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	//$table->addRowSearch("diallist",$fieldsFromSearch,$fieldsFromSearchShowAs);
	
	$table->exportFlag = '2';//对导出标记进行赋值
	$table->deleteFlag = '1';//对删除标记进行赋值
	$table->ordering = $ordering;

	$editFlag = 1;
	$deleteFlag = 1;
	$delteBtnFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['diallist']['delete']) {
			$deleteFlag = 1;
			$delteBtnFlag = 1;
			$table->deleteFlag = '1';
		} else {
			$deleteFlag = 0;
			$delteBtnFlag = 0;
			$table->deleteFlag = '0';
		}
		if($_SESSION['curuser']['privileges']['diallist']['edit']) {
			$editFlag = 1;
		}else {
			$editFlag = 0;
		}
	}
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$editFlag,$deleteFlag,0);
	$table->setAttribsCols($attribsCols);
	$table->addRowSearchMore("diallist",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$delteBtnFlag,$typeFromSearch,$typeFromSearchShowAs,$stype);

	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc['select_id'] = $row['id'];
		$rowc[] = $row['dialnumber'];
		$rowc[] = $row['customer'];
		$rowc[] = $row['assign'];
		$rowc[] = $row['dialtime'];
		$rowc[] = $row['groupname'];
		$rowc[] = $row['campaignname'];
		$rowc[] = $row['customername'];
		$rowc[] = $row['callOrder'];
		$rowc[] = $row['creby'];
		$rowc[] = $row['memo'];

		$table->addRow("diallist",$rowc,$editFlag,$deleteFlag,0,$divName,$fields);
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render('delGrid');
 	
 	return $html;
}

function add(){
	global $locate;
	$objResponse = new xajaxResponse();

	$html = Table::Top($locate->Translate("add_diallist"),"formDiv");  
	$html .= Customer::formAdd();
	$html .= Table::Footer();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	//增加读取campaign的js函数
	$objResponse->addScript("setCampaign();");

	return $objResponse->getXML();
}

function edit($id){
	global $locate;
	$html = Table::Top( $locate->Translate("Edit_Record"),"formDiv"); 
	$html .= Customer::formEdit($id);
	$html .= Table::Footer();
	// End edit zone

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	//$objResponse->addScript("setCampaign();");
	return $objResponse->getXML();
}

function update($f){
	global $locate;
	$objResponse = new xajaxResponse();
	
	if(trim(astercrm::getDigitsInStr($f['dialnumber'])) == ''){
		$objResponse->addAlert($locate->Translate("dial number must be digits"));
		return $objResponse->getXML();
	}

	if(trim(astercrm::getDigitsInStr($f['dialnumber'])) == '' || trim($f['groupid']) == '' || trim($f['campaignid']) == ''){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}
	// check if the assign number belong to this group
	if ($_SESSION['curuser']['usertype'] != 'admin' && $f['assign'] != ""){
		$flag = false;
		foreach ($_SESSION['curuser']['memberExtens'] as $extension){
			if ($extension == $f['assign']){
				$flag = true; 
				break;
			}
		}

		if (!$flag){
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("Cant insert, please confirm the assign number is in your group"));
			return $objResponse;
		}
	}

	$respOk = Customer::updateDiallistRecord($f);

	if ($respOk->message == ''){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("update_rec"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_update").":".$respOk->message);
	}
	
	return $objResponse->getXML();
}

function setCampaign($groupid){
	$objResponse = new xajaxResponse();
	$res = Customer::getRecordsByGroupid($groupid,"campaign");
	//添加option
	while ($res->fetchInto($row)) {
		$objResponse->addScript("addOption('campaignid','".$row['id']."','".$row['campaignname']."');");
	}
	return $objResponse;
}

function save($f){
	global $locate;
	$objResponse = new xajaxResponse();

	if(trim(astercrm::getDigitsInStr($f['dialnumber'])) == ''){
		$objResponse->addAlert($locate->Translate("dial number must be digits"));
		return $objResponse->getXML();
	}

	if(trim(astercrm::getDigitsInStr($f['dialnumber'])) == '' || trim($f['groupid']) == '' || trim($f['campaignid']) == ''){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}

	if($f['campaignid'] == ''){
		$objResponse->addAlert($locate->Translate("Must select a campaign"));
		return $objResponse->getXML();
	}

	// check if the assign number belong to this group
	if ($_SESSION['curuser']['usertype'] != 'admin' && $f['assign'] != ""){
		$flag = false;
		foreach ($_SESSION['curuser']['memberExtens'] as $extension){
			if ($extension == $f['assign']){
				$flag = true; 
				break;
			}
		}

		if (!$flag){
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("Cant insert, please confirm the assign number is in your group"));
			return $objResponse;
		}
	}

	$id = Customer::insertNewDiallist($f); 
	$html = createGrid(0,ROWSXPAGE);
	$objResponse->addAssign("grid", "innerHTML", $html);
	$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("diallist_added"));
	$objResponse->addAssign("formDiv", "style.visibility", "hidden");
	$objResponse->addClear("formDiv", "innerHTML");
	return $objResponse->getXML();
}

function searchFormSubmit($searchFormValue,$numRows = null,$limit = null,$id = null,$type = null){
	global $locate,$db;
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
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'diallist'); //得到要导出的sql语句
		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,'diallist');
		$joinstr=ltrim($joinstr,'AND');
		$sql = "SELECT diallist.dialnumber, customer.customer,diallist.customername,diallist.dialtime, diallist.assign,diallist.status,groupname,campaignname,diallist.cretime,diallist.creby,diallist.memo FROM diallist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = diallist.groupid LEFT JOIN campaign ON campaign.id = diallist.campaignid  LEFT JOIN customer ON customer.id = diallist.customerid";
		if($joinstr != '') $sql .= " WHERE ".$joinstr;
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("maintable", "value", 'diallist'); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($optionFlag == "delete"){
		astercrm::deletefromsearch($searchContent,$searchField,$searchType,'diallist');
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','',$order,$divName,$ordering,'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}else{
		if($type == "delete"){
			$res = Customer::deleteRecord($id,'diallist');
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
			$res_customer = astercrm::deleteRecord($vaule,'diallist');
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

function checkDuplicates($f){
	global $db,$locate;
	
	$html = Table::Top($locate->Translate("Duplicate Recorder"),"formDuplicate"); 			
	$html .= Customer::createDupGrid($f);	
	$html .= Table::Footer();
	//$html .= '<input id="curdupdate" type="hidden" value="">';
	$objResponse = new xajaxResponse();
	$objResponse->addScript("var curf = xajax.getFormValues('searchForm');xajax.$('curdupdate').value=curf;");
	$objResponse->addAssign("formDuplicate", "style.visibility", "visible");
	$objResponse->addAssign("formDuplicate", "innerHTML", $html);	
	return $objResponse->getXML();

	

	//DELETE diallist as a FROM diallist as a ,( SELECT * FROM diallist GROUP BY dialnumber HAVING COUNT(dialnumber) > 1 ) as b WHERE a.dialnumber = b.dialnumber and a.id <=  b.id ;
}

function clearDuplicates($f){
	global $db,$locate;
	
	$r = Customer::deleteDuplicates($f);

	$objResponse = new xajaxResponse();
	if($r){
		$objResponse->addAssign("formDuplicate", "style.visibility", "");
		$objResponse->addAssign("formDuplicate", "innerHTML", '');
		$objResponse->addScript("xajax_init()");		
		$objResponse->addAlert($locate->Translate("Clear success"));
	}else{
		$objResponse->addAlert($locate->Translate("Clear failed"));
	}
	return $objResponse;
	//DELETE diallist as a FROM diallist as a ,( SELECT * FROM diallist GROUP BY dialnumber HAVING COUNT(dialnumber) > 1 ) as b WHERE a.dialnumber = b.dialnumber and a.id <=  b.id ;
}

function exportDuplicates($f) {

	$objResponse = new xajaxResponse();
	$joinstr = astercrm::createSqlWithStype($f['searchField'],$f['searchContent'],$f['searchType'],"diallist");
	
	$ajoinstr = str_replace('diallist.','a.',$joinstr);
	if ($_SESSION['curuser']['usertype'] != 'admin'){
			$ajoinstr .= " AND a.groupid = '".$_SESSION['curuser']['groupid']."'";
			$joinstr .= " AND diallist.groupid = '".$_SESSION['curuser']['groupid']."'";
	}


	$query = "SELECT a.*,campaign.campaignname FROM diallist as a LEFT JOIN campaign ON campaign.id=a.campaignid,( SELECT * FROM diallist WHERE 1 ".$joinstr." GROUP BY dialnumber HAVING COUNT(dialnumber) > 1 ) as b WHERE a.dialnumber = b.dialnumber AND a.id <> b.id ".$ajoinstr." ";

	$_SESSION['export_sql'] = $query;
	$objResponse->addAssign("hidSql", "value", $query); //赋值隐含域
	$objResponse->addAssign("maintable", "value", 'diallist_dup'); //赋值隐含域
	$objResponse->addAssign("exporttype", "value", 'exportcsv');
	$objResponse->addScript("document.getElementById('exportForm').submit();");
	return $objResponse;
}

$xajax->processRequests();

?>