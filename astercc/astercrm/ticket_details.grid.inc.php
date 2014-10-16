<?php
/*******************************************************************************
* ticket_details.grid.inc.php
* ticket_details操作类
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
require_once 'ticket_details.common.php';
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
		
		$sql = "SELECT ticket_details.*,ticketcategory.ticketname as ticketcategoryname,tickets.ticketname as ticketname,AccountGroup.groupname as groupname, customer,username FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_details.groupid ";
		
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE ticket_details.groupid = '".$_SESSION['curuser']['groupid']."' ";
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
		$joinstr = Customer::createSqlWithStype($filter,$content,$stype,'ticket_details');//<---- change by your function

		$sql = "SELECT ticket_details.*,ticketcategory.ticketname as ticketcategoryname,tickets.ticketname as ticketname,AccountGroup.groupname as groupname, customer,username FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_details.groupid  WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1";
		}else{
			$sql .= " ticket_details.groupid = '".$_SESSION['curuser']['groupid']."' ";
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
			$sql = " SELECT COUNT(*) FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_details.groupid ";
		}else{
			$sql = " SELECT COUNT(*) FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_details.groupid  WHERE ticket_details.groupid = '".$_SESSION['curuser']['groupid']."'";
		}
		
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getNumRowsMore($filter = null, $content = null){
		global $db;
		$joinstr = Customer::createSqlWithStype($filter,$content,$stype,'ticket_details');//<---- change by your function

		$sql = "SELECT COUNT(*) FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_details.groupid  WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1";
		}else{
			$sql .= " ticket_details.groupid='".$_SESSION['curuser']['groupid']."'";
		}

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
		
		$joinstr = Customer::createSqlWithStype($filter,$content,$stype,'ticket_details');//<---- change by your function

		$sql = "SELECT COUNT(*) FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_details.groupid  WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1";
		}else{
			$sql .= " ticket_details.groupid = '".$_SESSION['curuser']['groupid']."' ";
		}
		
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

		$joinstr = Customer::createSqlWithStype($filter,$content,$stype,'ticket_details');//<---- change by your function

		$sql = "SELECT ticket_details.*,ticketcategory.ticketname as ticketcategoryname,AccountGroup.groupname as groupname,tickets.ticketname as ticketname, customer,username FROM ticket_details LEFT JOIN tickets AS ticketcategory ON ticketcategory.id = ticket_details.ticketcategoryid LEFT JOIN tickets AS tickets ON tickets.id = ticket_details.ticketid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_details.groupid  WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1";
		}else{
			$sql .= " ticket_details.groupid = '".$_SESSION['curuser']['groupid']."' ";
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
				if($filter[$i] == 'ticketcategoryname') {
					$filter[$i] = 'ticketcategory.ticketname';
				} else if($filter[$i] == 'ticketname') {
					$filter[$i] = 'tickets.ticketname';
				} else if($filter[$i] == 'groupname') {
					$filter[$i] = 'AccountGroup.groupname';
				} else if($filter[$i] == 'creby') {
					$filter[$i] = 'ticket_details.creby';
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
		$categoryHtml = Customer::getTicketCategory();
		//$customerHtml = Customer::getCustomer();
		//$accountHtml = Customer::getAccount();

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("TicketCategory Name").'*</td>
					<td align="left">'.$categoryHtml.'</td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Ticket Name").'*</td>
					<td id="ticketMsg"></td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Parent TicketDetail ID").'</td>
					<td><input type="text" id="parent_id" name="parent_id" maxlength="8" /></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group Name").'</td>
					<td id="groupMsg"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Customer Name").'*</td>
					<td id="customerMsg"><input type="text" id="ticket_customer" name="ticket_customer" onkeyup="ajax_showOptions(this,\'getCustomersByLetters\',event)" size="25" maxlength="50" autocomplete="off" /><input type="hidden" id="ticket_customer_hidden" name="customerid" value="" /></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Assignto").'</td>
					<td id="accountMsg"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Status").'</td>
					<td><select id="status" name="status">
						<option value="new">'.$locate->Translate("new").'</option>
						<option value="panding">'.$locate->Translate("panding").'</option>
						<option value="closed">'.$locate->Translate("closed").'</option>
						<option value="cancel">'.$locate->Translate("cancel").'</option>
					</select></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Memo").'</td>
					<td><textarea id="memo" name="memo" cols="40" rows="5"></textarea></td>
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

	function insertTicketDetail($f) {
		global $db;
		$sql = "INSERT INTO ticket_details SET"
			 ." ticketcategoryid='".$f['ticketcategoryid']."',"
			 ." ticketid='".$f['ticketid']."',"
			 ."parent_id='".(($f['parent_id'] == '')?'':str_pad($f['parent_id'],8,'0',STR_PAD_LEFT))."',"
			 ." customerid='".$f['customerid']."',"
			 ." status='".$f['status']."',"
			 ." assignto='".$f['assignto']."',"
			 ." groupid='".$f['groupid']."',"
			 ." memo='".$f['memo']."',"
			 ." cretime=now(),"
			 ." creby='".$_SESSION['curuser']['username']."' ;";
		astercrm::events($sql);
		
		$res = & $db->query($sql);
		return $res;
	}

	/**
	*	get ticketcategory from table tickets 
	*	@param $CategoryId		(int)		tickets's id
	*	@return $html	(string)	create the option by the result of query
	*/
	function getTicketCategory($CategoryId = '') {
		global $db,$locate;
		if($CategoryId != 0) {
			$fsql = "SELECT groupid FROM tickets WHERE id=$CategoryId";
			$groupid = & $db->getOne($fsql);
		} else {
			$groupid = 0;
		}

		$sql = "SELECT * FROM tickets ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " WHERE fid=0";
		}else{
			$sql .= " WHERE fid=0 AND groupid IN(0,".$_SESSION['curuser']['groupid'].")";
		}
		astercrm::events($sql);
		$result = & $db->query($sql);

		$html = '<select id="ticketcategoryid" name="ticketcategoryid" onchange="relateBycategoryID(this.value);"><option value="0">'.$locate->Translate('please select').'</option>';
		while($row = $result->fetchRow()) {
			$html .= '<option value="'.$row['id'].'"';
			if($row['id'] == $CategoryId && $CategoryId != '') {
				$html .= ' selected';
			}
			$html .= '>'.$row['ticketname'].'</option>';
		}
		$html .= '</select>';
		return $html;
	}

	/**
	*	get tickets from table tickets by ticketcategoryid
	*	@param $fid		(int)		ticketcategory's id 
	*			$Cid	(int)		current ticket id (for edit)
	*	@return $html	(string)	create the option by the result of query
	*/
	function getTicketByCid($fid,$Cid=0) {
		global $db,$locate;
		$sql = "SELECT * FROM tickets";
		if($fid == 0) {
			$sql .= " WHERE fid=-1";
		} else {
			$sql .= " WHERE fid=$fid";
		}
		astercrm::events($sql);
		$result = & $db->query($sql);
		$html = '<select id="ticketid" name="ticketid">';
		$tmp = '';
		while($row = $result->fetchRow()) {
			$tmp .= '<option value="'.$row['id'].'"';
			if($Cid != 0 && $row['ticketid'] == $Cid) {
				$tmp .= ' selected ';
			}
			$tmp .= '>'.$row['ticketname'].'</option>';
		}
		if($tmp == '') {
			$html .= '<option value="0">'.$locate->Translate('please select').'</option>';
		} else {
			$html .= $tmp;
		}
		$html .= '</select>';
		return $html;
	}

	function getGroup($FticketId = 0,$curGroupid = 0){
		global $db,$locate;
		if($FticketId == 0){
			$sql = "SELECT * FROM astercrm_accountgroup ";
		} else {
			$tmpSql = "SELECT groupid FROM tickets WHERE id='".$FticketId."' ";
			$groupid = & $db->getOne($tmpSql);
			if($groupid == 0) {
				$tmpHtml = '<select id="groupid" name="groupid" onchange="relateByGroup(this.value)"><option value="0">'.$locate->Translate('please select').'</option></select>';
				return $tmpHtml;
			}
			$sql = "SELECT AccountGroup.id,AccountGroup.groupname FROM tickets AS Ticket LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = Ticket.groupid WHERE Ticket.id='".$FticketId."' ";
		}
		astercrm::events($sql);
		$result = & $db->query($sql);
		
		$html = '<select id="groupid" name="groupid" onchange="relateByGroup(this.value)">';
		$tmp = '';
		while($row = $result->fetchRow()){
			$tmp .= '<option value="'.$row['id'].'"';
			if($curGroupid != 0 && $row['id'] == $curGroupid){
				$tmp .= ' selected ';
			}
			$tmp .= '>'.$row['groupname'].'</option>';
		}
		$html .= $tmp.'</select>';
		return $html;
	}
	
	/**
	*	get customer from table customer
	*	@param $customerid	(int)	 default 0  (for edit)
	*	@return		$html	(string)	create the option by the result of query
	*/
	function getCustomer($groupid = 0,$customerid=0) {
		global $db,$locate;
		$sql = "select * from customer";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			if($groupid == 0){
				$sql .= " ";
			} else {
				$sql .= " WHERE groupid = ".$groupid." ";
			}
		}else{
			$sql .= " WHERE groupid = ".$_SESSION['curuser']['groupid']." ";
		}
		astercrm::events($sql);
		$result = & $db->query($sql);
		$html = '<select id="customerid" name="customerid">';
		$tmp = '';
		while($row = $result->fetchRow()) {
			$tmp .= '<option value="'.$row['id'].'"';
			if($customerid != 0 && $row['id'] == $customerid) {
				$tmp .= ' selected';
			}
			$tmp .= '>'.$row['customer'].'</option>';
		}
		if($tmp == '') {
			$html .= '<option value="0">'.$locate->Translate('please select').'</option>';
		} else {
			$html .= $tmp;
		}
		$html .= '</select>';
		return $html;
	}

	/**
	*	get account from table account
	*	@param	$accountid	(int) default 0  (for edit)
	*	@return		$html	(string)	create the option by the result of query
	*/
	function getAccount($groupid=0,$accountid =0) {
		global $db,$locate,$config;
		$sql = "SELECT * FROM astercrm_account where username!='admin'";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			if($config['system']['create_ticket'] == 'system') {
				$sql .= " ";
			} else {
				$sql .= " AND groupid=".$_SESSION['curuser']['groupid']." ";
			}
		}
		
		astercrm::events($sql);
		$result = & $db->query($sql);
		$html = '<select id="assignto" name="assignto"><option value="0">'.$locate->Translate('please select').'</option>';
		$tmp = '';
		while($row = $result->fetchRow()) {
			$tmp .= '<option value="'.$row['id'].'"';
			if($accountid != 0 && $row['id'] == $accountid) {
				$tmp .= ' selected';
			}
			$tmp .= '>'.$row['username'].'</option>';
		}
		if($tmp == '') {
			$html .= '<option value="0">'.$locate->Translate('please select').'</option>';
		} else {
			$html .= $tmp;
		}
		$html .= '</select>';
		return $html;
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
		$result =& Customer::getRecordByID($id,'ticket_details');
		$categoryHtml = Customer::getTicketCategory($result['ticketcategoryid']);
		$ticketHtml = Customer::getTicketByCid($result['ticketcategoryid'],$result['ticketid']);
		$groupHtml = Customer::getGroup($result['ticketcategoryid'],$result['groupid']);
		//$customerHtml = Customer::getCustomer($result['groupid'],$result['customerid']);
		$customername = Customer::getCustomername($result['customerid']);
		$accountHtml = Customer::getAccount($result['groupid'],$result['assignto']);
		//print_r($accountHtml);exit;
		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("TicketCategory Name").'*</td>
					<td align="left">'.$categoryHtml.'<input type="hidden" id="id" name="id" value="'.$result['id'].'"><input type="hidden" id="curTicketid" value="'.$result['ticketid'].'"></td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Ticket Name").'*</td>
					<td id="ticketMsg">'.$ticketHtml.'</td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Parent TicketDetail ID").'</td>
					<td><input type="text" id="parent_id" name="parent_id"  maxlength="8" value="'.(($result['parent_id']=='')?'':str_pad($result['parent_id'],8,'0',STR_PAD_LEFT)).'" /></td>
				</tr>
				<tr>
					<td align="left" width="25%">'.$locate->Translate("Group Name").'*</td>
					<td id="groupMsg">'.$groupHtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Customer Name").'*</td>
					<td id="customerMsg"><input type="text" id="ticket_customer" name="ticket_customer" value="'.$customername.'" onkeyup="ajax_showOptions(this,\'getCustomersByLetters\',event)" size="25" maxlength="50" autocomplete="off" /><input type="hidden" id="ticket_customer_hidden" name="customerid" value="'.$result['customerid'].'" /></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Assignto").'</td>
					<td id="accountMsg">'.$accountHtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Status").'</td>
					<td><select id="status" name="status">
						<option value="new"';
						if($result['status'] == 'new'){$html .= ' selected';}
						$html .='>'.$locate->Translate("new").'</option>
						<option value="panding"';
						if($result['status'] == 'panding'){$html .= ' selected';}
						$html .='>'.$locate->Translate("panding").'</option>
						<option value="closed"';
						if($result['status'] == 'closed'){$html .= ' selected';}
						$html .='>'.$locate->Translate("closed").'</option>
						<option value="cancel"';
						if($result['status'] == 'cancel'){$html .= ' selected';}
						$html .='>'.$locate->Translate("cancel").'</option>
					</select></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Memo").'</td>
					<td><textarea id="memo" name="memo" cols="40" rows="5">'.$result['memo'].'</textarea></td>
				</tr>
				<tr>
					<td colspan="2"><input type="button" id="" onclick="xajax_viewSubordinateTicket('.$result['id'].')" value="'.$locate->Translate("Subordinate TicketDetails").'">&nbsp;&nbsp;<button id="submitButton" onClick=\'xajax_update(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>
			 </table>
			';
		$html .= '
				</form>
				'.$locate->Translate("obligatory_fields").'
				';
		return $html;
	}

	function getCustomername($customerid){
		global $db,$locate;
		$sql = "SELECT customer FROM customer WHERE id='".$customerid."' ";
		astercrm::events($sql);
		$customername = & $db->getOne($sql);
		return $customername;
	}

	/**
	*  Actualiza un registro de la tabla.
	*
	*	@param $f	(array)		Arreglo que contiene los datos del formulario pasado.
	*	@return $res	(object)	Devuelve el objeto con la respuesta de la sentencia SQL ejecutada del UPDATE.
	*/
	
	function updateTicketDetail($f){
		global $db;
		$f = astercrm::variableFiler($f);

		
		
		$query= "UPDATE ticket_details SET "
				."ticketcategoryid=".$f['ticketcategoryid'].", "
				."ticketid=".$f['ticketid'].", "
				."parent_id='".(($f['parent_id'] == '')?'':str_pad($f['parent_id'],8,'0',STR_PAD_LEFT))."',"
				."customerid=".$f['customerid'].", "
				."assignto=".$f['assignto'].","
				."status='".$f['status']."', "
				."groupid=".$f['groupid'].","
				."memo='".$f['memo']."' "
				."WHERE id=".$f['id']."";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	//验证填写的上级ticket_details 是否存在
	function validParentTicketId($pid){
		global $db;
		$sql = "select * from ticket_details where id='".$pid."' ";
		astercrm::events($sql);
		$result =& $db->getOne($sql);
		if($result){
			return true;
		} else {
			return false;
		}
	}

	//查看下级的ticket_details
	function subordinateTicket($pid){
		global $db,$locate;
		$sql = "SELECT ticket_details.*,tickets.ticketname,astercrm_accountgroup.groupname,customer.customer,astercrm_account.username FROM ticket_details LEFT JOIN tickets on tickets.id = ticket_details.ticketid LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = ticket_details.groupid LEFT JOIN customer ON customer.id = ticket_details.customerid LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto WHERE ticket_details.parent_id='".str_pad($pid,8,'0',STR_PAD_LEFT)."' ";
		
		astercrm::events($sql);
		$result = & $db->query($sql);
		$html = '
			<table width="100%" border="1" align="center" class="adminlist">
				<tr>
					<th>'.$locate->Translate("Ticket Name").'</th>
					<th>'.$locate->Translate("TicketDetail ID").'</th>
					<th>'.$locate->Translate("Group Name").'</th>
					<th>'.$locate->Translate("Customer").'</th>
					<th>'.$locate->Translate("AssignTo").'</th>
					<th>'.$locate->Translate("Status").'</th>
					<th>'.$locate->Translate("Memo").'</th>
				</tr>';
		while($row = $result->fetchRow()){
			$html .= "
				<tr>
					<td>".$row['ticketname']."</td>
					<td>".str_pad($row['id'],8,'0',STR_PAD_LEFT)."</td>
					<td>".$row['groupname']."</td>
					<td>".$row['customer']."</td>
					<td>".$row['username']."</td>
					<td>".$locate->Translate($row['status'])."</td>
					<td>".$row['memo']."</td>
				</tr>";
		}
		$html .= "</table>";
		return $html;
	}

	function getOriResult($Id){
		global $db;
		$sql = "SELECT * FROM ticket_details WHERE id='".$Id."' ;";
		astercrm::events($sql);
		$result = & $db->getRow($sql);
		return $result;
	}

	function ticketOpLogs($operate,$op_field = '',$op_ori_value = '',$op_new_value = '',$curOwner,$groupid){
		global $db;
		$sql = "INSERT INTO `ticket_op_logs` SET operate='".$operate."',`op_field`='".$op_field."',`op_ori_value`='".$op_ori_value."',`op_new_value`='".$op_new_value."',`curOwner`='".$curOwner."',`operator`='".$_SESSION['curuser']['username']."',`groupid`='".$groupid."',optime=now() ;";
		//print_r($sql);exit;
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function getAssignToName($assignto){
		global $db;
		$sql = "SELECT username FROM astercrm_account WHERE id='".$assignto."' ";
		astercrm::events($sql);
		$username = & $db->getOne($sql);
		return $username;
	}
}
?>