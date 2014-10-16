<?php
/*******************************************************************************
* MonitorRecords.server.php

* Function Desc
	provide trunkinfo management script

* 功能描述
	提供问卷管理脚本

* Function Desc

	showGrid
	init				初始化页面元素
	createGrid			生成grid的HTML代码
	
* Revision 0.045  2007/10/18 15:38:00  last modified by solo
* Desc: comment added

********************************************************************************/
require_once ("db_connect.php");
require_once ("monitorrecords.common.php");
require_once ('monitorrecords.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');


function showGrid($start = 0, $limit = 1,$filter = null, $content = null, $order = null, $divName = "grid", $ordering = ""){
	
	$html = createGrid('',$start, $limit,$filter, $content, $order, $divName, $ordering);
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
function createGrid($customerid='',$start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$stype=array()){
		global $locate;
		$_SESSION['ordering'] = $ordering;
		if($filter == null || $content == null || (!is_array($content) && $content == 'Array') || (!is_array(filter) && $filter == 'Array')){
			$content = null;
			$filter = null;
			$numRows =& astercrm::getRecNumRows($customerid);
			$arreglo =& astercrm::getAllRecRecords($customerid,$start,$limit,$order);
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
				if(is_array($order) || $order == '') $order = null;
				$numRows =& astercrm::getRecNumRows($customerid);
				$arreglo =& astercrm::getAllRecRecords($customerid,$start,$limit,$order);
			}elseif($flag3 != 1 ){  //未选择搜索方式
				$order = "monitorrecord.id";
				$numRows =& astercrm::getRecNumRowsMore($customerid,$filter, $content);
				$arreglo =& astercrm::getRecRecordsFilteredMore($customerid,$start, $limit, $filter, $content, $order);
			}else{
				$order = "monitorrecord.id";
				$numRows =& astercrm::getRecNumRowsMorewithstype($customerid,$filter, $content,$stype);
				$arreglo =& astercrm::getRecRecordsFilteredMorewithstype($customerid,$start, $limit, $filter, $content, $stype,$order);
			}
		}	
		// Databse Table: fields
		$fields = array();
		$fields[] = 'calldate';
		$fields[] = 'src';
		$fields[] = 'dst';
		$fields[] = 'didnumber';
		$fields[] = 'dstchannel';
		$fields[] = 'duration';
		$fields[] = 'billsec';
		$fields[] = 'filename';
		$fields[] = 'creby';

		// HTML table: Headers showed
		$headers = array();
		$headers[] = $locate->Translate("Calldate");
		$headers[] = $locate->Translate("Src");
		$headers[] = $locate->Translate("Dst");
		$headers[] = $locate->Translate("Callee Id");
		$headers[] = $locate->Translate("Agent");
		$headers[] = $locate->Translate("Duration");
		$headers[] = $locate->Translate("Billsec");
		$headers[] = $locate->Translate("filename");
		$headers[] = $locate->Translate("creby");

		// HTML table: hearders attributes
		$attribsHeader = array();
		$attribsHeader[] = 'width="13%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="13%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="12%"';
		$attribsHeader[] = 'width="10%"';


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
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.calldate","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.src","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.dst","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.didnumber","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.dstchannel","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.duration","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.billsec","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","monitorrecord.filename","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","monitorrecord.creby","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		
		
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

		// Select Box: fields table.
		$fieldsFromSearch = array();
		$fieldsFromSearch[] = 'src';
		$fieldsFromSearch[] = 'calldate';
		$fieldsFromSearch[] = 'extension';
		$fieldsFromSearch[] = 'dst';
		$fieldsFromSearch[] = 'didnumber';
		$fieldsFromSearch[] = 'dstchannel';
		$fieldsFromSearch[] = 'billsec';
		$fieldsFromSearch[] = 'filename';
		$fieldsFromSearch[] = 'creby';

		// Selecct Box: Labels showed on search select box.
		$fieldsFromSearchShowAs = array();
		$fieldsFromSearchShowAs[] = $locate->Translate("src");
		$fieldsFromSearchShowAs[] = $locate->Translate("calldate");
		$fieldsFromSearchShowAs[] = $locate->Translate("exten");
		$fieldsFromSearchShowAs[] = $locate->Translate("dst");
		$fieldsFromSearchShowAs[] = $locate->Translate("callee id");
		$fieldsFromSearchShowAs[] = $locate->Translate("agent");
		$fieldsFromSearchShowAs[] = $locate->Translate("billsec");
		$fieldsFromSearchShowAs[] = $locate->Translate("filename");
		$fieldsFromSearchShowAs[] = $locate->Translate("creby");

		// Create object whit 5 cols and all data arrays set before.
		$table = new ScrollTable(7,$start,$limit,$filter,$numRows,$content,$order,$customerid,'','','monitorrecord');
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=false,$delete=false,$detail=false);
		$table->setAttribsCols($attribsCols);
		$table->ordering = $ordering;
		$table->addRowSearchMore("monitorrecord",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

		while ($arreglo->fetchInto($row)) {
		// Change here by the name of fields of its database table
			$rowc = array();
			$rowc[] = $row['id'];
			$rowc[] = $row['calldate'];
			$rowc[] = $row['src'];
			$rowc[] = $row['dst'];
			$rowc[] = $row['didnumber'];
			if(strstr($row['dstchannel'],'agent')){
				$agent = split('/',$row['dstchannel']);
				$rowc[] = $agent['1'];
			}else{
				$rowc[]='';
			}
			$rowc[] = $row['duration'];
			$rowc[] = $row['billsec'];
			if($row['fileformat'] == 'error'){
				$rowc['filename'] = '';
			}else{
				$rowc['filename'] = $row['filename'].'.'.$row['fileformat'];
			}
			$rowc[] = $row['creby'];
			$table->addRow("monitorrecord",$rowc,false,false,false,$divName,$fields);
		}
		//donnie
		// End Editable Zone
		
		$html = $table->render();
		
		return $html;
	}

function searchFormSubmit($searchFormValue,$numRows,$limit,$id='',$type=''){
	global $locate,$db;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$searchType = array();
	$customerid = $searchFormValue['customerid'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];			//搜索方式 数组
	$ordering = $searchFormValue['ordering'];
	$order = $searchFormValue['order'];
	$divName = "grid";

	if($type == "delete"){
		$res = Customer::deleteRecord($id,'account');
		if ($res){
			$html = createGrid($customerid,$searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $searchField, $divName, "",$searchType);
			$objResponse = new xajaxResponse();
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
		}else{
			$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
		}
	}else{
		$html .= createGrid($customerid,$numRows, $limit,$searchField, $searchContent, $order, $divName, $ordering,$searchType);
	}
	$html .= Table::Footer();
	$objResponse->addClear("msgZone", "innerHTML");
	$objResponse->addAssign($divName, "innerHTML", $html);
	return $objResponse->getXML();
}

function playmonitor($id){
	global $config,$locate;
	$objResponse = new xajaxResponse();
	$res = Customer::getRecordByID($id,'monitorrecord');
	$path = $res['filename'].".".$res['fileformat'];
	$html = Table::Top($locate->Translate("playmonitor"),"formplaymonitor");
	if(is_file($path)){
		if($res['fileformat'] == 'mp3'){
			$html .='<object type="application/x-shockwave-flash" data="skin/default/player_mp3_maxi.swf" width="200" height="20"><param name="movie" value="skin/default/player_mp3_maxi.swf" /><param name="bgcolor" value="#ffffff" /><param name="FlashVars" value="mp3=records.php?file='.$id.'&amp;loop=0&amp;autoplay=1&amp;autoload=1&amp;volume=75&amp;showstop=1&amp;showinfo=1&amp;showvolume=1&amp;showloading=always" /></object><br><a href="###" onclick="window.location.href=\'records.php?file='.$id.'\'">'.$locate->Translate("download").'</a>';
		}else{
			$html .= '<embed src="records.php?file='.$id.'" autostart="true" width="300" height="40" name="sound" id="sound" enablejavascript="true"><br><a href="###" onclick="window.location.href=\'records.php?file='.$id.'\'">'.$locate->Translate("download").'</a>';
		}
	}else{
		$html .= '<b>404 File not found!</b>';
	}
	$html .= Table::Footer();
	$objResponse->addAssign("formplaymonitor", "style.visibility", "visible");
	$objResponse->addAssign("formplaymonitor", "innerHTML", $html);	
	return $objResponse->getXML();
}
$xajax->processRequests();

?>