<?php
/*******************************************************************************
* ticket_op_logs.grid.inc.php
* ticket_op_logs操作类
* Customer class

* @author			solo.fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Aug 2010

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	新增 getRecordsFilteredMore  用于获得多条件搜索记录集
	新增 getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'ticket_op_logs.common.php';
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
		
		$sql = "SELECT ticket_op_logs.*,AccountGroup.groupname as groupname FROM ticket_op_logs LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_op_logs.groupid ";
		
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE ticket_op_logs.groupid = '".$_SESSION['curuser']['groupid']."' ";
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
		$joinstr = Customer::createSqlWithStype($filter,$content,$stype,'ticket_op_logs');//<---- change by your function

		$sql = "SELECT ticket_op_logs.*,AccountGroup.groupname as groupname FROM ticket_op_logs LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_op_logs.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1";
		}else{
			$sql .= " ticket_op_logs.groupid = '".$_SESSION['curuser']['groupid']."' ";
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
			$sql = " SELECT COUNT(*) FROM ticket_op_logs LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_op_logs.groupid ";
		}else{
			$sql = " SELECT COUNT(*) FROM ticket_op_logs LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_op_logs.groupid  WHERE ticket_op_logs.groupid = '".$_SESSION['curuser']['groupid']."'";
		}
		
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getNumRowsMore($filter = null, $content = null){
		global $db;
		$joinstr = Customer::createSqlWithStype($filter,$content,$stype,'ticket_details');//<---- change by your function

		$sql = "SELECT COUNT(*) FROM ticket_op_logs LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_op_logs.groupid  WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1";
		}else{
			$sql .= " ticket_op_logs.groupid='".$_SESSION['curuser']['groupid']."'";
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

		$sql = "SELECT COUNT(*) FROM ticket_op_logs LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_op_logs.groupid  WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1";
		}else{
			$sql .= " ticket_op_logs.groupid = '".$_SESSION['curuser']['groupid']."' ";
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

		$sql = "SELECT ticket_op_logs.*,AccountGroup.groupname as groupname FROM ticket_op_logs LEFT JOIN astercrm_accountgroup AS AccountGroup ON AccountGroup.id = ticket_op_logs.groupid  WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1";
		}else{
			$sql .= " ticket_op_logs.groupid = '".$_SESSION['curuser']['groupid']."' ";
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
				if($filter[$i] == 'groupname') {
					$filter[$i] = 'AccountGroup.groupname';
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
}
?>