<?php
/*******************************************************************************
* accountgroup.server.php

* 账户管理系统后台文件
* accountgroup background management script

* Function Desc
	provide accountgroup management script

* 功能描述
	提供帐户管理脚本

* Function Desc
		init				初始化页面元素
		showGrid			显示grid
		createGrid			生成grid的HTML代码
		add					显示添加accountgroup的表单
		save				保存accountgroup信息
		update				更新accountgroup信息
		edit				显示修改accountgroup的表单
		delete				删除accountgroup信息
		showDetail			显示accountgroup详细信息
							当前返回空值
		searchFormSubmit    根据提交的搜索信息重构显示页面
		updateBillingtime	

* Revision 0.0456  2007/10/30 13:47:00  last modified by solo
* Desc: modify function showDetail, make it show accountgroup detail when click detail


* Revision 0.045  2007/10/19 10:01:00  last modified by solo
* Desc: modify extensions description

* Revision 0.045  2007/10/18 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('accountgroup.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/asterevent.class.php');
require_once ('include/common.class.php');
require_once ("accountgroup.common.php");

/**
*  initialize page elements
*
*/

function init(){
	global $locate;

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin));
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
	global $locate,$config;
	$_SESSION['ordering'] = $ordering;
	
	if($filter == null or $content == null || (!is_array($content) && $content == 'Array') || (!is_array(filter) && $filter == 'Array')){
		$content = null;
		$filter = null;
		$numRows =& Customer::getNumRows();
		$arreglo =& Customer::getAllRecords($start,$limit,$order);
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
			$order = null;
			$numRows =& Customer::getNumRows();
			$arreglo =& Customer::getAllRecords($start,$limit,$order);
		}elseif($flag3 != 1 ){
			$order = "id";
			$numRows =& Customer::getNumRowsMore($filter, $content,"accountgroup");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"accountgroup");
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
	$fields[] = 'id';
	$fields[] = 'groupname';
	$fields[] = 'resellername';
	$fields[] = 'accountcode';
	$fields[] = 'callback';
	$fields[] = 'creditlimit';
	$fields[] = 'limittype';
	$fields[] = 'curcredit';
	$fields[] = 'credit_clid';
	$fields[] = 'credit_group';
	$fields[] = 'credit_reseller';
	$fields[] = 'group_multiple';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ID").'<br/>';
	$headers[] = $locate->Translate("Name").'<br/>';
	$headers[] = $locate->Translate("Reseller").'<br/>';
	$headers[] = $locate->Translate("Callback").'<br/>';
	$headers[] = $locate->Translate("Credit Limit").'<br/>';
	$headers[] = $locate->Translate("Limit Type").'<br/>';
	$headers[] = $locate->Translate("Cur Credit").'<br/>';
	$headers[] = $locate->Translate("Clid Credit").'<br/>';
	$headers[] = $locate->Translate("Group Credit").'<br/>';
	$headers[] = $locate->Translate("Reseller Credit").'<br/>';
	$headers[] = $locate->Translate("Group Billsec Multiple").'<br/>';

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

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","id","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","resellername","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","allowcallback","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","creditlimit","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","limittype","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","curcredit","'.$divName.'","ORDERING");return false;\'';;
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","credit_clid","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","credit_group","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","credit_reseller","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","group_multiple","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'resellername';
	$fieldsFromSearch[] = 'accountgroup.accountcode';
	$fieldsFromSearch[] = 'accountgroup.allowcallback';
	$fieldsFromSearch[] = 'accountgroup.creditlimit';
	$fieldsFromSearch[] = 'accountgroup.limittype';
	$fieldsFromSearch[] = 'accountgroup.curcredit';
	$fieldsFromSearch[] = 'accountgroup.credit_clid';
	$fieldsFromSearch[] = 'accountgroup.credit_group';
	$fieldsFromSearch[] = 'accountgroup.credit_reseller';
	$fieldsFromSearch[] = 'accountgroup.group_multiple';
	$fieldsFromSearch[] = 'accountgroup.customer_multiple';
	$fieldsFromSearch[] = 'accountgroup.addtime';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("Group Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Reseller Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Account Code");
	$fieldsFromSearchShowAs[] = $locate->Translate("Callback");
	$fieldsFromSearchShowAs[] = $locate->Translate("Credit Limit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Limit Type");
	$fieldsFromSearchShowAs[] = $locate->Translate("Cur Credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Clid Credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group Credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Reseller Credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group Billsec Multiple");
	$fieldsFromSearchShowAs[] = $locate->Translate("Customer Billsec Multiple");
	$fieldsFromSearchShowAs[] = $locate->Translate("Last Update");

	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,1,1,0);
	$table->setAttribsCols($attribsCols);
	$table->exportFlag = '1';//对导出标记进行赋值

	$table->addRowSearchMore("accountgroup",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$typeFromSearch,$typeFromSearchShowAs,$stype);

	if($config['synchronize']['display_synchron_server']){
		$otherHost = $config['synchronize_host']['Host'];
		$hostArray = explode(',',trim($otherHost,','));
	}
	
	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		
		if($config['synchronize']['display_synchron_server']){
			$existFlag = false;
			foreach($hostArray as $tmp){
				if($row['id'] >= $config['synchronize_host'][$tmp.'_minId'] && $row['id'] <= $config['synchronize_host'][$tmp.'_maxId']){
					$rowc[] = $row['id'].'('.$config['synchronize_host'][$tmp].')';
					$existFlag = true;
				}
			}
			if(!$existFlag){
				$rowc[] = $row['id'].'('.$locate->Translate("Local").')';
			}
		} else {
			$rowc[] = $row['id'];
		}
		
		$rowc[] = $row['groupname'];
		$rowc[] = $row['resellername'];
		$rowc[] = $row['allowcallback'];
		$rowc[] = $row['creditlimit'];
		$rowc[] = $row['limittype'];
		$rowc[] = $row['curcredit'];
		$rowc[] = $row['credit_clid'];
		$rowc[] = $row['credit_group'];
		$rowc[] = $row['credit_reseller'];
			//astercc::readAmount($row['id'],null,$row['billingtime'],null,'callshopcredit');
		$rowc[] = $row['group_multiple'];

		if(!empty($row['limittype']) && ($row['creditlimit'] - $row['curcredit']) < 0 ){//|| $row['curcredit'] < 0)
			$trstyle = 'style="background-color:red;"';
		} else {
			$trstyle = '';
		}
		$table->addRow("accountgroup",$rowc,1,1,0,$divName,$fields,$trstyle);
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render();
 	return $html;
}

/**
*  generate accountgroup add form HTML code
*  @return	html		string		accountgroup add HTML code
*/

function add(){
   // Edit zone
	global $locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("Add group"),"formDiv");  // <-- Set the title for your form.
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
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();
	
	if (trim($f['groupname']) == '' || trim($f['resellerid']) == 0){
		$objResponse->addAlert('*'.$locate->Translate("obligatory fields"));
		return $objResponse;
	}
	
	if($config['synchronize']['id_autocrement_byset']){
		$local_lastid = astercrm::getLocalLastId('accountgroup');
		$f['id'] = intval($local_lastid+1);
	}

	$f['creditlimit'] = trim($f['creditlimit']);
	if ($f['creditlimit'] == '' or !is_numeric($f['creditlimit'])){
		$f['creditlimit'] = 0;
	}

	$id = astercrm::checkValues("accountgroup","groupname",$f['groupname']);

	if($id != ''){
		$objResponse->addAlert($locate->Translate("Groupname Duplicate"));
		return $objResponse->getXML();
	}

	$respOk = Customer::insertNewAccountgroup($f); // add a new group
	if ($respOk){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("add_group"));
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
	global $locate;
	$objResponse = new xajaxResponse();

	if (trim($f['groupname']) == '' || trim($f['resellerid']) == 0){
		$objResponse->addAlert('*'.$locate->Translate("obligatory fields"));
		return $objResponse;
	}

	$respOk = Customer::updateAccountgroupRecord($f);

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
	$html .= Customer::showGroupDetail($groupid);
	$html .= Table::Footer();

	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	return $objResponse;
}

function updateBillingtime($id,$billingtime){
	$objResponse = new xajaxResponse();
	global $locate;
	astercrm::updateField("accountgroup","billingtime",$billingtime,$id);
	astercrm::updateField("accountgroup","addtime",date("Y-m-d H:i:s"),$id);
	$objResponse->addScript("xajax_edit('".$id."')");
	return $objResponse;
}

function searchFormSubmit($searchFormValue,$numRows,$limit,$id,$type){
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];			//搜索方式 数组
	$divName = "grid";
	if($optionFlag == "export"){
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'accountgroup'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($type == "delete"){
		if($config['synchronize']['delete_by_use_history']){
			$res = Customer::deleteRecordToHistory('groupid',$id,'clid');
			$res = Customer::deleteRecordToHistory('groupid',$id,'myrate');
			$res = Customer::deleteRecordToHistory('groupid',$id,'callshoprate');
			$res = Customer::deleteRecordToHistory('groupid',$id,'account');
			$res = Customer::deleteRecordToHistory('id',$id,'accountgroup');
		} else {
			$res = Customer::deleteRecords('groupid',$id,'clid');
			$res = Customer::deleteRecords('groupid',$id,'myrate');
			$res = Customer::deleteRecords('groupid',$id,'callshoprate');
			$res = Customer::deleteRecords('groupid',$id,'account');
			$res = Customer::deleteRecord($id,'accountgroup');
		}

		if ($res){
			$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "",$searchType);
			$objResponse = new xajaxResponse();
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec"));
			$objResponse->addClear("msgZone", "innerHTML");
			$objResponse->addAssign($divName, "innerHTML", $html);
		}else{
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
		}
	}else{
		$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField, $divName, "",$searchType);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}	
	return $objResponse->getXML();
}


function resetGroup($groupId){
	global $locate;
	$objResponse = new xajaxResponse();

	$res = Customer::resetGroup($groupId);
	if ($res){
		$objResponse->addAlert($locate->Translate("reset_group_success"));
	}else{
		$objResponse->addAlert($locate->Translate("reset_group_failed")); 
	}
	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','')");
	return $objResponse->getXML();
}

$xajax->processRequests();
?>
