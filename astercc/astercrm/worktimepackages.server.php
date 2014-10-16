<?php
/*******************************************************************************
* worktimepackages.server.php

* 账户组管理系统后台文件
* worktimepackages background management script

* Function Desc
	provide worktimepackages management script

* 功能描述
	提供帐户管理脚本

* Function Desc
		init				初始化页面元素
		showGrid			显示grid
		createGrid			生成grid的HTML代码
		add					显示添加campaign的表单
		save				保存campaign信息
		update				更新campaign信息
		edit				显示修改campaign的表单
		delete				删除campaign信息
		showDetail			显示campaign详细信息
							当前返回空值
		searchFormSubmit    根据提交的搜索信息重构显示页面

* Revision 0.0456  2007/10/30 13:47:00  last modified by solo
* Desc: modify function showDetail, make it show campaign detail when click detail

* Revision 0.045  2007/10/19 10:01:00  last modified by solo
* Desc: modify extensions description

* Revision 0.045  2007/10/18 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('worktimepackages.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');
require_once ("worktimepackages.common.php");

/**
*  initialize page elements
*
*/

function init(){
	global $locate;

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("btnDial","value",$locate->Translate("Dial list"));
	$objResponse->addAssign("btnDialed","value",$locate->Translate("Dialed"));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));
	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','')");

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
*  @return	objResponse	object		xajax response object
*/

function showGrid($start = 0, $limit = 1,$filter = null, $content = null, $order = null, $divName = "grid", $ordering = ""){
	$html .= createGrid($start, $limit,$filter, $content, $order, $divName, $ordering);
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
*  @param	divName		string		which div grid want to be put
*  @param	order		string		data order
*  @return	html		string		grid HTML code
*/

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
			$numRows =& Customer::getNumRowsMore($filter, $content,"worktimepackages");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"worktimepackages");
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
	$fields[] = 'worktimepackage_name';
	$fields[] = 'worktimepackage_note';
	$fields[] = 'worktimepackage_status';
	$fields[] = 'groupname';
	$fields[] = 'cretime';
	$fields[] = 'creby';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("Package Name");
	$headers[] = $locate->Translate("Package Note");
	$headers[] = $locate->Translate("Package Status");
	$headers[] = $locate->Translate("Group Name");
	$headers[] = $locate->Translate("Cretime");
	$headers[] = $locate->Translate("Creby");


	// HTML table: hearders attributes
	$attribsHeader = array();
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

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","worktimepackage_name","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","worktimepackage_note","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","worktimepackage_status","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","worktimepackages.cretime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","worktimepackages.creby","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'worktimepackage_name';
	$fieldsFromSearch[] = 'worktimepackage_note';
	$fieldsFromSearch[] = 'worktimepackage_status';
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'worktimepackages.cretime';
	$fieldsFromSearch[] = 'worktimepackages.creby';
	
	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("Package Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Package Note");
	$fieldsFromSearchShowAs[] = $locate->Translate("Package Status");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Cretime");
	$fieldsFromSearchShowAs[] = $locate->Translate("Creby");

	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	//$table->exportFlag = '0';//对导出标记进行赋值
	$table->ordering = $ordering;

	$editFlag = 1;
	$deleteFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['worktimepackages']['delete']) {
			$deleteFlag = 1;
		} else {
			$deleteFlag = 0;
		}
		if($_SESSION['curuser']['privileges']['worktimepackages']['edit']) {
			$editFlag = 1;
		}else {
			$editFlag = 0;
		}
	}
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$editFlag,$deleteFlag,0);
	$table->setAttribsCols($attribsCols);
	$table->addRowSearchMore("worktimepackages",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc[] = $row['worktimepackage_name'];
		$rowc[] = $row['worktimepackage_note'];
		if($row['worktimepackage_status'] == 'enable')
			$rowc[] = $locate->Translate("enable");	
		else
			$rowc[] = $locate->Translate("disable");	
		$rowc[] = $row['groupname'];
		$rowc[] = $row['cretime'];
		$rowc[] = $row['creby'];
		$table->addRow("worktimepackages",$rowc,$editFlag,$deleteFlag,0,$divName,$fields);
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render();
 	
 	return $html;
}

/**
*  generate account add form HTML code
*  @return	html		string		account add HTML code
*/

function add(){
   // Edit zone
	global $locate;
	$objResponse = new xajaxResponse();
	//$objResponse->addScript("resetC();");
	$html = Table::Top($locate->Translate("Add Worktimepackage"),"formDiv");  // <-- Set the title for your form.
	$html .= Customer::formAdd();  // <-- Change by your method
	// End edit zone
	$html .= Table::Footer();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	
	return $objResponse->getXML();
}

/**
*  save campaign record
*  @param	f			array		campaign record
*  @return	objResponse	object		xajax response object
*/

function save($f){
	global $locate;
	$objResponse = new xajaxResponse();

	if(trim($f['worktimepackage_name']) == '' ){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}

	$respOk = Customer::insertNewWorktimepackage($f); // add a new account
	if ($respOk){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("Add Worktimepackage"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
		$objResponse->addClear("formDiv", "innerHTML");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_insert"));
	}
	return $objResponse->getXML();
	
}

/**
*  update group record
*  @param	f			array		group record
*  @return	objResponse	object		xajax response object
*/

function update($f){
	//print_r($f);exit;
	global $locate;
	$objResponse = new xajaxResponse();
	if(trim($f['worktimepackage_name']) == '' ){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}

	$respOk = Customer::updateWorktimepackage($f);

	if($respOk){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("update_rec"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_update"));
	}
	
	return $objResponse->getXML();
}

/**
*  show group edit form
*  @param	id			int			group id
*  @return	objResponse	object		xajax response object
*/

function edit($id){
	global $locate,$db;
	$html = Table::Top( $locate->Translate("Edit Worktimepackage"),"formDiv"); 
	$html .= Customer::formEdit($id);
	$html .= Table::Footer();
	// End edit zone
	
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	$objResponse->addScript("resetC();");

	$res = astercrm::getRecordsByField('worktimepackage_id',$id,'worktimepackage_worktimes');
	while($res->fetchInto($row)){
		$wp[]=$row['worktime_id'];
	}

	$query = "SELECT * FROM worktimes";
	if($_SESSION['curuser']['usertype'] != 'admin') $query .= " WHERE groupid = ".$_SESSION['curuser']['groupid'];
	$worktimes_res = $db->query($query);

	$i=0;
	$weekShow=array('',$locate->Translate("Monday"),$locate->Translate('Tuesday'),$locate->Translate('Wednesday'),$locate->Translate('Thursday'),$locate->Translate('Friday'),$locate->Translate('Saturday'),$locate->Translate('Sunday'));
	while ( $worktimes_res->fetchInto($worktimes_row)) {
		$i++;
		if(in_array($worktimes_row['id'],$wp)){
			$cur_content = $worktimes_row['id'].'-'.$locate->Translate("from").':'.$worktimes_row['starttime'].'&nbsp;'.$locate->Translate("to").':'.$worktimes_row['endtime'].'&nbsp;('.$weekShow[$worktimes_row['startweek']].'->'.$weekShow[$worktimes_row['endweek']].')';
			$objResponse->addScript("mf_click(".$i.",'".$cur_content."');");
		}
	}
	return $objResponse->getXML();
}

/**
*  show group record detail
*  @param	groupid	int			group id
*  @return	objResponse	object		xajax response object
*/

function showDetail($groupid){
	$objResponse = new xajaxResponse();
	global $locate;
	$html = Table::Top( $locate->Translate("group_detail"),"formDiv"); 
	$html .= Customer::showCampaignDetail($groupid);
	$html .= Table::Footer();

	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	return $objResponse;
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
	if($optionFlag == "export"){
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'worktimepackages'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($type == "delete"){
		$res = Customer::deleteRecord($id,'worktimepackages');
		$res1 = Customer::deleteRecords("worktimepackage_id",$id,'worktimepackage_worktimes');
		
		if ($res){
			$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $order, $divName, $ordering,$searchType);
			$objResponse = new xajaxResponse();
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
			$objResponse->addAssign($divName, "innerHTML", $html);
		}else{
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
		}
	}else{
		$html = createGrid($numRows, $limit,$searchField, $searchContent, $order, $divName, $ordering,$searchType);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}
	
	return $objResponse->getXML();
}

$xajax->processRequests();
?>
