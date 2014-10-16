<?php
/*******************************************************************************
* import.server.php
* import函数信息文件
* import parameter file
* 功能描述
* Function Desc
	init()  页面初始化
	selectTable()  选择表
	submitForm()  将csv，xsl格式文件数据插入数据库
	showDivMainRight() 显示csv，xsl格式文件数据
	getGridData() 得到显示csv，xsl格式文件数据的HTML语法
	getDiallistBar() 得到显示diallist导入框的HTML语法
	importResource() 得到要插入表的sql语句，并执行，返回有效记录数
	parseRowToSql() 得到sql语句和分区，存入数组
	getSourceData()得到excel文件的所有行数据，返回数组

* Revision 0.046  2007/11/8 8:33:00  modified by yunshida
* 描述: 取消了session的使用, 重新整理了流程

* Revision 0.045  2007/10/22 13:39:00  modified by yunshida
* Desc:
* 描述: 增加了包含include/common.class.php, 在init函数中增加了初始化对象divNav和divCopyright


* Revision 0.045  2007/10/18 15:25:00  modified by yunshida
* Desc: page create
* 描述: 页面建立

********************************************************************************/
require_once ("db_connect.php");
require_once ("import.common.php");
require_once ('include/excel.class.php');
require_once ('include/common.class.php');
require_once ('include/astercrm.class.php');
/**
*  function to init import page
*
*
*  @return $objResponse
*
*/
function init($fileName){
	global $locate,$config;
	$objResponse = new xajaxResponse();
	$file_list = getExistfilelist();
	$objResponse->addAssign('filelist','innerHTML','');
	$objResponse->addScript("addOption('filelist','0','".$locate->Translate('select a existent file')."');");
	foreach ( $file_list as $file ) {
		$objResponse->addScript("addOption('filelist','".$file['fileid']."','".$file['originalname']."');");
	}
	
	$tableList = "<select name='sltTable' id='sltTable' onchange='selectTable(this.value);' >
											<option value=''>".$locate->Translate("selecttable")."</option>
											<option value='customer'>customer</option>
											<option value='contact'>contact</option>
											<option value='diallist'>diallist</option>
										</select>";

	$objResponse->addAssign("divTables","innerHTML",$tableList);
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divGrid", "innerHTML", '');
	
	//$objResponse->addScript("xajax_showDivMainRight(document.getElementById('hidFileName').value);");
	//$objResponse->loadXML(showDivMainRight($fileName));
	//$objResponse->addAssign("divDiallistImport", "innerHTML", '');

	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));
	
	if ($_SESSION['curuser']['usertype'] == 'admin') {
		// add all group
		$res = astercrm::getGroups();
		while ($row = $res->fetchRow()) {
			$objResponse->addScript("addOption('groupid','".$row['groupid']."','".$row['groupname']."');");
		}
	}else{
		// add self
		$objResponse->addScript("addOption('groupid','".$_SESSION['curuser']['groupid']."','".$_SESSION['curuser']['group']['groupname']."');");
	}
	
	$objResponse->addScript("setCampaign();");
	
	$objResponse->loadXML(showDivMainRight($fileName));
	return $objResponse;
}

function setCampaign($groupid){
	$objResponse = new xajaxResponse();
	$res = astercrm::getRecordsByGroupid($groupid,"campaign");
	//添加option
	while ($res->fetchInto($row)) {
		$objResponse->addScript("addOption('campaignid','".$row['id']."','".$row['campaignname']."');");
	}
	return $objResponse;
}


/**
*  function to show divMainRight
*
*  @para	$filename		string
*  @return	$objResponse	object
*
*/
function showDivMainRight($filename){
	global $locate,$config;
	$objResponse = new xajaxResponse();

	$filePath = $config['system']['upload_file_path'].$filename;

	if(is_file($filePath)){	//check if file exsits

		$dataContent = getGridHTML($filePath);
		$objResponse->addAssign("divGrid", "innerHTML", $dataContent['gridHTML']);

		$diallistBar = getDiallistBar($dataContent['columnNumber']);
		$objResponse->addAssign("divDiallistImport", "innerHTML", $diallistBar);
		$objResponse->addScript("setDiallistCase();");

		$objResponse->addAssign("btnImportData", "disabled", false);
	}else{
		$objResponse->addAssign("divDiallistImport", "innerHTML", '');
		$objResponse->addAssign("divGrid", "innerHTML", '');
		$objResponse->addAssign("divMessage", "innerHTML",'');
	}

	return $objResponse;
}

/**
*  function to show table div
*
*  	@param $table	string		tablename
															customer
															contact
*  @return $objResponse
*
*/

function selectTable($tableName){
	global $locate,$db;

	$tableStructure = astercrm::getTableStructure($tableName);

	$HTML .= "<ul class='ulstyle'>";
	$i = 0;
	foreach($tableStructure as $row){
		$type_arr = explode(' ',$row['flags']);
		if(!in_array('auto_increment',$type_arr))
		{
			if ($row['name'] == "creby" || $row['name'] == "cretime" || $row['name'] == "groupid" || $row['name'] == "campaignid" ){
			}else{
				$HTML .= "<li height='20px'>";
				$HTML .= $i.":&nbsp;&nbsp;".$row['name'];
				$HTML .= "</li>";
				$i++;
			}
		}
	}
	$HTML .= "</ul>";
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divTableFields", "innerHTML", $HTML);
	$objResponse->addAssign("hidTableName","value",$tableName);
	$objResponse->addAssign("hidMaxTableColumnNum","value",$i-1);
	if($tableName == 'diallist') {
		$objResponse->addAssign("chkAdd","disabled",true);
		$objResponse->addAssign("chkAssign","disabled","");
	} else {
		$objResponse->addAssign("chkAdd","disabled","");
		$objResponse->addAssign("chkAssign","disabled",true);
	}
	
//	$objResponse->addAssign("divDiallistImport", "innerHTML", "");
	return $objResponse;
}

/**
*  function to insert data to database from excel
*
*  	@param $aFormValues	(array)			insert form excel
	if import datas to diallist					$aFormValues['chkAdd']
	if assign extnesion to phone numbers		$aFormValues['chkAssign']
	assign which extensions to phone numbers	$aFormValues['assign']
	import which field							$aFormValues['dialListField']
*	@return $objResponse
*
*/

function submitForm($aFormValues){
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();

	$order = $aFormValues['order']; //得到的排序数字，数组形式，要添加到数据库的列
	$fileName = $aFormValues['hidFileName'];
	$tableName = $aFormValues['hidTableName'];
	$flag = 0;
	foreach($order as $value){  //判断是否有要导入的数据
		if(trim($value) != ''){
			$flag = 1;
			break;
		}
	}
	if($flag != 1){  //判断是否要添加分区
		if(trim($aFormValues['dialListField'])=='' && trim($aFormValues['assign'])==''){
			$flag = 0;
		}else{
			$flag = 1;
		}
	}
	//如果没有任何选择, 就退出
	if($flag != 1){
		$objResponse->addScript('init();');
		return $objResponse;
	}
	
	//对提交的数据进行校验
	$orderNum = count($order);
	if($orderNum > 0)			//如果要导入表
	{
		$arrRepeat = array_count_values($order);
		foreach($arrRepeat as $key=>$value){
			if($key != '' && $value > 1){	//数据重复
				$objResponse->addAlert($locate->Translate('field_cant_repeat'));
				$objResponse->addScript('init();');
				return $objResponse;
			}
		}
	}
	for($j=0;$j<$orderNum;$j++){
		if(trim($order[$j]) != ''){
			if(trim($order[$j]) > $aFormValues['hidMaxTableColumnNum']){  //最大值校验
				$objResponse->addAlert($locate->Translate('field_overflow'));
				$objResponse->addScript('init();');
				return $objResponse;
			}
			if (!ereg("[0-9]+",trim($order[$j]))){ //是否为数字
				$objResponse->addAlert($locate->Translate('field_must_digits'));
				$objResponse->addScript('init();');
				return $objResponse;
			}
		}
	}

	$tableStructure_source = astercrm::getTableStructure($tableName);
	$tableStructure = array();
	foreach($tableStructure_source as $row) {
		$type_arr = explode(' ',$row['flags']);
		if(!in_array('auto_increment',$type_arr))
		{
				if ($row['name'] == "creby" || $row['name'] == "cretime" || $row['name'] == "groupid" || $row['name'] == "campaignid"){

				}else{
					$tableStructure[]= $row;
				}
		}
	}

	//print_r($tableStructure);exit;
	$filePath = $config['system']['upload_file_path'].$fileName;//数据文件存放路径

	$affectRows= 0;  //计数据库影响结果变量
	$x = 0;  //计数变量
	$date = date('Y-m-d H:i:s'); //当前时间
	$groupid = $aFormValues['groupid'];
	$campaignid = $aFormValues['campaignid'];
	if($tableName == 'diallist') {
		$aFormValues['chkAdd'] = '1';

		foreach($order as $key => $value){
			
			if($value == '0'){
				$aFormValues['dialListField'] = $key;
				break;
			}
		}
		//echo $aFormValues['dialListField'];exit;
		if(!is_numeric($aFormValues['dialListField'])){
			$objResponse->addAlert($locate->Translate('must select a cloumn for dialnumer'));
			return $objResponse;
		}
	}
	
	if($aFormValues['chkAdd'] != '' && $aFormValues['chkAdd'] == '1'){ //是否添加到拨号列表
		$dialListField = trim($aFormValues['dialListField']); //数字,得到将哪列添加到拨号列表
		$dialListTime = trim($aFormValues['dialListTime']); //数字,下拉列表选择将哪列做为dialtime添加到拨号列表
		$dialTimeInput = trim($aFormValues['dialtime']); //手动指定唯一的拨号时间组拨号列表
		
		if($aFormValues['chkAssign'] != '' && $aFormValues['chkAssign'] == '1'){ //是否添加分区assign
			$tmpStr = trim($aFormValues['assign']); //分区,以','号分隔的字符串
			if($tmpStr != ''){

				$arryAssign = explode(',',$tmpStr);
				//判断这些分机是否在该组管理范围内
				if ($_SESSION['curuser']['usertype'] != 'admin'){
					foreach ($arryAssign as $key => $myAssign){
						if ( ! in_array(trim($myAssign), $_SESSION['curuser']['memberExtens'])){ //该组不包含该分机
							unset($arryAssign[$key]);
						}
					}
				}
				//exit;
				$assignNum = count($arryAssign);//得到手动添加分区个数
				//print_r($arryAssign);
				//print $assignNum;
			}else{
				if ($_SESSION['curuser']['usertype'] == 'admin'){
					$res = astercrm::getGroupMemberListByID($groupid);
					while ($row = $res->fetchRow()) {
						$arryAssign[] = $row['extension']; //$array_extension数组,存放extension数据
					}
					$assignNum = count($arryAssign); //extension数据的个数
				}else{
					$arryAssign = $_SESSION['curuser']['memberExtens'];
					$assignNum = count($arryAssign); //extension数据的个数
				}
			}
		}else{
			$arryAssign[] = '';
			$assignNum = 0;
		}
	}
	$x = 0;

	$affectRows = importResource($filePath,$order,$tableName,$tableStructure,$dialListField,$dialListTime,$date,$groupid,$dialTimeInput,$assignNum,$arryAssign,$campaignid);
	
	$tableAffectRows = $affectRows['table'];
	$diallistAffectRows = $affectRows['diallist'];

	if($tableAffectRows< 0){
		$tableAffectRows= 0;
	}

	if($diallistAffectRows< 0){
		$diallistAffectRows= 0;
	}

	$resultMsg = $tableName.' : '.$tableAffectRows.' '.$locate->Translate('records_inserted')."<br>";
	$resultMsg .= 'diallist : '.$diallistAffectRows.' '.$locate->Translate('records_inserted');

	//delete upload file
	//@ unlink($filePath);

	$objResponse->addAlert($locate->Translate('success'));
	$objResponse->addScript("document.getElementById('btnImportData').disabled = false;");
	$objResponse->addAssign("divResultMsg", "innerHTML",$resultMsg);
	$objResponse->addScript("init();");
	return $objResponse;
}

/**
*  function to show divDiallistImport
*/
function getDiallistBar($columnNum){
	global $locate;
	$HTML = "";
	$HTML .= "<br />";
	$HTML .= "
					<table cellspacing='0' cellpadding='0' border='0' width='100%' style='text-align:center;'>
						<tr>
							<td>
								<input type='checkbox' value='1' name='chkAdd' id='chkAdd' onclick='chkAddOnClick();'/>
								&nbsp;".$locate->Translate('add')."
								<select name='dialListField' id='dialListField' disabled>
									<option value=''></option>";
	for ($c=0; $c < $columnNum; $c++) {
		$HTML .= "<option value='$c'>$c</option>";
	}
	$HTML .="</select>&nbsp;".$locate->Translate('scheduler').": <select name='dialListTime' id='dialListTime' onchange='selectTimecolumn();' disabled><option value=''></option>";
	for ($c=0; $c < $columnNum; $c++) {
		$HTML .= "<option value='$c'>$c</option>";
	}
	$HTML .= '</select>&nbsp;/&nbsp;<input type="text" name="dialtime" id="dialtime" size="20" value="" disabled><INPUT onclick="displayCalendar(document.getElementById(\'dialtime\'),\'yyyy-mm-dd hh:ii\',this,true)" type="button" value="Cal" id="cal" name="cal" disabled>&nbsp;';
	$HTML .= $locate->Translate('todiallist')." &nbsp;
								<input type='checkbox' value='1' name='chkAssign' id='chkAssign' onclick='chkAssignOnClick();' disabled/> ".$locate->Translate('area')."
								<input type='text' name='assign' id='assign' style='border:1px double #cccccc;width:100px;heiht:12px;' disabled />
							</td>
						</tr>
					</table>";
	//echo $HTML;exit;
	return $HTML;
}

function importResource($filePath,$order,$tableName,$tableStructure,$dialListField,$dialListTime,$date,$groupid,$dialTimeInput,$assignNum,$arryAssign,$campaignid){
	global $db;

	$arrData = getSourceData($filePath);
	
	$x = 0;
	$diallistAffectRows = 0;
	$tableAffectRows = 0;
	$query = "";
	$assgignKey = 0;

	foreach($arrData as $arrRow){
		
		if($tableName == 'diallist'){			

			if(trim($arrRow[$dialListField]) == ''){
				continue;
			}

			if($assignNum > 0 ){
				while ($arryAssign[$assgignKey] == ''){
					if($assgignKey >$assignNum){
						$assgignKey = 0;
					}else{
						$assgignKey ++;
					}
				}
			}
		}
		$arrRes = parseRowToSql($arrRow,$order,$dialListField,$dialListTime,$tableStructure,$tableName,$date,$groupid,$assignNum,$arryAssign,$campaignid,$assgignKey);

		$strSql = $arrRes['strSql'];					//得到插入选择表的sql语句
		
		$dialListNum = $arrRes['dialListNum'];	//以及要导入diallist的sql语句
		$dialListTime = $arrRes['dialListTime'];

		if($dialTimeInput != '' ){
			$dialtime = $dialTimeInput;
		}elseif($dialListTime != '' ){
			$dialtime = $dialListTime;
		}else{
			$dialtime = '';
		}
		if($tableName != '' && $strSql != '' ){
			$res = @ $db->query($strSql);  //插入customer或contact表
			$tableAffectRows += $db->affectedRows();   //得到影响的数据条数
		}

		if (trim($dialListNum) != "" && $tableName != 'diallist'){
			if(isset($dialListField) && trim($dialListField) != ''  && $assignNum > 0){  //是否存在添加到拨号列表
				while ($arryAssign[$x] == ''){
					if($x >=$assignNum){
						$x = 0;
					}else{
						$x ++;
					}
				}
				$query = "INSERT INTO diallist SET dialnumber = '$dialListNum', dialtime = '$dialtime', assign='".$arryAssign[$x]."',  groupid='$groupid',campaignid='$campaignid', cretime= now(), creby = '".$_SESSION['curuser']['username']."' ";
				$x++;
			}else if (isset($dialListField) && trim($dialListField) != ''  && $assignNum == 0){
				$query = "INSERT INTO diallist SET dialnumber = '$dialListNum', dialtime = '$dialtime', groupid='$groupid',campaignid='$campaignid', cretime= now(), creby = '".$_SESSION['curuser']['username']."' ";
			}
			if($query != ''){
				// 查询该号码是否属于某customer
				$myquery = "SELECT id FROM customer WHERE phone = '$dialListNum' OR fax = '$dialListNum' OR mobile ='$dialListNum' LIMIT 0,1";
				$customerid = $db->getOne($myquery);
				if ($customerid>0){
					$query = "$query, customerid = '$customerid' ";
				}
				$tmpRs = $db->query($query);  // 插入diallist表
				$diallistAffectRows += $db->affectedRows();
			}
		}
		
		$assgignKey++;
	}
	$affectRows['diallist'] = $diallistAffectRows;
	$affectRows['table'] = $tableAffectRows;
	return $affectRows;
}

//循环列数据，得到sql
function parseRowToSql($arrRow,$order,$dialListField,$dialListTime,$tableStructure,$tableName,$date,$groupid,$assignNum,$arryAssign,$campaignid,$assignKey){
	$fieldName = '';
	$strData = '';
//echo $dialListField.'111';exit;
	$phone_field = array( 0 => 'phone',1 => 'phone_ext', 2 => 'fax',3 => 'fax_ext',4 => 'mobile',5 => 'ext',6 => 'phone1',7 => 'ext1',8 => 'phone2',9 => 'ext2');
	
	//判断customer传过来的名字是不是空，如果是空就用 first name 和 last name 组合下赋给 customer的名
	$customerFieldExist = false;//判断导入选择的字段里是否有customer字段
	$customername = '';
	$hasCheckflag = false;
	$customerKey = '';//导入的时候选择的字段里有没有customer
	$firstnameKey = '';//导入的时候选择的字段里有没有customer
	$lastnameKey = '';//导入的时候选择的字段里有没有customer
	if($tableName == 'customer') {
		foreach($order as $key=>$tmp) {
			if($tableStructure[trim($tmp)]['name'] == 'customer') {
				$customerFieldExist = true;
				$customerKey = $key;
			} else if($tableStructure[trim($tmp)]['name'] == 'first_name') {
				$firstnameKey = $key;
			} else if($tableStructure[trim($tmp)]['name'] == 'last_name'){
				$lastnameKey = $key;
			}
		}
	}
	
	for ($j=0;$j<count($arrRow);$j++)
	{
		$arrRow[$j] = trim($arrRow[$j]);

//		if ($arrRow[$j] != mb_convert_encoding($arrRow[$j],"UTF-8","UTF-8"))
			//echo "ok";exit;
//		$arrRow[$j]=mb_convert_encoding($arrRow[$j],"UTF-8","GB2312");

		$fieldOrder = trim($order[$j]);//得到字段顺序号
		
		if($fieldOrder != '' && $arrRow[$j] != ''){
			
			$fieldName .= $tableStructure[$fieldOrder]['name'].',';
			if (in_array($tableStructure[$fieldOrder]['name'],$phone_field)){
				$arrRow[$j] = astercrm::getDigitsInStr($arrRow[$j]);
			}

			//如果导入里有customer字段并且值为空
			if($tableStructure[$fieldOrder]['name'] == 'customer' && $arrRow[$j] == '') {
				$tmpNameStr = '';
				if($firstnameKey !== '') {
					$tmpNameStr .= $arrRow[$firstnameKey].' ';
				}
				if($lastnameKey !== ''){
					$tmpNameStr .= $arrRow[$lastnameKey];
				}
				$arrRow[$j] = trim($tmpNameStr);
			}

			//如果导入里没有customer字段
			if(!$customerFieldExist && !$hasCheckflag && $tableName == 'customer') {
				$tmpNameStr = '';
				if($firstnameKey !== '') {
					$tmpNameStr .= $arrRow[$firstnameKey].' ';
				}
				if($lastnameKey !== ''){
					$tmpNameStr .= $arrRow[$lastnameKey];
				}
				$customername = addslashes(trim($tmpNameStr));
				$hasCheckflag = true;
			}

			$strData .= '"'.addslashes($arrRow[$j]).'"'.',';
		}
		
		if(isset($dialListField) && $dialListField != ''  && $arrRow[$j] != ''){
			if($dialListField == $j){
				if($tableName == 'diallist'){
					if($assignNum > 0){
//						while ($arryAssign[$x] == ''){
//							if($x >$assignNum){
//								$x = 0;
//							}else{
//								$x ++;
//							}
//						}
						$fieldName .= 'assign,';
						$strData .= '"'.addslashes($arryAssign[$assignKey]).'"'.',';
					}
					
				}else{
					$dialNum = astercrm::getDigitsInStr($arrRow[$j]);
				}
			}
		}
		if(isset($dialListTime) && $dialListTime != ''){
			if($dialListTime == $j)
				$dialTime = trim($arrRow[$j]);			
		}
	}
	
	if(!$customerFieldExist && $tableName == 'customer') {
		$fieldName = 'customer,'.substr($fieldName,0,strlen($fieldName)-1);
		$strData = '"'.$customername.'",'.substr($strData,0,strlen($strData)-1);
	} else {
		$fieldName = substr($fieldName,0,strlen($fieldName)-1);
		$strData = substr($strData,0,strlen($strData)-1);
	}

	if ($fieldName != ""){
		if ($tableName == "diallist"){
			$strSql = "INSERT INTO $tableName ($fieldName,cretime,creby,groupid,campaignid) VALUES ($strData, '".$date."', '".$_SESSION['curuser']['username']."', '".$groupid."','".$campaignid."')";
		}else{
			$strSql = "INSERT INTO $tableName ($fieldName,cretime,creby,groupid) VALUES ($strData, '".$date."', '".$_SESSION['curuser']['username']."', ".$groupid.")";
		}
	}
	
	return array('strSql'=>$strSql,'dialListNum'=>$dialNum,'dialListTime'=>$dialTime);
}

function csv_string_to_array($str){
   $expr="/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";

   $results=preg_split($expr,trim(addslashes($str)));
	
    return preg_replace(array("/^\"(.*)\"$/","/\"\"/"),array("$1",""),$results);
}

//得到excel文件的所有行数据，返回数组结构的数据

/**
*	get file data from a file
*	@param		$filePath			filepath, could be a csv file or a xsl file
*	@return		$arrData			data in the file
**/
function getSourceData($filePath,$line = -1){  
	$type = substr($filePath,-3);
	$i = 0;

	if($type == 'csv'){  //csv 格式文件
	//fgetcsv
		$handle = fopen($filePath,"r");  //打开csv文件,得到句柄
		while($row = fgetcsv($handle,1000)){
			if ($line > 0)
				if ($i>$line) 
					break;
			$i++;
			$arrData[] = $row;
		}
		/*while (($data = fgets($handle)) !== FALSE) {
			if ($line > 0)
				if ($i>$line) 
					break;
			$i++;
			$arrData[] = csv_string_to_array($data);
		}*/
	}elseif($type == 'xls'){  //xls格式文件
		Read_Excel_File($filePath,$return);
		for ($i=0;$i<count($return[Sheet1]);$i++){
			if ($line > 0)
				if ($i>$line) 
					break;
			$arrData[] = $return[Sheet1][$i];
		}
	}
	//print_r($arrData);exit;
	return $arrData;
}


/**
*	get HTML codes for a file
*	@param		$filePath		string		filepath, could be a csv file or a xsl file
*	@return		$HTML			array		
*								array['gridHTML']		HTML code to display a grid table
*								array['columnNumber']	columnNumber of the data
**/

function getGridHTML($filePath){
	$data_array = getSourceData($filePath,8);
	$row = 0;
	$HTML .= "<table cellspacing='1' cellpadding='0' border='0' width='100%'		style='text-align:left'>";
	foreach($data_array as $data_arr){
		$num = count($data_arr);
		$row++;
		$HTML .= "<input type='hidden' name='CHECK' value='1'/>";
		
		$HTML .= "<tr>";
		for ($c=0; $c < $num; $c++)
		{
//			if ($data_arr[$c] != mb_convert_encoding($data_arr[$c],"UTF-8","UTF-8"))
//					$data_arr[$c]=mb_convert_encoding($data_arr[$c],"UTF-8","GB2312");
			if($row % 2 != 0){
				$HTML .= "<td bgcolor='#ffffff' height='25px'>&nbsp;".trim($data_arr[$c])."</td>";
			}else{
				$HTML .= "<td bgcolor='#efefef' height='25px'>&nbsp;".trim($data_arr[$c])."</td>";
			}
		}
		$HTML .= "</tr>";
		if($row == 8)
			break;
	}
	$HTML .= "<tr>";
	for ($c=0; $c < $num; $c++) {
		$HTML .= "<td bgcolor='#F0F8FF' height='25px'>
						&nbsp;<input type='text' style='width:20px;border:1px double #cccccc;height:12px;' name='order[]'  />
					</td>";
	}
	$HTML .= "</tr>";
	$HTML .= "<tr>";
	for ($c=0; $c < $num; $c++) {
		$HTML .= "<td height='20px' align='left'><font color='#000000'><b>$c</b></font></td>";
	}
	$HTML .= "</tr>";
	$HTML .= "</table>";

	return array('gridHTML'=>$HTML,'columnNumber'=>$num);
}

function getExistfilelist(){
	global $db,$locate,$config;
	
	$sql = "SELECT * FROM uploadfile WHERE type='astercrm'";
	if($_SESSION['curuser']['usertype'] != 'admin'){
		$sql .= " AND groupid = '".$_SESSION['curuser']['groupid']."' ";
	}
	$res = $db->query($sql);

	//$uploaddir = opendir($config['system']['upload_file_path']);
	$file_list = array();
	$i = 0;
	while( $res->fetchinto($row) ) {	
		$filePath = $config['system']['upload_file_path'].$row['filename'];
		if ( is_file($filePath) ){
			$file_list[$i]['fileid'] = $row['id'];
			$file_list[$i]['filename'] = $row['filename'];
			$file_list[$i]['originalname'] = $row['originalname'];
			$i++;
		}
	}
	return $file_list;
}

function deleteFile($fileid){
	global $db,$locate,$config;
	$objResponse = new xajaxResponse();
	$sql = "SELECT * FROM uploadfile WHERE id = ".$fileid;
	$row = $db->getRow($sql);
	$sql = "DELETE FROM uploadfile WHERE id = ".$fileid;
	$res = $db->query($sql);
	//echo $config['system']['upload_file_path'].$row['filename'];exit;
	unlink($config['system']['upload_file_path'].$row['filename']);

	if($res == 1){
		$objResponse->addAssign("divMessage","innerHTML",$locate->Translate('delete file success'));
		$objResponse->addAssign("filelist","length",0);
		$file_list = getExistfilelist();
		$objResponse->addScript("addOption('filelist','0','".$locate->Translate('select a existent file')."');");
		foreach ( $file_list as $file ) {
			$objResponse->addScript("addOption('filelist','".$file['fileid']."','".$file['originalname']."');");
		}
	}else{
		$objResponse->addAssign("divMessage","innerHTML",$locate->Translate('delete file failed'));
	}

	$objResponse->addAssign("spnDel","style.display",'none');
	$objResponse->addAssign("btnDelete","disabled",true);
	
	return $objResponse;
}

$xajax->processRequests();

?>
