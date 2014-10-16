<?php
/*******************************************************************************
* customer.server.php

* 客户管理系统后台文件
* customer background management script

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

* Revision 0.0451  2007/10/22 16:45:00  last modified by solo
* Desc: remove Edit and Detail tab in xajaxGrid

* Revision 0.045  2007/10/22 16:45:00  last modified by solo
* Desc: remove function "importCSV" and "export"

* Revision 0.045  2007/10/18 14:30:00  last modified by solo
* Desc: remove function "edit"

* Revision 0.045  2007/10/18 14:08:00  last modified by solo
* Desc: comment added

********************************************************************************/
require_once ("db_connect.php");
require_once ("customer.common.php");
require_once ('customer.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('astercrm.server.common.php');
require_once ('include/common.class.php');
require_once ('include/astercrm.class.php');
require_once ('include/asterisk.class.php');
require_once ('include/phoogle.php');

/**
*  initialize page elements
*
*/

function init(){
	global $locate,$config;

	$objResponse = new xajaxResponse();

	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','')");

	$objResponse->addAssign("btnContact","value",$locate->Translate("contact"));
	$objResponse->addAssign("btnNote","value",$locate->Translate("note"));
	$objResponse->addAssign("btnCustomerLead","value",$locate->Translate("customer_leads"));
	if($config['system']['customer_leads'] == 'default_move' || $config['system']['customer_leads'] =='move') {
		$objResponse->addAssign("customerLeadAction","innerHTML","<input type=\"button\" onclick=\"xajax_customerLeadsAction('".$config['system']['customer_leads']."',xajax.getFormValues('delGrid'),xajax.getFormValues('searchForm'));\" id=\"btnCustomerlead\" name=\"btnCustomerlead\" value=\"".$locate->Translate("move_to_customerleads")."\">");
	} else if($config['system']['customer_leads'] == 'default_copy' || $config['system']['customer_leads'] =='copy'){
		$objResponse->addAssign("customerLeadAction","innerHTML","<input type=\"button\" onclick=\"xajax_customerLeadsAction('".$config['system']['customer_leads']."',xajax.getFormValues('delGrid'),xajax.getFormValues('searchForm'));\" id=\"btnCustomerlead\" name=\"btnCustomerlead\" value=\"".$locate->Translate("copy_to_customerleads")."\">");
	} else {
		$objResponse->addAssign("customerLeadAction","innerHTML","");
	}
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
	global $locate,$config;//
	//echo $ordering.$order;exit;
	$_SESSION['ordering'] = $ordering;
	//if($order == 'code' || $order == 'code');exit;
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
			$numRows =& Customer::getNumRowsMore($filter, $content,"customer");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"customer");
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
	$fields[] = 'city';
	$fields[] = 'phone';
	$fields[] = 'contact';
	$fields[] = 'website';
	$fields[] = 'category';
	if($config['system']['enable_code']) {
		$fields[] = 'note';
		$fields[] = 'codes';
		$fields[] = 'noteCretime';
	}
	$fields[] = 'cretime';
	$fields[] = 'creby';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ALL")."<input type='checkbox' onclick=\"ckbAllOnClick(this);\"><BR \>";//"select all for delete";
	$headers[] = $locate->Translate("customer_name")."<BR \>";//"Customer Name";
	$headers[] = $locate->Translate("state")."<BR \>";//"Customer Name";
	$headers[] = $locate->Translate("city")."<BR \>";//"Category";
	$headers[] = $locate->Translate("phone")."<BR \>";//"Contact";
	$headers[] = $locate->Translate("contact")."<BR \>";//"Category";
	$headers[] = $locate->Translate("website")."<BR \>";//"Note";
	$headers[] = $locate->Translate("category")."<BR \>";//"Category";
	if($config['system']['enable_code']) {
		$headers[] = $locate->Translate("note");
		$headers[] = $locate->Translate("codes");
		$headers[] = $locate->Translate("note_cretime");
	}
	$headers[] = $locate->Translate("create_time")."<BR \>";//"Create By";
	$headers[] = $locate->Translate("create_by")."<BR \>";

	// HTML table: hearders attributes
	$attribsHeader = array();
	if($config['system']['enable_code']) {
		$attribsHeader[] = 'width="5%"';
		$attribsHeader[] = 'width="12%"';
		$attribsHeader[] = 'width="7%"';
		$attribsHeader[] = 'width="8%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="8%"';
		$attribsHeader[] = 'width="8%"';
		$attribsHeader[] = 'width="7%"';
		$attribsHeader[] = 'width="5%"';
	} else {
		$attribsHeader[] = 'width="5%"';
		$attribsHeader[] = 'width="16%"';
		$attribsHeader[] = 'width="7%"';
		$attribsHeader[] = 'width="8%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="15%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="9%"';
		$attribsHeader[] = 'width="7%"';
		$attribsHeader[] = 'width="5%"';
	}
	

	// HTML Table: columns attributes
	$attribsCols = array();
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
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
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","city","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","phone","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","contact","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","website","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","category","'.$divName.'","ORDERING");return false;\'';
	if($config['system']['enable_code']) {
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","note","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","codes","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","noteCretime","'.$divName.'","ORDERING");return false;\'';
	}
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","cretime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","creby","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'customer';
	$fieldsFromSearch[] = 'state';
	$fieldsFromSearch[] = 'city';
	$fieldsFromSearch[] = 'phone';
	$fieldsFromSearch[] = 'fax';
	if($config['system']['enable_code']) {
		$fieldsFromSearch[] = 'note';
		$fieldsFromSearch[] = 'codes';
		$fieldsFromSearch[] = 'note.cretime';
	}
	$fieldsFromSearch[] = 'contact';
	$fieldsFromSearch[] = 'website';
	$fieldsFromSearch[] = 'category';
	$fieldsFromSearch[] = 'customer.cretime';
	$fieldsFromSearch[] = 'customer.creby';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("customer_name");
	$fieldsFromSearchShowAs[] = $locate->Translate("state");
	$fieldsFromSearchShowAs[] = $locate->Translate("city");
	$fieldsFromSearchShowAs[] = $locate->Translate("phone");
	$fieldsFromSearchShowAs[] = $locate->Translate("fax");
	if($config['system']['enable_code']) {
		$fieldsFromSearchShowAs[] = $locate->Translate("note");
		$fieldsFromSearchShowAs[] = $locate->Translate("codes");
		$fieldsFromSearchShowAs[] = $locate->Translate("note_cretime");
	}
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

	$delteBtnFlag = 1;
	$deleteFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['customer']['delete']) {
			$deleteFlag = 1;
			$table->deleteFlag = '1';
			$delteBtnFlag = 1;
		} else {
			$deleteFlag = 0;
			$delteBtnFlag = 0;
			$table->deleteFlag = '0';
		}
		if($_SESSION['curuser']['privileges']['customer']['edit']) {
			$editFlag = 1;
		}else {
			$editFlag = 0;
		}
	}
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,0,$deleteFlag,0);
	$table->setAttribsCols($attribsCols);
	$table->addRowSearchMore("customer",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$delteBtnFlag,$typeFromSearch,$typeFromSearchShowAs,$stype);
	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc['select_id'] = $row['id'];
		$rowc[] = '<a href=? onclick="xajax_showDetail(\''.$row['id'].'\');return false;">'.$row['customer'].'</a>';
		$rowc[] = $row['state'];
		$rowc[] = $row['city'];
		$rowc[] = $row['phone'];
		$rowc[] = $row['contact'];
		$rowc[] = $row['website'];
		$rowc[] = $row['category'];
		if($config['system']['enable_code']) {
			$rowc[] = $row['note'];
			$rowc[] = $row['codes'];
			$rowc[] = $row['noteCretime'];
		}
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
		$html .= Customer::showCustomerRecord($customerid);
		$html .= Table::Footer();
		$objResponse->addAssign("formCustomerInfo", "style.visibility", "visible");
		$objResponse->addAssign("formCustomerInfo", "innerHTML", $html);
	}
	return $objResponse->getXML();
}

function searchFormSubmit($searchFormValue,$numRows = null,$limit = null,$id = null,$type = null){
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];
	$ordering = $searchFormValue['ordering'];
	$order = $searchFormValue['order'];
//	print_r($searchFormValue);exit;
	$divName = "grid";
	if($optionFlag == "export"  || $optionFlag == "exportcsv"){
		if($config['system']['enable_code']) {
			$sql = Customer::specialGetSql($searchContent,$searchField,$searchType,'customer',array('customer.*','note.note'=>'note','note.codes'=>'codes','note.creby'=>'last_note_created_by','note.cretime'=>'noteCretime'),array('note'=>array('note.id','customer.last_note_id'))); //得到要导出的sql语句
			
		} else {
			$sql = Customer::specialGetSql($searchContent,$searchField,$searchType,'customer'); //得到要导出的sql语句
		}
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("maintable", "value", 'customer'); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}
	if($optionFlag == "delete"){
		$customer_ref=& Customer::getRecordsFilteredMorewithstype('','', $searchField, $searchContent, $searchType,'','customer','delete');
		while($customer_ref->fetchInto($row)){
			Customer::deleteRecord($row['id'],'customer');
			Customer::deleteRecords("customerid",$row['id'],'note');
			Customer::deleteRecords("customerid",$row['id'],'contact');
		}
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','',$order,$divName,$ordering,'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);

	} else if($optionFlag == "move_to_customerleads" || $optionFlag == "copy_to_customerleads"){
		$lead_sql= Customer::specialGetSql($searchContent,$searchField,$searchType,'customer');
		Customer::events($lead_sql);
		$customer_lead =& $db->query($lead_sql);
		$i = 0;
		while($customer_lead->fetchInto($row)){
			$res = astercrm::insertNewCustomerLead($row['id'],$config['system']['customer_leads'],true);
			if($res) {
				$i ++ ;
			}
		}
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','',$order,$divName,$ordering,'');
		//$objResponse->addClear("msgZone", "innerHTML");
		$showHtml = '';
		if($config['system']['customer_leads'] == 'move' || $config['system']['customer_leads'] == 'default_move') {
			$showHtml = $i.$locate->Translate(" customer was moved to customer_leads");
		} else if($config['system']['customer_leads'] == 'copy' || $config['system']['customer_leads'] == 'default_copy'){
			$showHtml = $i.$locate->Translate(" customer was copied to customer_leads");
		}
		$objResponse->addAssign($divName, "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML",$showHtml);
	}else{
		if($type == "delete"){
			$res = Customer::deleteRecord($id,'customer');
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
			$res_contact = astercrm::deleteRecords('customerid',$vaule,'contact');
			$res_note = astercrm::deleteRecords('customerid',$vaule,'note');
			$res_customer = astercrm::deleteRecord($vaule,'customer');
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

function dial($phoneNum,$first = '',$myValue,$dtmf = ''){
	global $config,$locate;

	$objResponse = new xajaxResponse();
	if(trim($myValue['curid']) > 0) $curid = trim($myValue['curid']) - 1;
	else $curid = trim($myValue['curid']);
	
	if ($dtmf != '') {
		$app = 'Dial';
		$data = 'local/'.$phoneNum.'@'.$config['system']['outcontext'].'|30'.'|D'.$dtmf;
		$first = 'caller';
	}

	$myAsterisk = new Asterisk();	
	if ($first == ''){
		$first = $config['system']['firstring'];
	}

	$myAsterisk->config['asmanager'] = $config['asterisk'];
	$res = $myAsterisk->connect();
	if (!$res)
		$objResponse->addAssign("mobileStatus", "innerText", "Failed");

	if ($first == 'caller'){	//caller will ring first
		$strChannel = "local/".$_SESSION['curuser']['extension']."@".$config['system']['incontext']."/n";

		if ($config['system']['allow_dropcall'] == true){
			$myAsterisk->dropCall($sid,array('Channel'=>"$strChannel",
								'WaitTime'=>30,
								'Exten'=>$phoneNum,
								'Context'=>$config['system']['outcontext'],
								'Account'=>$_SESSION['curuser']['accountcode'],
								'Variable'=>"$strVariable",
								'Priority'=>1,
								'MaxRetries'=>0,
								'CallerID'=>$phoneNum));
		}else{
			$myAsterisk->sendCall($strChannel,$phoneNum,$config['system']['outcontext'],1,$app,$data,30,$phoneNum,NULL,$_SESSION['curuser']['accountcode']);
		}
	}else{
		$strChannel = "local/".$phoneNum."@".$config['system']['outcontext']."/n";

		if ($config['system']['allow_dropcall'] == true){

/*
	coz after we use new method to capture dial event
	there's no good method to make both leg display correct clid for now
	so we comment these lines
*/
			$myAsterisk->dropCall($sid,array('Channel'=>"$strChannel",
								'WaitTime'=>30,
								'Exten'=>$_SESSION['curuser']['extension'],
								'Context'=>$config['system']['incontext'],
								'Account'=>$_SESSION['curuser']['accountcode'],
								'Variable'=>"$strVariable",
								'Priority'=>1,
								'MaxRetries'=>0,
								'CallerID'=>$_SESSION['curuser']['extension']));
		}else{
			$myAsterisk->sendCall($strChannel,$_SESSION['curuser']['extension'],$config['system']['incontext'],1,$app,$data,30,$_SESSION['curuser']['extension'],NULL,NULL);
		}
	}
	//$myAsterisk->disconnect();
	$objResponse->addAssign("divMsg", "style.visibility", "hidden");
	return $objResponse->getXML();
}

function addSchedulerDial($display='',$number){
	global $locate,$db;

	$objResponse = new xajaxResponse();
	if($display == "none"){
		$campaignflag = false;
		$html = '<td nowrap align="left">'.$locate->Translate("Scheduler Dial").'</td>
					<td align="left">'.$locate->Translate("Number").' : <input type="text" id="sDialNum" name="sDialNum" size="15" maxlength="35" value="'.$number.'">';
		if($number != ''){
			$curtime = date("Y-m-d H:i:s");
			$curtime = date("Y-m-d H:i:s",strtotime("$curtime -30 seconds"));
			$sql = "SELECT campaignid FROM dialedlist WHERE dialednumber = '".$number."' AND dialedtime > '".$curtime."' ";
			$curcampaignid = $db->getOne($sql);
			if($curcampaignid != ''){
				$campaignflag = true;
				$curcampaign = astercrm::getRecordByID($curcampaignid,'campaign');
				$curcampaign_name = $curcampaign['campaignname'];
				$html .= '&nbsp;'.$locate->Translate("campaign").' : <input type="text" value="'.$curcampaign_name.'" id="campaignname" name="campaignname" size="15" readonly><input type="hidden" value="'.$curcampaignid.'" id="curCampaignid" name="curCampaignid" size="15" readonly>';
			}
		}
		if(!$campaignflag){
			$campaign_res = astercrm::getRecordsByField("groupid",$_SESSION['curuser']['groupid'],"campaign");
			while ($campaign_res->fetchInto($campaign)) {
				$campaignoption .= '<option value="'.$campaign['id'].'">'.$campaign['campaignname'].'</option>'; 
			}
			$html .= '&nbsp;'.$locate->Translate("campaign").' : <select id="curCampaignid" name="curCampaignid" >'.$campaignoption.'</select>';
		}
		//
		$html .= '<br>'.$locate->Translate("Dialtime").' : <input type="text" name="sDialtime" id="sDialtime" size="15" value="" onfocus="displayCalendar(this,\'yyyy-mm-dd hh:ii\',this,true)">&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("Add").'" onclick="saveSchedulerDial();">
					</td>';		
		$objResponse->addAssign("trAddSchedulerDial", "innerHTML", $html);
		$objResponse->addAssign("trAddSchedulerDial", "style.display", "");
	}else{
		$objResponse->addAssign("trAddSchedulerDial", "style.display", "none");
	}
	return $objResponse->getXML();
}

function saveSchedulerDial($dialnumber='',$campaignid='',$dialtime=''){
	global $locate,$db;
	$objResponse = new xajaxResponse();
	if($dialnumber == ''){
		$objResponse->addAlert($locate->Translate("Number can not be blank"));
		return $objResponse->getXML();
	}
	if($campaignid == ''){
		$objResponse->addAlert($locate->Translate("Campaign can not be blank"));
		return $objResponse->getXML();
	}
	if($dialtime == ''){
		$objResponse->addAlert($locate->Translate("Dial time can not be blank"));
		return $objResponse->getXML();
	}	
	$sql = "INSERT INTO diallist SET "
			."dialnumber='".astercrm::getDigitsInStr($dialnumber)."', "
			."groupid='".$_SESSION['curuser']['groupid']."', "
			."dialtime='".$dialtime."', "
			."creby='".$_SESSION['curuser']['username']."', "
			."cretime= now(), "
			."campaignid= ".$campaignid." ";
	$res =& $db->query($sql);
	if($res){
		$objResponse->addAlert($locate->Translate("Add scheduler dial success"));
		$objResponse->addAssign("trAddSchedulerDial", "style.display", "none");
	}else{
		$objResponse->addAlert($locate->Translate("Add scheduler dial failed"));
	}
	return $objResponse->getXML();
}


function displayMap($address){
	global $config,$locate;
	$objResponse = new xajaxResponse();
	if($config['google-map']['key'] == ''){
		$objResponse->addAssign("divMap","style.visibility","hidden");
		$objResponse->addScript("alert('".$locate->Translate("google_map_no_key")."')");	
		return $objResponse;
	}
	if ($address == '')
		return $objResponse;
	$map = new PhoogleMap();
	$map->setAPIKey($config['google-map']['key']);
	$map->addAddress($address);
	//$map->showMap();
	$js = $map->generateJs();

	$objResponse->addAssign("divMap","style.visibility","visible");
	//$objResponse->addScript("alert('".$js."')");
	$objResponse->addScript($js);
	return $objResponse->getXML();
}

function customerLeadsAction($leadType,$f,$searchFormValue){
	$objResponse = new xajaxResponse();
	if(is_array($f['ckb'])){
		foreach($f['ckb'] as $vaule){
			$res_contact = astercrm::insertNewCustomerLead($vaule,$leadType,true);
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

function addTicket($customerid) {
	global $locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("ticket_detail"),"formTicketDetailDiv"); 			
	$html .= Customer::showTicketDetail($customerid);
	$html .= Table::Footer();
	$objResponse->addAssign("formTicketDetailDiv", "style.visibility", "visible");
	$objResponse->addAssign("formTicketDetailDiv", "innerHTML", $html);
	$objResponse->addScript("relateByCategory();");
	return $objResponse->getXML();
}
function relateByCategory($fid) {
	$objResponse = new xajaxResponse();
	$html = Customer::getTicketByCategory($fid);
	$objResponse->addAssign("ticketMsg", "innerHTML", $html);
	return $objResponse->getXML();
}
function relateByCategoryId($Cid,$curid=0) {
	$objResponse = new xajaxResponse();
	$option = Customer::getTicketByCategory($Cid,$curid);
	$objResponse->addAssign("ticketMsg","innerHTML",$option);
	return $objResponse->getXML();
}
function AllTicketOfMy($cid='',$Ctype,$start = 0, $limit = 5,$filter = null, $content = null, $order = null, $divName = "formMyTickets", $ordering = "",$stype = null) {
	global $locate;
	$objResponse = new xajaxResponse();

	$ticketHtml = Table::Top($locate->Translate("Customer Tickets"),"formMyTickets");
	$ticketHtml .= astercrm::createTikcetGrid($cid,$Ctype,$start, $limit,$filter, $content, $order, $divName, $ordering, $stype);
	$ticketHtml .= Table::Footer();

	$objResponse->addAssign("formMyTickets", "style.visibility", "visible");
	$objResponse->addAssign("formMyTickets", "innerHTML", $ticketHtml);

	return $objResponse->getXML();
}
function saveTicket($f) {
	global $locate;
	$objResponse = new xajaxResponse();
	if($f['ticketid'] == 0) {
		$objResponse->addAlert($locate->Translate("obligatory_fields"));
		return $objResponse->getXML();
	}
	$result = Customer::insertTicket($f);
	if($result == 1) {
		$objResponse->addAlert($locate->Translate("Add ticket success"));
		$objResponse->addAssign("formTicketDetailDiv", "style.visibility", "hidden");
		$objResponse->addScript('AllTicketOfMyself('.$f['customerid'].');');
	} else {
		$objResponse->addAlert($locate->Translate("Add ticket failed"));
	}
	return $objResponse->getXML();
}

$xajax->processRequests();

?>