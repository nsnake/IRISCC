<?
/*******************************************************************************
* cdr.grid.inc.php
* cdr操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数
	新增getRecordsFilteredMorewithstype 用于获得指定匹配方式(like,=,<,>)的多条件记录集
	新增getNumRowsMorewithstype 用于获得指定匹配方式(like,=,<,>)的多条件记录条数


********************************************************************************/

require_once 'db_connect.php';
require_once 'cdr.common.php';
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
	function &getAllRecords($start, $limit, $order = null, $creby = null, $table='mycdr',$allOrAnswer=null){
		global $db;
		if($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = "SELECT * FROM ".$table." WHERE (groupid > 0 OR resellerid > '0')";
		}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$sql = "SELECT * FROM ".$table." WHERE groupid = '".$_SESSION['curuser']['groupid']."'";
		}elseif($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql = "SELECT * FROM ".$table." WHERE resellerid = '".$_SESSION['curuser']['resellerid']."'";
		}elseif($_SESSION['curuser']['usertype'] == 'clid'){
			$sql = "SELECT * FROM ".$table." WHERE src = '".$_SESSION['curuser']['username']."'";
		}
		
		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND billsec > 0 ";
		}
		
		if($order == null || is_array($order)){
			$sql .= "ORDER by calldate DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY ".$order." ".$_SESSION['ordering']." LIMIT $start, $limit";
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

	function &getRecordsFilteredMore($start, $limit, $filter, $content, $order,$table, $ordering = "",$allOrAnswer=null){
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

		if($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = "SELECT * FROM ".$table." WHERE (groupid > 0 OR resellerid > '0')";
		}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$sql = "SELECT * FROM ".$table." WHERE groupid = '".$_SESSION['curuser']['groupid']."'";
		}elseif($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql = "SELECT * FROM ".$table." WHERE resellerid = '".$_SESSION['curuser']['resellerid']."'";
		}elseif($_SESSION['curuser']['usertype'] == 'clid'){
			$sql = "SELECT * FROM ".$table." WHERE src = '".$_SESSION['curuser']['username']."'";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
		}
		
		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND billsec > 0 ";
		}

		$sql .= " ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";

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
	
	function &getNumRows($table='mycdr',$allOrAnswer=null){
		global $db;
		
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE (groupid > 0 OR resellerid > '0')";
		}elseif ($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE groupid = '".$_SESSION['curuser']['groupid']."'";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE resellerid = '".$_SESSION['curuser']['resellerid']."'";
		}elseif($_SESSION['curuser']['usertype'] == 'clid'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE src = '".$_SESSION['curuser']['username']."'";
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND billsec > 0 ";
		}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getNumRowsMore($filter = null, $content = null,$table,$allOrAnswer=null){
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

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE (groupid > '0' OR resellerid > '0')";
		}elseif ($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE groupid = '".$_SESSION['curuser']['groupid']."'";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE resellerid = '".$_SESSION['curuser']['resellerid']."'";
		}elseif($_SESSION['curuser']['usertype'] == 'clid'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE src = '".$_SESSION['curuser']['username']."'";
		}

		if ($joinstr!=''){
			$sql .= " ".$joinstr;
		}else {
			$sql .= " 1";
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND billsec > 0 ";
		}

		Customer::events($sql);
		$res =& $db->getOne($sql);		
		return $res;
	}
	
	function &getNumRowsMorewithstype($filter = null, $content = null,$stype = null,$table,$allOrAnswer=null){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE (groupid > '0' OR resellerid > '0')";
		}elseif ($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE groupid = '".$_SESSION['curuser']['groupid']."'";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE resellerid = '".$_SESSION['curuser']['resellerid']."'";
		}elseif($_SESSION['curuser']['usertype'] == 'clid'){
			$sql .= " SELECT COUNT(*) FROM ".$table." WHERE src = '".$_SESSION['curuser']['username']."'";
		}

		if ($joinstr!=''){
			$sql .= " ".$joinstr;
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND billsec > 0 ";
		}
		Customer::events($sql);
		$res =& $db->getOne($sql);		
		return $res;
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype = null, $order,$table, $ordering = "",$allOrAnswer=null){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		if($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = "SELECT * FROM ".$table." WHERE (groupid > '0' OR resellerid > '0')";
		}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$sql = "SELECT * FROM ".$table." WHERE groupid = '".$_SESSION['curuser']['groupid']."'";
		}elseif($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql = "SELECT * FROM ".$table." WHERE resellerid = '".$_SESSION['curuser']['resellerid']."'";
		}elseif($_SESSION['curuser']['usertype'] == 'clid'){
			$sql = "SELECT * FROM ".$table." WHERE src = '".$_SESSION['curuser']['username']."'";
		}
		
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
		}

		if(!empty($allOrAnswer) && $allOrAnswer == 'answered') {
			$sql .= " AND billsec > 0 ";
		}

		$sql .= " ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";

		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getAllRecordsByCustomerid($start, $limit, $order = null, $creby = null, $table='mycdr',$customerid){
		global $db;
		$sql = "SELECT * FROM ".$table." WHERE customerid = $customerid AND (groupid > 0 OR resellerid > '0')";
		
		if($order == null || is_array($order)){
			$sql .= "ORDER by calldate DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY ".$order." ".$_SESSION['ordering']." LIMIT $start, $limit";
		}

		Customer::events($sql);
		$res =& $db->query($sql);

		return $res;
	}

	function &getNumRowsByCustomerid($table='mycdr',$customerid){
		global $db;
		
		$sql .= " SELECT COUNT(*) FROM ".$table." WHERE customerid = $customerid AND (groupid > 0 OR resellerid > '0')";
//echo $sql;exit;
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}
}
?>
