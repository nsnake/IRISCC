<?php
/*******************************************************************************
* survey.server.php

* Function Desc
	provide survey management script

* 功能描述
	提供问卷管理脚本

* Function Desc

	showGrid
	export				提交表单, 导出contact数据
	init				初始化页面元素
	createGrid			生成grid的HTML代码
	delete
	edit
	editField
	updateField
	showDetail
	add
	setSurvey
	save


* Revision 0.045  2007/10/18 15:38:00  last modified by solo
* Desc: comment added

********************************************************************************/
require_once ("db_connect.php");
require_once ("survey.common.php");
require_once ('survey.grid.inc.php');
require_once ('include/asterevent.class.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');


function showGrid($start = 0, $limit = 1,$filter = null, $content = null, $order = null, $divName = "grid", $ordering = ""){
	
	$html = createGrid($start, $limit,$filter, $content, $order, $divName, $ordering);
	$objResponse = new xajaxResponse();
	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign($divName, "innerHTML", $html);
	
	return $objResponse->getXML();
}

function init(){
	global $locate;//,$config,$db;

	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	$objResponse->addScript("xajax_showGrid(0,".ROWSXPAGE.",'','','')");

	return $objResponse;
}

//	create grid
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
			$numRows =& Customer::getNumRows($_SESSION['curuser']['groupid']);
			$arreglo =& Customer::getAllRecords($start,$limit,$order,$_SESSION['curuser']['groupid']);
		}elseif($flag3 != 1){
			$order = "id";
			$numRows =& Customer::getNumRowsMore($filter, $content,"survey");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"survey");
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
	$fields[] = 'surveyname';
	$fields[] = 'groupname';
	$fields[] = 'campaignname';
	$fields[] = 'cretime';
	$fields[] = 'creby';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("ID");
	$headers[] = $locate->Translate("survey_title");
	$headers[] = $locate->Translate("Group Name");
	$headers[] = $locate->Translate("Campaign Name");
	$headers[] = $locate->Translate("create_time");
	$headers[] = $locate->Translate("create_by");

	// HTML table: hearders attributes
	$attribsHeader = array();
	$attribsHeader[] = 'width="10%"';
	$attribsHeader[] = 'width="25%"';
	$attribsHeader[] = 'width="15%"';
	$attribsHeader[] = 'width="20%"';
	$attribsHeader[] = 'width="15%"';
	$attribsHeader[] = 'width="15%"';

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
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","id","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","surveyname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","campaignname","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","cretime","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","creby","'.$divName.'","ORDERING");return false;\'';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'id';
	$fieldsFromSearch[] = 'surveyname';
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'campaignname';
	$fieldsFromSearch[] = 'survey.cretime';
	$fieldsFromSearch[] = 'survey.creby';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("id");
	$fieldsFromSearchShowAs[] = $locate->Translate("survey_title");
	$fieldsFromSearchShowAs[] = $locate->Translate("Group Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("Campaign Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_time");
	$fieldsFromSearchShowAs[] = $locate->Translate("create_by");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	$table->exportFlag = '2';//对导出标记进行赋值
	$table->ordering = $ordering;

	$editFlag = 1;
	$deleteFlag = 1;
	if($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin') {
		if($_SESSION['curuser']['privileges']['survey']['delete']) {
			$deleteFlag = 1;
		} else {
			$deleteFlag = 0;
		}
		if($_SESSION['curuser']['privileges']['survey']['edit']) {
			$editFlag = 1;
		}else {
			$editFlag = 0;
		}
	}
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$editFlag,$deleteFlag,1);
	$table->setAttribsCols($attribsCols);
	$table->addRowSearchMore("survey",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

	while ($arreglo->fetchInto($row)) {

	// Change here by the name of fields of its database table
		$rowc = array();
		$rowc[] = $row['id'];
		$rowc[] = $row['id'];
		if ($row['enable'] == 1)
			$rowc[] = $row['surveyname'];
		else
			$rowc[] = "<font color=gray>".$row['surveyname']."</font>";
		$rowc[] = $row['groupname'];
		$rowc[] = $row['campaignname'];
		$rowc[] = $row['cretime'];
		$rowc[] = $row['creby'];
//		$rowc[] = 'Detail';
		$table->addRow("survey",$rowc,$editFlag,$deleteFlag,1,$divName,$fields);
 	}
 	
 	// End Editable Zone
 	
 	$html = $table->render();
 	
 	return $html;
}

function edit($surveyid = 0, $optionid = 0){
	global $locate;
	if ($surveyid == 0)
		return ;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("edit_survey"),"formDiv");  
	$html .= Customer::formAdd($surveyid, $optionid);
	$html .= Table::Footer();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);
	$objResponse->addScript("xajax.$('surveyoption').focus();");

	return $objResponse->getXML();
}

function editField($table, $field, $cell, $value, $id){
	$objResponse = new xajaxResponse();
	
	$html =' <input type="text" id="input'.$cell.'" value="'.$value.'" size="'.(strlen($value)+5).'"'
			.' onBlur="xajax_updateField(\''.$table.'\',\''.$field.'\',\''.$cell.'\',document.getElementById(\'input'.$cell.'\').value,\''.$id.'\');"'
			.' style="background-color: #CCCCCC; border: 1px solid #666666;">';
	$objResponse->addAssign($cell, "innerHTML", $html);
	$objResponse->addScript("document.getElementById('input$cell').focus();");
	return $objResponse->getXML();
}

function updateField($table, $field, $cell, $value, $id){
	global $locate;
	$objResponse = new xajaxResponse();
	$objResponse->addAssign($cell, "innerHTML", $value);
	Customer::updateField($table,$field,$value,$id);
	if ($table == 'survey'){
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("survey_updated"));
	}else{
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("option_updated"));
	}

	return $objResponse->getXML();
}

function showItem($optionid){
	global $locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("Option Item"),"itemDiv");  
	$html .= Customer::showItem($optionid);
	$html .= Table::Footer();
	$objResponse->addAssign("itemDiv", "style.visibility", "visible");
	$objResponse->addAssign("itemDiv", "innerHTML", $html);
	return $objResponse->getXML();
}

function add($surveyid = 0){
	global $locate;
	$objResponse = new xajaxResponse();

	$html = Table::Top($locate->Translate("add_survey"),"formDiv");  
	$html .= Customer::formAdd($surveyid);
	$html .= Table::Footer();
	$objResponse->addAssign("formDiv", "style.visibility", "visible");
	$objResponse->addAssign("formDiv", "innerHTML", $html);

	if ($surveyid == 0 ){
		$objResponse->addScript("xajax.$('surveyname').focus();");
	}else{
		$objResponse->addScript("xajax.$('surveyoption').focus();");
	}

	return $objResponse->getXML();
}



function setSurvey($survey){
	global $locate;
//	print_r($survey);
//	exit;
	$objResponse = new xajaxResponse();
	#if ($survey['radEnable'] == 1)
	#	Customer::setSurveyEnable(0,1,$survey['groupid']);

	Customer::setSurveyEnable($survey['surveyid'],$survey['radEnable']);
	Customer::updateField('survey','groupid',$survey['groupid'],$survey['surveyid']);
	Customer::updateField('survey','campaignid',$survey['campaignid'],$survey['surveyid']);

//	print $surveyenable;

	$html = createGrid(0,ROWSXPAGE);
	$objResponse->addAssign("grid", "innerHTML", $html);
	$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("survey_updated"));
	$objResponse->addAssign("formDiv", "style.visibility", "hidden");
	$objResponse->addClear("formDiv", "innerHTML");

//	$objResponse->addAlert($locate->Translate("survey_updated"));
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
	if($optionFlag == "export" || $optionFlag == "exportcsv"){
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'survey'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("maintable", "value", 'survey'); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($type == "delete"){
		$res = Customer::deleteRecord($id,'survey');
		$res = Customer::deleteRecords("surveyid",$id,'surveyoptions');
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

function showDetail($surveyid){
	global $db,$locate;
	$objResponse = new xajaxResponse();

	$sql = "SELECT * FROM account";
	$resAccount =& $db->query($sql);
	if (!$resAccount)
		return;

	$ind = 0;

	$html .= "<div style='display: block;clear: both; float:none;'>";
	while ($resAccount->fetchInto($account)){

		$html .= "<div><table width=300px align=left>";
		$html .= "<tr><th align='left' colspan='2'>".$locate->Translate("agent").": ".$account['username']."</th></tr>";
		$sql = "SELECT COUNT(*) as number, surveyoption, surveynote, itemcontent FROM surveyresult WHERE creby = '".$account['username']."' AND surveyid = $surveyid GROUP BY surveyoption,surveynote";
		
		$res =& $db->query($sql);
		if ($res){
			$html .= "<tr><td>".$locate->Translate("Item")."</td><td>".$locate->Translate("Note")."</td><td>".$locate->Translate("number")."</td></tr>";

			while ($res->fetchInto($row)){
				$html .= "<tr><td>".$row['itemcontent']."</td><td>".$row['surveynote']."</td><td>".$row['number']."</td></tr>";
			}
		}
		$html .= "</table></div>";
	}
	$html .= "</div>";

//	print $html;
//	exit;

	$html .= "<div style='display: block;clear: both; float:none;'>";
	
	$query = "SELECT * FROM surveyresult  WHERE surveyid = $surveyid  GROUP BY surveyoption";
	$surveyoptions = $db->query($query);
	while ($surveyoptions->fetchInto($surveyoption)){
		$sql = "SELECT COUNT(*) as number, itemcontent,surveynote FROM surveyresult WHERE surveyoptionid = '".$surveyoption['surveyoptionid']."' GROUP BY itemcontent";
		$totalrecords = 0;
		$res =& $db->query($sql);

			if ($res){
				$html .= "<div><table width=300 align=left>";
				$html .= "<tr><td colspan=2 align=left><strong>".$surveyoption['surveyoption']." </strong></td></tr>";
				$html .= "<tr><td width=250px>Option</td><td width=50px>Number</td></tr>";

				while ($res->fetchInto($row)){
					if ($row['itemcontent'] == ""){
						$item = $row['surveynote'];
					}else{
						$item = $row['itemcontent'];
					}
					$html .= "<tr><td>".$item."</td><td>".$row['number']."</td></tr>";
					$totalrecords += $row['number'];
				}
				$html .= "<tr><td colspan=2>".$locate->Translate("total").": ".$totalrecords."</td></tr>";
				$html .= "</table></div>";
			}
	}
	$html .= "</div>";



	$objResponse->addAssign("divSurveyStatistc", "innerHTML", $html);

	return $objResponse;
}

function updateOption($f,$optionid){
		global $locate;
		$objResponse = new xajaxResponse();
		Customer::updateOptionRecord($f,$optionid);
		$html = createGrid(0,ROWSXPAGE);
		$objResponse->addAssign("grid", "innerHTML", $html);
		$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("option updated"));
		$objResponse->addScript("xajax_edit('".$f['surveyid']."')");

		return $objResponse->getXML();

}

function addItem($f){
		global $locate,$db;
		$objResponse = new xajaxResponse();
		$query = "INSERT INTO surveyoptionitems SET
										optionid = '".$f['optionid']."', 
										itemtype = '".$f['optiontype']."', 
										itemcontent = ".$db->quote($f['itemcontent']).", 
										cretime = now(), 
										creby = '".$_SESSION['curuser']['username']."' ";
		$db->query($query);
		$objResponse->addScript("showItem('".$f['optionid']."');");
		return $objResponse;
}

	function save($f){
		global $locate;

		$objResponse = new xajaxResponse();
		if ($f['surveyid'] == 0){
			if ($f['surveyname'] == ''){
				$objResponse->addAlert($locate->Translate("please_enter_survey"));
				$objResponse->addScript("xajax.$('surveyname').focus();");
				return $objResponse;
			}else{
				$surveyid = Customer::insertNewSurvey($f); 
				$html = createGrid(0,ROWSXPAGE);
				$objResponse->addAssign("grid", "innerHTML", $html);
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("survey_added"));
			}
		}
		else
			$surveyid = $f['surveyid'];


		if ($surveyid == 0){
			return $objResponse;
		}else{
			if ($f['surveyoption'] != ''){
				$surveyoptionid = Customer::insertNewOption($f,$surveyid);
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("option_added"));
//				$objResponse->addAlert($locate->Translate("option_added"));
			}
		}
		$objResponse->addScript("xajax_add('".$surveyid."')");

		return $objResponse->getXML();
	}

function delete($id = null, $table){
	global $locate;
	$res = Customer::deleteRecord($id,$table);
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

function setCampaign($groupid){
	global $locate;
	$objResponse = new xajaxResponse();
	$res = Customer::getRecordsByGroupid($groupid,"campaign");
	//添加option
	$objResponse->addScript("addSltOption('campaignid','0','".$locate->Translate("All")."');");
	while ($res->fetchInto($row)) {
		$objResponse->addScript("addSltOption('campaignid','".$row['id']."','".$row['campaignname']."');");
	}

	return $objResponse;
}

$xajax->processRequests();

?>