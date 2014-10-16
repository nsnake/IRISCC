<?php
/*******************************************************************************
* rate.server.php


* Function Desc

* 功能描述

* Function Desc


* Revision 0.01  2007/11/21 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('rate.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');
require_once ('include/asterevent.class.php');
require_once ("rate.common.php");

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
	if ($_SESSION['curuser']['usertype'] == "groupadmin") {
		$row = astercrm::getRecordById($_SESSION['curuser']['groupid'],"accountgroup");
		$objResponse->addAssign("customer_multiple","value", $row['customer_multiple']);
		$objResponse->addAssign("spnShortcutUpdate","innerHTML", '<input type="button" value="'.$locate->Translate("Shortcut update rate").'" onclick="xajax_shortcutUpdate();">');
		$objResponse->addAssign("spnShortcutMsg","innerHTML", '');

	}

	return $objResponse;
}

function updateCustomerMultiple($val){
	global $db,$locate;
	$objResponse = new xajaxResponse();
	$query = "UPDATE accountgroup SET customer_multiple = '$val' , addtime = now() WHERE id = ".$_SESSION['curuser']['groupid'];
	$db->query($query);
	$objResponse->addAlert($locate->Translate("Customer Billsec Mutiple updated"));
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

function createGrid($start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "", $exportFlag="", $deleteFlag = "",$stype=array()){
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
			$numRows =& Customer::getNumRowsMore($filter, $content,"myrate");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"myrate");
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
	if($config['synchronize']['display_synchron_server']){
		$fields[] = 'id';
	}
	$fields[] = 'dialprefix';
	$fields[] = 'numlen';
	$fields[] = 'destination';
	$fields[] = 'connectcharge';
	$fields[] = 'initblock';
	$fields[] = 'rateinitial';
	$fields[] = 'billingblock';
	$fields[] = 'groupname';
	$fields[] = 'resellername';
	$fields[] = 'addtime';

	// HTML table: Headers showed
	$headers = array();
	if($config['synchronize']['display_synchron_server']){
		$headers[] = $locate->Translate("Id").'<br>';
	}
	$headers[] = $locate->Translate("Prefix").'<br>';
	$headers[] = $locate->Translate("Length").'<br>';
	$headers[] = $locate->Translate("Destination").'<br>';
	$headers[] = $locate->Translate("Connect Charge").'<br>';
	$headers[] = $locate->Translate("Init Block").'<br>';
	$headers[] = $locate->Translate("Rate").'<br>';
	$headers[] = $locate->Translate("Billing Block").'<br>';
	$headers[] = $locate->Translate("Group").'<br>';
	$headers[] = $locate->Translate("Reseller").'<br>';
	$headers[] = $locate->Translate("Addtime").'<br>';

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
	if($config['synchronize']['display_synchron_server']){
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","id","'.$divName.'","ORDERING");return false;\'';
	}
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","dialprefix","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","numlen","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","destination","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","connectcharge","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","initblock","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","rateinitial","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","billingblock","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","resellername","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","addtime","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'dialprefix';
	$fieldsFromSearch[] = 'numlen';
	$fieldsFromSearch[] = 'destination';
	$fieldsFromSearch[] = 'rateinitial';
	$fieldsFromSearch[] = 'initblock';
	$fieldsFromSearch[] = 'billingblock';
	$fieldsFromSearch[] = 'connectcharge';
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'resellername';
	$fieldsFromSearch[] = 'myrate.addtime';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("Prefix");
	$fieldsFromSearchShowAs[] = $locate->Translate("Length");
	$fieldsFromSearchShowAs[] = $locate->Translate("Destination");
	$fieldsFromSearchShowAs[] = $locate->Translate("Rate");
	$fieldsFromSearchShowAs[] = $locate->Translate("Init block");
	$fieldsFromSearchShowAs[] = $locate->Translate("Billing block");
	$fieldsFromSearchShowAs[] = $locate->Translate("Connect charge");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group");
	$fieldsFromSearchShowAs[] = $locate->Translate("Reseller");
	$fieldsFromSearchShowAs[] = $locate->Translate("Addtime");

	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller' || $_SESSION['curuser']['usertype'] == 'groupadmin'){
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,1,1,0);
		$table->deleteFlag = '1';//对删除标记进行赋值
		$table->multiEditFlag = '1';//对批量修改标记进行赋值
		$table->exportFlag = '1';//对导出标记进行赋值
	}else{
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,0,0,0);
	}


	$table->setAttribsCols($attribsCols);
	

	if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller' || $_SESSION['curuser']['usertype'] == 'groupadmin')
		$table->addRowSearchMore("myrate",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$typeFromSearch,$typeFromSearchShowAs,$stype);
	else
		$table->addRowSearchMore("myrate",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$typeFromSearch,$typeFromSearchShowAs,$stype);
	

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
		}
		$rowc[] = $row['dialprefix'];
		$rowc[] = $row['numlen'];
		$rowc[] = $row['destination'];
		$rowc[] = $row['connectcharge'];
		$rowc[] = $row['initblock'];
		$rowc[] = $row['rateinitial'];
		$rowc[] = $row['billingblock'];
		$rowc[] = $row['groupname'];
		$rowc[] = $row['resellername'];
		$rowc[] = $row['addtime'];
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller' || $_SESSION['curuser']['usertype'] == 'groupadmin'){
			if ( $_SESSION['curuser']['usertype'] == 'reseller' && $row['resellerid'] != $_SESSION['curuser']['resellerid'] ){
				$table->addRow("myrate",$rowc,0,0,0,$divName,$fields);
			}else if($_SESSION['curuser']['usertype'] == 'groupadmin' && $row['groupid'] != $_SESSION['curuser']['groupid'] ){
				$table->addRow("myrate",$rowc,0,0,0,$divName,$fields);
			}else{
				$table->addRow("myrate",$rowc,1,1,0,$divName,$fields);
			}
		}else
			$table->addRow("myrate",$rowc,0,0,0,$divName,$fields);
		}
 	
 	// End Editable Zone
 	
 	$html = $table->render();
 	
 	return $html;
}

function setGroup($resellerid){
	global $locate,$config;
	$objResponse = new xajaxResponse();
	if($resellerid != '' ){
		$res = astercrm::getAll("accountgroup",'resellerid',$resellerid);
		$objResponse->addScript("addOption('groupid','0','"."All"."');");
		//添加option
		while ($res->fetchInto($row)) {
			if($config['synchronize']['display_synchron_server']){
				$row['groupname'] = astercrm::getSynchronDisplay($row['id'],$row['groupname']);
			}

			$objResponse->addScript("addOption('groupid','".$row['id']."','".$row['groupname']."');");
		}
	}else{
		$objResponse->addScript("addOption('groupid','','');");
	}
	return $objResponse;
}

/**
*  generate account add form HTML code
*  @return	html		string		account add HTML code
*/

function add(){
   // Edit zone
	global $locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("Add Rate"),"formDiv");  // <-- Set the title for your form.
	$html .= Customer::formAdd();  // <-- Change by your method
	// End edit zone
	$html .= Table::Footer();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	
	return $objResponse->getXML();
}

/**
*  save account record
*  @param	f			array		account record
*  @return	objResponse	object		xajax response object
*/

function save($f){
	global $locate,$config;
	$objResponse = new xajaxResponse();

	if(!isset($f['groupid'])) $f['groupid'] = $_SESSION['curuser']['groupid'];
	if(!isset($f['resellerid'])) $f['resellerid'] = $_SESSION['curuser']['resellerid'];
	
	// check if clid duplicate
	$res = astercrm::checkRateDuplicate("myrate",$f,"insert");
	if ($res != ''){
		$objResponse->addAlert($locate->Translate("rate duplicate"));
		return $objResponse->getXML();
	}

	if($config['synchronize']['id_autocrement_byset']){
		$local_lastid = astercrm::getLocalLastId('myrate');
		$f['id'] = intval($local_lastid+1);
	}

	$respOk = Customer::insertNewRate($f); // add a new rate
	if ($respOk){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rate added"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
		$objResponse->addClear("formDiv", "innerHTML");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("can not insert rate"));
	}
	return $objResponse->getXML();
	
}

/**
*  update account record
*  @param	f			array		account record
*  @return	objResponse	object		xajax response object
*/

function update($f){
	global $locate;
	$objResponse = new xajaxResponse();
	if(!isset($f['groupid'])) $f['groupid'] = $_SESSION['curuser']['groupid'];
	if(!isset($f['resellerid'])) $f['resellerid'] = $_SESSION['curuser']['resellerid'];
	$res = astercrm::checkRateDuplicate("myrate",$f,"update");
	if ($res != ''){
		$objResponse->addAlert($locate->Translate("rate duplicate"));
		return $objResponse->getXML();
	}

	$respOk = Customer::updateRateRecord($f);

	if($respOk){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("Rate Updated"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("record cannot be updated"));
	}
	
	return $objResponse->getXML();
}

/**
*  show account edit form
*  @param	id			int			account id
*  @return	objResponse	object		xajax response object
*/

function edit($id){
	global $locate;
	$html = Table::Top( $locate->Translate("edit rate"),"formDiv"); 
	$html .= Customer::formEdit($id);
	$html .= Table::Footer();
	// End edit zone

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	return $objResponse->getXML();
}

/**
*  show account record detail
*  @param	accountid	int			account id
*  @return	objResponse	object		xajax response object
*/

function showDetail($accountid){
	$objResponse = new xajaxResponse();
	global $locate;
	$html = Table::Top( $locate->Translate("rate detail"),"formDiv"); 
	$html .= Customer::showAccountDetail($accountid);
	$html .= Table::Footer();

	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	return $objResponse;
}

function searchFormSubmit($searchFormValue,$numRows,$limit,$id,$type){
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$deleteFlag = $searchFormValue['deleteFlag'];
	$exportFlag = $searchFormValue['exportFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];			//搜索方式 数组
	$divName = "grid";
	if($optionFlag == "export"){
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'myrate'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($optionFlag == "delete"){
		if(empty($_SESSION['curuser']['usertype'])){
			$objResponse->addAlert($locate->Translate("Session time out,please try again"));
			return $objResponse->getXML();
		}
		if($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$searchContent[] = $_SESSION['curuser']['groupid'];
			$searchField[] = 'groupid';
			$searchType[] = 'equal';
		}elseif($_SESSION['curuser']['usertype'] == 'reseller'){
			$searchContent[] = $_SESSION['curuser']['resellerid'];
			$searchField[] = 'resellerid';
			$searchType[] = 'equal';
		}
		
		if($config['synchronize']['delete_by_use_history']){
			astercrm::deleteToHistoryFromSearch($searchContent,$searchField,$searchType,'myrate');
		} else {
			astercrm::deletefromsearch($searchContent,$searchField,$searchType,'myrate');
		}
		
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','','',$divName,"",1,1,'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}elseif($optionFlag == "multiEdit"){
		$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField, $divName, "",1,1,$searchType);
		$showMutiEdit = Table::Top($locate->Translate("Multi Edit"),"formDiv");
		$showMutiEdit .= astercrm::formMutiEdit($searchContent,$searchField,$searchType,'myrate');
		$showMutiEdit .= Table::Footer();
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
		$objResponse->addAssign('formDiv', "innerHTML", $showMutiEdit);
		$objResponse->addAssign('formDiv', "style.visibility", 'visible');
	}else{
		if($type == "delete"){
			if(empty($_SESSION['curuser']['usertype'])){
				$objResponse->addAlert($locate->Translate("Session time out,please try again"));
				return $objResponse->getXML();
			}

			if($config['synchronize']['delete_by_use_history']){
				$res = astercrm::deleteRecordToHistory('id',$id,'myrate');
			} else {
				$res = Customer::deleteRecord($id,'myrate');
			}
			
			if ($res){
				$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "",1,1,$searchType);
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("record deleted")); 
			}else{
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("record cannot be deleted")); 
			}
		}else{
			$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField, $divName, "",1,1,$searchType);
		}
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}
	return $objResponse->getXML();
}

function multiEditUpdate($searchContent = array(),$searchField = array(),$searchType = array(),$table,$f){
	global $db,$locate;

	$objResponse = new xajaxResponse();

	$searchContent = split(',',$searchContent);
	$searchField = split(',',$searchField);
	$searchType = split(',',$searchType);
	$i = 0;

	foreach($searchField as $field){

		if(trim($searchType[$i]) != '' && trim($searchContent[$i]) != ''){

			if($field == 'resellername' && trim($searchType[$i]) != '' && trim($searchContent[$i]) != ''){

				if(trim($searchType[$i]) == "like" ){
					$whereResellername .= "AND resellername LIKE '%".$searchContent[$i]."%' ";
				}elseif(trim($searchType[$i]) == "equal" ){
					$whereResellername .= "AND resellername = '".$searchContent[$i]."' ";
				}elseif(trim($searchType[$i]) == "more" ){
					$whereResellername .= "AND resellername > '".$searchContent[$i]."' ";
				}elseif(trim($searchType[$i]) == "less" ){
					$whereResellername .= "AND resellername < '".$searchContent[$i]."' ";
				}
				$searchField[$i] = '';

			}elseif( $field == 'groupname' ){

				if(trim($searchType[$i]) == "like" ){
					$whereGroupname .= "AND groupname like '%".$searchContent[$i]."%' ";
				}elseif(trim($searchType[$i]) == "equal" ){
					$whereGroupname .= "AND groupname = '".$searchContent[$i]."' ";
				}elseif(trim($searchType[$i]) == "more" ){
					$whereGroupname .= "AND groupname > '".$searchContent[$i]."' ";
				}elseif(trim($searchType[$i]) == "less" ){
					$whereGroupname .= "AND groupname < '".$searchContent[$i]."' ";
				}
				$searchField[$i] = '';
			}
			$i++;
		}
	}

	if($whereResellername != ''){
		$sql = "SELECT id FROM resellergroup WHERE 1 ".$whereResellername;
		$resellerRes =& $db->query($sql);
		while($resellerRes->fetchInto($row)){
			$resellerJoinStr .= "AND resellerid = ".$row['id']." ";
		}
	}

	if($whereGroupname != ''){
		$sql = "SELECT id FROM accountgroup WHERE 1 ".$whereGroupname;
		$groupRes =& $db->query($sql);
		while($groupRes->fetchInto($row)){
			$groupJoinStr .= "AND groupid = ".$row['id']." ";
		}
	}

	$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType);
	
	list($field,$fieldType) = split(',',$f['multieditField']);

	$sucessNum = 0;

	if($f['multioption'] == 'modify'){

		$query = "SELECT id,".trim($field)." FROM ".$table." WHERE 1 ".$joinstr.$groupJoinStr.$resellerJoinStr;
		if($_SESSION['curuser']['usertype'] == 'reseller'){
			$query .= " AND resellerid =".$f['resellerid']." ";
		}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$query .= " AND groupid =".$f['groupid']." ";
		}

		astercrm::events($query);
		$res = $db->query($query);
			
		while($res->fetchInto($row)){
			if($f['multieditcontent'] != ''){
				if($f['multieditType'] == 'to'){
					if($fieldType == 'int' || $fieldType == 'real'){
						if(!is_numeric($f['multieditcontent'])){
							$objResponse->addAlert($locate->Translate("Must fill number in blank for field").":".$field);
							return $objResponse;
						}
					}

					$newValue = $f['multieditcontent'];

				}else{
					if(!is_numeric($f['multieditcontent'])){
						$objResponse->addAlert($locate->Translate("Must fill number in blank for field").":".$field);
						return $objResponse;
					}
					if($f['multieditType'] == 'plus'){
						$newValue = $row[$field] + $f['multieditcontent'];
					}elseif($f['multieditType'] == 'minus'){
						$newValue = $row[$field] - $f['multieditcontent'];
					}elseif($f['multieditType'] == 'multiply'){
						$newValue = $row[$field] * $f['multieditcontent'];
					}
				}
				
				$updateSql = "UPDATE ".$table." SET ".trim($field)." = '".$newValue."'";
				
				if( $f['resellerid'] != '' ){
					if($_SESSION['curuser']['usertype'] != 'reseller' ){
						 $updateSql .= ", resellerid =".$f['resellerid'].", groupid =".$f['groupid'];
					}elseif($f['groupid'] !=''){
						$updateSql .= ", resellerid =".$f['resellerid'].", groupid =".$f['groupid'];
					}
				}
					$updateSql .= ",addtime = now() WHERE id = ".$row['id'];
			}else{
				if( $f['resellerid'] != '' ){
					if($_SESSION['curuser']['usertype'] != 'reseller' ){
						$updateSql ="UPDATE ".$table." SET resellerid =".$f['resellerid'].", groupid =".$f['groupid'].",addtime = now() WHERE id = ".$row['id'];
					}elseif($f['groupid'] !=''){
						$updateSql .= ", resellerid =".$f['resellerid'].", groupid =".$f['groupid'];
					}else{
						$objResponse->addAlert($locate->Translate("No Option"));
						return $objResponse;
					}
				}else{
					$objResponse->addAlert($locate->Translate("No Option"));
					return $objResponse;
				}
			}

			astercrm::events($updateSql);
			$updateRes = $db->query($updateSql);
			if($updateRes === 1) $sucessNum++;
		}
		
		$objResponse->addAlert($sucessNum.$locate->Translate("records have been changed"));

	}elseif($f['multioption'] == 'duplicate'){

		$query = "SELECT * FROM ".$table." WHERE 1 ".$joinstr.$groupJoinStr.$resellerJoinStr;
		astercrm::events($query);
		$res =& $db->query($query);

		while($res->fetchInto($row)){
			$insertField = '';
			$insertValue = '';
			if($f['multieditcontent'] != ''){
				if($f['multieditType'] == 'to'){
					if($fieldType == 'int' || $fieldType == 'real'){
						if(!is_numeric($f['multieditcontent'])){
							$objResponse->addAlert($locate->Translate("Must fill number in blank for field").":".$field);
							return $objResponse;
						}
					}

					$newValue = $f['multieditcontent'];

				}else{
					if(!is_numeric($f['multieditcontent'])){
						$objResponse->addAlert($locate->Translate("Must fill number in blank for field").":".$field);
						return $objResponse;
					}
					if($f['multieditType'] == 'plus'){
						$newValue = $row[$field] + $f['multieditcontent'];
					}elseif($f['multieditType'] == 'minus'){
						$newValue = $row[$field] - $f['multieditcontent'];
					}elseif($f['multieditType'] == 'multiply'){
						$newValue = $row[$field] * $f['multieditcontent'];
					}
				}
				foreach($row as $key => $value){
					if(!preg_match("/id$/",$key) && $key != 'addtime' ){
						$insertField .= $key.",";
						if($key != $field)
							$insertValue .= "'".$value."',";
						else
							$insertValue .= "'".$newValue."',";
					}
				}
				
				$insertField .= "resellerid,groupid";
				
				if( $f['resellerid'] != '' ){
					if($_SESSION['curuser']['usertype'] == 'reseller' ){
						if($f['groupid'] !=''){
							$insertValue .= $f['resellerid'].",".$f['groupid'];
						}else{
							$insertValue .= $row['resellerid'].",".$row['groupid'];
						}
					}else{
						$insertValue .= $f['resellerid'].",".$f['groupid'];
					}
				}else{
					$insertValue .= $row['resellerid'].",".$row['groupid'];
				}		
			
			}else{
				foreach($row as $key => $value){
					if(!preg_match("/id$/",$key) && $key != 'addtime' ){
						$insertField .= $key.",";
						$insertValue .= "'".$value."',";						
					}
				}

				if( $f['resellerid'] != '' ){
					$insertField .= " resellerid,groupid";
					if($_SESSION['curuser']['usertype'] == 'reseller' ){
						if($f['groupid'] !=''){
							$insertValue .= $f['resellerid'].",".$f['groupid'];
						}else{
							$objResponse->addAlert($locate->Translate("No Option"));
							return $objResponse;
						}
					}else{
						$insertValue .= $f['resellerid'].",".$f['groupid'];
					}
	
				}else{
					$objResponse->addAlert($locate->Translate("No Option"));
					return $objResponse;
				}
			}

			$insertField = "(".$insertField.",addtime)";
			$insertValue = "(".$insertValue.",now())";

			$insertSql = "INSERT INTO ".$table." ".$insertField." VALUES ".$insertValue;

			astercrm::events($insertSql);
			$insertRes = $db->query($insertSql);			
			if($insertRes === 1) $sucessNum++;
		}
		$objResponse->addAlert($sucessNum.$locate->Translate("records have been added"));		
	}

	$html = createGrid(0, 25,$searchField, $searchContent, $searchField, 'grid', "",1,1,$searchType);

	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign('grid', "innerHTML", $html);
	return $objResponse;
}

function setMultieditType($fields){
	global $locate;

	$objResponse = new xajaxResponse();
	list($field,$type) = split(',',$fields);
	if($type == 'int' || $type == 'real'){
		$objResponse->assign("multieditType","options.length",'0');
		$objResponse->addScript("addOption('multieditType','to','".$locate->Translate("to")."');");
		$objResponse->addScript("addOption('multieditType','plus','".$locate->Translate("plus")."');");
		$objResponse->addScript("addOption('multieditType','minus','".$locate->Translate("minus")."');");
		$objResponse->addScript("addOption('multieditType','multiply','".$locate->Translate("multiply")."');");		
	}else{
		$objResponse->assign("multieditType","options.length",'0');
		$objResponse->addScript("addOption('multieditType','to','".$locate->Translate("to")."');");
	}

	return $objResponse;
}

function showBuyRate($prefix){
	global $locate;
	$objResponse = new xajaxResponse();
	//echo $prefix;exit;

	if($_SESSION['curuser']['usertype'] == 'groupadmin' ) {
		$buyrate = astercc::searchRate($prefix,$_SESSION['curuser']['groupid'],$_SESSION['curuser']['resellerid'], 'callshoprate',"prefix");
		if($buyrate['id'] != ''){
			$buyrateDesc = astercc::readRateDesc($buyrate);
			$objResponse->assign("spanShowBuyRate","innerHTML",$locate->Translate("Buy Rate").":".$buyrate['destination']."(".$buyrateDesc.")");
		}else{
			$objResponse->assign("spanShowBuyRate","innerHTML","");
		}
	}
	return $objResponse;
}

function shortcutUpdate(){
	global $locate;
	$objResponse = new xajaxResponse();

	if($_SESSION['curuser']['usertype'] = 'groupadmin'){
		$html = Customer::shortUpdateGrid($_SESSION['curuser']['groupid'],$_SESSION['curuser']['resellerid']);
		$objResponse->addAssign('grid', "innerHTML", $html);
		$objResponse->addAssign("spnShortcutUpdate","innerHTML", '<input type="button" value="'.$locate->Translate("Return").'" onclick="init();">');
	}
	return $objResponse;
}

function shortcutUpdateSave($id,$newRate){
	global $locate,$db;

	$objResponse = new xajaxResponse();
	if(!is_numeric($newRate)) return $objResponse;

	$sql = "UPDATE myrate SET rateinitial = $newRate WHERE id = $id";
	$res = $db->query($sql);
	if($res == 1 ) 
		$objResponse->addAssign("spnShortcutMsg","innerHTML", $locate->Translate("update success"));
	else
		$objResponse->addAssign("spnShortcutMsg","innerHTML", $locate->Translate("update failed"));
	$objResponse->addScript("settimeout('document.getElementById(\'spnShortcutMsg\').innerHTML = \'\'','5');");
	return $objResponse;
}

$xajax->processRequests();
?>
