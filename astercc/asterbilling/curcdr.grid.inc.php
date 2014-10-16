<?
/*******************************************************************************
* curcdr.grid.inc.php

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List


* Revision 0.01  2011/11/08 last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'curcdr.common.php';
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
	function &getAllRecords($start, $limit, $order = null, $creby = null){
		global $db;
		
		$sql = "SELECT curcdr.*,clid.clid,resellergroup.resellername,accountgroup.groupname FROM curcdr LEFT JOIN clid ON clid.id = curcdr.userid LEFT JOIN resellergroup ON resellergroup.id = curcdr.resellerid LEFT JOIN accountgroup ON accountgroup.id = curcdr.groupid WHERE ";
		
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		} else if($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator') {
			$sql .= " curcdr.groupid = ".$_SESSION['curuser']['groupid']." ";
		} else if($_SESSION['curuser']['usertype'] == 'reseller') {
			$sql .= " curcdr.resellerid = ".$_SESSION['curuser']['resellerid']." ";
		}

		if($order == null){
			$sql .= " LIMIT $start, $limit";
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
		
		$sql = "SELECT curcdr.*,clid.clid,resellergroup.resellername,accountgroup.groupname FROM curcdr LEFT JOIN clid ON clid.id = curcdr.userid LEFT JOIN resellergroup ON resellergroup.id = curcdr.resellerid LEFT JOIN accountgroup ON accountgroup.id = curcdr.groupid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		} else if($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator') {
			$sql .= " curcdr.groupid = ".$_SESSION['curuser']['groupid']." ";
		} else if($_SESSION['curuser']['usertype'] == 'reseller') {
			$sql .= " curcdr.resellerid = ".$_SESSION['curuser']['resellerid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr." "
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
		
		$sql = "SELECT COUNT(*) AS numRows FROM curcdr LEFT JOIN clid ON clid.id = curcdr.userid LEFT JOIN resellergroup ON resellergroup.id = curcdr.resellerid LEFT JOIN accountgroup ON accountgroup.id = curcdr.groupid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		} else if($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator') {
			$sql .= " curcdr.groupid = ".$_SESSION['curuser']['groupid']." ";
		} else if($_SESSION['curuser']['usertype'] == 'reseller') {
			$sql .= " curcdr.resellerid = ".$_SESSION['curuser']['resellerid']." ";
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
		
		$sql = "SELECT COUNT(*) AS numRows FROM curcdr LEFT JOIN clid ON clid.id = curcdr.userid LEFT JOIN resellergroup ON resellergroup.id = curcdr.resellerid LEFT JOIN accountgroup ON accountgroup.id = curcdr.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		} else if($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator') {
			$sql .= " curcdr.groupid = ".$_SESSION['curuser']['groupid']." ";
		} else if($_SESSION['curuser']['usertype'] == 'reseller') {
			$sql .= " curcdr.resellerid = ".$_SESSION['curuser']['resellerid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr." ";
		}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);
		
		$sql = "SELECT curcdr.*,clid.clid,resellergroup.resellername,accountgroup.groupname FROM curcdr LEFT JOIN clid ON clid.id = curcdr.userid LEFT JOIN resellergroup ON resellergroup.id = curcdr.resellerid LEFT JOIN accountgroup ON accountgroup.id = curcdr.groupid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		} else if($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator') {
			$sql .= " curcdr.groupid = ".$_SESSION['curuser']['groupid']." ";
		} else if($_SESSION['curuser']['usertype'] == 'reseller') {
			$sql .= " curcdr.resellerid = ".$_SESSION['curuser']['resellerid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr." "
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
		
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		$sql = "SELECT COUNT(*) AS numRows FROM curcdr LEFT JOIN clid ON clid.id = curcdr.userid LEFT JOIN resellergroup ON resellergroup.id = curcdr.resellerid LEFT JOIN accountgroup ON accountgroup.id = curcdr.groupid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		} else if($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator') {
			$sql .= " curcdr.groupid = ".$_SESSION['curuser']['groupid']." ";
		} else if($_SESSION['curuser']['usertype'] == 'reseller') {
			$sql .= " curcdr.resellerid = ".$_SESSION['curuser']['resellerid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr." ";
		}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function deleteFromSearch($searchContent,$searchField,$searchType="",$table){
		global $db;
		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,$table);
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND');
			$sql = 'DELETE FROM '.$table.' LEFT JOIN clid ON clid.id = curcdr.userid LEFT JOIN resellergroup ON resellergroup.id = curcdr.resellerid LEFT JOIN accountgroup ON accountgroup.id = curcdr.groupid WHERE '.$joinstr;
		}else{
			if($_SESSION['curuser']['usertype'] == 'admin'){
				$sql = 'TRUNCATE '.$table;
			}else{
				$sql = "DELETE FROM ".$table." WHERE ".$table.".groupid = '".$_SESSION['curuser']['groupid']."'";
			}
		}
		//echo $sql;exit;
		Customer::events($sql);
		$res =& $db->query($sql);

		return $res;
	}
}
?>
