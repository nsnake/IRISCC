<?php
/*******************************************************************************
* sms_templates.grid.inc.php
* sms_templates操作类
* Customer class

* @author			solo.fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Aug 2010

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加account表单的HTML
	formEdit					生成编辑account表单的HTML
	新增 getRecordsFilteredMore  用于获得多条件搜索记录集
	新增 getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'sms_templates.common.php';
require_once 'include/astercrm.class.php';

class Customer extends astercrm {
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
		
		$sql = "SELECT sms_templates.*,campaign.campaignname,trunkinfo.trunkname FROM sms_templates LEFT JOIN campaign ON campaign.id = sms_templates.campaign_id LEFT JOIN trunkinfo ON trunkinfo.id = sms_templates.trunkinfo_id ";
			
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
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);//<---- change by your function

		$sql = "SELECT sms_templates.*,campaign.campaign.campaignname,trunkinfo.trunkname FROM sms_templates LEFT JOIN campaign ON campaign.id = sms_templates.campaign_id LEFT JOIN trunkinfo ON trunkinfo.id = sms_templates.trunkinfo_id WHERE 1 ";
		
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
		$sql = " SELECT COUNT(*) FROM sms_templates LEFT JOIN campaign ON campaign.id = sms_templates.campaign_id LEFT JOIN trunkinfo ON trunkinfo.id = sms_templates.trunkinfo_id ";
		
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getNumRowsMore($filter = null, $content = null){
		global $db;
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);//<---- change by your function

		$sql = "SELECT COUNT(*) FROM sms_templates LEFT JOIN campaign ON campaign.id = sms_templates.campaign_id LEFT JOIN trunkinfo ON trunkinfo.id = sms_templates.trunkinfo_id WHERE 1 ";

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr;
		}else {
			$sql .= " 1";
		}
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);//<---- change by your function

		$sql = "SELECT COUNT(*) FROM sms_templates LEFT JOIN campaign ON campaign.id = sms_templates.campaign_id LEFT JOIN trunkinfo ON trunkinfo.id = sms_templates.trunkinfo_id WHERE 1 ";
		
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr;
		}else {
			$sql .= " 1";
		}
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;		
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);//<---- change by your function

		$sql = "SELECT sms_templates.*,campaign.campaignname,trunkinfo.trunkname FROM sms_templates LEFT JOIN campaign ON campaign.id = sms_templates.campaign_id LEFT JOIN trunkinfo ON trunkinfo.id = sms_templates.trunkinfo_id WHERE 1 ";
		
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
					<td nowrap align="left">'.$locate->Translate("Template Title").'*</td>
					<td align="left"><input type="text" id="templatetitle" name="templatetitle" /></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Belong To").'</td>
					<td><select id="belongto" name="belongto" onchange="xajax_getCurObjId(this.value);">
						<option value="all">'.$locate->Translate("All").'</option>
						<option value="campaign">'.$locate->Translate("Campaign").'</option>
						<option value="trunk">'.$locate->Translate("Trunk").'</option>
					</select></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Object").'</td>
					<td id="objectSelect"><select id="object_id" name="object_id"></select></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Is_edit").'</td>
					<td>
						<input type="checkbox" id="is_edit" name="is_edit" checked/> 
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Content").'</td>
					<td id="objectSelect">
						<textarea id="content" name="content" cols="50" rows="8"></textarea>
					</td>
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

	function getCurObjId($curType,$curObjId){
		global $db;
		if($curType == 'campaign') {
			$table = 'campaign';
			$fieldname = 'campaignname';
		} else if($curType == 'trunk') {
			$table = 'trunkinfo';
			$fieldname = 'trunkname';
		}
		$sql = "SELECT * FROM $table ";
		astercrm::events($sql);
		$result = & $db->query($sql);

		$optionHtml = '<select id="object_id" name="object_id">';
		while($row = $result->fetchRow()) {
			$optionHtml .= '<option value="'.$row['id'].'"';
			if($curObjId == $row['id']){
				$optionHtml .= ' selected';
			}
			$optionHtml .= '>'.$row[$fieldname].'</option>';
		}
		$optionHtml .= "</select>";
		return $optionHtml;
	}

	/**
	*  Imprime la forma para editar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser editado.
	*	@return $html	(string)	Devuelve una cadena de caracteres que contiene la forma con los datos 
	*								a extraidos de la base de datos para ser editados 
	*/
	function formEdit($id){
		global $locate;
		$result =& Customer::getRecordByID($id,'sms_templates');
		$optionHtml = '';
		if($result['belongto'] == 'campaign') {
			$optionHtml = Customer::getCurObjId($result['belongto'],$result['campaign_id']);
		} else if($result['belongto'] == 'trunk') {
			$optionHtml = Customer::getCurObjId($result['belongto'],$result['trunkinfo_id']);
		}

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Template Title").'*</td>
					<td align="left"><input type="text" id="templatetitle" name="templatetitle" value="'.$result['templatetitle'].'" /><input type="hidden" id="id" name="id" value="'.$result['id'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Belong To").'</td>
					<td><select id="belongto" name="belongto" onchange="xajax_getCurObjId(this.value);">
						<option value="all"';
						if($result['belongto'] == 'all'){$html .= ' selected';}
						$html .='>'.$locate->Translate("All").'</option>
						<option value="campaign"';
						if($result['belongto'] == 'campaign'){$html .= ' selected';}
						$html .='>'.$locate->Translate("Campaign").'</option>
						<option value="trunk"';
						if($result['belongto'] == 'trunk'){$html .= ' selected';}
						$html .='>'.$locate->Translate("Trunk").'</option>
					</select></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Object").'</td>
					<td id="objectSelect">'.$optionHtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Is_edit").'</td>
					<td>';
						if($result['is_edit'] == 'yes') {
							$html .= '<input type="checkbox" id="is_edit" name="is_edit" checked/>';
						}else{
							$html .= '<input type="checkbox" id="is_edit" name="is_edit"/>';
						}
					$html .= '</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Content").'</td>
					<td><textarea id="content" name="content" cols="40" rows="5">'.$result['content'].'</textarea></td>
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
		
	function insertSmsTemplates($f){
		global $db;
		if($f['belongto'] == 'all') {
			$campaignId = 0;
			$trunkId = 0;
		} else if($f['belongto'] == 'campaign') {
			$campaignId = $f['object_id'];
			$trunkId = 0;
		} else {
			$trunkId = $f['object_id'];
			$campaignId = 0;
		}

		if($f['is_edit'] == 'on') {
			$f['is_edit'] = 'yes';
		} else {
			$f['is_edit'] = 'no';
		}

		$sql = "INSERT INTO sms_templates SET 
				`templatetitle`='".$f['templatetitle']."',
				`belongto`='".$f['belongto']."',
				`campaign_id`=".$campaignId.",
				`trunkinfo_id`=".$trunkId.",
				`content`='".$f['content']."',
				`is_edit`='".$f['is_edit']."',
				`cretime`=now();
			";
		astercrm::events($sql);
		$result = & $db->query($sql);
		return $result;
	}

	function updateSmsTemplates($f){
		global $db;
		if($f['belongto'] == 'all') {
			$campaignId = 0;
			$trunkId = 0;
		} else if($f['belongto'] == 'campaign') {
			$campaignId = $f['object_id'];
			$trunkId = 0;
		} else {
			$trunkId = $f['object_id'];
			$campaignId = 0;
		}
		if($f['is_edit'] == 'on') {
			$f['is_edit'] = 'yes';
		} else {
			$f['is_edit'] = 'no';
		}
		$sql = "UPDATE sms_templates SET 
				`templatetitle`='".$f['templatetitle']."',
				`belongto`='".$f['belongto']."',
				`campaign_id`=".$campaignId.",
				`trunkinfo_id`=".$trunkId.",
				`content`='".$f['content']."',
				`is_edit`='".$f['is_edit']."'
				 WHERE id=".$f['id'].";
			";
		astercrm::events($sql);
		$result = & $db->query($sql);
		return $result;
	}
}
?>