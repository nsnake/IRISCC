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
require_once 'agent.common.php';
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
		$agent_array = array();
		if($fhandle = fopen('/etc/asterisk/agents_astercc.conf','r')){
			$i = 0;
			while (!feof($fhandle)) {
				$buffer = fgets($fhandle, 4096);
				$buffer = explode('=>',$buffer);
				if(trim($buffer['0']) == 'agent' && trim($buffer['1']) != ''){
					$agents = explode(',',$buffer['1']);
					
					foreach($agents as $tmp){
						$agent_array[$i]['agent'] = trim($agents['0']);
						$agent_array[$i]['password'] = trim($agents['1']);
						$agent_array[$i]['name'] = trim($agents['2']);						
					}
				}
				$i++;
			}
			fclose($fhandle);
		}		
		return $agent_array;
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
	$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("agent").'*</td>
					<td align="left"><input type="text" id="agent" name="agent" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("password").'*</td>
					<td align="left"><input type="text" id="password" name="password" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("name").'*</td>
					<td align="left"><input type="text" id="name" name="name" size="25" maxlength="50"></td>
				</tr>
				';
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
		$agent_array = array();
		if($fhandle = fopen('/etc/asterisk/agents_astercc.conf','r')){
			while (!feof($fhandle)) {
				$buffer = fgets($fhandle, 4096);
				$buffer = explode('=>',$buffer);
				if(trim($buffer['0']) == 'agent' && trim($buffer['1']) != ''){
					$agents = explode(',',$buffer['1']);					

					if(trim($agents['0']) == $id){
						$agent_array['agent'] = trim($agents['0']);
						$agent_array['password'] = trim($agents['1']);
						$agent_array['name'] = trim($agents['2']);
						break;
					}
				}
			}
			fclose($fhandle);
		}
		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("agent").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $id.'"><input type="text" id="agent" name="agent" size="25" maxlength="30" value="'.$agent_array['agent'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("password").'*</td>
					<td align="left"><input type="text" id="password" name="password" size="25" maxlength="30" value="'.$agent_array['password'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("name").'*</td>
					<td align="left"><input type="text" id="name" name="name" size="25" maxlength="50" value="'.$agent_array['name'].'"></td>
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
		$agent_array = array();
		if($fhandle = fopen('/etc/asterisk/agents_astercc.conf','r')){
			while (!feof($fhandle)) {
				$buffer = fgets($fhandle, 4096);
				$buffer = explode('=>',$buffer);
				if(trim($buffer['0']) == 'agent' && trim($buffer['1']) != ''){
					$agents = explode(',',$buffer['1']);					

					if(trim($agents['0']) == $id){
						$agent_array['agent'] = trim($agents['0']);
						$agent_array['password'] = trim($agents['1']);
						$agent_array['name'] = trim($agents['2']);
						break;
					}
				}
			}
			fclose($fhandle);
		}

		$html = '
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("agent").'</td>
					<td align="left">'.$agent_array['agent'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("password").'</td>
					<td align="left">'.$agent_array['password'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("name").'</td>
					<td align="left">'.$agent_array['name'].'</td>
				</tr>
				
			 </table>
			';

		return $html;
	}

}
?>
