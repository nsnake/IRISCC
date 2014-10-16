<?php
/*******************************************************************************
* curcdr.server.php


* Function Desc

* 功能描述

* Function Desc


* Revision 0.01  2011/11/08  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('curcdr.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');
require_once ("curcdr.common.php");

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
function createGrid($start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "", $exportFlag="",$stype=array()){
	global $locate;
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
			$numRows =& Customer::getNumRowsMore($filter, $content,"curcdr");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"curcdr");
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
	$fields[] = 'curcdr.src';
	$fields[] = 'curcdr.dst';
	$fields[] = 'curcdr.srcname';
	$fields[] = 'curcdr.starttime';
	$fields[] = 'curcdr.answertime';
	//$fields[] = 'clid.clid';
	$fields[] = 'accountgroup.groupname';
	$fields[] = 'resellergroup.resellername';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("Src").'<br>';
	$headers[] = $locate->Translate("Dst").'<br>';
	$headers[] = $locate->Translate("Srcname").'<br>';
	$headers[] = $locate->Translate("Starttime").'<br>';
	$headers[] = $locate->Translate("Answertime").'<br>';
	//$headers[] = $locate->Translate("Clid").'<br>';
	$headers[] = $locate->Translate("Groupname").'<br>';
	$headers[] = $locate->Translate("Resellername").'<br>';

	// HTML table: fieldsFromSearch showed
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'curcdr.src';
	$fieldsFromSearch[] = 'curcdr.dst';
	$fieldsFromSearch[] = 'curcdr.srcname';
	$fieldsFromSearch[] = 'curcdr.starttime';
	$fieldsFromSearch[] = 'curcdr.answertime';
	//$fieldsFromSearch[] = 'clid.clid';
	$fieldsFromSearch[] = 'accountgroup.groupname';
	$fieldsFromSearch[] = 'resellergroup.resellername';

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

	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","curcdr.src","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","curcdr.dst","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","curcdr.srcname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","curcdr.starttime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","curcdr.answertime","'.$divName.'","ORDERING");return false;\'';
	//$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","clid.clid","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","accountgroup.groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","resellergroup.resellername","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'curcdr.src';
	$fieldsFromSearch[] = 'curcdr.dst';
	$fieldsFromSearch[] = 'curcdr.srcname';
	$fieldsFromSearch[] = 'curcdr.starttime';
	$fieldsFromSearch[] = 'curcdr.answertime';
	//$fieldsFromSearch[] = 'clid.clid';
	$fieldsFromSearch[] = 'accountgroup.groupname';
	$fieldsFromSearch[] = 'resellergroup.resellername';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("Src");
	$fieldsFromSearchShowAs[] = $locate->Translate("Dst");
	$fieldsFromSearchShowAs[] = $locate->Translate("Srcname");
	$fieldsFromSearchShowAs[] = $locate->Translate("Starttime");
	$fieldsFromSearchShowAs[] = $locate->Translate("Answertime");
	//$fieldsFromSearchShowAs[] = $locate->Translate("Clid");
	$fieldsFromSearchShowAs[] = $locate->Translate("Groupname");
	$fieldsFromSearchShowAs[] = $locate->Translate("Resellername");

	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,0,1,0);
	
	$table->setAttribsCols($attribsCols);
	
	$table->deleteFlag = '1';//对导出标记进行赋值
	$table->exportFlag = '0';//对导出标记进行赋值
	$table->multiEditFlag = '0';//对批量修改标记进行赋值
	
	$table->addRowSearchMore("curcdr",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

	while ($arreglo->fetchInto($row)) {
	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc[] = $row['src'];
		$rowc[] = $row['dst'];
		$rowc[] = $row['srcname'];
		$rowc[] = $row['starttime'];
		$rowc[] = $row['answertime'];
		//$rowc[] = $row['clid'];
		$rowc[] = $row['groupname'];
		$rowc[] = $row['resellername'];
		
		$table->addRow("curcdr",$rowc,0,1,0,$divName,$fields);
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
	$optionFlag = $searchFormValue['optionFlag'];
	$deleteFlag = $searchFormValue['deleteFlag'];
	$exportFlag = $searchFormValue['exportFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];			//搜索方式 数组
	$divName = "grid";
	if($exportFlag == "1" || $optionFlag == "export"){
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'curcdr'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($deleteFlag == "1" || $optionFlag == "delete"){
		Customer::deleteFromSearch($searchContent,$searchField,$searchType,'curcdr');
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','','',$divName,"",1,$searchType);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}else{
		if($type == "delete"){
			$res = Customer::deleteRecord($id,'curcdr');
			if ($res){
				$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "",1,$searchType);
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("record deleted")); 
			}else{
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("record cannot be deleted"));
			}
		}else{
			$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField, $divName, "",1,$searchType);
		}
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}
	return $objResponse->getXML();
}

$xajax->processRequests();
?>
