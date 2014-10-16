<?php /*******************************************************************************
* astercrm.php
* astercrm公用类
* astercrm class

* Public Functions List

			insertNewCustomer		向customer表插入数据
			insertNewContact		向contact表插入数据
			insertNewNote			向note表插入数据
			insertNewSurveyResult	向surveyresult表插入数据
			insertNewAccount
			insertNewDialedlist
			insertNewAccountgroup    向accountgroup表插入数据
			insertNewCampaign
			insertNewMonitor			向monitorrecord表插入数据
			insertNewKnowledge      向knowledge表插入数据

			updateCustomerRecord	更新customer表数据
			updateContactRecord		更新contact表数据
			updateNoteRecord		更新note表数据
			updateAccountRecord
			updateAccountgroupRecord  更新accountgroup表数据
			updateRecords		更新数据
			updateCampaignRecord
            updateKnowledgeRecord   更新knowledge表数据

			deleteRecord			从表中删除数据(以id作为标识)
			updateField				更新表中的数据(以id作为标识)
			events					日志记录
			checkValues				根据条件从数据库中检索是否有符合条件的记录
			showNoteList			生成note列表的HTML文件

			getCustomerByID			根据customerid获取customer记录信息或者根据noteid获取与之相关的customer信息
			getContactByID			根据contactid获取contact记录信息或者根据noteid获取与之相关的contact信息
			getContactListByID		根据customerid获取与之邦定的contact记录

			getGroupCurcdr			取出当前groupadmin所在group所包含的所有exten和agent的curcdr记录

			getRecord				从表中读取数据(以id作为标识)
			getRecordByID			根据id获取记录
			getRecordByField($field,$value,$table)
					根据某一条件获得记录
			getCountByField($field,$value,$table)
					根据某一条件获得记录数目
			getCustomerByCallerid	根据callerid查找customer表看是否有匹配的id
			getRecordsByGroupid

			getTableRecords				从表中读取数据
			getSql              得到多条件搜索的sql语句
			getGroupMemberListByID 得到组成员 
			getOptions				读取survey的所有option
			getNoteListByID			根据customerid或者contactid获取与之邦定的note记录

			getCustomerSmartMatch	客户callerid智能匹配(callerid去除后n位的匹配结果集)
			getContactSmartMatch	联系人callerid智能匹配(callerid去除后n位的匹配结果集)

			surveyAdd				生成添加survey的HTML语法
			noteAdd					生成添加note的HTML语法
			formAdd					生成添加综合信息(包括customer, contact, survey, note)的HTML语法
			formEdit				生成综合信息编辑的HTML语法, 
									包括编辑customer, contact以及添加note

			showCustomerRecord		生成显示customer信息的HTML语法
			showContactRecord		生成显示contact信息的HTML语法

			exportCSV				生成csv文件内容, 目前支持导出customer, contact

			variableFiler			用于转译变量, 自动加\
			exportDataToCSV     得到要导出的sql语句的结果集，转换为符合csv格式的文本字符串
			createSqlWithStype	根据filter,content,searchtype生成查询条件语句

			----------------2008-6 by donnie---------------------------------------
			formDiallistAdd			生成customer对应的diallist的html
			getDiallistNumRowsMorewithstype   customer对应的diallist多条件带搜索类型的记录数
			getDiallistFilteredMorewithstype  customer对应的diallist多条件带搜索类型的结果集
			getDiallistNumRowsMore	得到customer对应的diallist多条件搜索记录数
			getDiallistFilteredMore customer对应的diallist多条件搜索结果集
			getDiallistNumRows		得到customer对应的diallist全部记录数
			getAllDiallist			customer对应的diallist全部结果集
			createDiallistGrid		生成customer对应的diallist列表
			getCdrRecordsFilteredMorewithstype   得到customer对应的CDR多条件带搜索类型的结果集
			getCdrNumRowsMorewithstype   得到customer对应的CDR多条件带搜索类型的记录数
			getCdrNumRowsMore		得到customer对应的CDR多条件搜索记录数
			getCdrRecordsFilteredMore	得到customer对应的CDR多条件搜索结果集
			getCdrNumRows			得到customer对应的CDR全部记录数
			getAllCdrRecords		得到customer对应的CDR全部结果集
			createCdrGrid			生成customer对应的CDR列表
			getRecNumRows
			getAllRecRecords
			getRecNumRowsMore
			getRecRecordsFilteredMore
			getRecNumRowsMorewithstype
			getRecRecordsFilteredMorewithstype
			createRecordsGrid
			readReportAgent
			--------------------------------------------------------------------------
			
* Private Functions List
			generateSurvey			生成添加survey的HTML语法


* Revision 0.047  2008/2/24 10:11:00  last modified by solo
* Desc: add a new function insertNewMonitor

* Revision 0.0456  2007/11/8 10:11:00  last modified by solo
* Desc: add a new function getTableStructure

* Revision 0.0456  2007/11/7 11:30:00  last modified by solo
* Desc: add a new function variableFiler

* Revision 0.0456  2007/11/7 10:30:00  last modified by solo
* Desc: replace input with textarea in note field

* Revision 0.0456  2007/10/30 13:30:00  last modified by solo
* Desc: modified function insertNewAccount,updateAccountRecord

* Revision 0.045  2007/10/18 13:30:00  last modified by solo
* Desc: add function insertNewDiallist


********************************************************************************/

/** \brief astercrm Class
*

*
* @author	Solo Fu <solo.fu@gmail.com>
* @version	1.0
* @date		13 Auguest 2007
*/


Class astercrm extends PEAR{
	function countDailedlist($campaignresult,$campaignid,$groupid){
		global $db;
		$query = "SELECT COUNT(*) FROM campaigndialedlist WHERE campaignresult = '$campaignresult' AND campaignid = '$campaignid' AND groupid = '$groupid'";
		$res = $db->getOne($query);
		return $res;
	}

	function getTrunkinfo($trunk,$trunkdid = ''){
		global $db;
		if($trunkdid != ''){
			$query = "SELECT * FROM trunkinfo WHERE didnumber = '$trunkdid'";
			astercrm::events($query);
			$res =& $db->getRow($query);
			if($res) return $res;
		}

		$query = "SELECT * FROM trunkinfo WHERE trunkchannel = '$trunk'";
		
		astercrm::events($query);
		$res =& $db->getRow($query);
		return $res;
	}

	function insertNewSchedulerDial($f){
		global $db;
		$sql = "INSERT INTO diallist SET "
			."dialnumber='".astercrm::getDigitsInStr($f['sDialNum'])."', "
			."groupid='".$_SESSION['curuser']['groupid']."', "
			."dialtime='".$f['sDialtime']."', "
			."callOrder='1', "
			."assign='".$_SESSION['curuser']['extension']."', "
			."customerid='".$f['customerid']."', "
			."customername='".$f['customername']."', "
			."creby='".$_SESSION['curuser']['username']."', "
			."cretime= now(), "
			."campaignid= ".$f['curCampaignid']." ";
		
		astercrm::events($query);
		$res =$db->query($sql);
		return $res;
	}

	function insertNewMonitor($callerid,$filename,$uniqueid,$format,$curid){
		global $db;
		$query= "INSERT INTO monitorrecord SET "
				."callerid = '".$callerid."', "
				."filename = '".$filename."', "
				."fileformat = '".$format."', "
				."cretime=now(), "
				."groupid = ".$_SESSION['curuser']['groupid'].", "
				."extension = ".$_SESSION['curuser']['extension'].", "
				."uniqueid = '".$uniqueid."', "
				."creby='".$_SESSION['curuser']['username']."'";
		astercrm::events($query);
		$res =& $db->query($query);
		$mid = mysql_insert_id();
		$query = "UPDATE curcdr SET monitored = '".$mid."' WHERE id = '".$curid."'";
		$db->query($query);
		return $res;
	}

	function insertNewKnowledge($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$sql= "INSERT INTO knowledge SET "
				."knowledgetitle='".$f['knowledgetitle']."', "
				."content='".$f['content']."', "
				."groupid = ".$f['groupid'].", "
				."creby = '".$_SESSION['curuser']['username']."',"
				."cretime = now() ";

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function getAll($table,$field = '', $value = ''){
		global $db;
		if (trim($field) != '' && trim($value) != ''){
			$query = "SELECT * FROM $table WHERE $field = '$value' ";
		}else{
			$query = "SELECT * FROM $table ";
		}
		astercrm::events($query);
		$res = $db->query($query);
		return $res;
	}

	function getGroups(){
		global $db;
		$sql = "SELECT * FROM astercrm_accountgroup";
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function getGroupById($groupid){
		global $db;
		$sql = "SELECT groupname  FROM astercrm_accountgroup WHERE id = $groupid";
		astercrm::events($sql);
		$res =& $db->getRow($sql);
		return $res;
	}

	/**
	* update table values
	*	
	*	@param	$table				string	table name
	*	@param	$field					string	field name
	*	@param	$old_val		string	old value
	*	@param	$new_val		string	new value
	*
	$res = astercrm::updateRecords('accountgroup','groupid',$id,0);

	*/
	function updateRecords($table,$field,$old_val,$new_val){
		global $db;
		$query = "UPDATE $table SET $field = '$new_val' WHERE $field = '$old_val'";
		$res =& $db->query($query);
		return  $res;
	}


	/**
	*	get table structure
	*	
	*	@param	$table		string	table name
	*	@return $structure	array	table structure
	*
	*/
	function getTableStructure($tableName){
		global $db;
		$query = "select * from $tableName LIMIT 0,2";
		$res =& $db->query($query);
		return  $db->tableInfo($res);
	}

	function getTableRecords($tableName){
		global $db;
		$query = "select * from $tableName";
		$res =& $db->query($query);
		return  $db->tableInfo($res);
	}

	/**
	*  filer variables befor mysql query
	*
	*
	*
	*/

	function variableFiler($var){
		if (is_array($var)){
			$newVar = array();
			foreach ($var as  $key=>$value){
				$value = addslashes($value);
				$newVar[$key] = $value;
			}
		}else{
			$newVar = addslashes($var);
		}
		return $newVar;
	}
	
	/*
	* romove character which not a digit in string
	*/

	function getDigitsInStr($str){
		$digits = preg_replace('/\D/','',$str);
		return $digits;
	}

	function dbcToSbc($str) {  
		 $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',  
					  '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',  
					  'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',  
					  'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',  
					  'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',  
					  'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',  
					  'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',  
					  'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',  
					  'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',  
					  'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',  
					  'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',  
					  'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',  
					  'ｙ' => 'y', 'ｚ' => 'z',  
					  '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',  
					  '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']',  
					  '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',  
					  '》' => '>',  
					  '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',  
					  '：' => ':', '。' => '.', '、' => ',', '，' => ',', '、' => '.',  
					  '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',  
					  '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',  
					  '　' => ' ');  
   
		return strtr($str, $arr);  
	}


	/**
	*  insert a record to customer table
	*
	*	@param $f			(array)		array contain customer fields.
	*	@return $customerid	(object) 	id number for the record just inserted.
	*/
	
	function insertNewCustomer($f){
		global $db;
		$f = astercrm::variableFiler($f);
		if($f['customer'] == '') {
			$f['customer'] = $f['first_name'].' '.$f['last_name'];
		}
		$query= "INSERT INTO customer SET "
				."customer='".$f['customer']."', "
				."first_name='".$f['first_name']."', "
				."last_name='".$f['last_name']."', "
				."customertitle='".$f['customertitle']."', "
				."website='".$f['website']."', "
				."country='".$f['country']."', "
				."address='".$f['address']."', "
				."zipcode='".$f['zipcode']."', "
				."city='".$f['city']."', "
				."state='".$f['state']."', "
				."contact='".$f['customerContact']."', "
				."contactgender='".$f['customerContactGender']."', "
				."phone='".astercrm::getDigitsInStr($f['customerPhone'])."', "
				."phone_ext='".astercrm::getDigitsInStr($f['customerPhone_ext'])."', "
				."category='".$f['category']."', "
				."bankname='".$f['bankname']."', "
				."bankzip='".$f['bankzip']."', "
				."bankaccount='".$f['bankaccount']."', "
				."bankaccountname='".$f['bankaccountname']."', "
				."fax='".astercrm::getDigitsInStr($f['mainFax'])."', "
				."fax_ext='".astercrm::getDigitsInStr($f['mainFax_ext'])."', "
				."mobile='".astercrm::getDigitsInStr($f['mainMobile'])."', "
				."email='".$f['mainEmail']."', "
				."cretime=now(), "
				."groupid = ".$_SESSION['curuser']['groupid'].", "
				."creby='".$_SESSION['curuser']['username']."'";
		astercrm::events($query);
		$res =& $db->query($query);
		$customerid = mysql_insert_id();
		return $customerid;
	}


	/**
	*  insert a record to contact table
	*
	*	@param $f			(array)		array contain contact fields.
	*	@param $customerid	(array)		customer id of the new contact
	*	@return $customerid	(object) 	id number for the record just inserted.
	*/
	
	function insertNewContact($f,$customerid){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$query= "INSERT INTO contact SET "
				."contact='".$f['contact']."', "
				."gender='".$f['gender']."', "
				."position='".$f['position']."', "
				."phone='".astercrm::getDigitsInStr($f['phone'])."', "
				."ext='".astercrm::getDigitsInStr($f['ext'])."', "
				."phone1='".astercrm::getDigitsInStr($f['phone1'])."', "
				."ext1='".astercrm::getDigitsInStr($f['ext1'])."', "
				."phone2='".astercrm::getDigitsInStr($f['phone2'])."', "
				."ext2='".astercrm::getDigitsInStr($f['ext2'])."', "
				."mobile='".astercrm::getDigitsInStr($f['mobile'])."', "
				."fax='".astercrm::getDigitsInStr($f['fax'])."', "
				."fax_ext='".astercrm::getDigitsInStr($f['fax_ext'])."', "
				."email='".$f['email']."', "
				."cretime=now(), "
				."creby='".$_SESSION['curuser']['username']."', "
				."groupid = ".$_SESSION['curuser']['groupid'].", "
				."customerid=". $customerid ;
		astercrm::events($query);
		$res =& $db->query($query);
		$contactid = mysql_insert_id();
		return $contactid;
	}


	/**
	*  Insert a new note
	*
	*	@param $f			(array)		array contain note fields.
	*	@paran $customerid 	(int)		customer id of the new note
	*	@paran $contactid 	(int)		contact id of the new note
	*	@return $res	(object) 		object
	*/
	
	function insertNewNote($f,$customerid,$contactid){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "INSERT INTO note SET "
				."note='".$f['note']."', "
				."callerid='".$f['iptcallerid']."', "
				."attitude='".$f['attitude']."', "
				."priority=".$f['priority'].", "
				."private='".$f['private']."', "
				."cretime=now(), "
				."creby='".$_SESSION['curuser']['username']."', "
				."groupid = ".$_SESSION['curuser']['groupid'].", "
				."customerid=". $customerid . ", "
				."contactid=". $contactid .", "
				."codes='". $f['note_code']."' " ;
		//print $query;
		//exit;
		astercrm::events($query);

		$res =& $db->query($query);
		if($res) {
			$noteId = mysql_insert_id();
			$sql = "UPDATE customer SET last_note_id=$noteId WHERE id=$customerid ";
			$res =& $db->query($sql);
		}
		
		return $res;
	}

	/**
	*  Inserta un nuevo registro en la tabla.
	*
	*	@param $f	(array)		Arreglo que contiene los datos del formulario pasado.
	*	@return $res	(object) 	Devuelve el objeto con la respuesta de la sentencia SQL ejecutada del INSERT.

	*/
	
	function insertNewAccount($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "INSERT INTO astercrm_account SET "
				."username='".$f['username']."', "
				."password='".$f['password']."', "
				."firstname='".$f['firstname']."',"
				."lastname='".$f['lastname']."',"
				."callerid='".$f['callerid']."',"
				."extension='".$f['extension']."',"
				."agent = '".$f['agent']."',"
				."channel='".$f['channel']."',"			// added 2007/10/30 by solo
				."usertype='".$f['usertype']."',"
				."extensions='".$f['extensions']."', "	// added 2007/11/12 by solo
				."groupid='".$f['groupid']."', "	// added 2007/11/12 by solo
				."dialinterval='".$f['dialinterval']."', "
				."usertype_id = '".$f['usertype_id']."',"
				."accountcode='".$f['accountcode']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function insertNewAccountgroup($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "INSERT INTO astercrm_accountgroup SET "
				."groupname='".$f['groupname']."', "				
				."creby = '".$_SESSION['curuser']['username']."',"
				."cretime = now(),"
				."monitorforce='".$f['monitorforce']."',"
				."firstring='".$f['firstring']."',"
				."allowloginqueue='".$f['allowloginqueue']."',"
				."agentinterval='".$f['agentinterval']."',"
				."notice_interval='".$f['notice_interval']."',"
				."clear_popup='".$f['clear_popup']."',"
				."groupnote='".$f['groupnote']."',"
				."billingid='".$f['billingid']."',"
				."incontext='".$f['incontext']."',"
				."outcontext='".$f['outcontext']."' ";		// added 2007/10/30 by solo
		astercrm::events($query);
		$res =& $db->query($query);		
		$sql = "UPDATE astercrm_accountgroup SET groupid = id";
		astercrm::events($sql);
		$ures =& $db->query($sql);
		return $res;
	}


	function insertNewDialedlist($f){
		global $db;
		$f = astercrm::variableFiler($f);
		if($f['callresult'] == ''){
			$f['callresult'] = 'UNKNOWN';
		}
		
		$query = 'INSERT INTO dialedlist (dialednumber,dialedby,dialedtime,groupid,campaignid,trytime,assign,customerid,customername,callOrder,creby,callresult,memo) VALUES ("'.$f['dialednumber'].'","'.$f['dialedby'].'",now(),'.$f['groupid'].','.$f['campaignid'].','.$f['trytime'].',"'.$f['assign'].'",'.$f['customerid'].",'".$f['customername']."','".$f['callOrder']."',"."'".$f['creby']."','".$f['callresult']."','".$f['memo']."')";

		astercrm::events($query);
		$res =& $db->query($query);
		
		return mysql_insert_id();
	}

	function insertNewDiallist($f){
		global $db;
		$f = astercrm::variableFiler($f);
		if($f['callOrder'] == 0 || $f['callOrder'] == '' ) $f['callOrder'] = 1;
		$query= "INSERT INTO diallist SET "
				."dialnumber='".astercrm::getDigitsInStr($f['dialnumber'])."', "
				."customername='".$f['customername']."', "
				."groupid='".$f['groupid']."', "
				."dialtime='".$f['dialtime']."', "
				."callOrder='".$f['callOrder']."', "
				."creby='".$_SESSION['curuser']['username']."', "
				."cretime= now(), "
				."campaignid= ".$f['campaignid'].", "
				."memo= '".$f['memo']."', "
				."assign='".$f['assign']."'";
		if(!empty($f['customerid'])){
			$query .= ",customerid='".$f['customerid']."'";
		}
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function updateDiallistRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		if($f['callOrder'] == 0 || $f['callOrder'] == '' ) $f['callOrder'] = 1;
		$query= "UPDATE diallist SET "
				."dialnumber='".astercrm::getDigitsInStr($f['dialnumber'])."', "
				."customername='".$f['customername']."', "
				."groupid='".$f['groupid']."', "
				."dialtime='".$f['dialtime']."', "
				."callOrder='".$f['callOrder']."', "
				."creby='".$_SESSION['curuser']['username']."', "
				."cretime= now(), "
				."campaignid= ".$f['campaignid'].", "
				."memo= '".$f['memo']."', "
				."assign='".$f['assign']."'"
				."WHERE id = ".$f['id'];
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	/**
	*  update customer table
	*
	*	@param $f			(array)		array contain customer fields.
	*	@return $res		(object) 		object
	*/
	
	function updateCustomerRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		if($f['customer'] == '') {
			$f['customer'] = $f['first_name'].' '.$f['last_name'];
		}
		$query= "UPDATE customer SET "
				."customer='".$f['customer']."', "
				."first_name='".$f['first_name']."', "
				."last_name='".$f['last_name']."', "
				."customertitle='".$f['customertitle']."', "
				."website='".$f['website']."', "
				."country='".$f['country']."', "
				."address='".$f['address']."', "
				."zipcode='".$f['zipcode']."', "
				."phone='".astercrm::getDigitsInStr($f['customerPhone'])."', "
				."phone_ext='".astercrm::getDigitsInStr($f['customerPhone_ext'])."', "
				."contact='".$f['customerContact']."', "
				."contactgender='".$f['customerContactGender']."', "
				."state='".$f['state']."', "
				."city='".$f['city']."', "
				."category='".$f['category']."', "
				."bankname='".$f['bankname']."', "
				."bankzip='".$f['bankzip']."', "
				."fax='".astercrm::getDigitsInStr($f['mainFax'])."', "
				."fax_ext='".astercrm::getDigitsInStr($f['mainFax_ext'])."', "
				."mobile='".astercrm::getDigitsInStr($f['mainMobile'])."', "
				."email='".$f['mainEmail']."', "
				."bankaccount='".$f['bankaccount']."', "
				."bankaccountname='".$f['bankaccountname']."' "
				."WHERE id='".$f['customerid']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	/**
	*  update contact table
	*
	*	@param $f			(array)		array contain contact fields.
	*	@return $res		(object)	object
	*/
	
	function updateContactRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$query= "UPDATE contact SET "
				."contact='".$f['contact']."', "
				."gender='".$f['contactGender']."', "
				."position='".$f['position']."', "
				."phone='".astercrm::getDigitsInStr($f['phone'])."', "
				."ext='".astercrm::getDigitsInStr($f['ext'])."', "
				."phone1='".astercrm::getDigitsInStr($f['phone1'])."', "
				."ext1='".astercrm::getDigitsInStr($f['ext1'])."', "
				."phone2='".astercrm::getDigitsInStr($f['phone2'])."', "
				."ext2='".astercrm::getDigitsInStr($f['ext2'])."', "
				."mobile='".astercrm::getDigitsInStr($f['mobile'])."', "
				."fax='".astercrm::getDigitsInStr($f['fax'])."', "
				."fax_ext='".astercrm::getDigitsInStr($f['fax_ext'])."', "
				."email='".$f['email']."' "
				."WHERE id='".$f['contactid']."'";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	/**
	*  update note table 
	*  if $type is update, this function would use new data to replace the old one
	*  or else astercrm would append new data to note field
	*
	*	@param $f			(array)			array contain note fields.
	*	@param $type		(string)		update or append
	*	@return $res		(object) 		object
	*/

	function updateNoteRecord($f,$type="update"){
		global $db;
		$f = astercrm::variableFiler($f);

		if ($type == 'update')

			$query= "UPDATE note SET "
					."note='".$f['note']."', "
					."priority=".$f['priority']." ,"
					."private='".$f['private']."', "
					."attitude='".$f['attitude']."' "					
					."WHERE id='".$f['noteid']."'";
		else
			if (empty($f['note']))
				$query= "UPDATE note SET "
						."attitude='".$f['attitude']."', "
						."private='".$f['private']."', "
						."priority=".$f['priority']." "
						."WHERE id='".$f['noteid']."'";
			else
				$query= "UPDATE note SET "
						."note=CONCAT(note,'<br>',now(),' ".$f['note']." by " .$_SESSION['curuser']['username']. "'), "
						."attitude='".$f['attitude']."', "
						."private='".$f['private']."', "
						."priority=".$f['priority']." "
						."WHERE id='".$f['noteid']."'";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	/**
	*  Actualiza un registro de la tabla.
	*
	*	@param $f	(array)		Arreglo que contiene los datos del formulario pasado.
	*	@return $res	(object)	Devuelve el objeto con la respuesta de la sentencia SQL ejecutada del UPDATE.
	*/
	
	function updateAccountRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$query= "UPDATE astercrm_account SET "
				."username='".$f['username']."', "
				."password='".$f['password']."', "
				."firstname='".$f['firstname']."', "
				."lastname='".$f['lastname']."', "
				."callerid='".$f['callerid']."', "
				."extension='".$f['extension']."', "
				."agent ='".$f['agent']."', "
				."usertype='".$f['usertype']."', "
				."channel='".$f['channel']."', "	// added 2007/10/30 by solo
				."extensions='".$f['extensions']."', "
				."groupid='".$f['groupid']."', "     // new add 2007-11-15
				."dialinterval='".$f['dialinterval']."', "
				."accountcode='".$f['accountcode']."',"	// added 2007/11/12 by solo
				."usertype_id='".$f['usertype_id']."' "	// added 2010/12/31 by shixb
				."WHERE id='".$f['id']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function updateAccountgroupRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$query= "UPDATE astercrm_accountgroup SET "
				."groupname='".$f['groupname']."', "
				."monitorforce='".$f['monitorforce']."', "
				."firstring='".$f['firstring']."', "
				."allowloginqueue='".$f['allowloginqueue']."', "
				."agentinterval='".$f['agentinterval']."', "
				."notice_interval='".$f['notice_interval']."', "
				."clear_popup='".$f['clear_popup']."', "
				."groupnote='".$f['groupnote']."',"
				."incontext='".$f['incontext']."', "
				."outcontext='".$f['outcontext']."' "
				."WHERE id='".$f['id']."'";
		
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}


	function updateKnowledgeRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$query= "UPDATE knowledge SET "
				."knowledgetitle='".$f['knowledgetitle']."', "
				."content='".$f['content']."', "
				."groupid='".$f['groupid']."' "
				."WHERE id='".$f['id']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	/**
	*  select a record form a table
	*
	*	@param  $id			(int)		identity of the record
	*	@param  $table		(string)	table name
	*	@return $res		(object)	object
	*/

	function &getRecord($id,$table){
		global $db;
		
		$query = "SELECT * FROM $table WHERE id = $id";
		astercrm::events($query);
		$row =& $db->getRow($query);
		return $row;
	}

	/**
	*  update a field in a table 
	*
	*	@param  $table		(string)	table name
	*	@param  $field		(string)	field need to be updated
	*	@param  $value		(string)	value want to update to
	*	@param  $id			(int)		identity of the record
	*	@return $res		(object)	object
	*/

	function updateField($table,$field,$value,$id){

		global $db;
		$f = astercrm::variableFiler($f);

		$query = "UPDATE $table SET $field='$value' WHERE id='$id'";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
		
	}


	/**
	*  insert a record to asterisk event log file
	*
	*	@param  $event			(string)	the string need to be appended to the log file
	*	@return 
	*/

	function events($event = null){
		if(LOG_ENABLED){
			$now = date("Y-M-d H:i:s");
   		
			$fd = fopen (FILE_LOG,'a');
			$log = $now." ".$_SERVER["REMOTE_ADDR"] ." - $event \n";
			fwrite($fd,$log);
			fclose($fd);
		}
	}

	/**
	*	check if there's a record in a table
	*
	*	@param  $tblName		(string)	table name
	*	@param  $fldName		(string)	field
	*	@param  $myValue		(string)	value
	*	@param  $type			(string)	the value is string(use ' in sql command) or not
	*	@param  $fldName1		(string)	
	*	@param  $myValue1		(string)	
	*	@param  $type1			(string)	
	*	@return $id				(int)		return identity of the record if exsits or else return '' 
	*/

	function checkValues($tblName,$fldName,$myValue,$type="string",$fldName1 = null,$myValue1 = null,$type1 = "string"){

		global $db;

		if ($type == "string")
			$query = "SELECT id FROM $tblName WHERE $fldName='$myValue'";
		else
			$query = "SELECT id FROM $tblName WHERE $fldName=$myValue";
		
		if ($fldName1 != null)
			if ($type1 == "string")
				$query .= "AND $fldName1='$myValue1'";
			else
				$query .= "AND $fldName1=$myValue1";

		
		astercrm::events($query);
		$id =& $db->getOne($query);
		return $id;		
	}

	/**
	*	generate a html table contains note list
	*
	*	@param  $id				(int)		identity
	*	@param  $type			(string)	customerid or contactid
	*	@return $html			(string)	HTML include the notes of the customer/contact
	*/

	function showNoteList($id,$type = 'customer'){
		$noteList =& astercrm::getNoteListByID($id,$type);
		$html = '
				<table border="1" width="100%" class="adminlist">
				';

		while	($noteList->fetchInto($row)){
			$html .= '
				<tr><td align="left" width="25">'. $row['creby'] .'
				</td><td>'.nl2br($row['note']).'</td><td>'.$row['codes'].'</td><td>'.$row['cretime'].'</td></tr>
				';
		}
		$html .= '</table>';

		return $html;
	}

	/**
	*	get customer detail from table
	*
	*	@param  $id				(int)		identity
	*	@param  $type			(string)	customerid or noteid
	*	@return $row			(array)		customer data array
	*/

	function &getCustomerByID($id,$type="customer"){
		global $db;
		if ($type == 'customer')
			return astercrm::getRecordById($id,'customer');//$query = "SELECT * FROM customer WHERE id = $id";
		elseif ($type == 'contact')
			$query = "SELECT * FROM customer RIGHT JOIN (SELECT customerid FROM contact WHERE id = $id ) g ON customer.id = g.customerid";
		else
			$query = "SELECT * FROM customer RIGHT JOIN (SELECT customerid FROM note WHERE id = $id ) g ON customer.id = g.customerid";
		
		astercrm::events($query);
		$row =& $db->getRow($query);
		return $row;
	}

	/**
	*	get conatct detail 
	*
	*	@param  $id				(int)		identity
	*	@param  $type			(string)	contactid or noteid
	*	@return $row			(array)		conatct data array
	*/

	function &getContactByID($id,$type="contact"){
		global $db;

		if ($type == 'contact')
			$query = "SELECT * FROM contact WHERE id = $id";
		elseif ($type == 'note')
			$query = "SELECT * FROM contact RIGHT JOIN (SELECT contactid FROM note WHERE id = $id ) g ON contact.id = g.contactid";
		
		astercrm::events($query);
		$row =& $db->getRow($query);
		return $row;
	}

	/**
	*	get contact list which are binding to a specific customer
	*
	*	@param  $id				(int)		customerid
	*	@return $res			(object)
	*/

	function &getContactListByID($customerid){
		global $db;
		$query = "SELECT * FROM contact WHERE customerid=$customerid";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function getGroupMemberListByID($groupid = null){
		global $db;
		if ($groupid == null)
			$query = "SELECT id,username,extension,agent,channel FROM astercrm_account";
		else
			$query = "SELECT id,username,extension,agent,channel FROM astercrm_account WHERE groupid =$groupid";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	/**
	*	get note list from table
	*
	*	@param  $id				(int)		identity
	*	@param  $type			(string)	customerid or contactid
	*	@return $res			(object)
	*/

	function &getNoteListByID($id,$type = 'customer'){
		global $db;
		
		if($type == "customer")
			$query = "SELECT * FROM note WHERE customerid = '$id' ORDER BY cretime DESC";
		else
			$query = "SELECT * FROM note WHERE contactid = '$id' ORDER BY cretime DESC";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	/**
	*	general survey add html
	*
	*	@param  $customerid		(int)		customerid
	*	@param  $contactid		(int)		contactid
	*	@return $html			(array)		HTML
	*/

	function surveyAdd($surveyid,$customerid = 0, $contactid = 0,$callerid='', $campaignid=0){
		global $locate,$db,$config;

		$html = '<form method="post" name="formSurvey" id="formSurvey"><table border="1" width="100%" class="adminlist">';
		$survey = astercrm::getRecordById($surveyid,"survey");
		$html .= '<tr><td>
									<input type="hidden" value="'.$survey['id'].'" name="surveyid" id="surveyid">
									<input type="hidden" value="'.$customerid.'" name="customerid" id="customerid">
									<input type="hidden" value="'.$contactid.'" name="contactid" id="contactid">
									<input type="hidden" value="'.$callerid.'" name="callerid" id="contactid">
									<input type="hidden" value="'.$campaignid.'" name="campaignid" id="contactid">'
									.$survey['surveyname'].'&nbsp;&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("Save").'" onclick="xajax_surveySave(xajax.getFormValues(\'formSurvey\'));"></td></tr>';
		if (trim($survey['surveynote']) != ""){
			$html .= '<tr><td>'.$survey['surveynote'].'</td></tr>';
		}
		$options = astercrm::getRecordsByField("surveyid",$surveyid,"surveyoptions");
		while ($options->fetchInto($option)) {
			$html .= '<tr id="tr-option-'.$option['id'].'"><td>'.$option['surveyoption']."(".$option['optionnote'].")<input type=\"hidden\" name=\"surveyoption[]\" value=\"".$option['id']."\"></td></tr>";
			if ($option['optiontype'] == "text"){
				$html .= "<tr><td><input type=\"text\" name=\"".$option['id']."-note\" size='60'></td></tr>";
			}else{
				$items = astercrm::getRecordsByField("optionid",$option['id'],"surveyoptionitems");
				if ($items){
					$html .='<tr  id="tr-items-'.$option['id'].'"><td>';
					while ($items->fetchInto($item)) {
						$html .= '<input type="'.$option['optiontype'].'" name="'.$option['id'].'-item[]"  value="'.$item['id'].'-'.$item['itemcontent'].'" '.$additional.'>'.$item['itemcontent'];
					}
					if ($config['survey']['enable_surveynote'] == true){
						$html .= " | ".$locate->Translate("Note")." <input type=\"text\" name=\"".$option['id']."-note\" size='20'></td></tr>";
					}
				}
			}
		}
		$html .= '</table></form>';
		return $html;
	}


	/**
	*	general note add html
	*
	*	@param  $customerid		(int)		customerid
	*	@param  $contactid		(int)		contactid
	*	@return $html			(array)		HTML
	*/

	function noteAdd($customerid,$contactid){
		global $locate;
		$html .= '
				<form method="post" name="formNote" id="formNote">
				<table border="1" width="100%" class="adminlist">
					<tr>
						<td nowrap align="left">'.$locate->Translate("note").'</td>
						<td align="left">
							<textarea rows="4" cols="50" id="note" name="note" wrap="soft" style="overflow:auto"></textarea>
							<input type="hidden" value="'.$customerid.'" name="customerid" id="customerid">
							<input type="hidden" value="'.$contactid.'" name="contactid" id="contactid">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("note_code").'</td>
						<td align="left"><select id="note_code" name="note_code">';

			$getAllNoteCodes =& astercrm::getAllNoteCodes();
			foreach($getAllNoteCodes as $tmp) {
				$html .='<option value="'.$tmp['code'].'">'.$tmp['code'].'</option>';
			}
		
			$html .='
						</select></td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("priority").'</td>
						<td align="left">
							<select id="priority" name="priority">
								<option value=0>0</option>
								<option value=1>1</option>
								<option value=2>2</option>
								<option value=3>3</option>
								<option value=4>4</option>
								<option value=5 selected>5</option>
								<option value=6>6</option>
								<option value=7>7</option>
								<option value=8>8</option>
								<option value=9>9</option>
								<option value=10>10</option>
							</select> 

							&nbsp;  <input type="radio" name="attitude"   value="10"/><img src="skin/default/images/10.gif" width="25px" height="25px" border="0" /> 
							<input type="radio" name="attitude" value="5"/><img src="skin/default/images/5.gif" width="25px" height="25px" border="0" /> 
							<input type="radio" name="attitude"  value="-1"/><img src="skin/default/images/-1.gif" width="25px" height="25px" border="0" />
							<input type="radio" name="attitude"  value="0" checked/> <img src="skin/default/images/0.gif" width="25px" height="25px" border="0" />
						</td>
					</tr>
					<tr>
						<td nowrap colspan=2 align=right><input type="button" id="btnAddNote" name="btnAddNote" value="'.$locate->Translate("continue").'" onclick="xajax_saveNote(xajax.getFormValues(\'formNote\'));return false;"></td>
					</tr>
				';
			
		$html .='
				</table>
				</form>
				';
		return $html;
	}

	function countSurvey($callerid=''){
		global $db;
		$campaignid = 0;
		$surveyNum = 0;
		$surveyid = 0;

		# 尝试获取campaignid
		if($callerid != ''){
			$query = "SELECT * FROM dialedlist WHERE dialednumber = '$callerid' AND dialedtime > (now()-INTERVAL 600 SECOND) ORDER BY dialtime DESC LIMIT 1";
			astercrm::events($query);
			$cres = $db->query($query);
			if($cres->fetchInto($row)){
				$campaignid= $row['campaignid'];
			}
		}
		# 计算该campaign下所拥有的survey的数量
		if($campaignid == 0){
			$query = "SELECT COUNT(*) FROM survey WHERE enable=1 AND groupid = ".$_SESSION['curuser']['groupid']." AND campaignid = 0 ";
			astercrm::events($query);
			$resCount =& $db->getOne($query);
		}else{
			$query = "SELECT COUNT(*) FROM survey WHERE enable=1 AND groupid = ".$_SESSION['curuser']['groupid']." AND (campaignid = 0 OR campaignid=$campaignid)";
			astercrm::events($query);
			$resCount =& $db->getOne($query);
		}

		if ($resCount){
			$surveyNum = $resCount;
		}

		# 获取该campaign下正在使用的survey的id
		if($campaignid == 0){
			$query = "SELECT id FROM survey WHERE enable=1 AND groupid = ".$_SESSION['curuser']['groupid']." AND campaignid = 0 ORDER BY cretime DESC  LIMIT 0,1";
			astercrm::events($query);
			$resId =& $db->getOne($query);
		}else{
			$query = "SELECT id FROM survey WHERE enable=1 AND groupid = ".$_SESSION['curuser']['groupid']." AND (campaignid = 0 OR campaignid=$campaignid) ORDER BY cretime DESC LIMIT 0,1";
			astercrm::events($query);
			$resId =& $db->getOne($query);
		}
		if ($resId){
			$surveyid = $resId;
		}

		$res['count'] = $surveyNum;
		$res['id'] = $surveyid;
		$res['callerid'] = $callerid;
		$res['campaignid'] = $campaignid;
		return $res;
	}

	function surveyList($customerid,$contactid,$callerid = ''){
		global $locate,$config, $db;
		$html .= '<form method="post" name="fSurveyList" id="fSurveyList">';
		$html .= '<input type="hidden" value="'.$customerid.'" name="customerid" id="customerid">';
		$html .= '<input type="hidden" value="'.$contactid.'" name="contactid" id="contactid">';

		$html .= '	<table border="1" width="100%" class="adminlist"><tr>';
		
		$query = "SELECT * FROM survey WHERE enable=1 AND groupid = ".$_SESSION['curuser']['groupid']." ORDER BY cretime";
		$res = $db->query($query);

		while ($res->fetchInto($row)) {
			//get survey title and id
			$surveytitle = $row['surveyname'];
			$surveyid = $row['id'];
			$html .= "<tr><td>$surveytitle  [<a href=? onclick=\"xajax_showSurvey('$surveyid','$customerid','$contactid',$callerid,'".$row['campaignid']."');return false;\">".$locate->Translate("Add")."</a>]</td></tr>";
		}

		$html .= '	</table>';
		$html .= '</form>';
		return $html;
	}



	/**
	*  Imprime la forma para agregar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param callerid
	*	@param customerid
	*	@param contactid
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma para insertar 
	*							un nuevo registro.
	*/

	function formAdd($callerid = null,$customerid = null, $contactid = null,$campaignid=0,$diallistid=0,$note='',$srcname){
		global $locate,$config;
		
		$html = '
				<!-- No edit the next line -->
				<form method="post" name="f" id="f">
				<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left" colspan="2">'.$locate->Translate("add_record").' <a href="?" onclick="dial(\''.$callerid.'\',\'\',\'\',\'\',\''.$diallistid.'\');return false;">'. $callerid .'</a><input type="hidden" value="'.$callerid.'" id="iptcallerid" name="iptcallerid"> <span id="diallist_control"></span>';
				
				if($campaignid > 0){
					$html .= "<span id=\"diallist_control\"><input type=\"button\" id=\"insert_dnc_list\" name=\"insert_dnc_list\" value=\"".$locate->Translate("Add Dnc_list")."\" onclick=\"xajax_insertIntoDnc('".$callerid."','".$campaignid."');return false;\"/>";
					if($diallistid > 0){
						$html .="&nbsp;&nbsp;&nbsp;<input type=\"button\" id=\"skip_diallist\" name=\"skip_diallist\" value=\"".$locate->Translate("Skip this number")."\" onclick=\"xajax_skipDiallist('".$callerid."','".$diallistid."');return false;\"/>";
					}
					$html .= '</span>';
				}
				$html .= '</td>
				</tr>';
		
		if ($customerid == null || $customerid ==0){
			$customerid = 0;
			$html .= '
					<tr>
						<td nowrap align="left">'.$locate->Translate("customer_name").'</td>
						<td align="left">';
						if($_SESSION['curuser']['language'] != 'ZH' && $_SESSION['curuser']['country'] != 'cn'){
							$html .= '<select id="customertitle" name="customertitle">
									<option value="Mr" >'.$locate->Translate("Mr").'</option>
									<option value="Miss">'.$locate->Translate("Miss").'</option>
									<option value="Ms" >'.$locate->Translate("Ms").'</option>
									<option value="Mrs" >'.$locate->Translate("Mrs").'</option>
									<option value="other" >'.$locate->Translate("Other").'</option>
							</select>&nbsp;';
							if(!empty($srcname) && $srcname != '<unknown>') {
								$html .= '<input type="text" id="customer" name="customer" value="'.$srcname.'" onkeyup="ajax_showOptions(this,\'getCustomersByLetters\',event)" size="25" maxlength="50" autocomplete="off">';
							} else {
								$html .= '<input type="text" id="customer" name="customer" value="" onkeyup="ajax_showOptions(this,\'getCustomersByLetters\',event)" size="25" maxlength="50" autocomplete="off">';
							}
						}else{
							if(!empty($srcname) && $srcname != '<unknown>') {
								$html .= '<input type="text" id="customer" name="customer" value="'.$srcname.'" onkeyup="ajax_showOptions(this,\'getCustomersByLetters\',event)" size="25" maxlength="50" autocomplete="off">';
							} else {
								$html .= '<input type="text" id="customer" name="customer" value="" onkeyup="ajax_showOptions(this,\'getCustomersByLetters\',event)" size="25" maxlength="50" autocomplete="off">';
							}
							$html .= '&nbsp;
							<select id="customertitle" name="customertitle">
									<option value="Mr" >'.$locate->Translate("Mr").'</option>
									<option value="Miss">'.$locate->Translate("Miss").'</option>
									<option value="Ms" >'.$locate->Translate("Ms").'</option>
									<option value="Mrs" >'.$locate->Translate("Mrs").'</option>
									<option value="other" >'.$locate->Translate("Other").'</option>
							</select>';
						}
						if($config['system']['customer_leads'] == 'move') {
							$html .= ' <input type="checkbox" name="customer_leads_check" id="customer_leads" />'.$locate->Translate("move_to_customer_lead");
						} else if($config['system']['customer_leads'] == 'copy') {
							$html .= ' <input type="checkbox" name="customer_leads_check" id="customer_leads" />'.$locate->Translate("copy_to_customer_lead");
						} else if($config['system']['customer_leads'] == 'default_move') {
							$html .= ' <input type="checkbox" name="customer_leads_check" id="customer_leads" checked/>'.$locate->Translate("move_to_customer_lead");
						} else if($config['system']['customer_leads'] == 'default_copy') {
							$html .= ' <input type="checkbox" name="customer_leads_check" id="customer_leads" checked/>'.$locate->Translate("copy_to_customer_lead");
						}

						$html .= '<br /><input type="button" value="'.$locate->Translate("confirm").'" id="btnConfirmCustomer" name="btnConfirmCustomer" onclick="btnConfirmCustomerOnClick();"><input type="hidden" id="customerid" name="customerid" value="0">
						<input type="hidden" id="hidAddCustomerDetails" name="hidAddCustomerDetails" value="OFF">
						[<a href=? onclick="
							if (xajax.$(\'hidAddCustomerDetails\').value == \'OFF\'){
								showObj(\'trAddCustomerDetails\');
								xajax.$(\'hidAddCustomerDetails\').value = \'ON\';
							}else{
								hideObj(\'trAddCustomerDetails\');
								xajax.$(\'hidAddCustomerDetails\').value = \'OFF\';
							};
							return false;">
							'.$locate->Translate("detail").'
						</a>] &nbsp; [<a href=? onclick="
								if (xajax.$(\'hidAddBankDetails\').value == \'OFF\'){
									showObj(\'trAddBankDetails\');
									xajax.$(\'hidAddBankDetails\').value = \'ON\';
								}else{
									hideObj(\'trAddBankDetails\');
									xajax.$(\'hidAddBankDetails\').value = \'OFF\';
								}
								return false;">'.$locate->Translate("bank").'</a>]
							&nbsp; [<a href=? onclick="addSchedulerDial(\'0\'); return false;">'.$locate->Translate("Scheduler Dial").'</a>] <input type="hidden" id="addedSchedulerDialId" name="addedSchedulerDialId" value="" />
						</td>
					</tr>
					<tr id="trAddSchedulerDial" name="trAddSchedulerDial" style="display:none">		
					</tr>
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("first_name").'</td>
						<td align="left"><input type="text" id="first_name" name="first_name" size="35" maxlength="50"></td>
					</tr>
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("last_name").'</td>
						<td align="left"><input type="text" id="last_name" name="last_name" size="35" maxlength="50"></td>
					</tr>
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("address").'</td>
						<td align="left"><input type="text" id="address" name="address" size="35" maxlength="200"></td>
					</tr>
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("customer_contact").'</td>
						<td align="left">
							<input type="text" id="customerContact" name="customerContact" size="35" maxlength="35"><br>
							<select id="customerContactGender" name="customerContactGender">
								<option value="male">'.$locate->Translate("male").'</option>
								<option value="female">'.$locate->Translate("female").'</option>
								<option value="unknown" selected>'.$locate->Translate("unknown").'</option>
							</select>
						</td>
					</tr>				
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("address").'</td>
						<td align="left"><input type="text" id="address" name="address" size="35" maxlength="200"></td>
					</tr>
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("zipcode").'/'.$locate->Translate("city").'</td>
						<td align="left"> <input type="text" id="zipcode" name="zipcode" size="10" maxlength="10">&nbsp;&nbsp;<input type="text" id="city" name="city" size="17" maxlength="50"></td>
					</tr>
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("state").'</td>
						<td align="left"><input type="text" id="state" name="state" size="35" maxlength="50"></td>
					</tr>
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("country").'</td>
						<td align="left"><input type="text" id="country" name="country" size="35" maxlength="50"></td>
					</tr>
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("customer_phone").'</td>
						<td align="left"><input type="text" id="customerPhone" name="customerPhone" size="35" maxlength="50">-<input type="text" id="customerPhone_ext" name="customerPhone_ext" size="8" maxlength="8"></td>
					</tr>
					<tr name="trAddCustomerDetails" id="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("mobile").'</td>
						<td align="left"><input type="text" id="mainMobile" name="mainMobile" size="35" value="'.$callerid.'"></td>
					</tr>
					<tr name="trAddCustomerDetails" id="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("email").'</td>
						<td align="left"><input type="text" id="mainEmail" name="mainEmail" size="35"></td>
					</tr>				
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("website").'</td>
						<td align="left"><input type="text" id="website" name="website" size="35" maxlength="100" value="http://"><br><input type="button" value="'.$locate->Translate("browser").'" onclick="openWindow(xajax.$(\'website\').value);return false;"></td>
					</tr>
					<!--<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("zipcode").'</td>
						<td align="left"><input type="text" id="zipcode" name="zipcode" size="10" maxlength="10"></td>
					</tr>-->
					<tr name="trAddCustomerDetails" id="trAddCustomerDetails" style="display:none">
						<td nowrap align="left">'.$locate->Translate("fax").'</td>
						<td align="left"><input type="text" id="mainFax" name="mainFax" size="35">-<input type="text" id="mainFax_ext" name="mainFax_ext" size="8" maxlength="8"></td>
					</tr>
					<tr id="trAddCustomerDetails" name="trAddCustomerDetails" style="display:none">
						<td nowrap align="left" style="border-bottom:1px double orange;">'.$locate->Translate("category").'</td>
						<td align="left" style="border-bottom:1px double orange"><input type="text" id="category" name="category" size="35"></td>
					</tr>';
					/*
					*  control bank data
					*/
					$html .='
						
							<input type="hidden" id="hidAddBankDetails" name="hidAddBankDetails" value="OFF">
						<!--********************-->
						
						<tr id="trAddBankDetails" name="trAddBankDetails" style="display:none">
							<td nowrap align="left">'.$locate->Translate("bank_account_name").'</td>
							<td align="left"><input type="text" id="bankaccountname" name="bankaccountname" size="35"></td>
						</tr>
						<tr id="trAddBankDetails" name="trAddBankDetails" style="display:none">
						<td nowrap align="left" style="border-top:1px double orange;">'.$locate->Translate("bank_name").'</td>
						<td align="left" style="border-top:1px double orange"><input type="text" id="bankname" name="bankname" size="35"></td>
						</tr>
						<tr id="trAddBankDetails" name="trAddBankDetails" style="display:none">
							<td nowrap align="left">'.$locate->Translate("bank_zip").'</td>
							<td align="left"><input type="text" id="bankzip" name="bankzip" size="35"></td>
						</tr>
						<tr id="trAddBankDetails" name="trAddBankDetails" style="display:none">
							<td nowrap align="left">'.$locate->Translate("bank_account").'</td>
							<td align="left"><input type="text" id="bankaccount" name="bankaccount" size="35"></td>
						</tr>	
						<!--********************-->
						';
		}else{
			$customer =& astercrm::getCustomerByID($customerid);
			$html .= '
					<tr>
						<td nowrap align="left"><a href=? onclick="xajax_showCustomer('. $customerid .');return false;">'.$locate->Translate("customer_name").'</a></td>
						<td align="left">';
						if($_SESSION['curuser']['language'] != 'ZH' && $_SESSION['curuser']['country'] != 'cn'){
							$html .= $locate->Translate($customer['customertitle']).'&nbsp;<input type="text" id="customer" name="customer" value="'. $customer['customer'].'" onkeyup="ajax_showOptions(this,\'getCustomersByLetters\',event)" size="25" maxlength="50" autocomplete="off" readOnly>';
						}else{
							$html .= '<input type="text" id="customer" name="customer" value="'. $customer['customer'].'" onkeyup="ajax_showOptions(this,\'getCustomersByLetters\',event)" size="25" maxlength="50" autocomplete="off" readOnly>&nbsp;'.$locate->Translate($customer['customertitle']);
						}
						if($config['system']['customer_leads'] == 'move') {
							$html .= ' <input type="checkbox" name="customer_leads_check" id="customer_leads" />'.$locate->Translate("move_to_customer_lead");
						} else if($config['system']['customer_leads'] == 'copy') {
							$html .= ' <input type="checkbox" name="customer_leads_check" id="customer_leads" />'.$locate->Translate("copy_to_customer_lead");
						} else if($config['system']['customer_leads'] == 'default_move') {
							$html .= ' <input type="checkbox" name="customer_leads_check" id="customer_leads" checked/>'.$locate->Translate("move_to_customer_lead");
						} else if($config['system']['customer_leads'] == 'default_copy') {
							$html .= ' <input type="checkbox" name="customer_leads_check" id="customer_leads" checked/>'.$locate->Translate("copy_to_customer_lead");
						}

						$html .= '<BR /><input type="button" value="'.$locate->Translate("cancel").'" id="btnConfirmCustomer" name="btnConfirmCustomer" onclick="btnConfirmCustomerOnClick();"><input type="hidden" id="customerid" name="customerid" value="'. $customerid .'"></td>
					</tr>
					';
		}
		if($config['system']['enable_contact'] != '0'){ //控制contact模块的显示与隐藏
			if ($contactid == null){
					$html .='
						<tr>
							<td nowrap align="left">'.$locate->Translate("contact").'</td>
							<td align="left"><input type="text" id="contact" name="contact" value="" onkeyup="ajax_showOptions(this,\'customerid='.$customerid.'&getContactsByLetters\',event)" size="35" maxlength="50" autocomplete="off"><BR /><input id="btnConfirmContact" name="btnConfirmContact" type="button" onclick="btnConfirmContactOnClick();return false;" value="'.$locate->Translate("confirm").'"><input type="hidden" id="contactid" name="contactid" value="">
							<input type="hidden" id="contactDetail" name="contactDetail" value="OFF">
							[<a href=? onclick="
								if (xajax.$(\'contactDetail\').value == \'OFF\'){
									xajax.$(\'genderTR\').style.display = \'\';
									xajax.$(\'positionTR\').style.display = \'\';
									xajax.$(\'phoneTR\').style.display = \'\';
									xajax.$(\'phone1TR\').style.display = \'\';
									xajax.$(\'phone2TR\').style.display = \'\';
									xajax.$(\'mobileTR\').style.display = \'\';
									xajax.$(\'faxTR\').style.display = \'\';
									xajax.$(\'emailTR\').style.display = \'\';
									xajax.$(\'contactDetail\').value = \'ON\';
								}else{
									xajax.$(\'genderTR\').style.display = \'none\';
									xajax.$(\'positionTR\').style.display = \'none\';
									xajax.$(\'phoneTR\').style.display = \'none\';
									xajax.$(\'phone1TR\').style.display = \'none\';
									xajax.$(\'phone2TR\').style.display = \'none\';
									xajax.$(\'mobileTR\').style.display = \'none\';
									xajax.$(\'faxTR\').style.display = \'none\';
									xajax.$(\'emailTR\').style.display = \'none\';
									xajax.$(\'contactDetail\').value = \'OFF\';
								};
								return false;">
								'.$locate->Translate("detail").'
							</a>]
							</td>
						</tr>
						<tr name="genderTR" id="genderTR" style="display:none">
							<td nowrap align="left">'.$locate->Translate("gender").'</td>
							<td align="left">
								<select id="contactGender" name="contactGender">
									<option value="male">'.$locate->Translate("male").'</option>
									<option value="female">'.$locate->Translate("female").'</option>
									<option value="unknown" selected>'.$locate->Translate("unknown").'</option>
								</select>
							</td>
						</tr>
						<tr name="positionTR" id="positionTR" style="display:none">
							<td nowrap align="left">'.$locate->Translate("position").'</td>
							<td align="left"><input type="text" id="position" name="position" size="35"></td>
						</tr>
						<tr name="phoneTR" id="phoneTR" style="display:none">
							<td nowrap align="left">'.$locate->Translate("phone").'</td>
							<td align="left"><input type="text" id="phone" name="phone" size="35" value="'. $callerid .'">-<input type="text" id="ext" name="ext" size="8" maxlength="8" value=""></td>
						</tr>
						<tr name="phone1TR" id="phone1TR" style="display:none">
							<td nowrap align="left">'.$locate->Translate("phone1").'</td>
							<td align="left"><input type="text" id="phone1" name="phone1" size="35" value="">-<input type="text" id="ext1" name="ext1" size="8" maxlength="8" value=""></td>
						</tr>
						<tr name="phone2TR" id="phone2TR" style="display:none">
							<td nowrap align="left">'.$locate->Translate("phone2").'</td>
							<td align="left"><input type="text" id="phone2" name="phone2" size="35" value="">-<input type="text" id="ext2" name="ext2" size="8" maxlength="8" value=""></td>
						</tr>
						<tr name="mobileTR" id="mobileTR" style="display:none">
							<td nowrap align="left">'.$locate->Translate("mobile").'</td>
							<td align="left"><input type="text" id="mobile" name="mobile" size="35"></td>
						</tr>
						<tr name="faxTR" id="faxTR" style="display:none">
							<td nowrap align="left">'.$locate->Translate("fax").'</td>
							<td align="left"><input type="text" id="fax" name="fax" size="35">-<input type="text" id="fax_ext" name="fax_ext" size="8" maxlength="8" value=""></td>
						</tr>
						<tr name="emailTR" id="emailTR" style="display:none">
							<td nowrap align="left">'.$locate->Translate("email").'</td>
							<td align="left"><input type="text" id="email" name="email" size="35"></td>
						</tr>					
						';
			}else{
				$contact =& astercrm::getContactByID($contactid);

					$html .='
						<tr>
							<td nowrap align="left"><a href=? onclick="xajax_showContact('. $contactid .');return false;">'.$locate->Translate("contact").'</a></td>
							<td align="left"><input type="text" id="contact" name="contact" value="'. $contact['contact'].'" onkeyup="ajax_showOptions(this,\'getContactsByLetters\',event)" size="35" maxlength="50" autocomplete="off" readOnly><input type="button" value="'.$locate->Translate("cancel").'" id="btnConfirmContact" name="btnConfirmContact" onclick="btnConfirmContactOnClick();"><input type="hidden" id="contactid" name="contactid" value="'. $contactid .'"></td>
						</tr>
						';
			}
		}

		//add survey html
		//$html .= '<tr><td colspan="2">';

		//$surveyHTML =& astercrm::generateSurvey();
		//$html .= $surveyHTML;

		//$html .= '</tr></td>';
		//if(!defined('HOME_DIR')) define('HOME_DIR',dirname(dirname(__FILE__)));
		//add note html

		$html .='
				<tr>
					<td nowrap align="left">'.$locate->Translate("note").'(<input type="checkbox" name="sltPrivate" id="sltPrivate" value="0" onclick="if(this.checked){ document.getElementById(\'private\').value=0;}else{ document.getElementById(\'private\').value=1;}"';
					if($config['system']['default_share_note']){
					
						$html .= 'checked>'.$locate->Translate("share").')<input type="hidden" value="0" name="private" id="private"></td>';
					}else{
						$html .= '>'.$locate->Translate("share").')<input type="hidden" value="1" name="private" id="private"></td>';
					}
					$html .='<td align="left">
						<textarea rows="4" cols="50" id="note" name="note" wrap="soft" style="overflow:auto;">'.$note.'</textarea>
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("note_code").'</td>
					<td align="left"><select id="note_code" name="note_code">';

		$getAllNoteCodes =& astercrm::getAllNoteCodes();
		foreach($getAllNoteCodes as $tmp) {
			$html .='<option value="'.$tmp['code'].'">'.$tmp['code'].'</option>';
		}
		
		$html .='</select></td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("priority").'</td>
				<td align="left">
					<select id="priority" name="priority">
						<option value=0>0</option>
						<option value=1>1</option>
						<option value=2>2</option>
						<option value=3>3</option>
						<option value=4>4</option>
						<option value=5 selected>5</option>
						<option value=6>6</option>
						<option value=7>7</option>
						<option value=8>8</option>
						<option value=9>9</option>
						<option value=10>10</option>
					</select> 
					&nbsp;  <input type="radio" name="attitude"   value="10"/><img src="skin/default/images/10.gif" width="25px" height="25px" border="0" /> 
					<input type="radio" name="attitude" value="5"/><img src="skin/default/images/5.gif" width="25px" height="25px" border="0" /> 
					<input type="radio" name="attitude"  value="-1"/><img src="skin/default/images/-1.gif" width="25px" height="25px" border="0" />
					<input type="radio" name="attitude"  value="0" checked/> <img src="skin/default/images/0.gif" width="25px" height="25px" border="0" />
				</td>
			</tr>
			<tr>
				<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_save(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
			</tr>';
			
		$html .='
			</table>
			</form>
			'.$locate->Translate("ob_fields").'
			';
		return $html;
	}

	/**
	*  Devuelte el registro de acuerdo al $id pasado.
	*
	*	@param $id	(int)	Identificador del registro para hacer la b&uacute;squeda en la consulta SQL.
	*	@return $row	(array)	Arreglo que contiene los datos del registro resultante de la consulta SQL.
	*/
	
	function &getRecordByID($id,$table){
		global $db;
		
		$query = "SELECT * FROM $table "
				." WHERE id = $id";
		astercrm::events($query);
		$row =& $db->getRow($query);
		return $row;
	}

	function getRecordByField($field,$value,$table){
		global $db;
		$value = preg_replace("/'/","\\'",$value);
		if (is_numeric($value)){
			$query = "SELECT * FROM $table WHERE $field = $value ";
		}else{
			$query = "SELECT * FROM $table WHERE $field = '$value' ";
		}
		if($table == 'diallist'){
			$query .= " ORDER BY callOrder DESC ,id ASC ";
		}
		$query .= " LIMIT 0,1 ";
		astercrm::events($query);
		$row =& $db->getRow($query);
		return $row;
	}

	function getDialNumByAgent($userexten){
		global $db;
		
		$query = "SELECT * FROM diallist WHERE assign = '".$userexten."' AND dialtime > '0000-00-00 00:00:00' AND dialtime < now() ORDER BY dialtime ASC ,callOrder DESC ,id ASC LIMIT 0,1";

		astercrm::events($query);
		if(!($row =& $db->getRow($query))){
			$query = "SELECT * FROM diallist WHERE assign = '".$userexten."' AND dialtime = '0000-00-00 00:00:00' ORDER BY callOrder DESC ,id ASC LIMIT 0,1";
			$row =& $db->getRow($query);
		}
		return $row;
	}

	function getDialNumCountByAgent($userexten){
		global $db;
		
		$query = "SELECT count(*) FROM diallist WHERE assign = '".$userexten."' AND dialtime < now()";

		astercrm::events($query);
		$row =& $db->getOne($query);
		return $row;
	}

	function getRecordsByField($field,$value,$table){
		global $db;
		$value = preg_replace("/'/","\\'",$value);
		if (is_numeric($value)){
			$query = "SELECT * FROM $table WHERE $field = $value ";
		}else{
			$query = "SELECT * FROM $table WHERE $field = '$value' ";
		}
		if($table == 'diallist') $query .= " ORDER BY id ASC ";
		astercrm::events($query);
		$row =& $db->query($query);
		return $row;
	}

	function getCountByField($field = '',$value = '',$table){
		global $db;
		$value = preg_replace("/'/","\\'",$value);
		if (is_numeric($value)){
			$query = "SELECT count(*) FROM $table WHERE $field = $value";
		}else{
			if ($field != '' || $value != '')
				$query = "SELECT count(*) FROM $table WHERE $field = '$value'";
			else
				$query = "SELECT count(*) FROM $table ";
		}
		astercrm::events($query);
		$row =& $db->getOne($query);
		return $row;
	}

	function getOptions($surveyid){

		global $db;
		
		$query= "SELECT * FROM surveyoptions "
				." WHERE "
				."surveyid = " . $surveyid 
				." ORDER BY cretime ASC";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function insertNewSurveyResult($surveyid,$surveyoption,$surveynote,$customerID,$contactID){
		global $db;
		
		$query= "INSERT INTO surveyresult SET "
				."surveyid='".$surveyid."', "
				."surveyoption='".$surveyoption."', "
				."surveynote='".$surveynote."', "
				."customerid='".$customerID."', "
				."contactid='".$contactID."', "
				."cretime=now(), "
				."creby='".$_SESSION['curuser']['username']."'";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	/**
	*  generate HTML to add survey
	*  HTML include survey title, all survey options, survey note
	*	@return $html	(string)	
	*							
	*/

	function &generateSurvey(){
		global $db,$locate;

		$query = "SELECT * FROM survey WHERE enable=1 AND groupid = ".$_SESSION['curuser']['groupid']." ORDER BY cretime";
		astercrm::events($query);
		$res =& $db->query($query);

		if (!$res)//{
			return '';
//		}elseif($resCount == 1){
//			
//			$objResponse = new xajaxResponse();
//			while ($res->fetchInto($row)) {
//				$surveytitle = $row['surveyname'];
//				$surveyid = $row['id'];
//				$objResponse->addScript("showSurvey('$surveyid');return false;");
//				break;
//			}
//			echo "haha";exit;
//		}
	
		$html = "<table width='100%'>";
		while ($res->fetchInto($row)) {
			//get survey title and id
			$surveytitle = $row['surveyname'];
			$surveyid = $row['id'];
			$html .= "<tr><td>$surveytitle  [<a href=? onclick=\"showSurvey('$surveyid');return false;\">".$locate->Translate("Add")."</a>]</td></tr>";
		}

		$html .= "</table>";
		return $html;
		

		//get survey options
/*
		$options =& astercrm::getOptions($surveyid);
		if (!$options)
			return '';
		else {
			while ($options->fetchInto($row)) {
				$html .= "<tr><td><input type='radio' value='".$row['surveyoption']."' id='surveyoption' name='surveyoption'>".$row['surveyoption']."</td></tr>";
			}
			$html .= "<tr><td><input type='text' value='' id='surveynote' name='surveynote' size='50'></td></tr>";
		}
*/
	}
	/**
	*  Imprime la forma para editar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser editado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma con los datos 
	*									a extraidos de la base de datos para ser editados 
	*/
	
	function formEdit($id , $type){
		global $locate; global $db;
		if ($type == 'note'){
			$note =& astercrm::getRecordById($id,'note');
			for ($i=0;$i<11;$i++){
				$options .= "<option value='$i' ";
				if (trim($note['priority']) == $i)
					$options .= 'selected>';
				else
					$options .= '>';

				$options .= $i."</option>";
			}
		//	print $options;
		//	exit;
			$html = '
					<form method="post" name="f" id="f">
					<input type="hidden" id="noteid"  name="noteid" value="'.$note['id'].'">
					<table border="0" width="100%">
					<tr>
						<td nowrap align="left">'.$locate->Translate("note").'(<input type="checkbox" name="sltPrivate" id="sltPrivate" value="0" onclick="if(this.checked){ document.getElementById(\'private\').value=0;}else{ document.getElementById(\'private\').value=1;}" ';
						if($note['private'] == 0) $html .= 'checked';
					$html .= '>'.$locate->Translate("share").')<input type="hidden"  name="private" id="private" value="'.$note['private'].'"></td>
						<td align="left">'.nl2br($note['note']). '</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("append").'</td>
						<td align="left"><textarea rows="4" cols="50" id="note" name="note" wrap="soft" style="overflow:auto"></textarea></td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("priority").'</td>
						<td align="left">
							<select id="priority" name="priority">'.$options.'</select>

							&nbsp;  <input type="radio" name="attitude"   value="10" ';
							if($note['attitude'] == '10'){
								$html .= 'checked';
							}
							$html .= '/><img src="skin/default/images/10.gif" width="25px" height="25px" border="0" /> 
							<input type="radio" name="attitude"  value="5" ';
							if($note['attitude'] == '5'){
								$html .= 'checked';
							}
							$html .= ' /><img src="skin/default/images/5.gif" width="25px" height="25px" border="0" /> 
							<input type="radio" name="attitude"  value="-1" ';
							if($note['attitude'] == '-1'){
								$html .= 'checked';
							}
							$html .= ' 
							/><img src="skin/default/images/-1.gif" width="25px" height="25px" border="0" />
							<input type="radio" name="attitude"  value="0" ';
							if($note['attitude'] == '0'){
								$html .= 'checked';
							}
							$html .= ' 
							/> <img src="skin/default/images/0.gif" width="25px" height="25px" border="0" />
						</td>
					</tr>
					<tr>
						<td colspan="2" align="center">[<a href=? onclick="xajax_showCustomer(\'' . $note['customerid'] . '\');return false;">'.$locate->Translate("customer").'</a>]&nbsp;&nbsp;&nbsp;&nbsp;[<a href=? onclick="xajax_showContact(\'' . $note['contactid'] . '\');return false;">'.$locate->Translate("contact").'</a>]</td>
					</tr>
					<tr>
						<td colspan="2" align="center"><button id="btnContinue" name="btnContinue"  onClick=\'xajax_update(xajax.getFormValues("f"),"note");return false;\'>'.$locate->Translate("continue").'</button></td>
					</tr>
					';

		}elseif ($type == 'customer'){
			$customer =& astercrm::getCustomerByID($id);
			if ($customer['contactgender'] == 'male')
				$customerMaleSelected = 'selected';
			elseif ($customer['contactgender'] == 'female')
				$customerFemaleSelected = 'selected';
			else
				$customerUnknownSelected = 'selected';

			$html = '
					<form method="post" name="frmCustomerEdit" id="frmCustomerEdit">
					<table border="0" width="100%">
					<tr id="customerTR" name="customerTR">
						<td nowrap align="left">'.$locate->Translate("customer_name").'</td>
						<td align="left">';
						if($customer['customertitle'] == 'Mr'){
							$slt['Mr'] = 'selected';
						}elseif($customer['customertitle'] == 'Miss'){
							$slt['Miss'] = 'selected';
						}elseif($customer['customertitle'] == 'Ms'){
							$slt['Ms'] = 'selected';
						}elseif($customer['customertitle'] == 'Mrs'){
							$slt['Mrs'] = 'selected';
						}elseif($customer['customertitle'] == 'other'){
							$slt['other'] = 'selected';
						}
						$customertile = '<select id="customertitle" name="customertitle">
								<option value="Mr" '.$slt['Mr'].'>'.$locate->Translate("Mr").'</option>
								<option value="Miss" '.$slt['Miss'].'>'.$locate->Translate("Miss").'</option>
								<option value="Ms" '.$slt['Ms'].'>'.$locate->Translate("Ms").'</option>
								<option value="Mrs" '.$slt['Mrs'].'>'.$locate->Translate("Mrs").'</option>
								<option value="other" '.$slt['other'].'>'.$locate->Translate("Other").'</option>
						</select>';
						if($_SESSION['curuser']['language'] != 'ZH' && $_SESSION['curuser']['country'] != 'cn'){
							$html .= $customertile.'&nbsp;<input type="text" id="customer" name="customer" size="35" maxlength="100" value="' . $customer['customer'] . '">';
						}else{
							$html .= '<input type="text" id="customer" name="customer" size="35" maxlength="100" value="' . $customer['customer'] . '">&nbsp;'.$customertile;
						}
						$html .= '<input type="hidden" id="customerid"  name="customerid" value="'.$customer['id'].'"><BR />
						<input type="hidden" id="hidEditCustomerDetails" name="hidEditCustomerDetails" value="ON">
						<input type="hidden" id="hidEditBankDetails" name="hidEditBankDetails" value="ON">
					[<a href=? onclick="
						if (xajax.$(\'hidEditCustomerDetails\').value == \'OFF\'){
							showObj(\'trEditCustomerDetails\');
							xajax.$(\'hidEditCustomerDetails\').value = \'ON\';
						}else{
							hideObj(\'trEditCustomerDetails\');
							xajax.$(\'hidEditCustomerDetails\').value = \'OFF\';
						};
						return false;">
						'.$locate->Translate("detail").'
					</a>] &nbsp; [<a href=? onclick="
							if (xajax.$(\'hidEditBankDetails\').value == \'OFF\'){
								showObj(\'trEditBankDetails\');
								xajax.$(\'hidEditBankDetails\').value = \'ON\';
							}else{
								hideObj(\'trEditBankDetails\');
								xajax.$(\'hidEditBankDetails\').value = \'OFF\';
							}
							return false;">'.$locate->Translate("bank").'</a>]					
						</td>
					</tr>
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("customer_contact").'</td>
						<td align="left"><input type="text" id="customerContact" name="customerContact" size="35" maxlength="35" value="' . $customer['contact'] . '"><BR />

						<select id="customerContactGender" name="customerContactGender">
							<option value="male" '.$customerMaleSelected.'>'.$locate->Translate("male").'</option>
							<option value="female" '.$customerFemaleSelected.'>'.$locate->Translate("female").'</option>
							<option value="unknown" '.$customerUnknownSelected.'>'.$locate->Translate("unknown").'</option>
						</select>
						
						</td>
					</tr>
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails" >
						<td nowrap align="left">'.$locate->Translate("first_name").'</td>
						<td align="left"><input type="text" id="first_name" name="first_name" size="35" maxlength="50" value="' . $customer['first_name'] . '"></td>
					</tr>
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails" >
						<td nowrap align="left">'.$locate->Translate("last_name").'</td>
						<td align="left"><input type="text" id="last_name" name="last_name" size="35" maxlength="50" value="' . $customer['last_name'] . '"></td>
					</tr>
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("address").'</td>
						<td align="left"><input type="text" id="address" name="address" size="35" maxlength="200" value="' . $customer['address'] . '"></td>
					</tr>
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("zipcode").'/'.$locate->Translate("city").'</td>
						<td align="left"><input type="text" id="zipcode" name="zipcode" size="10" maxlength="10" value="' . $customer['zipcode'] . '">/<input type="text" id="city" name="city" size="17" maxlength="50" value="'.$customer['city'].'"></td>
					</tr>
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("state").'</td>
						<td align="left"><input type="text" id="state" name="state" size="35" maxlength="50" value="'.$customer['state'].'"></td>
					</tr>
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("country").'</td>
						<td align="left"><input type="text" id="country" name="country" size="35" maxlength="50" value="' . $customer['country'] . '"></td>
					</tr>
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("customer_phone").'</td>
						<td align="left"><input type="text" id="customerPhone" name="customerPhone" size="35" maxlength="50"  value="' . $customer['phone'] . '">-<input type="text" id="customerPhone_ext" name="customerPhone_ext" size="8" maxlength="8"  value="' . $customer['phone_ext'] . '"></td>
					</tr>
					<tr name="trEditCustomerDetails" id="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("mobile").'</td>
						<td align="left"><input type="text" id="mainMobile" name="mainMobile" size="35" value="' . $customer['mobile'] . '"></td>
					</tr>
					<tr name="trEditCustomerDetails" id="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("email").'</td>
						<td align="left"><input type="text" id="mainEmail" name="mainEmail" size="35" value="' . $customer['email'] . '"></td>
					</tr>				
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("website").'</td>
						<td align="left"><input type="text" id="website" name="website" size="35" maxlength="100" value="' . $customer['website'] . '"><BR /><input type="button" value="'.$locate->Translate("browser").'"  onclick="openWindow(xajax.$(\'website\').value);return false;"></td>
					</tr>
					<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
						<td nowrap align="left">'.$locate->Translate("category").'</td>
						<td align="left"><input type="text" id="category" name="category" size="35"  value="' . $customer['category'] . '"></td>
					</tr>

					<tr name="trEditCustomerDetails" id="trEditCustomerDetails" >
						<td nowrap align="left">'.$locate->Translate("fax").'</td>
						<td align="left"><input type="text" id="mainFax" name="mainFax" size="35" value="' . $customer['fax'] . '"><input type="text" id="mainFax_ext" name="mainFax_ext" maxlength="8" size="8" value="' . $customer['fax_ext'] . '"></td>
					</tr>
					<!--*********************************************************-->
					<tr id="trEditBankDetails" name="trEditBankDetails">
						<td nowrap align="left">'.$locate->Translate("bank_name").'</td>
						<td align="left"><input type="text" id="bankname" name="bankname" size="35"  value="' . $customer['bankname'] . '"></td>
					</tr>
					<tr id="trEditBankDetails" name="trEditBankDetails">
						<td nowrap align="left">'.$locate->Translate("bank_zip").'</td>
						<td align="left"><input type="text" id="bankzip" name="bankzip" size="35"  value="' . $customer['bankzip'] . '"></td>
					</tr>
					<tr id="trEditBankDetails" name="trEditBankDetails">
						<td nowrap align="left">'.$locate->Translate("bank_account_name").'</td>
						<td align="left"><input type="text" id="bankaccountname" name="bankaccountname" size="35" value="' . $customer['bankaccountname'] . '"></td>
					</tr>
					<tr id="trEditBankDetails" name="trEditBankDetails">
						<td nowrap align="left">'.$locate->Translate("bank_account").'</td>
						<td align="left"><input type="text" id="bankaccount" name="bankaccount" size="35"  value="' . $customer['bankaccount'] . '"></td>
					</tr>
					<tr>
						<td colspan="2" align="center"><button  id="btnContinue" name="btnContinue"  onClick=\'xajax_update(xajax.getFormValues("frmCustomerEdit"),"customer");return false;\'>'.$locate->Translate("continue").'</button></td>
					</tr>
					';
		}elseif ($type == 'diallist'){
			$diallist =& astercrm::getRecordByField('id',$id,'diallist');
			//print_r($diallist);exit;
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$res = Customer::getGroups();
				$groupoptions .= '<select name="groupid" id="groupid" onchange="setCampaign();">';
				while ($row = $res->fetchRow()) {
						$groupoptions .= '<option value="'.$row['groupid'].'"';
						if($row['groupid'] == $diallist['groupid']) $groupoptions .='selected';
						$groupoptions .='>'.$row['groupname'].'</option>';
				}				
				$groupoptions .= '</select>';
				$sql = "SELECT * FROM campaign WHERE groupid ='".$diallist['groupid']."'";			
				$res = & $db->query($sql);

				$campaignoptions .= '<select name="campaignid" id="campaignid" >';
				while ($campaign = $res->fetchRow()) {
					$campaignoptions .= '<option value="'.$campaign['id'].'"';
					if($campaign['id'] == $diallist['campaignid']) $campaignoptions .='selected';
					$campaignoptions .='>'.$campaign['campaignname'].'</option>';
				}				
				$campaignoptions .= '</select>';
				$assignoptions = '<input type="text" id="assign" name="assign" size="35" value="'.$diallist['assign'].'" >';
			}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
				$groupoptions .= $_SESSION['curuser']['group']['groupname'].'<input id="groupid" name="groupid" type="hidden" value="'.$_SESSION['curuser']['groupid'].'">';			$res = Customer::getRecordsByField('groupid',$_SESSION['curuser']['groupid'],'astercrm_account');
				$assignoptions .= '<select name="assign" id="assign">';
				while ($row = $res->fetchRow()) {
						$assignoptions .= '<option value="'.$row['extension'].'"';
						if($row['extension'] == $diallist['assign']) $assignoptions .= " selected";
						$assignoptions .='>'.$row['extension'].'</option>';
				}				
				$assignoptions .= '</select>';
				
				$sql = "SELECT * FROM campaign WHERE groupid ='".$diallist['groupid']."'";			
				$res = & $db->query($sql);

				$campaignoptions .= '<select name="campaignid" id="campaignid" >';
				while ($campaign = $res->fetchRow()) {
					$campaignoptions .= '<option value="'.$campaign['id'].'"';
					if($campaign['id'] == $diallist['campaignid']) $campaignoptions .='selected';
					$campaignoptions .='>'.$campaign['campaignname'].'</option>';
				}				
				$campaignoptions .= '</select>';
			}else{
				$groupoptions .= $_SESSION['curuser']['group']['groupname'].'<input id="groupid" name="groupid" type="hidden" value="'.$_SESSION['curuser']['groupid'].'">';

				$assignoptions = '<input type="text" id="assign" name="assign" size="35" value="'.$diallist['assign'].'" disabled><input type="hidden" id="assign" name="assign" value="'.$diallist['assign'].'">';
				
				$sql = "SELECT * FROM campaign WHERE groupid ='".$diallist['groupid']."'";			
				$res = & $db->query($sql);

				$campaignoptions .= '<select name="campaignid" id="campaignid" >';
				while ($campaign = $res->fetchRow()) {
					$campaignoptions .= '<option value="'.$campaign['id'].'"';
					if($campaign['id'] == $diallist['campaignid']) $campaignoptions .='selected';
					$campaignoptions .='>'.$campaign['campaignname'].'</option>';
				}				
				$campaignoptions .= '</select>';
			}

			$html = '
				<!-- No edit the next line -->
				<form method="post" name="formeditDiallist" id="formeditDiallist">
				
				<table border="1" width="100%" class="adminlist">
					<tr>
						<td nowrap align="left">'.$locate->Translate("Customername").'</td>
						<td align="left">
							<input type="text" name="customername" id="customername" size="20" value="'.$diallist['customername'].'">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("number").'</td>
						<td align="left">
							<input type="text" id="dialnumber" name="dialnumber" size="35" value="'.$diallist['dialnumber'].'" disabled><input type="hidden" id="dialnumber" name="dialnumber" value="'.$diallist['dialnumber'].'" >
							<input type="hidden" id="id"  name="id" value="'.$diallist['id'].'">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Assign To").'</td>
						<td align="left">
							'.$assignoptions.'
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Call Order").'</td>
						<td align="left">
							<input type="text" name="callOrder" id="callOrder" size="20" value="'.$diallist['callOrder'].'">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Dialtime").'</td>
						<td align="left">
							<input type="text" name="dialtime" id="dialtime" size="20" value="'.$diallist['dialtime'].'">
			<INPUT onclick="displayCalendar(document.getElementById(\'dialtime\'),\'yyyy-mm-dd hh:ii\',this,true)" type="button" value="Cal">
						</td>
					</tr>';
			$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Group Name").'</td>
						<td>'.$groupoptions.'</td>
					</tr>';
			$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Campaign Name").'</td>
						<td>'.$campaignoptions.'</td>
					</tr>';
			$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Memo").'</td>
						<td><textarea id="memo" name="memo" cols="50" rows="8">'.$diallist['memo'].'</textarea></td>
					</tr>';
			$html .= '
					<tr>
						<td nowrap colspan=2 align=right><input type="button" id="btnAddDiallist" name="btnAddDiallist" value="'.$locate->Translate("continue").'" onclick="xajax_saveDiallist(xajax.getFormValues(\'formeditDiallist\'));return false;"></td>
					</tr>
				<table>
				</form>
				';			
		}else {
			$contact =& astercrm::getContactByID($id);
			if ($contact['gender'] == 'male')
				$maleSelected = 'selected';
			elseif ($contact['gender'] == 'female')
				$femaleSelected = 'selected';
			else
				$unknownSelected = 'selected';

			$html = '
					<form method="post" name="formEdit" id="formEdit">
					<table border="0" width="100%">
					<tr>
						<td nowrap align="left">'.$locate->Translate("contact").'</td>
						<td align="left"><input type="text" id="contact" name="contact" size="35"  value="'.$contact['contact'].'"><input type="hidden" id="contactid"  name="contactid" value="'.$contact['id'].'">
</td>
					</tr>
					<tr name="genderTR" id="genderTR">
						<td nowrap align="left">'.$locate->Translate("gender").'</td>
						<td align="left">
							<select id="contactGender" name="contactGender">
								<option value="male" '.$maleSelected.'>'.$locate->Translate("male").'</option>
								<option value="female" '.$femaleSelected.'>'.$locate->Translate("female").'</option>
								<option value="unknown" '.$unknownSelected.'>'.$locate->Translate("unknown").'</option>
							</select>
						</td>
					</tr>
					<tr name="positionTR" id="positionTR">
						<td nowrap align="left">'.$locate->Translate("position").'</td>
						<td align="left"><input type="text" id="position" name="position" size="35"  value="'.$contact['position'].'"></td>
					</tr>
					<tr name="phoneTR" id="phoneTR">
						<td nowrap align="left">'.$locate->Translate("phone").'</td>
						<td align="left"><input type="text" id="phone" name="phone" size="35"  value="'.$contact['phone'].'">-<input type="text" id="ext" name="ext" size="8" maxlength="8"  value="'.$contact['ext'].'"></td>
					</tr>
					<tr name="phone1TR" id="phone1TR">
						<td nowrap align="left">'.$locate->Translate("phone1").'</td>
						<td align="left"><input type="text" id="phone1" name="phone1" size="35"  value="'.$contact['phone1'].'">-<input type="text" id="ext1" name="ext1" size="8" maxlength="8"  value="'.$contact['ext1'].'"></td>
					</tr>
					<tr name="phone2TR" id="phone2TR">
						<td nowrap align="left">'.$locate->Translate("phone2").'</td>
						<td align="left"><input type="text" id="phone2" name="phone2" size="35"  value="'.$contact['phone2'].'">-<input type="text" id="ext2" name="ext2" size="8" maxlength="8"  value="'.$contact['ext2'].'"></td>
					</tr>
					<tr name="mobileTR" id="mobileTR">
						<td nowrap align="left">'.$locate->Translate("mobile").'</td>
						<td align="left"><input type="text" id="mobile" name="mobile" size="35" value="'.$contact['mobile'].'"></td>
					</tr>
					<tr name="faxTR" id="faxTR">
						<td nowrap align="left">'.$locate->Translate("fax").'</td>
						<td align="left"><input type="text" id="fax" name="fax" size="35" value="'.$contact['fax'].'">-<input type="text" id="fax_ext" name="fax_ext" size="8" maxlength="8" value="'.$contact['fax_ext'].'"></td>
					</tr>
					<tr name="emailTR" id="emailTR">
						<td nowrap align="left">'.$locate->Translate("email").'</td>
						<td align="left"><input type="text" id="email" name="email" size="35" value="'.$contact['email'].'"></td>
					</tr>					
					<tr>
						<td colspan="2" align="center"><button id="btnContinue" name="btnContinue"  onClick=\'xajax_update(xajax.getFormValues("formEdit"),"contact");return false;\'>'.$locate->Translate("continue").'</button></td>
					</tr>
					';
		}

		$html .= '
				</table>
				</form>
				'.$locate->Translate("ob_fields").'
				';

		return $html;
	}
	

	/**
	*  Muestra todos los datos de un registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser mostrado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene una tabla con los datos 
	*									a extraidos de la base de datos para ser mostrados 
	*/
	function showCustomerRecord($id,$type="customer",$callerid=''){
    	global $locate;//echo $callerid;exit;
		$customer =& astercrm::getCustomerByID($id,$type);
		if($customer['id'] > 0){
			$contactList =& astercrm::getContactListByID($customer['id']);
		}

		$html = '
				<table border="0" width="100%">
				<tr>
					<td nowrap align="left" width="160">' .$locate->Translate("customer_name").'&nbsp;[<a href=? onclick="xajax_showNote(\''.$customer['id'].'\',\'customer\');return false;">'.$locate->Translate("note").'</a>]</td>
					<td align="left">';
					if($_SESSION['curuser']['language'] != 'ZH' && $_SESSION['curuser']['country'] != 'cn'){
						$html .= $locate->Translate($customer['customertitle']).'&nbsp;<b>'.$customer['customer'].'</b>';
					}else{
						$html .= '&nbsp;<b>'.$customer['customer'].'</b>'.$locate->Translate($customer['customertitle']);
					}
					$html .= '&nbsp;[<a href=? onclick="xajax_edit(\''.$customer['id'].'\',\'customer\');return false;">'.$locate->Translate("edit").'</a>]&nbsp; [<a href=? onclick="
							if (xajax.$(\'hidCustomerBankDetails\').value == \'OFF\'){
								showObj(\'trCustomerBankDetails\');
								xajax.$(\'hidCustomerBankDetails\').value = \'ON\';
							}else{
								hideObj(\'trCustomerBankDetails\');
								xajax.$(\'hidCustomerBankDetails\').value = \'OFF\';
							}
							return false;">'.$locate->Translate("bank").'</a>]<input type="hidden" value="OFF" name="hidCustomerBankDetails" id="hidCustomerBankDetails">&nbsp;[<a href=? onclick="showDiallist(\''.$_SESSION['curuser']['extension'].'\','.$customer['id'].',0,5,\'\',\'\',\'\',\'formDiallist\',\'\',\'\');return false;">'.$locate->Translate("diallist").'</a>]&nbsp;[<a href=? onclick="xajax_showRecords(\''.$customer['id'].'\');return false;">'.$locate->Translate("Cdr").'</a>]
							&nbsp; [<a href=? onclick="addSchedulerDial(\''.$customer['id'].'\'); return false;">'.$locate->Translate("Scheduler Dial").'</a>]
							&nbsp; [<a href=? onclick="addTicket(\''.$customer['id'].'\'); return false;">'.$locate->Translate("Ticket").'</a>]
						</td>
					</tr>
					<tr id="trAddSchedulerDial" name="trAddSchedulerDial" style="display:none"></tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("city").'/'.$locate->Translate("state").'/'.$locate->Translate("country").'['.$locate->Translate("zipcode").']'.'</td>
					<td align="left">'.$customer['city'].'/'.$customer['state'].'/'.$customer['country'].'['.$customer['zipcode'].']'.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("address").
						' | <a href="?" onclick="showMap(\''.$customer['city'].' '.$customer['state'].
						' '.$customer['zipcode'].' '.$customer['address'].'\');return false;">Map</a>'.
					'</td>
					<td align="left">'.$customer['address'].'</td>
				</tr>
				<!--**********************-->
				<tr>
					<td nowrap align="left">'.$locate->Translate("mobile").'</td>
					<td align="left"><a href=? onclick="dial(\''.$customer['mobile'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$customer['mobile'].'</a></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("fax").'</td>
					<td align="left"><a href=? onclick="dial(\''.$customer['fax'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$customer['fax'].'</a>-<a href=? onclick="dial(\''.$customer['fax'].'\',\'\',xajax.getFormValues(\'myForm\'),\''.$customer['fax_ext'].'\');return false;">'.$customer['fax_ext'].'</a></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("email").'</td>
					<td align="left"><a href="mailto:'.$customer['email'].'">'.$customer['email'].'</a></td>
				</tr>	
				<!--**********************-->
				<tr>
					<td nowrap align="left">'.$locate->Translate("website").'</td>
					<td align="left"><a href="'.$customer['website'].'" target="_blank">'.$customer['website'].'</a></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("customer_contact").'</td>
					<td align="left">'.$customer['contact'].'&nbsp;&nbsp;('.$locate->Translate($customer['contactgender']).')</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("customer_phone").'</td>
					<td align="left"><a href=? onclick="dial(\''.$customer['phone'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$customer['phone'].'</a>-<a href=? onclick="dial(\''.$customer['phone'].'\',\'\',xajax.getFormValues(\'myForm\'),\''.$customer['phone_ext'].'\');return false;">'.$customer['phone_ext'].'</a></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("category").'</td>
					<td align="left">'.$customer['category'].'</td>
				</tr>
				<tr id="trCustomerBankDetails" name="trCustomerBankDetails" style="display:none">
					<td nowrap align="left">'.$locate->Translate("bank_name").'</td>
					<td align="left">'.$customer['bankname'].'</td>
				</tr>
				<tr id="trCustomerBankDetails" name="trCustomerBankDetails" style="display:none">
					<td nowrap align="left">'.$locate->Translate("bank_zip").'</td>
					<td align="left">'.$customer['bankzip'].'</td>
				</tr>
				<tr id="trCustomerBankDetails" name="trCustomerBankDetails" style="display:none">
					<td nowrap align="left">'.$locate->Translate("bank_account_name").'</td>
					<td align="left">'.$customer['bankaccountname'].'</td>
				</tr>
				<tr id="trCustomerBankDetails" name="trCustomerBankDetails" style="display:none">
					<td nowrap align="left">'.$locate->Translate("bank_account").'</td>
					<td align="left">'.$customer['bankaccount'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("create_time").'</td>
					<td align="left">'.$customer['cretime'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("create_by").'</td>
					<td align="left">'.$customer['creby'].'</td>
				</tr>
				<tr>
					<td colspan=2>
						<table width="100%">
							<tr>
							<td>
					<a href=? onclick="if (xajax.$(\'allContact\').value==\'off\'){xajax.$(\'contactList\').style.display=\'block\';xajax.$(\'allContact\').value=\'on\'}else{xajax.$(\'contactList\').style.display=\'none\';xajax.$(\'allContact\').value=\'off\'} return false;">'.$locate->Translate("display_all").'</a>
							</td>
							<td>
							<a href="?" onclick="xajax_noteAdd(\''.$customer['id'].'\',0);return false;">'.$locate->Translate("add_note").'</a>
							</td>
							<td>';
							$survey = astercrm::countSurvey($callerid);
							//print_r($survey);exit;
							if($survey['count'] == 1){
								$html .= '<a href="?" onclick="xajax_showSurvey(\''.$survey['id'].'\',\''.$id.'\',0,\''.$survey['callerid'].'\',\''.$survey['campaignid'].'\');return false;">'.$locate->Translate("Add Survey").'</a>';
							}else{
								$html .= '<a href="?" onclick="xajax_surveyList(\''.$customer['id'].'\',0,\''.$survey['callerid'].'\');return false;">'.$locate->Translate("Add Survey").'</a>';
							}

							$html .= '</td><input type="hidden" id="allContact" name="allContact" value="off">
							</tr>
						</table>
					</td>
				</tr>
				</table>
				<table border="0" width="100%" id="contactList" name="contactList" style="display:none">
					';
				if(!empty($contactList)){
					while($contactList->fetchInto($row)){
						$html .= '<tr>';
						for ($i=1;$i<5;$i++){
							$html .= '
									<td align="left" width="20%">
										<a href=? onclick="xajax_showContact(\''. $row['id'] .'\');return false;">'. $row['contact'] .'</a>
									</td>
									';
							if (!$contactList->fetchInto($row))
								$html .= '<td>&nbsp;</td>';
						}
						$html .= '</tr>';
					}
				}

				$html .= '
					</table>';

		return $html;

	}

	function getRecordsByGroupid($groupid = null, $table){
		global $db;

		if ($groupid == null){
			$query = "SELECT * FROM $table ORDER BY id" ;
		}else{
			$query = "SELECT * FROM $table WHERE groupid = $groupid ORDER BY id";
		}
		$row =& $db->query($query);
		return $row;
	}

	function getDialNumber($campaignid = ''){
		global $db;
		$query = "SELECT diallist.*,campaign.incontext, campaign.inexten, campaign.outcontext, campaign.queuename FROM diallist LEFT JOIN campaign ON campaign.id = diallist.campaignid WHERE diallist.campaignid = $campaignid ";
		$query .=  " ORDER BY diallist.id DESC	LIMIT 0,1";

		$row =& $db->getRow($query);

		return $row;
	}

	function getCustomerphoneSqlByid($customerid,$feild,$type = '',$feild1='',$tableAlias=''){//$tableAlias  is used to sign the table name,add by shixb 2010-09-07
		
		$res_customer =astercrm::getRecordById($customerid,'customer');
		$res_contact =astercrm::getContactListByID($customerid);

		if($feild1 == ''){
			$sql = '';
			if ($res_customer['phone'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$res_customer['phone']."' ";
			if ($res_customer['mobile'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$res_customer['mobile']."' ";
			while ($res_contact->fetchInto($row)) {
				if ($row['phone'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$row['phone']."' ";
				if ($row['phone1'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$row['phone1']."' ";
				if ($row['phone2'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$row['phone2']."' ";
				if ($row['mobile'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$row['mobile']."' ";
			}
			if($sql != '') $sql = ltrim($sql,"\ ".$type);
		}else{
			$sql = '';
			
			if ($res_customer['phone'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$res_customer['phone']."' ".$type." ".$tableAlias.".".$feild1."='".$res_customer['phone']."' ";
			if ($res_customer['mobile'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$res_customer['mobile']."' ".$type." ".$tableAlias.".".$feild1."='".$res_customer['mobile']."' ";
			while ($res_contact->fetchInto($row)) {
				if ($row['phone'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$row['phone']."' ".$type." ".$tableAlias.".".$feild1."='".$row['phone']."' ";
				if ($row['phone1'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$row['phone1']."' ".$type." ".$tableAlias.".".$feild1."='".$row['phone1']."' ";
				if ($row['phone2'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$row['phone2']."' ".$type." ".$tableAlias.".".$feild1."='".$row['phone2']."' ";
				if ($row['mobile'] != '' && $tableAlias != '') $sql .= " ".$type." ".$tableAlias.".".$feild."='".$row['mobile']."' ".$type." ".$tableAlias.".".$feild1."='".$row['mobile']."'" ;
			}
			if($sql != '') $sql = ltrim($sql,"\ ".$type);
		}
		return $sql;
	}

	function getGroupCurcdr() {
		global $db;
		foreach ($_SESSION['curuser']['memberExtens'] as $value){
			$memberextena .= "'".$value."',";
			$memberextenb .= "'local/".$value."',";
			$memberextenc .= "'sip/".$value."',";
			$memberextend .= "'iax/".$value."',";
		}
		foreach ($_SESSION['curuser']['memberAgents'] as $value){
			$memberagents .= "'agent/".$value."',";				
		}
		$memberextens = rtrim($memberextena.$memberextenb.$memberextenc.$memberextend,',');
		$memberagents = rtrim($memberagents,',');

		$query = "SELECT * FROM curcdr WHERE src in ($memberextens) OR dst in ($memberextens)";
		if($memberagents != ''){
			$query .= " OR dstchan in ($memberagents)";
		}
		astercrm::events($query);
		$row =& $db->query($query);
		return $row;		
	}

	/**
	*  delete a record form a table
	*
	*	@param  $id			(int)		identity of the record
	*	@param  $table		(string)	table name
	*	@return $res		(object)	object
	*/
	
	function deleteRecord($id,$table){
		global $db;
		
		//backup all datas

		//delete all note
		$query = "DELETE FROM $table WHERE id = $id";
		astercrm::events($query);
		$res =& $db->query($query);

		return $res;
	}

	/**
	*  delete records form a table
	*
	*	@param  $field			(string)
	*	@param  $value			(string)
	*	@param  $table			(string)	table name
	*	@return $res		(object)	object
	*/
	
	function deleteRecords($field,$value,$table){
		global $db;
		
		//backup all datas

		//delete all note
		$query = "DELETE FROM $table WHERE $field = '$value'";
		astercrm::events($query);
		$res =& $db->query($query);

		return $res;
	}

	/**
	*  Muestra todos los datos de un registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser mostrado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene una tabla con los datos 
	*									a extraidos de la base de datos para ser mostrados 
	*/
	function showContactRecord($id,$type="contact"){
    	global $locate;
		$contact =& astercrm::getContactByID($id,$type);
		if ($contact['id'] == '' )
			return '';
		$html = '
				<table border="0" width="100%">
				<tr>
					<td nowrap align="left" width="80">'.$locate->Translate("contact").'&nbsp;[<a href=? onclick="xajax_showNote(\''.$contact['id'].'\',\'contact\');return false;">'.$locate->Translate("note").'</a>]</td>
					<td align="left">'.$contact['contact'].'&nbsp;&nbsp;&nbsp;&nbsp;<span align="right">[<a href=? onclick="contactCopy(\''.$contact['id'].'\');;return false;">'.$locate->Translate("copy").'</a>]</span>&nbsp;&nbsp;[<a href=? onclick="xajax_edit(\''.$contact['id'].'\',\'\',\'contact\');return false;">'.$locate->Translate("edit").'</a>]</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("gender").'</td>
					<td align="left">'.$locate->Translate($contact['gender']).'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("position").'</td>
					<td align="left">'.$contact['position'].'</td>
				</tr>';

		if ($contact['ext'] == '')
			$html .='
					<tr>
						<td nowrap align="left">'.$locate->Translate("phone").'</td>
						<td align="left"><a href=? onclick="dial(\''.$contact['phone'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$contact['phone'].'</a></td>
					</tr>';
		else
			$html .='
					<tr>
						<td nowrap align="left">'.$locate->Translate("phone").'</td>
						<td align="left"><a href=? onclick="dial(\''.$contact['phone'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$contact['phone'].'</a> ext: '.$contact['ext'].'</td>
					</tr>';

		if ($contact['phone1'] != '' || $contact['ext1'] != '')
			if ($contact['ext1'] == '')
				$html .='
						<tr>
							<td nowrap align="left">'.$locate->Translate("phone1").'</td>
							<td align="left"><a href="?" onclick="dial(\''.$contact['phone1'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$contact['phone1'].'</a></td>
						</tr>';
			else
				$html .='
						<tr>
							<td nowrap align="left">'.$locate->Translate("phone1").'</td>
							<td align="left"><a href="?" onclick="dial(\''.$contact['phone1'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$contact['phone1'].'</a> ext: '.$contact['ext1'].'</td>
						</tr>';
		
		if ($contact['phone2'] != '' || $contact['ext2'] != '')
			if ($contact['ext2'] == '')
				$html .='
						<tr>
							<td nowrap align="left">'.$locate->Translate("phone2").'</td>
							<td align="left"><a href="?" onclick="dial(\''.$contact['phone2'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$contact['phone2'].'</a></td>
						</tr>';
			else
				$html .='
						<tr>
							<td nowrap align="left">'.$locate->Translate("phone2").'</td>
							<td align="left"><a href="?" onclick="dial(\''.$contact['phone2'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$contact['phone2'].'</a> ext: '.$contact['ext2'].'</td>
						</tr>';

		$html .='
				<tr>
					<td nowrap align="left">'.$locate->Translate("mobile").'</td>
					<td align="left"><a href="?" onclick="dial(\''.$contact['mobile'].'\',\'\',xajax.getFormValues(\'myForm\'));return false;">'.$contact['mobile'].'</a></td>
				</tr>';
			if ($contact['fax'] != '' || $contact['fax_ext'] != ''){
				if ($contact['fax_ext'] != ''){
					$html .='
					<tr>
						<td nowrap align="left">'.$locate->Translate("fax").'</td>
						<td align="left">'.$contact['fax'].' ext: '.$contact['fax_ext'].'</td>
					</tr>';
				}else{
					$html .='
					<tr>
						<td nowrap align="left">'.$locate->Translate("fax").'</td>
						<td align="left">'.$contact['fax'].'</td>
					</tr>';
				}
			}
		$html .='
				<tr>
					<td nowrap align="left">'.$locate->Translate("email").'</td>
					<td align="left"><a href="mailto:'.$contact['email'].'">'.$contact['email'].'</a></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("create_time").'</td>
					<td align="left">'.$contact['cretime'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("create_by").'</td>
					<td align="left">'.$contact['creby'].'</td>
				</tr>
				</table>';

		return $html;
	}

	/**
	*  export datas to csv format
	*
	*	@param $type		(string)		data to be exported
	*	@return $txtstr		(string) 		csv format datas
	*/

	function exportCSV($type = 'customer'){
		global $db;

		if ($type == 'customer')
			$query = 'SELECT * FROM customer';
		elseif ($type == 'contact')
			$query = 'SELECT contact.*,customer.customer FROM contact LEFT JOIN customer ON customer.id = contact.customerid';
		else
			$query = 'SELECT contact.contact,customer.customer,note.* FROM note LEFT JOIN customer ON customer.id = note.customerid LEFT JOIN contact ON contact.id = note.contactid';

		astercrm::events($query);
		$res =& $db->query($query);
		while ($res->fetchInto($row)) {
			foreach ($row as $val){
				$val .= ',';
				if ($val != mb_convert_encoding($val,"UTF-8","UTF-8"))
						$val='"'.mb_convert_encoding($val,"UTF-8","GB2312").'"';
				
				$txtstr .= '"'.$val.'"';
			}
			$txtstr .= "\n";
		}
		return $txtstr;
	}

	function exportDataToCSV($query,$table=''){
		global $db;
		astercrm::events($query);
		$res =& $db->query($query);		
		$first = 'yes';
		while ($res->fetchInto($row)) {
			$first_line = '';
			foreach ($row as $key => $val){
				if($first == 'yes'){
					if($table = 'surveyresult'){
						//if()
					}
					$first_line .= '"'.$key.'"'.',';
				}
				if ($val != mb_convert_encoding($val,"UTF-8","UTF-8"))
						$val='"'.mb_convert_encoding($val,"UTF-8","GB2312").'"';
				
				$txtstr .= '"'.$val.'"'.',';
			}
			if($first_line != ''){
				$first_line .= "\n";
				$txtstr = $first_line.$txtstr;
				$first = 'no';
			}			
			$txtstr .= "\n";
		}
		return $txtstr;
	}

	function getFieldsByField($fields,$field,$content,$table,$stype=''){
		global $db;
		if ($stype != '' ){
			if($stype == "equal"){
				$query = "SELECT $fields FROM $table WHERE $field = '".$content."'";
			}elseif($stype == "more"){
				$query = "SELECT $fields FROM $table WHERE $field > '".$content."'";
			}elseif($stype == "less"){
				$query = "SELECT $fields FROM $table WHERE $field < '".$content."'";
			}else{
				$query = "SELECT $fields FROM $table WHERE $field LIKE '%".$content."%'";
			}
		}else{
			$query = "SELECT groupid FROM $table WHERE $field = $content";
		}

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	/**
	*  create a 'where string' with 'like,<,>,=' assign by stype
	*
	*	@param $stype		(array)		assign search type
	*	@param $filter		(array) 	filter in sql
	*	@param $content		(array)		content in sql
	*	@return $joinstr	(string)	sql where string
	*/
	function createSqlWithStype($filter,$content,$stype=array(),$table='',$option='search'){
//print_r($filter);echo $table;exit;
		$i=0;
		$joinstr='';
		foreach($stype as $type){
			$content[$i] = preg_replace("/'/","\\'",$content[$i]);
			if($filter[$i] != '' && trim($content[$i]) != ''){
				
				if($filter[$i] == 'groupname' and $table != "astercrm_accountgroup" and $table != "" and $table != "mycdr"){
					$group_res = astercrm::getFieldsByField('id','groupname',$content[$i],'astercrm_accountgroup',$type);
					
					while ($group_res->fetchInto($group_row)){
						$group_str.="OR $table.groupid = '".$group_row['id']."' ";
					}
					if($group_str == ''){
						$group_str.=" $table.groupid = '-1' ";
					}
				}elseif(($filter[$i] == 'campaignname' OR ($filter[$i] == 'campaign.campaignname' and $option = 'delete')) and $table != "campaign" and $table != ""){
					
					$campaign_res = astercrm::getFieldsByField('id','campaignname',$content[$i],'campaign',$type);
					
					while ($campaign_res->fetchInto($campaign_row)){
						$campaign_str.="OR $table.campaignid = '".$campaign_row['id']."' ";					
					}
					
					if($campaign_str == ''){
						$campaign_str.=" $table.campaignid = '0' ";
					}
				}else{
					if($table == 'monitorrecord' && $filter[$i] == 'dstchannel'){
						$content[$i] = 'agent/'.$content[$i];
					}
					//echo $filter[$i] == 'username' && $table != "" && $table == "mycdr";exit;
					if($filter[$i] == 'username' && $table != "" && $table == "mycdr") {
						$filter[$i] = 'astercrm_account.username';
					} else if($filter[$i] == 'groupname' && $table != "" && $table == "mycdr") {
						$filter[$i] = 'astercrm_accountgroup.groupname';
					}
					if($type == "equal"){
						$joinstr.="AND $filter[$i] = '".trim($content[$i])."' ";
					}elseif($type == "more"){
						$joinstr.="AND $filter[$i] > '".trim($content[$i])."' ";
					}elseif($type == "less"){
						$joinstr.="AND $filter[$i] < '".trim($content[$i])."' ";
					}else{
						$joinstr.="AND $filter[$i] like '%".trim($content[$i])."%' ";
					}
				}
			}
			$i++;
		}
		if($group_str != '' ){
			$group_str = ltrim($group_str,'OR');
			$joinstr.= "AND (".$group_str.")";
		}
		if($campaign_str != '' ){
			$campaign_str = ltrim($campaign_str,'OR');
			$joinstr.= "AND (".$campaign_str.")";
		}
		//echo $joinstr;exit;
		return $joinstr;
	}

	/**
	*  return customerid if match a phonenumber
	*
	*	@param $type		(string)		data to be exported
	*	@return $txtstr		(string) 		csv format datas
	*/

	function getCustomerByCallerid($callerid,$groupid = ''){
		global $db;
		$callerid = preg_replace("/'/","\\'",$callerid);
		if ($groupid == '') {
			$query = "SELECT id FROM customer WHERE phone LIKE '%$callerid' OR mobile LIKE '%$callerid' OR phone = '$callerid' OR mobile = '$callerid'";
		}else {
			$query = "SELECT id FROM customer WHERE phone LIKE '%$callerid' OR mobile LIKE '%$callerid' OR phone = '$callerid' OR mobile = '$callerid' AND groupid = $groupid ";
		}
		astercrm::events($query);
		$customerid =& $db->getOne($query);
		return $customerid;
	}

	function getContactByCallerid($callerid,$groupid = ''){
		global $db;
		$callerid = preg_replace("/'/","\\'",$callerid);
		if ($groupid == '')
			$query = "SELECT id,customerid FROM contact WHERE phone LIKE '%$callerid' OR phone = '$callerid' OR phone1 LIKE '%$callerid' OR phone1 = '$callerid' OR phone2 LIKE '%$callerid' OR phone2 = '$callerid' OR mobile LIKE '%$callerid' OR mobile = '$callerid' LIMIT 0,1";
		else
			$query = "SELECT id,customerid FROM contact WHERE phone LIKE '%$callerid' OR phone = '$callerid' OR phone1 LIKE '%$callerid' OR phone1 = '$callerid' OR phone2 LIKE '%$callerid' OR phone2 = '$callerid' OR mobile LIKE '%$callerid' OR mobile = '$callerid' AND groupid=$groupid LIMIT 0,1";
		astercrm::events($query);
		$row =& $db->getRow($query);
		return $row;
	}

	function getContactSmartMatch($callerid,$groupid = ''){
		global $db,$config;

		$callerid = preg_replace("/'/","\\'",$callerid);

		if (is_numeric($config['system']['smart_match_remove'])){
			$remove = 0 - $config['system']['smart_match_remove'];
			$callerid = substr($callerid,0,$remove);
		}else{
			$callerid = substr($callerid,0,-3);
		}

		if( $groupid == '' ) {
			$query = "SELECT * FROM contact WHERE phone LIKE '%$callerid%' OR phone1 LIKE '%$callerid' OR phone2 LIKE '%$callerid%' ";
		}else{
			$query = "SELECT * FROM contact WHERE phone LIKE '%$callerid%' OR phone1 LIKE '%$callerid%' OR phone2 LIKE '%$callerid%' AND groupid=$groupid";
		}
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function getCustomerSmartMatch($callerid,$groupid = ''){
		global $db,$config;

		$callerid = preg_replace("/'/","\\'",$callerid);

		if (is_numeric($config['system']['smart_match_remove'])){
			$remove = 0 - $config['system']['smart_match_remove'];
			$callerid = substr($callerid,0,$remove);
		}else{
			$callerid = substr($callerid,0,-3);
		}

		if ($groupid == '') {
			$query = "SELECT * FROM customer WHERE phone LIKE '%$callerid%' ";
		}else {
			$query = "SELECT * FROM customer WHERE phone LIKE '%$callerid%' AND groupid = $groupid ";
		}

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function getSql($searchContent,$searchField,$searchType=array(),$table,$fields = '',$leftjoins=array()){
		global $db;

		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,$table);
		$fieldstr = '';
		if(is_array($fields)){
			foreach($fields as $field){
				$fieldstr .= " ".$field.",";
			}
		}
		$leftStr = '';
		if(!empty($leftjoins)) {
			foreach($leftjoins as $model=>$param) {// the keys of array $leftjoins are the table which need to left join
				$leftStr .= 'LEFT JOIN '.$model.' ON '.$param[0].'='.$param[1].' ';
			}
		}
		
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND');

			if($fieldstr != ''){
				$fieldstr=rtrim($fieldstr,',');
				$query = 'SELECT '.$fieldstr.' FROM '.$table.' '.$leftStr.' WHERE '.$joinstr;
			}else{
				$query = 'SELECT * FROM '.$table.' '.$leftStr.' WHERE '.$joinstr;
			}
			
		}else {

			if($fieldstr != ''){
				$fieldstr=rtrim($fieldstr,',');
				$query = 'SELECT '.$fieldstr.' FROM '.$table.' '.$leftStr.' ';
			}else{
				$query = 'SELECT * FROM '.$table.'';
			}			
		}
//echo $query;exit;
		//if ($query != mb_convert_encoding($query,"UTF-8","UTF-8")){
		//	$query='"'.mb_convert_encoding($query,"UTF-8","GB2312").'"';
		//}

		return $query;
	}

	function deletefromsearch($searchContent,$searchField,$searchType="",$table){
		global $db;
		if(empty($_SESSION['curuser']['usertype'])){
			return;
		}
		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,$table,'delete');

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND');			
			if($_SESSION['curuser']['usertype'] == 'admin'){
				$sql = 'DELETE FROM '.$table.' WHERE '.$joinstr;
			}else{
				$sql = 'DELETE FROM '.$table.' WHERE '.$joinstr." AND ".$table.".groupid = '".$_SESSION['curuser']['groupid']."'";
			}
		}else{
			if($_SESSION['curuser']['usertype'] == 'admin'){
				$sql = 'TRUNCATE table '.$table;
			}else{
				$sql = "DELETE FROM ".$table." WHERE ".$table.".groupid = '".$_SESSION['curuser']['groupid']."'";
			}
		}

 		Customer::events($sql);
		$res =& $db->query($sql);

		return $res;
	}

	function addNewRemind($f){ //增加提醒
		global $db;
		$f = astercrm::variableFiler($f);
		$remindtime = $f['remindtime'];
		$touser = trim($f['touser']);
		//if($touser == ''){
		$touser = $_SESSION['curuser']['username'];
		//}
		$query= "INSERT INTO remind SET "
				."title='".$f['remindtitle']."', "
				."content='".$f['content']."', "
				."remindtime='".$remindtime."',"   //提醒时间
				."remindtype='".$f['remindtype']."',"	//提醒类别
				."priority='".$f['priority']."',"       //紧急程度
				."username='".$f['username']."', "	// 归属人
				."remindabout='".$f['remindabout']."', "	// 相关内容
				."readed=0, "	// added 2007/11/12 by solo
				."touser='".$touser."', "	// added 2007/11/12 by solo
				."creby='".$_SESSION['curuser']['username']."', "
				."cretime=now() ";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function updateRemind($f){  //修改提醒
		global $db;
		$f = astercrm::variableFiler($f);
		$remindtime = $f['remindtime'];
		$touser = trim($f['touser']);
		//if($touser == ''){
		$touser = $_SESSION['curuser']['username'];
		//}
		$query= "UPDATE remind SET "
				."title='".$f['remindtitle']."', "
				."content='".$f['content']."', "
				."remindtime='".$remindtime."',"   //提醒时间
				."remindtype='".$f['remindtype']."',"	//提醒类别
				."priority='".$f['priority']."',"       //紧急程度
				."username='".$f['username']."', "	// 归属人
				."remindabout='".$f['remindabout']."', "	// 相关内容
				."touser='".$touser."'  "	// added 2007/11/12 by solo
				."WHERE id='".$f['id']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function db2html($string){
		$string = str_replace("\n",'<br>',$string);
		return $string;
	}

	function createCdrGrid($customerid='',$cdrtype='',$start = 0, $limit = 1, $filter = null, $content = null, $stype = null, $order = null, $divName = "formCdr", $ordering = ""){
		global $locate;
		$_SESSION['ordering'] = $ordering;
		if($filter == null || $content == null || (!is_array($content) && $content == 'Array') || (!is_array(filter) && $filter == 'Array')){
			$content = null;
			$filter = null;
			$numRows =& astercrm::getCdrNumRows($customerid,$cdrtype);
			$arreglo =& astercrm::getAllCdrRecords($customerid,$cdrtype,$start,$limit,$order);
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
				$numRows =& astercrm::getCdrNumRows($customerid,$cdrtype);
				$arreglo =& astercrm::getAllCdrRecords($customerid,$cdrtype,$start,$limit,$order);
			}elseif($flag3 != 1 ){  //未选择搜索方式
				$order = "calldate";
				$numRows =& astercrm::getCdrNumRowsMore($customerid,$cdrtype,$filter, $content);
				$arreglo =& astercrm::getCdrRecordsFilteredMore($customerid,$cdrtype,$start, $limit, $filter, $content, $order);
			}else{
				$order = "calldate";
				$numRows =& astercrm::getCdrNumRowsMorewithstype($customerid,$cdrtype,$filter, $content,$stype);
				$arreglo =& astercrm::getCdrRecordsFilteredMorewithstype($customerid,$cdrtype,$start, $limit, $filter, $content, $stype,$order);
			}
		}	
		// Databse Table: fields
		if($cdrtype=='recent'){
			$fields = array();
			$fields[] = 'calldate';
			$fields[] = 'src';
			$fields[] = 'dst';			
			$fields[] = 'didnumber';
			$fields[] = 'dstchannel';
			$fields[] = 'duration';
			$fields[] = 'billsec';
			$fields[] = 'record';

			// HTML table: Headers showed
			$headers = array();
			$headers[] = $locate->Translate("Calldate").'<br>';
			$headers[] = $locate->Translate("Src").'<br>';
			$headers[] = $locate->Translate("Dst").'<br>';
			$headers[] = $locate->Translate("Callee Id").'<br>';
			$headers[] = $locate->Translate("Agent").'<br>';
			$headers[] = $locate->Translate("Duration").'<br>';
			$headers[] = $locate->Translate("Billsec").'<br>';
			$headers[] = $locate->Translate("record").'<br>';

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
			$eventHeader[]= 'onClick=\'showRecentCdrGrid("","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","calldate","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showRecentCdrGrid("","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","src","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showRecentCdrGrid("","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","dst","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showRecentCdrGrid("","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","didnumber","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showRecentCdrGrid("","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","dstchannel","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showRecentCdrGrid("","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","duration","'.$divName.'","ORDERING");return false;\'';
			$eventHeader[]= 'onClick=\'showRecentCdrGrid("","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","billsec","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showRecentCdrGrid("","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","id","billsec","'.$divName.'","ORDERING","'.$stype.'");return false;\'';			

			// Select Box: fields table.
			$fieldsFromSearch = array();
			$fieldsFromSearch[] = 'src';
			$fieldsFromSearch[] = 'calldate';
			$fieldsFromSearch[] = 'dst';
			$fieldsFromSearch[] = 'didnumber';
			$fieldsFromSearch[] = 'billsec';

			// Selecct Box: Labels showed on search select box.
			$fieldsFromSearchShowAs = array();
			$fieldsFromSearchShowAs[] = $locate->Translate("src");
			$fieldsFromSearchShowAs[] = $locate->Translate("calldate");
			$fieldsFromSearchShowAs[] = $locate->Translate("dst");
			$fieldsFromSearchShowAs[] = $locate->Translate("callee id");
			$fieldsFromSearchShowAs[] = $locate->Translate("billsec");

			// Create object whit 5 cols and all data arrays set before.
			$table = new ScrollTable(7,$start,$limit,$filter,$numRows,$content,$order,$customerid,$cdrtype);
			$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=false,$delete=false,$detail=false);
			$table->setAttribsCols($attribsCols);
			$table->addRowSearchMore("mycdr",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

			while ($arreglo->fetchInto($row)) {
			// Change here by the name of fields of its database table
				$rowc = array();
				$rowc[] = $row['monitorid'];
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
				if($row['processed'] == 'yes' && $row['fileformat'] != 'error' ) {
					$rowc['filename'] = $row['filename'].'.'.$row['fileformat'];
				} else {
					$rowc['filename'] = '';
				}
				$table->addRow("mycdr",$rowc,false,false,false,$divName,$fields);
			}
			$html = $table->render('static');
		}else{
			$fields = array();
			$fields[] = 'calldate';
			$fields[] = 'src';
			$fields[] = 'dst';
			$fields[] = 'didnumber';
			$fields[] = 'dstchannel';
			$fields[] = 'duration';
			$fields[] = 'billsec';
			$fields[] = 'disposition';
			$fields[] = 'credit';
			$fileds[] = 'destination';
			$fileds[] = 'memo';

			// HTML table: Headers showed
			$headers = array();
			$headers[] = $locate->Translate("Calldate").'<br>';
			$headers[] = $locate->Translate("Src").'<br>';
			$headers[] = $locate->Translate("Dst").'<br>';
			$headers[] = $locate->Translate("Callee Id").'<br>';
			$headers[] = $locate->Translate("Agent").'<br>';
			$headers[] = $locate->Translate("Duration").'<br>';
			$headers[] = $locate->Translate("Billsec").'<br>';
			$headers[] = $locate->Translate("Disposition").'<br>';
			$headers[] = $locate->Translate("credit").'<br>';
			$headers[] = $locate->Translate("destination").'<br>';
			$headers[] = $locate->Translate("memo").'<br>';

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
			$attribsCols[] = 'style="text-align: left"';
			$attribsCols[] = 'style="text-align: left"';

			// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
			$eventHeader = array();
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","calldate","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","src","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","dst","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","didnumber","'.$divName.'","ORDERING");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","dstchannel","'.$divName.'","ORDERING");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","duration","'.$divName.'","ORDERING");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","billsec","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","disposition","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","credit","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","destination","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'xajax_showCdr('.$customerid.',"'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","memo","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			
			// Select Box: type table.
			$typeFromSearch = array();
			$typeFromSearch[] = 'like';
			$typeFromSearch[] = 'equal';
			$typeFromSearch[] = 'more';
			$typeFromSearch[] = 'less';

			// Selecct Box: Labels showed on searchtype select box.
			$typeFromSearchShowAs = array();
			$typeFromSearchShowAs[] = 'like';
			$typeFromSearchShowAs[] = '=';
			$typeFromSearchShowAs[] = '>';
			$typeFromSearchShowAs[] = '<';

			// Select Box: fields table.
			$fieldsFromSearch = array();
			$fieldsFromSearch[] = 'src';
			$fieldsFromSearch[] = 'calldate';
			$fieldsFromSearch[] = 'dst';
			$fieldsFromSearch[] = 'didnumber';
			$fieldsFromSearch[] = 'billsec';
			$fieldsFromSearch[] = 'disposition';
			$fieldsFromSearch[] = 'credit';
			$fieldsFromSearch[] = 'destination';
			$fieldsFromSearch[] = 'memo';

			// Selecct Box: Labels showed on search select box.
			$fieldsFromSearchShowAs = array();
			$fieldsFromSearchShowAs[] = $locate->Translate("src");
			$fieldsFromSearchShowAs[] = $locate->Translate("calldate");
			$fieldsFromSearchShowAs[] = $locate->Translate("dst");
			$fieldsFromSearchShowAs[] = $locate->Translate("callee id");
			$fieldsFromSearchShowAs[] = $locate->Translate("billsec");
			$fieldsFromSearchShowAs[] = $locate->Translate("disposition");
			$fieldsFromSearchShowAs[] = $locate->Translate("credit");
			$fieldsFromSearchShowAs[] = $locate->Translate("destination");
			$fieldsFromSearchShowAs[] = $locate->Translate("memo");


			// Create object whit 5 cols and all data arrays set before.
			$table = new ScrollTable(9,$start,$limit,$filter,$numRows,$content,$order,$customerid,$cdrtype);
			$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=false,$delete=false,$detail=false);
			$table->setAttribsCols($attribsCols);
			$table->addRowSearchMore("mycdr",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

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
				$rowc[] = $row['disposition'];
				$rowc[] = $row['credit'];
				$rowc[] = $row['destination'];
				$rowc[] = $row['memo'];
				$table->addRow("mycdr",$rowc,false,false,false,$divName,$fields);
			}
			$html = $table->render();
		}
		// End Editable Zone		
		return $html;
	}

	
	function &getAllCdrRecords($customerid='',$cdrtype='',$start, $limit, $order = null, $creby = null,$allOrAnswer = null){//echo $cdrtype;exit;
		global $db;
		if($cdrtype == 'recent'){
			if($_SESSION['curuser']['extension'] != ''){
				$sql = "SELECT mycdr.*,monitorrecord.filename as filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.id as monitorid FROM mycdr LEFT JOIN monitorrecord ON monitorrecord.id = mycdr.monitored WHERE mycdr.accountid = '".$_SESSION['curuser']['accountid']."' AND mycdr.processed >= 0 ";
				if($order == null || is_array($order)){
					$sql .= " ORDER by mycdr.calldate DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
				}else{
					$sql .= " ORDER BY mycdr.".$order." ".$_SESSION['ordering']." LIMIT $start, $limit";
				}
				//echo $sql;exit;
				astercrm::events($sql);
				$res =& $db->query($sql);
				return $res;
			}else{
				$sql = "SELECT * FROM mycdr WHERE id = 0";
				astercrm::events($sql);
				$res =& $db->query($sql);
				return $res;
			}
		}
		if($customerid != ''){
			if($cdrtype == 'out'){
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR','','Mycdr');
				if($sql != ''){
					$sql = "(".$sql.") AND Mycdr.src != Mycdr.dst  AND mycdr.processed >= 0 ";
				}else{
					$sql = " Mycdr.id = 0";
				}
			}else{
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'src','OR','','Mycdr');
				if($sql != ''){
					$sql = "(".$sql.")  AND mycdr.processed >= 0 ";
				}else{
					$sql = " Mycdr.id = 0";
				}
			}
		}
		
		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON monitorrecord.id = mycdr.monitored LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.processed >= 0 ";
		}else if (($_SESSION['curuser']['usertype'] == 'groupadmin' || is_array($_SESSION['curuser']['privileges']['cdr'])) && $customerid == ''){
			if($_SESSION['curuser']['groupid'] != ''){
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON monitorrecord.id = mycdr.monitored LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE dstchannel != '' AND astercrm_groupid=".$_SESSION['curuser']['groupid']." AND mycdr.processed >= 0 ";
			}else {
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON monitorrecord.id = mycdr.monitored LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.id = 0";
			}
		}else{
			if($sql != '' ) {
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON monitorrecord.id = mycdr.monitored LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE ".$sql." AND mycdr.processed >= 0 ";
			}else {
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON monitorrecord.id = mycdr.monitored LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.id = 0";
			}
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND mycdr.billsec > 0 ";
		}
		
		//print_r($order);exit;
		if($order == null || is_array($order) || $order == ''){
			$sql .= " ORDER BY mycdr.calldate DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY mycdr.".$order." ".$_SESSION['ordering']." LIMIT $start, $limit";
		}
		#print_r($sql);exit;
		astercrm::events($sql);
		$res =& $db->query($sql);
		
		return $res;
	}

	function &getCdrNumRows($customerid='',$cdrtype='',$filter = null, $content = null,$allOrAnswer = null){
		global $db;
		if($cdrtype == 'recent'){
			if($_SESSION['curuser']['extension'] != ''){
				$sql = "SELECT COUNT(*) FROM mycdr WHERE mycdr.accountid = '".$_SESSION['curuser']['accountid']."' AND mycdr.processed >= 0 ";				
				astercrm::events($sql);
				$res =& $db->getOne($sql);
				return $res;
			}else{
				$sql = "SELECT COUNT(*) FROM mycdr WHERE id = 0";
				astercrm::events($sql);
				$res =& $db->getOne($sql);
				return $res;
			}
		}
		if($customerid != ''){
			if ($cdrtype == 'out'){
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR');
				if($sql != ''){
					$sql = "(".$sql.") AND src != dst";
				}else{
					$sql = " id = 0";
				}
			}else{
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'src','OR');
				if($sql != ''){
					$sql = "(".$sql.") ";
				}else{
					$sql = " id = 0";
				}
			}
		}

		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT COUNT(*) FROM mycdr LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.processed >= 0 ";
		}elseif (($_SESSION['curuser']['usertype'] == 'groupadmin' || is_array($_SESSION['curuser']['privileges']['cdr']))&& $customerid == ''){
			if($_SESSION['curuser']['groupid'] != ''){
				$sql = "SELECT COUNT(*) FROM mycdr LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE astercrm_groupid='".$_SESSION['curuser']['groupid']."' AND mycdr.processed >= 0 ";
			}else {
				return '0';
			}
		}else{
			if($sql != '' ) {
				$sql = "SELECT COUNT(*) FROM mycdr LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE ".$sql." AND mycdr.processed >= 0 ";
			}else {
				return '0';
			}
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND mycdr.billsec > 0 ";
		}
		
		astercrm::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getCdrRecordsFilteredMore($customerid='',$cdrtype='',$start, $limit, $filter, $content, $order,$table = '', $ordering = "",$allOrAnswer = null){
		global $db;
		
		$i=0;
		$joinstr='';
		foreach ($content as $value){
			$value = preg_replace("/'/","\\'",$value);
			$value=trim($value);
			if (strlen($value)!=0 && strlen($filter[$i]) != 0){
				if($filter[$i] == 'username') {
					$joinstr.="AND astercrm_account.".$filter[$i]." like '%".$value."%' ";
				} else if($filter[$i] == 'groupname') {
					$joinstr.="AND astercrm_accountgroup.".$filter[$i]." like '%".$value."%' ";
				} else {
					$joinstr.="AND mycdr.".$filter[$i]." like '%".$value."%' ";
				}
			}
			$i++;
		}
		if($customerid != ''){
			if($cdrtype == 'out'){
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR','','Mycdr');
				$sql = "(".$sql.") AND Mycdr.dstchannel != '' AND Mycdr.src != Mycdr.dst  AND mycdr.processed >= 0 ";
			}else{
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'src','OR','','Mycdr');
				$sql = "(".$sql.") AND Mycdr.dstchannel != ''  AND mycdr.processed >= 0 ";
			}
		}
		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.dstchannel != ''  AND mycdr.processed >= 0 ";
		}elseif (($_SESSION['curuser']['usertype'] == 'groupadmin' || is_array($_SESSION['curuser']['privileges']['cdr'])) && $customerid == ''){
			if($_SESSION['curuser']['groupid'] != ''){
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE dstchannel != '' AND astercrm_groupid=".$_SESSION['curuser']['groupid']."  AND mycdr.processed >= 0 ";
			}else {
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.id = 0";
			}
		}else{
			if($sql != '' ) {
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE ".$sql." AND mycdr.processed >= 0 ";
			}else {
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.id = 0";
			}
		}
		
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND mycdr.billsec > 0 ";
		}
		
		$sql .= " ORDER BY mycdr.".$order
					." DESC LIMIT $start, $limit $ordering";
		
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getCdrNumRowsMore($customerid='',$cdrtype='',$filter = null, $content = null,$table = '',$allOrAnswer = null){
		global $db;

		$i=0;
		$joinstr='';
		foreach ($content as $value){
			$value = preg_replace("/'/","\\'",$value);
			$value=trim($value);
			if (strlen($value)!=0 && strlen($filter[$i]) != 0){
				//$joinstr.="AND $filter[$i] like '%".$value."%' ";
				if($filter[$i] == 'username') {
					$joinstr.="AND astercrm_account.".$filter[$i]." like '%".$value."%' ";
				} else if($filter[$i] == 'groupname') {
					$joinstr.="AND astercrm_accountgroup.".$filter[$i]." like '%".$value."%' ";
				} else {
					$joinstr.="AND mycdr.".$filter[$i]." like '%".$value."%' ";
				}
			}
			$i++;
		}
		if($customerid != ''){
			if ($cdrtype == 'out'){
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR');
				$sql = "(".$sql.") AND dstchannel != '' AND src != dst ";
			}else{
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'src','OR');
				$sql = "(".$sql.") AND dstchannel != '' ";
			}
		}

		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT COUNT(*) FROM mycdr LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE dstchannel != ''  AND mycdr.processed >= 0 ";
		}elseif (($_SESSION['curuser']['usertype'] == 'groupadmin' || is_array($_SESSION['curuser']['privileges']['cdr'])) && $customerid == ''){
			if($_SESSION['curuser']['groupid'] != ''){
				$sql = "SELECT COUNT(*) FROM mycdr LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE astercrm_groupid=".$_SESSION['curuser']['groupid']." AND dstchannel != ''  AND mycdr.processed >= 0 ";
			}else {
				return '0';
			}
		}else{
			if($sql != '' ) {
				$sql = "SELECT COUNT(*) FROM mycdr LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.") AND mycdr.processed >= 0 ";
			}else {
				return '0';
			}
		}
		if ($joinstr!=''){
			$sql .= " ".$joinstr;
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND mycdr.billsec > 0 ";
		}

		astercrm::events($sql);
		$res =& $db->getOne($sql);		
		return $res;
	}

	function &getCdrNumRowsMorewithstype($customerid,$cdrtype,$filter, $content,$stype,$allOrAnswer = null){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);
		
		if($customerid != ''){
			if ($cdrtype == 'out'){
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR');
				$sql = "(".$sql.") AND dstchannel != '' AND src != dst ";
			}else{
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'src','OR');
				$sql = "(".$sql.") AND dstchannel != '' ";
			}
		}
		
		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT COUNT(*) FROM mycdr LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE 1  AND mycdr.processed >= 0 ";
		}elseif (($_SESSION['curuser']['usertype'] == 'groupadmin' || is_array($_SESSION['curuser']['privileges']['cdr'])) && $customerid == ''){
			if($_SESSION['curuser']['groupid'] != ''){
				$sql = "SELECT COUNT(*) FROM mycdr LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE astercrm_groupid=".$_SESSION['curuser']['groupid']." AND mycdr.processed >= 0 ";
			}else {
				return '0';
			}
		}else{
			if($sql != '' ) {
				$sql = "SELECT COUNT(*) FROM mycdr LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.") AND mycdr.processed >= 0 ";
			}else {
				return '0';
			}
		}
		if ($joinstr!=''){
			$sql .= " ".$joinstr;
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND mycdr.billsec > 0 ";
		}

//echo $sql;exit;
		astercrm::events($sql);
		$res =& $db->getOne($sql);		
		return $res;
	}

	function &getCdrRecordsFilteredMorewithstype($customerid,$cdrtype,$start, $limit, $filter, $content, $stype,$order,$allOrAnswer = null){
		global $db;
		
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);
		
		if($customerid != ''){
			if($cdrtype == 'out'){
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR','','Mycdr');
				$sql = "(".$sql.") AND Mycdr.src != Mycdr.dst  AND mycdr.processed >= 0 ";
			}else{
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'src','OR','','Mycdr');
				$sql = "(".$sql.") AND mycdr.processed >= 0 ";
			}
		}
		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE 1 AND mycdr.processed >= 0 ";
		}elseif (($_SESSION['curuser']['usertype'] == 'groupadmin' || is_array($_SESSION['curuser']['privileges']['cdr'])) && $customerid == ''){
			if($_SESSION['curuser']['groupid'] != ''){
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE  astercrm_groupid=".$_SESSION['curuser']['groupid']." AND mycdr.processed >= 0 ";
			}else {
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.id = 0";
			}
		}else{
			if($sql != '' ) {
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE ".$sql." AND mycdr.processed >= 0 ";
			}else {
				$sql = "SELECT mycdr.*,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.id = 0";
			}
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND mycdr.billsec > 0 ";
		}
		
		$sql .= " ORDER BY mycdr.".$order
					." DESC LIMIT $start, $limit $ordering";
		
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function createDiallistGrid($userexten,$customerid,$start = 0, $limit = 1, $filter = null, $content = null, $stype = null, $order = null, $divName = "formDiallist", $ordering = ""){

		global $locate,$config;
		$_SESSION['ordering'] = $ordering;
		if($filter == null || $content == null || (!is_array($content) && $content == 'Array') || (!is_array(filter) && $filter == 'Array')){
			$content = null;
			$filter = null;
			$numRows =& Customer::getDiallistNumRows($userexten,$customerid);
			$arreglo =& Customer::getAllDiallist($userexten,$customerid,$start,$limit,$order);
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
				$numRows =& Customer::getDiallistNumRows($userexten,$customerid);
				$arreglo =& Customer::getAllDiallist($userexten,$customerid,$start,$limit,$order);
			}elseif($flag3 != 1 ){  //未选择搜索方式
				$order = "dialtime";
				$numRows =& Customer::getDiallistNumRowsMore($userexten,$customerid,$filter, $content);
				$arreglo =& Customer::getDiallistFilteredMore($userexten,$customerid,$start, $limit, $filter, $content, $order);
			}else{
				$order = "dialtime";
				$numRows =& Customer::getDiallistNumRowsMorewithstype($userexten,$customerid,$filter, $content,$stype);
				$arreglo =& Customer::getDiallistFilteredMorewithstype($userexten,$customerid,$start, $limit, $filter, $content, $stype,$order);
			}
		}	

		// Editable zone

		// Databse Table: fields
		$fields = array();
		$fields[] = 'dialnumber';
		$fields[] = 'customername';
		$fields[] = 'dialtime';
		//$fields[] = 'status';
		$fields[] = 'trytime';
		$fields[] = 'creby';
		//$fields[] = 'cretime';
		$fileds[] = 'campaignname';
		$fileds[] = 'memo';
		//$fileds[] = 'campaignnote';
		//$fieeds[] = 'inexten';

		// HTML table: Headers showed
		$headers = array();
		$headers[] = $locate->Translate("Dialnumber");
		$headers[] = $locate->Translate("Customername");
		$headers[] = $locate->Translate("Dialtime");
		//$headers[] = $locate->Translate("Status");
		$headers[] = $locate->Translate("Trytime");
		$headers[] = $locate->Translate("Creby");
		//$headers[] = $locate->Translate("Cretime");
		$headers[] = $locate->Translate("Campaignname");
		$headers[] = $locate->Translate("Memo");
		//$headers[] = $locate->Translate("Campaignnote");
		//$headers[] = $locate->Translate("Inexten");

		// HTML table: hearders attributes
		$attribsHeader = array();
		$attribsHeader[] = 'width="13%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="13%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="10%"';
		$attribsHeader[] = 'width="12%"';
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
		if($divName == "formDiallistPannel"){
			$stype = 'none';
		}else{
			$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","dialnumber","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","customername","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","dialtime","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			//$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","status","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","trytime","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","creby","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			//$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","cretime","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","diallist.id","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			//$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","diallist.id","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
			//$eventHeader[]= 'onClick=\'showDiallist("'.$userexten.'","'.$customerid.'",0,'.$limit.',"'.$filter.'","'.$content.'","diallist.inexten","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		}
		// Select Box: type table.
		$typeFromSearch = array();
		$typeFromSearch[] = 'like';
		$typeFromSearch[] = 'equal';
		$typeFromSearch[] = 'more';
		$typeFromSearch[] = 'less';

		// Selecct Box: Labels showed on searchtype select box.
		$typeFromSearchShowAs = array();
		$typeFromSearchShowAs[] = 'like';
		$typeFromSearchShowAs[] = '=';
		$typeFromSearchShowAs[] = '>';
		$typeFromSearchShowAs[] = '<';

		// Select Box: fields table.
		$fieldsFromSearch = array();
		$fieldsFromSearch[] = 'dialnumber';
		$fieldsFromSearch[] = 'customername';
		$fieldsFromSearch[] = 'dialtime';
		$fieldsFromSearch[] = 'status';
		$fieldsFromSearch[] = 'trytime';
		$fieldsFromSearch[] = 'creby';
		//$fieldsFromSearch[] = 'cretime';

		// Selecct Box: Labels showed on search select box.
		$fieldsFromSearchShowAs = array();
		$fieldsFromSearchShowAs[] = $locate->Translate("dialnumber");
		$fieldsFromSearchShowAs[] = $locate->Translate("dialtime");
		$fieldsFromSearchShowAs[] = $locate->Translate("status");
		$fieldsFromSearchShowAs[] = $locate->Translate("trytime");
		$fieldsFromSearchShowAs[] = $locate->Translate("creby");
		//$fieldsFromSearchShowAs[] = $locate->Translate("cretime");

		// Create object whit 5 cols and all data arrays set before.
		$table = new ScrollTable(11,$start,$limit,$filter,$numRows,$content,$order,$customerid,'',$userexten,'diallist',$divName);
		//if($divName == "formDiallistPannel"){
			//$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=0,$delete=1,$detail=false);
		//}else{
			$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=1,$delete=1,$detail=false);
		//}
		$table->setAttribsCols($attribsCols);
		$table->addRowSearchMore("diallist",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,"1",0,$typeFromSearch,$typeFromSearchShowAs,$stype);
		
		
		if($divName == "formDiallistPannel"){
			foreach($arreglo as $row){
				$rowc = array();			
				$rowc[] = $row['id'];
				$rowc[] = "<a href=? onclick=\"xajax_getPreDiallist('".$row['id']."');return false;\">".$row['dialnumber']."</a>";
				$rowc[] = $row['customername'];
				$rowc[] = $row['dialtime'];
				$rowc[] = $row['trytime'];
				$rowc[] = $row['creby'];
				$rowc[] = $row['campaignname'];
				$rowc[] = $row['memo'];

				$styleStr = '';
				$tipmins = $config['system']['diallist_pannel_tip'];
				if(!is_numeric($tipmins)) $tipmins = 30;

				if($row['dialtime'] > '0000-00-00 00:00:00'){
					
					$tip1time = date("Y-m-d H:i:s",strtotime($row['dialtime']." -$tipmins mins"));

					if($tip1time <= date("Y-m-d H:i:s")){
						$styleStr = "background:#78cdd1";
					}
				}

				$table->addRow("diallist",$rowc,1,1,false,$divName,$fields,$row['creby'],$styleStr);

			}
		}else{
			while ($arreglo->fetchInto($row)) {
				//print_R($row);exit;
			// Change here by the name of fields of its database table
				$rowc = array();			
				$rowc[] = $row['id'];
				$rowc[] = $row['dialnumber'];
				$rowc[] = $row['customername'];
				$rowc[] = $row['dialtime'];
				//$rowc[] = $row['status'];
				$rowc[] = $row['trytime'];
				$rowc[] = $row['creby'];
				//$rowc[] = $row['cretime'];
				$rowc[] = $row['campaignname'];
				$rowc[] = $row['memo'];
				//$rowc[] = $row['campaignnote'];
				//$rowc[] = $row['inexten'];				
				$table->addRow("diallist",$rowc,1,1,false,$divName,$fields,$row['creby'],$styleStr);
			}
		}
		
		// End Editable Zone
		
		$html = $table->render();
		
		return $html;
	}

	
	function &getAllDiallist($userexten,$customerid,$start, $limit, $order = null, $creby = null){
		global $db;
		if($customerid > 0){
			$sql = astercrm::getCustomerphoneSqlByid($customerid,'dialnumber','OR');
		}
		
		if( $sql != '') {
			$sql = "SELECT diallist.*,campaign.campaignname,campaign.campaignnote, campaign.inexten FROM diallist LEFT JOIN campaign ON diallist.campaignid = campaign.id WHERE diallist.assign ='".$userexten."' AND (".$sql.")";
		}else{
			
			if($customerid > 0){
				$sql = "SELECT * FROM diallist WHERE id = '0' ";
			}else{ //diallistPannel	
				$sql = "SELECT diallist.*,campaign.campaignname,campaign.campaignnote, campaign.inexten FROM diallist LEFT JOIN campaign ON diallist.campaignid = campaign.id WHERE diallist.assign ='".$userexten."' AND dialtime != '0000-00-00 00:00:00' AND callOrder > 0 ORDER BY dialtime ASC, callOrder DESC, id ASC LIMIT 0,$limit";
				astercrm::events($sql);
				$res =& $db->query($sql);
				
				while($res->fetchInto($row)){
					$rows[] = $row;
				}
				if(count($rows) < 5){
					$limit = 5 - count($rows);
					$sql = "SELECT diallist.*,campaign.campaignname,campaign.campaignnote, campaign.inexten FROM diallist LEFT JOIN campaign ON diallist.campaignid = campaign.id WHERE diallist.assign ='".$userexten."' AND dialtime = '0000-00-00 00:00:00' AND callOrder > 0 ORDER BY callOrder DESC, id ASC LIMIT 0,$limit";
					astercrm::events($sql);
					$res =& $db->query($sql);
					while($res->fetchInto($row)){
						$rows[] = $row;
					}
				}
				
				return $rows;
			}
		}
		if($customerid > 0){
			if($order == null || is_array($order)){
				$sql .= " ORDER by diallist.id DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
			}else{
				$sql .= " ORDER BY diallist.".$order." ".$_SESSION['ordering']." LIMIT $start, $limit";
			}
		}
//echo $sql;exit;
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getDiallistNumRows($userexten,$customerid,$filter = null, $content = null){
		global $db;
		
		$sql = astercrm::getCustomerphoneSqlByid($customerid,'dialnumber','OR');
		
		if( $sql != '') {
			$sql = "SELECT COUNT(*) FROM diallist WHERE assign ='".$userexten."' AND (".$sql.")";
		}else{
			return '0';
		}

		astercrm::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getDiallistFilteredMore($userexten,$customerid,$start, $limit, $filter, $content, $order,$table = '', $ordering = ""){
		global $db;

		$sql = astercrm::getCustomerphoneSqlByid($customerid,'dialnumber','OR');
				
		$i=0;
		$joinstr='';
		foreach ($content as $value){
			$value = preg_replace("/'/","\\'",$value);
			$value=trim($value);
			if (strlen($value)!=0 && strlen($filter[$i]) != 0){
				$joinstr.="AND diallist.$filter[$i] like '%".$value."%' ";
			}
			$i++;
		}
		
		if( $sql != '') {
			$sql = "SELECT diallist.*,campaign.campaignname,campaign.campaignnote,campaign.inexten FROM diallist LEFT JOIN campaign ON diallist.campaignid = campaign.id WHERE diallist.assign ='".$userexten."' AND (".$sql.")";
		}else{
			$sql = "SELECT * FROM diallist WHERE id = '0' ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
		}

		$sql .= " ORDER BY diallist.".$order
					." DESC LIMIT $start, $limit $ordering";

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getDiallistNumRowsMore($userexten,$customerid,$filter = null, $content = null){
		global $db;

		$sql = astercrm::getCustomerphoneSqlByid($customerid,'dialnumber','OR');

		$i=0;
		$joinstr='';
		foreach ($content as $value){
			$value = preg_replace("/'/","\\'",$value);
			$value=trim($value);
			if (strlen($value)!=0 && strlen($filter[$i]) != 0){
				$joinstr.="AND diallist.$filter[$i] like '%".$value."%' ";
			}
			$i++;
		}
		
		if( $sql != '') {
			$sql = "SELECT COUNT(*) FROM diallist WHERE assign ='".$userexten."' AND (".$sql.") ";
		}else{
			return '0';
		}
		
		if ($joinstr!=''){
			$sql .= " ".$joinstr;
		}

		astercrm::events($sql);
		$res =& $db->getOne($sql);		
		return $res;
	}

	function &getDiallistNumRowsMorewithstype($userexten,$customerid,$filter, $content,$stype){
		global $db;

		$sql = astercrm::getCustomerphoneSqlByid($customerid,'dialnumber','OR');

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);
		
		if( $sql != '') {
			$sql = "SELECT COUNT(*) FROM diallist WHERE assign ='".$userexten."' AND (".$sql.") ";
		}else{
			return '0';
		}
		
		if ($joinstr!=''){
			$sql .= " ".$joinstr;
		}

		astercrm::events($sql);
		$res =& $db->getOne($sql);		
		return $res;
	}

	function &getDiallistFilteredMorewithstype($userexten,$customerid,$start, $limit, $filter, $content, $stype,$order){
		global $db;

		$sql = astercrm::getCustomerphoneSqlByid($customerid,'dialnumber','OR');
				
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);
		
		if( $sql != '') {
			$sql = "SELECT diallist.*,campaign.campaignname,campaign.campaignnote,campaign.inexten FROM diallist LEFT JOIN campaign ON diallist.campaignid = campaign.id WHERE diallist.assign ='".$userexten."' AND (".$sql.")";
		}else{
			$sql = "SELECT * FROM diallist WHERE id = '0' ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
		}

		$sql .= " ORDER BY diallist.".$order
					." DESC LIMIT $start, $limit $ordering";

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function formDiallistAdd($userexten,$customerid){
		global $locate;
//		echo $userexten.$customerid;exit;
		if ($_SESSION['curuser']['usertype'] == 'admin'){
				$res = Customer::getGroups();
				$groupoptions .= '<select name="groupid" id="groupid" onchange="setCampaign();">';
				while ($row = $res->fetchRow()) {
						$groupoptions .= '<option value="'.$row['groupid'].'"';
						$groupoptions .='>'.$row['groupname'].'</option>';
				}				
				$groupoptions .= '</select>';	
				$assignoptions = '<input type="text" id="assign" name="assign" size="35"">';
		}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){				
				$groupoptions .= $_SESSION['curuser']['group']['groupname'].'<input id="groupid" name="groupid" type="hidden" value="'.$_SESSION['curuser']['groupid'].'">';	
				$res = Customer::getRecordsByField('groupid',$_SESSION['curuser']['groupid'],'astercrm_account');
				$assignoptions .= '<select name="assign" id="assign">';
				$assignoptions .= '<option value="">'.$locate->Translate("none").'</option>';
				while ($row = $res->fetchRow()) {
						$assignoptions .= '<option value="'.$row['extension'].'"';
						$assignoptions .='>'.$row['extension'].'</option>';
				}				
				$assignoptions .= '</select>';
		}else{
				$groupoptions .= $_SESSION['curuser']['group']['groupname'].'<input id="groupid" name="groupid" type="hidden" value="'.$_SESSION['curuser']['groupid'].'">';	
				$assignoptions = '<input type="text" id="assign" name="assign" size="35" value="'.$_SESSION['curuser']['extension'].'" disabled><input type="hidden" id="assign" name="assign" value="'.$_SESSION['curuser']['extension'].'">';
		}

		$customernamehtml = '';
		if($userexten != '' && $customerid > 0){
			$res_customer =astercrm::getRecordById($customerid,'customer');
			$res_contact =astercrm::getContactListByID($customerid);
			$numberblank = '<select name="dialnumber" id="dialnumber">';
			if ($res_customer['phone'] != '') $numberblank .= '<option value="'.$res_customer['phone'].'">'.$res_customer['phone'].'</option>';
			if ($res_customer['mobile'] != '') $numberblank .= '<option value="'.$res_customer['mobile'].'">'.$res_customer['mobile'].'</option>';
			while ($res_contact->fetchInto($row)) {
				if ($row['phone'] != '') $numberblank .= '<option value="'.$row['phone'].'">'.$row['phone'].'</option>';
				if ($row['phone1'] != '') $numberblank .= '<option value="'.$row['phone1'].'">'.$row['phone1'].'</option>';
				if ($row['phone2'] != '') $numberblank .= '<option value="'.$row['phone2'].'">'.$row['phone2'].'</option>';
				if ($row['mobile'] != '') $numberblank .= '<option value="'.$row['mobile'].'">'.$row['mobile'].'</option>';
			}
			$numberblank .= '</select>';
			$saveHtml = '
					<tr>
						<td nowrap colspan=2 align=right><input type="button" id="btnAddDiallist" name="btnAddDiallist" value="'.$locate->Translate("continue").'" onclick="xajax_saveDiallist(xajax.getFormValues(\'formaddDiallist\'),\''.$userexten.'\',\''.$customerid.'\');return false;"></td>
					</tr>
				<table>
				</form>
				';
		}else{
			$numberblank = '<input  name="dialnumber" id="dialnumber">';
			$customernamehtml = '<tr>
									<td nowrap align="left">'.$locate->Translate("Customer Name").'</td>
									<td align="left"><input  name="customername" id="customername"></td>
								</tr>';
			$saveHtml = '
					<tr>
						<td nowrap colspan=2 align=right><input type="button" id="btnAddDiallist" name="btnAddDiallist" value="'.$locate->Translate("continue").'" onclick="saveDiallistMain(xajax.getFormValues(\'formaddDiallist\'));return false;"></td>
					</tr>
				<table>
				</form>
				';
		}

		$html = '
				<!-- No edit the next line -->
				<form method="post" name="formaddDiallist" id="formaddDiallist">
				
				<table border="1" width="100%" class="adminlist">'.$customernamehtml.'
					<tr>
						<td nowrap align="left">'.$locate->Translate("Dialnumber").'*</td>
						<td align="left">'.$numberblank.'</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Assign To").'</td>
						<td align="left">
							'.$assignoptions.'
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Call Order").'</td>
						<td align="left">
							<input type="text" id="callOrder" name="callOrder" size="20" value="1">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Dialtime").'</td>
						<td align="left">
							<input type="text" id="dialtime" name="dialtime" size="20" value="'.date("Y-m-d H:i",time()).'">
			<INPUT onclick="displayCalendar(document.getElementById(\'dialtime\'),\'yyyy-mm-dd hh:ii\',this,true)" type="button" value="Cal">
						</td>
					</tr>';
		$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Group Name").'</td>
						<td>'.$groupoptions.'</td>
					</tr>';
		$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Campaign Name").'</td>
						<td><SELECT id="campaignid" name="campaignid"></SELECT></td>
					</tr>';
		$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Memo").'</td>
						<td><textarea id="memo" name="memo" cols="50" rows="8"></textarea></td>
					</tr>';
		$html .= $saveHtml;
		return $html;
	}

	function createRecordsGrid($customerid='',$start = 0, $limit = 1, $filter = null, $content = null, $order = null, $divName = "formRecords", $ordering = "",$stype=null ){
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
				$order = null;
				$numRows =& astercrm::getRecNumRows($customerid);
				$arreglo =& astercrm::getAllRecRecords($customerid,$start,$limit,$order);
			}elseif($flag3 != 1 ){  //未选择搜索方式
				$order = "mycdr.id";
				$numRows =& astercrm::getRecNumRowsMore($customerid,$filter, $content);
				$arreglo =& astercrm::getRecRecordsFilteredMore($customerid,$start, $limit, $filter, $content, $order);
			}else{
				$order = "mycdr.id";
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
		$attribsHeader[] = 'width="11%"';
		$attribsHeader[] = 'width="11%"';
		$attribsHeader[] = 'width="11%"';
		$attribsHeader[] = 'width="12%"';
		$attribsHeader[] = 'width="11%"';
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
		$eventHeader[]= 'onClick=\'xajax_showRecords('.$customerid.',0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.calldate","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showRecords('.$customerid.',0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.src","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showRecords('.$customerid.',0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.dst","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showRecords('.$customerid.',0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.didnumber","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showRecords('.$customerid.',0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.dstchannel","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showRecords('.$customerid.',0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.duration","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showRecords('.$customerid.',0,'.$limit.',"'.$filter.'","'.$content.'","mycdr.billsec","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showRecords('.$customerid.',0,'.$limit.',"'.$filter.'","'.$content.'","monitorrecord.filename","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'xajax_showRecords('.$customerid.',0,'.$limit.',"'.$filter.'","'.$content.'","monitorrecord.creby","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		
		
		// Select Box: type table.
		$typeFromSearch = array();
		$typeFromSearch[] = 'like';
		$typeFromSearch[] = 'equal';
		$typeFromSearch[] = 'more';
		$typeFromSearch[] = 'less';

		// Selecct Box: Labels showed on searchtype select box.
		$typeFromSearchShowAs = array();
		$typeFromSearchShowAs[] = 'like';
		$typeFromSearchShowAs[] = '=';
		$typeFromSearchShowAs[] = '>';
		$typeFromSearchShowAs[] = '<';

		// Select Box: fields table.
		$fieldsFromSearch = array();
		$fieldsFromSearch[] = 'src';
		$fieldsFromSearch[] = 'calldate';
		$fieldsFromSearch[] = 'dst';
		$fieldsFromSearch[] = 'didnumber';
		$fieldsFromSearch[] = 'billsec';
		$fieldsFromSearch[] = 'filename';
		$fieldsFromSearch[] = 'creby';

		// Selecct Box: Labels showed on search select box.
		$fieldsFromSearchShowAs = array();
		$fieldsFromSearchShowAs[] = $locate->Translate("src");
		$fieldsFromSearchShowAs[] = $locate->Translate("calldate");
		$fieldsFromSearchShowAs[] = $locate->Translate("dst");
		$fieldsFromSearchShowAs[] = $locate->Translate("callee id");
		$fieldsFromSearchShowAs[] = $locate->Translate("billsec");
		$fieldsFromSearchShowAs[] = $locate->Translate("filename");
		$fieldsFromSearchShowAs[] = $locate->Translate("creby");

		// Create object whit 5 cols and all data arrays set before.
		$table = new ScrollTable(7,$start,$limit,$filter,$numRows,$content,$order,$customerid,'','','monitorrecord');
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=false,$delete=false,$detail=false);
		$table->setAttribsCols($attribsCols);
		$table->addRowSearchMore("cur_mycdrs",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,0,$typeFromSearch,$typeFromSearchShowAs,$stype);

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

			if($row['processed'] == 'yes' && $row['fileformat'] != 'error'){
				$rowc['filename'] = $row['filename'].'.'.$row['fileformat'];
			}else{
				$rowc['filename'] = '';
			}
			
			$rowc[] = $row['creby'];
			$table->addRow("monitorrecord",$rowc,false,false,false,$divName,$fields);
		}
		//donnie
		// End Editable Zone
		
		$html = $table->render();
		
		return $html;
	}

	function &getAllRecRecords($customerid='',$start, $limit, $order = null, $creby = null){
		global $db;
		//echo 'aaaa';exit;
		if($customerid != ''){
			$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR','src','mycdr');
		}

		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.processed >= 0 ";
		}elseif ($_SESSION['curuser']['usertype'] == 'groupadmin' && $customerid == ''){
						
			$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.astercrm_groupid = '".$_SESSION['curuser']['groupid'] ."' AND mycdr.processed >= 0 ";
		}else{

			if($sql != '' ) {
				if($_SESSION['curuser']['usertype'] == 'admin' ){
					$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.")  AND mycdr.processed >= 0 ";
				}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
					$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.astercrm_groupid = '".$_SESSION['curuser']['groupid'] ."' AND (".$sql.")  AND mycdr.processed >= 0 ";
				}else{
					$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.accountid = '".$_SESSION['curuser']['accountid'] ."' AND (".$sql.")  AND mycdr.processed >= 0 ";
				}

			}else {
				$sql = "SELECT mycdr.* FROM monitorrecord LEFT JOIN mycdr ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.id = 0";
			}
		}

		if($order == null || is_array($order)){
			$sql .= " ORDER by mycdr.id DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY ".$order." ".$_SESSION['ordering']." LIMIT $start, $limit";
		}

		//echo $sql;exit;
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getRecNumRows($customerid='',$filter = null, $content = null){
		global $db;
		if($customerid != ''){
				$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR','src','mycdr');
		}

		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT count(*) FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.processed >= 0 ";
		}elseif ($_SESSION['curuser']['usertype'] == 'groupadmin' && $customerid == ''){
			
			$sql = "SELECT count(*) FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.astercrm_groupid = '".$_SESSION['curuser']['groupid'] ."' AND mycdr.processed >= 0 ";
			
		}else{
			if($sql != '' ) {
				if($_SESSION['curuser']['usertype'] == 'admin' ){
					$sql = "SELECT count(*) FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.") AND monitorrecord.creby = '".$_SESSION['curuser']['username']."' AND mycdr.dstchannel != ''  AND mycdr.processed >= 0 ";
				}elseif( $_SESSION['curuser']['usertype'] == 'groupadmin'){
					$sql = "SELECT count(*) FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.") AND mycdr.astercrm_groupid = '".$_SESSION['curuser']['groupid'] ."'  AND mycdr.processed >= 0 ";
				}else{
					$sql = "SELECT count(*) FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.") AND mycdr.accountid = '".$_SESSION['curuser']['accountid'] ."'  AND mycdr.processed >= 0 ";
				}
			}else {
				return '0';
			}
		}
		
		//$sql = "SELECT count(total.id) FROM (".$sql.") AS total ";
		astercrm::events($sql);
		$res =& $db->getOne($sql);
		//print_r($res);die;
		return $res;		
	}	

	function &getRecNumRowsMorewithstype($customerid,$filter, $content,$stype){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'mycdr');

		if($customerid != ''){
			$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR','src','mycdr');
		}

		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT mycdr.id FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.processed >= 0 ";
		}elseif ($_SESSION['curuser']['usertype'] == 'groupadmin' && $customerid == ''){
			$sql = "SELECT mycdr.id FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.astercrm_groupid = '".$_SESSION['curuser']['groupid'] ."'  AND mycdr.processed >= 0 ";
			
		}else{
			if($sql != '' ) {
				if($_SESSION['curuser']['usertype'] == 'admin' ){
					$sql = "SELECT count(*) FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.")  AND mycdr.processed >= 0 ";
				}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
					$sql = "SELECT count(*) FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.") AND mycdr.astercrm_groupid = '".$_SESSION['curuser']['groupid'] ."'  AND mycdr.processed >= 0 ";
				}else{
					$sql = "SELECT count(*) FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.") AND mycdr.accountid = '".$_SESSION['curuser']['accountid'] ."'  AND mycdr.processed >= 0 ";
				}
			}else {
				return '0';
			}
		}
		if ($joinstr!=''){
			$sql .= " ".$joinstr;
		}
		//echo $sql;exit;
		//$sql .= " GROUP BY mycdr.id ";
		//$sql = "SELECT count(total.id) FROM (".$sql.") AS total ";
		astercrm::events($sql);
		$res =& $db->getOne($sql);		
		return $res;
	}

	function &getRecRecordsFilteredMorewithstype($customerid,$start, $limit, $filter, $content, $stype,$order){
		global $db;
		
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'monitorrecord');

		if($customerid != ''){
			$sql = astercrm::getCustomerphoneSqlByid($customerid,'dst','OR','src','mycdr');
		}
		if($_SESSION['curuser']['usertype'] == 'admin' && $customerid == ''){
			$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.processed >= 0 ";
		}elseif ($_SESSION['curuser']['usertype'] == 'groupadmin' && $customerid == ''){
			$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE  mycdr.astercrm_groupid = '".$_SESSION['curuser']['groupid'] ."' AND mycdr.processed >= 0 ";
			
		}else{
			if($sql != '' ) {
				if($_SESSION['curuser']['usertype'] == 'admin' ){
					$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.") AND mycdr.processed >= 0 ";
				}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
					$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE  (".$sql.")  AND mycdr.astercrm_groupid = '".$_SESSION['curuser']['groupid'] ."' AND mycdr.processed >= 0 ";
				}else{
					$sql = "SELECT mycdr.calldate,mycdr.src,mycdr.dst,mycdr.didnumber,mycdr.dstchannel,mycdr.duration,mycdr.billsec,monitorrecord.id,monitorrecord.filename,monitorrecord.fileformat,monitorrecord.processed,monitorrecord.creby,astercrm_accountgroup.groupname,astercrm_account.username FROM mycdr LEFT JOIN monitorrecord ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE (".$sql.")  AND mycdr.accountid = '".$_SESSION['curuser']['accountid'] ."' AND mycdr.processed >= 0 ";
				}
			}else {
				$sql = "SELECT mycdr.* FROM monitorrecord LEFT JOIN mycdr ON mycdr.monitored = monitorrecord.id LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = mycdr.astercrm_groupid LEFT JOIN astercrm_account ON astercrm_account.id = mycdr.accountid WHERE mycdr.id = 0";
			}
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
		}

		$sql .= " ORDER BY ".$order
					." DESC LIMIT $start, $limit $ordering";
		
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getAllSpeedDialRecords(){
		global $db;

		$sql = "SELECT number,description FROM speeddial ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE groupid = ".$_SESSION['curuser']['groupid']." ";
		}
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &readReport($groupid,$accountid,$sdate,$edate,$type){
		global $db;
		if(($groupid == '' || $groupid == 0) && ($accountid == '' || $accountid == 0) && $_SESSION['curuser']['usertype'] != "admin") $groupid = $_SESSION['curuser']['groupid'];

		$query = "SELECT COUNT(*) as recordNum, SUM(billsec) as seconds FROM mycdr WHERE calldate >= '$sdate' AND  calldate <= '$edate'";
		if(is_numeric($accountid) && $accountid > 0){			
			$query .= " AND accountid = $accountid";
		}else{
			if(is_numeric($groupid)){
				if($groupid > 0){					
					$query .= " AND astercrm_groupid = $groupid";
				}
			}
		}
//echo $query;exit;
		astercrm::events($query);
		$all_res =& $db->query($query);
		if($type != 'both'){
			return $all_res;
		}
		$answered = & $db->getone($query." AND disposition = 'ANSWERED'");
		return array('all'=>$all_res,'answered'=>$answered);
	}

	function &readReportAgent($groupid,$accountid,$sdate,$edate){
		global $db;

		$return_arr = array();

		if ($_SESSION['curuser']['usertype'] == "admin"){
			if(($groupid == '' || $groupid == 0) && ($accountid == '' || $accountid == 0)){
				$query = "SELECT COUNT(*) as recordNum, mycdr.astercrm_groupid,groupname FROM mycdr LEFT JOIN astercrm_accountgroup ON mycdr.astercrm_groupid = astercrm_accountgroup.id WHERE  calldate >= '$sdate' AND  calldate <= '$edate' AND mycdr.astercrm_groupid > 0 ";

				$query_a = "SELECT COUNT(*) as arecordNum, SUM(billsec) as seconds ,mycdr.astercrm_groupid FROM mycdr WHERE calldate >= '$sdate' AND  calldate <= '$edate' AND mycdr.astercrm_groupid > 0 AND billsec > 0";
		
				$query .= " GROUP BY mycdr.astercrm_groupid ";
				$query_a .= " GROUP BY mycdr.astercrm_groupid ";
				$all_res =& $db->query($query);
				$return_arr['type'] = 'grouplist';

				while($all_res->fetchinto($row)){
					$return_arr[$row['astercrm_groupid']]['recordNum'] = $row['recordNum'];
					$return_arr[$row['astercrm_groupid']]['groupname'] = $row['groupname'];
					$return_arr[$row['astercrm_groupid']]['arecordNum'] = 0;
					$return_arr[$row['astercrm_groupid']]['seconds'] = 0;
					
				}

				$answer_res =& $db->query($query_a);

				while($answer_res->fetchinto($arow)){

					$return_arr[$arow['astercrm_groupid']]['arecordNum'] = $arow['arecordNum'];
					$return_arr[$arow['astercrm_groupid']]['seconds'] = $arow['seconds'];
				}
				return $return_arr;
			}
		}	

		if(($groupid == '' || $groupid == 0) && ($accountid == '' || $accountid == 0) && $_SESSION['curuser']['usertype'] != "admin") $groupid = $_SESSION['curuser']['groupid'];
		
		if(is_numeric($accountid) && $accountid > 0){
			$return_arr['type'] = 'agentlist';
			$query = "SELECT COUNT(*) as recordNum FROM mycdr WHERE calldate >= '$sdate' AND  calldate <= '$edate' ";

			$query_a = "SELECT COUNT(*) as arecordNum, SUM(billsec) as seconds FROM mycdr WHERE  calldate >= '$sdate' AND  calldate <= '$edate' AND billsec > 0";
			$query .= " AND mycdr.astercrm_groupid = ".$groupid." ";
			$query_a .= " AND mycdr.astercrm_groupid = ".$groupid." ";
			
			$query .= " AND accountid=".$accountid;
			$query_a .= " AND accountid=".$accountid;

			$all_count = & $db->getone($query);
			$answer_row = & $db->getRow($query_a);

			$account = astercrm::getRecordById($accountid,'astercrm_account');

			$return_arr[$accountid]['recordNum'] = $all_count;
			$return_arr[$accountid]['username'] = $account['extension'];
			$return_arr[$accountid]['name'] = $account['username'];
			$return_arr[$accountid]['arecordNum'] = $answer_row['arecordNum'];
			$return_arr[$accountid]['seconds'] = $answer_row['seconds'];
		}else{
			if(is_numeric($groupid)){
				if($groupid > 0){
					$return_arr['type'] = 'agentlist';
					$member = astercrm::getGroupMemberListByID($groupid);
					while($member->fetchinto($row)){
						$extens = '';
						$channels = '';
						$agents = '';
						$query = "SELECT COUNT(*) as recordNum FROM mycdr WHERE calldate >= '$sdate' AND  calldate <= '$edate' ";

						$query_a = "SELECT COUNT(*) as arecordNum, SUM(billsec) as seconds FROM mycdr WHERE calldate >= '$sdate' AND  calldate <= '$edate' AND billsec > 0";

						$query .= " AND mycdr.astercrm_groupid = ".$groupid." ";
						$query_a .= " AND mycdr.astercrm_groupid = ".$groupid." ";
						
						$query .= " AND accountid=".$row['id'];
						$query_a .= " AND accountid=".$row['id'];

						$all_count = & $db->getone($query);
						$answer_row = & $db->getRow($query_a);
						$return_arr[$row['id']]['recordNum'] = $all_count;
						$return_arr[$row['id']]['username'] = $row['extension'];
						$return_arr[$row['id']]['name'] = $row['username'];
						$return_arr[$row['id']]['arecordNum'] = $answer_row['arecordNum'];
						$return_arr[$row['id']]['seconds'] = $answer_row['seconds'];

					}					
				}
			}
		}

		return $return_arr;
	}

	function &checkDialedlistCall($dialnumber){
		global $db;
		$sql = "SELECT id,campaignid FROM dialedlist WHERE dialednumber = '".$dialnumber."' ORDER BY dialedtime DESC LIMIT 1";
		//echo $sql;exit;
		astercrm::events($sql);
		//$res = & $db->getOne($sql);
		$res = & $db->getRow($sql);
		return $res;
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

	function createTikcetGrid($Cid=0,$Ctype="",$start=0,$limit=1,$filter=null,$content=null,$order=null,$divName="",$ordering="", $stype=array()){
		global $locate;
		$_SESSION['ordering'] = $ordering;
		if($filter == null or $content == null or $content == 'Array' or $filter == 'Array'){
			$numRows =& astercrm::getTicketNumRows($filter, $content,$Ctype,$Cid);
			$arreglo =& astercrm::getAllTicketRecords($start,$limit,$order,$Ctype,$Cid);
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
				$numRows =& astercrm::getTicketNumRows($filter, $content,$Ctype,$Cid);
				$arreglo =& astercrm::getAllTicketRecords($start,$limit,$order,$Ctype,$Cid);
			}elseif($flag3 != 1){
				$order = "id";
				$numRows =& astercrm::getTicketNumRowsMore($filter, $content,$Ctype,$Cid);
				$arreglo =& astercrm::getTicketRecordsFilteredMore($start, $limit, $filter, $content, $order,$Ctype,$Cid);
			}else{
				$order = "id";
				$numRows =& astercrm::getTicketNumRowsMorewithstype($filter, $content,$stype,$table,$Ctype,$Cid);
				$arreglo =& astercrm::getTicketRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table,$Ctype,$Cid);
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
		if($Ctype == 'agent_tickets') {
			$fields[] = 'id';
			$fields[] = 'ticketname';
			$fields[] = 'customer';
			$fields[] = 'status';
			$fields[] = 'memo';
			$fields[] = 'creby';
		} else {
			$fields[] = 'id';
			$fields[] = 'ticketcategoryname';
			$fields[] = 'ticketname';
			$fields[] = 'customer';
			$fields[] = 'assignto';
			$fields[] = 'status';
			$fields[] = 'memo';
			$fields[] = 'creby';
		}

		// HTML table: Headers showed
		$headers = array();
		$headers[] = $locate->Translate("Ticket Id");
		if($Ctype != 'agent_tickets') {
			$headers[] = $locate->Translate("TicketCategory Name");
		}
		$headers[] = $locate->Translate("Ticket Name");
		$headers[] = $locate->Translate("Customer");
		if($Ctype != 'agent_tickets') {
			$headers[] = $locate->Translate("AssignTo");
		}
		$headers[] = $locate->Translate("Status");
		$headers[] = $locate->Translate("Memo");
		$headers[] = $locate->Translate("Creby");
		
		if($Ctype == 'agent_tickets') {
			// HTML table: hearders attributes
			$attribsHeader = array();
			$attribsHeader[] = 'width="15%"';
			$attribsHeader[] = 'width="20%"';
			$attribsHeader[] = 'width="20%"';
			$attribsHeader[] = 'width="10%"';
			$attribsHeader[] = 'width="20%"';
			$attribsHeader[] = 'width="15%"';

			// HTML Table: columns attributes
			$attribsCols = array();
			$attribsCols[] = 'style="text-align: left"';
			$attribsCols[] = 'style="text-align: left"';
			$attribsCols[] = 'style="text-align: left"';
			$attribsCols[] = 'style="text-align: left"';
			$attribsCols[] = 'style="text-align: left"';
		} else {
			// HTML table: hearders attributes
			$attribsHeader = array();
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
		}
		
		// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
		$eventHeader = array();

		if($Ctype == 'agent_tickets') {
			$XajaxMath = 'showMyTicketsGrid';
		} else{
			$XajaxMath = 'AllTicketOfMyGrid';
		}
		$eventHeader[]= 'onClick=\''.$XajaxMath.'('.$Cid.',"'.$Ctype.'",0,'.$limit.',"'.$filter.'","'.$content.'","id","'.$divName.'","ORDERING");return false;\'';
		if($Ctype != 'agent_tickets') {
			$eventHeader[]= 'onClick=\''.$XajaxMath.'('.$Cid.',"'.$Ctype.'",0,'.$limit.',"'.$filter.'","'.$content.'","ticketcategoryname","'.$divName.'","ORDERING");return false;\'';
		}
		$eventHeader[]= 'onClick=\''.$XajaxMath.'('.$Cid.',"'.$Ctype.'",0,'.$limit.',"'.$filter.'","'.$content.'","ticketname","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\''.$XajaxMath.'('.$Cid.',"'.$Ctype.'",0,'.$limit.',"'.$filter.'","'.$content.'","customer","'.$divName.'","ORDERING");return false;\'';
		if($Ctype != 'agent_tickets') {
			$eventHeader[]= 'onClick=\''.$XajaxMath.'('.$Cid.',"'.$Ctype.'",0,'.$limit.',"'.$filter.'","'.$content.'","username","'.$divName.'","ORDERING");return false;\'';
		}
		$eventHeader[]= 'onClick=\''.$XajaxMath.'('.$Cid.',"'.$Ctype.'",0,'.$limit.',"'.$filter.'","'.$content.'","status","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\''.$XajaxMath.'('.$Cid.',"'.$Ctype.'",0,'.$limit.',"'.$filter.'","'.$content.'","memo","'.$divName.'","ORDERING");return false;\'';
		$eventHeader[]= 'onClick=\''.$XajaxMath.'('.$Cid.',"'.$Ctype.'",0,'.$limit.',"'.$filter.'","'.$content.'","creby","'.$divName.'","ORDERING");return false;\'';

		// Select Box: fields table.
		$fieldsFromSearch = array();
		if($Ctype != 'agent_tickets') {
			$fieldsFromSearch[] = 'ticketcategoryname';
		}
		$fieldsFromSearch[] = 'ticketname';
		$fieldsFromSearch[] = 'customer';
		if($Ctype != 'agent_tickets') {
			$fieldsFromSearch[] = 'username';
		}
		//$fieldsFromSearch[] = 'status';
		//$fieldsFromSearch[] = 'memo';
		$fieldsFromSearch[] = 'creby';

		// Selecct Box: Labels showed on search select box.
		$fieldsFromSearchShowAs = array();
		if($Ctype != 'agent_tickets') {
			$fieldsFromSearchShowAs[] = $locate->Translate("TicketCategory Name");
		}
		$fieldsFromSearchShowAs[] = $locate->Translate("Ticket Name");
		$fieldsFromSearchShowAs[] = $locate->Translate("Customer");
		if($Ctype != 'agent_tickets') {
			$fieldsFromSearchShowAs[] = $locate->Translate("AssignTo");
		}
		//$fieldsFromSearchShowAs[] = $locate->Translate("Status");
		//$fieldsFromSearchShowAs[] = $locate->Translate("Memo");
		$fieldsFromSearchShowAs[] = $locate->Translate("Creby");

		// Create object whit 5 cols and all data arrays set before.
		$table = new ScrollTable(7,$start,$limit,$filter,$numRows,$content,$order,$Cid,$cdrtype,$userexten,$table='ticket_details',$divName);
		
		#$table = new ScrollTable(7,$start,$limit,$filter,$numRows,$content,$order,$customerid,$cdrtype);

		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,0,0,0);
		$table->ordering = $ordering;
		
		//echo $divName;exit;
		if($divName == 'formCurTickets') {
			$stype = 'none';
			$table->addRowSearchMore("add_new_tickets",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,1,0,$typeFromSearch,$typeFromSearchShowAs,$stype);
		} else {
			$table->addRowSearchMore("ticket_details",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,0,$typeFromSearch,$typeFromSearchShowAs,$stype);
		}

		while ($arreglo->fetchInto($row)) {
		// Change here by the name of fields of its database table
			$rowc = array();
			$rowc[] = $row['id'];
			$rowc[] = str_pad($row['id'],8,'0',STR_PAD_LEFT);
			if($Ctype != 'agent_tickets') {
				$rowc[] = $row['ticketcategoryname'];
			}
			$rowc[] = $row['ticketname'];
			$rowc[] = $row['customer'];
			if($Ctype != 'agent_tickets') {
				$rowc[] = $row['username'];
			}
			$rowc[] = $locate->Translate($row['status']);
			$rowc[] = $row['memo'];
			$rowc[] = $row['creby'];
			$table->addRow("ticket_details",$rowc,0,0,0,$divName,$fields);
		}
		if($divName == 'formCurTickets'){
			$html = $table->render('');//static
		} else {
			$html = $table->render('static');
		}
		return $html;
	}

	/**
	*  Obtiene todos los registros de la tabla paginados.
	*
	*  	@param $start	(int)	Inicio del rango de la p&aacute;gina de datos en la consulta SQL.
	*	@param $limit	(int)	L&iacute;mite del rango de la p&aacute;gina de datos en la consultal SQL.
	*	@param $order 	(string) Campo por el cual se aplicar&aacute; el orden en la consulta SQL.
	*	@return $res 	(object) Objeto que contiene el arreglo del resultado de la consulta SQL.
	*/
	function &getAllTicketRecords($start,$limit,$order=null,$Ctype,$Cid=0){
		global $db;
		$sql = "SELECT ticket_details.*,ticketcategory.ticketname as ticketcategoryname,tickets.ticketname as ticketname, customer,username FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto WHERE ";
		
		if($Ctype == 'agent_tickets') {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 AND ticket_details.assignto=$Cid AND ticket_details.status IN('new','panding')";
			}else{
				$sql .= " (username = '".$_SESSION['curuser']['username']."' OR (ticket_details.groupid='".$_SESSION['curuser']['groupid']."' AND ticket_details.assignto=0)) AND ticket_details.status IN('new','panding')";
			}
		} else {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 AND ticket_details.customerid=$Cid";
			}else{
				$sql .= " ticket_details.assignto IN (0,".$_SESSION['curuser']['accountid'].") AND ticket_details.customerid=$Cid";
			}
		}
		
		if($order == null){
			$sql .= " LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit";
		}
		
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	/**
	*  Obtiene todos registros de la tabla paginados y aplicando un filtro
	*
	*  @param $start		(int) 		Es el inicio de la p&aacute;gina de datos en la consulta SQL
	*	@param $limit		(int) 		Es el limite de los datos p&aacute;ginados en la consultal SQL.
	*	@param $filter		(string)	Nombre del campo para aplicar el filtro en la consulta SQL
	*	@param $content 	(string)	Contenido a filtrar en la conslta SQL.
	*	@param $order		(string) 	Campo por el cual se aplicar&aacute; el orden en la consulta SQL.
	*	@return $res		(object)	Objeto que contiene el arreglo del resultado de la consulta SQL.
	*/

	function &getTicketRecordsFilteredMore($start, $limit, $filter, $content, $order, $ordering = "",$Ctype,$Cid=0){
		global $db;		
		$joinstr = astercrm::createTicketSqlWithStype($filter,$content,$stype,'ticket_details');//<---- change by your function
		$sql = "SELECT ticket_details.*,ticketcategory.ticketname as ticketcategoryname,tickets.ticketname as ticketname, customer,username FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto WHERE ";
		
		if($Ctype == 'agent_tickets') {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 AND ticket_details.assignto=$Cid AND ticket_details.status IN('new','panding')";
			}else{
				$sql .= " (username = '".$_SESSION['curuser']['username']."' OR (ticket_details.groupid='".$_SESSION['curuser']['groupid']."' AND ticket_details.assignto=0)) AND ticket_details.status IN('new','panding')";
			}
		} else {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 AND ticket_details.customerid=$Cid";
			}else{
				$sql .= " ticket_details.assignto IN (0,".$_SESSION['curuser']['accountid'].") AND ticket_details.customerid=$Cid";
			}
		}
		
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	/**
	*  Devuelte el numero de registros de acuerdo a los par&aacute;metros del filtro
	*
	*	@param $filter	(string)	Nombre del campo para aplicar el filtro en la consulta SQL
	*	@param $order	(string)	Campo por el cual se aplicar&aacute; el orden en la consulta SQL.
	*	@return $row['numrows']	(int) 	N&uacute;mero de registros (l&iacute;neas)
	*/
	
	function &getTicketNumRows($filter = null, $content = null,$Ctype,$Cid=0){
		global $db;
		$sql = " SELECT COUNT(*) FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto WHERE 1";
		
		if($Ctype == 'agent_tickets') {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " AND ticket_details.assignto=$Cid AND ticket_details.status IN('new','panding')";
			} else {
				$sql .= " AND (username = '".$_SESSION['curuser']['username']."' OR (ticket_details.groupid = '".$_SESSION['curuser']['groupid']."' AND ticket_details.assignto=0)) AND ticket_details.status IN('new','panding')";
			}
		} else {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " AND ticket_details.customerid=$Cid";
			} else {
				$sql .= " AND ticket_details.assignto IN (0,".$_SESSION['curuser']['accountid'].") AND ticket_details.customerid=$Cid";
			}
		}
		
		astercrm::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getTicketNumRowsMore($filter = null, $content = null,$Ctype,$Cid=0){
		global $db;
		$joinstr = astercrm::createTicketSqlWithStype($filter,$content,$stype,'ticket_details');//<---- change by your function

		$sql = "SELECT COUNT(*) FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto WHERE ";
		
		if($Ctype == 'agent_tickets') {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 AND ticket_details.assignto=$Cid AND ticket_details.status IN('new','panding')";
			}else{
				$sql .= " (username='".$_SESSION['curuser']['username']."' OR (ticket_details.groupid='".$_SESSION['curuser']['groupid']."' AND ticket_details.assignto=0)) AND ticket_details.status IN('new','panding')";
			}
		} else {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 AND ticket_details.customerid=$Cid";
			}else{
				$sql .= " ticket_details.assignto IN (0,".$_SESSION['curuser']['accountid'].") AND ticket_details.customerid=$Cid";
			}
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr;
		}else {
			$sql .= " 1";
		}
		astercrm::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getTicketNumRowsMorewithstype($filter, $content,$stype,$table,$Ctype,$Cid=0){
		global $db;
		$joinstr = astercrm::createTicketSqlWithStype($filter,$content,$stype,'ticket_details');//<---- change by your function

		$sql = "SELECT COUNT(*) FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto WHERE ";
		
		if($Ctype == 'agent_tickets') {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1  AND ticket_details.assignto=$Cid AND ticket_details.status IN('new','panding')";
			}else{
				$sql .= " (username = '".$_SESSION['curuser']['username']."' OR (ticket_details.groupid = '".$_SESSION['curuser']['groupid']."' AND ticket_details.assignto=0)) AND ticket_details.status IN('new','panding')";
			}
		} else {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 AND ticket_details.customerid=$Cid";
			}else{
				$sql .= " ticket_details.assignto IN (0,".$_SESSION['curuser']['accountid'].") AND ticket_details.customerid=$Cid";
			}
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr;
		}else {
			$sql .= " 1";
		}
		astercrm::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getTicketRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table,$Ctype,$Cid=0){
		global $db;		

		$joinstr = astercrm::createTicketSqlWithStype($filter,$content,$stype,'ticket_details');//<---- change by your function

		$sql = "SELECT ticket_details.*,ticketcategory.ticketname as ticketcategoryname,tickets.ticketname as ticketname, customer,username FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto WHERE ";
		
		if($Ctype == 'agent_tickets') {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 AND ticket_details.assignto=$Cid AND ticket_details.status IN('new','panding')";
			}else{
				$sql .= " (username = '".$_SESSION['curuser']['username']."' OR (ticket_details.groupid='".$_SESSION['curuser']['groupid']."' AND ticket_details.assignto=0)) AND ticket_details.status IN('new','panding')";
			}
		} else {
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 AND ticket_details.customerid=$Cid";
			}else{
				$sql .= " ticket_details.assignto IN (0,".$_SESSION['curuser']['accountid'].") AND ticket_details.customerid=$Cid";
			}
		}
		
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}
	
	/**
	*  create a 'where string' with 'like,<,>,=' assign by stype
	*
	*	@param $stype		(array)		assign search type
	*	@param $filter		(array) 	filter in sql
	*	@param $content		(array)		content in sql
	*	@return $joinstr	(string)	sql where string
	*/
	function createTicketSqlWithStype($filter,$content,$stype=array(),$table='',$option='search'){
		$i=0;
		$joinstr='';
		foreach($stype as $type){
			$content[$i] = preg_replace("/'/","\\'",$content[$i]);
			if($filter[$i] != '' && trim($content[$i]) != ''){
				if($filter[$i] == 'ticketcategoryname') {
					$filter[$i] = 'ticketcategory.ticketname';
				} else if($filter[$i] == 'ticketname') {
					$filter[$i] = 'tickets.ticketname';
				}
				
				if($type == "equal"){
					$joinstr.="AND $filter[$i] = '".trim($content[$i])."' ";
				}elseif($type == "more"){
					$joinstr.="AND $filter[$i] > '".trim($content[$i])."' ";
				}elseif($type == "less"){
					$joinstr.="AND $filter[$i] < '".trim($content[$i])."' ";
				}else{
					$joinstr.="AND $filter[$i] like '%".trim($content[$i])."%' ";
				}
			}
			$i++;
		}
		return $joinstr;
	}


	/**
	*	get note code
	*/

	function &getAllNoteCodes(){
		global $db;
		$query = "SELECT * FROM codes";
		astercrm::events($query);
		$result =& $db->query($query);
		$array = array();
		while($result->fetchInto($row)) {
			$array[] = $row;
		}
		return $array;
	}

	/**
	*  insert a record to customer_leads table
	*
	*	@param $customerID			(int)		customer id fields.
	*	@param $customerLead		(varchar)	customer_leads
	*	@param $saveNote			boolean		if save note
	*	@return $customerid	(object) 	id number for the record just inserted.
	*/
	
	function insertNewCustomerLead($customerID,$customerLead,$saveNote){
		global $db,$config;
		$sql ="SELECT * FROM customer WHERE id=$customerID";
		$f =& $db->getRow($sql);
		
		$query= "INSERT INTO customer_leads SET "
			."customer='".addslashes($f['customer'])."', "
			."customertitle='".addslashes($f['customertitle'])."', "
			."website='".addslashes($f['website'])."', "
			."country='".addslashes($f['country'])."', "
			."address='".addslashes($f['address'])."', "
			."zipcode='".addslashes($f['zipcode'])."', "
			."city='".addslashes($f['city'])."', "
			."state='".addslashes($f['state'])."', "
			."contact='".addslashes($f['contact'])."', "
			."contactgender='".addslashes($f['contactgender'])."', "
			."phone='".$f['phone']."', "
			."phone_ext='".addslashes($f['phone_ext'])."', "
			."category='".$f['category']."', "
			."bankname='".addslashes($f['bankname'])."', "
			."bankzip='".addslashes($f['bankzip'])."', "
			."bankaccount='".addslashes($f['bankaccount'])."', "
			."bankaccountname='".addslashes($f['bankaccountname'])."', "
			."fax='".addslashes($f['fax'])."', "
			."fax_ext='".addslashes($f['fax_ext'])."', "
			."mobile='".$f['mobile']."', "
			."email='".addslashes($f['email'])."', "
			."cretime=now(), "
			."groupid = ".$f['groupid'].", "
			."last_note_id = 0, "
			."creby='".$f['creby']."'";
		$res =& $db->query($query);
		$customerid = mysql_insert_id();

		if($customerid) {
			if($saveNote) {
				$note_sql = "SELECT * FROM note WHERE id=".$f['last_note_id']." ";
				$noteResult = & $db->getRow($note_sql);
				if(!empty($noteResult)) {
					$noteSql = "INSERT INTO note_leads SET `note`='".addslashes($noteResult['note'])."',`callerid`='".addslashes($noteResult['callerid'])."',`priority`=".$noteResult['priority'].",`attitude`=".$noteResult['attitude'].",`cretime`=now(),`creby`='".$noteResult['creby']."',`customerid`=".$customerid.",`contactid`=0,`groupid`=".$noteResult['groupid'].",`codes`='".addslashes($noteResult['codes'])."',`private`=".$noteResult['private']." ";
					
					$note =& $db->query($noteSql);
					$last_note_id = mysql_insert_id();

					//更新customer_leads对应数据的last_note_id值
					$update_sql = "UPDATE customer_leads SET last_note_id=$last_note_id WHERE id=$customerid ";
					$res =& $db->query($update_sql);
				}
			}
		}
		
		if($customerLead == 'move' || $customerLead == 'default_move') {
			astercrm::deleteRecord($customerID,'customer');
			astercrm::deleteRecords("customerid",$customerID,'note');
			//astercrm::deleteRecords("customerid",$customerID,'contact');
			//$deleteSql = "DELETE FROM customer WHERE id=$customerID";
			//astercrm::events($deleteSql);
			//$res =& $db->query($deleteSql);
		}
		
		return $customerID;
	}

	function FormatSec($sec){
		$formateStr = '00:00:00';
		if($sec >= 86400) {
			$h = intval($sec/3600);
			$m = intval(($sec%3600)/60);
			$s = intval(($sec%3600)%60);
			if(strlen($h) == 1) $h = '0'.$h;
			if(strlen($m) == 1) $m = '0'.$m;
			if(strlen($s) == 1) $s = '0'.$s;
			$formateStr = $h.':'.$m.':'.$s;
		} else {
			$formateStr = gmstrftime("%H:%M:%S",$sec);
		}
		return $formateStr;
	}

	function updateAgentOnlineTime($type='',$time,$accountid){
		global $db;
		$sql = "UPDATE astercrm_account SET last_update_time='".$time."' ";
		if($type == 'login') {
			$sql .= ",last_login_time='".$time."' ";
		}
		$sql .= " WHERE id=$accountid";
		$result = & $db->query($sql);
		return $result;
	}

	function calculateAgentOntime($usrtype,$username){
		global $db,$config;
		$query = "SELECT * FROM astercrm_account WHERE username='$username' ;";
		$result = & $db->getRow($query);
		$last_login_time = $result['last_login_time'];
		$last_update_time = $result['last_update_time'];
		$identity = false;
		if($last_login_time != '0000-00-00 00:00:00') {
			//是否超过坐席在线更新时间
			$updateInterval = strtotime(date("Y-m-d H:i:s"))-strtotime($last_update_time);
			//如果当前时间跟last_update_time 的时间间隔 大于 系统的坐席更新时间，将会把时间更新到坐席在线时间表里
			
			$sql = "INSERT INTO agent_online_time SET username='$username',login_time='$last_login_time' ";
			if($usrtype == 'logout') {
				$sql .= ",logout_time='".date('Y-m-d H:i:s')."',onlinetime=".(strtotime(date("Y-m-d H:i:s"))-strtotime($last_login_time))." ";
			} else {
				if($updateInterval > ($config['system']['update_online_interval']*60)) {
					$sql .= ",logout_time='".$last_update_time."',onlinetime=".(strtotime($last_update_time)-strtotime($last_login_time))." ";
				}
			}
			$res = & $db->query($sql);
			if($res) {
				$empty_sql = "UPDATE astercrm_account SET last_login_time='0000-00-00 00:00:00',last_update_time='0000-00-00 00:00:00' WHERE username='$username'";
				$empty = & $db->query($empty_sql);
				$identity = true;
			}
		} else {
			$empty_sql = "UPDATE astercrm_account SET last_login_time='0000-00-00 00:00:00',last_update_time='0000-00-00 00:00:00' WHERE username='$username'";
			$empty = & $db->query($empty_sql);
			$identity = true;
		}
		return $identity;
	}


	function getCustomerNote($customerid){
		global $db;
		$sql = "SELECT * FROM note WHERE customerid = '".$customerid."' ";
		astercrm::events($sql);
		$result =& $db->query($sql);
		$highestNoteId = 0;
		$highestPrority = 0;
		$lastestNoteId = 0;
		$lastestTime = '';
		while($result->fetchInto($row)) {
			if($row['priority'] >= $highestPrority) {
				$highestPrority = $row['priority'];
				$highestNoteId = $row['id'];
			}
			if(strtotime($row['cretime']) >= strtotime($lastestTime)) {
				$lastestNoteId = $row['id'];
				$lastestTime = $row['cretime'];
			}
		}

		return $highestNoteId.'-'.$lastestNoteId;
	}

	function showNoteDetails($noteId){
		global $locate,$db;
		$sql = "SELECT note.*,contact.contact,customer.customer FROM note LEFT JOIN contact ON contact.id = note.contactid LEFT JOIN customer ON customer.id = note.customerid WHERE note.id='".$noteId."' ;";
		astercrm::events($sql);
		$result = & $db->getRow($sql);
		$html = '<table width="100%" border="0">
			<tr>
				<td>'.$locate->translate('note').'</td>
				<td>'.$result['note'].'</td>
			</tr>
			<tr>
				<td>'.$locate->translate('priority').'</td>
				<td>'.$result['priority'].'</td>
			</tr>
			<tr>
				<td>'.$locate->translate('codes').'</td>
				<td>'.$result['codes'].'</td>
			</tr>
			<tr>
				<td>'.$locate->translate('contact').'</td>
				<td>'.$result['contact'].'</td>
			</tr>
			<tr>
				<td>'.$locate->translate('customer_name').'</td>
				<td>'.$result['customer'].'</td>
			</tr>
			<tr>
				<td>'.$locate->translate('callerid').'</td>
				<td>'.$result['callerid'].'</td>
			</tr>
			<tr>
				<td>'.$locate->translate('create_time').'</td>
				<td>'.$result['cretime'].'</td>
			</tr>
			<tr>
				<td>'.$locate->translate('create_by').'</td>
				<td>'.$result['creby'].'</td>
			</tr>
		</table>';
		return $html;
	}

	function updateAddedSchedulerDial($customerid,$diallistId){
		global $db;
		$sql = "SELECT * FROM customer WHERE id='".$customerid."' ";
		astercrm::events($sql);
		$result = & $db->getRow($sql);
		
		$updateSql = "UPDATE diallist SET customerid='".$customerid."',customername='".$result['customer']."' WHERE id='".$diallistId."' ";
		astercrm::events($updateSql);
		$res = & $db->query($updateSql);
		return $res;
	}
}
?>
