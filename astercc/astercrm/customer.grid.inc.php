<?php
/*******************************************************************************
* customer.grid.inc.php
* customer操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	deleteRecord				删除customer记录, 同时删除与之相关的contact和note
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.045  2007/10/18 14:04:00  last modified by solo
* Desc: delete function getRecordByID

* Revision 0.045  2007/10/18 13:30:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'customer.common.php';
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

		$sql = "SELECT customer.*,note.note,note.codes,note.cretime AS noteCretime FROM customer LEFT JOIN note ON customer.last_note_id = note.id ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE customer.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if($order == null){
			$sql .= " ORDER BY customer.cretime DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
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

		$sql = "SELECT customer.*,note.note,note.codes,note.cretime AS noteCretime FROM customer  LEFT JOIN note ON customer.last_note_id = note.id WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " customer.groupid = ".$_SESSION['curuser']['groupid']." ";
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
			$sql = " SELECT COUNT(*) FROM customer LEFT JOIN note ON customer.last_note_id = note.id ";
		}else{
			$sql = " SELECT COUNT(*) FROM customer  LEFT JOIN note ON customer.last_note_id = note.id WHERE customer.groupid = ".$_SESSION['curuser']['groupid']." ";
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

			$sql = "SELECT COUNT(*) FROM customer  LEFT JOIN note ON customer.last_note_id = note.id WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " customer.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

	function getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table,$type = ''){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		$sql = "SELECT customer.*,note.note,note.codes,note.cretime AS noteCretime FROM customer  LEFT JOIN note ON customer.last_note_id = note.id WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " customer.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
			if($type == ''){
				$sql .= " ORDER BY ".$order
				." ".$_SESSION['ordering']
				." LIMIT $start, $limit $ordering";
			}
		}
		//echo $sql;exit;
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}
	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

			$sql = "SELECT COUNT(*) FROM customer  LEFT JOIN note ON customer.last_note_id = note.id WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " customer.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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
	*  Borra un registro de la tabla.
	*
	*	@param $id		(int)	customerid
	*	@return $res	(object) Devuelve el objeto con la respuesta de la sentencia SQL ejecutada del DELETE.
	*/

	function deleteRecord($id){
		global $db;

		//backup all datas

		//delete all customers
		$sql = "DELETE FROM customer WHERE id = $id";
		Customer::events($sql);
		$res =& $db->query($sql);

		//delete all note
		$sql = "DELETE FROM note WHERE customerid = $id OR contactid in (SELECT id FROM contact WHERE customerid = $id)";
		Customer::events($sql);
		$res =& $db->query($sql);

		//delete all contact
		$sql = "DELETE FROM contact WHERE customerid = $id";
		Customer::events($sql);
		$res =& $db->query($sql);

		return $res;
	}

	
	function specialGetSql($searchContent,$searchField,$searchType=array(),$table,$fields = '',$leftjoins=array()){
		global $db;
		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,$table);
		$fieldstr = '';
		if(is_array($fields)){
			foreach($fields as $field => $alias){
				if(!is_numeric($field)) {
					$fieldstr .= " ".$field." AS ".$alias.",";
				} else {
					$fieldstr .= " ".$alias.",";
				}
			}
		}
		$leftStr = '';
		if(!empty($leftjoins)) {
			foreach($leftjoins as $model=>$param) {// the keys of array $leftjoins are the table which need to left join
				$leftStr .= 'LEFT JOIN '.$model.' ON '.$param[0].'='.$param[1].' ';
			}
		}
		
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND');

			if($fieldstr != ''){
				$fieldstr=rtrim($fieldstr,',');
				$query = 'SELECT '.$fieldstr.' FROM '.$table.' '.$leftStr.' WHERE '.$joinstr;
			}else{
				$query = 'SELECT * FROM '.$table.' '.$leftStr.' WHERE '.$joinstr;
			}
			
		}else {

			if($fieldstr != ''){
				$fieldstr=rtrim($fieldstr,',');
				$query = 'SELECT '.$fieldstr.' FROM '.$table.' '.$leftStr.' ';
			}else{
				$query = 'SELECT * FROM '.$table.'';
			}			
		}
		return $query;
	}

	function showTicketDetail($customerid) {
		global $db,$locate;
		$sql = "SELECT customer FROM customer WHERE id=$customerid";
		astercrm::events($sql);
		$customername = & $db->getOne($sql);
		
		$statusOption = '<select id="Tstatus" name="Tstatus"><option value="new">'.$locate->Translate("new").'</option><option value="panding">'.$locate->Translate("panding").'</option><option value="closed">'.$locate->Translate("closed").'</option><option value="cancel">'.$locate->Translate("cancel").'</option></select>';
		
		$ticketCategory = Customer::getTicketCategory();
		$html = '<form method="post" name="t" id="t">
					<table border="1" width="100%" class="adminlist">
						<tr>
							<td nowrap align="left">'.$locate->Translate("TicketCategory Name").'</td>
							<td align="left">'.$ticketCategory.'</td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Ticket Name").'*</td>
							<td align="left" id="ticketMsg"></td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Customer Name").'</td>
							<td align="left"><input type="hidden" name="customerid" value="'.$customerid.'" />'.$customername.'&nbsp;&nbsp;<a onclick="javascript:AllTicketOfMyself('.$customerid.');return false;" href="?">'.$locate->Translate("Customer Tickets").'</a></td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Status").'</td>
							<td align="left">'.$statusOption.'</td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Memo").'</td>
							<td align="left"><textarea cols="40" rows="5" name="Tmemo" id="Tmemo"></textarea></td>
						</tr>
						<tr>
							<td colspan="2" align="center"><button onClick=\'xajax_saveTicket(xajax.getFormValues("t"));return false;\'>'.$locate->Translate("continue").'</button></td>
						</tr>
					</table>';
			$html .='
				</form>
				'.$locate->Translate("obligatory_fields").'
				';
		return $html;
	}

	function getTicketCategory($CategoryId = '') {
		global $db,$locate;
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

	function getTicketByCategory($fid,$Cid=0) {
		global $db,$locate;
		$sql = "SELECT * FROM tickets WHERE ";

		if($fid != 0) {
			$sql .= "fid=$fid";
		} else {
			$sql .= "fid = -1";
		}
		if($fid != 0) {
			$fsql = "SELECT groupid FROM tickets WHERE id=$fid";
			$groupid = & $db->getOne($fsql);
		} else {
			$groupid = 0;
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
		$html .= '</select><input type="hidden" id="groupid" name="groupid" value="'.$groupid.'" />';
		return $html;
	}
	function insertTicket($f) {
		global $db;
		$customer_sql = "select id from astercrm_account where username='".$_SESSION['curuser']['username']."'";
		astercrm::events($customer_sql);
		$customerid = & $db->getOne($customer_sql);
		
		$sql = "insert into ticket_details set"
				." ticketcategoryid=".$f['ticketcategoryid'].", "
				." ticketid=".$f['ticketid'].", "
				." customerid=".$f['customerid'].", "
				." status='".$f['Tstatus']."', "
				." assignto=".$customerid.", "
				." groupid=".$f['groupid'].", "
				." memo='".$f['Tmemo']."', "
				." cretime=now(),"
				." creby='".$_SESSION['curuser']['username']."' ;";
		
		astercrm::events($sql);
		$result = & $db->query($sql);
		return $result;
	}
}
?>
