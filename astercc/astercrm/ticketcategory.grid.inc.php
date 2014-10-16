<?php
/*******************************************************************************
* ticketcategory.grid.inc.php
* ticketcategory操作类
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
require_once 'ticketcategory.common.php';
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
		
		$sql = "SELECT tickets.*, groupname, campaignname,ticketcategory.ticketname AS parentname FROM tickets LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = tickets.groupid LEFT JOIN campaign ON campaign.id = tickets.campaignid LEFT JOIN tickets AS ticketcategory ON ticketcategory.id=tickets.fid";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE tickets.groupid = ".$_SESSION['curuser']['groupid']."";
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
				if($filter[$i] == 'parentname') {
					$filter[$i] = 'ticketcategory.ticketname';
				} else if($filter[$i] == 'groupname'){
					$filter[$i] = 'groupname';
				} else if($filter[$i] == 'campaignname') {
					$filter[$i] = 'campaignname';
				} else {
					$filter[$i] = 'tickets.'.$filter[$i];
				}
				$joinstr.="AND $filter[$i] like '%".$value."%' ";
			}
			$i++;
		}

		$sql = "SELECT tickets.*, groupname, campaignname,ticketcategory.ticketname as parentname FROM tickets LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = tickets.groupid LEFT JOIN campaign ON campaign.id = tickets.campaignid LEFT JOIN tickets AS ticketcategory ON ticketcategory.id=tickets.fid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1";
		}else{
			$sql .= " tickets.groupid = ".$_SESSION['curuser']['groupid']."";
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
			$sql = " SELECT COUNT(*) FROM tickets LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = tickets.groupid LEFT JOIN campaign ON campaign.id = tickets.campaignid LEFT JOIN tickets AS ticketcategory ON ticketcategory.id=tickets.fid";
		}else{
			$sql = " SELECT COUNT(*) FROM tickets LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = tickets.groupid LEFT JOIN campaign ON campaign.id = tickets.campaignid LEFT JOIN tickets AS ticketcategory ON ticketcategory.id=tickets.fid WHERE tickets.groupid = ".$_SESSION['curuser']['groupid']."";
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
				if($filter[$i] == 'parentname') {
					$filter[$i] = 'ticketcategory.ticketname';
				} else if($filter[$i] == 'groupname'){
					$filter[$i] = 'groupname';
				} else if($filter[$i] == 'campaignname') {
					$filter[$i] = 'campaignname';
				} else {
					$filter[$i] = 'tickets.'.$filter[$i];
				}
				$joinstr.="AND $filter[$i] like '%".$value."%' ";
			}
			$i++;
		}

		$sql = "SELECT COUNT(*) FROM tickets LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = tickets.groupid LEFT JOIN  campaign ON campaign.id = tickets.campaignid LEFT JOIN tickets AS ticketcategory ON ticketcategory.id=tickets.fid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 AND ";
		}else{
			$sql .= " tickets.groupid = ".$_SESSION['curuser']['groupid']." AND";
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
		$joinstr = Customer::createSqlWithStype($filter,$content,$stype,'tickets');

		$sql = "SELECT COUNT(*) FROM tickets LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = tickets.groupid LEFT JOIN campaign ON campaign.id = tickets.campaignid LEFT JOIN tickets AS ticketcategory ON ticketcategory.id=tickets.fid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 AND ";
		}else{
			$sql .= " tickets.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

		$joinstr = Customer::createSqlWithStype($filter,$content,$stype,'astercrm_account');

		$sql = "SELECT tickets.*, groupname, campaignname,ticketcategory.ticketname AS parentname FROM tickets LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = tickets.groupid LEFT JOIN campaign ON campaign.id = tickets.campaignid LEFT JOIN tickets AS ticketcategory ON ticketcategory.id=tickets.fid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " tickets.groupid = ".$_SESSION['curuser']['groupid']." ";
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
	*  create a 'where string' with 'like,<,>,=' assign by stype
	*
	*	@param $stype		(array)		assign search type
	*	@param $filter		(array) 	filter in sql
	*	@param $content		(array)		content in sql
	*	@return $joinstr	(string)	sql where string
	*/
	function createSqlWithStype($filter,$content,$stype=array(),$table='',$option='search'){
		$i=0;
		$joinstr='';
		foreach($stype as $type){
			$content[$i] = preg_replace("/'/","\\'",$content[$i]);
			if($filter[$i] != '' && trim($content[$i]) != ''){
				if($filter[$i] == 'parentname') {
					$filter[$i] = 'ticketcategory.ticketname';
				} else if($filter[$i] == 'groupname'){
					$filter[$i] = 'groupname';
				} else if($filter[$i] == 'campaignname') {
					$filter[$i] = 'campaignname';
				} else {
					$filter[$i] = 'tickets.'.$filter[$i];
				}
				if($type == "equal"){
					$joinstr.="AND $filter[$i] = '".trim($content[$i])."' ";
				}elseif($type == "more"){
					$joinstr.="AND $filter[$i] > '".trim($content[$i])."' ";
				}elseif($type == "less"){
					$joinstr.="AND $filter[$i] < '".trim($content[$i])."' ";
				}else{
					$joinstr.="AND $filter[$i] like '%".trim($content[$i])."%' ";
				}
			}
			$i++;
		}
		return $joinstr;
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
			$groupoptions = '<select name="groupid" id="groupid" onchange="javascript:relateByGid(this.value);return false;"><option value="0">'.$locate->Translate('None').'</option>';
			$res = Customer::getGroups();
			while ($row = $res->fetchRow()) {
				$groupoptions .= '<option value="'.$row['groupid'].'"';
				$groupoptions .='>'.$row['groupname'].'</option>';
			}
			$groupoptions .= '</select>';
		}else{
			$groupoptions .= '<input type="hidden" value="'.$_SESSION['curuser']['groupid'].'" name="groupid" id="groupid" />'.$_SESSION['curuser']['group']['groupname'];
		}

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Ticket Name").'*</td>
					<td><input type="text" id="ticketname" name="ticketname" size="25" maxlength="100"></td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Group Name").'</td>
					<td>'.$groupoptions.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Campaign Name").'</td>
					<td id="campaignMsg">'.$campaignOption.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Parent Category").'</td>
					<td align="left" id="parentMsg">'.$parentOption.'</td>
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
	*	get information from table campaign by campaign id
	*	@param $groupid		(int)	default 0		group's id
	*			$currentId	(int)	default 0		current record's fid
	*			$Cid		(int)	default 0		current record's id
	*	@return $res	(string)	return the result by query 
	*/
	function getParentCateGory($groupid=0,$currentId=0,$Cid=0) {
		global $db,$locate;
		$html = '<select id="fid" name="fid"><option value="0">'.$locate->Translate("None").'</option>';
		$sql = "SELECT * FROM tickets WHERE fid=0 AND groupid=$groupid";
		if($Cid != 0) {
			$sql .= " AND id != $Cid";
		}
		
		astercrm::events($sql);
		$result = & $db->query($sql);
		
		while($row = $result->fetchRow()) {
			$html .= '<option value="'.$row['id'].'"';
			if($currentId != 0 && $row['id'] == $currentId) {
				$html .= ' selected';
			}
			$html .= '>'.$row['ticketname'].'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	function getCampaign() {
		global $db;
		$sql = "SELECT * FROM campaign";
		astercrm::events($sql);
		$res = & $db->query($sql);
		return $res;
	}

	function insertNewTicket($f) {
		global $db;
		$sql = "INSERT INTO tickets SET"
			 ." ticketname='".$f['ticketname']."',"
			 ." campaignid=".$f['campaignid'].","
			 ." groupid=".$f['groupid'].","
			 ." fid=".$f['fid'].","
			 ." cretime=now(),"
			 ." creby='".$_SESSION['curuser']['username']."' ;";
		astercrm::events($sql);
		$res = & $db->query($sql);
		return $res;
	}

	function getParentCategoryByID($fid) {
		global $db;
		$sql = "SELECT ticketname FROM tickets WHERE id=$fid";
		astercrm::events($sql);
		$res = & $db->getOne($sql);
		return $res;
	}

	/**
	*  Imprime la forma para editar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser editado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma con los datos 
	*									a extraidos de la base de datos para ser editados 
	*/
	
	function showTicketDetail($id){
		global $locate;
		$ticket =& Customer::getRecordByID($id,'tickets');
		$group = & Customer::getGroupByID($ticket['groupid']);
		$campaign = & Customer::getCampaignByID($ticket['campaignid']);
		$parentname = & Customer::getParentCategoryByID($ticket['fid']);
		
		if(empty($campaign)) {
			$campaign['campaignname'] = '';
		}
		$html = '
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Ticket Name").'</td>
					<td align="left">'.$ticket['ticketname'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group Name").'</td>
					<td align="left">'.$group['groupname'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Campaign Name").'</td>
					<td align="left">'.$campaign['campaignname'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Parent Category").'</td>
					<td align="left">'.$parentname.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Creby").'</td>
					<td align="left">'.$ticket['creby'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Cretime").'</td>
					<td align="left">'.$ticket['cretime'].'</td>
				</tr>
			</table>
			';
		return $html;
	}

	/**
	*	get information from table campaign by campaign id
	*	@param $id		(int)		campaign's id
	*	@return $res	(string)	return the result by query 
	*/

	function getCampaignByID($id) {
		global $db;
		$sql = "SELECT * FROM campaign WHERE id=$id ;";
		astercrm::events($sql);
		$res =& $db->getRow($sql);
		return $res;
	}

	/**
	*	get information from table campaign by campaign id
	*	@param $gid		(int)	default 0		group's id
	*			$cid	(int)	default 0		current record's campaignid
	*	@return $res	(string)	return the result by query 
	*/
	function getCampaignByGid($gid=0,$cid=0) {
		global $db,$locate;
		$campaignHtml = '<select id="campaignid" name="campaignid"><option value="0">'.$locate->Translate("None").'</option>';
		
		$sql = "select * from campaign where groupid=$gid ;";
		astercrm::events($sql);
		$res = & $db->query($sql);
		
		while($rows = $res->fetchRow()) {
			$campaignHtml .= '<option value="'.$rows['id'].'"';
			if($cid != 0 && $rows['id'] == $cid) {
				$campaignHtml .= ' selected';
			}
			$campaignHtml .= '>'.$rows['campaignname'].'</option>';
		}
		$campaignHtml .= '</select>';
		return $campaignHtml;
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
		$tickets =& Customer::getRecordByID($id,'tickets');
		
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$grouphtml = '<select name="groupid" id="groupid" onchange="javascript:relateByGid(this.value,document.getElementById(\'id\').value);return false;"><option value="0">'.$locate->Translate("None").'</option>';
			$res = Customer::getGroups();
			while ($row = $res->fetchRow()) {
				$grouphtml .= '<option value="'.$row['groupid'].'"';
				if($row['groupid'] == $tickets['groupid']){
					$grouphtml .= ' selected ';
				}
				$grouphtml .= '>'.$row['groupname'].'</option>';
			}
			$grouphtml .= '</select>';
		}else{
			$grouphtml = '<input type="hidden" name="groupid" id="groupid" value="'.$_SESSION['curuser']['groupid'].'" />'.$_SESSION['curuser']['group']['groupname'];
		}

		$campaignOption = Customer::getCampaignByGid($tickets['groupid'],$tickets['campaignid']);
		$parentOption = Customer::getParentCateGory($tickets['groupid'],$tickets['fid'],$tickets['id']);

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Ticket Name").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $tickets['id'].'"><input type="text" id="ticketname" name="ticketname" size="25" maxlength="100" value="'.$tickets['ticketname'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group Name").'</td>
					<td align="left">'.$grouphtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Campaign Name").'</td>
					<td align="left" id="campaignMsg">'.$campaignOption.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Parent Category").'</td>
					<td align="left" id="parentMsg">'.$parentOption.'</td>
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
	*  Actualiza un registro de la tabla.
	*
	*	@param $f	(array)		Arreglo que contiene los datos del formulario pasado.
	*	@return $res	(object)	Devuelve el objeto con la respuesta de la sentencia SQL ejecutada del UPDATE.
	*/
	
	function updateTCategoryRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "UPDATE tickets SET "
				."ticketname='".$f['ticketname']."', "
				."campaignid='".$f['campaignid']."', "
				."groupid='".$f['groupid']."', "
				."fid=".$f['fid']." "
				."WHERE id='".$f['id']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}
}
?>