<?php /*******************************************************************************
* worktime.grid.inc.php
* worktime操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加campaign表单的HTML
	formEdit					生成编辑campaign表单的HTML
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.0456  2007/10/30 13:15:00  last modified by solo
* Desc: add channel field 

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'worktime.common.php';
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
		
		$sql = "SELECT worktimes.*,groupname FROM worktimes LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimes.groupid ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE worktimes.groupid = ".$_SESSION['curuser']['groupid']." ";
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

	function insertNewWorktime($f){
		global $db;
		$f = astercrm::variableFiler($f);

		$query= "INSERT INTO worktimes SET "
				."starttime='".$f['starttime']."', "
				."endtime='".$f['endtime']."', "
				."startweek='".$f['startweek']."', "
				."endweek='".$f['endweek']."', "				
				."groupid='".$f['groupid']."', "
				."creby = '".$_SESSION['curuser']['username']."',"
				."cretime = now()";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}


	function updateWorktimeRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$query= "UPDATE worktimes SET "
				."starttime='".$f['starttime']."', "
				."endtime='".$f['endtime']."', "
				."startweek='".$f['startweek']."', "	
				."endweek='".$f['endweek']."', "				
				."groupid='".$f['groupid']."' "
				."WHERE id=".$f['id'];

		astercrm::events($query);
		$res =& $db->query($query);
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
			$sql = " SELECT COUNT(*) FROM worktimes LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimes.groupid";
		}else{
			$sql = " SELECT COUNT(*) FROM worktimes LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimes.groupid WHERE worktimes.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"worktimes");

		$sql = "SELECT worktimes.*, groupname FROM worktimes LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimes.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " worktimes.groupid = ".$_SESSION['curuser']['groupid']." ";
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

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"worktimes");

			$sql = "SELECT COUNT(*) FROM worktimes LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimes.groupid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " worktimes.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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
			global $locate,$config,$db;

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
					<td nowrap align="left">'.$locate->Translate("Time").'*</td>
					<td align="left">'.$locate->Translate("From").':&nbsp;<input id="starttime" name="starttime" type="text" readonly onclick="showTimeList(\'timelist\');_SetTime(this)"/>&nbsp;'.$locate->Translate("To").':&nbsp;<input id="endtime" name="endtime" type="text" readonly onclick="showTimeList(\'timelist\');_SetTime(this)"/><div id="timelist" ></div></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Week").'*</td>
					<td align="left">'.$locate->Translate("From").':&nbsp;
							<SELECT id="startweek" name="startweek">
									<OPTION value="1">'.$locate->Translate("Monday").'</OPTION>
									<OPTION value="2">'.$locate->Translate("Tuesday").'</OPTION>
									<OPTION value="3">'.$locate->Translate("Wednesday").'</OPTION>
									<OPTION value="4">'.$locate->Translate("Thursday").'</OPTION>
									<OPTION value="5">'.$locate->Translate("Friday").'</OPTION>
									<OPTION value="6">'.$locate->Translate("Saturday").'</OPTION>
									<OPTION value="7">'.$locate->Translate("Sunday").'</OPTION>							
							</SELECT>&nbsp;'.$locate->Translate("To").':&nbsp;
							<SELECT id="endweek" name="endweek">
									<OPTION value="1">'.$locate->Translate("Monday").'</OPTION>
									<OPTION value="2">'.$locate->Translate("Tuesday").'</OPTION>
									<OPTION value="3">'.$locate->Translate("Wednesday").'</OPTION>
									<OPTION value="4">'.$locate->Translate("Thursday").'</OPTION>
									<OPTION value="5">'.$locate->Translate("Friday").'</OPTION>
									<OPTION value="6">'.$locate->Translate("Saturday").'</OPTION>
									<OPTION value="7">'.$locate->Translate("Sunday").'</OPTION>							
							</SELECT>
					</td>
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
		global $locate,$db;
		$worktimes =& Customer::getRecordByID($id,'worktimes');

		if ($_SESSION['curuser']['usertype'] == 'admin'){ 
				$grouphtml .=	'<select name="groupid" id="groupid" >
																<option value=""></option>';
				$res = Customer::getGroups();
				while ($row = $res->fetchRow()) {
					$grouphtml .= '<option value="'.$row['groupid'].'"';
					if($row['groupid'] == $worktimes['groupid']){
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
					<td nowrap align="left"><input type="hidden" id= "id" name="id" value="'.$worktimes['id'].'">'.$locate->Translate("Time").'*</td>
					<td align="left">'.$locate->Translate("From").':&nbsp;<input id="starttime" name="starttime" type="text" value="'.$worktimes['starttime'].'" readonly onclick="showTimeList(\'timelist\');_SetTime(this)"/>&nbsp;'.$locate->Translate("To").':&nbsp;<input id="endtime" name="endtime" type="text" value="'.$worktimes['endtime'].'" readonly onclick="showTimeList(\'timelist\');_SetTime(this)"/><div id="timelist" ></div></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Week").'*</td>
					<td align="left">'.$locate->Translate("From").':&nbsp;
							<SELECT id="startweek" name="startweek">
									<OPTION value="1"';
									if($worktimes['startweek']==1) $html .= 'selected';
									$html .= '>'.$locate->Translate("Monday").'</OPTION>
									<OPTION value="2"';
									if($worktimes['startweek']==2) $html .= 'selected';
									$html .= '>'.$locate->Translate("Tuesday").'</OPTION>
									<OPTION value="3"';
									if($worktimes['startweek']==3) $html .= 'selected';
									$html .= '>'.$locate->Translate("Wednesday").'</OPTION>
									<OPTION value="4"';
									if($worktimes['startweek']==4) $html .= 'selected';
									$html .= '>'.$locate->Translate("Thursday").'</OPTION>
									<OPTION value="5"';
									if($worktimes['startweek']==5) $html .= 'selected';
									$html .= '>'.$locate->Translate("Friday").'</OPTION>
									<OPTION value="6"';
									if($worktimes['startweek']==6) $html .= 'selected';
									$html .= '>'.$locate->Translate("Saturday").'</OPTION>
									<OPTION value="7"';
									if($worktimes['startweek']==7) $html .= 'selected';
									$html .= '>'.$locate->Translate("Sunday").'</OPTION>							
							</SELECT>&nbsp;'.$locate->Translate("To").':&nbsp;
							<SELECT id="endweek" name="endweek">
									<OPTION value="1"';
									if($worktimes['endweek']==1) $html .= 'selected';
									$html .= '>'.$locate->Translate("Monday").'</OPTION>
									<OPTION value="2"';
									if($worktimes['endweek']==2) $html .= 'selected';
									$html .= '>'.$locate->Translate("Tuesday").'</OPTION>
									<OPTION value="3"';
									if($worktimes['endweek']==3) $html .= 'selected';
									$html .= '>'.$locate->Translate("Wednesday").'</OPTION>
									<OPTION value="4"';
									if($worktimes['endweek']==4) $html .= 'selected';
									$html .= '>'.$locate->Translate("Thursday").'</OPTION>
									<OPTION value="5"';
									if($worktimes['endweek']==5) $html .= 'selected';
									$html .= '>'.$locate->Translate("Friday").'</OPTION>
									<OPTION value="6"';
									if($worktimes['endweek']==6) $html .= 'selected';
									$html .= '>'.$locate->Translate("Saturday").'</OPTION>
									<OPTION value="7"';
									if($worktimes['endweek']==7) $html .= 'selected';
									$html .= '>'.$locate->Translate("Sunday").'</OPTION>							
							</SELECT>
					</td>
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
}
?>
