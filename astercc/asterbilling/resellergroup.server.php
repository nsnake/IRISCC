<?php
/*******************************************************************************
* resellergroup.server.php

* 账户管理系统后台文件
* resellergroup background management script

* Function Desc
	provide resellergroup management script

* 功能描述
	提供帐户管理脚本

* Function Desc
		init				初始化页面元素
		showGrid			显示grid
		createGrid			生成grid的HTML代码
		add					显示添加resellergroup的表单
		save				保存resellergroup信息
		update				更新resellergroup信息
		edit				显示修改resellergroup的表单
		delete				删除resellergroup信息
		showDetail			显示resellergroup详细信息
							当前返回空值
		searchFormSubmit    根据提交的搜索信息重构显示页面

* Revision 0.0456  2007/10/30 13:47:00  last modified by solo
* Desc: modify function showDetail, make it show resellergroup detail when click detail


* Revision 0.045  2007/10/19 10:01:00  last modified by solo
* Desc: modify extensions description

* Revision 0.045  2007/10/18 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('resellergroup.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/asterevent.class.php');
require_once ('include/asterisk.class.php');
require_once ('include/common.class.php');
require_once ("resellergroup.common.php");

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
	global $locate,$db,$config;
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
			$numRows =& Customer::getNumRowsMore($filter, $content,"resellergroup");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"resellergroup");
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
	$fields[] = 'resellername';
	$fields[] = 'accountcode';
	$fields[] = 'callback';
	$fields[] = 'creditlimit';	
	$fields[] = 'limittype';
	$fields[] = 'curcredit';
	$fields[] = 'credit_clid';
	$fields[] = 'credit_group';
	$fields[] = 'credit_reseller';
	$fields[] = 'addtime';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ID").'<br>';
	$headers[] = $locate->Translate("Name").'<br>';
	$headers[] = $locate->Translate("Callback").'<br>';
	$headers[] = $locate->Translate("Credit Limit").'<br>';
	$headers[] = $locate->Translate("Limit Type").'<br>';
	$headers[] = $locate->Translate("Billsec Multiple").'<br>';
	$headers[] = $locate->Translate("Cur credit").'<br>';
	$headers[] = $locate->Translate("Clid Credit").'<br>';
	$headers[] = $locate->Translate("Group Credit").'<br>';
	$headers[] = $locate->Translate("Reseller Credit").'<br>';
	$headers[] = $locate->Translate("Last Update").'<br>';

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
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","resellername","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","allowcallback","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","creditlimit","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","limittype","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","multiple","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","curcredit","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","credit_clid","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","credit_group","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","credit_reseller","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","addtime","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'resellername';
	$fieldsFromSearch[] = 'accountcode';
	$fieldsFromSearch[] = 'callback';
	$fieldsFromSearch[] = 'creditlimit';
	$fieldsFromSearch[] = 'curcredit';
	$fieldsFromSearch[] = 'limittype';
	$fieldsFromSearch[] = 'multiple';
	$fieldsFromSearch[] = 'credit_clid';
	$fieldsFromSearch[] = 'credit_group';
	$fieldsFromSearch[] = 'credit_reseller';
	$fieldsFromSearch[] = 'addtime';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("Reseller Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Account Code");
	$fieldsFromSearchShowAs[] = $locate->Translate("Callback");
	$fieldsFromSearchShowAs[] = $locate->Translate("Credit Limit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Cur Credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Limit Status");
	$fieldsFromSearchShowAs[] = $locate->Translate("Billsec Multiple");
	$fieldsFromSearchShowAs[] = $locate->Translate("Clid Credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group Credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Reseller Credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Last Update");

	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,1,1,0);
	$table->setAttribsCols($attribsCols);

	$table->exportFlag = '1';//对导出标记进行赋值
	$table->addRowSearchMore("resellergroup",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$typeFromSearch,$typeFromSearchShowAs,$stype);

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
		
		$rowc[] = $row['resellername'];
		$rowc[] = $row['allowcallback'];
		$rowc[] = $row['creditlimit'];
		$rowc[] = $row['limittype'];
		$rowc[] = $row['multiple'];
		$rowc[] = $row['curcredit'];
		$rowc[] = $row['credit_clid'];
		$rowc[] = $row['credit_group'];
		$rowc[] = $row['credit_reseller'];
		$rowc[] = $row['addtime'];

		if(!empty($row['limittype']) && (($row['creditlimit'] - $row['curcredit']) < 0 || $row['curcredit'] < 0)){
			$trstyle = 'style="background-color:red;"';
		} else {
			$trstyle = '';
		}
		
		$table->addRow("resellergroup",$rowc,1,1,0,$divName,$fields,$trstyle);
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render();
	$res = Customer::trunkAll();
	$Tr_res = $res->fetchInto($row);
	if(!empty($Tr_res)) {
		//$objResponse->addAssign("msgZone","innerHTML",$locate->Translate("There are new changes"));
		$html .= '<div id="reload"><input type="button" value="'.$locate->Translate("reload").'" onclick="xajax_reload();return false;">&nbsp;'.$locate->Translate("There are new changes")."</div>";
	}
 	return $html;
}

function reload(){
	global $locate;
	$objResponse = new xajaxResponse();
	$res = Customer::Reloadfile();
	$content = ";;;This conf is auto generated by astercc, don't modify!\n";
	$register = ";;;This conf is auto generated by astercc, don't modify!\n";
	while($res->fetchInto($row)){
			$content .= "[".$row['trunkidentity']."]\r\n"
						.';trunkname='.$row['trunkname']."\r\n"
						.$row['trunkdetail']."\r\n";
			if($row['registrystring'] != ''){
				$register .= "register=".$row['registrystring']."\r\n";
			}
	}
	$resT = Customer::CreateFile('trunks',$content);
	$resT1 = Customer::CreateFile('registrations',$register);
	if(!empty($resT)) {
		$message = $locate->Translate("reload success");
		$objResponse->addAssign("msgZone", "innerHTML",$message);
		$objResponse->addScript("xajax_reloadSip();");
	} else {
		$message = $locate->Translate("reload failed");
	}
	return $objResponse;
}

function reloadSip(){
	global $locate;
	$objResponse = new xajaxResponse();
	if ($_SESSION['curuser']['usertype'] == 'reseller' || $_SESSION['curuser']['usertype'] == 'admin'){
		$myAsterisk = new Asterisk();
		$myAsterisk->execute("sip reload");
		$objResponse->addAlert($locate->Translate("sip conf reloaded"));
		$objResponse->addAssign('msgZone','innerHTML',' ');
		$objResponse->addRemove('reload');
	}
	return $objResponse;
}

/**
*  generate resellergroup add form HTML code
*  @return	html		string		resellergroup add HTML code
*/

function add(){
   // Edit zone
	global $locate;
	$objResponse = new xajaxResponse();


	$html = Table::Top($locate->Translate("Add reseller"),"formDiv");  // <-- Set the title for your form.
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
//print_r($f);exit;
	if (trim($f['resellername']) == ''){
		$objResponse->addAlert($locate->Translate("please enter the resellername"));
		return $objResponse;
	}

	$f['creditlimit'] = trim($f['creditlimit']);
	if ($f['creditlimit'] == '' or !is_numeric($f['creditlimit'])){
		$f['creditlimit'] = 0;
	}
	//print_r($f);exit;
//	if($f['routetype'] == 'customize') {
//		$f['trunkname'] = trim($f['trunkname']);
//		if($f['trunkname'] == '') {
//			$objResponse->addAlert($locate->Translate("please enter the trunkname"));
//			return $objResponse;
//		}
//		$f['detail'] = trim($f['detail']);
//		if($f['detail'] == '') {
//			$objResponse->addAlert($locate->Translate("please enter the detail"));
//			return $objResponse;
//		}
//		if(trim($f['timeout']) == '') {
//			$f['timeout'] = 0;
//		}
//		$pin = Customer::generateUniquePin(10);
//		$n = array('trunkname'=>$f['trunkname'],'trunkprotocol'=>$f['protocoltype'],'registrystring'=>$f['registrystring'],'detail'=>$f['detail'],'timeout'=>$f['timeout'],'trunkprefix'=>$f['trunkprefix'],'removeprefix'=>$f['removeprefix'],'trunkidentity'=>$pin);
//		$resTrunk = Customer::insertNewTrunk($n);
//		$f['trunk_id'] = $resTrunk;
//	} else if($f['routetype'] == 'default') {
		//$f['trunk_id'] = -1;
//	} else if($f['routetype'] == 'auto'){
		//$f['trunk_id'] = 0;
//	}

	if($f['trunk1_id'] != $f['tmptrunk1id'] && $f['tmptrunk1id'] > 0){
		Customer::deleteRecord($f['tmptrunk1id'],'trunks');
	}
	if($f['trunk2_id'] != $f['tmptrunk2id'] && $f['tmptrunk2id'] > 0){
		Customer::deleteRecord($f['tmptrunk2id'],'trunks');
	}

	if($config['synchronize']['id_autocrement_byset']){
		$local_lastid = astercrm::getLocalLastId('resellergroup');
		$f['id'] = intval($local_lastid+1);
	}
	
	$respOk = Customer::insertNewResellergroup($f); // add a new group
	if ($respOk){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("Add Reseller"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
		$objResponse->addClear("formDiv", "innerHTML");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_insert"));
	}
	
	//generate include file
	if ($_SESSION['curuser']['usertype'] == 'admin'){
		astercc::generateResellerFile();
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
//	if($f['routetype'] == 'customize') {
//		$f['trunkname'] = trim($f['trunkname']);
//		if($f['trunkname'] == '') {
//			$objResponse->addAlert($locate->Translate("please enter the trunkname"));
//			return $objResponse;
//		}
//		$f['detail'] = trim($f['detail']);
//		if($f['detail'] == '') {
//			$objResponse->addAlert($locate->Translate("please enter the detail"));
//			return $objResponse;
//		}
//	}

	if($f['trunk1_id'] != $f['tmptrunk1id'] && $f['tmptrunk1id'] > 0){
		Customer::deleteRecord($f['tmptrunk1id'],'trunks');
	}
	if($f['trunk2_id'] != $f['tmptrunk2id'] && $f['tmptrunk2id'] > 0){
		Customer::deleteRecord($f['tmptrunk2id'],'trunks');
	}

	if(trim($f['timeout']) == ''){
		$f['timeout'] = 0;
	}
	$respOk = Customer::updateResellergroupRecord($f);
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
	$html = Table::Top( $locate->Translate("Edit Reseller"),"formDiv"); 
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
	astercrm::updateField("resellergroup","billingtime",$billingtime,$id);
	astercrm::updateField("resellergroup","addtime",date("Y-m-d H:i:s"),$id);
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
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'resellergroup'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($type == "delete"){
		if($config['synchronize']['delete_by_use_history']){
			$res = Customer::deleteRecordToHistory('resellerid',$id,'clid');
			$res = Customer::deleteRecordToHistory('resellerid',$id,'accountgroup');
			$res = Customer::deleteRecordToHistory('resellerid',$id,'myrate');
			$res = Customer::deleteRecordToHistory('resellerid',$id,'callshoprate');
			$res = Customer::deleteRecordToHistory('resellerid',$id,'resellerrate');
			$res = Customer::deleteRecordToHistory('resellerid',$id,'account');
			
			$res = Customer::deleteTrunk($id,'trunks');

			$res = Customer::deleteRecordToHistory('id',$id,'resellergroup');
		} else {
			$res = Customer::deleteRecords('resellerid',$id,'clid');
			$res = Customer::deleteRecords('resellerid',$id,'accountgroup');
			$res = Customer::deleteRecords('resellerid',$id,'myrate');
			$res = Customer::deleteRecords('resellerid',$id,'callshoprate');
			$res = Customer::deleteRecords('resellerid',$id,'resellerrate');
			$res = Customer::deleteRecords('resellerid',$id,'account');

			$res = Customer::deleteTrunk($id,'trunks');

			$res = Customer::deleteRecord($id,'resellergroup');
		}
		
		if ($res){
			$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "",$searchType);
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec"));
			$objResponse->addClear("msgZone", "innerHTML");
			$objResponse->addAssign($divName, "innerHTML", $html);
		}else{
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
			$objResponse->addClear("msgZone", "innerHTML");
		}
	}else{
		$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField, $divName, "",$searchType);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}
	
	return $objResponse->getXML();
}

function saveTrunk($f){
	//print_r($f);exit;
	global $locate;
	$objResponse = new xajaxResponse();

	if($f['whichtrunk'] == 1){
		$routetype = $f['routetype1'];
		$curtrunkid = $f['tmptrunk1id'];
	}else{
		$routetype = $f['routetype2'];
		$curtrunkid = $f['tmptrunk2id'];
	}
	
	if($routetype == 'customize') {
		$f['trunkname'] = trim($f['trunkname']);
		if($f['trunkname'] == '') {
			$objResponse->addAlert($locate->Translate("please enter the trunkname"));
			return $objResponse;
		}
		$f['detail'] = trim($f['detail']);
		if($f['detail'] == '') {
			$objResponse->addAlert($locate->Translate("please enter the detail"));
			return $objResponse;
		}
		if(trim($f['timeout']) == '') {
			$f['timeout'] = 0;
		}
		$pin = Customer::generateUniquePin(10);
		$n = array('trunkname'=>$f['trunkname'],'trunkprotocol'=>$f['protocoltype'],'registrystring'=>$f['registrystring'],'detail'=>$f['detail'],'timeout'=>$f['timeout'],'trunkprefix'=>$f['trunkprefix'],'removeprefix'=>$f['removeprefix'],'trunkidentity'=>$pin,'trunkorder'=>$f['whichtrunk']);
		if($curtrunkid > 0){
			$n['curtrunkid'] = $curtrunkid;
			$resTrunk = Customer::updateNewTrunk($n);
			if($f['whichtrunk'] == 1){
				$objResponse->addAssign('trunkname1c', "innerHTML", '<a href="javascript:void(null)" onclick="javascript:xajax_trunkdetail(xajax.$(\'tmptrunk1id\').value,1);">'.$f['trunkname'].'</a>&nbsp;<a href="javascript:void(null)" onclick="javascript:deltrunk(\'1\');">'.$locate->Translate("del").'</a>');
			}else{
				$objResponse->addAssign('trunkname2c', "innerHTML", '<a href="javascript:void(null)" onclick="javascript:xajax_trunkdetail(xajax.$(\'tmptrunk2id\').value,2);">'.$f['trunkname'].'</a>&nbsp;<a href="javascript:void(null)" onclick="javascript:deltrunk(\'2\');">'.$locate->Translate("del").'</a>');
			}
		}else{
			$resTrunk = Customer::insertNewTrunk($n);
			if($f['whichtrunk'] == 1){
				$objResponse->addAssign('trunk1_id', "value", $resTrunk);
				$objResponse->addAssign('tmptrunk1id', "value", $resTrunk);
				$objResponse->addAssign('trunkname1c', "innerHTML", '<a href="javascript:void(null)" onclick="javascript:xajax_trunkdetail(xajax.$(\'tmptrunk1id\').value,1);">'.$f['trunkname'].'</a>&nbsp;<a href="javascript:void(null)" onclick="javascript:deltrunk(\'1\');">'.$locate->Translate("del").'</a>');
			}else{
				$objResponse->addAssign('trunk2_id', "value", $resTrunk);
				$objResponse->addAssign('tmptrunk2id', "value", $resTrunk);
				$objResponse->addAssign('trunkname2c', "innerHTML", '<a href="javascript:void(null)" onclick="javascript:xajax_trunkdetail(xajax.$(\'tmptrunk2id\').value,2);">'.$f['trunkname'].'</a>&nbsp;<a href="javascript:void(null)" onclick="javascript:deltrunk(\'2\');">'.$locate->Translate("del").'</a>');
			}
			$f['trunk_id'] = $resTrunk;
		}
	} else if($routetype == 'default') {
		$f['trunk_id'] = -1;
	} else if($routetype == 'auto'){
		$f['trunk_id'] = 0;
	}
	
	$objResponse->addScript('document.getElementById(\'savetrunktip\').style.display=\'\'');
	$objResponse->addScript('setTimeout("document.getElementById(\'trunk\').style.display=\'none\';document.getElementById(\'savetrunktip\').style.display=\'none\';",1500);');
	return $objResponse;
}

function trunkdetail($trunkid,$order){
	global $db,$locate;
	$trunk = & Customer::getRecordByID($trunkid,'trunks');
	$objResponse = new xajaxResponse();

	if($trunk['id'] > 0){
		$objResponse->addAssign('trunkname', "value", $trunk['trunkname']);
		$objResponse->addAssign('protocoltype', "value", $trunk['trunkprotocol']);
		$objResponse->addAssign('registrystring', "value", $trunk['registrystring']);
		$objResponse->addAssign('trunkprefix', "value", $trunk['trunkprefix']);
		$objResponse->addAssign('removeprefix', "value", $trunk['removeprefix']);
		$objResponse->addAssign('timeout', "value", $trunk['trunktimeout']);
		$objResponse->addAssign('detail', "value", $trunk['trunkdetail']);
	}
	//print_r($trunk);
	//echo $trunkid;exit;
	if($order == 1){
		$objResponse->addAssign('whichtrunk', "value", 1);
		$objResponse->addAssign('whichtrunktip', "innerHTML", $locate->Translate("trunk1"));
	}else{
		$objResponse->addAssign('whichtrunk', "value", 2);
		$objResponse->addAssign('whichtrunktip', "innerHTML", $locate->Translate("trunk2"));
	}
	$objResponse->addScript('document.getElementById(\'trunk\').style.display=\'\'');
	return $objResponse;
}

function delTrunk($trunkid,$order,$rid=0){
	global $db;
	//echo $rid;exit;
	$objResponse = new xajaxResponse();
	$trunk = & Customer::deleteRecord($trunkid,'trunks');
	if($order == 1){
		$objResponse->addAssign('trunkname1c', "innerHTML",'');
		$objResponse->addAssign('routetype1', "value",'auto');
		$objResponse->addAssign('trunk1_id', "value", 0);
		$objResponse->addAssign('tmptrunk1id', "value", 0);
		if($rid > 0){
			$query = "UPDATE resellergroup SET trunk1_id = '0' WHERE id='$rid'";
			$res =& $db->query($query);
		}
	}else{
		$objResponse->addAssign('trunkname2c', "innerHTML",'');
		$objResponse->addAssign('routetype2', "value",'auto');
		$objResponse->addAssign('trunk2_id', "value", 0);
		$objResponse->addAssign('tmptrunk2id', "value", 0);
		if($rid > 0){
			$query = "UPDATE resellergroup SET trunk2_id = '0' WHERE id='$rid'";
			$res =& $db->query($query);
		}
	}
	//print_r($trunk);
	//echo $trunkid;exit;
	$objResponse->addScript('document.getElementById(\'trunk\').style.display=\'none\'');
	return $objResponse;
}

$xajax->processRequests();
?>
