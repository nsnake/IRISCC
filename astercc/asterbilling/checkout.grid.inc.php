<?
/*******************************************************************************
* clid.grid.inc.php
* clid操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加clid表单的HTML
	formEdit					生成编辑clid表单的HTML
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.0456  2007/10/30 13:15:00  last modified by solo
* Desc: add channel field 

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'checkout.common.php';
require_once 'include/astercrm.class.php';

//ini_set('include_path',dirname($_SERVER["SCRIPT_FILENAME"])."/include");

// define database connection string
if($config['a2billing']['enable']){
	define('SQLCA2B', $config['a2billing']['dbtype']."://".$config['a2billing']['username'].":".$config['a2billing']['password']."@tcp+".$config['a2billing']['dbhost'].":".$config['a2billing']['dbport']."/".$config['a2billing']['dbname']."");
	//echo SQLCA2B;exit;
	// set a global variable to save customers database connection
	$GLOBALS['a2billing_db'] = DB::connect(SQLCA2B);
	//print_R($GLOBALS['customers_db']);exit;
	// need to check if db connected
	if (DB::iserror($GLOBALS['a2billing_db'])){
		die("customer database:".$GLOBALS['a2billing_db']->getmessage());
	}

	// change database fetch mode
	$GLOBALS['a2billing_db']->setFetchMode(DB_FETCHMODE_ASSOC);
}

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
	function readA2Breport($resellerid, $groupid, $booth, $sdate, $edate, $groupby = '',$orderby='',$limit=''){
		global $a2billing_db,$db,$config;
		
		if (($groupid == '' || $groupid == 0) && ($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator')){
			$groupid = $_SESSION['curuser']['groupid'];
		}

		if ( ($resellerid == '' || $resellerid == 0) && $_SESSION['curuser']['usertype'] == 'reseller' ){
			$resellerid = $_SESSION['curuser']['resellerid'];
		}

		if($booth != '' && $booth != 0){
			$clidstr = $booth;
		}else{
			if($groupid > 0){
				$query = "SELECT clid FROM clid WHERE groupid = $groupid";

			}elseif($resellerid > 0){
				$query = "SELECT clid FROM clid WHERE resellerid = $resellerid";

			}else{
				$query = "SELECT clid FROM clid ";
			}

			$clidstr = '';
			$clid_res = $db->query($query);
			while ($clid_res->fetchInto($rows)) {
				$clidstr .= $rows['clid'].',';
			}
			$clidstr = rtrim($clidstr,',');
		}	

		if($clidstr != ''){
			$query = "SELECT * FROM ".$config['a2billing']['cidtable']." WHERE cid in ($clidstr)";
			$card_id_str = '';

			$card_res = $a2billing_db->query($query);
			while ($card_res->fetchInto($rows)) {
				$card_id_str .= $rows['id_cc_card'].',';
			}

			$card_id_str = rtrim($card_id_str,',');
			if($card_id_str != ''){
				$query = "SELECT sum(sessionbill) AS a2bcost FROM ".$config['a2billing']['calltable']." WHERE card_id in ($card_id_str) AND starttime >= '$sdate' AND  starttime <= '$edate'";

				$a2bcost = $a2billing_db->getOne($query);
				return $a2bcost;
			}else{
				return 0;
			}
		}else{
			return 0;
		}
		
		
	}
}
?>
