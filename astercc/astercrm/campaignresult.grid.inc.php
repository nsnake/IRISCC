<?php
/*******************************************************************************
* campaignresult.grid.inc.php
* campaignresult操作类
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
require_once 'campaignresult.common.php';
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
		
		$sql = "SELECT campaignresult.*, groupname, campaign.campaignname AS campaignname, presult.resultname AS parentresult FROM campaignresult LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaignresult.groupid LEFT JOIN campaign ON campaign.id = campaignresult.campaignid LEFT JOIN campaignresult AS presult ON presult.id = campaignresult.parentid ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE campaignresult.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

//		if ($creby != null)
//			$sql .= " WHERE note.creby = '".$_SESSION['curuser']['username']."' ";
			

		if($order == null){
			$sql .= " LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit";
		}

		//echo $sql;exit;
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
			$sql = "SELECT * FROM campaign"
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
			$value = preg_replace("/'/","\\'",$value);
			$value=trim($value);
			if (strlen($value)!=0 && strlen($filter[$i]) != 0){
				$joinstr.="AND $filter[$i] like '%".$value."%' ";
			}
			$i++;
		}

		$sql = "SELECT campaign.*, groupname, servers.name as servername, FROM campaign LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaign.groupid LEFT JOIN servers ON servers.id = campaign.serverid  WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " campaignresult.groupid = ".$_SESSION['curuser']['groupid']." ";
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

	function insertNewCampaignResult($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$bindqueue = 0;
		if ($f['bindqueue'] =="on"){
			$bindqueue = 1;
		}

		$query= "INSERT INTO campaignresult SET "
				."resultname='".$f['resultname']."', "
				."resultnote='".$f['resultnote']."', "
				."status='".$f['status']."', "
				."campaignid='".$f['campaignid']."', "				
				."parentid='".$f['parentid']."', "
				."groupid='".$f['groupid']."', "
				."creby = '".$_SESSION['curuser']['username']."',"
				."cretime = now()";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}


	function updateCampaignResultRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$bindqueue = 0;
		if ($f['bindqueue'] =="on"){
			$bindqueue = 1;
		}

		$query= "UPDATE campaignresult SET "
				."resultname='".$f['resultname']."', "
				."resultnote='".$f['resultnote']."', "
				."status='".$f['status']."', "	
				."campaignid='".$f['campaignid']."', "
				."parentid='".$f['parentid']."', "				
				."groupid='".$f['groupid']."' "
				."WHERE id=".$f['id'];
		astercrm::events($query);
//		echo $query;exit;
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
			$sql = " SELECT COUNT(*) FROM campaignresult LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaignresult.groupid  LEFT JOIN campaignresult AS presult ON presult.id = campaignresult.parentid";
		}else{
			$sql = " SELECT COUNT(*) FROM campaignresult LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaignresult.groupid  LEFT JOIN campaignresult AS presult ON presult.id = campaignresult.parentid WHERE campaignresult.groupid = ".$_SESSION['curuser']['groupid']." ";
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
				$value = preg_replace("/'/","\\'",$value);
				$value=trim($value);
				if (strlen($value)!=0 && strlen($filter[$i]) != 0){
					$joinstr.="AND $filter[$i] like '%".$value."%' ";
				}
				$i++;
			}

			$sql = "SELECT COUNT(*) FROM campaign LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaign.groupid LEFT JOIN servers ON servers.id = campaign.serverid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " campaignresult.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table,$fields='',$option=''){
		global $db;
		$fieldstr = '';
		if(is_array($fields)){
			foreach($fields as $field){
				$fieldstr .= ' '.$field.',';
			}
		}

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"campaignresult");
		if($fieldstr != ''){
			$fieldstr = rtrim($fieldstr,',');
			$sql = "SELECT $fieldstr FROM campaignresult LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaignresult.groupid LEFT JOIN campaign ON campaign.id = campaignresult.campaignid  LEFT JOIN campaignresult AS presult ON presult.id = campaignresult.parentid WHERE ";
		}else{
			$sql = "SELECT campaignresult.*, groupname, campaign.campaignname AS campaignname,presult.resultname AS parentresult FROM campaignresult LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaignresult.groupid LEFT JOIN campaign ON campaign.id = campaignresult.campaignid  LEFT JOIN campaignresult AS presult ON presult.id = campaignresult.parentid WHERE ";
		}
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " campaignresult.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";					
		}
		if($option == ''){
			$sql .= " ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}elseif($option == 'export'){
			return $sql;
		}

		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"campaign");

			$sql = "SELECT COUNT(*) FROM campaignresult LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaignresult.groupid LEFT JOIN campaign ON campaign.id = campaignresult.campaignid LEFT JOIN campaignresult AS presult ON presult.id = campaignresult.parentid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " campaignresult.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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
				$grouphtml .= '<select name="groupid" id="groupid" onchange="setCampaign();">';
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
					<td nowrap align="left">'.$locate->Translate("Result Name").'*</td>
					<td align="left"><input type="text" id="resultname" name="resultname" size="30" maxlength="60"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Result Note").'</td>
					<td align="left"><input type="text" id="resultnote" name="resultnote" size="30" maxlength="255"></td>
				</tr>
				<tr>					
					<td align="left" colspan="2">'.$locate->Translate("ANSWERED").'&nbsp;<input type="radio" id="status" name="status" value="ANSWERED" checked>&nbsp;'.$locate->Translate("NOANSWER").'&nbsp;<input type="radio" id="status" name="status" value="NOANSWER" ></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'</td>
					<td align="left">'.$grouphtml.'</td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Campaign Name").'</td>
					<td><SELECT id="campaignid" name="campaignid" onchange="setParentResult();"></SELECT></td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Parent Result Name").'</td>
					<td><SELECT id="parentid" name="parentid" ></SELECT></td>
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
		$campaignresult =& Customer::getRecordByID($id,'campaignresult');

		if ($_SESSION['curuser']['usertype'] == 'admin'){ 
				$grouphtml .=	'<select name="groupid" id="groupid" onchange="setCampaign();">
																<option value=""></option>';
				$res = Customer::getGroups();
				while ($row = $res->fetchRow()) {
					$grouphtml .= '<option value="'.$row['groupid'].'"';
					if($row['groupid'] == $campaignresult['groupid']){
						$grouphtml .= ' selected ';
					}
					$grouphtml .= '>'.$row['groupname'].'</option>';
				}
				$grouphtml .= '</select>';
		}else{
				
				$grouphtml .= $_SESSION['curuser']['group']['groupname'].'<input type="hidden" name="groupid" id="groupid" value="'.$_SESSION['curuser']['groupid'].'">';
		}
		$statusAnswered = "";
		$statusNoanswer = "";
		if ($campaignresult['status'] == 'ANSWERED'){
			$statusAnswered = "checked";
		}else{
			$statusNoanswer = "checked";
		}

			
		$campaign_res = Customer::getRecordsByGroupid($campaignresult['groupid'],"campaign");
		while ($campaign_row = $campaign_res->fetchRow()) {
			$campaignoption .= '<option value="'.$campaign_row['id'].'"';
			if($campaign_row['id'] == $campaignresult['campaignid']){
				$campaignoption .= ' selected ';
			}
			$campaignoption .= '>'.$campaign_row['campaignname'].'</option>';
		}

		$parentoption .= '<option value="0"';
		if($campaignresult['parentid'] == 0){
			$parentoption .= ' selected ';
		}
		$parentoption .= '>'.$locate->Translate("None").'</option>';

		$parent_res = Customer::getRecordsByField('campaignid', $campaignresult['campaignid'],'campaignresult');		
		while ($parent_row = $parent_res->fetchRow()) {
			if($parent_row['parentid'] == 0){
				$parentoption .= '<option value="'.$parent_row['id'].'"';
				if($parent_row['id'] == $campaignresult['parentid']){
					$parentoption .= ' selected ';
				}
				$parentoption .= '>'.$parent_row['resultname'].'</option>';
			}
		}

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Result Name").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $campaignresult['id'].'"><input type="text" id="resultname" name="resultname" size="30" maxlength="60" value="'.$campaignresult['resultname'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Result Note").'</td>				<td align="left"><input type="text" id="resultnote" name="resultnote" size="30" maxlength="255" value="'.$campaignresult['resultnote'].'"></td>
				</tr>
				<tr>					
					<td align="left" colspan="2">'.$locate->Translate("Answered").'&nbsp;
					<input type="radio" id="status" name="status" value="Answered" '.$statusAnswered.'>&nbsp;'.$locate->Translate("Noanswer").'&nbsp;
					<input type="radio" id="status" name="status" value="Noanswer" '.$statusNoanswer.'>
					</td>
				</tr>				
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'</td>
					<td align="left">'.$grouphtml.'</td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Campaign Name").'</td>
					<td><SELECT id="campaignid" name="campaignid" onchange="setParentResult();">'.$campaignoption.'</SELECT></td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Parent Result Name").'</td>
					<td><SELECT id="parentid" name="parentid" >'.$parentoption.'</SELECT></td>
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
