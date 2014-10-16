<?php /*******************************************************************************
* useronline.grid.inc.php
* diallist操作类
* Customer class

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd
	getRecordsFiltered          获取多条件搜索的所有记录
	getNumRowsMore              获取多条件搜索的所有记录条数

********************************************************************************/

require_once 'db_connect.php';
require_once 'useronline.common.php';
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
		global $db,$config;
		$updateTimeInterval = $config['system']['update_online_interval']*60;
		$sql = "SELECT * FROM astercrm_account WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " AND groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if($order == null){
			$sql .= " ORDER BY id DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
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
		global $db,$config;
		$updateTimeInterval = $config['system']['update_online_interval']*60;

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

		$sql = "SELECT * FROM astercrm_account WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " AND groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}
//		print $sql;
//		exit;
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
		global $db,$config;
		$updateTimeInterval = $config['system']['update_online_interval']*60;

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = " SELECT COUNT(*) FROM astercrm_account WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval  ";
		}else{
			$sql = " SELECT COUNT(*) FROM astercrm_account  WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval AND groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getNumRowsMore($filter = null, $content = null,$table){
		global $db,$config;
		$updateTimeInterval = $config['system']['update_online_interval']*60;
		
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

			$sql = "SELECT COUNT(*) FROM astercrm_account WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " AND groupid = ".$_SESSION['curuser']['groupid']." AND ";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " AND ".$joinstr;
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

function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db,$config;
		$updateTimeInterval = $config['system']['update_online_interval']*60;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"diallist");

		$sql = "SELECT * FROM astercrm_account WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= "  ";
		}else{
			$sql .= " AND groupid = ".$_SESSION['curuser']['groupid']." ";
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
		global $db,$config;
		$updateTimeInterval = $config['system']['update_online_interval']*60;
		
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"diallist");

		$sql = "SELECT COUNT(*) FROM astercrm_account WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " AND groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

	function getOnlineSql($searchContent,$searchField,$searchType=array(),$table,$fields = '',$leftjoins=array()){
		global $db,$config;
		$updateTimeInterval = $config['system']['update_online_interval']*60;

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
				$query = "SELECT ".$fieldstr." FROM ".$table." ".$leftStr." WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval AND ".$joinstr;
			}else{
				$query = "SELECT * FROM ".$table." '".$leftStr." WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval AND ".$joinstr;
			}
			
		}else {

			if($fieldstr != ''){
				$fieldstr=rtrim($fieldstr,',');
				$query = "SELECT ".$fieldstr." FROM ".$table." ".$leftStr."WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval ";
			}else{
				$query = "SELECT * FROM ".$table." WHERE (UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(last_update_time)) < $updateTimeInterval ";
			}			
		}
		return $query;
	}
}
?>
