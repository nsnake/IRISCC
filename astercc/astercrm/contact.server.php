<?php
/*******************************************************************************
* contact.server.php

* 联系人管理系统后台文件
* contact background management script

* Function Desc
	provide contact management script

* 功能描述
	提供联系人管理脚本

* Function Desc

	init				初始化页面元素
	createGrid			生成grid的HTML代码
	showDetail			显示contact信息
	searchFormSubmit    根据提交的搜索信息重构显示页面

* Revision 0.0451  2007/10/22 16:45:00  last modified by solo
* Desc: remove Edit and Detail tab in xajaxGrid

* Revision 0.045  2007/10/22 16:45:00  last modified by solo
* Desc: remove function "importCSV", "export"

* Revision 0.045  2007/10/18 14:30:00  last modified by solo
* Desc: remove function "edit"

* Revision 0.045  2007/10/18 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/
require_once ("db_connect.php");
require_once ("contact.common.php");
require_once ('contact.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');
require_once ('astercrm.server.common.php');
require_once ('include/asterisk.class.php');


/**
*  initialize page elements
*
*/

function init(){
	global $locate;//,$config,$db;

	$objResponse = new xajaxResponse();

	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	$objResponse->addAssign("btnCustomer","value",$locate->Translate("customer"));
	$objResponse->addAssign("btnNote","value",$locate->Translate("note"));

	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','')");

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
			$numRows =& Customer::getNumRowsMore($filter, $content,"contact");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"contact");
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
	$fields[] = 'contact';
	$fields[] = 'gender';
	$fields[] = 'position';
	$fields[] = 'phone';
	$fields[] = 'mobile';
	$fields[] = 'email';
	$fields[] = 'customer';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ALL")."<input type='checkbox' onclick=\"ckbAllOnClick(this);\"><BR \>";//"select all for delete";
	$headers[] = $locate->Translate("contact");//"Customer Name";
	$headers[] = $locate->Translate("gender");//"Customer Name";
	$headers[] = $locate->Translate("position");//"Category";
	$headers[] = $locate->Translate("phone");//"Contact";
	$headers[] = $locate->Translate("mobile");//"Category";
	$headers[] = $locate->Translate("email");//"Note";
	$headers[] = $locate->Translate("customer_name");
	$headers[] = $locate->Translate("note");

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="4%"';
	$attribsHeader[] = 'width="18%"';
	$attribsHeader[] = 'width="7%"';
	$attribsHeader[] = 'width="8%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="20%"';
	$attribsHeader[] = 'width="15%"';
	$attribsHeader[] = 'width="10%"';

	// HTML Table: columns attributes
	$attribsCols = array();
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'nowrap style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= '';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","contact","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","gender","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","position","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","phone","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","mobile","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","email","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","customer","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","note","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'contact.contact';
	$fieldsFromSearch[] = 'contact.gender';
	$fieldsFromSearch[] = 'contact.position';
	$fieldsFromSearch[] = 'contact.phone';
	$fieldsFromSearch[] = 'contact.mobile';
	$fieldsFromSearch[] = 'contact.email';
	$fieldsFromSearch[] = 'customer.customer';
	$fieldsFromSearch[] = 'contact.creby';
	$fieldsFromSearch[] = 'note.note';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("contact");
	$fieldsFromSearchShowAs[] = $locate->Translate("gender");
	$fieldsFromSearchShowAs[] = $locate->Translate("position");
	$fieldsFromSearchShowAs[] = $locate->Translate("phone");
	$fieldsFromSearchShowAs[] = $locate->Translate("mobile");
	$fieldsFromSearchShowAs[] = $locate->Translate("email");
	$fieldsFromSearchShowAs[] = $locate->Translate("customer_name");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_by");
	$fieldsFromSearchShowAs[] = $locate->Translate("note");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	$table->exportFlag = '2';//对导出标记进行赋值
	$table->deleteFlag = '1';
	$table->ordering = $ordering;

	$deleteFlag = 1;
	$deleteBtnFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['contact']['delete']) {
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
	$table->addRowSearchMore("contact",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$deleteBtnFlag,$typeFromSearch,$typeFromSearchShowAs,$stype);
	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc['select_id'] = $row['id'];
		$rowc[] = '<a href=? onclick="xajax_showContact(\''.$row['id'].'\');return false;">'.$row['contact'].'</a>';
		$rowc[] = $row['gender'];
		$rowc[] = $row['position'];
		$rowc[] = $row['phone'];
		$rowc[] = $row['mobile'];
		$rowc[] = $row['email'];
		if ($row['customer'] == '')
			$rowc[] = $row['customer'];
		else
			$rowc[] = "<a href=? onclick=\"xajax_showCustomer('".$row['customerid']."','customer');return false;\"
		>".$row['customer']."</a>";
		$rowc[] = $row['note'];
		$table->addRow("contact",$rowc,0,$deleteFlag,0,$divName,$fields);
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render('delGrid');
 	
 	return $html;
}


/**
*  show contact record detail
*  @param	contactid	int			contact id
*  @return	objResponse	object		xajax response object
*/

function showDetail($contactid){
	global $locate;
	$objResponse = new xajaxResponse();
	if($contactid != null){
		$html = Table::Top($locate->Translate("contact_detail"),"formContactInfo"); 			
		$html .= Customer::showContactRecord($contactid); 		
		$html .= Table::Footer();
		$objResponse->addAssign("formContactInfo", "style.visibility", "visible");
		$objResponse->addAssign("formContactInfo", "innerHTML", $html);	
	}
	return $objResponse->getXML();
}

function searchFormSubmit($searchFormValue,$numRows = null,$limit = null,$id = null,$type = null){
	global $locate,$db;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$exportFlag = $searchFormValue['exportFlag'];  //导出标记
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];
	$ordering = $searchFormValue['ordering'];
	$order = $searchFormValue['order'];
	$divName = "grid";
	if($optionFlag == "export"  || $optionFlag == "exportcsv"){
		//$sql = astercrm::getSql($searchContent,$searchField,$searchType,'contact'); //得到要导出的sql语句
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'contact',array('contact.*','note.note'),array('note'=>array('contact.id','note.contactid')));
		
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addAssign("maintable", "value", 'contact');
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($optionFlag == "delete"){
		astercrm::deletefromsearch($searchContent,$searchField,$searchType,'contact');
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','','',$divName,"",'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}else{
		if($type == "delete"){
			$res = Customer::deleteRecord($id,'contact');
			if ($res){
				$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $order, $divName, $ordering,$searchType);
				$objResponse = new xajaxResponse();
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
			}else{
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
			}
		}else{
			$html = createGrid($numRows, $limit,$searchField, $searchContent, $order, $divName, $ordering,$searchType);
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
			$res_note = astercrm::deleteRecords('contactid',$vaule,'note');
			$res_customer = astercrm::deleteRecord($vaule,'contact');
		}
	}
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$numRows = $searchFormValue['numRows'];
	$limit = $searchFormValue['limit'];     
	$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField,'grid');
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

$xajax->processRequests();

?>