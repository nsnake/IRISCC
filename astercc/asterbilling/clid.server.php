<?php
/*******************************************************************************
* clid.server.php

* 账户管理系统后台文件
* clid background management script

* Function Desc
	provide clid management script

* 功能描述
	提供帐户管理脚本

* Function Desc
		init				初始化页面元素
		showGrid			显示grid
		createGrid			生成grid的HTML代码
		add					显示添加clid的表单
		save				保存clid信息
		update				更新clid信息
		edit				显示修改clid的表单
		delete				删除clid信息
							当前返回空值
		searchFormSubmit    根据提交的搜索信息重构显示页面

* Revision 0.0456  2007/10/30 13:47:00  last modified by solo
* Desc: modify function showDetail, make it show clid detail when click detail


* Revision 0.045  2007/10/19 10:01:00  last modified by solo
* Desc: modify extensions description

* Revision 0.045  2007/10/18 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('clid.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/asterevent.class.php');
require_once ('include/asterisk.class.php');
require_once ('include/common.class.php');
require_once ("clid.common.php");

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

function generateSipFile(){
	global $locate,$db;
	$objResponse = new xajaxResponse();
	if ($_SESSION['curuser']['usertype'] == 'reseller'){
		astercc::generatePeersFile($_SESSION['curuser']['resellerid']);
		$objResponse->addAlert($locate->Translate("sip conf file generated"));
	}elseif ($_SESSION['curuser']['usertype'] == 'admin'){
		$res = astercrm::getAll("resellergroup");
		while ($res->fetchInto($row)) {
			astercc::generatePeersFile($row['id']);
		}
		$objResponse->addAlert($locate->Translate("all reseller sip conf files generated"));
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
	}
	return $objResponse;
}

function setGroup($resellerid){
	global $locate,$config;
	$objResponse = new xajaxResponse();
	$res = astercrm::getAll("accountgroup",'resellerid',$resellerid);
	//添加option
	while ($res->fetchInto($row)) {
		if($config['synchronize']['display_synchron_server']){
			$row['groupname'] = astercrm::getSynchronDisplay($row['id'],$row['groupname']);
		}

		$objResponse->addScript("addOption('groupid','".$row['id']."','".$row['groupname']."');");
	}
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
			$numRows =& Customer::getNumRowsMore($filter, $content,"clid");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"clid");
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

	// Databse Table: fields
	$fields = array();

	if($config['synchronize']['display_synchron_server']){
		$fields[] = 'id';
	}
	$fields[] = 'clid';
	$fields[] = 'pin';
	$fields[] = 'display';
	$fields[] = 'status';
	$fields[] = 'creditlimit';
	$fields[] = 'curcredit';
	$fields[] = 'limittype';
	$fields[] = 'credit_clid';
	$fields[] = 'groupname';
	$fields[] = 'resellername';
	$fields[] = 'isshow';
	$fields[] = 'addtime';

	// HTML table: Headers showed
	$headers = array();
	if($config['synchronize']['display_synchron_server']){
		$headers[] = $locate->Translate("Id")."<br>";
	}

	if($_SESSION['curuser']['billingfield'] == 'accountcode')
		$headers[] = $locate->Translate("Accountcode")."<br>";
	else
		$headers[] = $locate->Translate("Clid")."<br>";
	
	$headers[] = $locate->Translate("Pin")."<br>";
	$headers[] = $locate->Translate("Display")."<br>";
	$headers[] = $locate->Translate("Status")."<br>";
	$headers[] = $locate->Translate("Credit Limit")."<br>";
	$headers[] = $locate->Translate("Cur Credit")."<br>";
	$headers[] = $locate->Translate("Limit Type")."<br>";
	$headers[] = $locate->Translate("Clid Credit")."<br>";
	$headers[] = $locate->Translate("Group")."<br>";
	$headers[] = $locate->Translate("Reseller")."<br>";
	$headers[] = $locate->Translate("Is Show")."<br>";
	$headers[] = $locate->Translate("Last Update")."<br>";

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
	$attribsCols[] = 'style="text-align: left"';

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	if($config['synchronize']['display_synchron_server']){
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","id","'.$divName.'","ORDERING");return false;\'';
	}
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","clid","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","pin","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","display","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","status","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","creditlimit","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","curcredit","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","limittype","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","credit_clid","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","resellername","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","isshow","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","addtime","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'clid';
	$fieldsFromSearch[] = 'pin';
	$fieldsFromSearch[] = 'display';
	$fieldsFromSearch[] = 'status';
	$fieldsFromSearch[] = 'clid.creditlimit';
	$fieldsFromSearch[] = 'clid.curcredit';
	$fieldsFromSearch[] = 'clid.limittype';	
	$fieldsFromSearch[] = 'clid.credit_clid';
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'resellername';
	$fieldsFromSearch[] = 'clid.addtime';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	if($billingfield == 'accountcode')
		$fieldsFromSearchShowAs[] = $locate->Translate("Accountcode");
	else
		$fieldsFromSearchShowAs[] = $locate->Translate("Clid");
	$fieldsFromSearchShowAs[] = $locate->Translate("Pin");
	$fieldsFromSearchShowAs[] = $locate->Translate("Display");
	$fieldsFromSearchShowAs[] = $locate->Translate("Status");
	$fieldsFromSearchShowAs[] = $locate->Translate("Credit Limit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Cur credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Limit type");
	$fieldsFromSearchShowAs[] = $locate->Translate("Clid Credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group");
	$fieldsFromSearchShowAs[] = $locate->Translate("Reseller");
	$fieldsFromSearchShowAs[] = $locate->Translate("Last Update");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);

	$table->setAttribsCols($attribsCols);	

	if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,1,1,0);
		$table->deleteFlag = '1';//对删除标记进行赋值
		$table->exportFlag = '1';//对导出标记进行赋值
		$table->addRowSearchMore("clid",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$typeFromSearch,$typeFromSearchShowAs,$stype);
	}else{
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,1,0,0);
		if($_SESSION['curuser']['usertype'] == 'groupadmin') $table->exportFlag = '1';//对导出标记进行赋值
		$table->addRowSearchMore("clid",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$typeFromSearch,$typeFromSearchShowAs,$stype);
	}

	

//	if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
//		$table->addRowSearchMore("clid",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$typeFromSearch,$typeFromSearchShowAs,$stype);
//	}else{
//		$table->addRowSearchMore("clid",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$typeFromSearch,$typeFromSearchShowAs,$stype);
//	}

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
		$rowc[] = $row['clid'];
		$rowc[] = $row['pin'];
		$rowc[] = $row['display'];
		$rowc[] = $row['status'];
		$rowc[] = $row['creditlimit'];
		$rowc[] = $row['curcredit'];
		$rowc[] = $row['limittype'];
		$rowc[] = $row['credit_clid'];
		$rowc[] = $row['groupname'];
		$rowc[] = $row['resellername'];
		$rowc[] = $locate->Translate($row['isshow']);
		$rowc[] = $row['addtime'];

		if(!empty($row['limittype']) && (($row['creditlimit'] - $row['curcredit'] < 0) || $row['curcredit'] < 0)){
			$trstyle = 'style="background-color:red;"';
		} else {
			$trstyle = '';
		}
		
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
			$table->addRow("clid",$rowc,1,1,0,$divName,$fields,$trstyle);
		}else{
			$table->addRow("clid",$rowc,1,0,0,$divName,$fields,$trstyle);
		}
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
	$html = Table::Top($locate->Translate("add_clid"),"formDiv");  // <-- Set the title for your form.
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
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();
	//check clid could only be numuric
	if (!is_numeric($f['clid'])){
		$objResponse->addAlert("clid must be numeric");
		return $objResponse;
	}

	if ( trim($f['pin']) == '' ){
		$objResponse->addAlert("pin field cant be null");
		return $objResponse;
	}

	if ($f['groupid'] == 0 || $f['resellerid'] == 0){
		$objResponse->addAlert($locate->Translate("Please choose reseller and group"));
		return $objResponse->getXML();
	}

	if($config['synchronize']['id_autocrement_byset']){
		$local_lastid = astercrm::getLocalLastId('clid');
		$f['id'] = intval($local_lastid+1);
	}

	// check if clid duplicate
	$res = astercrm::checkValues("clid","clid",$f['clid']);

	if ($res != ''){
		$objResponse->addAlert($locate->Translate("clid duplicate"));
		return $objResponse->getXML();
	}

	if ($f['display'] == '') {
		$f['display'] = $f['clid'];
	}

	// check if pin duplicate
	$res = astercrm::checkValues("clid","pin",$f['pin']);

	if ($res != ''){
		$objResponse->addAlert($locate->Translate("pin duplicate"));
		return $objResponse->getXML();
	}

	$respOk = Customer::insertNewClid($f); // add a new account
	if ($respOk){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("add_clid"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
		$objResponse->addClear("formDiv", "innerHTML");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_insert"));
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

	if (!is_numeric($f['clid'])){
		$objResponse->addAlert($locate->Translate("clid must be numeric"));
		return $objResponse;
	}

	if ( trim($f['pin']) == '' ){
		$objResponse->addAlert($locate->Translate("pin field cant be null"));
		return $objResponse;
	}

	if ($f['groupid'] == 0 || $f['resellerid'] == 0){
		$objResponse->addAlert($locate->Translate("Please choose reseller and group"));
		return $objResponse->getXML();
	}

	// check if clid duplicate
	$res = astercrm::checkValuesNon($f['id'],"clid","clid",$f['clid']);

	if ($res != ''){
		$objResponse->addAlert($locate->Translate("clid duplicate"));
		return $objResponse->getXML();
	}


	// check if pin duplicate
	if ($f['pin'] != ''){
		$res = astercrm::checkValuesNon($f['id'],"clid","pin",$f['pin'],"string","groupid",$f['groupid']);
		if ($res != ''){
			$objResponse->addAlert($locate->Translate("pin duplicate in same group"));
			return $objResponse->getXML();
		}
	}

	if ($f['display'] == '') {
		$f['display'] = $f['clid'];
	}

//	$res = astercrm::checkValues("clid","clid",$f['clid']);

	$respOk = Customer::updateClidRecord($f);

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
*  show account edit form
*  @param	id			int			account id
*  @return	objResponse	object		xajax response object
*/

function edit($id){
	global $locate;
	$html = Table::Top( $locate->Translate("edit_clid"),"formDiv"); 
	$html .= Customer::formEdit($id);
	$html .= Table::Footer();
	// End edit zone

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	return $objResponse->getXML();
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
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'clid'); //得到要导出的sql语句
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
			astercrm::deleteToHistoryFromSearch($searchContent,$searchField,$searchType,'clid');
		} else {
			astercrm::deletefromsearch($searchContent,$searchField,$searchType,'clid');
		}

		$html = createGrid($numRows, $limit,'','','',$divName,"",$searchType);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}elseif($type == "delete"){
		if(empty($_SESSION['curuser']['usertype'])){
			$objResponse->addAlert($locate->Translate("Session time out,please try again"));
			return $objResponse->getXML();
		}

		if($config['synchronize']['delete_by_use_history']){
			$res = Customer::deleteRecordToHistory('id',$id,'clid');
		} else {
			$res = Customer::deleteRecord($id,'clid');
		}
		
		if ($res){
			$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "",$searchType);
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("record deleted"));
			$objResponse->addAssign($divName, "innerHTML", $html);
		}else{
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("record cannot be deleted"));		
		}
		
	}else{
		$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField, $divName, "",$searchType);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}
	
	return $objResponse->getXML();
}

$xajax->processRequests();
?>
