<?php
/*******************************************************************************
* surveyresult.server.php

* Function Desc
	provide surveyresult management script

* 功能描述
	提供问卷管理脚本

* Function Desc

	showGrid
	init				初始化页面元素
	createGrid			生成grid的HTML代码
	delete				删除一条问卷结果
	searchFormSubmit    根据提交的搜索信息重构显示页面
	add					null
	showDetail			null

* Revision 0.045  2007/10/18 15:38:00  last modified by solo
* Desc: comment added

********************************************************************************/
require_once ("db_connect.php");
require_once ("surveyresult.common.php");
require_once ('surveyresult.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');



function showCustomer($id = 0, $type="customer"){
	global $locate;
	$objResponse = new xajaxResponse();
	if($id != 0 && $id != null ){
		$html = Table::Top($locate->Translate("customer_detail"),"formCustomerInfo"); 			
		$html .= Customer::showCustomerRecord($id,$type); 		
		$html .= Table::Footer();
		$objResponse->addAssign("formCustomerInfo", "style.visibility", "visible");
		$objResponse->addAssign("formCustomerInfo", "innerHTML", $html);	
		return $objResponse->getXML();
	}else
		return $objResponse->getXML();
}

function showContact($id = null, $type="contact"){
	global $locate;
	$objResponse = new xajaxResponse();

	if($id != null ){
		$html = Table::Top($locate->Translate("contact_detail"),"formContactInfo"); 
		$contactHTML .= Customer::showContactRecord($id,$type);

		if ($contactHTML == '')
			return $objResponse->getXML();
		else
			$html .= $contactHTML;

		$html .= Table::Footer();
		$objResponse->addAssign("formContactInfo", "style.visibility", "visible");
		$objResponse->addAssign("formContactInfo", "innerHTML", $html);	
		return $objResponse->getXML();
	}
}


function showGrid($start = 0, $limit = 1,$filter = null, $content = null, $order = null, $divName = "grid", $ordering = ""){
	
	$html = createGrid($start, $limit,$filter, $content, $order, $divName, $ordering);
	$objResponse = new xajaxResponse();
	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign($divName, "innerHTML", $html);
	
	return $objResponse->getXML();
}

/**
*  initialize page elements
*
*/

function init(){
	global $locate;//,$config,$db;

	$objResponse = new xajaxResponse();

	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','')");

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
function createGrid($start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$exportFlag="",$stype=array()){
	//print_r($stype);exit;
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
			$numRows =& Customer::getNumRowsMore($filter, $content,"surveyresult");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"surveyresult");
		}else {
			$order = "id";
			$numRows =& Customer::getNumRowsMorewithstype($filter, $content,$stype,"surveyresult");
			$arreglo =& Customer::getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,"surveyresult");
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
	$typeFromSearchShowAs[] = $locate->Translate('like');
	$typeFromSearchShowAs[] = '=';
	$typeFromSearchShowAs[] = '>';
	$typeFromSearchShowAs[] = '<';


	// Editable zone

	// Databse Table: fields
	$fields = array();
	$fields[] = 'surveytitle';
	$fields[] = 'surveyoption';
	$fields[] = 'surveynote';
	$fields[] = 'customer';
	$fields[] = 'contact';
	$fields[] = 'phonenumber';
	$fields[] = 'campaignname';
	$fields[] = 'cretime';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("Survey Title");
	$headers[] = $locate->Translate("Survey Option");
	$headers[] = $locate->Translate("Survey Item");
	$headers[] = $locate->Translate("Survey Note");
	$headers[] = $locate->Translate("customer");
	$headers[] = $locate->Translate("contact");
	$headers[] = $locate->Translate("Phone Number");
		$headers[] = $locate->Translate("Campaign Name");
	$headers[] = $locate->Translate("create_time");

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

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","surveyname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","surveyoption","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","itemcontent","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","surveynote","'.$divName.'","ORDERING");return false;\'';
 	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","contact","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","phonenumber","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","campaignname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","cretime","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'surveytitle';
	$fieldsFromSearch[] = 'surveyoption';
	$fieldsFromSearch[] = 'itemcontent';
	$fieldsFromSearch[] = 'surveyresult.surveynote';
	$fieldsFromSearch[] = 'customer';
	$fieldsFromSearch[] = 'contact.contact';	
	$fieldsFromSearch[] = 'surveyresult.phonenumber';
	$fieldsFromSearch[] = 'campaign.campaignname';
	//$fieldsFromSearch[] = 'survey.id';
	$fieldsFromSearch[] = 'surveyresult.cretime';
	$fieldsFromSearch[] = 'surveyresult.creby';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("survey_title");
	$fieldsFromSearchShowAs[] = $locate->Translate("survey_option");
	$fieldsFromSearchShowAs[] = $locate->Translate("survey_item");
	$fieldsFromSearchShowAs[] = $locate->Translate("survey_note");
	$fieldsFromSearchShowAs[] = $locate->Translate("customer");
	$fieldsFromSearchShowAs[] = $locate->Translate("contact");	
	$fieldsFromSearchShowAs[] = $locate->Translate("Phone Number");
	$fieldsFromSearchShowAs[] = $locate->Translate("Campaign Name");
	//$fieldsFromSearchShowAs[] = $locate->Translate("Survey ID");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_time");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_by");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	$table->exportFlag = '1';//对导出标记进行赋值
	$table->deleteFlag = '1';//对删除标记进行赋值
	$deleteFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['surveyresult']['delete']) {
			$deleteFlag = 1;
			$table->deleteFlag = '1';
		} else {
			$deleteFlag = 0;
			$table->deleteFlag = '0';
		}
	}
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,0,$deleteFlag,0);
	$table->setAttribsCols($attribsCols);
	$table->ordering = $ordering;
	$table->addRowSearchMore("surveyresult",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc[] = $row['surveyname'];
		$rowc[] = $row['surveyoption'];
		$rowc[] = $row['itemcontent'];
		$rowc[] = $row['surveynote'];
		$rowc[] = "<a href=? onclick='xajax_showCustomer(".$row['customerid'].");return false;'>".$row['customer']."</a>";
		$rowc[] = "<a href=? onclick='xajax_showContact(".$row['contactid'].");return false;'>".$row['contact']."</a>";
		$rowc[] = $row['phonenumber'];
		$rowc[] = $row['campaignname'];
		$rowc[] = $row['cretime'];

		$table->addRow("surveyresult",$rowc,0,$deleteFlag,0,$divName,$fields);

 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render();
 	
 	return $html;
}

/**
*  delete survey result
*  @param	id				int			record start
*  @param	table_DB		string		table name
*  @return	objResponse		object		xajax response object
*/

function delete($id = null, $table_DB = null){
	global $locate;
	Customer::deleteRecord($id,$table_DB); 				// <-- Change by your method
	$html = createGrid(0,ROWSXPAGE);
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("grid", "innerHTML", $html);
	$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("record_deleted")); 
	return $objResponse->getXML();
}

/**
*  return null
*/
function add($surveyid = 0){
	global $locate;
	$objResponse = new xajaxResponse();
	return $objResponse;
}

/**
*  return null
*/
function showDetail($surveyid){
	$objResponse = new xajaxResponse();
	return $objResponse;
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
	$divName = "grid";
	$searchType =  $searchFormValue['searchType'];
	$ordering = $searchFormValue['ordering'];
	$order = $searchFormValue['order'];
	if($exportFlag == "1" || $optionFlag == "export"){
		// 需要特殊处理

//		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'surveyresult'); //得到要导出的sql语句
		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,'surveyresult');

		$objResponse->addAssign("hidSql", "value", $joinstr); //赋值隐含域
		$objResponse->addScript("document.getElementById('exportForm').submit();");

	}elseif($optionFlag == "delete"){
		astercrm::deletefromsearch($searchContent,$searchField,$searchType,'surveyresult');
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','',$order,$divName,$ordering,1,1,'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}else{
		if($type == "delete"){
			$res = Customer::deleteRecord($id,'surveyresult');
			if ($res){
				$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $order, $divName, $ordering,"",$searchType);
				$objResponse = new xajaxResponse();
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
			}else{
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
			}
		}else{
			$html = createGrid($numRows, $limit,$searchField, $searchContent, $order, $divName, $ordering,"",$searchType);
		}
		$objResponse = new xajaxResponse();
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}
	return $objResponse->getXML();
}

$xajax->processRequests();

?>