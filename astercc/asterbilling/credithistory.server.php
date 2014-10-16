<?php
/*******************************************************************************
* credithistory.server.php

* credithistory 系统后台文件
* credithistory showed script

* 功能描述
	提供 credithistory 查询脚本

* Function Desc
		init				初始化页面元素
		showGrid			显示grid
		createGrid			生成grid的HTML代码
		searchFormSubmit    根据提交的搜索信息重构显示页面

********************************************************************************/

require_once ("db_connect.php");
require_once ('credithistory.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');
require_once ("credithistory.common.php");

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
*  @param	stype		string		the matching type for search 
*  @return	objResponse	object		xajax response object
*/

function showGrid($start = 0, $limit = 1,$filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$stype = null){
	
	$html .= createGrid($start, $limit,$filter, $content, $stype, $order, $divName, $ordering);
	$objResponse = new xajaxResponse();

	if($_SESSION['curuser']['usertype'] == 'clid') $objResponse->addScript("xajax_showClidCredit()");

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
*  @param	stype		string		the matching type for search 
*  @param	divName		string		which div grid want to be put
*  @param	order		string		data order
*  @return	html		string		grid HTML code
*/

function createGrid($start = 0, $limit = 1, $filter = null, $content = null, $stype = null, $order = null, $divName = "grid", $ordering = "",$stype=array()){
	global $locate;
	$_SESSION['ordering'] = $ordering;
	if($filter == null || $content == null || (!is_array($content) && $content == 'Array') || (!is_array(filter) && $filter == 'Array')){
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
		if($flag != "1" || $flag2 != "1" ){  //无值	
			$order = null;
			$numRows =& Customer::getNumRows();
			$arreglo =& Customer::getAllRecords($start,$limit,$order);
		}elseif($flag3 != 1 ){  //未选择搜索方式
			$order = "modifytime";
			$numRows =& Customer::getNumRowsMore($filter, $content,"credithistory");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"credithistory");
		}else{
			$order = "modifytime";
			$numRows =& Customer::getNumRowsMorewithstype($filter, $content,$stype,"credithistory");
			$arreglo =& Customer::getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,"credithistory");
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
	if($_SESSION['curuser']['usertype'] == 'clid'){
		// Database Table: fields
		$fields = array();
		$fields[] = 'modifytime';		
		$fields[] = 'clidid';
		$fields[] = 'srccredit';
		$fields[] = 'modifystatus';
		$fields[] = 'modifyamount';
		$fields[] = 'comment';

		// HTML table: Headers showed
		$headers = array();
		$headers[] = $locate->Translate("Modifytime");
		$headers[] = $locate->Translate("Clid");
		$headers[] = $locate->Translate("Srccredit");
		$headers[] = $locate->Translate("Modifystatus");
		$headers[] = $locate->Translate("Modifyamount");
		$headers[] = $locate->Translate("Comment");

		// HTML table: hearders attributes
		$attribsHeader = array();
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

		// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
		$eventHeader = array();
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","modifytime","'.$divName.'","ORDERING","'.$stype.'");return false;\'';		
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","clidid","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","srccredit","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","modifystatus","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","modifyamount","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","comment","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			
		
		// Select Box: fields table.
		$fieldsFromSearch = array();
		$fieldsFromSearch[] = 'modifytime';
		$fieldsFromSearch[] = 'clidid';
		$fieldsFromSearch[] = 'srccredit';
		$fieldsFromSearch[] = 'modifystatus';
		$fieldsFromSearch[] = 'modifyamount';
		$fieldsFromSearch[] = 'comment';


		// Selecct Box: Labels showed on search select box.
		$fieldsFromSearchShowAs = array();
		$fieldsFromSearchShowAs[] = $locate->Translate("modifytime");
		$fieldsFromSearchShowAs[] = $locate->Translate("clidid");
		$fieldsFromSearchShowAs[] = $locate->Translate("srccredit");
		$fieldsFromSearchShowAs[] = $locate->Translate("modifystatus");
		$fieldsFromSearchShowAs[] = $locate->Translate("modifyamount");
		$fieldsFromSearchShowAs[] = $locate->Translate("comment");

		// Create object whit 5 cols and all data arrays set before.
		$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=false,$delete=false,$detail=false);

		$table->setAttribsCols($attribsCols);
		$table->addRowSearchMore("credithistory",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$typeFromSearch,$typeFromSearchShowAs,$stype);
			
		while ($arreglo->fetchInto($row)) {
		// Change here by the name of fields of its database table

			$name =& Customer::getNames($row['operator'], $row['resellerid'], $row['groupid'],$row['clidid']);
			
			$rowc = array();
			$rowc[] = $row['id'];
			$rowc[] = $row['modifytime'];
			$rowc[] = $name['clidname'];
			$rowc[] = $row['srccredit'];
			if($row['modifystatus'] == 'add'){
				$rowc['modifystatus'] = 'refund';
			}else{
				$rowc['modifystatus'] = 'charge';
			}
			$rowc['modifyamount'] = $row['modifyamount'];
			$rowc['comment'] = $row['comment'];
			$table->addRow("credithistory",$rowc,false,false,false,$divName,$fields);
		}
	}else{
		// Database Table: fields
		$fields = array();
		$fields[] = 'modifytime';
		$fields[] = 'resellerid';
		$fields[] = 'groupid';
		$fields[] = 'clidid';
		$fields[] = 'srccredit';
		$fields[] = 'modifystatus';
		$fields[] = 'modifyamount';
		$fields[] = 'comment';
		$fields[] = 'operator';

		// HTML table: Headers showed
		$headers = array();
		$headers[] = $locate->Translate("Modifytime");
		$headers[] = $locate->Translate("Resellername");
		$headers[] = $locate->Translate("Group");
		$headers[] = $locate->Translate("Clid");
		$headers[] = $locate->Translate("Srccredit");
		$headers[] = $locate->Translate("Modifystatus");
		$headers[] = $locate->Translate("Modifyamount");
		$headers[] = $locate->Translate("Comment");
		$headers[] = $locate->Translate("Modifyby");

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

		// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
		$eventHeader = array();
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","modifytime","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","resellerid","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupid","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","clidid","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","srccredit","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","modifystatus","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","modifyamount","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","comment","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","operator","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			
		
		// Select Box: fields table.
		$fieldsFromSearch = array();
		$fieldsFromSearch[] = 'modifytime';
		$fieldsFromSearch[] = 'resellerid';
		$fieldsFromSearch[] = 'groupid';
		$fieldsFromSearch[] = 'clidid';
		$fieldsFromSearch[] = 'srccredit';
		$fieldsFromSearch[] = 'modifystatus';
		$fieldsFromSearch[] = 'modifyamount';
		$fieldsFromSearch[] = 'comment';
		$fieldsFromSearch[] = 'epayment_txn_id';
		$fieldsFromSearch[] = 'operator';

		// Selecct Box: Labels showed on search select box.
		$fieldsFromSearchShowAs = array();
		$fieldsFromSearchShowAs[] = $locate->Translate("modifytime");
		$fieldsFromSearchShowAs[] = $locate->Translate("resellerid");
		$fieldsFromSearchShowAs[] = $locate->Translate("groupid");
		$fieldsFromSearchShowAs[] = $locate->Translate("clidid");
		$fieldsFromSearchShowAs[] = $locate->Translate("srccredit");
		$fieldsFromSearchShowAs[] = $locate->Translate("modifystatus");
		$fieldsFromSearchShowAs[] = $locate->Translate("modifyamount");
		$fieldsFromSearchShowAs[] = $locate->Translate("comment");
		$fieldsFromSearchShowAs[] = $locate->Translate("txn id");
		$fieldsFromSearchShowAs[] = $locate->Translate("operator");

		// Create object whit 5 cols and all data arrays set before.
		$table = new ScrollTable(10,$start,$limit,$filter,$numRows,$content,$order);
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=false,$delete=false,$detail=false);

		if ($_SESSION['curuser']['usertype'] == 'admin') $table->deleteFlag = '1';//对删除标记进行赋值

		$table->exportFlag = '1';//对导出标记进行赋值

		$table->setAttribsCols($attribsCols);
		$table->addRowSearchMore("credithistory",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$typeFromSearch,$typeFromSearchShowAs,$stype);
			
		while ($arreglo->fetchInto($row)) {
		// Change here by the name of fields of its database table

			$name =& Customer::getNames($row['operator'], $row['resellerid'], $row['groupid'],$row['clidid']);
			
			$rowc = array();
			$rowc[] = $row['id'];
			$rowc[] = $row['modifytime'];
			$rowc[] = $name['resellername']."(".$row['resellerid'].")";
			$rowc[] = $name['groupname']."(".$row['groupid'].")";
			$rowc[] = $name['clidname']."(".$row['clidid'].")";
			$rowc[] = $row['srccredit'];
			if($row['modifystatus'] == 'add'){
				$rowc['modifystatus'] = 'refund';
			}else{
				$rowc['modifystatus'] = 'charge';
			}
			$rowc['modifyamount'] = $row['modifyamount'];
			if($row['epayment_txn_id'] != ''){
				$rowc['comment'] = $row['comment'].'('.$row['epayment_txn_id'].')';
			}else{
				$rowc['comment'] = $row['comment'];
			}
			$rowc[] = $name['username'];
			$table->addRow("credithistory",$rowc,false,false,false,$divName,$fields);
		}
 	}
 	// End Editable Zone
 	$html = $table->render();
 	
 	return $html;
}

function searchFormSubmit($searchFormValue,$numRows,$limit,$id,$type){
	global $locate,$db;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$searchType = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];			//搜索方式 数组
	$divName = "grid";
	if($optionFlag == "export"){
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'credithistory'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($optionFlag == "delete"){
		astercrm::deletefromsearch($searchContent,$searchField,$searchType,'credithistory');
		$html = createGrid($numRows,$limit,'','','','','',$divName,"",'');
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}elseif($type == "delete"){
		$res = Customer::deleteRecord($id,'credithistory');
		if ($res){
			$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "",$searchType);
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec"));
			$objResponse->addAssign($divName, "innerHTML", $html);
		}else{
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
		}
	}else{
		$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchType, $searchField[count($searchField)-1], $divName, "",$searchType);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}
	
	return $objResponse->getXML();
}
function showClidCredit() {

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divClid", "style.visibility", "visible");
	$clid = astercrm::getRecordById($_SESSION['curuser']['clidid'],'clid');
	if($clid['limittype'] == ''){
		$limit = 'no limit';
	}else{
		$limit = $clid['creditlimit'];
	}
	$objResponse->addAssign('spanCost', "innerHTML", $clid['credit_clid']);
	$objResponse->addAssign("spanLimit", "innerHTML", $limit);
	$objResponse->addAssign("spancurcredit", "innerHTML", $clid['curcredit']);
	return $objResponse;
}
$xajax->processRequests();
?>
