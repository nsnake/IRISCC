<?php
/*******************************************************************************
* useronline.server.php

* Function Desc
	provide note management script

* 功能描述
	提供备注管理脚本

* Function Desc

	export				提交表单, 导出contact数据
	init				初始化页面元素
	createGrid			生成grid的HTML代码
	searchFormSubmit    根据提交的搜索信息重构显示页面

********************************************************************************/
require_once ("db_connect.php");
require_once ('useronline.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');
require_once ('astercrm.server.common.php');


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

	$objResponse->addAssign("btnCustomer","value",$locate->Translate("customer"));
	$objResponse->addAssign("btnUseronlineReport","value",$locate->Translate("Useronline Report"));

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
			$numRows =& Customer::getNumRowsMore($filter, $content,"astercrm_accout");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"astercrm_accout");
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
	$fields[] = 'login_time';
	$fields[] = 'online_time';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ALL")."<input type='checkbox' onclick=\"ckbAllOnClick(this);\">";//"select all for delete";
	$headers[] = $locate->Translate("username");
	$headers[] = $locate->Translate("login time");
	$headers[] = $locate->Translate("online time");//"Customer Name";

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="5%"';
	$attribsHeader[] = 'width="30%"';
	$attribsHeader[] = 'width="30%"';
	$attribsHeader[] = 'width="30%"';

	// HTML Table: columns attributes
	$attribsCols = array();
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= '';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","username","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= '';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","last_login_time","'.$divName.'","ORDERING");return false;\'';
	

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'username';
	$fieldsFromSearch[] = 'last_login_time';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("username");
	$fieldsFromSearchShowAs[] = $locate->Translate("login time");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,0,0,0);
	$table->setAttribsCols($attribsCols);
	$table->exportFlag = '2';//对导出标记进行赋值
	//$table->deleteFlag = '1';
	$table->ordering = $ordering;
	//$table->addRowSearchMore("note",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content);
	$table->addRowSearchMore("astercrm_accout",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,0,$typeFromSearch,$typeFromSearchShowAs,$stype);
	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc['select_id'] = $row['id'];
		$rowc[] = $row['username'];
		$rowc[] = astercrm::FormatSec(strtotime(date("Y-m-d H:i:s"))-strtotime($row['last_login_time']));
		$rowc[] = $row['last_login_time'];
		$table->addRow("astercrm_accout",$rowc,0,0,0,$divName,$fields);
 	}
 	
 	// End Editable Zone
 	$html = $table->render('delGrid');
 	return $html;
}

function searchFormSubmit($searchFormValue,$numRows = null,$limit = null,$id = null,$type = null){
	global $locate,$db;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$exportFlag = $searchFormValue['exportFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$ordering = $searchFormValue['ordering'];
	$order = $searchFormValue['order'];
	$divName = "grid";
	$searchType =  $searchFormValue['searchType'];
	if($optionFlag == "export" || $optionFlag == "exportcsv"){
		$sql =& Customer::getOnlineSql($searchContent,$searchField,$searchType,'astercrm_account',array('username','(UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_login_time))'=>'onlinetime','last_login_time')); //得到要导出的sql语句
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addAssign("maintable", "value", "note_leads");//传递主表名，防止groupid等字段在各表中重复
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}else{
		$html = createGrid($numRows, $limit,$searchField, $searchContent, $order, $divName, $ordering,$searchType);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}
	return $objResponse->getXML();
}


$xajax->processRequests();

?>