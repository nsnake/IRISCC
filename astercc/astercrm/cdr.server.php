<?php
/*******************************************************************************
* trunkinfo.server.php

* Function Desc
	provide trunkinfo management script

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
	save


* Revision 0.045  2007/10/18 15:38:00  last modified by solo
* Desc: comment added

********************************************************************************/
require_once ("db_connect.php");
require_once ("cdr.common.php");
require_once ('cdr.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');


function showGrid($start = 0, $limit = 1,$filter = null, $content = null, $order = null, $divName = "grid", $ordering = ""){
	
	$html = createGrid('','',$start, $limit,$filter, $content, $order, $divName, $ordering);
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
function createGrid($customerid='',$cdrtype='',$start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "grid", $ordering = "",$stype=array(),$allOrAnswer=null){
	global $locate;
	
	$_SESSION['ordering'] = $ordering;
	if($filter == null || $content == null || (!is_array($content) && $content == 'Array') || (!is_array(filter) && $filter == 'Array')){
		$content = null;
		$filter = null;
		$numRows =& Customer::getCdrNumRows($customerid,$cdrtype,null,null,$allOrAnswer);
		$arreglo =& Customer::getAllCdrRecords($customerid,$cdrtype,$start,$limit,$order,null,$allOrAnswer);
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
			$numRows =& Customer::getCdrNumRows($customerid,$cdrtype,null,null,$allOrAnswer);
			$arreglo =& Customer::getAllCdrRecords($customerid,$cdrtype,$start,$limit,$order,null,$allOrAnswer);
		}elseif($flag3 != 1 ){  //未选择搜索方式
			$order = "calldate";
			$numRows =& Customer::getCdrNumRowsMore($customerid,$cdrtype,$filter, $content,null,$allOrAnswer);
			$arreglo =& Customer::getCdrRecordsFilteredMore($customerid,$cdrtype,$start, $limit, $filter, $content, $order,null,null,$allOrAnswer);
		}else{
			$order = "calldate";
			$numRows =& Customer::getCdrNumRowsMorewithstype($customerid,$cdrtype,$filter, $content,$stype,$allOrAnswer);
			$arreglo =& Customer::getCdrRecordsFilteredMorewithstype($customerid,$cdrtype,$start, $limit, $filter, $content, $stype,$order,$allOrAnswer);
		}
	}	
	// Databse Table: fields
	$fields = array();
	$fields[] = 'calldate';
	$fields[] = 'src';
	$fields[] = 'dst';
	$fields[] = 'didnumber';
	$fields[] = 'dstchannel';
	$fields[] = 'username';
	$fields[] = 'groupname';
	$fields[] = 'duration';
	$fields[] = 'billsec';
	$fields[] = 'disposition';
	$fields[] = 'billsec_leg_a';
	$fields[] = 'credit';
	$fileds[] = 'destination';
	$fileds[] = 'transfertime';
	$fileds[] = 'transfertarget';
	$fileds[] = 'memo';
	$fileds[] = 'filename';

	// HTML table: Headers showed
	$headers = array();
	$headers[] = $locate->Translate("Calldate")."<br>";
	$headers[] = $locate->Translate("Src")."<br>";
	$headers[] = $locate->Translate("Dst")."<br>";
	$headers[] = $locate->Translate("Callee Id")."<br>";
	$headers[] = $locate->Translate("Agent").'<br>';
	$headers[] = $locate->Translate("UserName").'<br>';
	$headers[] = $locate->Translate("AgentGroup Name").'<br>';
	$headers[] = $locate->Translate("Duration")."<br>";
	$headers[] = $locate->Translate("Billsec")."<br>";
	$headers[] = $locate->Translate("Disposition")."<br>";
	$headers[] = $locate->Translate("Total Billsec")."<br>";
	$headers[] = $locate->Translate("Credit")."<br>";
	#$headers[] = $locate->Translate("Destination")."<br>";
	$headers[] = $locate->Translate("Transfer Time")."<br>";
	$headers[] = $locate->Translate("Transfer Target")."<br>";
	#$headers[] = $locate->Translate("Memo")."<br>";
	$headers[] = $locate->Translate("filename")."<br>";

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
	$attribsHeader[] = 'width=""';
	#$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	$attribsHeader[] = 'width=""';
	#$attribsHeader[] = 'width=""';

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
	#$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	$attribsCols[] = 'style="text-align: left"';
	#$attribsCols[] = 'style="text-align: left"';


	// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
	$eventHeader = array();
		//$eventHeader[]= '';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","calldate","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","src","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","dst","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","didnumber","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","dstchannel","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","username","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","groupname","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","duration","'.$divName.'","ORDERING");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","billsec","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","disposition","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","billsec_leg_a","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","credit","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","transfertime","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","transfertarget","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	#$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","destination","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	#$eventHeader[]= 'onClick=\'xajax_showGrid(0,'.$limit.',"'.$filter.'","'.$content.'","memo","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
	
	// Select Box: type table.
	$typeFromSearch = array();
	$typeFromSearch[] = 'like';
	$typeFromSearch[] = 'equal';
	$typeFromSearch[] = 'more';
	$typeFromSearch[] = 'less';

	// Selecct Box: Labels showed on searchtype select box.
	$typeFromSearchShowAs = array();
	$typeFromSearchShowAs[] = $locate->Translate('like');
	$typeFromSearchShowAs[] = '=';
	$typeFromSearchShowAs[] = '>';
	$typeFromSearchShowAs[] = '<';

	// Select Box: fields table.
	$fieldsFromSearch = array();
	$fieldsFromSearch[] = 'src';
	$fieldsFromSearch[] = 'calldate';
	$fieldsFromSearch[] = 'dst';
	$fieldsFromSearch[] = 'didnumber';
	$fieldsFromSearch[] = 'username';
	$fieldsFromSearch[] = 'groupname';
	$fieldsFromSearch[] = 'billsec';
	$fieldsFromSearch[] = 'disposition';
	$fieldsFromSearch[] = 'credit';
	$fieldsFromSearch[] = 'transfertime';
	$fieldsFromSearch[] = 'transfertarget';
	#$fieldsFromSearch[] = 'destination';
	$fieldsFromSearch[] = 'memo';

	// Selecct Box: Labels showed on search select box.
	$fieldsFromSearchShowAs = array();
	$fieldsFromSearchShowAs[] = $locate->Translate("src");
	$fieldsFromSearchShowAs[] = $locate->Translate("calldate");
	$fieldsFromSearchShowAs[] = $locate->Translate("dst");
	$fieldsFromSearchShowAs[] = $locate->Translate("callee id");
	$fieldsFromSearchShowAs[] = $locate->Translate("UserName");
	$fieldsFromSearchShowAs[] = $locate->Translate("AgentGroup Name");
	$fieldsFromSearchShowAs[] = $locate->Translate("billsec");
	$fieldsFromSearchShowAs[] = $locate->Translate("disposition");
	$fieldsFromSearchShowAs[] = $locate->Translate("credit");
	$fieldsFromSearchShowAs[] = $locate->Translate("transfer time");
	$fieldsFromSearchShowAs[] = $locate->Translate("transfer target");
	#$fieldsFromSearchShowAs[] = $locate->Translate("destination");
	$fieldsFromSearchShowAs[] = $locate->Translate("memo");


	// Create object whit 5 cols and all data arrays set before.
	$table = new ScrollTable(9,$start,$limit,$filter,$numRows,$content,$order,$customerid,$cdrtype);
	$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=false,$delete=false,$detail=false);
	$table->setAttribsCols($attribsCols);
	$table->ordering = $ordering;
	$table->exportFlag = '2';//对导出标记进行赋值
	$table->addRowSearchMore("mycdr",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,0,$typeFromSearch,$typeFromSearchShowAs,$stype,$allOrAnswer);

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
		$rowc[] = $row['username'];
		$rowc[] = $row['groupname'];
		$rowc[] = astercrm::FormatSec($row['duration']);
		$rowc[] = astercrm::FormatSec($row['billsec']);
		$rowc[] = $row['disposition'];
		$rowc[] = astercrm::FormatSec($row['billsec_leg_a']);
		$rowc[] = $row['credit'];
		#$rowc[] = $row['destination'];
		$rowc[] = $row['transfertime'];
		$rowc[] = $row['transfertarget'];
		#$rowc[] = $row['memo'];
		if($row['processed'] == 'yes' && $row['fileformat'] != 'error' ) {
			$rowc['filename'] = $row['filename'].'.'.$row['fileformat'];
		} else {
			$rowc['filename'] = '';
		}
		
		$table->addRow("mycdr",$rowc,false,false,false,$divName,$fields);
	}
	
	// End Editable Zone
	
	$html = $table->render();
	
	return $html;
}

function searchFormSubmit($searchFormValue,$numRows = null,$limit = null,$id = null,$type = null,$order= ''){
	global $locate,$db;
	#print_r($searchFormValue);exit;
	$objResponse = new xajaxResponse();
	$searchField = array();
	$searchContent = array();
	$searchType = array();
	$optionFlag = $searchFormValue['optionFlag'];
	$searchContent = $searchFormValue['searchContent'];  //搜索内容 数组
	$searchField = $searchFormValue['searchField'];      //搜索条件 数组
	$searchType =  $searchFormValue['searchType'];			//搜索方式 数组
	$ordering = $searchFormValue['ordering'];
	$order = $searchFormValue['order'];
	$divName = "grid";

	$allOrAnswer = $searchFormValue['allOrAnswer'];#选中的radio值

	if($optionFlag == "export" || $optionFlag == "exportcsv"){
		$fieldArray = array('mycdr.*','astercrm_accountgroup.groupname','astercrm_account.username');
		$leftjoinArray = array('astercrm_accountgroup'=>array('astercrm_accountgroup.id','mycdr.astercrm_groupid'),'astercrm_account'=>array('astercrm_account.id','mycdr.accountid'));

		if($searchFormValue['allOrAnswer'] == 'answered'){
			$searchContent[] = '0';
			$searchField[] = 'billsec';
			$searchType[] = 'more';
		}
		
		$sql = astercrm::getSql($searchContent,$searchField,$searchType,'mycdr',$fieldArray,$leftjoinArray); //得到要导出的sql语句
		
		$_SESSION['export_sql'] = $sql;
		$objResponse->addAssign("hidSql", "value", $sql); //赋值隐含域
		$objResponse->addAssign("exporttype", "value", $optionFlag);
		$objResponse->addScript("document.getElementById('exportForm').submit();");
	}else{
		if($type == "delete"){
			$res = Customer::deleteRecord($id,'account');
			if ($res){
				$html = createGrid('','',$searchFormValue['numRows'], $searchFormValue['limit'],$searchField, $searchContent, $order, $divName, $ordering,$searchType,$allOrAnswer);
				$objResponse = new xajaxResponse();
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("delete_rec")); 
			}else{
				$objResponse->addAssign("msgZone", "innerHTML", $locate->Translate("rec_cannot_delete")); 
			}
		}else{
			$html .= createGrid('','',$numRows, $limit,$searchField, $searchContent,  $order, $divName,$ordering,$searchType,$allOrAnswer);
		}
		$objResponse->addClear("msgZone", "innerHTML");
		$objResponse->addAssign($divName, "innerHTML", $html);
	}

	return $objResponse->getXML();
}

function playmonitor($path,$l,$t){
	global $config,$locate;
	$objResponse = new xajaxResponse();
	$html = Table::Top($locate->Translate("playmonitor"),"formplaymonitor");
	if(is_file($path) && !empty($path)){
		$filebasename = basename($path);
		$file_extension = strtolower(substr(strrchr($filebasename,"."),1));

		if($file_extension == 'mp3'){
			$html .='<object type="application/x-shockwave-flash" data="skin/default/player_mp3_maxi.swf" width="200" height="20"><param name="movie" value="skin/default/player_mp3_maxi.swf" /><param name="bgcolor" value="#ffffff" /><param name="FlashVars" value="mp3=records.php?file='.$path.'&amp;loop=0&amp;autoplay=1&amp;autoload=1&amp;volume=75&amp;showstop=1&amp;showinfo=1&amp;showvolume=1&amp;showloading=always" /></object><br><a href="###" onclick="window.location.href=\'records.php?file='.$path.'\'">'.$locate->Translate("download").'</a>';
		}else{
			$html .= '<embed src="records.php?file='.$path.'" autostart="true" width="300" height="40" name="sound" id="sound" enablejavascript="true"><br><a href="###" onclick="window.location.href=\'records.php?file='.$path.'\'">'.$locate->Translate("download").'</a>';
		}
	}else{
		$html .= '<b>404 File not found!</b>';
	}
	$html .= Table::Footer();
	
    $objResponse->addAssign("formplaymonitor", "style.left", ($l-500)."px");
    $objResponse->addAssign("formplaymonitor", "style.top", ($t-120)."px");
	$objResponse->addAssign("formplaymonitor", "style.visibility", "visible");
	$objResponse->addAssign("formplaymonitor", "innerHTML", $html);	
	return $objResponse->getXML();
}

$xajax->processRequests();

?>