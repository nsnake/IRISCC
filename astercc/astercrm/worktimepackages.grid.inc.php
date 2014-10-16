<?php /*******************************************************************************
* worktimepackages.grid.inc.php
* worktimepackages操作类
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
require_once 'worktimepackages.common.php';
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
		
		$sql = "SELECT worktimepackages.*,groupname FROM worktimepackages LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimepackages.groupid ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE worktimepackages.groupid = ".$_SESSION['curuser']['groupid']." ";
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

	function insertNewWorktimepackage($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$query= "INSERT INTO worktimepackages SET "
				."worktimepackage_name=".$db->quote($f['worktimepackage_name']).", "
				."worktimepackage_note=".$db->quote($f['worktimepackage_note']).", "
				."worktimepackage_status='".$f['worktimepackage_status']."', "				
				."groupid='".$f['groupid']."', "
				."creby = '".$_SESSION['curuser']['username']."',"
				."cretime = now()";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}


	function updateWorktimepackage($f){
		global $db;
		$f = astercrm::variableFiler($f);

		$query= "UPDATE worktimepackages SET "
				."worktimepackage_name=".$db->quote($f['worktimepackage_name']).", "
				."worktimepackage_note=".$db->quote($f['worktimepackage_note']).", "
				."worktimepackage_status='".$f['worktimepackage_status']."', "
				."groupid='".$f['groupid']."' "
				."WHERE id=".$f['id'];

		$wp_res = Customer::deleteRecords("worktimepackage_id",$f['id'],'worktimepackage_worktimes');

		$sltedWorktimes=split(',',rtrim($f['sltedWorktimes'],','));
		foreach($sltedWorktimes as $worktimeid){
			$sql = "INSERT INTO worktimepackage_worktimes SET "
					."worktimepackage_id='".$f['id']."', "
					."worktime_id='".$worktimeid."', "
					."creby = '".$_SESSION['curuser']['username']."',"
					."cretime = now()";
			$wp_res = & $db->query($sql);
		}
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
			$sql = " SELECT COUNT(*) FROM worktimepackages LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimepackages.groupid";
		}else{
			$sql = " SELECT COUNT(*) FROM worktimepackages LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimepackages.groupid WHERE worktimepackages.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"worktimepackages");

		$sql = "SELECT worktimepackages.*, groupname FROM worktimepackages LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimepackages.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " worktimepackages.groupid = ".$_SESSION['curuser']['groupid']." ";
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
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"worktimepackages");

			$sql = "SELECT COUNT(*) FROM worktimepackages LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = worktimepackages.groupid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " worktimepackages.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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
		
//		$query = "SELECT * FROM worktimes";
//		if($_SESSION['curuser']['usertype'] != 'admin') $query .= " WHERE groupid = ".$_SESSION['curuser']['groupid'];
//		$worktimes_res = $db->query($query);
//		$worktimeshtml .= '';
//		$i=0;
//		while ($worktimes_row = $worktimes_res->fetchRow()) {
//			$i++;
//			$cur_content = $worktimes_row['id'].'-'.$locate->Translate("from").':'.$worktimes_row['starttime'].'&nbsp;'.$locate->Translate("to").':'.$worktimes_row['endtime'];
//			$worktimeshtml .= '<a href="javascript:void(0);" id="op_'.$i.'" onclick="mf_click('.$i.', \''.$cur_content.'\');">'.$cur_content.'</a><input type="hidden" id="worktimeVal_'.$i.'" name="worktimeVal_'.$i.'" value="'.$worktimes_row['id'].'">';			
//		}
//		$worktimeshtml = '
//			<table width="300" border="0" cellpadding="0" cellspacing="0" id="formTable">
//				<tr><td width="180"><div id="worktimeAllDiv">'.$worktimeshtml.'</div></td></tr>
//				<tr><td><div id="worktimeSltdDiv"></div><input type="hidden" id="sltedWorktimes" name="sltedWorktimes" value=""></td></tr>
//			</table>';
		
//echo $worktimeshtml;exit;
	$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Package Name").'*</td>
					<td align="left"><input type="text" id="worktimepackage_name" name="worktimepackage_name" size="30" maxlength="60"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Status").'*</td>
					<td align="left" colspan="2">'.$locate->Translate("Enable").'&nbsp;<input type="radio" id="worktimepackage_status" name="worktimepackage_status" value="enable" checked>&nbsp;'.$locate->Translate("Disable").'&nbsp;<input type="radio" id="worktimepackage_status" name="worktimepackage_status" value="disabled" ></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Package Note").'</td>
					<td align="left"><input type="text" id="worktimepackage_note" name="worktimepackage_note" size="30" maxlength="255"></td>
				</tr>				
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'*</td>
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
		$worktimepackages =& Customer::getRecordByID($id,'worktimepackages');

		if ($_SESSION['curuser']['usertype'] == 'admin'){
				$grouphtml .=	'<select name="groupid" id="groupid" >
																<option value=""></option>';
				$res = Customer::getGroups();
				while ($row = $res->fetchRow()) {
					$grouphtml .= '<option value="'.$row['groupid'].'"';
					if($row['groupid'] == $worktimepackages['groupid']){
						$grouphtml .= ' selected ';
					}
					$grouphtml .= '>'.$row['groupname'].'</option>';
				}
				$grouphtml .= '</select>';
		}else{
				
				$grouphtml .= $_SESSION['curuser']['group']['groupname'].'<input type="hidden" name="groupid" id="groupid" value="'.$_SESSION['curuser']['groupid'].'">';
		}
		
		//print_r($wp);exit;
		$query = "SELECT * FROM worktimes";
		if($_SESSION['curuser']['usertype'] != 'admin') $query .= " WHERE groupid = ".$_SESSION['curuser']['groupid'];
		$worktimes_res = $db->query($query);
		$worktimeshtml .= '';
		$i=0;
		$weekShow=array('',$locate->Translate("Monday"),$locate->Translate('Tuesday'),$locate->Translate('Wednesday'),$locate->Translate('Thursday'),$locate->Translate('Friday'),$locate->Translate('Saturday'),$locate->Translate('Sunday'));

		while ($worktimes_row = $worktimes_res->fetchRow()) {			
			$i++;
			$cur_content = $worktimes_row['id'].'-'.$locate->Translate("from").':'.$worktimes_row['starttime'].'&nbsp;'.$locate->Translate("to").':'.$worktimes_row['endtime'].'&nbsp;('.$weekShow[$worktimes_row['startweek']].'->'.$weekShow[$worktimes_row['endweek']].')';

			$worktimeshtml .= '<a href="javascript:void(0);" id="op_'.$i.'" onclick="mf_click('.$i.', \''.$cur_content.'\');">'.$cur_content.'</a><input type="hidden" id="worktimeVal_'.$i.'" name="worktimeVal_'.$i.'" value="'.$worktimes_row['id'].'">';			
		}

		$worktimeshtml = '
			<table width="300" border="0" cellpadding="0" cellspacing="0" id="formTable">
				<tr><td width="180"><div id="worktimeAllDiv">'.$worktimeshtml.'</div></td></tr>
				<tr><td><div id="worktimeSltdDiv"></div><input type="hidden" id="sltedWorktimes" name="sltedWorktimes" value=""></td></tr>
			</table>';

		if($worktimepackages['worktimepackage_status'] == 'enable'){
			$enable = 'checked';
		}else{
			$disabled = 'checked';
		}

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Package Name").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $worktimepackages['id'].'"><input type="text" id="worktimepackage_name" name="worktimepackage_name" size="30" maxlength="60" value="'.$worktimepackages['worktimepackage_name'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Package Note").'</td>
					<td align="left"><input type="text" id="worktimepackage_note" name="worktimepackage_note" size="30" maxlength="255" value="'.$worktimepackages['worktimepackage_note'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Status").'*</td>
					<td align="left" colspan="2">'.$locate->Translate("Enable").'&nbsp;<input type="radio" id="worktimepackage_status" name="worktimepackage_status" value="enable" '.$enable.'>&nbsp;'.$locate->Translate("Disable").'&nbsp;<input type="radio" id="worktimepackage_status" name="worktimepackage_status" value="disabled"  '.$disabled.'></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Select Worktime").'*</td>
					<td align="left"><div class="worktimeSltDiv">'.$worktimeshtml.'</div></td>
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
