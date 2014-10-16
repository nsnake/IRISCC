<?
/*******************************************************************************
* resellergroup.grid.inc.php
* resellergroup操作类
* Customer class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	formAdd						生成添加resellergroup表单的HTML
	formEdit					生成编辑resellergroup表单的HTML
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.0456  2007/10/30 13:15:00  last modified by solo
* Desc: add channel field 

* Revision 0.045  2007/10/18 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'resellergroup.common.php';
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
		
		$sql = "SELECT * FROM resellergroup ";

//		if ($creby != null)
//			$sql .= " WHERE note.creby = '".$_SESSION['curuser']['username']."' ";
			

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
	
	function insertNewResellergroup($f){
		global $db,$config;
		$f = astercrm::variableFiler($f);
		$sql= "INSERT INTO resellergroup SET "
				."resellername='".$f['resellername']."', "
				."accountcode='".$f['accountcode']."', "
				."clid_context='".$f['clid_context']."', "
				#."allowcallback='".$f['allowcallback']."', "
				."allowcallback='no', "
				."creditlimit= ".$f['creditlimit'].", "
				."limittype= '".$f['limittype']."', "
				."trunk1_id= '".$f['trunk1_id']."', "
				."trunk2_id= '".$f['trunk2_id']."', "
				."multiple= '".$f['multiple']."', "
				."addtime = now() ";
				
		if($config['synchronize']['id_autocrement_byset']){
			$sql .= ",id='".$f['id']."' ";
		}
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}
	/**
	*  insert a record to trunks table
	*
	*	@param $f			(array)		array contain customer fields.
	*	@return $res	(object) 	
	*/
	function insertNewTrunk($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$sql= "INSERT INTO trunks SET "
				."trunkname='".$f['trunkname']."', "
				."trunkprotocol='".$f['trunkprotocol']."', "
				."registrystring = '".$f['registrystring']."', "
				."trunkdetail = '".$f['detail']."', "
				."trunktimeout = ".$f['timeout'].", "
				."trunkprefix = '".$f['trunkprefix']."',"
				."trunkidentity = ".$f['trunkidentity'].","
				."property= 'new', "
				."removeprefix = '".$f['removeprefix']."',"
				."creby='".$_SESSION['curuser']['username']."', "
				."created = now() ";
		astercrm::events($sql);
		$res =& $db->query($sql);
		$insertId =mysql_insert_id();
		return $insertId;
	}

	function updateNewTrunk($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$sql= "UPDATE trunks SET "
				."trunkname='".$f['trunkname']."', "
				."trunkprotocol='".$f['trunkprotocol']."', "
				."registrystring = '".$f['registrystring']."', "
				."trunkdetail = '".$f['detail']."', "
				."trunktimeout = ".$f['timeout'].", "
				."trunkprefix = '".$f['trunkprefix']."',"
				."trunkidentity = ".$f['trunkidentity'].","
				."property= 'new', "
				."removeprefix = '".$f['removeprefix']."' "
				."WHERE id='".$f['curtrunkid']."'";
		astercrm::events($sql);
		$res =& $db->query($sql);
		$insertId =mysql_insert_id();
		return $insertId;
	}

	function trunkAll()
	{
		global $db;
		$sql = "SELECT * FROM trunks WHERE property='new'OR property='edit'";
		astercrm::events($sql);
		$result =& $db->query($sql);
		return $result;
	}

	function Reloadfile() {
		global $db;
		$sql = "SELECT * FROM trunks";
		astercrm::events($sql);
		$result =& $db->query($sql);
		return $result;
	}
	
	function CreateFile($str,$content) {
		global $config,$db;
		$filepath = $config['system']['sipfile'];
		$fp=fopen($filepath."_".$str.".conf","w");
		$result = fwrite($fp,$content);
		fclose($fp);
		if(!empty($result)) {
			$sql = "UPDATE trunks SET property='normal' WHERE property='new' OR property='edit'";
			astercrm::events($sql);
			$res =& $db->query($sql);
		}
		return $result;
	}

	/**
	*  update resellergroup table
	*
	*	@param $f			(array)		array contain customer fields.
	*	@return $res		(object) 		object
	*/

	function updateResellergroupRecord($f){
		global $db,$config;
		$f = astercrm::variableFiler($f);
		if ( $f['creditmodtype'] == '' ){
			$newcurcredit = $f['curcredit'];
		}elseif ( $f['creditmodtype'] == 'add' && is_numeric( $f['creditmod'])){
			$newcurcredit = $f['curcredit'] + $f['creditmod'];
			$newcurcreditstr = "curcredit=curcredit + ".$f['creditmod'].", ";
			$historysql = "INSERT INTO credithistory SET "
							."modifytime= now(), "
							."resellerid='".$f['resellerid']."', "
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
							."srccredit='".$f['curcredit']."', "
							."modifystatus= 'reduce', "
							."modifyamount='".$f['creditmod']."', "
							."comment='".$f['comment']."', "
							."operator='".$_SESSION['curuser']['userid']."'";
							$historyres =& $db->query($historysql);
		}
		
		$sql= "UPDATE resellergroup SET "
				."resellername='".$f['resellername']."', "
				."accountcode='".$f['accountcode']."', "
				."clid_context='".$f['clid_context']."', "
				.$newcurcreditstr
				."creditlimit='".$f['creditlimit']."', "
				."limittype='".$f['limittype']."', "
				."multiple= '".$f['multiple']."', "
				."trunk1_id= '".$f['trunk1_id']."', "
				."trunk2_id= '".$f['trunk2_id']."', "
				#."allowcallback='".$f['allowcallback']."', "
				."allowcallback='no', "
				."addtime= now() "
				."WHERE id='".$f['resellerid']."'";

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
		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql = "SELECT * FROM resellergroup"
					." WHERE ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}else {
			$sql = "SELECT * FROM resellergroup";
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
		
		$sql = "SELECT COUNT(*) AS numRows FROM resellergroup";
		
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
			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql = 'SELECT COUNT(*) AS numRows FROM resellergroup WHERE '.$joinstr;
			}else {
				$sql = "SELECT COUNT(*) AS numRows FROM resellergroup";
			}
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql = "SELECT * FROM resellergroup"
					." WHERE ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}else {
			$sql = "SELECT * FROM resellergroup";
		}
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}
	
	function deleteTrunk($id,$table)
	{
		global $db;//& $db->query("SELECT * FROM resellergroup WHERE id=".$id)
		$result = mysql_fetch_array(mysql_query("SELECT * FROM resellergroup WHERE id=".$id));
		$query = "DELETE FROM $table WHERE id=".$result['trunk1_id'];
		astercrm::events($query);
		$res =& $db->query($query);

		$query = "DELETE FROM $table WHERE id=".$result['trunk2_id'];
		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}
	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);			

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql = 'SELECT COUNT(*) AS numRows FROM resellergroup WHERE '.$joinstr;
			}else {
				$sql = "SELECT COUNT(*) AS numRows FROM resellergroup";
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
	$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Reseller Name").'*</td>
					<td align="left"><input type="text" id="resellername" name="resellername" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Account Code").'</td>
					<td align="left"><input type="text" id="accountcode" name="accountcode" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Clid Context").'</td>
					<td align="left"><input type="text" id="clid_context" name="clid_context" size="25" maxlength="30"></td>
				</tr>
				<!--<tr>
					<td nowrap align="left">'.$locate->Translate("Callback").'</td>
					<td align="left">
					<select id="allowcallback" name="allowcallback">
						<option value="yes">'.$locate->Translate("Yes").'</option>
						<option value="no">'.$locate->Translate("No").'</option>
					</select>
					</td>
				</tr>-->
				<tr>
					<td nowrap align="left">'.$locate->Translate("Credit Limit").'</td>
					<td align="left"><input type="text" id="creditlimit" name="creditlimit" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Billsec Multiple").'</td>
					<td align="left"><input type="text" id="multiple" name="multiple" size="6" maxlength="6" value="1.0000"></td>
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
					<td nowrap align="left">'.$locate->Translate("trunk1").'</td>
					<td align="left">
					<select id="routetype1" name="routetype1"  onclick="showTrunk(\'routetype1\')">
						<option value="auto" selected>'.$locate->Translate("auto").'</option>
						<option value="default">'.$locate->Translate("default").'</option>
						<option value="customize">'.$locate->Translate("customize").'</option>
					</select>
					&nbsp;
					<select id="defaulttrunk1" name="defaulttrunk1" style="display:none" onchange="defaultTrunkChg(this);">';
					if($config['resellertrunk']['trunk1'] != ''){
						$html .= '<option value="-1">'.$config['resellertrunk']['trunk1'].'</option>';
					}
					if($config['resellertrunk']['trunk2'] != ''){
						$html .= '<option value="-2">'.$config['resellertrunk']['trunk2'].'</option>';
					}
					$html .= '</select>
					<input type="hidden" id="trunk1_id" name="trunk1_id" value="0" />
					<span id="trunkname1c" ></span><input type="hidden" value="0" id="tmptrunk1id" name="tmptrunk1id">
					</td>
				</tr>

				<tr>
					<td nowrap align="left">'.$locate->Translate("trunk2").'</td>
					<td align="left">
					<select id="routetype2" name="routetype2"  onclick="showTrunk(\'routetype2\')">
						<option value="auto" selected>'.$locate->Translate("auto").'</option>
						<option value="default">'.$locate->Translate("default").'</option>
						<option value="customize">'.$locate->Translate("customize").'</option>
					</select>
					&nbsp;
					<select id="defaulttrunk2" name="defaulttrunk2" style="display:none" onchange="defaultTrunkChg(this);">';
					if($config['resellertrunk']['trunk1'] != ''){
						$html .= '<option value="-1">'.$config['resellertrunk']['trunk1'].'</option>';
					}
					if($config['resellertrunk']['trunk2'] != ''){
						$html .= '<option value="-2">'.$config['resellertrunk']['trunk2'].'</option>';
					}
					$html .= '</select>
					<input type="hidden" id="trunk2_id" name="trunk2_id" value="0" />
					<span id="trunkname2c" ></span><input type="hidden" value="0" id="tmptrunk2id" name="tmptrunk2id">
					</td>
				</tr>
				
			 </table>
			 <table width="500px" class="adminlist" id="trunk" style="display:none;">			    
				<tr>
					<td width="175px" align="left">'.$locate->Translate("Trunk Name").'*:</td>
					<td align="left"  width="326px"><input type="text" id="trunkname" name="trunkname" size="25" maxlength="30"><input type="hidden" id="whichtrunk" name="whichtrunk">&nbsp;(<span id="whichtrunktip"></span>)</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Trunk Protocol").':</td>
					<td align="left">
						<select id="protocoltype" name="protocoltype">
							<option value="sip" selected>'.$locate->Translate("SIP").'</option>
							<option value="iax">'.$locate->Translate("IAX2").'</option>
						</select>
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Registry String").':</td>
					<td align="left"><input type="text" id="registrystring" name="registrystring" size="25" maxlength="254"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Trunk Prefix").':</td>
					<td align="left"><input type="text" id="trunkprefix" name="trunkprefix" size="25"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Remove Prefix").':</td>
					<td align="left"><input type="text" id="removeprefix" name="removeprefix" size="25"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Timeout").':</td>
					<td align="left"><input type="text" id="timeout" name="timeout" size="25" onblur="CheckNumeric(\'timeout\')"></td>
				</tr>
				<tr>					
					<td nowrap align="left">'.$locate->Translate("Detail").'*:</td>
					<td align="left"><textarea id="detail" name="detail" rows="10" cols="45">host=***provider ip address***
username=***userid***
secret=***password***
type=peer</textarea></td>
				</tr>
				<tr>
				    <td nowrap align="center" colspan="2"><div id="savetrunktip" style="display:none;">'.$locate->Translate("Save trunk success").'</div></td>
				</tr>
				<tr>
					<td nowrap align="right" colspan="2"><input type="button" value="'.$locate->Translate("save trunk").'" onclick="xajax_saveTrunk(xajax.getFormValues(\'f\'));"></td>
				</tr>
			</table>
			<table width="100%" style="border:0;">
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
		$resellergroup =& Customer::getRecordByID($id,'resellergroup');
		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			<input type="hidden" id="resellerid" name="resellerid" value='.$resellergroup['id'].'>
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Reseller Name").'*</td>
					<td align="left"><input type="text" id="resellername" name="resellername" size="25" maxlength="30" value="'.$resellergroup['resellername'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Account Code").'</td>
					<td align="left"><input type="text" id="accountcode" name="accountcode" size="25" maxlength="30" value="'.$resellergroup['accountcode'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Clid Context").'</td>
					<td align="left"><input type="text" id="clid_context" name="clid_context" size="25" maxlength="30" value="'.$resellergroup['clid_context'].'"></td>
				</tr>
				<!--<tr>
					<td nowrap align="left">'.$locate->Translate("Allow Callback").'</td>
					<td align="left">
					<select id="allowcallback" name="allowcallback">';

					if ($resellergroup['allowcallback'] == "yes"){
						$html .= '<option value="yes" selected>'.$locate->Translate("Yes").'</option>';
						$html .= '<option value="no">'.$locate->Translate("No").'</option>';
					}else{
						$html .= '<option value="yes">'.$locate->Translate("Yes").'</option>';
						$html .= '<option value="no" selected>'.$locate->Translate("No").'</option>';
					}

					$html .='
					</select>
					</td>
				</tr>-->
				<tr>
					<td nowrap align="left">'.$locate->Translate("Credit Limit").'</td>
					<td align="left"><input type="text" id="creditlimit" name="creditlimit" size="25" maxlength="30" value="'.$resellergroup['creditlimit'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Cur Credit").'</td>
					<td align="left">
						<input type="text" id="curcreditshow" name="curcreditshow" size="15" maxlength="100" value="'.$resellergroup['curcredit'].'" readonly>

						<input type="hidden" id="curcredit" name="curcredit"  value="'.$resellergroup['curcredit'].'">

					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Operate").'</td>
					<td align="left">
						<select id="creditmodtype" name="creditmodtype" onchange="showComment(this)">
							<option value="">'.$locate->Translate("No change").'</option>
							<option value="add">'.$locate->Translate("Refund").'</option>
							<option value="reduce">'.$locate->Translate("Charge").'</option>
						</select>
						<input type="text" id="creditmod" name="creditmod" size="15" maxlength="100" value="" disabled><p>'.$locate->Translate("Comment").' :&nbsp;<input type="text" id="comment" name="comment" size="18" maxlength="20" value="" disabled></p>
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Limit Type").'</td>
					<td align="left">
					<select id="limittype" name="limittype">';
				if ($resellergroup['limittype'] == "postpaid"){
					$html .='
						<option value="">'.$locate->Translate("No limit").'</option>
						<option value="prepaid">'.$locate->Translate("Prepaid").'</option>
						<option value="postpaid" selected>'.$locate->Translate("Postpaid").'</option>';
				}elseif( $resellergroup['limittype'] == "prepaid" ){
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
/*
			$currenttime = date("Y-m-d H:i:s");
			$currentcredit = astercc::readAmount($resellergroup['id'],null,$resellergroup['billingtime'],$currenttime,'resellercredit');
				$html .='
				<tr>
					<td nowrap align="left">'.$locate->Translate("Billing time")."(".$locate->Translate("for postpaid").')</td>
					<td align="left">'.$resellergroup['billingtime'].'<BR><INPUT TYPE="BUTTON" VALUE="'.$locate->Translate("Reset").'" onClick="setBillingtime(\''.$resellergroup['id'].'\',\''.$currenttime.'\');">'."(".$currentcredit.$locate->Translate(" By ").$currenttime.")".'</td>
				</tr>';
*/
				$default1style = 'style="display:none"';
				$default2style = 'style="display:none"';
				$tmptrunk1id = 0;
				if($resellergroup['trunk1_id'] == 0) {
					$autos1 = 'selected';
				}elseif($resellergroup['trunk1_id'] < 0){
					$default1 = 'selected';
					$default1style = '';
					if($resellergroup['trunk1_id'] == -1){
						$default1v1 = 'selected';
					}else{
						$default1v2 = 'selected';
					}
				}else{
					$customize1 = 'selected';
					$trunk1 =& Customer::getRecordByID($resellergroup['trunk1_id'],'trunks');
					if($trunk1['id'] > 0){
						$trunkname1c = '<a href="javascript:void(null)" onclick="javascript:xajax_trunkdetail(xajax.$(\'tmptrunk1id\').value,1);">'.$trunk1['trunkname'].'</a>&nbsp;<a href="javascript:void(null)" onclick="javascript:deltrunk(\'1\');">'.$locate->Translate("del").'</a>';
						$tmptrunk1id = $trunk1['id'];
					}
				}

				if($resellergroup['trunk2_id'] == 0) {
					$autos2 = 'selected';
				}elseif($resellergroup['trunk2_id'] < 0){
					$default2 = 'selected';
					$default2style = '';
					if($resellergroup['trunk2_id'] == -1){
						$default2v1 = 'selected';
					}else{
						$default2v2 = 'selected';
					}
				}else{
					$customize2 = 'selected';
					$trunk2 =& Customer::getRecordByID($resellergroup['trunk2_id'],'trunks');
					if($trunk2['id'] > 0){
						$trunkname2c = '<a href="javascript:void(null)" onclick="javascript:xajax_trunkdetail(xajax.$(\'tmptrunk2id\').value,2);">'.$trunk2['trunkname'].'</a>&nbsp;<a href="javascript:void(null)" onclick="javascript:deltrunk(\'2\');">'.$locate->Translate("del").'</a>';
						$tmptrunk2id = $trunk2['id'];
					}
				}

				$html .='
				<tr>
					<td nowrap align="left">'.$locate->Translate("Billsec Multiple").'</td>
					<td align="left"><input type="text" id="multiple" name="multiple" size="6" maxlength="6" value="'.$resellergroup['multiple'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("trunk1").'</td>
					<td align="left">
					<select id="routetype1" name="routetype1"  onclick="showTrunk(\'routetype1\')">
						<option value="auto" '.$autos1.'>'.$locate->Translate("auto").'</option>
						<option value="default" '.$default1.'>'.$locate->Translate("default").'</option>
						<option value="customize" '.$customize1.'>'.$locate->Translate("customize").'</option>
					</select>
					&nbsp;
					<select id="defaulttrunk1" name="defaulttrunk1"  onchange="defaultTrunkChg(this);" '.$default1style.'>
						<option value="-1" '.$default1v1.'>'.$config['resellertrunk']['trunk1'].'</option>
						<option value="-2" '.$default1v2.'>'.$config['resellertrunk']['trunk2'].'</option>
					</select>
					<input type="hidden" id="trunk1_id" name="trunk1_id" value="'.$resellergroup['trunk1_id'].'" />
					<span id="trunkname1c" >'.$trunkname1c.'</span><input type="hidden" value="'.$tmptrunk1id.'" id="tmptrunk1id" name="tmptrunk1id">
					</td>
				</tr>';

				$html .='				
				<tr>
					<td nowrap align="left">'.$locate->Translate("trunk2").'</td>
					<td align="left">
					<select id="routetype2" name="routetype2"  onclick="showTrunk(\'routetype2\')">
						<option value="auto" '.$autos2.'>'.$locate->Translate("auto").'</option>
						<option value="default" '.$default2.'>'.$locate->Translate("default").'</option>
						<option value="customize" '.$customize2.'>'.$locate->Translate("customize").'</option>
					</select>
					&nbsp;
					<select id="defaulttrunk2" name="defaulttrunk2"  onchange="defaultTrunkChg(this);" '.$default2style.'>
						<option value="-1" '.$default2v1.'>'.$config['resellertrunk']['trunk1'].'</option>
						<option value="-2" '.$default2v2.'>'.$config['resellertrunk']['trunk2'].'</option>
					</select>
					<input type="hidden" id="trunk2_id" name="trunk2_id" value="'.$resellergroup['trunk2_id'].'" />
					<span id="trunkname2c" >'.$trunkname2c.'</span><input type="hidden" value="'.$tmptrunk2id.'" id="tmptrunk2id" name="tmptrunk2id">
					</td>
				</tr>';
					
					$html .= '</table>';
			
//			if(!empty($TrunkArray)) {
//				$html .= '<table width="500px" class="adminlist" id="trunk"><tr>
//					<td width="175px" align="left">'.$locate->Translate("Trunk Name").'*:</td>
//					<td align="left"  width="326px"><input type="hidden" id="trunkid" name="trunkid" value="'.$TrunkArray['id'].'"/><input type="text" id="trunkname" name="trunkname" size="25" maxlength="30" value="'.$TrunkArray['trunkname'].'"></td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Trunk Protocol").':</td>
//					<td align="left">
//						<select id="protocoltype" name="protocoltype">';
//						if($TrunkArray['trunkprotocol'] == 'sip') {
//							$html .= '<option value="sip" selected>'.$locate->Translate("SIP").'</option>
//							<option value="iax">'.$locate->Translate("IAX2").'</option>';
//						} else {
//							$html .= '<option value="sip">'.$locate->Translate("SIP").'</option>
//							<option value="iax" selected>'.$locate->Translate("IAX2").'</option>';
//						} 
//						$html .='
//						</select>
//					</td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Registry String").':</td>
//					<td align="left"><input type="text" id="registrystring" name="registrystring" size="25" maxlength="254" value="'.$TrunkArray['registrystring'].'"></td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Trunk Prefix").':</td>
//					<td align="left"><input type="text" id="trunkprefix" name="trunkprefix" size="25" value="'.$TrunkArray['trunkprefix'].'"></td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Remove Prefix").':</td>
//					<td align="left"><input type="text" id="removeprefix" name="removeprefix" size="25" value="'.$TrunkArray['removeprefix'].'"></td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Timeout").':</td>
//					<td align="left"><input type="text" id="timeout" name="timeout" size="25" value="'.$TrunkArray['trunkusage'].'" onblur="CheckNumeric(\'timeout\')"></td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Detail").'*:</td>
//					<td align="left"><textarea id="detail" name="detail" rows="10" cols="45" > '.$TrunkArray['trunkdetail'].'</textarea></td>
//				</tr>';
//			} else {
//				$html .='<table width="500px" class="adminlist" id="trunk" style="display:none;"><tr>
//					<td width="175px" align="left">'.$locate->Translate("Trunk Name").'*:</td>
//					<td align="left"  width="326px"><input type="hidden" id="trunkid" name="trunkid" value=""/><input type="text" id="trunkname" name="trunkname" size="25" maxlength="30"></td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Trunk Protocol").':</td>
//					<td align="left">
//						<select id="protocoltype" name="protocoltype">
//							<option value="sip" selected>'.$locate->Translate("SIP").'</option>
//							<option value="iax">'.$locate->Translate("IAX2").'</option>
//						</select>
//					</td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Registry String").':</td>
//					<td align="left"><input type="text" id="registrystring" name="registrystring" size="25" maxlength="254"></td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Trunk Prefix").':</td>
//					<td align="left"><input type="text" id="trunkprefix" name="trunkprefix" size="25"></td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Remove Prefix").':</td>
//					<td align="left"><input type="text" id="removeprefix" name="removeprefix" size="25"></td>
//				</tr>
//				<tr>
//					<td nowrap align="left">'.$locate->Translate("Timeout").':</td>
//					<td align="left"><input type="text" id="timeout" name="timeout" size="25" onblur="CheckNumeric(\'timeout\')"></td>
//				</tr>
//				<tr>					
//					<td nowrap align="left">'.$locate->Translate("Detail").'*:</td>
//					<td align="left"><textarea id="detail" name="detail" rows="10" cols="45">host=***provider ip address***
//username=***userid***
//secret=***password***
//type=peer</textarea></td>
//				</tr>';
//			}
			$html .='
			<table width="500px" class="adminlist" id="trunk" style="display:none;">			    
				<tr>
					<td width="175px" align="left">'.$locate->Translate("Trunk Name").'*:</td>
					<td align="left"  width="326px"><input type="text" id="trunkname" name="trunkname" size="25" maxlength="30"><input type="hidden" id="whichtrunk" name="whichtrunk">&nbsp;(<span id="whichtrunktip"></span>)</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Trunk Protocol").':</td>
					<td align="left">
						<select id="protocoltype" name="protocoltype">
							<option value="sip" selected>'.$locate->Translate("SIP").'</option>
							<option value="iax">'.$locate->Translate("IAX2").'</option>
						</select>
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Registry String").':</td>
					<td align="left"><input type="text" id="registrystring" name="registrystring" size="25" maxlength="254"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Trunk Prefix").':</td>
					<td align="left"><input type="text" id="trunkprefix" name="trunkprefix" size="25"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Remove Prefix").':</td>
					<td align="left"><input type="text" id="removeprefix" name="removeprefix" size="25"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Timeout").':</td>
					<td align="left"><input type="text" id="timeout" name="timeout" size="25" onblur="CheckNumeric(\'timeout\')"></td>
				</tr>
				<tr>					
					<td nowrap align="left">'.$locate->Translate("Detail").'*:</td>
					<td align="left"><textarea id="detail" name="detail" rows="10" cols="45">host=***provider ip address***
username=***userid***
secret=***password***
type=peer</textarea></td>
				</tr>
				<tr>
				    <td nowrap align="center" colspan="2"><div id="savetrunktip" style="display:none;">'.$locate->Translate("Save trunk success").'</div></td>
				</tr>
				<tr>
					<td nowrap align="right" colspan="2"><input type="button" value="'.$locate->Translate("save trunk").'" onclick="xajax_saveTrunk(xajax.getFormValues(\'f\'));"></td>
				</tr>
			</table>			
			<table class="adminlist" width="100%">
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_update(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>

			 </table>
			';			

		$html .= '
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
	
	function showGroupDetail($id){
		global $locate;
		$resellergroup =& Customer::getRecordByID($id,'resellergroup');
		$html = '
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("username").'</td>
					<td align="left">'.$resellergroup['username'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("password").'</td>
					<td align="left">'.$resellergroup['password'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("usertype").'</td>
					<td align="left">'.$resellergroup['usertype'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("extensions").'</td>
					<td align="left">'.$resellergroup['extensions'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("group_code").'</td>
					<td align="left">'.$resellergroup['groupcode'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Callback").'</td>
					<td align="left">'.$resellergroup['callback'].'</td>
				</tr>
			 </table>
			';

		return $html;
	}

	/**
	* generate a unique pin number, can be assign length by $len 
	*
	*	@param $len		(int)		pin length
	*	@return $pin	(string)	pin number
	*/

	function generateUniquePin($len=10) {
		global $db;
		srand((double)microtime()*1000003);
		$prefix = rand(1000000000,9999999999);
		$sqlStr = "SELECT * FROM trunks WHERE trunkidentity='";
		if(is_numeric($len) && $len = 10){
			$pin = $prefix;
			$curpin =mysql_fetch_array(mysql_query($sqlStr.$pin."'"));
			while(!empty($curpin)){
				$pin = $prefix.rand($min,$max);
				$curpin =mysql_fetch_array(mysql_query($sqlStr.$pin."'"));
			}
		}
		return $pin;
	}

}
?>
