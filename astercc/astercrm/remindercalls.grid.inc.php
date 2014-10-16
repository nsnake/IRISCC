<?php
/*******************************************************************************
* remindercalls.grid.inc.php
* remindercalls操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加remindercalls表单的HTML
	formEdit					生成编辑remindercalls表单的HTML
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.0456  2007/10/30 13:15:00  last modified by solo
* Desc: add channel field 

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'remindercalls.common.php';
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
	function &getAllRecords($start, $limit, $order = null, $creby = null){
		global $db;
		
		$sql = "SELECT remindercalls.*, groupname, asteriskcallsname  FROM remindercalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = remindercalls.groupid LEFT JOIN asteriskcalls ON asteriskcalls.id = remindercalls.asteriskcallsid";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE remindercalls.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

//		if ($creby != null)
//			$sql .= " WHERE note.creby = '".$_SESSION['curuser']['username']."' ";
			

		if($order == null){
			$sql .= " LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit";
		}

		//echo $sql;
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

	function &getRecordsFiltered($start, $limit, $filter = null, $content = null, $order = null, $ordering = ""){
		global $db;
		
		if(($filter != null) and ($content != null)){
			$sql = "SELECT * FROM remindercalls"
					." WHERE ".$filter." like '%".$content."%' "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}
	

	function &getRecordsFilteredMore($start, $limit, $filter, $content, $order,$table, $ordering = ""){
		global $db;

		$i=0;
		$joinstr='';
		foreach ($content as $value){
			$value=trim($value);
			if (strlen($value)!=0 && strlen($filter[$i]) != 0){
				$joinstr.="AND $filter[$i] like '%".$value."%' ";
			}
			$i++;
		}

		$sql = "SELECT remindercalls.*, groupname, asteriskcallsname FROM remindercalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = remindercalls.groupid LEFT JOIN asteriskcalls ON asteriskcalls.id = remindercalls.asteriskcallsid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " remindercalls.groupid = ".$_SESSION['curuser']['groupid']." ";
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
			$sql = " SELECT COUNT(*) FROM remindercalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = remindercalls.groupid LEFT JOIN asteriskcalls ON asteriskcalls.id = remindercalls.asteriskcallsid ";
		}else{
			$sql = " SELECT COUNT(*) FROM remindercalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = remindercalls.groupid LEFT JOIN asteriskcalls ON asteriskcalls.id = remindercalls.asteriskcallsid WHERE remindercalls.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getNumRowsMore($filter = null, $content = null,$table){
		global $db;
		
			$i=0;
			$joinstr='';
			foreach ($content as $value){
				$value=trim($value);
				if (strlen($value)!=0 && strlen($filter[$i]) != 0){
					$joinstr.="AND $filter[$i] like '%".$value."%' ";
				}
				$i++;
			}

			$sql = "SELECT COUNT(*) FROM remindercalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = remindercalls.groupid LEFT JOIN asteriskcalls ON asteriskcalls.id = remindercalls.asteriskcallsid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " remindercalls.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

	/**
	*  Imprime la forma para agregar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param ninguno
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma para insertar 
	*							un nuevo registro.
	*/
	
	function formAdd(){
		global $locate;

		$groupoptions = '';
		$group = astercrm::getGroups();

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$groupoptions .= '<select id="groupid" name="groupid" onchange="setAsteriskcalls();">';
			$groupoptions .= '<option value="0"></option>';
			while	($group->fetchInto($row)){
				$groupoptions .= "<OPTION value='".$row['groupid']."'>".$row['groupname']."</OPTION>";
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

		//$res = Customer::getRecordsByGroupid($_SESSION['curuser']['groupid'],'asteriskcalls');
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'groupadmin'){
			$asteriskcallsoptions .= '<select id="asteriskcallsid" name="asteriskcallsid">';
			$asteriskcallsoptions .= "<OPTION value='0'></OPTION>";
			//while	($group->fetchInto($row)){
			//	$groupoptions .= "<OPTION value='".$row['id']."'>".$row['groupname']."</OPTION>";
			//}
			$asteriskcallsoptions .= '</select>';
		}else{
			//while	($group->fetchInto($row)){
			//	if ($row['id'] == $_SESSION['curuser']['groupid']){
			//		$groupoptions .= $row['groupname'].'<input type="hidden" value="'.$row['id'].'" name="groupid" id="groupid">';
			//		break;
			//	}
			//}
		}


		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Phone number").' *</td>
					<td align="left"><input type="text" id="phonenumber" name="phonenumber" size="30" maxlength="50"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Note").'</td>
					<td align="left"><input type="text" id="note" name="note" size="50" maxlength="255"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'</td>
					<td align="left">'.$groupoptions.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Call plan").' *</td>
					<td align="left">'.$asteriskcallsoptions.'</td>
				</tr>
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
		$remindercalls =& Customer::getRecordByID($id,'remindercalls');

		$groupoptions = '';
		$group = astercrm::getGroups();

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$groupoptions .= '<select id="groupid" name="groupid" onchange="setAsteriskcalls();">';
			$groupoptions .= '<option value="0"></option>';
			while	($group->fetchInto($row)){
				$groupoptions .= '<option value="'.$row['groupid'].'"';
				if($row['groupid'] == $remindercalls['groupid']){
					$groupoptions .= ' selected ';
				}
				$groupoptions .= '>'.$row['groupname'].'</option>';
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

		$asteriskcalls = Customer::getRecordsByGroupid($remindercalls['groupid'],'asteriskcalls');
		
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'groupadmin'){
			$asteriskcallsoptions .= '<select id="asteriskcallsid" name="asteriskcallsid">';
			$asteriskcallsoptions .= "<OPTION value='0'></OPTION>";
			while	($asteriskcalls->fetchInto($row)){
				if ($row['id'] == $remindercalls['asteriskcallsid']){
					$asteriskcallsoptions .= "<OPTION value='".$row['id']."' selected>".$row['asteriskcallsname']."</OPTION>";
				}else{
					$asteriskcallsoptions .= "<OPTION value='".$row['id']."' >".$row['asteriskcallsname']."</OPTION>";
				}
			}
			$asteriskcallsoptions .= '</select>';
		}else{
			while	($group->fetchInto($row)){
				if ($row['id'] == $remindercalls['asteriskcallsid']){
					$asteriskcallsoptions .= $row['asteriskcallsname'].'<input type="hidden" value="'.$row['id'].'" name="asteriskcallsid" id="asteriskcallsid">';
					break;
				}
			}
		}



		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Phone number").' *</td>
					<td align="left"><input type="text" id="phonenumber" name="phonenumber" size="30" maxlength="50" value="'.$remindercalls['phonenumber'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Note").'</td>
					<td align="left"><input type="text" id="note" name="note" size="50" maxlength="255" value="'.$remindercalls['note'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'</td>
					<td align="left">'.$groupoptions.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Call plan").' *</td>
					<td align="left">'.$asteriskcallsoptions.'</td>
				</tr>
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

	function insertNewRemindercalls($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "INSERT INTO remindercalls SET "
				."customerid='".$f['customerid']."', "
				."contactid='".$f['contactid']."', "
				."phonenumber= '".$f['phonenumber']."', "
				."asteriskcallsid= '".$f['asteriskcallsid']."', "
				."note= '".$f['note']."', "
				."dialtime= '".$f['dialtime']."', "
				."groupid = ".$f['groupid'].", "
				."cretime = now(), "
				."creby='".$_SESSION['curuser']['username']."'";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function updateRemindercallsRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "UPDATE remindercalls SET "
				."customerid='".$f['customerid']."', "
				."contactid='".$f['contactid']."', "
				."phonenumber= '".$f['phonenumber']."', "
				."asteriskcallsid= '".$f['asteriskcallsid']."', "
				."note= '".$f['note']."', "
				."dialtime= '".$f['dialtime']."', "
				."groupid = ".$f['groupid'].", "
				."cretime = now() "
				."WHERE id= ".$f['id']." ";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

}
?>
