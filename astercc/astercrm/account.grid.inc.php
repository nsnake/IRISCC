<?php
/*******************************************************************************
* account.grid.inc.php
* account操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加account表单的HTML
	formEdit					生成编辑account表单的HTML
	新增 getRecordsFilteredMore  用于获得多条件搜索记录集
	新增 getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.0456  2007/10/30 13:15:00  last modified by solo
* Desc: add channel field 

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'account.common.php';
require_once 'include/astercrm.class.php';


class Customer extends astercrm
{

	/**
	*  Obtiene todos los registros de la tabla paginados.
	*
	*  	@param $start	(int)	Inicio del rango de la p&aacute;gina de datos en la consulta SQL.
	*	@param $limit	(int)	L&iacute;mite del rango de la p&aacute;gina de datos en la consultal SQL.
	*	@param $order 	(string) Campo por el cual se aplicar&aacute; el orden en la consulta SQL.
	*	@return $res 	(object) Objeto que contiene el arreglo del resultado de la consulta SQL.
	*/
	function &getAllRecords($start, $limit, $order = null, $groupid = null){
		global $db;
		
		$sql = "SELECT astercrm_account.*, groupname  FROM astercrm_account LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = astercrm_account.groupid";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." ";
		}
			
		if($order == null){
			$sql .= " LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit";
		}

		Customer::events($sql);
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

	function &getRecordsFilteredMore($start, $limit, $filter, $content, $order, $ordering = ""){
		global $db;		
		$i=0;
		$joinstr='';
		foreach ($content as $value){
			$value = preg_replace("/'/","\\'",$value);
			$value=trim($value);
			if (strlen($value)!=0 && strlen($filter[$i]) != 0){
				$joinstr.="AND $filter[$i] like '%".$value."%' ";
			}
			$i++;
		}

		$sql = "SELECT astercrm_account.*, groupname FROM astercrm_account LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = astercrm_account.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}

		Customer::events($sql);
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
	
	function &getNumRows($filter = null, $content = null){
		global $db;
		
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = " SELECT COUNT(*) FROM astercrm_account LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = astercrm_account.groupid";
		}else{
			$sql = " SELECT COUNT(*) FROM astercrm_account LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = astercrm_account.groupid WHERE astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." ";
		}
		
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getNumRowsMore($filter = null, $content = null){
		global $db;
		
			$i=0;
			$joinstr='';
			foreach ($content as $value){
				$value = preg_replace("/'/","\\'",$value);
				$value=trim($value);
				if (strlen($value)!=0 && strlen($filter[$i]) != 0){
					$joinstr.="AND $filter[$i] like '%".$value."%' ";
				}
				$i++;
			}

			$sql = "SELECT COUNT(*) FROM astercrm_account LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = astercrm_account.groupid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." AND ";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " ".$joinstr;
			}else {
				$sql .= " 1";
			}
		Customer::events($sql);
		$res =& $db->getOne($sql);
//		print $sql;
//		print "\n";
//		print $res;
//		exit;
		return $res;
	}

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'astercrm_account');

			$sql = "SELECT COUNT(*) FROM astercrm_account LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = astercrm_account.groupid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." AND ";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " ".$joinstr;
			}else {
				$sql .= " 1";
			}
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;		

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'astercrm_account');

		$sql = "SELECT astercrm_account.*, groupname FROM astercrm_account LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = astercrm_account.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}

		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function insertNewAccountForBilling($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$sql= "INSERT INTO clid SET "
				."clid='".$f['extension']."', "
				."pin='".$f['password']."', "
				."display='".$f['username']."', "
				."groupid = ".$f['groupid'].", "
				."resellerid = ".$f['resellerid'].", "
				."creditlimit = '".$f['creditlimit']."',"
				."limittype = '".$f['limittype']."',"
				."addtime = now() ";

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	/**
	*  Imprime la forma para agregar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param ninguno
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma para insertar 
	*							un nuevo registro.
	*/
	
	function formAdd(){
			global $locate;

		if ($_SESSION['curuser']['usertype'] == 'admin'){
				$res = Customer::getGroups();
				$groupoptions .= '<select name="groupid" id="groupid">';
				while ($row = $res->fetchRow()) {
						$groupoptions .= '<option value="'.$row['groupid'].'"';
						$groupoptions .='>'.$row['groupname'].'</option>';
				}
				$groupoptions .= '</select>';
		}else{
				$groupoptions .= $_SESSION['curuser']['group']['groupname'].'<input id="groupid" name="groupid" type="hidden" value="'.$_SESSION['curuser']['groupid'].'">';
		}
	$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("username").'*</td>
					<td align="left"><input type="text" id="username" name="username" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("password").'*</td>
					<td align="left"><input type="text" id="password" name="password" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("first name").'*</td>
					<td align="left"><input type="text" id="firstname" name="firstname" size="25" maxlength="15"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("last name").'*</td>
					<td align="left"><input type="text" id="lastname" name="lastname" size="25" maxlength="15"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("callerid").'</td>
					<td align="left"><input type="text" id="callerid" name="callerid" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("extension").'*</td>
					<td align="left"><input type="text" id="extension" name="extension" size="25" maxlength="15"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("dynamic agent").'</td>
					<td align="left"><input type="text" id="agent" name="agent" size="25" maxlength="15"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("extensions").'</td>
					<td align="left"><input type="text" id="extensions" name="extensions" size="25" maxlength="100" onclick="chkExtenionClick(this.value,this)" onblur="chkExtenionBlur(this.value,this)" style="color:#BBB" value="'.$locate->translate('extensions_input_tip').'" />&nbsp;<input type="radio" value="username" id="extensType" name="extensType" checked>'.$locate->Translate("username").'<input type="radio" value="extension" id="extensType" name="extensType" >'.$locate->Translate("extension").'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("channel").'</td>
					<td align="left"><input type="text" id="channel" name="channel" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("usertype").'*</td>
					<td align="left">
					<select id="usertypeSelect" onchange="usertypeChange(this)">
						<option value="0"></option>
						<option value="0">agent</option>
						<option value="0">groupadmin</option>';
						if ($_SESSION['curuser']['usertype'] == 'admin') {
							$html .='<option value="0">admin</option>';
						}
					$userTyperesult = Customer::getAstercrmUsertype();
					if(!empty($userTyperesult)) {
						foreach($userTyperesult as $usertype) {
							$html .='<option value="'.$usertype['id'].'">'.$usertype['usertype_name'].'</option>';
						}
					}
			$html .='
					</select><input type="hidden" id="usertype" name="usertype" value="" /><input type="hidden" id="usertype_id" name="usertype_id" value="0" /></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("account_code").'</td>
					<td align="left"><input type="text" id="accountcode" name="accountcode" size="20" maxlength="20"></td>
				</tr>';
		$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Group Name").'</td>
						<td>'.$groupoptions.'</td>
					</tr>';
		$html .= '<tr>
					<td nowrap align="left">'.$locate->Translate("Dial Interval").'</td>
					<td align="left"><input type="text" id="dialinterval" name="dialinterval" size="20" maxlength="20"></td>
				</tr>';
		$html .= '
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_save(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>

			 </table>
			';

		$html .='
			</form>
			'.$locate->Translate("obligatory_fields").'
			';
		
		return $html;
	}

	/**
	*  Imprime la forma para editar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser editado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma con los datos 
	*									a extraidos de la base de datos para ser editados 
	*/
	
	function formEdit($id){
		global $locate;
		$account =& Customer::getRecordByID($id,'astercrm_account');
		
	if ($_SESSION['curuser']['usertype'] == 'admin'){ 
			$grouphtml .=	'<select name="groupid" id="groupid" >';
			$res = Customer::getGroups();
			while ($row = $res->fetchRow()) {
				$grouphtml .= '<option value="'.$row['groupid'].'"';
				if($row['groupid'] == $account['groupid']){
					$grouphtml .= ' selected ';
				}
				$grouphtml .= '>'.$row['groupname'].'</option>';
			}
			$grouphtml .= '</select>';
	}else{
			
			$grouphtml .= $_SESSION['curuser']['group']['groupname'].'<input type="hidden" name="groupid" id="groupid" value="'.$_SESSION['curuser']['groupid'].'">';
	}


		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("username").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $account['id'].'"><input type="text" id="username" name="username" size="25" maxlength="30" value="'.$account['username'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("password").'*</td>
					<td align="left"><input type="text" id="password" name="password" size="25" maxlength="30" value="'.$account['password'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("first name").'*</td>
					<td align="left"><input type="text" id="firstname" name="firstname" size="25" maxlength="15" value="'.$account['firstname'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("last name").'*</td>
					<td align="left"><input type="text" id="lastname" name="lastname" size="25" maxlength="15" value="'.$account['lastname'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("callerid").'</td>
					<td align="left"><input type="text" id="callerid" name="callerid" size="25" maxlength="30" value="'.$account['callerid'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("extension").'*</td>
					<td align="left"><input type="text" id="extension" name="extension" size="25" maxlength="15" value="'.$account['extension'].'"></td>
				</tr>				
				<tr>
					<td nowrap align="left">'.$locate->Translate("dynamic agent").'</td>
					<td align="left"><input type="text" id="agent" name="agent" size="25" maxlength="15" value="'.$account['agent'].'"></td>
				</tr>
				<tr><td nowrap align="left">'.$locate->Translate("extensions").'</td>
					<td align="left">';
				if($account['extensions'] == '') {
					$html .= '<input type="text" id="extensions" name="extensions" size="25" maxlength="100" onclick="chkExtenionClick(this.value,this)" onblur="chkExtenionBlur(this.value,this)" style="color:#BBB" value="'.$locate->translate('extensions_input_tip').'">';
				} else {
					$html .= '<input type="text" id="extensions" name="extensions" size="25" maxlength="100" onclick="chkExtenionClick(this.value,this)" onblur="chkExtenionBlur(this.value,this)" value="'.$account['extensions'].'">';
				}
		$html .= '
					&nbsp;<input type="radio" value="username" id="extensType" name="extensType" checked>'.$locate->Translate("username").'<input type="radio" value="extension" id="extensType" name="extensType" >'.$locate->Translate("extension").'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("channel").'</td>
					<td align="left"><input type="text" id="channel" name="channel" size="25" maxlength="30" value="'.$account['channel'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("usertype").'*</td>
					<td align="left">
					<select id="usertypeSelect" onchange="usertypeChange(this)">
						<option value="0" ';
						if($account['usertype'] == ''){
							$html .= ' selected ';
						}
				$html .= '></option>
						<option value="0"';
						if($account['usertype'] == 'agent'){
							$html .= ' selected ';
						}
				$html .=' >agent</option>
						<option value="0"';
						if($account['usertype'] == 'groupadmin'){
							$html .= ' selected ';
						}
				$html .='>groupadmin</option>';

				if ($_SESSION['curuser']['usertype'] == 'admin') {
					$html .='<option value="0"';
					if($account['usertype'] == 'admin')	$html .= ' selected ';
					$html .='>admin</option>';
				}
				$userTyperesult = Customer::getAstercrmUsertype();
				if(!empty($userTyperesult)) {
					foreach($userTyperesult as $usertype) {
						$html .='<option value="'.$usertype['id'].'" ';
						if($usertype['id'] == $account['usertype_id']) {
							$html .=' selected';
						}
						$html .='>'.$usertype['usertype_name'].'</option>';
					}
				}
				$html .=	'</select><input type="hidden" id="usertype" name="usertype" value="'.$account['usertype'].'" /><input type="hidden" id="usertype_id" name="usertype_id" value="'.$account['usertype_id'].'" />
					<!--<input type="text" id="usertype" name="usertype" size="25" maxlength="30" value="'.$account['usertype'].'">--></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("account_code").'</td>
					<td align="left"><input type="text" id="accountcode" name="accountcode" size="20" maxlength="20" value="'.$account['accountcode'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("group_name").'</td>
					<td align="left">'.$grouphtml.'
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dial Interval").'</td>
					<td align="left"><input type="text" id="dialinterval" name="dialinterval" size="20" maxlength="20" value="'.$account['dialinterval'].'"></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_update(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>

			 </table>
			';

			

		$html .= '
				</form>
				'.$locate->Translate("obligatory_fields").'
				';

		return $html;
	}

	/**
	*  Imprime la forma para editar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser editado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma con los datos 
	*									a extraidos de la base de datos para ser editados 
	*/
	
	function showAccountDetail($id){
		global $locate;
		$account =& Customer::getRecordByID($id,'astercrm_account');
		$group = & Customer::getGroupByID($account['groupid']);
		$html = '
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("username").'</td>
					<td align="left">'.$account['username'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("password").'</td>
					<td align="left">'.$account['password'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("first name").'</td>
					<td align="left">'.$account['firstname'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("last name").'</td>
					<td align="left">'.$account['lastname'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("callerid").'</td>
					<td align="left">'.$account['callerid'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("extension").'</td>
					<td align="left">'.$account['extension'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("dynamic agent").'</td>
					<td align="left">'.$account['agent'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("extensions").'</td>
					<td align="left">'.$account['extensions'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("channel").'</td>
					<td align="left">"'.$account['channel'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("usertype").'</td>
					<td align="left">'.$account['usertype'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("account_code").'</td>
					<td align="left">'.$account['accountcode'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("group_name").'</td>
					<td align="left">'.$group['groupname'].'</td>
				</tr>
			 </table>
			';

		return $html;
	}


	function getAstercrmUsertype(){
		global $db;
		$sql = "SELECT * FROM user_types ";
		astercrm::events($sql);
		$result = & $db->query($sql);
		$usertype = array();
		while($result->fetchInto($row)) {
			$usertype[] = $row;
		}
		return $usertype;
	}

}
?>
