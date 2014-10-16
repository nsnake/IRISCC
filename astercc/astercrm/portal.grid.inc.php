<?php
/*******************************************************************************
* portal.grid.inc.php
* portal操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords											获取所有记录
		($start, $limit, $order = null)
	getRecordsFiltered							获取多条件搜索结果记录集
		($start, $limit, $filter, $content, $order)
	getNumRows										 获取多条件搜索结果记录条数
		($filter = null, $content = null)

* Revision 0.0456  2007/12/19 15:11:00  last modified by solo
* Desc: deleted function getRecordsFiltered,getNumRowsMore

* Revision 0.045  2007/10/18 15:11:00  last modified by solo
* Desc: deleted function getRecordByID

* Revision 0.045  2007/10/18 13:30:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'portal.common.php';
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
	function &getAllRecords($start, $limit, $order = null){
		global $db,$config;

		if ($config['system']['portal_display_type'] == "note"){
			$sql = "SELECT 
									note.id AS id,
									note.contactid AS contactid,
									note.customerid AS customerid,
									note.attitude AS attitude, 
									note, 
									priority,
									private,
									customer.customer AS customer,
									contact.contact AS contact,
									customer.category AS category,
									note.cretime AS cretime,
									note.creby AS creby 
									FROM note 
									LEFT JOIN customer ON customer.id = note.customerid 
									LEFT JOIN contact ON contact.id = note.contactid 
									WHERE priority>0 AND note.creby = '".$_SESSION['curuser']['username']."' ";
			
		}else{
			$sql = "SELECT customer.id,
									customer.customer AS customer,
									note.note AS note,
									note.priority AS priority,
									note.attitude AS attitude,
									note.private AS private,
									note.creby AS creby,
									customer.category AS category,
									customer.contact AS contact,
									customer.cretime as cretime,
									customer.phone as phone,
									customer.mobile as mobile
									FROM customer LEFT JOIN note ON customer.id = note.customerid ";
			if($config['system']['detail_level'] != 'all')						
				$sql .= " WHERE customer.groupid = '".$_SESSION['curuser']['groupid']."' ";
		}

		if($order == null){
			$sql .= " ORDER BY cretime DESC LIMIT $start, $limit";
		}else{
			$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit";
		}
		//echo $sql;exit;
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}
	
	function getRecordsFiltered($start, $limit, $filter, $content, $order){
		global $db,$config;

		$i=0;
		$joinstr='';
		foreach ($content as $value){
			$value = preg_replace("/'/","\\'",$value);
			$value=trim($value);
			if (strlen($value)!=0 && strlen($filter[$i]) != 0){
				$joinstr.="AND $filter[$i] like '%$value%' ";
			}
			$i++;
		}

		if ($config['system']['portal_display_type'] == "note"){
				if ($joinstr != ''){
					$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
					$sql = "SELECT 
											note.id AS id, 
											note, 
											priority,
											private,
											customer.customer AS customer,
											contact.contact AS contact,
											customer.category AS category,
											note.cretime AS cretime,
											note.creby AS creby,
											note.customerid AS customerid,
											note.contactid AS contactid
											FROM note 
											LEFT JOIN customer ON customer.id = note.customerid 
											LEFT JOIN contact ON contact.id = note.contactid
											WHERE $joinstr  
											AND priority>0
											AND note.creby = '".$_SESSION['curuser']['username']."' "
											." ORDER BY $order ".$_SESSION['ordering']
											." LIMIT $start, $limit ";
				}else {
					$sql = "SELECT 
											note.id AS id, 
											note, 
											priority,
											private,
											customer.customer AS customer,
											contact.contact AS contact,
											customer.category AS category,
											note.cretime AS cretime,
											note.creby AS creby ,
											note.customerid AS customerid,
											note.contactid AS contactid
											FROM note 
											LEFT JOIN customer ON customer.id = note.customerid 
											LEFT JOIN contact ON contact.id = note.contactid"
											." AND  note.creby = '".$_SESSION['curuser']['username']."' "
											." ORDER BY $order ".$_SESSION['ordering']
											." LIMIT $start, $limit ";
				}
			}else{
				if ($joinstr != ''){
					$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
					$sql = "SELECT customer.id AS id,
											customer.customer AS customer,
											customer.category AS category,
											customer.contact AS contact,
											customer.cretime as cretime,
											customer.phone as phone,
											customer.mobile as mobile,
											note.note AS note,
											note.priority AS priority,
											note.attitude AS attitude
											FROM customer
											LEFT JOIN note ON customer.id = note.customerid"
											." WHERE ".$joinstr." "
											." AND  customer.creby = '".$_SESSION['curuser']['username']."' "
											." ORDER BY $order ".$_SESSION['ordering']
											." LIMIT $start, $limit ";
				}else {
					$sql = "SELECT customer.id AS id,
											customer.customer AS customer,
											customer.category AS category,
											customer.contact AS contact,
											customer.cretime as cretime,
											customer.phone as phone,
											customer.mobile as mobile,
											note.note AS note,
											note.priority AS priority,
											note.attitude AS attitude
											FROM customer
											LEFT JOIN note ON customer.id = note.customerid"
											." AND  customer.creby = '".$_SESSION['curuser']['username']."' "
											." ORDER BY $order ".$_SESSION['ordering']
											." LIMIT $start, $limit ";
				}
			}

		astercrm::events($sql);
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
	

	function getNumRows($filter = null, $content = null){
		global $db,$config;
		if ($filter == null){
			if ($config['system']['portal_display_type'] == "note"){
				$sql = "SELECT 
										COUNT(*) AS numRows 
										FROM note 
										LEFT JOIN customer ON customer.id = note.customerid 
										LEFT JOIN contact ON contact.id = note.contactid  
										WHERE priority>0  AND note.creby = '".$_SESSION['curuser']['username']."'";
			}else{
				$sql = "SELECT 
										COUNT(*) AS numRows 
										FROM customer 
										LEFT JOIN note ON customer.id = note.customerid";

				if($config['system']['detail_level'] != 'all')						
					$sql .= " WHERE customer.groupid = '".$_SESSION['curuser']['groupid']."' ";
			}
		}else{
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
			if ($config['system']['portal_display_type'] == "note"){
				if ($joinstr!=''){
					$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
					$sql = 	"SELECT 
												COUNT(*) AS numRows
												FROM note 
												LEFT JOIN customer ON customer.id = note.customerid 
												LEFT JOIN contact ON contact.id = note.contactid 
												WHERE ".$joinstr
												." AND  note.creby = '".$_SESSION['curuser']['username']."' ";
				}else {
					$sql = "SELECT 
											COUNT(*) AS numRows 
											FROM note 
											LEFT JOIN customer ON customer.id = note.customerid 
											LEFT JOIN contact ON contact.id = note.contactid  
											WHERE priority>0  
											AND note.creby = '".$_SESSION['curuser']['username']."'";
				}
			}else{
				if ($joinstr!=''){
					$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
					$sql = 	"SELECT 
												COUNT(*) AS numRows
												FROM customer 
												LEFT JOIN note ON customer.id = note.customerid  
												WHERE ".$joinstr;

					if($config['system']['detail_level'] != 'all')						
						$sql .= " AND customer.groupid = '".$_SESSION['curuser']['groupid']."' ";
				}else {
					$sql = "SELECT 
											COUNT(*) AS numRows 
											FROM customer 
											LEFT JOIN note ON customer.id = note.customerid ";

					if($config['system']['detail_level'] != 'all')						
						$sql .= " WHERE customer.groupid = '".$_SESSION['curuser']['groupid']."' ";
				}
			}
		}

		astercrm::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getNumRowsMorewithstype($filter = null, $content = null,$stype = null,$table){
		global $db,$config;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

			if ($config['system']['portal_display_type'] == "note"){
				if ($joinstr!=''){
					$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
					$sql = 	"SELECT 
												COUNT(*) AS numRows
												FROM note 
												LEFT JOIN customer ON customer.id = note.customerid 
												LEFT JOIN contact ON contact.id = note.contactid 
												WHERE ".$joinstr
												." AND  note.creby = '".$_SESSION['curuser']['username']."' ";
				}else {
					$sql = "SELECT 
											COUNT(*) AS numRows 
											FROM note 
											LEFT JOIN customer ON customer.id = note.customerid 
											LEFT JOIN contact ON contact.id = note.contactid  
											WHERE priority>0  
											AND note.creby = '".$_SESSION['curuser']['username']."'";
				}
			}else{
				if ($joinstr!=''){
					$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
					$sql = 	"SELECT 
												COUNT(*) AS numRows
												FROM customer 
												LEFT JOIN note ON customer.id = note.customerid  
												WHERE ".$joinstr;
					if($config['system']['detail_level'] != 'all')						
						$sql .= " AND customer.groupid = '".$_SESSION['curuser']['groupid']."' ";
				}else {
					$sql = "SELECT 
											COUNT(*) AS numRows 
											FROM customer 
											LEFT JOIN note ON customer.id = note.customerid ";
					if($config['system']['detail_level'] != 'all')						
						$sql .= " WHERE customer.groupid = '".$_SESSION['curuser']['groupid']."' ";
				}
			}
		astercrm::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype = null, $order,$table, $ordering = ""){
		global $db,$config;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		if ($config['system']['portal_display_type'] == "note"){
				if ($joinstr != ''){
					$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
					$sql = "SELECT 
											note.id AS id, 
											note, 
											priority,
											private,
											customer.customer AS customer,
											contact.contact AS contact,
											customer.category AS category,
											note.cretime AS cretime,
											note.creby AS creby,
											note.customerid AS customerid,
											note.contactid AS contactid
											FROM note 
											LEFT JOIN customer ON customer.id = note.customerid 
											LEFT JOIN contact ON contact.id = note.contactid
											WHERE $joinstr  
											AND priority>0
											AND note.creby = '".$_SESSION['curuser']['username']."' "
											." ORDER BY $order ".$_SESSION['ordering']
											." LIMIT $start, $limit ";
				}else {
					$sql = "SELECT 
											note.id AS id, 
											note, 
											priority,
											private,
											customer.customer AS customer,
											contact.contact AS contact,
											customer.category AS category,
											note.cretime AS cretime,
											note.creby AS creby ,
											note.customerid AS customerid,
											note.contactid AS contactid
											FROM note 
											LEFT JOIN customer ON customer.id = note.customerid 
											LEFT JOIN contact ON contact.id = note.contactid"
											." AND  note.creby = '".$_SESSION['curuser']['username']."' "
											." ORDER BY $order ".$_SESSION['ordering']
											." LIMIT $start, $limit ";
				}
			}else{
				if ($joinstr != ''){
					$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
					$sql = "SELECT customer.id AS id,
											customer.customer AS customer,
											customer.category AS category,
											customer.contact AS contact,
											customer.cretime as cretime,
											customer.phone as phone,
											customer.mobile as mobile,
											note.note AS note,
											note.priority AS priority,
											note.attitude AS attitude,
											note.private AS private,
											note.creby AS creby
											FROM customer
											LEFT JOIN note ON customer.id = note.customerid"
											." WHERE ".$joinstr;

					if($config['system']['detail_level'] != 'all')						
						$sql .= " AND customer.groupid = '".$_SESSION['curuser']['groupid']."' ";
					
					$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit ";
				}else {
					$sql = "SELECT customer.id AS id,
											customer.customer AS customer,
											customer.category AS category,
											customer.contact AS contact,
											customer.cretime as cretime,
											customer.phone as phone,
											customer.mobile as mobile,
											note.note AS note,
											note.priority AS priority,
											note.attitude AS attitude,
											note.private AS private,
											note.creby AS creby
											FROM customer
											LEFT JOIN note ON customer.id = note.customerid ";
					
					if($config['system']['detail_level'] != 'all')						
						$sql .= " WHERE customer.groupid = '".$_SESSION['curuser']['groupid']."' ";

					$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit ";
				}
			}

		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getAllSpeedDialRecords(){
		global $db;

		$sql = "SELECT number,description FROM speeddial ";


		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE groupid = ".$_SESSION['curuser']['groupid']." ";
		}
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getMyMemberStatus(){
		global $db;
		$sql = "SELECT * FROM queue_agent WHERE (agent='Agent/".$_SESSION['curuser']['agent']."' OR Agent LIKE '%".$_SESSION['curuser']['extension']."@%') ";
		
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function getCampaignResultHtml($dialedlistid,$status = 'NOANSWER'){
		global $db,$locate;
		$sql = "SELECT campaignid,dialednumber FROM dialedlist Where id = $dialedlistid ORDER BY dialtime DESC LIMIT 1";
		astercrm::events($sql);
		$result = & $db->query($sql);
		while ($result->fetchInto($rows)) {
			$campaignId = $rows['campaignid'];
			$tmp_callerid = $rows['dialednumber'];
		}

		$sql = "SELECT id,resultname FROM campaignresult WHERE campaignid = ".$campaignId." AND parentid = 0 AND status = '".$status."'";
		Customer::events($sql);
		$res =& $db->query($sql);
		$html = '';
		$option = '';
		$n = 0;
		while ($res->fetchInto($row)) {
			$option .='<option value="'.$row['id'].'">'.$row['resultname'].'</option>' ;
			if($n == 0){
				$n++;
				$callresultname = $row['resultname'];
				$curparentid = $row['id'];
			}
		}

		if($curparentid != '' && $curparentid != 0){
			$sql = "SELECT id,resultname FROM campaignresult WHERE parentid = $curparentid AND status = '".$status."'";

			Customer::events($sql);
			$res =& $db->query($sql);
			$secondoption = '';
			$n = 0;
			while ($res->fetchInto($row)) {
				$secondoption .='<option value="'.$row['id'].'">'.$row['resultname'].'</option>' ;
				if($n == 0){
					$n++;
					$callresultname = $row['resultname'];
					$callresultid = $row['id'];
				}
			}
		}

		if($option != ''){
			$html = $locate->Translate("Call Result").':&nbsp;<select id="fcallresult" onchange="setSecondCampaignResult()">'.$option.'</select>&nbsp;';
//
			if($secondoption != ''){
				$html .= '&nbsp;<span id="spnScallresult"><select id="scallresult" onchange="setCallresult(this);">'.$secondoption.'</select></span>';
			}else{
				$html .= '&nbsp;<span id="spnScallresult" style="display:none"><select id="scallresult" onchange="setCallresult(this);">'.$secondoption.'</select></span>';
			}

			$html .= '<input type="hidden" id="dialedlistid" name="dialedlistid" value="'.$dialedlistid.'"><input type="hidden" id="tmp60_callerid" name="tmp60_callerid" value="'.$tmp_callerid.'"><input type="hidden" id="callresultname" name="callresultname" value="'.$callresultname.'">&nbsp;<input type="button" value="'.$locate->Translate("Update").'" onclick="updateCallresult();"><span id="updateresultMsg"></span>';
			
		}
		return $html;
	}

	function getAgentData(){
		global $db;
		if($_SESSION['curuser']['channel'] == ''){
			$sql = "SELECT * From queue_agent WHERE agent = 'agent/".$_SESSION['curuser']['agent']."' OR agent LIKE 'local/".$_SESSION['curuser']['extension']."@%'";
		}else{
			$sql = "SELECT * From queue_agent WHERE agent = 'agent/".$_SESSION['curuser']['agent']."' OR agent LIKE 'local/".$_SESSION['curuser']['extension']."@%' OR agent = '".$_SESSION['curuser']['channel']."'";
		}
		//echo $sql;exit;
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
		if(!$res || $res['status'] == 'Unavailable' || $res['status'] == 'Invalid'){//如果无动态座席信息或动态座席未登录,就查静态座席
			$sql = "SELECT * From queue_agent WHERE agent LIKE 'local/".$_SESSION['curuser']['extension']."@%'";
			Customer::events($sql);
			$sres =& $db->getRow($sql);
			if($sres){
				$res = $sres;
			}
		}
	//	print_r($res);exit;
		return $res;
	}

	function formDiallist($dialedlistid){
		global $locate, $db;
		$sql = "SELECT dialednumber, customername,memo,campaignid FROM dialedlist WHERE id = $dialedlistid";
		Customer::events($sql);
		$row =& $db->getRow($sql);
		$html = '';
		if($row){
			$html = Table::Top($locate->Translate("Customer from Diallist"),"formDiallistPopup");  // <-- Set the title for your form.	
			$html .= '<table border="1" width="100%" class="adminlist" id="d" name="d">
						<tr><td width="45%">&nbsp;'.$locate->Translate("Customer Name").':&nbsp;</td><td>'.$row['customername'].'</td></tr>
						<tr><td>&nbsp;'.$locate->Translate("Pone Number").':&nbsp;</td><td>'.$row['dialednumber'].'</td></tr>
						<tr><td>&nbsp;'.$locate->Translate("Memo").':&nbsp;</td><td>'.$row['memo'].'</td></tr>';

			if($row['campaignid'] != 0 && $row['campaignid'] != '') {
				//获取拨号计划的备注
				$CampaignNote = Customer::getCampaignNote($row['campaignid']);
				$html .= '<tr><td>&nbsp;'.$locate->Translate("Campaign Memo").':&nbsp;</td><td>'.$CampaignNote.'</td></tr>';
			}
			
			$html .= '
					</table>'; // <-- Change by your method
			$html .= Table::Footer();
		}
		return $html;
	}

	function getLastOwnDiallistId(){
		global $db;
		$sql = "SELECT id FROM diallist WHERE diallist.assign ='".$_SESSION['curuser']['extension']."' AND dialtime != '0000-00-00 00:00:00' AND callOrder > 0 ORDER BY dialtime ASC, callOrder DESC, id ASC LIMIT 0,5";
		$res =& $db->query($sql);
		$i = 0;
		while($res->fetchInto($row)){
			$idstr .= $row['id'];
			$i++;
		}

		if($i < 5){
			$limit = 5 - $i;
			$sql = "SELECT id FROM diallist WHERE diallist.assign ='".$_SESSION['curuser']['extension']."' AND dialtime = '0000-00-00 00:00:00' AND callOrder > 0 ORDER BY callOrder DESC, id ASC LIMIT 0,$limit";

			$res =& $db->query($sql);
			while($res->fetchInto($row)){
				$idstr .= $row['id'];
			}
		}
		return $idstr;
	}

	function getAgentWorkStat(){
		global $db;
		$sql = "SELECT COUNT(*) AS count, SUM(billsec) AS billsec FROM mycdr WHERE calldate >= '".date("Y-m-d")." 00:00:00' AND  calldate <= '".date("Y-m-d")." 23:59:59' AND mycdr.astercrm_groupid > 0 AND billsec > 0 AND accountid = '".$_SESSION['curuser']['accountid']."'";

		$res = $db->getRow($sql);
		return $res;
	}

	function getKnowledge(){
	    global $db;
		$sql = "SELECT id,knowledgetitle FROM knowledge WHERE knowledgetitle!=''";
		if($_SESSION['curuser']['usertype'] == 'admin'){
            $sql .= "";
		}else{
            $sql .= " AND (groupid='".$_SESSION['curuser']['groupid']."' OR groupid='0')";
		}
		$res = $db->query($sql);
		return $res;

	}

    function knowledge($knowledgeid){
	    global $db;
        $row = Customer::getRecordByID($knowledgeid,'knowledge');
		$html = '<textarea rows="20" cols="70" id="content" wrap="soft" style="overflow:auto;" readonly>'.$row['content'].'</textarea>';
        return $html;
	}

	function showTicketDetail($customerid) {
		global $db,$locate;
		$sql = "SELECT customer,groupid FROM customer WHERE id=$customerid";
		astercrm::events($sql);
		$customerResult = & $db->getRow($sql);
		
		$statusOption = '<select id="Tstatus" name="Tstatus"><option value="new">'.$locate->Translate("new").'</option><option value="panding">'.$locate->Translate("panding").'</option><option value="closed">'.$locate->Translate("closed").'</option><option value="cancel">'.$locate->Translate("cancel").'</option></select>';
		
		$ticketCategory = Customer::getTicketCategory('',$customerResult['groupid']);
		$ticketHtml = Customer::getTicketByCategory($fid);
		$groupHtml = Customer::getGroup($fid);
		$accountHtml = Customer::getAccountForTk($customerResult['groupid']);
		$html = '<form method="post" name="t" id="t">
					<table border="1" width="100%" class="adminlist">
						<tr>
							<td nowrap align="left">'.$locate->Translate("TicketCategory Name").'</td>
							<td align="left">'.$ticketCategory.'</td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Ticket Name").'*</td>
							<td align="left" id="ticketMsg">'.$ticketHtml.'</td>
						</tr>
						<tr>
							<td align="left" width="25%">'.$locate->Translate("Parent TicketDetail ID").'</td>
							<td><input type="text" id="parent_id" name="parent_id"  maxlength="8" /></td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Group Name").'*</td>
							<td align="left" id="groupMsg">'.$groupHtml.'</td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Customer Name").'</td>
							<td align="left"><input type="hidden" name="customerid" value="'.$customerid.'" />'.$customerResult['customer'].'&nbsp;&nbsp;<a onclick="javascript:AllTicketOfMyself('.$customerid.');return false;" href="?">'.$locate->Translate("Customer Tickets").'</a></td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Account Name").'</td>
							<td align="left" id="accountMsg">'.$accountHtml.'</td>
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

	function getAccountForTk($groupid = 0){
		global $db,$locate,$config;
		
		$sql = "SELECT * FROM astercrm_account WHERE ";
		//print_r($groupid);exit;
		if($_SESSION['curuser']['usertype'] == 'admin'){
			if($config['system']['create_ticket'] == 'default') {
				if($groupid == 0) {
					$sql .= "  username!='admin' ";
				} else {
					$sql .= "  username!='admin' AND groupid='".$groupid."' ";
				}
			} else {
				$sql .= " username!='admin' ";
			}
		} else if($_SESSION['curuser']['usertype'] == 'groupadmin'){
			if($config['system']['create_ticket'] == 'system') {
				$sql .= " username!='admin' ";
			} else{
				$sql .= " username!='admin' AND groupid=".$_SESSION['curuser']['groupid']." ";
			}
		} else {//if($_SESSION['curuser']['usertype'] == 'agent')
			if($config['system']['create_ticket'] == 'system'){
				$sql .= " username!='admin' ";
			} else if($config['system']['create_ticket'] == 'group') {
				$sql .= " username!='admin' AND groupid=".$_SESSION['curuser']['groupid']." ";
			} else if($config['system']['create_ticket'] == 'default') {
				$sql .= " id=".$_SESSION['curuser']['accountid']." ";
			}
		}

		/*$sql = "SELECT * FROM astercrm_account WHERE ";
		if($_SESSION['curuser']['usertype'] == 'agent') {
			$sql .= " id='".$_SESSION['curuser']['accountid']."' ";
		} else {
			if($groupid == 0) {
				$sql .= " 1 ";
			} else {
				$sql .= " groupid='".$groupid."' ";
			}
		}*/
		
		astercrm::events($sql);
		$result = & $db->query($sql);

		$html = '<select id="Taccountid" name="Taccountid"><option value="0">'.$locate->Translate('please select').'</option>';
		while($row = $result->fetchRow()){
			if($row['username'] != 'admin'){
				$html .= '<option value="'.$row['id'].'">'.$row['username'].'</option>';
			}
		}
		$html .= '</select>';
		return $html;
	}

	function getTicketCategory($CategoryId = '',$groupId=0) {
		global $db,$locate;
		$sql = "SELECT * FROM tickets ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			if($groupId == 0) {
				$sql .= " WHERE fid=0";
			} else {
				$sql .= " WHERE fid=0 AND groupid IN(0,".$groupId.")";
			}
		}else if($_SESSION['curuser']['usertype'] == 'agent'){
			$sql .= " WHERE fid=0 AND groupid ='".$_SESSION['curuser']['groupid']."' ";
		} else {
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
		$html .= '</select>';
		return $html;//<input type="hidden" id="groupid" name="groupid" value="'.$groupid.'" />
	}

	function insertTicket($f) {
		global $db;
		//$customer_sql = "select id from astercrm_account where username='".$_SESSION['curuser']['username']."'";
		//astercrm::events($customer_sql);
		//$customerid = & $db->getOne($customer_sql);
		$sql = "insert into ticket_details set"
				." ticketcategoryid=".$f['ticketcategoryid'].", "
				." ticketid=".$f['ticketid'].", "
				."parent_id='".(($f['parent_id'] == '')?'':str_pad($f['parent_id'],8,'0',STR_PAD_LEFT))."',"
				." customerid=".$f['customerid'].", "
				." status='".$f['Tstatus']."', "
				." assignto=".$f['assignto'].", "
				." groupid=".$f['groupid'].", "
				." memo='".addslashes($f['Tmemo'])."', "
				." cretime=now(),"
				." creby='".$_SESSION['curuser']['username']."' ;";
		
		astercrm::events($sql);
		$result = & $db->query($sql);
		return $result;
	}

	function checkAlltickets($cid,$status='') {
		global $db,$locate;
		$sql = "SELECT ticket_details.*,ticketname,customer FROM ticket_details LEFT JOIN tickets ON tickets.id=ticket_details.ticketid LEFT JOIN customer ON customer.id=ticket_details.customerid ";
		if($cid != 0){
			$sql .= "WHERE ticket_details.customerid=$cid ";
		}

		if($status != '') {
			$sql .= " AND ticket_details.status='".$status."'";
		}
		
		astercrm::events($sql);
		$result = & $db->query($sql);

		$ticketHtml .= '<form><table width="100%" border="1" class="adminlist">
						<tr>
							<td>'.$locate->Translate('Ticket Name').'</td>
							<td>'.$locate->Translate('Ticket Status').'</td>
							<td>'.$locate->Translate('Ticket Creby').'</td>
						</tr>';
		while($row = $result->fetchRow()) {
			$ticketHtml .= '<tr>
								<td>'.$row['ticketname'].'</td>
								<td>'.$locate->Translate($row['status']).'</td>
								<td>'.$row['creby'].'</td>
							</tr>';
		}
		$ticketHtml .= '</table></form>';
		return $ticketHtml;
	}

	function getAccountid($username="") {
		global $db;
		$sql = "SELECT id FROM astercrm_account WHERE";
		if($username == "") {
			$sql .= " username='".$_SESSION['curuser']['username']."' ";
		} else {
			$sql .= " username='".$username."'";
		}
		astercrm::events($sql);
		$customerid = & $db->getOne($sql);
		return $customerid;
	}

	/**
	*  Imprime la forma para editar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser editado.
	*	@return $html	(string)	Devuelve una cadena de caracteres que contiene la forma con los datos 
	*								a extraidos de la base de datos para ser editados 
	*/
	function formTicketEdit($id){
		global $locate;
		$result =& Customer::getRecordByID($id,'ticket_details');
		
		$categoryHtml = Customer::getTicketCategory($result['ticketcategoryid']);
		$ticketHtml = Customer::getTicketByCategory($result['ticketcategoryid'],$result['ticketid']);
		$groupHtml = Customer::getGroup($result['ticketcategoryid'],$result['groupid']);
		//$customerHtml = Customer::getCustomer($result['groupid'],$result['customerid']);
		$customername = Customer::getCustomername($result['customerid']);
		$accountHtml = Customer::getAccount($result['groupid'],$result['assignto']);

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
					<td><input type="text" id="parent_id" name="parent_id"  maxlength="8" value="'.(($result['parent_id'] == '')?'':str_pad($result['parent_id'],8,'0',STR_PAD_LEFT)).'" /></td>
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
					<td colspan="2" align="center"><input type="button" id="" onclick="xajax_viewSubordinateTicket('.$result['id'].')" value="'.$locate->Translate("Subordinate TicketDetails").'">&nbsp;&nbsp;<button id="submitButton" onClick=\'xajax_updateCurTicket(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>
			 </table>
			';
		$html .= '
				</form>
				'.$locate->Translate("obligatory_fields").'
				';
		return $html;
	}

	function getGroup($FticketId=0,$curGroupid=0){
		global $db,$locate;
		if($FticketId == 0){
			if($_SESSION['curuser']['usertype'] == 'admin'){
				$sql = "SELECT * FROM astercrm_accountgroup ";
			} else {
				$sql = "SELECT * FROM astercrm_accountgroup where id ='".$_SESSION['curuser']['groupid']."'";
			}
			
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
	function getCustomer($groupid=0,$customerid=0) {
		global $db,$locate;
		$sql = "select * from customer";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			if($groupid == 0) {
				$sql .= " ";
			} else {
				$sql .= " WHERE groupid IN (0,".$groupid.") ";
			}
			
		}else if($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$sql .= " WHERE groupid IN (0,".$_SESSION['curuser']['groupid'].") ";
		} else {
			$sql .= " WHERE groupid = '".$_SESSION['curuser']['groupid']."' ";
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

		$sql = "SELECT * FROM astercrm_account WHERE ";
		//print_r($_SESSION['curuser']['usertype']);exit;
		if($_SESSION['curuser']['usertype'] == 'admin'){
			if($config['system']['create_ticket'] == 'default') {
				if($groupid == 0) {
					$sql .= " 1 ";
				} else {
					$sql .= " groupid='".$groupid."' ";
				}
			} else {
				$sql .= " username!='admin' ";
			}
		} else if($_SESSION['curuser']['usertype'] == 'groupadmin'){
			if($config['system']['create_ticket'] == 'system') {
				$sql .= " username!='admin' ";
			} else{
				$sql .= " username!='admin' AND groupid=".$_SESSION['curuser']['groupid']." ";
			}
		} else {// if($_SESSION['curuser']['usertype'] == 'agent')
			if($config['system']['create_ticket'] == 'system'){
				$sql .= " username!='admin' ";
			} else if($config['system']['create_ticket'] == 'group') {
				$sql .= " username!='admin' AND groupid=".$_SESSION['curuser']['groupid']." ";
			} else if($config['system']['create_ticket'] == 'default') {
				$sql .= " id=".$_SESSION['curuser']['accountid']." ";
			}
		}
		/*if ($_SESSION['curuser']['usertype'] == 'admin'){
			if($groupid == 0) {
				$sql .= " username!='admin' ";
			} else {
				$sql .= " username!='admin' AND groupid=".$groupid." ";
			}
		}else if($_SESSION['curuser']['usertype'] == 'agent'){
			$sql .= " id=".$_SESSION['curuser']['accountid']." ";
		} else {
			$sql .= " username!='admin' AND groupid=".$_SESSION['curuser']['groupid']." ";
		}*/
		astercrm::events($sql);
		$result = & $db->query($sql);

		if($_SESSION['curuser']['usertype'] == 'agent'){
			$html = '<select id="assignto" name="assignto">';
		} else {
			$html = '<select id="assignto" name="assignto"><option value="0">'.$locate->Translate('please select').'</option>';
		}
		
		$tmp = '';
		while($row = $result->fetchRow()) {
			$tmp .= '<option value="'.$row['id'].'"';
			if($accountid != 0 && $row['id'] == $accountid) {
				$tmp .= ' selected';
			}
			$tmp .= '>'.$row['username'].'</option>';
		}
		$html .= $tmp.'</select>';
		return $html;
	}

	function getCustomerid($customer) {
		global $db;
		$sql = "SELECT id FROM customer WHERE customer='".$customer."'";
		astercrm::events($sql);
		$customerid = & $db->getOne($sql);
		return $customerid;
	}

	function getTicketInWork(){
		global $db,$locate;
		if($_SESSION['curuser']['usertype'] == 'admin') {
			$sql = "SELECT COUNT(*) FROM ticket_details LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto WHERE username='".$_SESSION['curuser']['username']."'";
		} else {
			$sql = "SELECT COUNT(*) FROM ticket_details LEFT JOIN astercrm_account ON astercrm_account.id = ticket_details.assignto WHERE (username='".$_SESSION['curuser']['username']."' OR (ticket_details.groupid='".$_SESSION['curuser']['groupid']."' and ticket_details.assignto = 0))";
		}
		$panding_sql = $sql." AND status = 'panding'";
		astercrm::events($panding_sql);
		$panding_num = & $db->getOne($panding_sql);

		$new_sql = $sql." AND status = 'new'";
		astercrm::events($new_sql);
		$new_num = & $db->getOne($new_sql);
		//.$locate->Translate('new').":"   $locate->Translate('panding').":".
		$html = "(".$new_num."/".$panding_num.")";
		return $html;
	}
		
	function updateCurTicket($f){
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

	function generateUniquePin($len=10) {
	
		srand((double)microtime()*1000003);
		$prefix = rand(1000000000,9999999999);
		if(is_numeric($len) && $len > 10 && $len < 20 ){
			$len -= 10;
			$min = 1;
			for($i=1; $i < $len; $i++){
			$min = $min*10;
			}
			$max = ($min*10) - 1;
			$pin = $prefix.rand($min,$max);
						
		}elseif($len <= 10){
			$pin = $prefix;
		}else{
			$pin = $prefix.rand(1000000000,9999999999);
		}		
		return $pin;
	}


	function getMsgInCampaign($groupId) {
		global $db;
		$sql = "SELECT id,campaignname,queuename FROM campaign WHERE queuename != '' AND groupid='$groupId' AND enable= 1 ORDER BY queuename ASC";
		$result = & $db->query($sql);

		$dataArray = array();
		while($row = $result->fetchRow()) {
			$dataArray[] = $row;
		}
		return $dataArray;
	}

	function getCampaignNote($campaign_id){
		global $db;
		$sql = "SELECT campaignnote FROM campaign WHERE id = $campaign_id ";
		astercrm::events($sql);
		$result = & $db->getOne($sql);
		return $result;
	}

	function createSMSForm($sendType,$objId=null){
		global $locate;
		if($sendType == 'callerid') {
			$sms_templates = Customer::getSMSTemplates();
		} else if($sendType == 'campaign_number') {
			$sms_templates = Customer::getSMSTemplates('campaign_number',$objId);
		} else if($sendType == 'trunk_number') {
			$sms_templates = Customer::getSMSTemplates('trunk_number',$objId);
		}
		
		$html = 
			'<form id="sendsmsForm" name="sendsmsForm" method="post">
			<table class="adminlist" width="100%" border="1" align="center">
				<tr>
					<td>'.$locate->translate('Sender').'<input type="hidden" id="incomeNumber" name="incomeNumber" value="'.$objId.'"></td>
					<td>';
					if($sendType == 'callerid' && $objId != null) {
						$html .= '<input type="text" id="sender" name="sender" size="40" maxlength="20" value="'.$objId.'" />';
					} else if($sendType == 'trunk_number' && $objId != null) {
						$html .= '<input type="text" id="sender" name="sender" size="40" maxlength="20" value="'.$objId.'" />';
					} else if($sendType == 'campaign_number' && $objId != null) {
						$sms_number = Customer::getSmsNumberByCampaign($objId);
						$html .= '<input type="text" id="sender" name="sender" size="40" maxlength="20" value="'.$sms_number.'" />';
					}
					$html .= '</td>
				</tr>
				<tr>
					<td>'.$locate->translate('SMS Template').'</td>
					<td><select id="sms_template" name="sms_template" onchange="xajax_templateChange(this.value);">'.$sms_templates.'</select></td>
				</tr>
				<tr>
					<td>'.$locate->translate('Text').'</td>
					<td>
						'.$locate->translate('you can enter').' <span id="inputcodeLength">70</span> '.$locate->translate('characters').'<br/>
						<textarea id="SMSmessage" name="SMSmessage" cols="50" rows="8" onkeyup="calculateMessage(this);"></textarea>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><input type="button" value="'.$locate->translate('send').'" onclick="SendSms(xajax.getFormValues(\'sendsmsForm\'));return false;" /></td>
				</tr>
			</table>
			</form>';

		return $html;
	}


	function getSMSTemplates($belongto,$objId=null){
		global $db;
		$sql = "SELECT * FROM sms_templates WHERE ";
		if($belongto == 'campaign_number') {
			if($objId != null) {
				$sql .= " (belongto = 'campaign' AND campaign_id=".$objId.") OR belongto = 'all' ";
			} else {
				$sql .= " belongto IN ('campaign','all') ";
			}
		} else if($belongto == 'trunk_number') {
			if($objId != null) {
				$sql .= " (belongto = 'trunk' AND trunkinf_id=".$objId.") OR  belongto = 'all' ";
			} else {
				$sql .= " belongto IN ('trunk','all') ";
			}
		} else {
			$sql .= " belongto='all' ";
		}
		astercrm::events($sql);
		$result = & $db->query($sql);
		
		$optionHtml = '<option value=""></option>';
		while($row = $result->fetchRow()) {
			$optionHtml .= '<option value="'.$row['id'].'">'.$row['templatetitle'].'</option>';
		}
		return $optionHtml;
	}

	function getTemplateById($id){
		global $db;
		$sql = "SELECT * FROM sms_templates WHERE id=$id ";
		astercrm::events($sql);
		$result = & $db->getRow($sql);
		return $result;
	}

	function getSmsNumberByCampaign($campaignid){
		global $db;
		$sql = "SELECT sms_number FROM campaign WHERE id=$campaignid ";
		astercrm::events($sql);
		$result = & $db->getOne($sql);
		return $result;
	}

	function insertSentSms($f){
		global $db;
		if($f['incomeNumber'] == 0) $f['incomeNumber'] = '';
		$sql = "INSERT INTO sms_sents SET
				username = '".$_SESSION['curuser']['username']."',
				callerid = '".$f['incomeNumber']."',
				target   = '".$f['sender']."',
				content  = '".$f['SMSmessage']."',
				cretime  = now()
		";
		astercrm::events($sql);
		$result = & $db->query($sql);
		return $result;
	}

	function addNewTicket(){
		global $db,$locate;
		$statusOption = '<select id="Tstatus" name="Tstatus"><option value="new">'.$locate->Translate("new").'</option><option value="panding">'.$locate->Translate("panding").'</option><option value="closed">'.$locate->Translate("closed").'</option><option value="cancel">'.$locate->Translate("cancel").'</option></select>';
		
		$ticketCategory = Customer::getTicketCategory();
		//$ticketHtml = Customer::getTicketByCategory($fid);
		//$groupHtml = Customer::getGroup($fid);
		//$accountHtml = Customer::getAccountForTk($customerResult['groupid']);
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
							<td align="left" width="25%">'.$locate->Translate("Parent TicketDetail ID").'</td>
							<td><input type="text" id="parent_id" name="parent_id"  maxlength="8" /></td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Group Name").'*</td>
							<td align="left" id="groupMsg"></td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Customer Name").'*</td>
							<td align="left" id="customerMsg"><input type="text" id="ticket_customer" name="ticket_customer" onkeyup="ajax_showOptions(this,\'getCustomersByLetters\',event)" size="25" maxlength="50" autocomplete="off" /><input type="hidden" id="ticket_customer_hidden" name="customerid" value="" /></td>
						</tr>
						<tr>
							<td nowrap align="left">'.$locate->Translate("Account Name").'</td>
							<td align="left" id="accountMsg"></td>
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
							<td colspan="2" align="center"><button onClick=\'xajax_saveNewTicket(xajax.getFormValues("t"));return false;\'>'.$locate->Translate("continue").'</button></td>
						</tr>
					</table>';
			$html .='
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

	function getNoticeInterval($groupid){
		global $db;
		$sql = "SELECT notice_interval FROM astercrm_accountgroup WHERE id ='".$groupid."' ";
		$notice_interval = & $db->getOne($sql);
		return $notice_interval;
	}
	function ticketNoticeValid(){
		global $db;
		$lastNoticetime = $_SESSION['ticketNoticeTime'];
		$accountId = $_SESSION['curuser']['accountid'];
		
		$sql = "SELECT * FROM ticket_details WHERE assignto='".$accountId."' AND status='new' ;";
		astercrm::events($sql);
		$result = & $db->query($sql);

		$resultArray = array();
		while($row = $result->fetchRow()){
			$resultArray[] = $row;
		}
		return $resultArray;
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
		$sql = "INSERT INTO `ticket_op_logs` SET operate='".$operate."',`op_field`='".$op_field."',`op_ori_value`='".$op_ori_value."',`op_new_value`='".$op_new_value."',`curOwner`='".$curOwner."',`groupid`='".$groupid."',`operator`='".$_SESSION['curuser']['username']."',optime=now() ;";
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

	function formRequireReasion($queueno,$context,$agent){
		global $locate,$config;
	$html = '
			<!-- No edit the next line -->
			<form method="post" name="require_reasion" id="require_reasion">
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Pause Reasion").'</td>
					<td align="left">
						<textarea rows="5" cols="40" id="require_reasion" name="require_reasion"></textarea>
						<input type="hidden" id="" name="require_reasion_queueno" value="'.$queueno.'" />
						<input type="hidden" id="" name="require_reasion_context" value="'.$context.'" />
						<input type="hidden" id="" name="require_reasion_agent" value="'.$agent.'" />
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_requireReasionWhenPause(xajax.getFormValues("require_reasion"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>

			 </table>
			';
		$html .='
			</form>';
		return $html;
	}

	function savePauseReasion($queueno,$action,$reasion){
		global $db;
		$sql = 
			"INSERT INTO `agent_queue_log` SET 
				action = '".$action."',
				queue = '".$queueno."',
				account = '".$_SESSION['curuser']['username']."',
				reasion = '".$reasion."',
				groupid = '".$_SESSION['curuser']['groupid']."',
				pausetime = 0,
				cretime = now() 
			";
		astercrm::events($sql);
		$result = & $db->query($sql);
		return $result;
	}
	
	function savePauseToContinue($queueno){
		global $db;
		$chkSql = "SELECT * FROM `agent_queue_log` WHERE account='".$_SESSION['curuser']['username']."' ORDER BY cretime DESC LIMIT 1 ; ";
		astercrm::events($chkSql);
		$chkResult = & $db->getRow($chkSql);
		
		if($chkResult['action'] == 'pause') {
			$sql = 
			"INSERT INTO `agent_queue_log` SET 
				action = 'continue',
				queue = '".$queueno."',
				account = '".$_SESSION['curuser']['username']."',
				reasion = '',
				groupid = '".$_SESSION['curuser']['groupid']."',
				pausetime = 0,
				cretime = now() 
			";
			astercrm::events($sql);
			$saveResult = & $db->query($sql);

			if($saveResult) {
				$pausetime = strtotime(date("Y-m-d H:i:s"))-strtotime($chkResult['cretime']);
				$updateSql = "UPDATE `agent_queue_log` SET pausetime='".$pausetime."' WHERE id='".$chkResult['id']."' ";
				astercrm::events($updateSql);
				$chkResult = & $db->query($updateSql);
			}
		}
	}

	function get_real_ip() {
		$ip=false;
		if(!empty($_SERVER["HTTP_CLIENT_IP"])) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
			if ($ip) {
				array_unshift($ips, $ip); $ip = FALSE;
			}
			for ($i = 0; $i < count($ips); $i++) {
				if (!eregi ("^(10|172\.16|192\.168)\.", $ips[$i])) {
					$ip = $ips[$i];
					break;
				}
			}
		}
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
	}

	function getSrcnameByCurid($curid){
		global $db;
		
		$sql = "SELECT srcname FROM curcdr WHERE id='".$curid."'";
		astercrm::events($sql);
		$srcname = & $db->getOne($sql);
		return $srcname;
	}
}
?>
