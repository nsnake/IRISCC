<?php
/*******************************************************************************
* survey.grid.inc.php
* survey操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	insertNewSurvey				保存survey
	insertNewOption				保存option
	setSurveyEnable				设定survey的可用情况

* Revision 0.0456  2007/11/6 20:30:00  last modified by solo
* Desc: remove function deleteSurvey

* Revision 0.045  2007/10/18 13:30:00  last modified by solo
* Desc: page created

********************************************************************************/
require_once 'db_connect.php';
require_once 'dialedlist.common.php';
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

		$sql = "SELECT campaigndialedlist.*, groupname, campaignname,customer.customer FROM campaigndialedlist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaigndialedlist.groupid LEFT JOIN campaign ON campaign.id = campaigndialedlist.campaignid LEFT JOIN customer ON customer.id = campaigndialedlist.customerid ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if($order == null){
			$sql .= " ORDER BY dialedtime DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit";
		}
//echo $sql;exit;
		Customer::events($sql);
		$res =& $db->query($sql);
//		print_r($res);
//		exit;
		return $res;
	}
	
	function getNoanswerCallsNumber(){
		global $db;

		//$sql = "SELECT count(*) FROM campaigndialedlist WHERE billsec = 0 AND callresult!='dnc'";

		$sql = "SELECT count(*) FROM campaigndialedlist LEFT JOIN campaign ON campaigndialedlist.campaignid = campaign.id WHERE campaigndialedlist.billsec = 0 AND campaigndialedlist.callresult!='dnc' AND campaign.`maxtrytime` > campaigndialedlist.`trytime` AND campaigndialedlist.recycles = 0";
		
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " AND campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
		}
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function recycleDialedlist(){
		global $db;

		$i = 0;
		//get phone numbers

		$sql = "SELECT campaigndialedlist.*,campaign.maxtrytime ,customer.customer FROM campaigndialedlist LEFT JOIN campaign ON campaigndialedlist.campaignid = campaign.id  LEFT JOIN customer ON customer.id = campaigndialedlist.customerid WHERE campaigndialedlist.billsec = 0 AND campaigndialedlist.callresult!='dnc' AND `maxtrytime` > `trytime` AND campaigndialedlist.recycles = 0";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " AND campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
		}
		
		Customer::events($sql);
		$res =& $db->query($sql);
		
		while ($res->fetchInto($row)) {
			$number = $row["dialednumber"];
			$groupid = $row["groupid"];
			$assign = $row["assign"];
			$campaignid = $row["campaignid"];
			$trytime = $row["trytime"];
			$customerid = $row['customerid'];
			$creby = $row['creby'];
			$recycles = (int)($row['recycles']+1);
			$customername = $row['customername'];
			if($row['maxtrytime'] > $row["trytime"]){
				$query = "INSERT INTO diallist SET dialnumber = '$number', cretime = now(), groupid ='$groupid', campaignid='$campaignid', creby = '$creby',trytime= '$trytime', assign = '$assign',customerid = '$customerid' ,customername = '$customername' ";
				$db->query($query);
				$query = "UPDATE campaigndialedlist SET recycles=$recycles WHERE id='".$row['id']."' ";
				//$query = "DELETE FROM campaigndialedlist WHERE id = ".$row['id'];
				$db->query($query);	
				$i++;
			}					
		}
		return $i;
	}

	function recycleDialedlistById($id){
		global $db;
		$i = 0;
		//get phone numbers

		$sql = "SELECT campaigndialedlist.*,campaign.maxtrytime  ,customer.customer FROM campaigndialedlist LEFT JOIN campaign ON campaigndialedlist.campaignid = campaign.id  LEFT JOIN customer ON customer.id = campaigndialedlist.customerid WHERE campaigndialedlist.id=$id";

		Customer::events($sql);
		$row =& $db->getRow($sql);
		$creby = $row["creby"];
		
		$number = $row["dialednumber"];
		$groupid = $row["groupid"];
		$assign = $row["assign"];
		$campaignid = $row["campaignid"];
		$trytime = $row["trytime"];
		$customerid = $row['customerid'];
		$customername = $row['customername'];
		$callOrder = $row['callOrder'];
		$recycles = (int)($row['recycles']+1);
		if($trytime >= $row["maxtrytime"]) $trytime = $row["maxtrytime"] - 1;
		$query = "INSERT INTO diallist SET dialnumber = '$number', cretime = now(), groupid =$groupid, campaignid=$campaignid, creby = '$creby',trytime= '$trytime', assign = '$assign' ,customerid = '$customerid' ,customername = '$customername' ,callOrder = '$callOrder' ";
		$db->query($query);
		$query = "UPDATE campaigndialedlist SET recycles=$recycles WHERE id='".$row['id']."' ";
		//$query = "DELETE FROM campaigndialedlist WHERE id = ".$row['id'];
		$db->query($query);	
		$i++;

		return $i;
	}

	function recyclefromsearch($searchContent,$searchField,$searchType="",$table){
		global $db;
		$joinstr = astercrm::createSqlWithStype($searchField,$searchContent,$searchType,$table);
		$i = 0;
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND');
			$sql = 'SELECT campaigndialedlist.*,campaign.maxtrytime  ,customer.customer FROM campaigndialedlist LEFT JOIN campaign ON campaigndialedlist.campaignid = campaign.id  LEFT JOIN customer ON customer.id = campaigndialedlist.customerid WHERE '.$joinstr;
		}else{
			$sql = 'SELECT campaigndialedlist.*,campaign.maxtrytime  ,customer.customer FROM campaigndialedlist LEFT JOIN campaign ON campaigndialedlist.campaignid = campaign.id  LEFT JOIN customer ON customer.id = campaigndialedlist.customerid WHERE 1 ';
		}

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " AND campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
		}
		
		Customer::events($sql);
		$res =& $db->query($sql);
		
		while ($res->fetchInto($row)) {
			$number = $row["dialednumber"];
			$groupid = $row["groupid"];
			$assign = $row["assign"];
			$campaignid = $row["campaignid"];
			$trytime = $row["trytime"];
			$customerid = $row['customerid'];
			$creby = $row["creby"];
			$customername = $row['customername'];
			$callOrder = $row['callOrder'];
			$recycles = (int)($row['recycles']+1);
			if($trytime >= $row["maxtrytime"]) $trytime = $row["maxtrytime"] - 1;
			$query = "INSERT INTO diallist SET dialnumber = '$number', cretime = now(), groupid =$groupid, campaignid=$campaignid, creby = '$creby',trytime= '$trytime', assign = '$assign',customerid = '$customerid' ,customername = '$customername' ,callOrder = '$callOrder' ";
			$db->query($query);
			$query = "UPDATE campaigndialedlist SET recycles=$recycles WHERE id='".$row['id']."'";
			//$query = "DELETE FROM campaigndialedlist WHERE id = ".$row['id'];
			$db->query($query);	
			$i++;
			//}					
		}
		return $i;
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

		$sql = "SELECT campaigndialedlist.*, groupname, campaignname FROM campaigndialedlist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaigndialedlist.groupid LEFT JOIN campaign ON campaign.id = campaigndialedlist.campaignid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
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

			$sql = "SELECT COUNT(*) FROM campaigndialedlist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaigndialedlist.groupid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'campaigndialedlist');

		$sql = "SELECT campaigndialedlist.*, groupname, campaignname,customer.customer FROM campaigndialedlist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaigndialedlist.groupid LEFT JOIN campaign ON campaign.id = campaigndialedlist.campaignid  LEFT JOIN customer ON customer.id = campaigndialedlist.customerid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
		}
		
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}
//echo $sql;exit;
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,'campaigndialedlist');
		
			$sql = "SELECT COUNT(*) FROM campaigndialedlist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaigndialedlist.groupid LEFT JOIN campaign ON campaign.id = campaigndialedlist.campaignid  LEFT JOIN customer ON customer.id = campaigndialedlist.customerid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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
	*  Devuelte el numero de registros de acuerdo a los par&aacute;metros del filtro
	*
	*	@param $filter	(string)	Nombre del campo para aplicar el filtro en la consulta SQL
	*	@param $order	(string)	Campo por el cual se aplicar&aacute; el orden en la consulta SQL.
	*	@return $row['numrows']	(int) 	N&uacute;mero de registros (l&iacute;neas)
	*/
	
	function &getNumRows($filter = null, $content = null){
		global $db;
		
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = " SELECT COUNT(*) FROM campaigndialedlist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaigndialedlist.groupid LEFT JOIN customer ON customer.id = campaigndialedlist.customerid";
		}else{
			$sql = " SELECT COUNT(*) FROM campaigndialedlist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaigndialedlist.groupid LEFT JOIN customer ON customer.id = campaigndialedlist.customerid WHERE campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function getCampaignReport($aFormValues){
		global $db,$locate;

		$total_sql = "SELECT COUNT(*) AS total,SUM(campaigndialedlist.billsec) AS billsec,SUM(campaigndialedlist.billsec_leg_a) AS billsec_leg_a,SUM(campaigndialedlist.duration) AS duration,campaign.campaignname,campaigndialedlist.campaignid FROM campaigndialedlist LEFT JOIN campaign ON campaign.id  = campaigndialedlist.campaignid WHERE campaigndialedlist.dialedtime BETWEEN '".$aFormValues['sdate']."' AND '".$aFormValues['edate']."'  ";

		$answer_sql = "SELECT COUNT(*) AS total,SUM(billsec) AS billsec,SUM(billsec_leg_a) AS billsec_leg_a,SUM(duration) AS duration,campaignid FROM campaigndialedlist WHERE dialedtime BETWEEN '".$aFormValues['sdate']."' AND '".$aFormValues['edate']."' AND billsec > 0  ";

		$transfer_sql = "SELECT COUNT(transfertarget) AS transferednum,campaignid FROM campaigndialedlist WHERE dialedtime BETWEEN '".$aFormValues['sdate']."' AND '".$aFormValues['edate']."' and transfertarget != ''  ";
		
		if ($_SESSION['curuser']['usertype'] != 'admin'){
			$total_sql .= " AND campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
			$answer_sql .= " AND campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
			$transfer_sql .= " AND campaigndialedlist.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		$total_sql .= " GROUP BY campaigndialedlist.campaignid ";
		$answer_sql .= "  GROUP BY campaignid ";
		$transfer_sql .= " GROUP BY campaignid ";

		astercrm::events($total);
		$total = & $db->getAll($total_sql);

		astercrm::events($answer_sql);
		$answered = & $db->getAll($answer_sql);

		astercrm::events($transfer_sql);
		$transfered = & $db->getAll($transfer_sql);
				
		$resultArray = array();
		foreach($total as $tol){
			$resultArray[$tol['campaignid']]['totalnum'] = $tol['total'];
			$resultArray[$tol['campaignid']]['tbillsec'] = $tol['billsec'];
			$resultArray[$tol['campaignid']]['tbillsec_leg_a'] = $tol['billsec_leg_a'];
			$resultArray[$tol['campaignid']]['tduration'] = $tol['duration'];
			$resultArray[$tol['campaignid']]['campaignname'] = $tol['campaignname'];
		}
		
		foreach($answered as $ans){
			$resultArray[$ans['campaignid']]['atotalnum'] = $ans['total'];
			$resultArray[$ans['campaignid']]['abillsec'] = $ans['billsec'];
			$resultArray[$ans['campaignid']]['abillsec_leg_a'] = $ans['billsec_leg_a'];
			$resultArray[$ans['campaignid']]['aduration'] = $ans['duration'];
		}
		foreach($transfered as $tran){
			$resultArray[$tran['campaignid']]['transferednum'] = $tran['transferednum'];
		}

		$campaigns = & $db->getAll('select id,campaignname from campaign');

		foreach($campaigns as $row){
			if(array_key_exists($row['id'],$resultArray)){
				$resultArray[$row['id']]['campaignname'] = $row['campaignname'];
			}
		}
		
		$campiangStr = '<table style="width:95%;"><tr><th>'.$locate->Translate("Campaign Name").'</th><th>'.$locate->Translate("ToalCallNum").'</th><th>'.$locate->Translate("ToalAnsweredNum").'</th><th>'.$locate->Translate("AnsweredRate").'</th><th>'.$locate->Translate("AvgOfCustomerAnswered").'</th><th>'.$locate->Translate("AvgOfTalk").'</th><th>'.$locate->Translate("AvgOfRing").'</th><th>'.$locate->Translate("AvgOfRingByAnswer").'</th><th>'.$locate->Translate("TransferedNum").'</th><th>'.$locate->Translate("TransferedRate").'</th></tr>';
		
		foreach($resultArray as $key=>$val) {
			$ToalCallNum = $val['totalnum'];//总通话数
			$ToalAnsweredNum = $val['atotalnum'];//接通总数
			$AnsweredRate = (round($val['atotalnum']/$val['totalnum'],4)*100).'%';//接通率

			$tmpAnswered = intval($val['abillsec']/$val['atotalnum']);
			$AvgOfCustomerAnswered = astercrm::FormatSec($tmpAnswered)." (".$tmpAnswered." ".$locate->Translate("sec").")";//平均通话时长

			$tmpTalk = intval($val['tbillsec_leg_a']/$val['totalnum']);
			$AvgOfTalk = astercrm::FormatSec($tmpTalk)." (".$tmpTalk." ".$locate->Translate("sec").")";//平均客户接听时长

			$tmpRing = intval(($val['tduration']-$val['tbillsec_leg_a'])/$val['totalnum']);
			$AvgOfRing = astercrm::FormatSec($tmpRing)." (".$tmpRing." ".$locate->Translate("sec").")";//平均振铃时长

			$tmpRingByAnswer = intval(($val['aduration']-$val['abillsec_leg_a'])/$val['atotalnum']);
			$AvgOfRingByAnswer = astercrm::FormatSec($tmpRingByAnswer)." (".$tmpRingByAnswer." ".$locate->Translate("sec").")";//平均接听振铃时长

			$AvgOfTransferedNum = (round(($val['transferednum']/$ToalAnsweredNum)*100,2)).'%';//转接比率
			if($ToalAnsweredNum == ''){$ToalAnsweredNum = 0;}
			$campiangStr .= '<tr><td>'.$val['campaignname'].'</td><td>'.$ToalCallNum.'</td><td>'.$ToalAnsweredNum.'</td><td>'.$AnsweredRate.'</td><td>'.$AvgOfCustomerAnswered.'</td><td>'.$AvgOfTalk.'</td><td>'.$AvgOfRing.'</td><td>'.$AvgOfRingByAnswer.'</td><td>'.$val['transferednum'].'</td><td>'.$AvgOfTransferedNum.'</td></tr>';
		}
		$campiangStr .= '</table>';
		return $campiangStr;
	}
}
?>