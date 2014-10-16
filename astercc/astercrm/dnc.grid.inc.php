<?php
/*******************************************************************************
* dnc.grid.inc.php
* dnc操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd
	getRecordsFiltered          获取多条件搜索的所有记录
	getNumRowsMore              获取多条件搜索的所有记录条数


* Revision 0.045  2007/10/18 19:53:00  last modified by solo
* Desc: delete function getRecordByID, add function  formAdd


* Revision 0.045  2007/10/18 13:30:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'dnc.common.php';
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
		
		$sql = "SELECT dnc_list.*, groupname,campaignname FROM dnc_list LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = dnc_list.groupid LEFT JOIN campaign ON campaign.id = dnc_list.campaignid  ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE dnc_list.groupid = ".$_SESSION['curuser']['groupid']." ";
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

		$sql = "SELECT dnc_list.*, groupname,campaignname FROM dnc_list LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = dnc_list.groupid  LEFT JOIN campaign ON campaign.id = dnc_list.campaignid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " dnc_list.groupid = ".$_SESSION['curuser']['groupid']." ";
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
		global $db;
		
		$sql = "SELECT COUNT(*) AS numRows FROM dnc_list ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = " SELECT COUNT(*) FROM dnc_list LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = dnc_list.groupid  LEFT JOIN campaign ON campaign.id = dnc_list.campaignid ";
		}else{
			$sql = " SELECT COUNT(*) FROM dnc_list LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = dnc_list.groupid  LEFT JOIN campaign ON campaign.id = dnc_list.campaignid WHERE dnc_list.groupid = ".$_SESSION['curuser']['groupid']." ";
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

			$sql = "SELECT COUNT(*) FROM dnc_list LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = dnc_list.groupid  LEFT JOIN campaign ON campaign.id = dnc_list.campaignid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " dnc_list.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"dnc_list");

		$sql = "SELECT dnc_list.*, groupname,campaignname FROM dnc_list LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = dnc_list.groupid  LEFT JOIN campaign ON campaign.id = dnc_list.campaignid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " dnc_list.groupid = ".$_SESSION['curuser']['groupid']." ";
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
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"dnc_list");

			$sql = "SELECT COUNT(*) FROM dnc_list LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = dnc_list.groupid  LEFT JOIN campaign ON campaign.id = dnc_list.campaignid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " dnc_list.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

	function formAdd(){
		global $locate;
		if ($_SESSION['curuser']['usertype'] == 'admin'){
				$res = Customer::getGroups();
				$groupoptions .= '<select name="groupid" id="groupid" onchange="setCampaign();">';
				$groupoptions .= '<option value="0"';
				$groupoptions .='>'.$locate->Translate("please select").'</option>';
				while ($row = $res->fetchRow()) {
						$groupoptions .= '<option value="'.$row['groupid'].'"';
						$groupoptions .='>'.$row['groupname'].'</option>';
				}
				$groupoptions .= '</select>';
		}else{
				$groupoptions .= $_SESSION['curuser']['group']['groupname'].'<input id="groupid" name="groupid" type="hidden" value="'.$_SESSION['curuser']['groupid'].'">';
		}

		$html = '
				<!-- No edit the next line -->
				<form method="post" name="formDiallist" id="formDiallist">
				
				<table border="1" width="100%" class="adminlist">
					<tr>
						<td nowrap align="left">'.$locate->Translate("number").'*</td>
						<td align="left">
							<input type="text" id="number" name="number" size="35">
						</td>
					</tr>';
					
		$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Group Name").'*</td>
						<td>'.$groupoptions.'</td>
					</tr>';
		$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Campaign Name").'*</td>
						<td><SELECT id="campaignid" name="campaignid"></SELECT></td>
					</tr>';
		$html .= '
					<tr>
						<td nowrap colspan=2 align=right><input type="button" id="btnAddDiallist" name="btnAddDiallist" value="'.$locate->Translate("continue").'" onclick="xajax_save(xajax.getFormValues(\'formDiallist\'));return false;"></td>
					</tr>
				<table>
				</form>
				'.$locate->Translate("obligatory_fields").'
			';
		return $html;
	}

	function formEdit($id){
		global $locate;
		$diallist =& Customer::getRecordByID($id,'dnc_list');

		if ($_SESSION['curuser']['usertype'] == 'admin'){
				$res = Customer::getGroups();
				$groupoptions .= '<select name="groupid" id="groupid" onchange="setCampaign();">';
				$groupoptions .= '<option value="0"';
				$groupoptions .='>'.$locate->Translate("please select").'</option>';
				while ($row = $res->fetchRow()) {
						$groupoptions .= '<option value="'.$row['groupid'].'"';
						if ($diallist['groupid']  == $row['groupid'])
							$groupoptions .= ' selected';
						$groupoptions .='>'.$row['groupname'].'</option>';
				}
				$groupoptions .= '</select>';
		}else{
				$groupoptions .= $_SESSION['curuser']['group']['groupname'].'<input id="groupid" name="groupid" type="hidden" value="'.$_SESSION['curuser']['groupid'].'">';
		}

		$campaignlist =  Customer::getAll("campaign","groupid", $diallist['groupid']);
		while ($row = $campaignlist->fetchRow()) {
			$campaign_options .= '<option value="'.$row['id'].'"';
			if ($diallist['campaignid']  == $row['id'])
				$campaign_options .= ' selected';
			$campaign_options .='>'.$row['campaignname'].'</option>';
		}


		$html = '
				<!-- No edit the next line -->
				<form method="post" name="formDiallist" id="formDiallist">
				
				<table border="1" width="100%" class="adminlist">
					<tr>
						<td nowrap align="left">'.$locate->Translate("number").'*</td>
						<td align="left">
							<input type="text" id="number" name="number" size="35"  value="'.$diallist['number'].'">
							<input type="hidden" id="id" name="id" value="'.$diallist['id'].'">
						</td>
					</tr>';
		$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Group Name").'</td>
						<td>'.$groupoptions.'</td>
					</tr>';
		$html .= '
					<tr>
						<td align="left" width="25%">'.$locate->Translate("Campaign Name").'</td>
						<td><SELECT id="campaignid" name="campaignid">'.$campaign_options.'</SELECT></td>
					</tr>';
		$html .= '
					<tr>
						<td nowrap colspan=2 align=right><input type="button" id="btnAddDiallist" name="btnAddDiallist" value="'.$locate->Translate("continue").'" onclick="xajax_update(xajax.getFormValues(\'formDiallist\'));return false;"></td>
					</tr>
				<table>
				</form>
				';
		return $html;
	}

	function insertNewDNClist($f){
		global $db;
		$f = astercrm::variableFiler($f);

		$query= "INSERT INTO dnc_list SET "
				."number='".astercrm::getDigitsInStr($f['number'])."', "
				."groupid='".$f['groupid']."', "
				."creby='".$_SESSION['curuser']['username']."', "
				."cretime= now(), "
				."campaignid= '".$f['campaignid']."'";
		astercrm::events($query);

		$res =& $db->query($query);
		return $res;
	}

	function updateDNCRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);

		$query= "UPDATE dnc_list SET "
				."number='".astercrm::getDigitsInStr($f['number'])."', "
				."groupid='".$f['groupid']."', "
				."campaignid= '".$f['campaignid']."' "
				."WHERE id='".$f['id']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}
}
?>
