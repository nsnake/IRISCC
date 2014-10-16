<?php
/*******************************************************************************
* resellerrate.server.php


* Function Desc

* 功能描述

* Function Desc


* Revision 0.01  2007/11/21 12:40:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('resellerrate.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');
require_once ("resellerrate.common.php");

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
			$numRows =& Customer::getNumRowsMore($filter, $content,"resellerrate");
			$arreglo =& Customer::getRecordsFilteredMore($start, $limit, $filter, $content, $order,"resellerrate");
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
	$headers[] = $locate->Translate("Reseller").'<br>';
	$headers[] = $locate->Translate("Addtime").'<br>';

	// HTML table: fieldsFromSearch showed
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'dialprefix';
	$fieldsFromSearch[] = 'numlen';
	$fieldsFromSearch[] = 'destination';
	$fieldsFromSearch[] = 'rateinitial';
	$fieldsFromSearch[] = 'initblock';
	$fieldsFromSearch[] = 'billingblock';
	$fieldsFromSearch[] = 'connectcharge';
	$fieldsFromSearch[] = 'resellername';
	$fieldsFromSearch[] = 'addtime';

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
	$fieldsFromSearch[] = 'resellername';
	$fieldsFromSearch[] = 'resellerrate.addtime';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("prefix");
	$fieldsFromSearchShowAs[] = $locate->Translate("length");
	$fieldsFromSearchShowAs[] = $locate->Translate("destination");
	$fieldsFromSearchShowAs[] = $locate->Translate("rate");
	$fieldsFromSearchShowAs[] = $locate->Translate("init block");
	$fieldsFromSearchShowAs[] = $locate->Translate("billing block");
	$fieldsFromSearchShowAs[] = $locate->Translate("connect charge");
	$fieldsFromSearchShowAs[] = $locate->Translate("reseller");
	$fieldsFromSearchShowAs[] = $locate->Translate("addtime");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(6,$start,$limit,$filter,$numRows,$content,$order);
	if ($_SESSION['curuser']['usertype'] == 'admin' ){
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,1,1,0);
		$table->deleteFlag = '1';//对导出标记进行赋值
		$table->multiEditFlag = '1';//对批量修改标记进行赋值		
	}else{
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,0,0,0);
	}
	$table->setAttribsCols($attribsCols);
	$table->exportFlag = '1';//对导出标记进行赋值
	
	if ($_SESSION['curuser']['usertype'] == 'admin')
		$table->addRowSearchMore("resellerrate",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,$typeFromSearch,$typeFromSearchShowAs,$stype);
	else
		$table->addRowSearchMore("resellerrate",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

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
		$rowc[] = $row['resellername'];
		$rowc[] = $row['addtime'];
		if ($_SESSION['curuser']['usertype'] == 'admin')
			$table->addRow("resellerrate",$rowc,1,1,0,$divName,$fields);
		else
			$table->addRow("resellerrate",$rowc,0,0,0,$divName,$fields);
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
	$html = Table::Top($locate->Translate("add rate"),"formDiv");  // <-- Set the title for your form.
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

	// check if clid duplicate
	$res = astercrm::checkRateDuplicate("resellerrate",$f,"insert");
	if ($res != ''){
		$objResponse->addAlert("rate duplicate");
		return $objResponse->getXML();
	}
	
	if($config['synchronize']['id_autocrement_byset']){
		$local_lastid = astercrm::getLocalLastId('resellerrate');
		$f['id'] = intval($local_lastid+1);
	}

	$respOk = Customer::insertNewRate($f); // add a new account
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

	$res = astercrm::checkRateDuplicate("resellerrate",$f,"update");
	if ($res != ''){
		$objResponse->addAlert("rate duplicate");
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
	if($exportFlag == "1" || $optionFlag == "export"){
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'resellerrate'); //得到要导出的sql语句
		$_SESSION['export_sql'] = $sql;
		
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}elseif($deleteFlag == "1" || $optionFlag == "delete"){
		if(empty($_SESSION['curuser']['usertype'])){
			$objResponse->addAlert($locate->Translate("Session time out,please try again"));
			return $objResponse->getXML();
		}

		if($config['synchronize']['delete_by_use_history']){
			astercrm::deleteToHistoryFromSearch($searchContent,$searchField,$searchType,'resellerrate');
		} else {
			astercrm::deletefromsearch($searchContent,$searchField,$searchType,'resellerrate');
		}
		
		$html = createGrid($searchFormValue['numRows'], $searchFormValue['limit'],'','','',$divName,"",1,$searchType);
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}elseif($optionFlag == "multiEdit"){
		$html = createGrid($numRows, $limit,$searchField, $searchContent, $searchField, $divName, "",1,1,$searchType);
		$showMutiEdit = Table::Top($locate->Translate("Multi Edit"),"formDiv");
		$showMutiEdit .= astercrm::formMutiEdit($searchContent,$searchField,$searchType,'resellerrate');
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
				$res = Customer::deleteRecordToHistory('id',$id,'resellerrate');
			} else {
				$res = Customer::deleteRecord($id,'resellerrate');
			}
			
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

	$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType);
	
	list($field,$fieldType) = split(',',$f['multieditField']);

	$sucessNum = 0;

	if($f['multioption'] == 'modify'){

		$query = "SELECT id,".trim($field)." FROM ".$table." WHERE 1 ".$joinstr.$resellerJoinStr;
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
					$updateSql .= ", resellerid =".$f['resellerid'];
				}
					$updateSql .= ",addtime = now() WHERE id = ".$row['id'];
			}else{
				if( $f['resellerid'] != '' ){
					$updateSql ="UPDATE ".$table." SET resellerid =".$f['resellerid'].",addtime = now() WHERE id = ".$row['id'];
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

		$query = "SELECT * FROM ".$table." WHERE 1 ".$joinstr.$resellerJoinStr;
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
				
				$insertField .= "resellerid";
				
				if( $f['resellerid'] != '' ){					
					$insertValue .= $f['resellerid'];
				}else{
					$insertValue .= $row['resellerid'];
				}		
			
			}else{
				foreach($row as $key => $value){
					if(!preg_match("/id$/",$key) && $key != 'addtime' ){
						$insertField .= $key.",";
						$insertValue .= "'".$value."',";						
					}
				}

				if( $f['resellerid'] != '' ){
					$insertField .= " resellerid";
					$insertValue .= $f['resellerid'];
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

$xajax->processRequests();
?>
