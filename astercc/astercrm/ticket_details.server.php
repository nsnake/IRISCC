<?php
require_once ("db_connect.php");
require_once ('ticket_details.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');
require_once ("ticket_details.common.php");

/**
*  initialize page elements
*
*/

function init(){
	global $locate;

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("btnTicketCategory","value",$locate->Translate("ticketcategory_manage"));
	$objResponse->addAssign("btnTicketOplogs","value",$locate->Translate("ticket_details_operate_logs"));
	$objResponse->addAssign("btnTicket","value",$locate->Translate("ticket_manage"));
	$_SESSION['ParentID'] = '';
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
	$fields[] = 'ticketcategoryname';
	$fields[] = 'ticketname';
	$fields[] = 'id';// ticket id
	$fields[] = 'customer';
	$fields[] = 'assignto';
	$fields[] = 'status';
	$fields[] = 'memo';
	$fields[] = 'creby';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ALL")."<input type='checkbox' onclick=\"ckbAllOnClick(this);\"><BR \>";//"select all for delete";
	$headers[] = $locate->Translate("TicketCategory Name");
	$headers[] = $locate->Translate("Ticket Name");
	$headers[] = $locate->Translate("TicketDetail ID");
	$headers[] = $locate->Translate("Group Name");
	$headers[] = $locate->Translate("Customer");
	$headers[] = $locate->Translate("AssignTo");
	$headers[] = $locate->Translate("Status");
	$headers[] = $locate->Translate("Memo");
	$headers[] = $locate->Translate("Creby");

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="5%"';
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
	$eventHeader[]= '';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","ticketcategoryname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","ticketname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","id","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","username","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","status","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","memo","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","creby","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'ticketcategoryname';
	$fieldsFromSearch[] = 'ticketname';
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'customer';
	$fieldsFromSearch[] = 'username';
	//$fieldsFromSearch[] = 'status';
	//$fieldsFromSearch[] = 'memo';
	$fieldsFromSearch[] = 'creby';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("TicketCategory Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Ticket Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Customer");
	$fieldsFromSearchShowAs[] = $locate->Translate("AssignTo");
	//$fieldsFromSearchShowAs[] = $locate->Translate("Status");
	//$fieldsFromSearchShowAs[] = $locate->Translate("Memo");
	$fieldsFromSearchShowAs[] = $locate->Translate("Creby");

	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(7,$start,$limit,$filter,$numRows,$content,$order);
	
	$table->exportFlag = '2';//对导出标记进行赋值
	$table->deleteFlag = '1';//对删除标记进行赋值
	$table->ordering = $ordering;

	$editFlag = 1;
	$deleteFlag = 1;
	$deleteBtnFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['ticket_details']['delete']) {
			$deleteFlag = 1;
			$table->deleteFlag = '1';
			$deleteBtnFlag = 1;
		} else {
			$deleteFlag = 0;
			$table->deleteFlag = '0';
			$deleteBtnFlag = 0;
		}
		if($_SESSION['curuser']['privileges']['ticket_details']['edit']) {
			$editFlag = 1;
		}else {
			$editFlag = 0;
		}
	}

	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$editFlag,$deleteFlag,0);
	$table->setAttribsCols($attribsCols);
	$table->addRowSearchMore("ticket_details",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$deleteBtnFlag,$typeFromSearch,$typeFromSearchShowAs,$stype);
	
	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc['select_id'] = $row['id'];
		$rowc[] = $row['ticketcategoryname'];
		$rowc[] = $row['ticketname'];
		$rowc[] = str_pad($row['id'],8,'0',STR_PAD_LEFT);
		$rowc[] = $row['groupname'];
		$rowc[] = $row['customer'];
		$rowc[] = $row['username'];
		$rowc[] = $locate->Translate($row['status']);
		$rowc[] = $row['memo'];
		$rowc[] = $row['creby'];
		$table->addRow("ticket_details",$rowc,$editFlag,$deleteFlag,0,$divName,$fields);
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render('delGrid');
 	
 	return $html;
}

function add(){
	global $locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("adding_ticketdetails"),"formDiv");  // <-- Set the title for your form.
	$html .= Customer::formAdd();  // <-- Change by your method
	$html .= Table::Footer();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	$objResponse->addScript("relateBycategoryID(document.getElementById('ticketcategoryid').value)");
	return $objResponse->getXML();
}

/**
*  save ticketcategory record
*  @param	f			array		ticket record
*  @return	objResponse	object		xajax response object
*/

function save($f){
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();
	if(trim($f['ticketcategoryid']) == 0 || trim($f['ticketid']) == 0 || trim($f['customerid']) == 0){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}
	
	$validParentTicket = true;
	if($f['parent_id'] != '') {
		if(!preg_match('/^[\d]*$/',$f['parent_id'])){
			$objResponse->addAlert($locate->Translate("Parent TicketDetail ID must be integer"));
			return $objResponse->getXML();
		}
		//验证写入的parent_id 是否存在
		$validParentTicket = Customer::validParentTicketId($f['parent_id']);
	}
	
	// check over
	//if ( $f['usertype'] == 'admin' ) $f['groupid'] = 0;

	$respOk = Customer::insertTicketDetail($f); // add a new ticket
	
	if ($respOk == 1){
		if(!$validParentTicket) {
			$objResponse->addAlert($locate->Translate("Save Success,but Parent TicketDetail ID is not exists"));
		}

		// track the ticket_op_logs
		if($f['assignto'] == 0) {
			$f['assignto'] = '';
		}
		Customer::ticketOpLogs('add','status','','new',$f['assignto'],$f['groupid']);

		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("add_ticket_details"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
		$objResponse->addClear("formDiv", "innerHTML");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_insert"));
	}
	return $objResponse->getXML();
	
}

function relateByCategoryId($fid,$curTicketId=0) {
	$objResponse = new xajaxResponse();
	//ticket option
	$ticketOption = Customer::getTicketByCid($fid);
	$objResponse->addAssign("ticketMsg","innerHTML",$ticketOption);
	
	// group option
	$groupOption = Customer::getGroup($fid);
	$objResponse->addAssign("groupMsg","innerHTML",$groupOption);
	
	$objResponse->addScript("relateByGroup(document.getElementById('groupid').value)");
	return $objResponse->getXML();
}

function relateByGroup($groupId){
	$objResponse = new xajaxResponse();
	// customer option
	//$customerOption = Customer::getCustomer($groupId);
	//$objResponse->addAssign("customerMsg","innerHTML",$customerOption);

	// account option
	$accountOption = Customer::getAccount($groupId);
	$objResponse->addAssign("accountMsg","innerHTML",$accountOption);
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
		$joinstr = Customer::createSqlWithStype($searchField,$searchContent,$searchType,'ticket_details'); //得到要导出的sql语句
		$joinstr=ltrim($joinstr,'AND');
		$sql = "SELECT ticketcategory.ticketname as ticketcategoryname,tickets.ticketname as ticketname, customer,ticket_details.status,customer.customer,ticket_details.id,ticket_details.memo,ticket_details.cretime,ticket_details.creby FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto";
		if($joinstr != '') $sql .= " WHERE ".$joinstr;
		$_SESSION['export_sql'] = $sql.'';
		
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("maintable", "value", 'ticket_details'); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($optionFlag == "delete"){
		astercrm::deletefromsearch($searchContent,$searchField,$searchType,'ticket_details');
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','',$order,$divName,$ordering,'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}elseif($type == "delete"){
		/*$myrow =  astercrm::getRecordByField('ticketname',$_SESSION['curuser']['ticketname'],'ticket_details');
	
		$myid = $myrow['id'];
		//echo $myid;exit;
		if ($myid == $id ) {
			$objResponse->addAlert($locate->Translate("Can not delete this ticket_detail"));
			return $objResponse->getXML();
		}*/
		$res = Customer::deleteRecord($id,'ticket_details');
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

function deleteByButton($f,$searchFormValue){
	$objResponse = new xajaxResponse();
	if(is_array($f['ckb'])){
		foreach($f['ckb'] as $vaule){
			$res_customer = astercrm::deleteRecord($vaule,'ticket_details');
		}
	}
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$numRows = $searchFormValue['numRows'];
	$limit = $searchFormValue['limit'];     
	$html = createGrid($numRows, $limit,$searchField, $searchContent, $order,'grid');
	$objResponse->addAssign('grid', "innerHTML", $html);
	return $objResponse->getXML();
}

/**
*  update ticketcategory record
*  @param	ticketid	int			ticket id
*  @return	objResponse	object		xajax response object
*/

function delete($ticketid = null){
	global $locate;		
	
	$res = Customer::deleteRecord($ticketid,'ticket_details');
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
*  show ticket_details edit form
*  @param	id		int		ticket_detail id
*  @return	objResponse	object		xajax response object
*/

function edit($id){
	global $locate;
	$html = Table::Top( $locate->Translate("edit_ticket_detail"),"formDiv"); 
	$html .= Customer::formEdit($id);
	$html .= Table::Footer();
	// End edit zone
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	//$objResponse->addScript("relateBycategoryID(document.getElementById('ticketcategoryid').value,'edit')");
	return $objResponse->getXML();
}

/**
*  update ticketcategory record
*  @param	f			array		account record
*  @return	objResponse	object		xajax response object
*/

function update($f){
	global $locate,$db;
	$objResponse = new xajaxResponse();
	
	if(trim($f['ticketcategoryid']) == 0 || trim($f['ticketid']) == 0 || trim($f['customerid']) == 0){
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}

	$validParentTicket = true;
	if($f['parent_id'] != '') {
		if(!preg_match('/^[\d]*$/',$f['parent_id'])){
			$objResponse->addAlert($locate->Translate("Parent TicketDetail ID must be integer"));
			return $objResponse->getXML();
		}
		//验证写入的parent_id 是否存在
		$validParentTicket = Customer::validParentTicketId($f['parent_id']);
	}

	$oriResult = Customer::getOriResult($f['id']);

	//if ( $f['usertype'] == 'admin' ) $f['groupid'] = 0;
	// check over
	$respOk = Customer::updateTicketDetail($f);

	if($respOk){
		if(!$validParentTicket) {
			$objResponse->addAlert($locate->Translate("Update Success,but Parent TicketDetail ID is not exists"));
		}

		$new_assign = '';
		if($f['assignto'] != 0) {
			$new_assign = Customer::getAssignToName($f['assignto']);
		}

		$ori_assign = '';
		if($oriResult['assignto'] != 0) {
			$ori_assign = Customer::getAssignToName($oriResult['assignto']);
		}

		// track the ticket_op_logs
		if($oriResult['status'] != $f['status']) {
			Customer::ticketOpLogs('update','status',$oriResult['status'],$f['status'],$new_assign,$f['groupid']);
		}

		if($oriResult['assignto'] != $f['assignto']) {
			Customer::ticketOpLogs('update','assignto',$ori_assign,$new_assign,$new_assign,$f['groupid']);
		}
		
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("update_rec"));
		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_update"));
	}
	
	return $objResponse->getXML();
}


function viewSubordinateTicket($pid){
	global $locate;
	$html = Table::Top( $locate->Translate("view_subordinate_ticketdetails"),"formSubordinateTicketDiv"); 
	$html .= Customer::subordinateTicket($pid);
	$html .= Table::Footer();
	// End edit zone
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("formSubordinateTicketDiv", "style.visibility", "visible");
	$objResponse->addAssign("formSubordinateTicketDiv", "innerHTML", $html);
	return $objResponse->getXML();
	
}

$xajax->processRequests();
?>