<?php
/*******************************************************************************
* note.server.php

* Function Desc
	provide note management script

* 功能描述
	提供备注管理脚本

* Function Desc

	export				提交表单, 导出contact数据
	init				初始化页面元素
	createGrid			生成grid的HTML代码
	searchFormSubmit    根据提交的搜索信息重构显示页面

* Revision 0.045  2007/10/22 16:45:00  last modified by solo
* Desc: remove function "export"

* Revision 0.045  2007/10/18 14:08:00  last modified by solo
* Desc: comment added

********************************************************************************/
require_once ("db_connect.php");
require_once ('note.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');
require_once ('astercrm.server.common.php');


/**
*  initialize page elements
*
*/

function init(){
	global $locate;

	$objResponse = new xajaxResponse();

	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','')");

	$objResponse->addAssign("btnCustomer","value",$locate->Translate("customer"));
	$objResponse->addAssign("btnContact","value",$locate->Translate("contact"));

	return $objResponse;
}

/**
*  generate grid HTML code
*  @param	start		int			record start
*  @param	limit		int			how many records need
*  @param	filter		string		the field need to search
*  @param	content		string		the contect want to match
*  @param	divName		string		which div grid want to be put
*  @param	order		string		data order
*  @return	html		string		grid HTML code
*/

function createGrid($start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$stype=array()){
	global $locate,$config;
	
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
			$numRows =& Customer::getNumRowsMore($filter, $content,"note");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"note");
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
	$fields[] = 'note';
	$fields[] = 'priority';
	$fields[] = 'contact';
	$fields[] = 'customer';
	$fields[] = 'callerid';
	$fields[] = 'note.cretime';
	$fields[] = 'note.creby';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ALL")."<input type='checkbox' onclick=\"ckbAllOnClick(this);\">";//"select all for delete";
	$headers[] = $locate->Translate("note");
	$headers[] = $locate->Translate("priority");
	if($config['system']['enable_code']) {
		$headers[] = $locate->Translate("codes");
	}
	$headers[] = $locate->Translate("contact");
	$headers[] = $locate->Translate("customer_name");//"Customer Name";
	$headers[] = $locate->Translate("callerid");
	$headers[] = $locate->Translate("create_time");//"Create By";
	$headers[] = $locate->Translate("create_by");

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="8%"';
	$attribsHeader[] = 'width="19%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="12%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="10%"';
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

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= '';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","note","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","priority","'.$divName.'","ORDERING");return false;\'';
	if($config['system']['enable_code']) {
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","codes","'.$divName.'","ORDERING");return false;\'';
	}
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","contact","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","callerid","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","note.cretime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","note.creby","'.$divName.'","ORDERING");return false;\'';
	

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'note';
	$fieldsFromSearch[] = 'priority';
	if($config['system']['enable_code']) {
		$fieldsFromSearch[] = 'codes';
	}
	$fieldsFromSearch[] = 'contact.contact';
	$fieldsFromSearch[] = 'customer.customer';
	$fieldsFromSearch[] = 'note.callerid';
	$fieldsFromSearch[] = 'note.cretime';
	$fieldsFromSearch[] = 'note.creby';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("note");
	$fieldsFromSearchShowAs[] = $locate->Translate("priority");
	if($config['system']['enable_code']) {
		$fieldsFromSearchShowAs[] = $locate->Translate("codes");
	}
	$fieldsFromSearchShowAs[] = $locate->Translate("contact");
	$fieldsFromSearchShowAs[] = $locate->Translate("customer_name");
	$fieldsFromSearchShowAs[] = $locate->Translate("callerid");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_time");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_by");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);

	$table->exportFlag = '2';//对导出标记进行赋值
	$table->deleteFlag = '1';
	$table->ordering = $ordering;

	$editFlag = 1;
	$deleteFlag = 1;
	$deleteBtnFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['note']['delete']) {
			$deleteFlag = 1;
			$table->deleteFlag = '1';
			$deleteBtnFlag = 1;
		} else {
			$deleteFlag = 0;
			$table->deleteFlag = '0';
			$deleteBtnFlag = 0;
		}
		if($_SESSION['curuser']['privileges']['note']['edit']) {
			$editFlag = 1;
		}else {
			$editFlag = 0;
		}
	}
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$editFlag,$deleteFlag,0);
	$table->setAttribsCols($attribsCols);
	//$table->addRowSearchMore("note",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content);
	$table->addRowSearchMore("note",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$deleteBtnFlag,$typeFromSearch,$typeFromSearchShowAs,$stype);
	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc['select_id'] = $row['id'];
		$rowc[] = $row['note'];
		$rowc[] = $row['priority'];
		if($config['system']['enable_code']) {
			$rowc[] = $row['codes'];
		}
		$rowc[] = $row['contact'];
		$rowc[] = $row['customer'];
		$rowc[] = $row['callerid'];
		$rowc[] = $row['cretime'];
		$rowc[] = $row['creby'];
//		$rowc[] = 'Detail';
		$table->addRow("note",$rowc,$editFlag,$deleteFlag,0,$divName,$fields);
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render('delGrid');
 	
 	return $html;
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
	$ordering = $searchFormValue['ordering'];
	$order = $searchFormValue['order'];
	$divName = "grid";
	$searchType =  $searchFormValue['searchType'];
	if($optionFlag == "export" || $optionFlag == "exportcsv"){
		$sql =& Customer::getExportSql($searchContent,$searchField,$searchType,'note'); //得到要导出的sql语句
		//$_SESSION['export_sql'] = $sql;
		//echo $sql;exit;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addAssign("maintable", "value", "note");//传递主表名，防止groupid等字段在各表中重复
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($optionFlag == "delete"){
		astercrm::deletefromsearch($searchContent,$searchField,$searchType,'note');
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','',$order,$divName,$ordering,'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}else{
		if($type == "delete"){
		$res = Customer::deleteRecord($id,'note');
		if ($res){
				$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "",$searchType);
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
			$res = astercrm::deleteRecord($vaule,'note');
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

$xajax->processRequests();

?>