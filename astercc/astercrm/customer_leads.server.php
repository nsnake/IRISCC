<?php
/*******************************************************************************
* customer_leads.server.php

* 客户lead管理系统后台文件
* customer_leads background management script

* Function Desc
	provide customer management script

* 功能描述
	提供客户管理脚本

* Function Desc

	export				提交表单, 导出contact数据
	init				初始化页面元素
	createGrid			生成grid的HTML代码
	showDetail			显示contact信息
	searchFormSubmit    根据提交的搜索信息重构显示页面
	addSearchTr         增加搜索条件

********************************************************************************/
require_once ("db_connect.php");
require_once ("customer_leads.common.php");
require_once ('customer_leads.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('astercrm.server.common.php');
require_once ('include/common.class.php');
require_once ('include/astercrm.class.php');
require_once ('include/asterisk.class.php');

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

	$objResponse->addAssign("btnContact","value",$locate->Translate("contact"));
	$objResponse->addAssign("btnNote","value",$locate->Translate("note"));
	$objResponse->addAssign("btnCustomer","value",$locate->Translate("customer"));
	$objResponse->addAssign("btnNoteLeads","value",$locate->Translate("note_leads"));

	//*******
	$objResponse->addAssign("by","value",$locate->Translate("by"));  //搜索条件
	$objResponse->addAssign("search","value",$locate->Translate("search")); //搜索内容
	//*******

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
	global $locate,$config;
	//echo $ordering.$order;exit;
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
			$numRows =& Customer::getNumRows();
			$arreglo =& Customer::getAllRecords($start,$limit,$order);
		}elseif($flag3 != 1){
			$order = "id";
			$numRows =& Customer::getNumRowsMore($filter, $content,"customer_leads");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"customer_leads");
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
	$fields[] = 'customer';
	$fields[] = 'state';
	if($config['system']['enable_code']) {
		$fields[] = 'note';
		$fields[] = 'codes';
		$fields[] = 'note_leads.cretime';
	}
	$fields[] = 'city';
	$fields[] = 'phone';
	$fields[] = 'contact';
	$fields[] = 'website';
	$fields[] = 'category';
	$fields[] = 'cretime';
	$fields[] = 'creby';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ALL")."<input type='checkbox' onclick=\"ckbAllOnClick(this);\"><BR \>";//"select all for delete";
	$headers[] = $locate->Translate("customer_name")."<BR \>";//"Customer Name";
	$headers[] = $locate->Translate("state")."<BR \>";//"state";
	if($config['system']['enable_code']) {
		$headers[] = $locate->Translate("note")."<BR \>";
		$headers[] = $locate->Translate("codes")."<BR \>";
		$headers[] = $locate->Translate("note_cretime")."<BR \>";
	}
	$headers[] = $locate->Translate("city")."<BR \>";//"Category";
	$headers[] = $locate->Translate("phone")."<BR \>";//"Contact";
	$headers[] = $locate->Translate("contact")."<BR \>";//"Category";
	$headers[] = $locate->Translate("website")."<BR \>";//"Note";
	$headers[] = $locate->Translate("category")."<BR \>";//"Create Time";
	$headers[] = $locate->Translate("create_time")."<BR \>";//"Create By";
	$headers[] = $locate->Translate("create_by")."<BR \>";

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="5%"';
	$attribsHeader[] = 'width="12%"';
	$attribsHeader[] = 'width="8%"';
	if($config['system']['enable_code']) {
		$attribsHeader[] = 'width="8%"';
		$attribsHeader[] = 'width="8%"';
	}
	$attribsHeader[] = 'width="7%"';
	$attribsHeader[] = 'width="8%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="12%"';
	$attribsHeader[] = 'width="8%"';
	$attribsHeader[] = 'width="9%"';
	$attribsHeader[] = 'width="7%"';
//	$attribsHeader[] = 'width="5%"';

	// HTML Table: columns attributes
	$attribsCols = array();
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	if($config['system']['enable_code']) {
		$attribsCols[] = 'style="text-align: left"';
		$attribsCols[] = 'style="text-align: left"';
	}
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'nowrap style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= '';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","state","'.$divName.'","ORDERING");return false;\'';
	if($config['system']['enable_code']) {
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","note","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","codes","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","noteCretime","'.$divName.'","ORDERING");return false;\'';
	}
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","city","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","phone","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","contact","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","website","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","category","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer_leads.cretime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer_leads.creby","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'customer';
	$fieldsFromSearch[] = 'state';
	if($config['system']['enable_code']) {
		$fieldsFromSearch[] = 'note';
		$fieldsFromSearch[] = 'codes';
		$fieldsFromSearch[] = 'note_leads.cretime';
	}
	$fieldsFromSearch[] = 'city';
	$fieldsFromSearch[] = 'phone';
	$fieldsFromSearch[] = 'fax';
	$fieldsFromSearch[] = 'contact';
	$fieldsFromSearch[] = 'website';
	$fieldsFromSearch[] = 'category';
	$fieldsFromSearch[] = 'customer_leads.cretime';
	$fieldsFromSearch[] = 'customer_leads.creby';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("customer_name");
	$fieldsFromSearchShowAs[] = $locate->Translate("state");
	if($config['system']['enable_code']) {
		$fieldsFromSearchShowAs[] = $locate->Translate("note");
		$fieldsFromSearchShowAs[] = $locate->Translate("codes");
		$fieldsFromSearchShowAs[] = $locate->Translate("note_cretime");
	}
	$fieldsFromSearchShowAs[] = $locate->Translate("city");
	$fieldsFromSearchShowAs[] = $locate->Translate("phone");
	$fieldsFromSearchShowAs[] = $locate->Translate("fax");
	$fieldsFromSearchShowAs[] = $locate->Translate("contact");
	$fieldsFromSearchShowAs[] = $locate->Translate("website");
	$fieldsFromSearchShowAs[] = $locate->Translate("category");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_time");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_by");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(7,$start,$limit,$filter,$numRows,$content,$order);
	$table->exportFlag = '2';//对导出标记进行赋值
	$table->deleteFlag = '1';
	$table->ordering = $ordering;

	$deleteFlag = 1;
	$deleteBtnFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['customer_leads']['delete']) {
			$deleteFlag = 1;
			$table->deleteFlag = '1';
			$deleteBtnFlag =1;
		} else {
			$deleteFlag = 0;
			$table->deleteFlag = '0';
			$deleteBtnFlag =0;
		}
	}
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,0,$deleteFlag,0);
	$table->setAttribsCols($attribsCols);
	$table->addRowSearchMore("customer_leads",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$deleteBtnFlag,$typeFromSearch,$typeFromSearchShowAs,$stype);
	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc['select_id'] = $row['id'];
		$rowc[] = '<a href=? onclick="xajax_showDetail(\''.$row['id'].'\');return false;">'.$row['customer'].'</a>';
		$rowc[] = $row['state'];
		if($config['system']['enable_code']) {
			$rowc[] = $row['note'];
			$rowc[] = $row['codes'];
			$rowc[] = $row['noteCretime'];
		}
		$rowc[] = $row['city'];
		$rowc[] = $row['phone'];
		$rowc[] = $row['contact'];
		$rowc[] = $row['website'];
		$rowc[] = $row['category'];
		$rowc[] = $row['cretime'];
		$rowc[] = $row['creby'];
//		$rowc[] = 'Detail';
		
		$table->addRow("customer",$rowc,0,$deleteFlag,0,$divName,$fields);	
		
 	}
	
	$html = $table->render('delGrid');
	return $html;
	 
 	// End Editable Zone
}

/**
*  show customer record detail
*  @param	contactid	int			contact id
*  @return	objResponse	object		xajax response object
*/

function showDetail($customerid){
	global $locate;
	$objResponse = new xajaxResponse();
	if($customerid != null){
		$html = Table::Top($locate->Translate("customer_detail"),"formCustomerInfo");
		$html .= Customer::showCustomerLeadRecord($customerid);
		$html .= Table::Footer();
		$objResponse->addAssign("formCustomerInfo", "style.visibility", "visible");
		$objResponse->addAssign("formCustomerInfo", "innerHTML", $html);
	}
	return $objResponse->getXML();
}

function searchFormSubmit($searchFormValue,$numRows = null,$limit = null,$id = null,$type = null){
	global $locate,$db;

	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];
	$ordering = $searchFormValue['ordering'];
	$order = $searchFormValue['order'];
	
	$divName = "grid";
	if($optionFlag == "export"  || $optionFlag == "exportcsv"){
		$sql = Customer::specialGetSql($searchContent,$searchField,$searchType,'customer_leads',array('customer_leads.*','note_leads.note'=>'note','note_leads.codes'=>'codes','note_leads.creby'=>'last_note_created_by','note_leads.cretime'=>'noteLeadCsretime'),array('note_leads'=>array('note_leads.id','customer_leads.last_note_id'))); //得到要导出的sql语句

		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("maintable", "value", 'customer_leads'); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}
	if($optionFlag == "delete"){
		$customer_ref=& Customer::getRecordsFilteredMorewithstype('','', $searchField, $searchContent, $searchType,'','customer_leads','delete');
		while($customer_ref->fetchInto($row)){
			Customer::deleteRecord($row['id'],'customer_leads');
		}
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','',$order,$divName,$ordering,'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);

	}else{
		if($type == "delete"){
			$res = Customer::deleteRecord($id,'customer_leads');
			if ($res){
				$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $order, $divName, $ordering,1,$searchType);
				$objResponse = new xajaxResponse();
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
			}else{
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
			}
		}else{
			$html = createGrid($numRows, $limit,$searchField, $searchContent, $order, $divName, $ordering,1,$searchType);
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
			$res_customer = astercrm::deleteRecord($vaule,'customer_leads');
		}
	}
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$numRows = $searchFormValue['numRows'];
	$limit = $searchFormValue['limit'];     
	$html = createGrid($numRows, $limit,$searchField, $searchContent,'','grid');
	$objResponse->addAssign('grid', "innerHTML", $html);
	return $objResponse->getXML();
}

/**
*  show CustomrLeadEdit form
*  @param	id			int			id
*  @param	type		sting		customer/contact/note
*  @return	objResponse	object		xajax response object
*/

function CustomrLeadEdit($id = null){
	global $locate;
	// Edit zone
	$formdiv = 'formEditInfo';
	$html = Table::Top($locate->Translate("edit_record"),$formdiv);
	$html .= Customer::formCustomrLeadEdit($id, $type);
	$html .= Table::Footer();
   	// End edit zone

	$objResponse = new xajaxResponse();
	$objResponse->addAssign($formdiv, "style.visibility", "visible");
	$objResponse->addAssign($formdiv, "innerHTML", $html);
	
	return $objResponse->getXML();
}

function updateCustomerLead($f){
	global $config,$locate;
	$objResponse = new xajaxResponse();

	if (empty($f['customer']))
		$message = $locate->translate("The field Customer does not have to be null");
	else{
		$respOk = Customer::updateCustomerLeadRecord($f);
		if (!$respOk){
			$message = 'update customer_leads table failed';
		}
		if(!$message){
			if($respOk){
				$html = createGrid(0,ROWSXPAGE);
				$objResponse->addAssign("grid", "innerHTML", $html);
				$objResponse->addAssign("msgZone", "innerHTML", "A record has been updated");
				$objResponse->addAssign("formEditInfo", "style.visibility", "hidden");
			}else{
				$objResponse->addAssign("msgZone", "innerHTML", "The record could not be updated");
			}
		}else{
			$objResponse->addAlert($message);
		}
	}

	return $objResponse->getXML();
}

function addNote($customerLid){
	global $locate;
	$html = Table::Top($locate->Translate("add_note"),"formNoteInfo"); 			
	$html .= Customer::addNote($customerLid); 		
	$html .= Table::Footer();
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formNoteInfo", "style.visibility", "visible");
	$objResponse->addAssign("formNoteInfo", "innerHTML", $html);	
	return $objResponse->getXML();
}

function saveCustomerLeadNote($f){
	global $locate;
	$objResponse = new xajaxResponse();
	$respOk = Customer::saveCustomerLeadNote($f);
	if ($respOk){
		$objResponse->addAssign("formNoteInfo", "style.visibility", "hidden");
		$objResponse->addClear("formNoteInfo","innerHTML");	
		
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("a_new_note_added"));

	}else
		$objResponse->addAlert('can not add note');

	return $objResponse;
}

function showNoteLeads($id = ''){
	global $locate;
	if($id != ''){
		$html = Table::Top($locate->Translate("note_detail"),"formNoteInfo"); 			
		$html .= Customer::showNoteLeads($id); 		
		$html .= Table::Footer();
		$objResponse = new xajaxResponse();
		$objResponse->addAssign("formNoteInfo", "style.visibility", "visible");
		$objResponse->addAssign("formNoteInfo", "innerHTML", $html);	
		return $objResponse->getXML();
	}
}

$xajax->processRequests();

?>