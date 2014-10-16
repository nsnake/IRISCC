<?php
/*******************************************************************************
* diallist.grid.inc.php
* diallist操作类
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
require_once 'diallist.common.php';
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
		
		$sql = "SELECT diallist.*, groupname,campaignname, customer.customer  FROM diallist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = diallist.groupid LEFT JOIN campaign ON campaign.id = diallist.campaignid  LEFT JOIN customer ON customer.id = diallist.customerid";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE diallist.groupid = ".$_SESSION['curuser']['groupid']." ";
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

		$sql = "SELECT diallist.*, groupname,campaignname FROM diallist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = diallist.groupid  LEFT JOIN campaign ON campaign.id = diallist.campaignid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " diallist.groupid = ".$_SESSION['curuser']['groupid']." ";
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
		
		$sql = "SELECT COUNT(*) AS numRows FROM diallist ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = " SELECT COUNT(*) FROM diallist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = diallist.groupid  LEFT JOIN campaign ON campaign.id = diallist.campaignid LEFT JOIN customer ON customer.id = diallist.customerid ";
		}else{
			$sql = " SELECT COUNT(*) FROM diallist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = diallist.groupid  LEFT JOIN campaign ON campaign.id = diallist.campaignid LEFT JOIN customer ON customer.id = diallist.customerid WHERE diallist.groupid = ".$_SESSION['curuser']['groupid']." ";
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

			$sql = "SELECT COUNT(*) FROM diallist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = diallist.groupid  LEFT JOIN campaign ON campaign.id = diallist.campaignid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " diallist.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"diallist");

		$sql = "SELECT diallist.*, groupname,campaignname,customer.customer FROM diallist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = diallist.groupid  LEFT JOIN campaign ON campaign.id = diallist.campaignid LEFT JOIN customer ON customer.id = diallist.customerid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " diallist.groupid = ".$_SESSION['curuser']['groupid']." ";
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
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"diallist");

			$sql = "SELECT COUNT(*) FROM diallist LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = diallist.groupid  LEFT JOIN campaign ON campaign.id = diallist.campaignid LEFT JOIN customer ON customer.id = diallist.customerid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " diallist.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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
							<input type="text" id="dialnumber" name="dialnumber" size="35">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Name").'</td>
						<td align="left">
							<input type="text" id="customername" name="customername" size="35">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Assign To").'</td>
						<td align="left">
							<input type="text" id="assign" name="assign" size="35"">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Call Order").'</td>
						<td align="left">
							<input type="text" id="callOrder" name="callOrder" size="35" value="1">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Dialtime").'</td>
						<td align="left">
							<input type="text" name="dialtime" id="dialtime" size="20" value="">
			<INPUT onclick="displayCalendar(document.getElementById(\'dialtime\'),\'yyyy-mm-dd hh:ii\',this,true)" type="button" value="'.$locate->Translate("Cal").'">
			<br/>
			'.$locate->Translate("empty means no scheduler").'
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
						<td align="left" width="25%">'.$locate->Translate("Memo").'</td>
						<td><textarea id="memo" name="memo" cols="50" rows="8"></textarea></td>
					</tr>';
		$html .= '
					<tr>
						<td nowrap colspan=2 align=center><input type="button" id="btnAddDiallist" name="btnAddDiallist" value="'.$locate->Translate("continue").'" onclick="xajax_save(xajax.getFormValues(\'formDiallist\'));return false;"></td>
					</tr>
				</table>
				</form>
				'.$locate->Translate("obligatory_fields").'
			';
		return $html;
	}

	function formEdit($id){
		global $locate;
		$diallist =& Customer::getRecordByID($id,'diallist');

		if ($_SESSION['curuser']['usertype'] == 'admin'){
				$res = Customer::getGroups();
				$groupoptions .= '<select name="groupid" id="groupid" onchange="setCampaign();">';
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
							<input type="text" id="dialnumber" name="dialnumber" size="35"  value="'.$diallist['dialnumber'].'">
							<input type="hidden" id="id" name="id" value="'.$diallist['id'].'">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Name").'</td>
						<td align="left">
							<input type="text" id="customername" name="customername" value="'.$diallist['customername'].'" size="35">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Assign To").'</td>
						<td align="left">
							<input type="text" id="assign" name="assign" size="35" value="'.$diallist['assign'].'">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Call Order").'</td>
						<td align="left">
							<input type="text" id="callOrder" name="callOrder" size="35" value="'.$diallist['callOrder'].'">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Dialtime").'</td>
						<td align="left">
							<input type="text" name="dialtime" id="dialtime" size="20" value="'.$diallist['dialtime'].'">
			<INPUT onclick="displayCalendar(document.getElementById(\'dialtime\'),\'yyyy-mm-dd hh:ii\',this,true)" type="button" value="'.$locate->Translate("Cal").'">
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
						<td align="left" width="25%">'.$locate->Translate("Memo").'</td>
						<td><textarea id="memo" name="memo" cols="50" rows="8">'.$diallist['memo'].'</textarea></td>
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

	function insertNewDiallist($f){
		global $db;
		$f = astercrm::variableFiler($f);
		if($f['callOrder'] == 0 || $f['callOrder'] == '' ) $f['callOrder'] = 1; 
		$query= "INSERT INTO diallist SET "
				."dialnumber='".astercrm::getDigitsInStr($f['dialnumber'])."', "
				."customername='".$f['customername']."', "
				."groupid='".$f['groupid']."', "
				."dialtime='".$f['dialtime']."', "
				."callOrder='".$f['callOrder']."', "
				."creby='".$_SESSION['curuser']['username']."', "
				."cretime= now(), "
				."campaignid= ".$f['campaignid'].", "
				."assign='".$f['assign']."',"
				."memo='".$f['memo']."'";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function updateDiallistRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		if($f['callOrder'] == 0 || $f['callOrder'] == '' ) $f['callOrder'] = 1;
		$query= "UPDATE diallist SET "
				."dialnumber='".astercrm::getDigitsInStr($f['dialnumber'])."', "
				."customername='".$f['customername']."', "
				."groupid='".$f['groupid']."', "
				."dialtime='".$f['dialtime']."', "
				."callOrder='".$f['callOrder']."', "
				."campaignid= ".$f['campaignid'].", "
				."assign='".$f['assign']."',"
				."memo='".$f['memo']."'"
				."WHERE id='".$f['id']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function createDupGrid($f){//print_r($f);exit;
		global $db,$locate;
		$joinstr = astercrm::createSqlWithStype($f['searchField'],$f['searchContent'],$f['searchType'],"diallist");

		$ajoinstr = str_replace('diallist.','a.',$joinstr);
		if ($_SESSION['curuser']['usertype'] != 'admin'){
				$ajoinstr .= " AND a.groupid = '".$_SESSION['curuser']['groupid']."'";
				$joinstr .= " AND diallist.groupid = '".$_SESSION['curuser']['groupid']."'";
		}
	

		$query = "SELECT a.*,campaign.campaignname FROM diallist as a LEFT JOIN campaign ON campaign.id=a.campaignid,( SELECT * FROM diallist WHERE 1 ".$joinstr." GROUP BY dialnumber HAVING COUNT(dialnumber) > 1 ) as b WHERE a.dialnumber = b.dialnumber AND a.id <> b.id ".$ajoinstr." LIMIT 0,100;";
		

		$fields = array();
		$fields[] = 'dialnumber';
		$fields[] = 'assign';
		//$fields[] = 'groupid';			
		$fields[] = 'campaignname';

		// HTML table: Headers showed
		$headers = array();
		$headers[] = $locate->Translate("Number").'<br>';
		$headers[] = $locate->Translate("Assign to").'<br>';
		//$headers[] = $locate->Translate("Group Name").'<br>';
		$headers[] = $locate->Translate("Campaign Name").'<br>';


		// HTML table: hearders attributes
		$attribsHeader = array();
		$attribsHeader[] = 'width=""';
		$attribsHeader[] = 'width=""';
		//$attribsHeader[] = 'width=""';
		$attribsHeader[] = 'width=""';

		// HTML Table: columns attributes
		$attribsCols = array();
		$attribsCols[] = 'style="text-align: left"';
		$attribsCols[] = 'style="text-align: left"';
		//$attribsCols[] = 'style="text-align: left"';
		$attribsCols[] = 'style="text-align: left"';

	
		// HTML Table: If you want ascendent and descendent ordering, set the Header Events.
		$eventHeader = array();
		$eventHeader[]= 'onClick=\'showRecentCdrGrid("NONE","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","a.dialnumber","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'showRecentCdrGrid("NONE","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","a.assign","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		//$eventHeader[]= 'onClick=\'showRecentCdrGrid("","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","dst","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
		$eventHeader[]= 'onClick=\'showRecentCdrGrid("NONE","'.$cdrtype.'",0,'.$limit.',"'.$filter.'","'.$content.'","campaignname","'.$divName.'","ORDERING","'.$stype.'");return false;\'';
				

		// Select Box: fields table.
		$fieldsFromSearch = array();
	
		// Selecct Box: Labels showed on search select box.
		$fieldsFromSearchShowAs = array();

		// Create object whit 5 cols and all data arrays set before.
		$table = new ScrollTable(4,$start,$limit,$filter,$numRows,$content,$order,$customerid,"diallist_dup");
		$table->setHeader('title',$headers,$attribsHeader,$eventHeader,$edit=false,$delete=false,$detail=false);
		$table->setAttribsCols($attribsCols);
		//$table->addRowSearchMore("mycdr",$fieldsFromSearch,$fieldsFromSearchShowAs,$filter,$content,$start,$limit,0,0,$typeFromSearch,$typeFromSearchShowAs,$stype);
		$res = $db->query($query);
		while ($res->fetchInto($row)) {
			//print_r($row);exit;
		// Change here by the name of fields of its database table
			$rowc = array();
			$rowc[] = $row['id'];
			$rowc[] = $row['dialnumber'];
			$rowc[] = $row['assign'];
			//$rowc[] = $row['groupid'];
			$rowc[] = $row['campaignname'];
			
			$table->addRow("Duplicate",$rowc,false,false,false,'formDuplicate',$fields);
		}//exit;
		$html = $table->render('static');
		return $html;		
	}


	function deleteDuplicates($f){
		global $db,$locate;
		$joinstr = astercrm::createSqlWithStype($f['searchField'],$f['searchContent'],$f['searchType'],"diallist");
		$ajoinstr = str_replace('diallist.','a.',$joinstr);
		if ($_SESSION['curuser']['usertype'] != 'admin'){
				$ajoinstr .= " AND a.groupid = '".$_SESSION['curuser']['groupid']."'";
				$joinstr .= " AND diallist.groupid = '".$_SESSION['curuser']['groupid']."'";
		}

		$query = "DELETE diallist as a FROM diallist as a ,( SELECT * FROM diallist WHERE 1 ".$joinstr." GROUP BY dialnumber HAVING COUNT(dialnumber) > 1 ) as b WHERE a.dialnumber = b.dialnumber AND a.id <> b.id ".$ajoinstr." ";

		$res = $db->query($query);
		return $res;
	}
}
?>
