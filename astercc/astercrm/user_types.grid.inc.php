<?php /*******************************************************************************
* user_types.grid.inc.php
* user_types操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Dec 2010

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加account表单的HTML
	formEdit					生成编辑account表单的HTML
	新增 getRecordsFilteredMore  用于获得多条件搜索记录集
	新增 getNumRowsMore          用于获得多条件搜索记录条数

********************************************************************************/

require_once 'db_connect.php';
require_once 'user_types.common.php';
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
		
		$sql = "SELECT *  FROM user_types ";

//		if ($_SESSION['curuser']['usertype'] == 'admin'){
//			$sql .= " ";
//		}else{
//			$sql .= " WHERE astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." ";
//		}
			
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

		$sql = "SELECT * FROM user_types WHERE 1 ";

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
		
//		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = " SELECT COUNT(*) FROM user_types ";
//		}else{
//			$sql = " SELECT COUNT(*) FROM astercrm_account LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = astercrm_account.groupid WHERE astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." ";
//		}
		
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

			$sql = "SELECT COUNT(*) FROM user_types WHERE ";
//			if ($_SESSION['curuser']['usertype'] == 'admin'){
//				$sql .= " ";
//			}else{
//				$sql .= " astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." AND ";
//			}

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
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'astercrm_account');

			$sql = "SELECT COUNT(*) FROM user_types WHERE ";
//			if ($_SESSION['curuser']['usertype'] == 'admin'){
//				$sql .= " ";
//			}else{
//				$sql .= " astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." AND ";
//			}

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

		$sql = "SELECT * FROM user_types WHERE 1 ";
//		if ($_SESSION['curuser']['usertype'] == 'admin'){
//			$sql .= " 1 ";
//		}else{
//			$sql .= " astercrm_account.groupid = ".$_SESSION['curuser']['groupid']." ";
//		}

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

	function insertNewUserType($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$sql= "INSERT INTO user_types SET "
				."usertype_name='".$f['usertype_name']."', "
				."memo='".$f['memo']."', "
				."created = now() ";

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function updateUserTypeRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$sql= "UPDATE user_types SET "
				."usertype_name='".$f['usertype_name']."', "
				."memo='".$f['memo']."' where id=".$f['Id']." ";

		astercrm::events($sql);
		$res =& $db->query($sql);

		//下面是编辑权限,先删除这个用户的所有权限，然后进行重新分配
		astercrm::events("DELETE FROM user_privileges WHERE user_type_id=".$f['Id']." ");
		$delete =& $db->query("DELETE FROM user_privileges WHERE user_type_id=".$f['Id']." ");
		if(!$delete) return $res;//删除权限失败就不重新插如新的权限
		$insertArray = array();
		$curView = explode(',',rtrim($f['chkView'],','));
		foreach($curView as $view) {
			$obj = explode('=',$view);
			$checked = $obj[1];
			$page = str_replace("_view","",$obj[0]);
			if($checked) {
				$insertArray[] = array('view',$page,$f['Id']);
			}
		}
		
		$curEdit = explode(',',rtrim($f['chkEdit'],','));
		foreach($curEdit as $edit) {
			$Eobj = explode('=',$edit);
			$Echecked = $Eobj[1];
			$Epage =  str_replace("_edit","",$Eobj[0]);
			if($Echecked) {
				$insertArray[] = array('edit',$Epage,$f['Id']);
			}
		}

		$curDel = explode(',',rtrim($f['ckdelete'],','));
		foreach($curDel as $del) {
			$Dobj = explode('=',$del);
			$Dchecked = $Dobj[1];
			$Dpage =  str_replace("_delete","",$Dobj[0]);
			if($Dchecked) {
				$insertArray[] = array('delete',$Dpage,$f['Id']);
			}
			
		}
		if(!empty($insertArray)) {
			$privilege_sql = "INSERT INTO `user_privileges` (`action`,`page`,`user_type_id`,`created`) VALUES ";
			foreach($insertArray as $tmp) {
				$privilege_sql .= "('".$tmp[0]."','".$tmp[1]."',".$tmp[2].",now()),";
			}
			$new_sql .= rtrim($privilege_sql,',').';';
			
			astercrm::events($new_sql);
			$result =& $db->query($new_sql);
		}
		
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
					<td nowrap align="left">'.$locate->Translate("usertype name").'*</td>
					<td align="left"><input type="text" id="usertype_name" name="usertype_name" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("memo").'*</td>
					<td align="left"><textarea rows="8" cols="40" id="memo" name="memo"></textarea></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_save(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>

			 </table>
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
		$result =& Customer::getRecordByID($id,'user_types');
		
		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("usertype name").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $result['id'].'"><input type="text" id="usertype_name" name="usertype_name" size="25" maxlength="30" value="'.$result['usertype_name'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("memo").'*</td>
					<td align="left"><textarea rows="8" cols="40" id="memo" name="memo">'.$result['memo'].'</textarea></td>
				</tr>
				<tr style="height:200px;">
					<td nowrap align="left" colspan="2">
						<div style="width:100%;height:198px;overflow:auto;">';
							$html .= Customer::getPrivilegePage($id);
				$html .= '</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onclick=\'update(xajax.getFormValues(f));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>
			 </table>
			</form>
			'.$locate->Translate("obligatory_fields").'
			';
		return $html;
	}

	function getCurUserTypePrivileges($usertypeId){
		global $db;
		$sql = "SELECT * FROM user_privileges WHERE user_type_id=".$usertypeId." ";
		
		astercrm::events($sql);
		$result = & $db->query($sql);
		$privileges = array();
		while ($result->fetchInto($row)) {
			//$privileges[$row['page'].'_'.$row['action']] = 'true';
			$privileges[] = $row;
		}
		return $privileges;
	}

	function getPrivilegePage($usertypeId){
		global $locate;
		
		$pageHtml = 
			'<table class="adminlist" width="100%" border="1">
				<tr>
					<td>'.$locate->translate('page').'</td>
					<td>'.$locate->translate('view').' <input type="checkbox" id="checkAll_view" onclick="ckviewAllOnClick(this);" /></td>
					<td>'.$locate->translate('edit').' <input type="checkbox" id="checkAll_edit" onclick="ckeditAllOnClick(this);"/></td>
					<td>'.$locate->translate('delete').' <input type="checkbox" id="checkAll_delete" onclick="ckdeleteAllOnClick(this);"/></td>
				</tr>
				<tr>
					<td>'.$locate->translate('import').'</td>
					<td><input type="checkbox" id="import_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td> </td>
					<td> </td>
				</tr>
				<tr>
					<td>'.$locate->translate('statisic').'</td>
					<td><input type="checkbox" id="surveyresult_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td> </td>
					<td> </td>
				</tr>
				<tr>
					<td>'.$locate->translate('extension').'</td>
					<td><input type="checkbox" id="account_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td><input type="checkbox" id="account_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="account_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('customer').'</td>
					<td><input type="checkbox" id="customer_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td><input type="checkbox" id="customer_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="customer_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('dialer').'</td>
					<td><input type="checkbox" id="predictivedialer_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td> </td>
					<td> </td>
				</tr>
				<tr>
					<td>'.$locate->translate('system').'</td>
					<td><input type="checkbox" id="systemstatus_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td> </td>
					<td> </td>
				</tr>
				<tr>
					<td>'.$locate->translate('survey').'</td>
					<td><input type="checkbox" id="survey_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td><input type="checkbox" id="survey_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="survey_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('dial_list').'</td>
					<td><input type="checkbox" id="diallist_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td><input type="checkbox" id="diallist_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="diallist_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('preference').'</td>
					<td><input type="checkbox" id="preferences_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td> </td>
					<td> </td>
				</tr>
				<tr>
					<td>'.$locate->translate('trunkinfo').'</td>
					<td><input type="checkbox" id="trunkinfo_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td><input type="checkbox" id="trunkinfo_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="trunkinfo_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('cdr').'</td>
					<td><input type="checkbox" id="cdr_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td><input type="checkbox" id="cdr_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="cdr_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('speeddial').'</td>
					<td><input type="checkbox" id="speeddial_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td><input type="checkbox" id="speeddial_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="speeddial_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('report').'</td>
					<td><input type="checkbox" id="report_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td><input type="checkbox" id="report_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="report_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('queuestatus').'</td>
					<td><input type="checkbox" id="queuestatus_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td> </td>
					<td> </td>
				</tr>
				<tr>
					<td>'.$locate->translate('agentsettings').'</td>
					<td><input type="checkbox" id="agent_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="agent_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="agent_delete" name="ckdelete[]" onclick="singleDelChk(this)" />
					</td>
				</tr>
				<tr>
					<td>'.$locate->translate('knowledge').'</td>
					<td><input type="checkbox" id="knowledge_view" name="ckview[]" onclick="singleViewChk(this)" /></td>
					<td><input type="checkbox" id="knowledge_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="knowledge_delete" name="ckdelete[]" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('dnc_list').'</td>
					<td><input type="checkbox" id="dnc_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="dnc_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="dnc_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('ticketcategory').'</td>
					<td><input type="checkbox" id="ticketcategory_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="ticketcategory_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="ticketcategory_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('useronline').'</td>
					<td><input type="checkbox" id="useronline_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td> </td>
					<td> </td>
				</tr>
				<tr>
					<td>'.$locate->translate('useronline').'</td>
					<td><input type="checkbox" id="user_online_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td> </td>
					<td> </td>
				</tr>
				<tr>
					<td>'.$locate->translate('codes').'</td>
					<td><input type="checkbox" id="codes_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="codes_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="codes_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('sms_templates').'</td>
					<td>
						<input type="checkbox" id="sms_templates_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="sms_templates_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="sms_templates_delete" name="ckdelete[]" onclick="singleDelChk(this)" />
					</td>
				</tr>
				<tr>
					<td>'.$locate->translate('sms_sents').'</td>
					<td><input type="checkbox" id="sms_sents_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="sms_sents_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="sms_sents_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('contact').'</td>
					<td><input type="checkbox" id="contact_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="contact_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="contact_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('note').'</td>
					<td><input type="checkbox" id="note_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="note_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="note_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('customer_leads').'</td>
					<td><input type="checkbox" id="customer_leads_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="customer_leads_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="customer_leads_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('note_leads').'</td>
					<td><input type="checkbox" id="note_leads_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="note_leads_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="note_leads_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('dialedlist').'</td>
					<td><input type="checkbox" id="dialedlist_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="dialedlist_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="dialedlist_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('campaign').'</td>
					<td><input type="checkbox" id="campaign_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="campaign_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="campaign_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('campaignresult').'</td>
					<td><input type="checkbox" id="campaignresult_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="campaignresult_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="campaignresult_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('worktimepackages').'</td>
					<td><input type="checkbox" id="worktimepackages_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="worktimepackages_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="worktimepackages_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('worktime').'</td>
					<td><input type="checkbox" id="worktime_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="worktime_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="worktime_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
				<tr>
					<td>'.$locate->translate('ticket_details').'</td>
					<td><input type="checkbox" id="ticket_details_view" name="ckview[]" onclick="singleViewChk(this)" /> </td>
					<td><input type="checkbox" id="ticket_details_edit" name="ckedit[]" onclick="singleEditChk(this)" /></td>
					<td><input type="checkbox" id="ticket_details_delete" name="ckdelete[]" onclick="singleDelChk(this)" /></td>
				</tr>
			</table>';
		return $pageHtml;
	}
}
?>
