<?
/*******************************************************************************
* accountgroup.grid.inc.php
* accountgroup操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加accountgroup表单的HTML
	formEdit					生成编辑accountgroup表单的HTML
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.0456  2007/10/30 13:15:00  last modified by solo
* Desc: add channel field 

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'accountgroup.common.php';
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
		
		$sql = "SELECT accountgroup.*, resellername FROM accountgroup LEFT JOIN resellergroup ON resellergroup.id = accountgroup.resellerid ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " WHERE accountgroup.resellerid = ".$_SESSION['curuser']['resellerid']." ";
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
	*  insert a record to accountgroup table
	*
	*	@param $f			(array)		array contain customer fields.
	*	@return $res	(object) 	
	*/
	
	function insertNewAccountgroup($f){
		global $db,$config;
		$f = astercrm::variableFiler($f);
		$sql= "INSERT INTO accountgroup SET "
				."groupname='".$f['groupname']."', "
				."grouptitle='".$f['grouptitle']."', "
				."grouptagline='".$f['grouptagline']."', "
				."accountcode='".$f['accountcode']."', "
				#."allowcallback='".$f['allowcallback']."', "
				."allowcallback='no', "
				."creditlimit= ".$f['creditlimit'].", "
				."limittype= '".$f['limittype']."', "
				."group_multiple= '".$f['group_multiple']."', "
				."customer_multiple= '".$f['customer_multiple']."', "
				."resellerid= ".$f['resellerid'].", "
				."addtime = now() ";
				
		if($config['synchronize']['id_autocrement_byset']){
			$sql .= ",id='".$f['id']."' ";
		}
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}


	/**
	*  update accountgroup table
	*
	*	@param $f			(array)		array contain customer fields.
	*	@return $res		(object) 		object
	*/
	
	function updateAccountgroupRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		if ( $f['creditmodtype'] == '' ){
			$newcurcredit = $f['curcredit'];
		}elseif ( $f['creditmodtype'] == 'add' && is_numeric( $f['creditmod']) ){
			$newcurcredit = $f['curcredit'] + $f['creditmod'];
			$newcurcreditstr = "curcredit=curcredit + ".$f['creditmod'].", ";
			$historysql = "INSERT INTO credithistory SET "
							."modifytime= now(), "
							."resellerid='".$f['resellerid']."', "
							."groupid='".$f['groupid']."', "
							."srccredit='".$f['curcredit']."', "
							."modifystatus= 'add', "
							."modifyamount='".$f['creditmod']."', "
							."comment='".$f['comment']."', "
							."operator='".$_SESSION['curuser']['userid']."'";
			$historyres =& $db->query($historysql);
		}elseif ( $f['creditmodtype'] == 'reduce' && is_numeric( $f['creditmod'])){
			$newcurcredit = $f['curcredit'] - $f['creditmod'];
			$newcurcreditstr = "curcredit=curcredit - ".$f['creditmod'].", ";
			$historysql = "INSERT INTO credithistory SET "
							."modifytime= now(), "
							."resellerid='".$f['resellerid']."', "
							."groupid='".$f['groupid']."', "
							."srccredit='".$f['curcredit']."', "
							."modifystatus= 'reduce', "
							."modifyamount='".$f['creditmod']."', "
							."comment='".$f['comment']."', "
							."operator='".$_SESSION['curuser']['userid']."'";
			$historyres =& $db->query($historysql);
		}
		$sql= "UPDATE accountgroup SET "
				."groupname='".$f['groupname']."', "
				."grouptitle='".$f['grouptitle']."', "
				."grouptagline='".$f['grouptagline']."', "
				."grouplogostatus='".$f['grouplogostatus']."', "
				."resellerid='".$f['resellerid']."', "
				.$newcurcreditstr
				."creditlimit='".$f['creditlimit']."', "
				."limittype='".$f['limittype']."', "
				."group_multiple= '".$f['group_multiple']."', "
				."customer_multiple= '".$f['customer_multiple']."', "
				#."allowcallback='".$f['allowcallback']."', "
				."allowcallback='no', "
				."addtime= now(), "
				."accountcode='".$f['accountcode']."' "
				."WHERE id='".$f['groupid']."'";

		astercrm::events($sql);
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
		$sql = "SELECT accountgroup.*, resellername FROM accountgroup LEFT JOIN resellergroup ON resellergroup.id = accountgroup.resellerid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " accountgroup.resellerid = ".$_SESSION['curuser']['resellerid']." ";
		}

		if ($joinstr !='' ){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
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
	
	function &getNumRows($filter = null, $content = null){
		global $db;
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = "SELECT COUNT(*) AS numRows FROM accountgroup LEFT JOIN resellergroup ON resellergroup.id = accountgroup.resellerid";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql = "SELECT COUNT(*) AS numRows FROM accountgroup LEFT JOIN resellergroup ON resellergroup.id = accountgroup.resellerid WHERE accountgroup.resellerid = ".$_SESSION['curuser']['resellerid']." ";
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

			$sql = 'SELECT COUNT(*) AS numRows FROM accountgroup LEFT JOIN resellergroup ON resellergroup.id = accountgroup.resellerid WHERE ';

			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
				$sql .= " accountgroup.resellerid = ".$_SESSION['curuser']['resellerid']." AND ";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " ".$joinstr;
			}else {
				$sql = " 1 ";
			}


		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		$sql = "SELECT accountgroup.*, resellername FROM accountgroup LEFT JOIN resellergroup ON resellergroup.id = accountgroup.resellerid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " accountgroup.resellerid = ".$_SESSION['curuser']['resellerid']." ";
		}

		if ($joinstr !='' ){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
		}
		$sql .= " ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";

		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

			$sql = 'SELECT COUNT(*) AS numRows FROM accountgroup LEFT JOIN resellergroup ON resellergroup.id = accountgroup.resellerid WHERE ';

			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
				$sql .= " accountgroup.resellerid = ".$_SESSION['curuser']['resellerid']." AND ";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " ".$joinstr;
			}else {
				$sql = " 1 ";
			}


		Customer::events($sql);
		$res =& $db->getOne($sql);
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
		global $locate,$config;

		$reselleroptions = '';
		$reseller = astercrm::getAll('resellergroup');

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$reselleroptions .= '<select id="resellerid" name="resellerid">';
			$reselleroptions .= '<option value="0"></option>';
			while	($reseller->fetchInto($row)){
				if($config['synchronize']['display_synchron_server']){
					$row['resellername'] = astercrm::getSynchronDisplay($row['id'],$row['resellername']);
				}

				$reselleroptions .= "<OPTION value='".$row['id']."'>".$row['resellername']."</OPTION>";
			}
			$reselleroptions .= '</select>';


		}else{
			while	($reseller->fetchInto($row)){
				if ($row['id'] == $_SESSION['curuser']['resellerid']){
					if($config['synchronize']['display_synchron_server']){
						$row['resellername'] = astercrm::getSynchronDisplay($row['id'],$row['resellername']);
					}

					$reselleroptions .= $row['resellername'].'<input type="hidden" value="'.$row['id'].'" name="resellerid" id="resellerid">';
					break;
				}
			}


		}

	$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Reseller").'</td>
					<td align="left">'
						.$reselleroptions.
					'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group Name").'*</td>
					<td align="left"><input type="text" id="groupname" name="groupname" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Title").'</td>
					<td align="left"><input type="text" id="grouptitle" name="grouptitle" size="25" maxlength="50"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Tagline").'</td>
					<td align="left"><input type="text" id="grouptagline" name="grouptagline" size="25" maxlength="80"></td>
				</tr>				
				<tr>
					<td nowrap align="left">'.$locate->Translate("Account Code").'</td>
					<td align="left"><input type="text" id="accountcode" name="accountcode" size="25" maxlength="30">'."(".$locate->Translate("might be useful for callback").")".'</td>
				</tr>
				<!--<tr>
					<td nowrap align="left">'.$locate->Translate("Allow Callback").'</td>
					<td align="left">
					<select id="allowcallback" name="allowcallback">';
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['allowcallback'] == 'yes'){
			$html .=
						'
						<option value="yes">'.$locate->Translate("Yes").'</option>
						<option value="no">'.$locate->Translate("No").'</option>
						';
		}else{
			$html .=
						'
						<option value="no">'.$locate->Translate("No").'</option>
						';
		}
		$html .=
					'</select>
					</td>
				</tr>-->
				<tr>
					<td nowrap align="left">'.$locate->Translate("Credit Limit").'</td>
					<td align="left"><input type="text" id="creditlimit" name="creditlimit" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Limit Type").'</td>
					<td align="left">
					<select id="limittype" name="limittype">
						<option value="" selected>'.$locate->Translate("No limit").'</option>
						<option value="prepaid">'.$locate->Translate("Prepaid").'</option>
						<option value="postpaid">'.$locate->Translate("Postpaid").'</option>
					</select>
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group Billsec Multiple").'</td>
					<td align="left">
						<input type="text" id="group_multiple" name="group_multiple" size="6" maxlength="6" value="1.0000">
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Customer Billsec Multiple").'</td>
					<td align="left">
						<input type="text" id="customer_multiple" name="customer_multiple" size="6" maxlength="6" value="1.0000">
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_save(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>

			 </table>
			';

		$html .='
			</form>
			*'.$locate->Translate("Obligatory Fields").'
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
		global $locate,$config;
		$group =& Customer::getRecordByID($id,'accountgroup');

		$reselleroptions = '';
		$reseller = astercrm::getAll('resellergroup');

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$reselleroptions .= '<select id="resellerid" name="resellerid">';
			$reselleroptions .= '<option value="0"></option>';
			while	($reseller->fetchInto($row)){
				if($config['synchronize']['display_synchron_server']){
					$row['resellername'] = astercrm::getSynchronDisplay($row['id'],$row['resellername']);
				}

				if ($row['id'] == $group['resellerid']){
					$reselleroptions .= "<OPTION value='".$row['id']."' selected>".$row['resellername']."</OPTION>";
				}else{
					$reselleroptions .= "<OPTION value='".$row['id']."'>".$row['resellername']."</OPTION>";
				}
			}
			$reselleroptions .= '</select>';

			//the admin has the reset group button
			$resetBtnStr = '<input type="button" onclick="if(confirm(&quot;'.$locate->Translate("Make Sure To Reset The Relate Data By Group").'?&quot;)) xajax_resetGroup(&quot;'.$group['id'].'&quot;);return false;" value="'.$locate->Translate("Reset Group").'">';
		}else{
			while	($reseller->fetchInto($row)){
				if ($row['id'] == $_SESSION['curuser']['resellerid']){
					if($config['synchronize']['display_synchron_server']){
						$row['resellername'] = astercrm::getSynchronDisplay($row['id'],$row['resellername']);
					}

					$reselleroptions .= $row['resellername'].'<input type="hidden" value="'.$row['id'].'" name="resellerid" id="resellerid">';
					break;
				}
			}

			//except the admin,other don't have the reset group button
			$resetBtnStr = '';
		}
		

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			<input type="hidden" id="groupid" name="groupid" value='.$group['id'].'>
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Reseller").'*</td>
					<td align="left">'
						.$reselleroptions.
					'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group Name").'*</td>
					<td align="left"><input type="text" id="groupname" name="groupname" size="25" maxlength="30" value="'.$group['groupname'].'">&nbsp;'.$resetBtnStr.'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Title").'</td>
					<td align="left"><input type="text" id="grouptitle" name="grouptitle" size="25" maxlength="50" value="'.$group['grouptitle'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Tagline").'</td>
					<td align="left"><input type="text" id="grouptagline" name="grouptagline" size="25" maxlength="80" value="'.$group['grouptagline'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Account Code").'</td>
					<td align="left"><input type="text" id="accountcode" name="accountcode" size="25" maxlength="30" value="'.$group['accountcode'].'">'."(".$locate->Translate("might be useful for callback").")".'</td>
				</tr>
				<!--<tr>
					<td nowrap align="left">'.$locate->Translate("Allow Callback").'</td>
					<td align="left">
					<select id="allowcallback" name="allowcallback">';
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['allowcallback'] == 'yes'){
					if ($group['allowcallback'] == "yes"){
						$html .= '<option value="yes" selected>'.$locate->Translate("Yes").'</option>';
						$html .= '<option value="no">'.$locate->Translate("No").'</option>';
					}else{
						$html .= '<option value="yes">'.$locate->Translate("Yes").'</option>';
						$html .= '<option value="no" selected>'.$locate->Translate("No").'</option>';
					}
		}else{
			$html .= '<option value="no">'.$locate->Translate("No").'</option>';
		}

					$html .='
					</select>
					</td>
				</tr>-->
				<tr>
					<td nowrap align="left">'.$locate->Translate("Credit Limit").'*</td>
					<td align="left"><input type="text" id="creditlimit" name="creditlimit" size="25" maxlength="30" value="'.$group['creditlimit'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Cur Credit").'</td>
					<td align="left">
					<input type="text" id="curcreditshow" name="curcreditshow" size="25" maxlength="100" value="'.$group['curcredit'].'" readonly>
					<input type="hidden" id="curcredit" name="curcredit" value="'.$group['curcredit'].'">
				</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Operate").'</td>
					<td align="left" id="tdOperate">
						<select id="creditmodtype" name="creditmodtype" onchange="showComment(this)">
							<option value="">'.$locate->Translate("No change").'</option>
							<option value="add">'.$locate->Translate("Refund").'</option>
							<option value="reduce">'.$locate->Translate("Charge").'</option>
						</select>
						<input type="text" id="creditmod" name="creditmod" size="15" maxlength="100" value="" disabled><p>
						'.$locate->Translate("Comment").' :&nbsp;<input type="text" id="comment" name="comment" size="18" maxlength="20" value="" disabled></p>
					</td>
				</tr>

				<tr>
					<td nowrap align="left">'.$locate->Translate("Limit Type").'</td>
					<td align="left">
					<select id="limittype" name="limittype">';
				if ($group['limittype'] == "postpaid"){
					$html .='
						<option value="">'.$locate->Translate("No limit").'</option>
						<option value="prepaid">'.$locate->Translate("Prepaid").'</option>
						<option value="postpaid" selected>'.$locate->Translate("Postpaid").'</option>';
				}elseif( $group['limittype'] == "prepaid" ){
					$html .='
						<option value="">'.$locate->Translate("No limit").'</option>
						<option value="prepaid" selected>'.$locate->Translate("Prepaid").'</option>
						<option value="postpaid">'.$locate->Translate("Postpaid").'</option>';
				}else{
					$html .='
						<option value="" selected>'.$locate->Translate("No limit").'</option>
						<option value="prepaid">'.$locate->Translate("Prepaid").'</option>
						<option value="postpaid">'.$locate->Translate("Postpaid").'</option>';
				}

				$html .=
					'</select>
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group Billsec Multiple").'</td>
					<td align="left">
						<input type="text" id="group_multiple" name="group_multiple" size="6" maxlength="6" value="'.$group['group_multiple'].'">
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Customer Billsec Multiple").'</td>
					<td align="left">
						<input type="text" id="customer_multiple" name="customer_multiple" size="6" maxlength="6" value="'.$group['customer_multiple'].'">
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Display Logo").'</td>
					<td align="left"><input type="radio" id="grouplogostatus" name="grouplogostatus" value="1" ';
				if($group['grouplogostatus'] == 1) $html .= 'checked';
				$html .=
				'>enable&nbsp;&nbsp;<input type="radio" id="grouplogostatus" name="grouplogostatus" value="0"';
				if($group['grouplogostatus'] == 0) $html .= 'checked';
				$html .= '>disable</td>
				</tr>
				</table>
				</form>';

				$html .= 
				'<form action="upload.php" method="post" enctype="multipart/form-data" name="formLogoUpload" id="formLogoUpload" target="iframeForUpload">
				<table border="1" width="100%" class="adminlist">
					<tr>
					<td nowrap align="left">'.$locate->Translate("Upload New Logo").'</td>
					<td><input type="file" name="callshopLogo" id="callshopLogo" size="35"/><input type="hidden" id="LogoUploadFlag" name="LogoUploadFlag" value="1">
					<input type="hidden" id="group_id" name="group_id" value='.$group['id'].'></td>
					<tr>
					<tr>
						<td  colspan="2" align="center"><button id="submitButton" onClick=\'xajax_update(xajax.getFormValues("f")); document.getElementById("formLogoUpload").submit();return false;\'>'.$locate->Translate("continue").'</button></td>
					</tr>
				</table>
				</form>
			';

			

		$html .= '				
				*'.$locate->Translate("Obligatory Fields").'
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
	
	function showGroupDetail($id){
		global $locate;
		$group =& Customer::getRecordByID($id,'accountgroup');
		$html = '
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("username").'</td>
					<td align="left">'.$group['username'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("password").'</td>
					<td align="left">'.$group['password'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("usertype").'</td>
					<td align="left">'.$group['usertype'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("extensions").'</td>
					<td align="left">'.$group['extensions'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("group_code").'</td>
					<td align="left">'.$group['groupcode'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Callback").'</td>
					<td align="left">'.$group['callback'].'</td>
				</tr>
			 </table>
			';

		return $html;
	}

	function resetGroup($groupId){
		global $db;

		//delete all mycdr data by this group
		$query_mycdr = "DELETE FROM mycdr WHERE groupid = '$groupId'";
		astercrm::events($query_mycdr);
		$res =& $db->query($query_mycdr);

		//delete all historycdr data by this group
		$query_historycdr = "DELETE FROM historycdr WHERE groupid = '$groupId'";
		astercrm::events($query_historycdr);
		$res =& $db->query($query_historycdr);

		//delete all credithistory data by this group
		$query_credithistory = "DELETE FROM credithistory WHERE groupid = '$groupId'";
		astercrm::events($query_credithistory);
		$res =& $db->query($query_credithistory);

		//reset clid field
		$reset_clid = "UPDATE clid SET creditlimit='0.0000',curcredit='0.0000',credit_clid='0.0000',credit_group='0.0000',credit_reseller='0.0000' WHERE groupid = '$groupId'";
		astercrm::events($reset_clid);
		$res =& $db->query($reset_clid);

		//reset accountgroup field
		$reset_accountgroup = "UPDATE accountgroup SET creditlimit='0.0000',curcredit='0.0000',credit_clid='0.0000',credit_group='0.0000',credit_reseller='0.0000' WHERE id = '$groupId'";
		astercrm::events($reset_accountgroup);
		$res =& $db->query($reset_accountgroup);

		return $res;
	}
	
}
?>
