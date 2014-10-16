<?php
/*******************************************************************************
* account.server.php

* 账户管理系统后台文件
* account background management script

* Function Desc
	provide account management script

* 功能描述
	提供帐户管理脚本

* Function Desc
		init				初始化页面元素
		showGrid			显示grid
		createGrid			生成grid的HTML代码
		add					显示添加account的表单
		save				保存account信息
		update				更新account信息
		edit				显示修改account的表单
		delete				删除account信息
		showDetail			显示account详细信息
							当前返回空值
		searchFormSubmit    根据提交的搜索信息重构显示页面

* Revision 0.0456  2007/10/30 13:47:00  last modified by solo
* Desc: modify function showDetail, make it show account detail when click detail


* Revision 0.045  2007/10/19 10:01:00  last modified by solo
* Desc: modify extensions description

* Revision 0.045  2007/10/18 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('account.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');
require_once ("account.common.php");

/**
*  initialize page elements
*
*/

function init(){
	global $locate;

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("btnGroup","value",$locate->Translate("group_manage"));
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
		$numRows =& Customer::getNumRows($_SESSION['curuser']['groupid']);
		$arreglo =& Customer::getAllRecords($start,$limit,$order,$_SESSION['curuser']['groupid']);
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
			$numRows =& Customer::getNumRows($_SESSION['curuser']['groupid']);
			$arreglo =& Customer::getAllRecords($start,$limit,$order,$_SESSION['curuser']['groupid']);
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
	$fields[] = 'username';
	$fields[] = 'password';
	$fields[] = 'extension';
	$fields[] = 'agent';
	$fields[] = 'channel';
	$fields[] = 'extensions';
	$fields[] = 'usertype';
	$fields[] = 'groupname';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("username")."<BR \>";
	$headers[] = $locate->Translate("password")."<BR \>";
	$headers[] = $locate->Translate("extension")."<BR \>";
	$headers[] = $locate->Translate("dynamic agent")."<BR \>";
	$headers[] = $locate->Translate("channel")."<BR \>";
	$headers[] = $locate->Translate("extensions").','.$locate->Translate("extensions_note")."<BR \>";
	$headers[] = $locate->Translate("usertype").'<BR \>'.$locate->Translate("usertype_note")."";
	$headers[] = $locate->Translate("Group Name")."<BR \>";

	// HTML table: hearders attributes
	$attribsHeader = array();
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
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","username","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","password","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","extension","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","agent","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","channel","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","extensions","'.$divName.'","ORDERING");return false;\'';	
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","usertype","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'username';
	$fieldsFromSearch[] = 'password';
	$fieldsFromSearch[] = 'extension';
	$fieldsFromSearch[] = 'agent';
	$fieldsFromSearch[] = 'extensions';
	$fieldsFromSearch[] = 'usertype';
	$fieldsFromSearch[] = 'groupname';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("username");
	$fieldsFromSearchShowAs[] = $locate->Translate("password");
	$fieldsFromSearchShowAs[] = $locate->Translate("extension");
	$fieldsFromSearchShowAs[] = $locate->Translate("dynamic agent");
	$fieldsFromSearchShowAs[] = $locate->Translate("extensions");
	$fieldsFromSearchShowAs[] = $locate->Translate("User Type");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group Name");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(7,$start,$limit,$filter,$numRows,$content,$order);
	$table->exportFlag = '2';//对导出标记进行赋值
	$table->deleteFlag = '1';//对删除标记进行赋值
	$editFlag = 1;
	$deleteFlag = 1;
	$addFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['account']['delete']) {
			$deleteFlag = 1;
			$table->deleteFlag = '1';
		} else {
			$deleteFlag = 0;
			$table->deleteFlag = '0';
		}
		if($_SESSION['curuser']['privileges']['account']['edit']) {
			$editFlag = 1;
		}else {
			$editFlag = 0;
		}
	}
	//如果是groupoperator 就没有添加 编辑和删除的功能
	if($_SESSION['curuser']['usertype'] == 'groupoperator') {
		$addFlag = 0;
		$editFlag = 0;
		$deleteFlag = 0;
	}

	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$editFlag,$deleteFlag);
	$table->setAttribsCols($attribsCols);
	$table->ordering = $ordering;
	$table->addRowSearchMore("astercrm_account",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,$addFlag,$deleteFlag,$typeFromSearch,$typeFromSearchShowAs,$stype);
	
	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc[] = $row['username'];
		$rowc[] = $row['password'];
		$rowc[] = $row['extension'];
		$rowc[] = $row['agent'];
		$rowc[] = $row['channel'];
		$rowc[] = $row['extensions'];
		$rowc[] = $row['usertype'];
		$rowc[] = $row['groupname'];
		$table->addRow("astercrm_account",$rowc,$editFlag,$deleteFlag,1,$divName,$fields);
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
	$html = Table::Top($locate->Translate("adding_account"),"formDiv");  // <-- Set the title for your form.
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
	$f['extension'] = strtolower($f['extension']);
	$f['agent'] = strtolower($f['agent']);
	$f['channel'] = strtolower($f['channel']);

	if(trim($f['username']) == '' || trim($f['password']) == '' || trim($f['extension']) == '' || trim($f['usertype']) == '' || trim($f['firstname']) == '' || trim($f['lastname']) == ''){
		//$objResponse->addScript('window.location.href="portal.php";');
		//$objResponse->addScript('alert("abc")');
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}

	if(trim($f['usertype']) != 'admin' && trim($f['groupid']) == ''){
		$objResponse->addAlert($locate->Translate("please add group first"));
		return $objResponse->getXML();
	}

	$username = $f['username'];
	$userid = astercrm::checkValues("astercrm_account","username",$username);

	if($userid != '' ){
		$objResponse->addAlert($locate->Translate("username_repeat"));
		return $objResponse->getXML();
	}
	
	if($f['extensions'] == $locate->translate('extensions_input_tip')){
		$f['extensions'] = '';
	}
	
	if($f['extensions'] != ""){
		$myExtensions = split(",",astercrm::dbcToSbc($f['extensions']));

		if($f['extensType'] != "username" ){
		
			foreach($myExtensions as $exten){
				$sqlStr .= "OR extension = '$exten'";
			}
			$sqlStr = ltrim($sqlStr,"OR");
			$query = "SELECT username From astercrm_account WHERE $sqlStr";
			astercrm::events($query);
			$res =& $db->query($query);
			$myExtensions = array();
			while($res->fetchInto($row)){
				$myExtensions[] = $row['username'];
				$newextensions .= ",".$row['username'];
			}
			$f['extensions'] = ltrim($newextensions,',');
		}

		// check the assign username if belong to this group
		if ($_SESSION['curuser']['usertype'] != 'admin'){
			$myusernames = $myExtensions;
			$newextensions = "";
			
			$groupList = astercrm::getGroupMemberListByID($_SESSION['curuser']['groupid']);
			while	($groupList->fetchInto($groupRow)){
				$memberNames[] = $groupRow['username'];
			}

			foreach ($myusernames as $myusername){
				if(in_array($myusername,$memberNames)){
					$newextensions .= ",$myusername";
				}
			}
			$f['extensions'] = ltrim($newextensions,',');
		}	
	}

	// check over

	if ( $f['usertype'] == 'admin' ) $f['groupid'] = 0;

	$respOk = Customer::insertNewAccount($f); // add a new account
	if ($respOk == 1){
		if ( $f['usertype'] != 'admin' ){
			$group = astercrm::getRecordByID($f['groupid'],'astercrm_accountgroup');
			if($group['billingid'] > 0){
				if($config['billing']['resellerid'] >0 ){
					$checkreseller = astercrm::getRecordByID($config['billing']['resellerid'],'resellergroup');
					if($checkreseller['id'] == $config['billing']['resellerid']){
						$f['groupid'] = $group['billingid'];
						$f['resellerid'] = $config['billing']['resellerid'];
						$f['creditlimit'] = $config['billing']['clidcreditlimit'];
						$f['limittype'] = $config['billing']['clidlimittype'];
						$res = Customer::insertNewAccountForBilling($f);
						if($res == 1){
							$objResponse->addAlert($locate->Translate("add as a billing clid success"));
						}else{
							$objResponse->addAlert($locate->Translate("add as a billing clid failed"));
						}
					}else{
						$objResponse->addAlert($locate->Translate("Reseller id is incorrect, can not add this account as a billing clid"));
					}
				}else{
					$objResponse->addAlert($locate->Translate("Reseller id is incorrect, can not add this account as a billing clid"));
				}
			}
		}
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("add_account"));
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
	global $locate,$db;
	$objResponse = new xajaxResponse();
	
	$f['extension'] = strtolower($f['extension']);
	$f['agent'] = strtolower($f['agent']);
	$f['channel'] = strtolower($f['channel']);
	
	if(trim($f['username']) == '' || trim($f['password']) == '' || trim($f['extension']) == '' || trim($f['usertype']) == '' || trim($f['firstname']) == '' || trim($f['lastname']) == ''){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}

	if(trim($f['usertype']) != 'admin' && trim($f['groupid']) == ''){
		$objResponse->addAlert($locate->Translate("please add group first"));
		return $objResponse->getXML();
	}
	
	$username = $f['username'];
	$userid = astercrm::checkValues("astercrm_account","username",$username);

	if($userid != '' && $userid != $f['id'] ){
		$objResponse->addAlert($locate->Translate("username_repeat"));
		return $objResponse->getXML();
	}

	if($f['extensions'] == $locate->translate('extensions_input_tip')){
		$f['extensions'] = '';
	}
	
	if($f['extensions'] != ""){

		$f['extensions'] = astercrm::dbcToSbc($f['extensions']);
		$myExtensions = split(",",$f['extensions']);

		if( $f['extensType'] != "username" ){
		
			foreach($myExtensions as $exten){
				$sqlStr .= "OR extension = '$exten'";
			}
			$sqlStr = ltrim($sqlStr,"OR");
			$query = "SELECT username From astercrm_account WHERE $sqlStr";
			astercrm::events($query);
			$res =& $db->query($query);
			$myExtensions = array();
			while($res->fetchInto($row)){
				$myExtensions[] = $row['username'];
				$newextensions .= ",".$row['username'];
			}
			$f['extensions'] = ltrim($newextensions,',');
		}

		// check the assign username if belong to this group
		if ($_SESSION['curuser']['usertype'] != 'admin'){
			$myusernames = $myExtensions;
			$newextensions = "";
			
			$groupList = astercrm::getGroupMemberListByID($_SESSION['curuser']['groupid']);
			while	($groupList->fetchInto($groupRow)){
				$memberNames[] = $groupRow['username'];
			}
			foreach ($myusernames as $myusername){
				if(in_array($myusername,$memberNames)){
					$newextensions .= ",$myusername";
				}
			}
			
			$f['extensions'] = ltrim($newextensions,',');
		}
	}

	if ( $f['usertype'] == 'admin' ) $f['groupid'] = 0;
	// check over
	$respOk = Customer::updateAccountRecord($f);

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
	$html = Table::Top( $locate->Translate("edit_account"),"formDiv"); 
	$html .= Customer::formEdit($id);
	$html .= Table::Footer();
	// End edit zone

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	return $objResponse->getXML();
}

/**
*  update account record
*  @param	accountid	int			account id
*  @return	objResponse	object		xajax response object
*/

function delete($accountid = null){
	global $locate;		
	$res = Customer::deleteRecord($accountid,'astercrm_account');
	if ($res){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse = new xajaxResponse();
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
	}
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
	$html = Table::Top( $locate->Translate("account_detail"),"formDiv"); 
	$html .= Customer::showAccountDetail($accountid);
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
	if($optionFlag == "export"  || $optionFlag == "exportcsv"){
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'astercrm_account'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("maintable", "value", 'astercrm_account'); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($optionFlag == "delete"){
		astercrm::deletefromsearch($searchContent,$searchField,$searchType,'astercrm_account');
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','',$order,$divName,$ordering,'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}elseif($type == "delete"){
		$myrow =  astercrm::getRecordByField('username',$_SESSION['curuser']['username'],'astercrm_account');
	
		$myid = $myrow['id'];
		//echo $myid;exit;
		if ($myid == $id ) {
			$objResponse->addAlert($locate->Translate("Can not delete your own account"));
			return $objResponse->getXML();
		}
		$res = Customer::deleteRecord($id,'astercrm_account');
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
