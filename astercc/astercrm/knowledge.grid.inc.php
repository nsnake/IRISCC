<?php /*******************************************************************************
* knowledge.grid.inc.php
* knowledge操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加knowledge表单的HTML
	formEdit					生成编辑knowledge表单的HTML
	getRecordsFilteredMore      用于获得多条件搜索记录集
	getNumRowsMore              用于获得多条件搜索记录条数

* Revision 0.0456  2007/10/30 13:15:00  last modified by solo
* Desc: add channel field 

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'knowledge.common.php';
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
		
		$sql = "SELECT knowledge.*, groupname  FROM knowledge LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = knowledge.groupid";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE knowledge.groupid = ".$_SESSION['curuser']['groupid']." ";
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

		$sql = "SELECT knowledge.*, groupname FROM knowledge LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = knowledge.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " knowledge.groupid = ".$_SESSION['curuser']['groupid']." ";
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
			$sql = " SELECT COUNT(*) FROM knowledge LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = knowledge.groupid";
		}else{
			$sql = " SELECT COUNT(*) FROM knowledge LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = knowledge.groupid WHERE knowledge.groupid = ".$_SESSION['curuser']['groupid']." ";
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

			$sql = "SELECT COUNT(*) FROM knowledge LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = knowledge.groupid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " knowledge.groupid = ".$_SESSION['curuser']['groupid']." AND ";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " ".$joinstr;
			}else {
				$sql .= " 1";
			}
		Customer::events($sql);
		$res =& $db->getOne($sql);;
		return $res;
	}

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'knowledge');

			$sql = "SELECT COUNT(*) FROM knowledge LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = knowledge.groupid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " knowledge.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'knowledge');

		$sql = "SELECT knowledge.*, groupname FROM knowledge LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = knowledge.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " knowledge.groupid = ".$_SESSION['curuser']['groupid']." ";
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
				$groupoptions .= '<option value="0">'.$locate->Translate("please_select").'</option>';
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
					<td nowrap align="left">'.$locate->Translate("knowledgetitle").'*</td>
					<td align="left"><input type="text" id="knowledgetitle" name="knowledgetitle" size="45" maxlength="200"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("content").'*</td>
					<td align="left"><textarea rows="10" cols="70" id="content" name="content" wrap="soft" style="overflow:auto;">'.$customer['content'].'</textarea></td>
				</tr>';
		$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Group Name").'</td>
						<td>'.$groupoptions.'</td>
					</tr>';
		$html .= '
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_save(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("save").'</button></td>
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
		$knowledge =& Customer::getRecordByID($id,'knowledge');

	if ($_SESSION['curuser']['usertype'] == 'admin'){ 
			$grouphtml .=	'<select name="groupid" id="groupid" >';
			$grouphtml .= '<option value="0">'.$locate->Translate("please_select").'</option>';
			$res = Customer::getGroups();
			while ($row = $res->fetchRow()) {
				$grouphtml .= '<option value="'.$row['groupid'].'"';
				if($row['groupid'] == $knowledge['groupid']){
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
					<td nowrap align="left">'.$locate->Translate("knowledgetitle").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $knowledge['id'].'"><input type="text" id="knowledgetitle" name="knowledgetitle" size="45" maxlength="30" value="'.$knowledge['knowledgetitle'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("content").'*</td>
					<td align="left"><textarea rows="10" cols="70" id="content" name="content" wrap="soft" style="overflow:auto;">'.$knowledge['content'].'</textarea></td>
				</tr>			
				<tr>
					<td nowrap align="left">'.$locate->Translate("group_name").'</td>
					<td align="left">'.$grouphtml.'
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_update(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("save").'</button></td>
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

}
?>
