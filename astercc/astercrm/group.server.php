<?php
/*******************************************************************************
* group.server.php

* 账户组管理系统后台文件
* group background management script

* Function Desc
	provide group management script

* 功能描述
	提供帐户管理脚本

* Function Desc
		init				初始化页面元素
		showGrid			显示grid
		createGrid			生成grid的HTML代码
		add					显示添加group的表单
		save				保存group信息
		update				更新group信息
		edit				显示修改group的表单
		delete				删除group信息
		showDetail			显示group详细信息
							当前返回空值
		searchFormSubmit    根据提交的搜索信息重构显示页面

* Revision 0.0456  2007/10/30 13:47:00  last modified by solo
* Desc: modify function showDetail, make it show group detail when click detail

* Revision 0.045  2007/10/19 10:01:00  last modified by solo
* Desc: modify extensions description

* Revision 0.045  2007/10/18 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('group.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');
require_once ("group.common.php");

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
			$numRows =& Customer::getNumRowsMore($filter, $content);
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order);
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
	$fields[] = 'groupname';
	$fields[] = 'groupid';
	$fields[] = 'firstring';
	$fields[] = 'allowloginqueue';
	$fields[] = 'incontext';
	$fields[] = 'outcontext';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("groupname");
	$headers[] = $locate->Translate("groupid");
	$headers[] = $locate->Translate("first ring");
	$headers[] = $locate->Translate("allowloginqueue");
	$headers[] = $locate->Translate("incontext");
	$headers[] = $locate->Translate("outcontext");

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="15%"';
	$attribsHeader[] = 'width="15%"';
	$attribsHeader[] = 'width="15%"';
	$attribsHeader[] = 'width="15%"';
	$attribsHeader[] = 'width="15%"';
	$attribsHeader[] = 'width="15%"';

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
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupid","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","firstring","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","allowloginqueue","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","incontext","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","outcontext","'.$divName.'","ORDERING");return false;\'';
	
	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'groupid';
	$fieldsFromSearch[] = 'firstring';
	$fieldsFromSearch[] = 'allowloginqueue';
	$fieldsFromSearch[] = 'incontext';
	$fieldsFromSearch[] = 'outcontext';
	
	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("groupname");
	$fieldsFromSearchShowAs[] = $locate->Translate("groupid");
	$fieldsFromSearchShowAs[] = $locate->Translate("first ring");
	$fieldsFromSearchShowAs[] = $locate->Translate("allowloginqueue");
	$fieldsFromSearchShowAs[] = $locate->Translate("incontext");
	$fieldsFromSearchShowAs[] = $locate->Translate("outcontext");

	//echo 'dddddddddddddd';
	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	//echo 'ggggggggggggg';
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader);
	$table->setAttribsCols($attribsCols);
	$table->exportFlag = '2';//对导出标记进行赋值
	$table->ordering = $ordering;
	$table->addRowSearchMore("accountgroup",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc[] = $row['groupname'];
		$rowc[] = $row['groupid'];
		$rowc[] = $locate->Translate($row['firstring']);
		$rowc[] = $locate->Translate($row['allowloginqueue']);
		$rowc[] = $row['incontext'];
		$rowc[] = $row['outcontext'];
		$table->addRow("group",$rowc,1,1,1,$divName,$fields);
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
	$html = Table::Top($locate->Translate("adding_group"),"formDiv");  // <-- Set the title for your form.
	$html .= Customer::formAdd();  // <-- Change by your method
	// End edit zone
	$html .= Table::Footer();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	
	return $objResponse->getXML();
}

/**
*  save group record
*  @param	f			array		group record
*  @return	objResponse	object		xajax response object
*/

function save($f){
	global $locate,$config;

	$objResponse = new xajaxResponse();
	if(trim($f['groupname']) == '' ){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}
	
	if(!preg_match('/^[\d]*$/',$f['notice_interval'])) {
		$objResponse->addAlert($locate->Translate("notice interval must be integer"));
		return $objResponse->getXML();
	}

//	if (!ereg("[0-9]+",$f['groupid'])){
//		$objResponse->addAlert($locate->Translate("digit_only"));
//		return $objResponse->getXML();
//	}
	$f['billingid'] = 0;
	if($f['addToBilling']){
		if($config['billing']['resellerid'] >0 ){
			$checkreseller = astercrm::getRecordByID($config['billing']['resellerid'],'resellergroup');
			if($checkreseller['id'] == $config['billing']['resellerid']){
				$group = array();
				$group['groupname'] = $f['groupname'];
				$group['creditlimit'] = $config['billing']['groupcreditlimit'];
				$group['limittype'] = $config['billing']['grouplimittype'];
				$group['resellerid'] = $config['billing']['resellerid'];
				$billingid = Customer::insertNewGroupForBilling($group);

				if($billingid > 0){
					$objResponse->addAlert($locate->Translate("add this group to asterbilling success"));
					$f['billingid'] = $billingid;
				}else{
					$objResponse->addAlert($locate->Translate("add this group to asterbilling failed"));					
				}
			}else{
				$objResponse->addAlert($locate->Translate("Reseller id is incorrect, can not add this group to asterbilling"));
			}
		}else{
			$objResponse->addAlert($locate->Translate("Reseller id is incorrect, can not add this group to asterbilling"));
		}
	}

	$res = Customer::insertNewAccountgroup($f); // add a new account
	if($res == 1){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("add_group"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
		$objResponse->addClear("formDiv", "innerHTML");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_insert").":".$respOk->message);
	}
	
	return $objResponse->getXML();
	
}

/**
*  update group record
*  @param	f			array		group record
*  @return	objResponse	object		xajax response object
*/

function update($f){
	global $locate;
	$objResponse = new xajaxResponse();
	if(trim($f['groupname']) == '' ){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}
	if(!preg_match('/^[\d]*$/',$f['notice_interval'])) {
		$objResponse->addAlert($locate->Translate("notice interval must be integer"));
		return $objResponse->getXML();
	}

//	if (!ereg("[0-9]+",$f['groupid'])){
//		$objResponse->addAlert($locate->Translate("digit_only"));
//		return $objResponse->getXML();
//	}

	$respOk = Customer::updateAccountgroupRecord($f);

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

/**
*  show group edit form
*  @param	id			int			group id
*  @return	objResponse	object		xajax response object
*/

function edit($id){
	global $locate;
	$html = Table::Top( $locate->Translate("edit_group"),"formDiv"); 
	$html .= Customer::formEdit($id);
	$html .= Table::Footer();
	// End edit zone

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
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
	$html .= Customer::showAccountgroupDetail($groupid);
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
	if($optionFlag == "export" || $optionFlag == "exportcsv"){
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'astercrm_accountgroup'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("maintable", "value", 'astercrm_accountgroup'); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($type == "delete"){
		$res = Customer::deleteRecord($id,'astercrm_accountgroup');
		$res = Customer::deleteRecords("groupid",$id,'astercrm_account');
		$res = Customer::deleteRecords("groupid",$id,'campaign');
		$res = Customer::deleteRecords("groupid",$id,'diallist');
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
