<?php
/*******************************************************************************
* asteriskcalls.grid.inc.php
* asteriskcalls操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加asteriskcalls表单的HTML
	formEdit					生成编辑asteriskcalls表单的HTML
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.0456  2007/10/30 13:15:00  last modified by solo
* Desc: add channel field 

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'asteriskcalls.common.php';
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
		
		$sql = "SELECT asteriskcalls.*, groupname  FROM asteriskcalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = asteriskcalls.groupid";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE asteriskcalls.groupid = ".$_SESSION['curuser']['groupid']." ";
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
			$sql = "SELECT * FROM asteriskcalls"
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

		$sql = "SELECT asteriskcalls.*, groupname FROM asteriskcalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = asteriskcalls.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " asteriskcalls.groupid = ".$_SESSION['curuser']['groupid']." ";
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
			$sql = " SELECT COUNT(*) FROM asteriskcalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = asteriskcalls.groupid";
		}else{
			$sql = " SELECT COUNT(*) FROM asteriskcalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = asteriskcalls.groupid WHERE asteriskcalls.groupid = ".$_SESSION['curuser']['groupid']." ";
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

			$sql = "SELECT COUNT(*) FROM asteriskcalls LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = asteriskcalls.groupid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " asteriskcalls.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

		if ($_SESSION['curuser']['usertype'] == 'admin'){
				$res = Customer::getGroups();
				$grouphtml .= '<select name="groupid" id="groupid">';
				while ($row = $res->fetchRow()) {
						$grouphtml .= '<option value="'.$row['groupid'].'"';
						$grouphtml .='>'.$row['groupname'].'</option>';
				}
				$grouphtml .= '</select>';
		}else{
				$grouphtml .= $_SESSION['curuser']['group']['groupname'].'<input id="groupid" name="groupid" type="hidden" value="'.$_SESSION['curuser']['groupid'].'">';
		}

	$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Name").' *</td>
					<td align="left"><input type="text" id="asteriskcallsname" name="asteriskcallsname" size="30" maxlength="50"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dialout context").' *</td>
					<td align="left"><input type="text" id="outcontext" name="outcontext" size="30" maxlength="50"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dialin context").' *</td>
					<td align="left"><input type="text" id="incontext" name="incontext" size="30" maxlength="50"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dialin extension").'</td>
					<td align="left"><input type="text" id="inextension" name="inextension" size="30" maxlength="50"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'</td>
					<td align="left">'.$grouphtml.'</td>
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
		$asteriskcalls =& Customer::getRecordByID($id,'asteriskcalls');
		
		if ($_SESSION['curuser']['usertype'] == 'admin'){ 
				$grouphtml .=	'<select name="groupid" id="groupid" >
																<option value=""></option>';
				$res = Customer::getGroups();
				while ($row = $res->fetchRow()) {
					$grouphtml .= '<option value="'.$row['groupid'].'"';
					if($row['groupid'] == $asteriskcalls['groupid']){
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
					<td nowrap align="left">'.$locate->Translate("Name").' *</td>
					<td align="left"><input type="text" id="asteriskcallsname" name="asteriskcallsname" size="30" maxlength="50" value="'.$asteriskcalls['asteriskcallsname'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dialout context").'</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $asteriskcalls['id'].'"><input type="text" id="dialoutcontext" name="dialoutcontext" size="30" maxlength="50" value="'.$asteriskcalls['outcontext'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dialin context").'</td>
					<td align="left"><input type="text" id="dialincontext" name="dialincontext" size="30" maxlength="50" value="'.$asteriskcalls['incontext'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dialin extension").'</td>
					<td align="left"><input type="text" id="dialinextension" name="dialinextension" size="30" maxlength="50" value="'.$asteriskcalls['inextension'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'</td>
					<td align="left">'.$grouphtml.'</td>
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

	function insertNewAsteriskcalls($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "INSERT INTO asteriskcalls SET "
				."asteriskcallsname='".$f['asteriskcallsname']."', "
				."outcontext='".$f['outcontext']."', "
				."incontext='".$f['incontext']."', "
				."inextension= '".$f['inextension']."', "
				."groupid = ".$f['groupid'].", "
				."cretime = now(), "
				."creby='".$_SESSION['curuser']['username']."'";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function updateAsteriskcallsRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "UPDATE asteriskcalls SET "
				."asteriskcallsname='".$f['asteriskcallsname']."', "
				."outcontext='".$f['dialoutcontext']."', "
				."incontext='".$f['dialincontext']."', "
				."inextension= '".$f['dialinextension']."', "
				."groupid = ".$f['groupid'].", "
				."cretime = now() "
				."WHERE id= ".$f['id']." ";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

}
?>
