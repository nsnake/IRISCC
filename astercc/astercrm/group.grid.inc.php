<?php
/*******************************************************************************
* account.grid.inc.php
* account操作类
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
require_once 'group.common.php';
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
		
		$sql = "SELECT * FROM astercrm_accountgroup ";

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
			$sql = "SELECT * FROM astercrm_accountgroup"
					." WHERE ".$filter." like '%".$content."%' "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}
	

	function &getRecordsFilteredMore($start, $limit, $filter, $content, $order,$ordering = ""){
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
			$sql = "SELECT * FROM astercrm_accountgroup"
					." WHERE ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}else {
			$sql = "SELECT * FROM astercrm_accountgroup";
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
		
		$sql = "SELECT COUNT(*) AS numRows FROM astercrm_accountgroup";
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;		
	}

	function &getNumRowsMore($filter = null, $content = null){
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
				$sql = 'SELECT COUNT(*) AS numRows FROM astercrm_accountgroup WHERE '.$joinstr;
			}else {
				$sql = "SELECT COUNT(*) AS numRows FROM astercrm_accountgroup";
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
			$sql = "SELECT * FROM astercrm_accountgroup"
					." WHERE ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}else {
			$sql = "SELECT * FROM astercrm_accountgroup";
		}
		
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql = 'SELECT COUNT(*) AS numRows FROM astercrm_accountgroup WHERE '.$joinstr;
			}else {
				$sql = "SELECT COUNT(*) AS numRows FROM astercrm_accountgroup";
			}
		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function insertNewGroupForBilling($group){
		global $db;
		$f = astercrm::variableFiler($group);
		$sql= "INSERT INTO accountgroup SET "
				."groupname='".$group['groupname']."', "				
				."creditlimit= ".$group['creditlimit'].", "
				."limittype= '".$group['limittype']."', "
				."resellerid= ".$group['resellerid'].", "
				."addtime = now() ";
		astercrm::events($sql);
		$res =& $db->query($sql);
		$curid = mysql_insert_id() ;
		return $curid;
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
					<td nowrap align="left">'.$locate->Translate("groupname").'*</td>
					<td align="left"><input type="text" id="groupname" name="groupname" size="25" maxlength="30"></td>
				</tr>				
				<tr>
					<td nowrap align="left">'.$locate->Translate("incontext").'</td>
					<td align="left"><input type="text" id="incontext" name="incontext" size="25" maxlength="30" value="from-internal"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("outcontext").'</td>
					<td align="left"><input type="text" id="outcontext" name="outcontext" size="25" maxlength="100" value="from-internal"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("force monitor").'</td>
					<td align="left"><input type="radio" id="monitorforce" name="monitorforce" value="0" checked>'.$locate->Translate("disable").'<input type="radio" id="monitorforce" name="monitorforce" value="1" >'.$locate->Translate("enable").'
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("first ring").'</td>
					<td align="left"><input type="radio" id="firstring" name="firstring" value="caller" checked>'.$locate->Translate("caller").'<input type="radio" id="firstring" name="firstring" value="callee" >'.$locate->Translate("callee").'
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("allowloginqueue").'</td>
					<td align="left"><input type="radio" id="allowloginqueue" name="allowloginqueue" value="yes">'.$locate->Translate("yes").'<input type="radio" id="allowloginqueue" name="allowloginqueue" value="no" checked>'.$locate->Translate("no").'
					</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("agent interval").'</td>
					<td align="left"><input type="text" id="agentinterval" name="agentinterval" size="25" maxlength="100"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("notice interval").'</td>
					<td align="left"><input type="text" id="notice_interval" name="notice_interval" size="11" maxlength="11"> ('.$locate->Translate("m").')</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("clear popup").'(s)</td>
					<td align="left"><input type="text" id="clear_popup" name="clear_popup" size="5" maxlength="5"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group Note").'</td>
					<td align="left"><textarea rows="5" cols="50" id="groupnote" name="groupnote"></textarea></td>
				</tr>';
				if($config['billing']['workwithasterbilling'] == 1)
				$html .= '<tr>
					<td nowrap align="left">'.$locate->Translate("Billing").'</td>
					<td align="left"><input type="checkbox" value="1" id="addToBilling" name="addToBilling" checked>'.$locate->Translate("Add this group to asterbilling").'</td>
				</tr>';
				$html .= '<tr>
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
		global $locate;
		$account =& Customer::getRecordByID($id,'astercrm_accountgroup');
		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("groupname").'*</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $account['id'].'"><input type="text" id="groupname" name="groupname" size="25" maxlength="30" value="'.$account['groupname'].'"></td>
				</tr>				
				<tr>
					<td nowrap align="left">'.$locate->Translate("incontext").'</td>
					<td align="left"><input type="text" id="incontext" name="incontext" size="25" maxlength="30" value="'.$account['incontext'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("outcontext").'</td>
					<td align="left"><input type="text" id="outcontext" name="outcontext" size="25" maxlength="100" value="'.$account['outcontext'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("force monitor").'</td>
					<td align="left">';
				if($account['monitorforce']){
					$html .= '<input type="radio" id="monitorforce" name="monitorforce" value="0" >'.$locate->Translate("disable").'<input type="radio" id="monitorforce" name="monitorforce" value="1" checked>'.$locate->Translate("enable");
				}else{
					$html .= '<input type="radio" id="monitorforce" name="monitorforce" value="0" checked>'.$locate->Translate("disable").'<input type="radio" id="monitorforce" name="monitorforce" value="1" >'.$locate->Translate("enable");
				}
				
		$html .='</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("first ring").'</td>
					<td align="left">';
				if($account['firstring'] == 'caller'){
					$html .= '<input type="radio" id="firstring" name="firstring" value="caller"  checked>'.$locate->Translate("caller").'<input type="radio" id="firstring" name="firstring" value="callee">'.$locate->Translate("callee");
				}else{
					$html .= '<input type="radio" id="firstring" name="firstring" value="caller">'.$locate->Translate("caller").'<input type="radio" id="firstring" name="firstring" value="callee" checked>'.$locate->Translate("callee");
				}
		$html .='</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("allowloginqueue").'</td>
					<td align="left">';
				if($account['allowloginqueue'] == 'yes'){
					$html .= '<input type="radio" id="allowloginqueue" name="allowloginqueue" value="yes"  checked>'.$locate->Translate("yes").'<input type="radio" id="allowloginqueue" name="allowloginqueue" value="no">'.$locate->Translate("no");
				}else{
					$html .= '<input type="radio" id="allowloginqueue" name="allowloginqueue" value="yes">'.$locate->Translate("yes").'<input type="radio" id="allowloginqueue" name="allowloginqueue" value="no" checked>'.$locate->Translate("no");
				}
		$html .='</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("agent interval").'</td>
					<td align="left"><input type="text" id="agentinterval" name="agentinterval" size="25" maxlength="100" value="'.$account['agentinterval'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("notice interval").'</td>
					<td align="left"><input type="text" id="notice_interval" name="notice_interval" size="11" maxlength="11" value="'.$account['notice_interval'].'"> ('.$locate->Translate("m").')</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("clear popup").'(s)</td>
					<td align="left"><input type="text" id="clear_popup" name="clear_popup" size="5" maxlength="5" value="'.$account['clear_popup'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group Note").'</td>
					<td align="left"><textarea rows="5" cols="50" id="groupnote" name="groupnote">'.$account['groupnote'].'</textarea></td>
				</tr>
				<tr>
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

	/**
	*  Imprime la forma para editar un nuevo registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser editado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene la forma con los datos 
	*									a extraidos de la base de datos para ser editados 
	*/
	
	function showAccountgroupDetail($id){
		global $locate, $db;
		$account =& Customer::getRecordByID($id,'astercrm_accountgroup');
		$contactList =& astercrm::getGroupMemberListByID($account['groupid']);
		$html = '
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left" width="45%">'.$locate->Translate("groupname").'</td>
					<td align="left" width="55%">'.$account['groupname'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("incontext").'</td>
					<td align="left">'.$account['incontext'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("outcontext").'</td>
					<td align="left">'.$account['outcontext'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("groupid").'</td>
					<td align="left">'.$account['groupid'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("force monitor").'</td>
					<td align="left">'.$account['monitorforce'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("first ring").'</td>
					<td align="left">'.$locate->Translate($account['firstring']).'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("allowloginqueue").'</td>
					<td align="left">'.$locate->Translate($account['allowloginqueue']).'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("agent interval").'</td>
					<td align="left">'.$account['agentinterval'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("clear popup").'(s)</td>
					<td align="left">'.$account['clear_popup'].'</td>
				</tr>';
				/*<tr>
					<td nowrap align="left">'.$locate->Translate("pdcontext").'</td>
					<td align="left">'.$account['pdcontext'].'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("pdextensions").'</td>
					<td align="left">'.$account['pdextension'].'</td>
				</tr>';*/
			$html .= '<tr>
					<td>
						<a href=? onclick="if (xajax.$(\'allMember\').value==\'off\'){xajax.$(\'memberList\').style.display=\'block\';xajax.$(\'allMember\').value=\'on\'}else{xajax.$(\'memberList\').style.display=\'none\';xajax.$(\'allMember\').value=\'off\'} return false;">'.$locate->Translate("display_all_member").'</a>
						<input type="hidden" id="allMember" name="allMember" value="off">
					</td>
				</tr>
				
			 </table>
			 <table border="0" id="memberList" name="memberList" style="display:none" class="memberlist">
			 <tr><td colspan="4" width="100%" height="1px" ></td></tr>
					';
				while	($contactList->fetchInto($row)){
					$html .= '<tr>';
					$html .= '
							<td align="left">
								'. $row['username'] .'
							</td>
							';

					for ($i=1;$i<4;$i++){
						if (!$contactList->fetchInto($row)){
							$html .= '<td>&nbsp;</td>';
						}else{
							$html .= '
									<td align="left">
										'. $row['username'] .'
									</td>
									';
						}
					}
					$html .= '</tr>';
				}

				$html .= '
					</table>';
			return $html;
		}

}
?>
