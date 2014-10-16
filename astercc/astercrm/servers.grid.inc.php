<?php
/*******************************************************************************
* servers.grid.inc.php
* servers操作类
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
require_once 'servers.common.php';
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
	function &getAllRecords($start, $limit, $order = null){
		global $db;
		
		$sql = "SELECT * FROM servers ";
			
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

		$sql = "SELECT * FROM servers WHERE 1";
		
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
	
	function &getNumRows(){
		global $db;
		
		$sql = " SELECT COUNT(*) FROM servers ";
		
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

			$sql = "SELECT COUNT(*) FROM servers WHERE ";
			
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

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'servers');

			$sql = "SELECT COUNT(*) FROM servers WHERE ";
			
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

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'servers');

		$sql = "SELECT * FROM servers WHERE 1 ";
		
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

	$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Server name").'*</td>
					<td align="left"><input type="text" id="name" name="name" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("IP").'*</td>
					<td align="left"><input type="text" id="ip" name="ip" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Port").'*</td>
					<td align="left"><input type="text" id="port" name="port" size="25" maxlength="15"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Username").'*</td>
					<td align="left"><input type="text" id="username" name="username" size="25" maxlength="15"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Secret").'*</td>
					<td align="left"><input type="text" id="secret" name="secret" size="25" maxlength="15"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Note").'</td>
					<td align="left"><textarea rows="3" cols="30" id="note" name="note"></textarea></td>
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
		$server =& Customer::getRecordByID($id,'servers');

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Server name").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $server['id'].'"><input type="text" id="name" name="name" size="25" maxlength="30" value="'.$server['name'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("IP").'*</td>
					<td align="left"><input type="text" id="ip" name="ip" size="25" maxlength="30" value="'.$server['ip'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Port").'*</td>
					<td align="left"><input type="text" id="port" name="port" size="25" maxlength="15" value="'.$server['port'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Username").'*</td>
					<td align="left"><input type="text" id="username" name="username" size="25" maxlength="15" value="'.$server['username'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Secret").'*</td>
					<td align="left"><input type="text" id="secret" name="secret" size="25" maxlength="15" value="'.$server['secret'].'"></td>
				</tr>				
				<tr>
					<td nowrap align="left">'.$locate->Translate("Note").'</td>
					<td align="left"><textarea rows="3" cols="30" id="note" name="note">'.$server['note'].'</textarea></td>
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
	
	function showServerDetail($id){
		global $locate;
		$server =& Customer::getRecordByID($id,'servers');

		$html = '
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Server name").'</td>
					<td align="left">'.$server['name'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("IP").'</td>
					<td align="left">'.$server['ip'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Port").'</td>
					<td align="left">'.$server['port'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Username").'</td>
					<td align="left">'.$server['username'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Secret").'</td>
					<td align="left">'.$server['secret'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Note").'</td>
					<td align="left">'.$server['note'].'</td>
				</tr>				
				<tr>
					<td nowrap align="left" ><input type="button" value="'.$locate->Translate("Check AMI connection").'" onclick="xajax_checkAMI('.$server['id'].')" id="btnCheckServer" name="btnCheckServer"></td>
					<td><div id="divCheckServer" name="divCheckServer"></td>
				</tr>
			 </table>
			';

		return $html;
	}

	function insertNewServer($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "INSERT INTO servers SET "
				."name='".$f['name']."', "
				."ip='".$f['ip']."', "
				."port='".$f['port']."',"
				."username='".$f['username']."',"
				."secret='".$f['secret']."',"
				."note = '".$f['note']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function updateServerRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$query= "UPDATE servers SET "
				."name='".$f['name']."', "
				."ip='".$f['ip']."', "
				."port='".$f['port']."', "
				."username= '".$f['username']."', "
				."secret='".$f['secret']."', "
				."note='".$f['note']."' "
				."WHERE id='".$f['id']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}
}
?>
