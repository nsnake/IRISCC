<?php /*******************************************************************************
* campaign.grid.inc.php
* campaign操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加campaign表单的HTML
	formEdit					生成编辑campaign表单的HTML
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.0456  2007/10/30 13:15:00  last modified by solo
* Desc: add channel field 

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'campaign.common.php';
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
		
		$sql = "SELECT campaign.*, groupname, servers.name as servername FROM campaign LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.groupid = campaign.groupid LEFT JOIN servers ON servers.id = campaign.serverid ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE campaign.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

//		if ($creby != null)
//			$sql .= " WHERE note.creby = '".$_SESSION['curuser']['username']."' ";
			

		if($order == null){
			$sql .= " LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit";
		}

		//echo $sql;
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

	function &getRecordsFiltered($start, $limit, $filter = null, $content = null, $order = null, $ordering = ""){
		global $db;
		
		if(($filter != null) and ($content != null)){
			$sql = "SELECT * FROM campaign"
					." WHERE ".$filter." like '%".$content."%' "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}
	

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

		$sql = "SELECT campaign.*, groupname, servers.name as servername FROM campaign LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaign.groupid LEFT JOIN servers ON servers.id = campaign.serverid  WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " campaign.groupid = ".$_SESSION['curuser']['groupid']." ";
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

	function insertNewCampaign($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$bindqueue = 0;
		if ($f['bindqueue'] =="on"){
			$bindqueue = 1;
		}
		if ($f['dialtwoparty'] =="on"){
			$dialtwoparty = "yes";
		} else {
			$dialtwoparty = "no";
		}

		if ($f['use_ext_chan'] =="on"){
			$useExtChan = "yes";
		} else {
			$useExtChan = "no";
		}
		

		$query= "INSERT INTO campaign SET "
				."campaignname='".$f['campaignname']."', "
				."campaignnote='".$f['campaignnote']."', "
				."enable='".$f['enable']."', "
				."serverid='".$f['serverid']."', "
				."waittime='".$f['waittime']."', "
				."worktime_package_id='".$f['worktime_package_id']."', "
				."outcontext='".$f['outcontext']."', "
				."incontext='".$f['incontext']."', "
				."nextcontext='".$f['nextcontext']."', "
				."firstcontext='".$f['firstcontext']."', "
				."inexten='".$f['inexten']."', "
				."queuename='".$f['queuename']."', "
				."bindqueue='".$bindqueue."', "
				."max_dialing='".$f['max_dialing']."', "
				."maxtrytime='".$f['maxtrytime']."', "
				."recyletime='".$f['recyletime']."', "
				."enablerecyle='".$f['enablerecyle']."', "
				."minduration='".$f['minduration']."', "
				."minduration_billsec='".$f['minduration_billsec']."', "
				."minduration_leg_a='".$f['minduration_leg_a']."', "
				."callerid='".$f['callerid']."', "
				."use_ext_chan='".$useExtChan."', "
				."groupid='".$f['groupid']."', "
				."dialtwoparty='".$dialtwoparty."', "
				."queue_context = '".$f['queue_context']."',"
				."sms_number = '".$f['sms_number']."',"
				."balance = '".$f['balance']."',"
				."init_billing = '".$f['init_billing']."',"
				."billing_block = '".$f['billing_block']."',"
				."enablebalance = '".$f['enablebalance']."',"
				."creby = '".$_SESSION['curuser']['username']."',"
				."cretime = now()";
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}


	function updateCampaignRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$bindqueue = 0;
		if ($f['bindqueue'] =="on"){
			$bindqueue = 1;
		}
		
		if ($f['dialtwoparty'] =="on"){
			$dialtwoparty = "yes";
		} else {
			$dialtwoparty = "no";
		}

		if ($f['use_ext_chan'] =="on"){
			$useExtChan = "yes";
		} else {
			$useExtChan = "no";
		}

		$limit_type = '';
		if ($f['queuename'] == ""){
			$limit_type = 'channel';
		}

		$query= "UPDATE campaign SET "
				."campaignname='".$f['campaignname']."', "
				."campaignnote='".$f['campaignnote']."', "
				."enable='".$f['enable']."', "	
				."serverid='".$f['serverid']."', "
				."worktime_package_id='".$f['worktime_package_id']."', "
				."waittime='".$f['waittime']."', "
				."outcontext='".$f['outcontext']."', "
				."incontext='".$f['incontext']."', "
				."nextcontext='".$f['nextcontext']."', "
				."firstcontext='".$f['firstcontext']."', "
				."inexten='".$f['inexten']."', "
				."queuename='".$f['queuename']."', "
				."bindqueue='".$bindqueue."', "
				."max_dialing='".$f['max_dialing']."', "
				."maxtrytime='".$f['maxtrytime']."', "
				."recyletime='".$f['recyletime']."', "
				."enablerecyle='".$f['enablerecyle']."', "
				."minduration='".$f['minduration']."', "
				."minduration_billsec='".$f['minduration_billsec']."', "
				."minduration_leg_a='".$f['minduration_leg_a']."', "
				."callerid='".$f['callerid']."', "
				."use_ext_chan='".$useExtChan."', "
				."dialtwoparty='".$dialtwoparty."', "
				."queue_context='".$f['queue_context']."', "
				."sms_number='".$f['sms_number']."', "
				."balance = '".$f['balance']."',"
				."init_billing = '".$f['init_billing']."',"
				."billing_block = '".$f['billing_block']."',"
				."enablebalance = '".$f['enablebalance']."',"
				."groupid='".$f['groupid']."' ";
		if($limit_type != ''){
			$query .= ",limit_type='$limit_type' ";
		}

		$query .= "WHERE id=".$f['id'];

		astercrm::events($query);
		$res =& $db->query($query);
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
			$sql = " SELECT COUNT(*) FROM campaign LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaign.groupid";
		}else{
			$sql = " SELECT COUNT(*) FROM campaign LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaign.groupid WHERE campaign.groupid = ".$_SESSION['curuser']['groupid']." ";
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

			$sql = "SELECT COUNT(*) FROM campaign LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaign.groupid LEFT JOIN servers ON servers.id = campaign.serverid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " campaign.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"campaign");

		$sql = "SELECT campaign.*, groupname, servers.name as servername FROM campaign LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaign.groupid LEFT JOIN servers ON servers.id = campaign.serverid WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " campaign.groupid = ".$_SESSION['curuser']['groupid']." ";
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
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype,"campaign");

			$sql = "SELECT COUNT(*) FROM campaign LEFT JOIN astercrm_accountgroup ON astercrm_accountgroup.id = campaign.groupid LEFT JOIN servers ON servers.id = campaign.serverid WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " campaign.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

	function getCountAnswered($campaignid){
		global $db;
		$query = "SELECT COUNT(*) FROM campaigndialedlist WHERE campaignid = $campaignid AND answertime > '0000-00-00 00:00:00'";
		Customer::events($query);
		$res =& $db->getOne($query);
		return $res;
	}
	
	/**
	*  Imprime la forma para agregar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param ninguno
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma para insertar 
	*							un nuevo registro.
	*/
	
	function formAdd(){
			global $locate,$config,$db;

		if ($_SESSION['curuser']['usertype'] == 'admin'){
				$res = Customer::getGroups();
				$grouphtml .= '<select name="groupid" id="groupid">';
				while ($row = $res->fetchRow()) {
						$grouphtml .= '<option value="'.$row['groupid'].'"';
						$grouphtml .='>'.$row['groupname'].'</option>';
				}
				$grouphtml .= '</select>';
		}else{
				$grouphtml .= $_SESSION['curuser']['group']['groupname'].'<input id="groupid" name="groupid" type="hidden" value="'.$_SESSION['curuser']['groupid'].'">';
		}

		$query = "SELECT id,worktimepackage_name From worktimepackages";
		if($_SESSION['curuser']['usertype'] != 'admin'){
			$query .= " Where groupid =".$_SESSION['curuser']['groupid'];
		}

		$worktimepackage_res = $db->query($query);
		$worktimepackagehtml .= '<select name="worktime_package_id" id="worktime_package_id">
						<option value="0">'.$locate->Translate("Any time").'</option>';
		while ($worktimepackage_row = $worktimepackage_res->fetchRow()) {
			$worktimepackagehtml .= '<option value="'.$worktimepackage_row['id'].'"';
			$worktimepackagehtml .='>'.$worktimepackage_row['worktimepackage_name'].'</option>';
		}
		$worktimepackagehtml .= '</select>';
		
		$query = "SELECT id,name From servers";
		$server_res = $db->query($query);
		$serverhtml .= '<select name="serverid" id="serverid">
						<option value="0">'.$locate->Translate("Default Server").'</option>';
		while ($server_row = $server_res->fetchRow()) {
			$serverhtml .= '<option value="'.$server_row['id'].'"';
			$serverhtml .='>'.$server_row['name'].'</option>';
		}
		$serverhtml .= '</select>';

	$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Campaign Name").'*</td>
					<td align="left"><input type="text" id="campaignname" name="campaignname" size="30" maxlength="60"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Campaign Note").'</td>
					<td align="left"><input type="text" id="campaignnote" name="campaignnote" size="30" maxlength="255"></td>
				</tr>
				<tr>					
					<td align="left" colspan="2">'.$locate->Translate("Enable").'&nbsp;<input type="radio" id="enable" name="enable" value="1" checked>&nbsp;'.$locate->Translate("Disable").'&nbsp;<input type="radio" id="enable" name="enable" value="0" ></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Asterisk Server").'*</td>
					<td align="left">'.$serverhtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Worktime package").'</td>
					<td align="left">'.$worktimepackagehtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Waitting time").'</td>
					<td align="left"><input type="text" id="waittime" name="waittime" size="30" maxlength="3" value="45"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Call Result Detect").'</td>
					<td align="left"><input type="checkbox" id="crdenable" name="crdenable" onclick="if(this.checked){xajax.$(\'crdtr\').style.display=\'\';xajax.$(\'amdtr\').style.display=\'\';}else{xajax.$(\'crdtr\').style.display=\'none\';xajax.$(\'amdtr\').style.display=\'none\';}">&nbsp;</td>
				</tr>
				<tr id="crdtr" style="display:none">
					<td nowrap align="left">'.$locate->Translate("CRD context").'</td>
					<td align="left"><input type="text" id="crdcontext" name="crdcontext" size="26" maxlength="60" value="'.$config['system']['crdcontext'].'" ></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Outcontext").'*</td>
					<td align="left"><input type="text" id="outcontext" name="outcontext" size="30" maxlength="60" value="'.$config['system']['outcontext'].'"></td>
				</tr>
				
				<tr id="amdtr" style="display:none">
					<td nowrap align="left">'.$locate->Translate("AMD context").'</td>
					<td align="left"><input type="text" id="amdcontext" name="amdcontext" size="26" maxlength="60" value="'.$config['system']['amdcontext'].'" ></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Incontext").'*</td>
					<td align="left"><input type="text" id="incontext" name="incontext" size="30" maxlength="60" value="'.$config['system']['incontext'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dial two party at same time").'</td>
					<td align="left"><input type="checkbox" id="dialtwoparty" name="dialtwoparty">&nbsp;</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Inexten").'</td>
					<td align="left"><input type="text" id="inexten" name="inexten" size="30" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Queue number").'</td>
					<td align="left">
						<input type="text" id="queuename" name="queuename" size="15" maxlength="15">
						<input type="checkbox" name="bindqueue" id="bindqueue">'.$locate->Translate("send calls to this queue directly").'
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Queue Context").'</td>
					<td align="left"><input type="text" id="queue_context" name="queue_context" size="30" maxlength="60"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Use Extension Channel For Queue").'</td>
					<td align="left"><input type="checkbox" id="use_ext_chan" name="use_ext_chan" /></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("CallerID").'</td>
					<td align="left"><input type="text" id="callerid" name="callerid" size="30" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'*</td>
					<td align="left">'.$grouphtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Max Dialing").'</td>
					<td align="left"><input type="text" id="max_dialing" value="0" name="max_dialing" size="10" maxlength="4"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Max trytime").'</td>
					<td align="left"><input type="text" id="maxtrytime" value="1" name="maxtrytime" size="10" maxlength="10"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Recyle time").'</td>
					<td align="left"><input type="text" id="recyletime" value="3600" name="recyletime" size="10" maxlength="10"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Enable Auto Recyle").'</td>
					<td align="left"><select name="enablerecyle" id="enablerecyle"><option value="no">'.$locate->Translate("no").'</option><option value="yes">'.$locate->Translate("yes").'</option></select></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Min Duration").'</td>
					<td align="left"><input type="text" id="minduration" value="0" name="minduration" size="10" maxlength="10"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Agent Answer Min Duration").'</td>
					<td align="left"><input type="text" id="minduration_billsec" value="0" name="minduration_billsec" size="10" maxlength="10"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Customer Answer Min Duration").'</td>
					<td align="left"><input type="text" id="minduration_leg_a" value="0" name="minduration_leg_a" size="10" maxlength="10"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("SMS Number").'</td>
					<td align="left"><input type="text" id="sms_number" name="sms_number" size="20" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Balance").'</td>
					<td align="left"><input type="text" id="balance" name="balance" size="20" maxlength="11"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Init Billing").'</td>
					<td align="left"><input type="text" id="init_billing" name="init_billing" size="20" maxlength="11"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Billing Block").'</td>
					<td align="left"><input type="text" id="billing_block" name="billing_block" size="20" maxlength="11"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Enable Balance").'</td>
					<td align="left"><select name="enablebalance" id="enablebalance"><option value="yes">'.$locate->Translate("yes").'</option><option value="no">'.$locate->Translate("no").'</option><option value="strict">'.$locate->Translate("strict").'</option></select></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_save(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>

			 </table>
			';

		$html .='
			</form>
			'.$locate->Translate("obligatory_fields").'
			';
		
		return $html;
	}

	/**
	*  Imprime la forma para editar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser editado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma con los datos 
	*									a extraidos de la base de datos para ser editados 
	*/
	
	function formEdit($id){
		global $locate,$db,$config;
		$campaign =& Customer::getRecordByID($id,'campaign');

		if ($_SESSION['curuser']['usertype'] == 'admin'){ 
				$grouphtml .=	'<select name="groupid" id="groupid" >
																<option value=""></option>';
				$res = Customer::getGroups();
				while ($row = $res->fetchRow()) {
					$grouphtml .= '<option value="'.$row['groupid'].'"';
					if($row['groupid'] == $campaign['groupid']){
						$grouphtml .= ' selected ';
					}
					$grouphtml .= '>'.$row['groupname'].'</option>';
				}
				$grouphtml .= '</select>';
		}else{
				
				$grouphtml .= $_SESSION['curuser']['group']['groupname'].'<input type="hidden" name="groupid" id="groupid" value="'.$_SESSION['curuser']['groupid'].'">';
		}
		$bindqueue = "";
		if ($campaign['bindqueue'] == 1){
			$bindqueue = "checked";
		}

		$dialTochecked = "";
		if ($campaign['dialtwoparty'] == "yes"){
			$dialTochecked = "checked";
		}

		$query = "SELECT id,name From servers";
		$server_res = $db->query($query);
		$serverhtml .= '<select name="serverid" id="serverid">
						<option value="0">'.$locate->Translate("Default Server").'</option>';
		while ($server_row = $server_res->fetchRow()) {
			$serverhtml .= '<option value="'.$server_row['id'].'"';
				if($server_row['id'] == $campaign['serverid']){
					$serverhtml .= ' selected ';
				}
				$serverhtml .= '>'.$server_row['name'].'</option>';
		}
		$serverhtml .= '</select>';

		$query = "SELECT id,worktimepackage_name From worktimepackages";
		if($_SESSION['curuser']['usertype'] != 'admin'){
			$query .= " Where groupid =".$_SESSION['curuser']['groupid'];
		}
		$worktimepackage_res = $db->query($query);
		$worktimepackagehtml .= '<select name="worktime_package_id" id="worktime_package_id">
						<option value="0">'.$locate->Translate("Any time").'</option>';
		while ($worktimepackage_row = $worktimepackage_res->fetchRow()) {
			$worktimepackagehtml .= '<option value="'.$worktimepackage_row['id'].'"';
			if($worktimepackage_row['id'] == $campaign['worktime_package_id']){
				$worktimepackagehtml .= ' selected ';
			}
			$worktimepackagehtml .='>'.$worktimepackage_row['worktimepackage_name'].'</option>';
		}
		$worktimepackagehtml .= '</select>';

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Campaign Name").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $campaign['id'].'"><input type="text" id="campaignname" name="campaignname" size="30" maxlength="60" value="'.$campaign['campaignname'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Campaign Note").'</td>
					<td align="left"><input type="text" id="campaignnote" name="campaignnote" size="30" maxlength="255" value="'.$campaign['campaignnote'].'"></td>
				</tr>
				<tr>					
					<td align="left" colspan="2">'.$locate->Translate("Enable").'&nbsp;<input type="radio" id="enable" name="enable" value="1"';
			if($campaign['enable']) 
				$html .= 'checked>&nbsp;'.$locate->Translate("Disable").'&nbsp;<input type="radio" id="enable" name="enable" value="0" ></td>';
			else
				$html .= '>&nbsp;'.$locate->Translate("Disable").'&nbsp;<input type="radio" id="enable" name="enable" value="0" checked></td>';
			
			if($campaign['firstcontext'] != '' && $campaign['nextcontext'] != ''){
				$crdchecked = 'checked';
				$outcontext = $campaign['firstcontext'];
				$crdcontext = $campaign['outcontext'];
				$crdtr='';

				$amdchecked = 'checked';
				$incontext = $campaign['nextcontext'];
				$amdcontext = $campaign['incontext'];
				$amdtr='';
			}else{
				$outcontext = $campaign['outcontext'];
				$crdcontext = $config['system']['crdcontext'];
				$crdtr='style="display:none"';

				$incontext = $campaign['incontext'];
				$amdcontext = $config['system']['amdcontext'];
				$amdtr='style="display:none"';
			}

			if($campaign['enablerecyle'] == 'no'){
				$recyleno = 'selected'; 
				$recyleyes = ''; 
			}else{
				$recyleno = ''; 
				$recyleyes = 'selected'; 
			}

			if($campaign['use_ext_chan'] == 'yes'){
				$useExtChanChecked = 'checked';
			}else{
				$useExtChanChecked = '';
			}

			if($campaign['enablebalance'] == 'no'){
				$enablebalanceNo = 'selected'; 
				$enablebalanceYes = '';
				$enablebalanceStrict = '';
			}else if($campaign['enablebalance'] == 'yes'){
				$enablebalanceNo = ''; 
				$enablebalanceYes = 'selected';
				$enablebalanceStrict = ''; 
			} else {
				$enablebalanceNo = ''; 
				$enablebalanceYes = '';
				$enablebalanceStrict = 'selected';
			}

			$html .= 
				'</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Asterisk Server").'*</td>
					<td align="left">'.$serverhtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Worktime package").'</td>
					<td align="left">'.$worktimepackagehtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Waitting time").'</td>
					<td align="left"><input type="text" id="waittime" name="waittime" size="30" maxlength="3" value="'.$campaign['waittime'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Call Result Detect").'</td>
					<td align="left"><input type="checkbox" id="crdenable" name="crdenable" onclick="if(this.checked == true){xajax.$(\'crdtr\').style.display=\'\';xajax.$(\'amdtr\').style.display=\'\';}else{xajax.$(\'crdtr\').style.display=\'none\';xajax.$(\'amdtr\').style.display=\'none\';}" '.$crdchecked.'>&nbsp;</td>
				</tr>
				<tr id="crdtr" '.$crdtr.'>
					<td nowrap align="left">'.$locate->Translate("CRD context").'</td>
					<td align="left"><input type="text" id="crdcontext" name="crdcontext" size="26" maxlength="60" value="'.$crdcontext.'" ></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Outcontext").'*</td>
					<td align="left"><input type="text" id="outcontext" name="outcontext" size="30" maxlength="60" value="'.$outcontext.'"></td>
				</tr>				
				<tr id="amdtr" '.$amdtr.'>
					<td nowrap align="left">'.$locate->Translate("AMD context").'</td>
					<td align="left"><input type="text" id="amdcontext" name="amdcontext" size="26" maxlength="60" value="'.$amdcontext.'" ></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Incontext").'*</td>
					<td align="left"><input type="text" id="incontext" name="incontext" size="30" maxlength="60" value="'.$incontext.'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dial two party at same time").'</td>
					<td align="left"><input type="checkbox" id="dialtwoparty" name="dialtwoparty" '.$dialTochecked.'>&nbsp;</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Inexten").'</td>
					<td align="left"><input type="text" id="inexten" name="inexten" size="30" maxlength="30" value="'.$campaign['inexten'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Queue number").'</td>
					<td align="left">
						<input type="text" id="queuename" name="queuename" size="30" maxlength="30" value="'.$campaign['queuename'].'">
						<input type="checkbox" name="bindqueue" id="bindqueue" '.$bindqueue.'>'.$locate->Translate("send calls to this queue directly").'						
						</td>
				</tr>

				<tr>
					<td nowrap align="left">'.$locate->Translate("Queue Context").'</td>
					<td align="left"><input type="text" id="queue_context" name="queue_context" size="30" maxlength="60" value="'.$campaign['queue_context'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Use Extension Channel For Queue").'</td>
					<td align="left"><input type="checkbox" id="use_ext_chan" name="use_ext_chan" '.$useExtChanChecked.'/></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("CallerID").'</td>
					<td align="left"><input type="text" id="callerid" name="callerid" size="30" maxlength="30" value="'.$campaign['callerid'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'*</td>
					<td align="left">'.$grouphtml.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Max Dialing").'</td>
					<td align="left"><input type="text" id="max_dialing" name="max_dialing" size="10" maxlength="4" value="'.$campaign['max_dialing'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Max trytime").'</td>
					<td align="left"><input type="text" id="maxtrytime" name="maxtrytime" size="30" maxlength="30" value="'.$campaign['maxtrytime'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Recyle time").'</td>
					<td align="left"><input type="text" id="recyletime" name="recyletime" size="10" maxlength="10" value="'.$campaign['recyletime'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Enable Auto Recyle").'</td>
					<td align="left"><select name="enablerecyle" id="enablerecyle"><option value="no" '.$recyleno.' >'.$locate->Translate("no").'</option><option value="yes" '.$recyleyes.'>'.$locate->Translate("yes").'</option></select></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Min Duration").'</td>
					<td align="left"><input type="text" id="minduration" name="minduration" size="10" maxlength="10" value="'.$campaign['minduration'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Agent Answer Min Duration").'</td>
					<td align="left"><input type="text" id="minduration_billsec" name="minduration_billsec" size="10" maxlength="10" value="'.$campaign['minduration_billsec'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Customer Answer Min Duration").'</td>
					<td align="left"><input type="text" id="minduration_leg_a" name="minduration_leg_a" size="10" maxlength="10" value="'.$campaign['minduration_leg_a'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("SMS Number").'</td>
					<td align="left"><input type="text" id="sms_number" name="sms_number" size="20" maxlength="30" value="'.$campaign['sms_number'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Balance").'</td>
					<td align="left"><input type="text" id="balance" name="balance" size="20" maxlength="11" value="'.$campaign['balance'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Init Billing").'</td>
					<td align="left"><input type="text" id="init_billing" name="init_billing" size="20" maxlength="11" value="'.$campaign['init_billing'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Billing Block").'</td>
					<td align="left"><input type="text" id="billing_block" name="billing_block" size="20" maxlength="11" value="'.$campaign['billing_block'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Enable Balance").'</td>
					<td align="left"><select name="enablebalance" id="enablebalance"><option value="yes" '.$enablebalanceYes.'>'.$locate->Translate("yes").'</option><option value="no" '.$enablebalanceNo.'>'.$locate->Translate("no").'</option><option value="strict" '.$enablebalanceStrict.'>'.$locate->Translate("strict").'</option></select></td>
				</tr>';

				//print_r($campaign);exit;
				$ad_hours = intval($campaign['billsec']/3600);
				$ad_min = intval(($campaign['billsec']%3600)/60);
				$ad_sec = $campaign['billsec']%60;
			
				$asr = round($campaign['answered']/$campaign['dialed'] * 100,2);
				$acd = round($campaign['billsec']/$campaign['answered']/60,1);
				if($acd > 1){
					$acd = $acd.'&nbsp;'.$locate->Translate("min");
				}else{
					$acd = round($campaign['billsec']/$campaign['answered'],0);
					$acd = $acd.'&nbsp;'.$locate->Translate("sec");
				}

				$rsd = round(($campaign['duration_answered'] + $campaign['duration_noanswer'] - $campaign['billsec_leg_a'])/$campaign['dialed'],0);

				$abandoned = & $db->getOne("SELECT COUNT(*) FROM campaigndialedlist WHERE billsec_leg_a > 0 AND billsec = 0 AND campaignid = '$id'");

				//print_r($campaign);exit;
				//统计数据
				$html .= '<tr>
					<td colspan="2" nowrap align="left"><table border="1" width="100%" class="adminlist">
						<tr>
							<td>
								'.$locate->Translate("Total calls").':&nbsp;<b>'.$campaign['dialed'].'</b>
							</td>
							<td>
								'.$locate->Translate("Answered calls").':&nbsp;<b>'.$campaign['answered'].'</b>
							</td>							
						<tr>
						<tr>
							<td>
								'.$locate->Translate("Transfered").':&nbsp;<b>'.$campaign['transfered'].'</b>
							</td>
							<td>
								'.$locate->Translate("Transfere Rate").':&nbsp;<b>'.round(($campaign['transfered']/$campaign['answered'])*100,2).'%</b>
							</td>							
						<tr>
						<tr>							
							<td>
								'.$locate->Translate("Answered duration").':&nbsp;<b>'.$ad_hours.'&nbsp;'.$locate->Translate("hour").'&nbsp;'.$ad_min.$locate->Translate("min").'&nbsp;'.$ad_sec.'&nbsp;'.$locate->Translate("sec").'&nbsp;</b>
							</td>
							<td>								'.$locate->Translate("ASR").':&nbsp;<b>'.$asr.'%</b>
							</td>
						<tr>
						<tr>							
							<td>								'.$locate->Translate("ACD").':&nbsp;<b>'.$acd.'</b>
							</td>
							<td>								'.$locate->Translate("RSD").':&nbsp;<b>'.$rsd.'&nbsp;'.$locate->Translate("sec").'</b>
							</td>
						<tr>

						<tr>							
							<td>								'.$locate->Translate("abandoned").':&nbsp;<b>'.$abandoned.'</b>
							</td>
							<td>
							<a href="dialedlist.php?cid='.$id.'&action=abandoned">'.$locate->Translate("abandoned detail").'</a></b>
							</td>
						<tr>
					</table></td>
				</tr>';
				$html .= '<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_update(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>

			 </table>
			';

			

		$html .= '
				</form>
				'.$locate->Translate("obligatory_fields").'
				';

		return $html;
	}

	function showCampaignReport($campaignid){
		$html = '
				<!-- No edit the next line -->
				<form method="post" name="f" id="f">			
				<table border="1" width="100%" class="adminlist">
					<tr>
						<td><a href="campaignreport.php?category=call_result_analysis&campaignid='.$campaignid.'" target="_blank">Call Result Analysis</a></td>
					</tr>
					<tr>
						<td><a href="campaignreport.php?category=hit_rate_analysis&campaignid='.$campaignid.'" target="_blank">Hit Rate Analysis</a></td>
					</tr>
					<tr>
						<td><a href="campaignreport.php?category=referals_vs_contacts&campaignid='.$campaignid.'" target="_blank">referals vs contacts</a></td>
					</tr>
					<tr>
						<td><a href="campaignreport.php?category=agents&campaignid='.$campaignid.'" target="_blank">Agents</a></td>
					</tr>
				</table>
				</form>';
		return $html;
	}
}
?>
