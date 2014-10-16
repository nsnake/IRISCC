<?
/*******************************************************************************
* astercrm.php
* astercrm公用类
* astercrm class

* Public Functions List

			insertNewAccountgroup		向 accountgroup 表插入数据
			insertNewClid		向 clid 表插入数据
			insertNewNote			向note表插入数据
			insertNewSurveyResult	向surveyresult表插入数据
			insertNewAccount
			insertNewDiallist

			updateCustomerRecord	更新customer表数据
			updateContactRecord		更新contact表数据
			updateNoteRecord		更新note表数据
			updateAccountRecord
			updateaccountgroupRecord  更新accountaccountgroup表数据

			deleteRecord			从表中删除数据(以id作为标识)
			getRecord				从表中读取数据(以id作为标识)
			updateField				更新表中的数据(以id作为标识)

			events					日志记录
			checkValues				根据条件从数据库中检索是否有符合条件的记录
			showNoteList			生成note列表的HTML文件
			getCustomerByID			根据customerid获取customer记录信息或者根据noteid获取与之相关的customer信息
			getContactByID			根据contactid获取contact记录信息或者根据noteid获取与之相关的contact信息
			getContactListByID		根据customerid获取与之邦定的contact记录
			getRecordByID			根据id获取记录
			surveyAdd				生成添加survey的HTML语法
			noteAdd					生成添加note的HTML语法
			formAdd					生成添加综合信息(包括customer, contact, survey, note)的HTML语法
			formEdit				生成综合信息编辑的HTML语法, 
									包括编辑customer, contact以及添加note
			getaccountgroupList				读取所有的accountgroup信息

			showCustomerRecord		生成显示customer信息的HTML语法
			showContactRecord		生成显示contact信息的HTML语法

			exportCSV				生成csv文件内容, 目前支持导出customer, contact
			createSqlWithStype
			generateUniquePin				生成clid的pin number
			getCustomerByCallerid	根据callerid查找customer表看是否有匹配的id

			variableFiler			用于转译变量, 自动加\
			新增exportDataToCSV     得到要导出的sql语句的结果集，转换为符合csv格式的文本字符串
			新增getSql              得到多条件搜索的sql语句
			新增getaccountgroupMemberListByID 得到组成员 
			新增deletefromsearch	从指定表中删除符合搜索条件的行
			
* Private Functions List
			generateSurvey			生成添加survey的HTML语法
			getCalleridListByID			根据customerid或者contactid获取与之邦定的note记录



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
	function &getCalleridListByID($id){
		global $db;
		
		$sql = "SELECT * FROM clid WHERE groupid = $id ORDER BY clid ASC";

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function getAllExtension(){
		global $db;
		$query = "select extension from account";
		$res =& $db->query($query);
		return  $res;
	}

	function getRecordByField($field,$value,$table){
		global $db;
		if (is_numeric($value)){
			$query = "SELECT * FROM $table WHERE $field = $value LIMIT 0,1";
		}else{
			$query = "SELECT * FROM $table WHERE $field = '$value'  LIMIT 0,1";
		}
		astercrm::events($query);
		$row =& $db->getRow($query);
		return $row;
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



	/**
	*  insert a record to contact table
	*
	*	@param $f			(array)		array contain contact fields.
	*	@param $customerid	(array)		customer id of the new contact
	*	@return $customerid	(object) 	id number for the record just inserted.
	*/
	
	function insertNewClid($f){
		global $db,$config;
		$f = astercrm::variableFiler($f);
		
		$sql= "INSERT INTO clid SET "
				."clid='".$f['clid']."', "
				."accountcode='".$f['accountcode']."', "
				."pin='".$f['pin']."', "
				."display='".$f['display']."', "
				."groupid = ".$f['groupid'].", "
				."resellerid = ".$f['resellerid'].", "
				."isshow = '".$f['isshow']."', "
				."status = ".$f['status'].", 
				 creditlimit = '".$f['creditlimit']."',
				 limittype = '".$f['limittype']."',"
				."addtime = now() ";

		if($config['synchronize']['id_autocrement_byset']){
			$sql .= ",id='".$f['id']."' ";
		}
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
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
		//print_r($f);
		$sql= "INSERT INTO note SET "
				."note='".$f['note']."', "
				."attitude='".$f['attitude']."', "
				."priority=".$f['priority'].", "
				."cretime=now(), "
				."creby='".$_SESSION['curuser']['username']."', "
				."customerid=". $customerid . ", "
				."contactid=". $contactid ;
		//print $sql;
		//exit;
		astercrm::events($sql);

		$res =& $db->query($sql);
		return $res;
	}

	/**
	*  Inserta un nuevo registro en la tabla.
	*
	*	@param $f	(array)		Arreglo que contiene los datos del formulario pasado.
	*	@return $res	(object) 	Devuelve el objeto con la respuesta de la sentencia SQL ejecutada del INSERT.

	*/
	
	function insertNewAccount($f){
		global $db,$config;
		$f = astercrm::variableFiler($f);

		if ($_SESSION['curuser']['usertype'] == 'reseller'){
			$f['resellerid'] = $_SESSION['curuser']['resellerid'];
			//$f['groupid'] = $_SESSION['curuser']['groupid'];
		}

		if ($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$f['resellerid'] = $_SESSION['curuser']['resellerid'];
			$f['groupid'] = $_SESSION['curuser']['groupid'];
		}

		if ($f['usertype'] == 'admin'){
			$f['resellerid'] = 0;
			$f['groupid'] = 0;
		}elseif ($f['usertype'] == 'reseller'){
			$f['groupid'] = 0;
		}

		$sql= "INSERT INTO account SET "
				."username='".$f['username']."', "
				."password='".$f['password']."', "
				."usertype='".$f['usertype']."',"
				."groupid='".$f['groupid']."', "
				."resellerid='".$f['resellerid']."', "
				."addtime= now() ";
		
		if($config['synchronize']['id_autocrement_byset']){
			$sql .= ",id='".$f['id']."' ";
		}

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}


	/**
	*  Insert a account log
	*
	*	@param $f			(array)		array contain note fields.
	*	@return $res	(object) 		object
	*/
	
	function insertAccountLog($f){
		global $db;
		$f = astercrm::variableFiler($f);
		//print_r($f);
		$sql = "UPDATE account_log SET failedtimes='".$f['failedtimes']."' WHERE ip='".$f['ip']."'";
		astercrm::events($sql);
		$res =& $db->query($sql);

		$sql= "INSERT INTO account_log SET "
				."account_id='".$f['account_id']."', "
				."username='".$f['username']."', "
				."usertype='".$f['usertype']."', "
				."ip='".$f['ip']."', "
				."action='".$f['action']."', "
				."status='".$f['status']."', "
				."failedcause='".$f['failedcause']."', "
				."failedtimes='".$f['failedtimes']."', "
				."cretime=now()" ;
		//print $sql;
		//exit;
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function insertNewDiallist($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$sql= "INSERT INTO diallist SET "
				."dialnumber='".$f['dialnumber']."', "
				."groupid='".$f['groupid']."', "
				."campaignid='".$f['campaignid']."', "
				."assign='".$f['assign']."'";

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}




	/**
	*  update clid table
	*
	*	@param $f			(array)		array contain contact fields.
	*	@return $res		(object)	object
	*/
	
	function updateClidRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		if ( $f['creditmodtype'] == '' ){
			$newcurcredit = $f['curcredit'];
		}elseif ( $f['creditmodtype'] == 'add' ){
			$newcurcredit = $f['curcredit'] + $f['creditmod'];
			$historysql = "INSERT INTO credithistory SET "
							."modifytime= now(), "
							."resellerid='".$f['resellerid']."', "
							."groupid='".$f['groupid']."', 
							 clidid='".$f['id']."',"
							."srccredit='".$f['curcredit']."', "
							."modifystatus= 'add', "
							."modifyamount='".$f['creditmod']."', "
							."comment='".$f['comment']."', "
							."operator='".$_SESSION['curuser']['userid']."'";
			$historyres =& $db->query($historysql);
		}elseif ( $f['creditmodtype'] == 'reduce' ){
			$newcurcredit = $f['curcredit'] - $f['creditmod'];
			$historysql = "INSERT INTO credithistory SET "
							."modifytime= now(), "
							."resellerid='".$f['resellerid']."', "
							."groupid='".$f['groupid']."', "
							."clidid='".$f['id']."',"
							."srccredit='".$f['curcredit']."', "
							."modifystatus= 'reduce', "
							."modifyamount='".$f['creditmod']."', "
							."comment='".$f['comment']."', "
							."operator='".$_SESSION['curuser']['userid']."'";
			$historyres =& $db->query($historysql);
		}
		$sql= "UPDATE clid SET "
				."clid='".$f['clid']."', "
				."accountcode='".$f['accountcode']."', "
				."pin='".$f['pin']."', "
				."display='".$f['display']."', "
				."groupid='".$f['groupid']."', "
				."resellerid='".$f['resellerid']."', "
				."creditlimit = '".$f['creditlimit']."',"
				."curcredit = '".$newcurcredit."',"
				."limittype = '".$f['limittype']."',"
				."status= ".$f['status'].", "
				."isshow= '".$f['isshow']."', "
				."addtime = now() "
				."WHERE id='".$f['id']."'";
		astercrm::events($sql);
		$res =& $db->query($sql);
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

			$sql= "UPDATE note SET "
					."note='".$f['note']."', "
					."priority=".$f['priority']." ,"
					."attitude='".$f['attitude']."' "
					."WHERE id='".$f['noteid']."'";
		else
			if (empty($f['note']))
				$sql= "UPDATE note SET "
						."attitude='".$f['attitude']."', "
						."priority=".$f['priority']." "
						."WHERE id='".$f['noteid']."'";
			else
				$sql= "UPDATE note SET "
						."note=CONCAT(note,'<br>',now(),'  ".$f['note']." by " .$_SESSION['curuser']['username']. "'), "
						."attitude='".$f['attitude']."', "
						."priority=".$f['priority']." "
						."WHERE id='".$f['noteid']."'";

		astercrm::events($sql);
		$res =& $db->query($sql);
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

		if ($_SESSION['curuser']['usertype'] == 'reseller'){
			$f['resellerid'] = $_SESSION['curuser']['resellerid'];
		}

		if ($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$f['resellerid'] = $_SESSION['curuser']['resellerid'];
			$f['groupid'] = $_SESSION['curuser']['groupid'];
		}

		if ($f['usertype'] == 'admin'){
			$f['resellerid'] = 0;
			$f['groupid'] = 0;
		}elseif ($f['usertype'] == 'reseller'){
			$f['groupid'] = 0;
		}

		$sql= "UPDATE account SET "
				."username='".$f['username']."', "
				."password='".$f['password']."', "
				."usertype='".$f['usertype']."', "
				."groupid='".$f['groupid']."', "
				."resellerid='".$f['resellerid']."', "
				."addtime= now() "
				."WHERE id='".$f['id']."'";
		astercrm::events($sql);
		$res =& $db->query($sql);
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
		
		$sql = "SELECT * FROM $table WHERE id = $id";
		astercrm::events($sql);
		$row =& $db->getRow($sql);
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

	function updateField($table,$field,$value,$id,$fld = "id"){

		global $db;
		$f = astercrm::variableFiler($f);

		$sql = "UPDATE $table SET $field='$value' WHERE $fld = '$id'";

		astercrm::events($sql);
		$res =& $db->query($sql);
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

	function checkRateDuplicate($tblName,$f,$action){
		global $db;
		if ($tblName == 'callshoprate' || $tblName == 'myrate'){
			if ($action == 'insert'){
				$query = "SELECT id FROM $tblName WHERE dialprefix = '".$f['dialprefix']."' AND numlen = ".$f['numlen']." AND resellerid = ".$f['resellerid']." AND groupid =".$f['groupid'];
			}else{
				$query = "SELECT id FROM $tblName WHERE dialprefix = '".$f['dialprefix']."' AND numlen = ".$f['numlen']." AND resellerid = ".$f['resellerid']." AND groupid =".$f['groupid']." AND id !=".$f['id'];
			}
		}elseif($tblName == 'resellerrate'){
			if ($action == 'insert'){
				$query = "SELECT id FROM $tblName WHERE dialprefix = '".$f['dialprefix']."' AND numlen = ".$f['numlen']." AND resellerid =".$f['resellerid'];
			}else{
				$query = "SELECT id FROM $tblName WHERE dialprefix = '".$f['dialprefix']."' AND numlen = ".$f['numlen']." AND resellerid =".$f['resellerid']." AND id !=".$f['id'];
			}
		}
		astercrm::events($query);
		$id =& $db->getOne($query);
		return $id;		
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
			$sql = "SELECT id FROM $tblName WHERE $fldName='$myValue'";
		else
			$sql = "SELECT id FROM $tblName WHERE $fldName=$myValue";
		
		if ($fldName1 != null)
			if ($type1 == "string")
				$sql .= "AND $fldName1='$myValue1'";
			else
				$sql .= "AND $fldName1=$myValue1";

		
		astercrm::events($sql);
		$id =& $db->getOne($sql);
		return $id;		
	}

	function checkValuesNon($id,$tblName,$fldName,$myValue,$type="string",$fldName1 = null,$myValue1 = null,$type1 = "string"){

		global $db;

		if ($type == "string")
			$sql = "SELECT id FROM $tblName WHERE id != $id AND $fldName='$myValue'";
		else
			$sql = "SELECT id FROM $tblName WHERE id != $id  $fldName=$myValue";
		
		if ($fldName1 != null)
			if ($type1 == "string")
				$sql .= "AND $fldName1='$myValue1'";
			else
				$sql .= "AND $fldName1=$myValue1";

		
		astercrm::events($sql);
		$id =& $db->getOne($sql);
		return $id;		
	}

	function &getaccountgroupMemberListByID($groupid){
		global $db;
		$sql = "SELECT id,username FROM account WHERE groupid =$groupid";
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	/**
	*  Devuelte el registro de acuerdo al $id pasado.
	*
	*	@param $id	(int)	Identificador del registro para hacer la b&uacute;squeda en la consulta SQL.
	*	@return $row	(array)	Arreglo que contiene los datos del registro resultante de la consulta SQL.
	*/
	
	function &getRecordByID($id,$table){
		global $db;
		
		$sql = "SELECT * FROM $table "
				." WHERE id = $id";
		astercrm::events($sql);
		$row =& $db->getRow($sql);
		return $row;
	}


	function getGroupList(){

		global $db;
		
		$sql= "SELECT * FROM accountgroup ";
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
	*  Muestra todos los datos de un registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser mostrado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene una tabla con los datos 
	*									a extraidos de la base de datos para ser mostrados 
	*/
	function showCustomerRecord($id,$type="customer"){
    	global $locate;
		$customer =& astercrm::getCustomerByID($id,$type);
		$contactList =& astercrm::getContactListByID($customer['id']);
		$html = '
				<table border="0" width="100%">
				<tr>
					<td nowrap align="left" width="160">'.$locate->Translate("customer_name").'&nbsp;[<a href=? onclick="xajax_showNote(\''.$customer['id'].'\',\'customer\');return false;">'.$locate->Translate("note").'</a>]</td>
					<td align="left">'.$customer['customer'].'&nbsp;[<a href=? onclick="xajax_edit(\''.$customer['id'].'\',\'customer\');return false;">'.$locate->Translate("edit").'</a>]&nbsp; [<a href=? onclick="
							if (xajax.$(\'hidCustomerBankDetails\').value == \'OFF\'){
								showObj(\'trCustomerBankDetails\');
								xajax.$(\'hidCustomerBankDetails\').value = \'ON\';
							}else{
								hideObj(\'trCustomerBankDetails\');
								xajax.$(\'hidCustomerBankDetails\').value = \'OFF\';
							}
							return false;">'.$locate->Translate("bank").'</a>]<input type="hidden" value="OFF" name="hidCustomerBankDetails" id="hidCustomerBankDetails"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("city").'/'.$locate->Translate("state").'['.$locate->Translate("zipcode").']'.'</td>
					<td align="left">'.$customer['city'].'/'.$customer['state'].'['.$customer['zipcode'].']'.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("address").'</td>
					<td align="left">'.$customer['address'].'</td>
				</tr>
				<!--**********************-->
				<tr>
					<td nowrap align="left">'.$locate->Translate("mobile").'</td>
					<td align="left"><a href=? onclick="xajax_dial(\''.$customer['mobile'].'\');return false;">'.$customer['mobile'].'</a></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("fax").'</td>
					<td align="left"><a href=? onclick="xajax_dial(\''.$customer['fax'].'\');return false;">'.$customer['fax'].'</a></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("email").'</td>
					<td align="left">'.$customer['email'].'</td>
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
					<td align="left"><a href=? onclick="xajax_dial(\''.$customer['phone'].'\');return false;">'.$customer['phone'].'</a></td>
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
							<td>
							<a href="?" onclick="xajax_surveyAdd(\''.$customer['id'].'\',0);return false;">'.$locate->Translate("add_survey").'</a>
							</td>					<input type="hidden" id="allContact" name="allContact" value="off">
							</tr>
						</table>
					</td>
				</tr>
				</table>
				<table border="0" width="100%" id="contactList" name="contactList" style="display:none">
					';

				while	($contactList->fetchInto($row)){
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

				$html .= '
					</table>';

		return $html;

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
		$sql = "DELETE FROM $table WHERE id = $id";
		astercrm::events($sql);
		$res =& $db->query($sql);

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
	*  export datas to csv format
	*
	*	@param $type		(string)		data to be exported
	*	@return $txtstr		(string) 		csv format datas
	*/

	function exportCSV($type = 'customer'){
		global $db;

		if ($type == 'customer')
			$sql = 'SELECT * FROM customer';
		elseif ($type == 'contact')
			$sql = 'SELECT contact.*,customer.customer FROM contact LEFT JOIN customer ON customer.id = contact.customerid';
		else
			$sql = 'SELECT contact.contact,customer.customer,note.* FROM note LEFT JOIN customer ON customer.id = note.customerid LEFT JOIN contact ON contact.id = note.contactid';

		astercrm::events($sql);
		$res =& $db->query($sql);
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

	/**
	*  create a 'where string' with 'like,<,>,=' assign by stype 
	*
	*	@param $stype		(array)		assign search type
	*	@param $filter		(array) 	filter in sql
	*	@param $content		(array)		content in sql
	*	@return $joinstr	(string)	sql where string
	*/
	function createSqlWithStype($filter,$content,$stype,$table){

		$i=0;
		$joinstr='';
		foreach($stype as $type){
			//echo $filter[$i];exit;
			$content[$i] = preg_replace("/'/","\\'",$content[$i]);
			if($filter[$i] != '' && trim($content[$i]) != ''){

				if($filter[$i] == 'groupname' and $table != "accountgroup" and $table != ""){
					$group_res = astercrm::getFieldsByField('id','groupname',$content[$i],'accountgroup',$type);
					
					while ($group_res->fetchInto($group_row)){
						$group_str.="OR $table.groupid = '".$group_row['id']."' ";					
					}
					
					if($group_str == ''){
						$group_str.=" $table.groupid = '-1' ";
					}
				}elseif($filter[$i] == 'resellername' and $table != "resellergroup" and $table != ""){
					$reseller_res = astercrm::getFieldsByField('id','resellername',$content[$i],'resellergroup',$type);
					
					while ($reseller_res->fetchInto($reseller_row)){
						$reseller_str.="OR $table.resellerid = '".$reseller_row['id']."' ";
					}

					if($reseller_str == ''){
						$reseller_str.=" $table.resellerid = '-1' ";
					}
				}else{
				
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

		if($reseller_str != '' ){
			$reseller_str = ltrim($reseller_str,'OR');
			$joinstr.= "AND (".$reseller_str.")";
		}
		//echo $joinstr;exit;
		return $joinstr;
	}

	/**
	* generate a unique pin number, can be assign length by $len 
	*
	*	@param $len		(int)		pin length
	*	@return $pin	(string)	pin number
	*/

	function generateUniquePin($len=10) {
		
		srand((double)microtime()*1000003);
		$prefix = rand(1000000000,9999999999);
		if(is_numeric($len) && $len > 10 && $len < 20 ){
			$len -= 10;
			$min = 1;
			for($i=1; $i < $len; $i++){
			$min = $min*10;
			}
			$max = ($min*10) - 1;
			$pin = $prefix.rand($min,$max);
			$curpin = astercrm::getRecordByField('pin',$pin,'clid');
			while($curpin){
				$pin = $prefix.rand($min,$max);
				$curpin = astercrm::getRecordByField('pin',$pin,'clid');
			}			
		}elseif($len <= 10){
			$pin = $prefix;
			$curpin = astercrm::getRecordByField('pin',$pin,'clid');
			while($curpin){
				$pin = rand(1000000000,9999999999);
				$curpin = astercrm::getRecordByField('pin',$pin,'clid');
			}
		}else{
			$pin = $prefix.rand(1000000000,9999999999);
			$curpin = astercrm::getRecordByField('pin',$pin,'clid');
			while($curpin){
				$pin = $prefix.rand(1000000000,9999999999);
				$curpin = astercrm::getRecordByField('pin',$pin,'clid');
			}			
		}		
		return $pin;
	}

	function exportDataToCSV($sql){
		global $db;
		
		//require_once ("include/xajax.inc.php");
		//$locate=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],$table);

		astercrm::events($sql);
		$res =& $db->query($sql);
		
		$fieldArray = array();//table field name which wants to export to the csv
		while ($res->fetchInto($row)) {
			foreach ($row as $key=>$val){
				if(!in_array($key,$fieldArray)){
					$fieldArray[] = $key;//$locate->Translate($key);
				}
				
				if ($val != mb_convert_encoding($val,"UTF-8","UTF-8"))
						$val='"'.mb_convert_encoding($val,"UTF-8","GB2312").'"';
				
				$txtstr .= '"'.$val.'"'.',';
			}
			$txtstr .= "\n";
		}
		$fieldStr = '"'.implode('","',$fieldArray)."\"\n";
		
		return $fieldStr.$txtstr;
	}

	/**
	*  return customerid if match a phonenumber
	*
	*	@param $type		(string)		data to be exported
	*	@return $txtstr		(string) 		csv format datas
	*/

	function getCustomerByCallerid($callerid){
		global $db;
		$sql = "SELECT id FROM customer WHERE phone LIKE '%$callerid'";
		$customerid =& $db->getOne($sql);
		astercrm::events($sql);
		return $customerid;
	}

	/**
	*  新增一个参数 $field，标识要查询的字段,如果为 * 表示查询全部, 传递值的形式是以 , 分割的字符串
	*/
	function getSql($searchContent,$searchField,$searchType,$table,$fieldStr='*'){
		global $db;

		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType);
		
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND');
			$sql = 'SELECT '.$fieldStr.' FROM '.$table.' WHERE '.$joinstr;
		}else {
			$sql = 'SELECT '.$fieldStr.' FROM '.$table.'';
		}
		//if ($sql != mb_convert_encoding($sql,"UTF-8","UTF-8")){
		//	$sql='"'.mb_convert_encoding($sql,"UTF-8","GB2312").'"';
		//}		
		return $sql;
	}

	function deletefromsearch($searchContent,$searchField,$searchType="",$table){
		global $db;
		if(empty($_SESSION['curuser']['usertype'])){
			return;
		}
		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,$table);

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND');
			$sql = 'DELETE FROM '.$table.' WHERE '.$joinstr;
		}else{
			if($_SESSION['curuser']['usertype'] == 'admin'){
				#echo 'cccccccccc';exit;
				$sql = 'TRUNCATE '.$table;
			}else{
				#echo 'ggggggggggggg';exit;
				$sql = "DELETE FROM ".$table." WHERE ".$table.".groupid = '".$_SESSION['curuser']['groupid']."'";
			}
		}
		//echo $sql;exit;
		Customer::events($sql);
		$res =& $db->query($sql);

		return $res;
	}

	function formMutiEdit($searchContent,$searchField,$searchType,$table){
		global $locate;
		
		$reselleroptions = '';
		$reseller = astercrm::getAll('resellergroup');

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$reselleroptions .= '<select id="resellerid" name="resellerid"'; 
			if($table != "resellerrate") $reselleroptions .= 'onchange="setGroup();"';
			$reselleroptions .= '><option value=""></option>';
			$reselleroptions .= '<option value="0">'.$locate->Translate("All").'</option>';
			while	($reseller->fetchInto($row)){
				$reselleroptions .= "<OPTION value='".$row['id']."'>".$row['resellername']."</OPTION>";
			}
			$reselleroptions .= '</select>';
		}else{
			while	($reseller->fetchInto($row)){
				if ($row['id'] == $_SESSION['curuser']['resellerid']){
					$reselleroptions .= $row['resellername'].'<input type="hidden" value="'.$row['id'].'" name="resellerid" id="resellerid">';
					break;
				}
			}
		}
		if($table != "resellerrate"){
			$group = astercrm::getAll('accountgroup','resellerid',$_SESSION['curuser']['resellerid']);
			if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
				$groupoptions .= '<select id="groupid" name="groupid">';
				if( $_SESSION['curuser']['usertype'] == 'reseller')	$groupoptions .= "<OPTION value=''></OPTION>";
				$groupoptions .= "<OPTION value='0'>".$locate->Translate("All")."</OPTION>";
				while	($group->fetchInto($row)){
					$groupoptions .= "<OPTION value='".$row['id']."'>".$row['groupname']."</OPTION>";
				}
				$groupoptions .= '</select>';
			}else{
				while	($group->fetchInto($row)){
					if ($row['id'] == $_SESSION['curuser']['groupid']){
						$groupoptions .= $row['groupname'].'<input type="hidden" value="'.$row['id'].'" name="groupid" id="groupid">';
						break;
					}
				}
			}
		}		
		//可修改字段
		$tableField = astercrm::getTableStructure('myrate');

		foreach($tableField as $row ){
			if($row['name'] != 'id' && $row['name'] != 'resellerid' && $row['name'] != 'groupid' && $row['name'] != 'addtime'){
				$fieldOption .= '<option value="'.$row['name'].','.$row['type'].'">'.$row['name'].'</option>'; 
			}
		}

		//将条件重置成字符串，通过post传递
		$i = 0;
		foreach($searchContent as $content){
			if(trim($content) != '' && trim($searchField[$i]) != ''){
				$searchContentStr .= $content.",";
				$searchFieldStr .= $searchField[$i].",";
				$searchTypeStr .= $searchType[$i].",";
			}
			$i++;
		}
		//echo $searchContentStr.$searchFieldStr.$searchTypeStr;exit;
//print_r($searchField);exit;


		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Option").'</td>
					<td align="left"><input type="radio" name="multioption" id="multioption" value="modify" checked>&nbsp;'.$locate->Translate("Modify").'&nbsp;&nbsp;<input type="radio" name="multioption" id="multioption" value="duplicate">&nbsp;'.$locate->Translate("Duplicate").'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Change").'</td>
					<td align="left"><select id="multieditField" name="multieditField" onchange=\'xajax_setMultieditType(this.value)\'>'.$fieldOption.'</select>&nbsp;&nbsp;
					<select id="multieditType" name="multieditType" >
						<option value="to">'.$locate->Translate("to").'</option>
					</select>&nbsp;&nbsp;<input type="text" id="multieditcontent" name="multieditcontent" size="15"></td>
				</tr>
				
				<tr>
					<td nowrap align="left">'.$locate->Translate("Reseller").'</td>
					<td align="left">'
						.$reselleroptions.
					'</td>
				</tr>';

				if($table != "resellerrate"){
					$html .= '<tr><td nowrap align="left">'.$locate->Translate("Group").'</td><td align="left">'.$groupoptions.'</td></tr>';
				}

				$html .= '<tr>
					<td colspan="2" align="center">
						<button id="submitButton" onClick=\'xajax_multiEditUpdate("'.$searchContentStr.'","'.$searchFieldStr.'","'.$searchTypeStr.'","'.$table.'",xajax.getFormValues("f"));return false;\'>'.$locate->Translate("Continue").'</button>
					</td>
				</tr>

			 </table>
			';

		$html .='
			</form>';
		//echo $html;exit;
		return $html;
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

	/**
	*	get the last id which id in the setting id array 
	*
	*	@param  $tablename the table name
	*/
	function getLocalLastId($tablename){
		global $db,$config;
		$local_minId = $config['local_host']['minId'];
		$local_maxId = $config['local_host']['maxId'];

		$sql = "SELECT id FROM ".$tablename." WHERE id between ".$local_minId." AND ".$local_maxId." ORDER BY id DESC limit 1 ";
		astercrm::events($sql);
		$res =& $db->getRow($sql);
		return $res['id'];
	}

	/**
	*  delete a record form a table into that's history table
	*
	*	@param  $id			(int)		identity of the record
	*	@param  $table		(string)	table name
	*	@return $res		(object)	object
	*/
	function deleteRecordToHistory($field,$value,$table){
		global $db;
		
		//backup all datas
		$history_sql = "INSERT INTO ".$table."_history SELECT * FROM ".$table." WHERE ".$field."='".$value."' ";
		astercrm::events($history_sql);
		$history_res =& $db->query($history_sql);
		if($history_res){
			//delete all note
			$sql = "DELETE FROM $table WHERE ".$field." = '".$value."'";
			astercrm::events($sql);
			$res =& $db->query($sql);

			return $res;
		} else {
			return false;
		}
	}

	function deleteToHistoryFromSearch($searchContent,$searchField,$searchType="",$table){
		global $db,$config;
		if(empty($_SESSION['curuser']['usertype'])){
			return;
		}
		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,$table);

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND');

			$history_sql = "INSERT INTO ".$table."_history SELECT * FROM ".$table." WHERE ".$joinstr;
			
			$sql = 'DELETE FROM '.$table.' WHERE '.$joinstr;
		}else{
			if($_SESSION['curuser']['usertype'] == 'admin'){
				$sql = 'TRUNCATE '.$table;

				$history_sql = "INSERT INTO ".$table."_history SELECT * FROM ".$table." ";
			}else{
				$sql = "DELETE FROM ".$table." WHERE ".$table.".groupid = '".$_SESSION['curuser']['groupid']."'";

				$history_sql = "INSERT INTO ".$table."_history SELECT * FROM ".$table." WHERE ".$table.".groupid = '".$_SESSION['curuser']['groupid']."' ";
			}
		}
		
		Customer::events($history_sql);
		$result =& $db->query($history_sql);
		if($result) {
			Customer::events($sql);
			$res =& $db->query($sql);
			return $res;
		} else {
			return false;
		}
	}

	/**
	*  the data belongs to the server judge by id ,then show the field with the server ip
	*
	*	@param  $id			(int)		id of the record
	*	@param  $field		(string)	table field name
	*	@return $field		(string)	table field name with the server ip
	*/
	function getSynchronDisplay($id,$field){
		global $config,$locate;
		
		$otherHost = $config['synchronize_host']['Host'];
		$hostArray = explode(',',trim($otherHost,','));

		$existFlag = false;
		foreach($hostArray as $tmp){
			if($id >= $config['synchronize_host'][$tmp.'_minId'] && $id <= $config['synchronize_host'][$tmp.'_maxId']){
				$field = $field.' ('.$config['synchronize_host'][$tmp].')';
				$existFlag = true;
			}
		}
		if(!$existFlag){
			$field = $field.' ('.$locate->Translate("Local").')';
		}

		return $field;
	}
}
?>