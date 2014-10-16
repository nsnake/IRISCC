<?php /*******************************************************************************
* customer_leads.grid.inc.php
* customer_leads操作类
* customer_leads class

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List

	getAllRecords				获取所有记录
	getRecordsFiltered			获取记录集
	getNumRows					获取记录集条数
	deleteRecord				删除customer记录, 同时删除与之相关的contact和note
	新增getRecordsFilteredMore  用于获得多条件搜索记录集
	新增getNumRowsMore          用于获得多条件搜索记录条数

* Revision 0.045  2007/10/18 14:04:00  last modified by solo
* Desc: delete function getRecordByID

* Revision 0.045  2007/10/18 13:30:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'customer_leads.common.php';
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

		$sql = "SELECT customer_leads.*,note_leads.note,note_leads.codes,note_leads.cretime AS noteCretime FROM customer_leads LEFT JOIN note_leads ON note_leads.id = customer_leads.last_note_id ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}else{
			$sql .= " WHERE customer_leads.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if($order == null){
			$sql .= " ORDER BY customer_leads.cretime DESC LIMIT $start, $limit";//.$_SESSION['ordering'];
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

		$sql = "SELECT customer_leads.*,note_leads.note,note_leads.codes,note_leads.cretime AS noteCretime FROM customer_leads LEFT JOIN note_leads ON note_leads.id = customer_leads.last_note_id WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " customer_leads.groupid = ".$_SESSION['curuser']['groupid']." ";
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
			$sql = " SELECT COUNT(*) FROM customer_leads LEFT JOIN note_leads ON note_leads.id = customer_leads.last_note_id ";
		}else{
			$sql = " SELECT COUNT(*) FROM customer_leads LEFT JOIN note_leads ON note_leads.id = customer_leads.last_note_id WHERE customer_leads.groupid = ".$_SESSION['curuser']['groupid']." ";
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

			$sql = "SELECT COUNT(*) FROM customer_leads LEFT JOIN note_leads ON note_leads.id = customer_leads.last_note_id WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " customer_leads.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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

	function getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table,$type = ''){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		$sql = "SELECT customer_leads.*,note_leads.note,note_leads.codes,note_leads.cretime AS noteCretime FROM customer_leads LEFT JOIN note_leads ON note_leads.id = customer_leads.last_note_id WHERE ";
		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " customer_leads.groupid = ".$_SESSION['curuser']['groupid']." ";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  ";
			if($type == ''){
				$sql .= " ORDER BY ".$order
				." ".$_SESSION['ordering']
				." LIMIT $start, $limit $ordering";
			}
		}
		//echo $sql;exit;
		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}
	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $db;
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

			$sql = "SELECT COUNT(*) FROM customer_leads LEFT JOIN note_leads ON note_leads.id = customer_leads.last_note_id WHERE ";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " ";
			}else{
				$sql .= " customer_leads.groupid = ".$_SESSION['curuser']['groupid']." AND ";
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
	*  Borra un registro de la tabla.
	*
	*	@param $id		(int)	customerid
	*	@return $res	(object) Devuelve el objeto con la respuesta de la sentencia SQL ejecutada del DELETE.
	*/

	function deleteRecord($id){
		global $db;

		//backup all datas

		//delete all customers
		$sql = "DELETE FROM customer_leads WHERE id = $id";
		Customer::events($sql);
		$res =& $db->query($sql);

		return $res;
	}

	/**
	*  Muestra todos los datos de un registro sobre el DIV identificado por "formDiv".
	*
	*	@param $id		(int)		Identificador del registro a ser mostrado.
	*	@return $html	(string) Devuelve una cadena de caracteres que contiene una tabla con los datos 
	*									a extraidos de la base de datos para ser mostrados 
	*/
	function showCustomerLeadRecord($id){
		global $locate;
		$customer =& astercrm::getRecordById($id,'customer_leads');
		$html = '
			<table border="0" width="100%">
			<tr>
				<td nowrap align="left" width="160">' .$locate->Translate("customer_name").'&nbsp;[<a href=? onclick="xajax_showNoteLeads(\''.$customer['id'].'\',\'customer_leads\');return false;">'.$locate->Translate("note").'</a>]</td>
				<td align="left">';
				if($_SESSION['curuser']['language'] != 'ZH' && $_SESSION['curuser']['country'] != 'cn'){
					$html .= $locate->Translate($customer['customertitle']).'&nbsp;<b>'.$customer['customer'].'</b>';
				}else{
					$html .= '&nbsp;<b>'.$customer['customer'].'</b>'.$locate->Translate($customer['customertitle']);
				}
				$html .= '&nbsp;[<a href=? onclick="xajax_CustomrLeadEdit(\''.$customer['id'].'\',\'customer\');return false;">'.$locate->Translate("edit").'</a>]&nbsp; [<a href=? onclick="
						if (xajax.$(\'hidCustomerBankDetails\').value == \'OFF\'){
							showObj(\'trCustomerBankDetails\');
							xajax.$(\'hidCustomerBankDetails\').value = \'ON\';
						}else{
							hideObj(\'trCustomerBankDetails\');
							xajax.$(\'hidCustomerBankDetails\').value = \'OFF\';
						}
						return false;">'.$locate->Translate("bank").'</a>]&nbsp;[<a href=? onclick="xajax_addNote('.$customer['id'].');return false;">'.$locate->Translate("add note").'</a>]<input type="hidden" value="OFF" name="hidCustomerBankDetails" id="hidCustomerBankDetails">
					</td>
				</tr>
				<tr id="trAddSchedulerDial" name="trAddSchedulerDial" style="display:none"></tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("city").'/'.$locate->Translate("state").'/'.$locate->Translate("country").'['.$locate->Translate("zipcode").']'.'</td>
				<td align="left">'.$customer['city'].'/'.$customer['state'].'/'.$customer['country'].'['.$customer['zipcode'].']'.'</td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("address").
					' '.
				'</td>
				<td align="left">'.$customer['address'].'</td>
			</tr>
			<!--**********************-->
			<tr>
				<td nowrap align="left">'.$locate->Translate("mobile").'</td>
				<td align="left">'.$customer['mobile'].'</td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("fax").'</td>
				<td align="left">'.$customer['fax'].'-'.$customer['fax_ext'].'</td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("email").'</td>
				<td align="left">'.$customer['email'].'</td>
			</tr>	
			<!--**********************-->
			<tr>
				<td nowrap align="left">'.$locate->Translate("website").'</td>
				<td align="left"><a href="'.$customer['website'].'" target="_blank">'.$customer['website'].'</a></td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("customer_contact").'</td>
				<td align="left">'.$customer['contact'].'&nbsp;&nbsp;('.$locate->Translate($customer['contactgender']).')</td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("customer_phone").'</td>
				<td align="left">'.$customer['phone'].'-'.$customer['phone_ext'].'</td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("category").'</td>
				<td align="left">'.$customer['category'].'</td>
			</tr>
			<tr id="trCustomerBankDetails" name="trCustomerBankDetails" style="display:none">
				<td nowrap align="left">'.$locate->Translate("bank_name").'</td>
				<td align="left">'.$customer['bankname'].'</td>
			</tr>
			<tr id="trCustomerBankDetails" name="trCustomerBankDetails" style="display:none">
				<td nowrap align="left">'.$locate->Translate("bank_zip").'</td>
				<td align="left">'.$customer['bankzip'].'</td>
			</tr>
			<tr id="trCustomerBankDetails" name="trCustomerBankDetails" style="display:none">
				<td nowrap align="left">'.$locate->Translate("bank_account_name").'</td>
				<td align="left">'.$customer['bankaccountname'].'</td>
			</tr>
			<tr id="trCustomerBankDetails" name="trCustomerBankDetails" style="display:none">
				<td nowrap align="left">'.$locate->Translate("bank_account").'</td>
				<td align="left">'.$customer['bankaccount'].'</td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("create_time").'</td>
				<td align="left">'.$customer['cretime'].'</td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("note").'</td>
				<td align="left">'.$customer['note'].'</td>
			</tr>
			<tr>
				<td nowrap align="left">'.$locate->Translate("create_by").'</td>
				<td align="left">'.$customer['creby'].'</td>
			</tr>
			
			</table>';
		return $html;
	}

	function formCustomrLeadEdit($id){
		global $locate,$db;
		$customer =& astercrm::getRecordById($id,'customer_leads');
		if ($customer['contactgender'] == 'male')
			$customerMaleSelected = 'selected';
		elseif ($customer['contactgender'] == 'female')
			$customerFemaleSelected = 'selected';
		else
			$customerUnknownSelected = 'selected';

		$html = '
				<form method="post" name="frmCustomerEdit" id="frmCustomerEdit">
				<table border="0" width="100%">
				<tr id="customerTR" name="customerTR">
					<td nowrap align="left">'.$locate->Translate("customer_name").'</td>
					<td align="left">';
					if($customer['customertitle'] == 'Mr'){
						$slt['Mr'] = 'selected';
					}elseif($customer['customertitle'] == 'Miss'){
						$slt['Miss'] = 'selected';
					}elseif($customer['customertitle'] == 'Ms'){
						$slt['Ms'] = 'selected';
					}elseif($customer['customertitle'] == 'Mrs'){
						$slt['Mrs'] = 'selected';
					}elseif($customer['customertitle'] == 'other'){
						$slt['other'] = 'selected';
					}
					$customertile = '<select id="customertitle" name="customertitle">
							<option value="Mr" '.$slt['Mr'].'>'.$locate->Translate("Mr").'</option>
							<option value="Miss" '.$slt['Miss'].'>'.$locate->Translate("Miss").'</option>
							<option value="Ms" '.$slt['Ms'].'>'.$locate->Translate("Ms").'</option>
							<option value="Mrs" '.$slt['Mrs'].'>'.$locate->Translate("Mrs").'</option>
							<option value="other" '.$slt['other'].'>'.$locate->Translate("Other").'</option>
					</select>';
					if($_SESSION['curuser']['language'] != 'ZH' && $_SESSION['curuser']['country'] != 'cn'){
						$html .= $customertile.'&nbsp;<input type="text" id="customer" name="customer" size="35" maxlength="100" value="' . $customer['customer'] . '">';
					}else{
						$html .= '<input type="text" id="customer" name="customer" size="35" maxlength="100" value="' . $customer['customer'] . '">&nbsp;'.$customertile;
					}
					$html .= '<input type="hidden" id="customerid"  name="customerid" value="'.$customer['id'].'"><BR />
					<input type="hidden" id="hidEditCustomerDetails" name="hidEditCustomerDetails" value="ON">
					<input type="hidden" id="hidEditBankDetails" name="hidEditBankDetails" value="ON">
				[<a href=? onclick="
					if (xajax.$(\'hidEditCustomerDetails\').value == \'OFF\'){
						showObj(\'trEditCustomerDetails\');
						xajax.$(\'hidEditCustomerDetails\').value = \'ON\';
					}else{
						hideObj(\'trEditCustomerDetails\');
						xajax.$(\'hidEditCustomerDetails\').value = \'OFF\';
					};
					return false;">
					'.$locate->Translate("detail").'
				</a>] &nbsp; [<a href=? onclick="
						if (xajax.$(\'hidEditBankDetails\').value == \'OFF\'){
							showObj(\'trEditBankDetails\');
							xajax.$(\'hidEditBankDetails\').value = \'ON\';
						}else{
							hideObj(\'trEditBankDetails\');
							xajax.$(\'hidEditBankDetails\').value = \'OFF\';
						}
						return false;">'.$locate->Translate("bank").'</a>]					
					</td>
				</tr>					
				<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("customer_contact").'</td>
					<td align="left"><input type="text" id="customerContact" name="customerContact" size="35" maxlength="35" value="' . $customer['contact'] . '"><BR />

					<select id="customerContactGender" name="customerContactGender">
						<option value="male" '.$customerMaleSelected.'>'.$locate->Translate("male").'</option>
						<option value="female" '.$customerFemaleSelected.'>'.$locate->Translate("female").'</option>
						<option value="unknown" '.$customerUnknownSelected.'>'.$locate->Translate("unknown").'</option>
					</select>
					
					</td>
				</tr>					
				<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("address").'</td>
					<td align="left"><input type="text" id="address" name="address" size="35" maxlength="200" value="' . $customer['address'] . '"></td>
				</tr>
				<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("zipcode").'/'.$locate->Translate("city").'</td>
					<td align="left"><input type="text" id="zipcode" name="zipcode" size="10" maxlength="10" value="' . $customer['zipcode'] . '">/<input type="text" id="city" name="city" size="17" maxlength="50" value="'.$customer['city'].'"></td>
				</tr>
				<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("state").'</td>
					<td align="left"><input type="text" id="state" name="state" size="35" maxlength="50" value="'.$customer['state'].'"></td>
				</tr>
				<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("country").'</td>
					<td align="left"><input type="text" id="country" name="country" size="35" maxlength="50" value="' . $customer['country'] . '"></td>
				</tr>
				<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("customer_phone").'</td>
					<td align="left"><input type="text" id="customerPhone" name="customerPhone" size="35" maxlength="50"  value="' . $customer['phone'] . '">-<input type="text" id="customerPhone_ext" name="customerPhone_ext" size="8" maxlength="8"  value="' . $customer['phone_ext'] . '"></td>
				</tr>
				<tr name="trEditCustomerDetails" id="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("mobile").'</td>
					<td align="left"><input type="text" id="mainMobile" name="mainMobile" size="35" value="' . $customer['mobile'] . '"></td>
				</tr>
				<tr name="trEditCustomerDetails" id="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("email").'</td>
					<td align="left"><input type="text" id="mainEmail" name="mainEmail" size="35" value="' . $customer['email'] . '"></td>
				</tr>				
				<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("website").'</td>
					<td align="left"><input type="text" id="website" name="website" size="35" maxlength="100" value="' . $customer['website'] . '"><BR /><input type="button" value="'.$locate->Translate("browser").'"  onclick="openWindow(xajax.$(\'website\').value);return false;"></td>
				</tr>
				<tr id="trEditCustomerDetails" name="trEditCustomerDetails">
					<td nowrap align="left">'.$locate->Translate("category").'</td>
					<td align="left"><input type="text" id="category" name="category" size="35"  value="' . $customer['category'] . '"></td>
				</tr>

				<tr name="trEditCustomerDetails" id="trEditCustomerDetails" >
					<td nowrap align="left">'.$locate->Translate("fax").'</td>
					<td align="left"><input type="text" id="mainFax" name="mainFax" size="35" value="' . $customer['fax'] . '"><input type="text" id="mainFax_ext" name="mainFax_ext" maxlength="8" size="8" value="' . $customer['fax_ext'] . '"></td>
				</tr>
				<!--*********************************************************-->
				<tr id="trEditBankDetails" name="trEditBankDetails">
					<td nowrap align="left">'.$locate->Translate("bank_name").'</td>
					<td align="left"><input type="text" id="bankname" name="bankname" size="35"  value="' . $customer['bankname'] . '"></td>
				</tr>
				<tr id="trEditBankDetails" name="trEditBankDetails">
					<td nowrap align="left">'.$locate->Translate("bank_zip").'</td>
					<td align="left"><input type="text" id="bankzip" name="bankzip" size="35"  value="' . $customer['bankzip'] . '"></td>
				</tr>
				<tr id="trEditBankDetails" name="trEditBankDetails">
					<td nowrap align="left">'.$locate->Translate("bank_account_name").'</td>
					<td align="left"><input type="text" id="bankaccountname" name="bankaccountname" size="35" value="' . $customer['bankaccountname'] . '"></td>
				</tr>
				<tr id="trEditBankDetails" name="trEditBankDetails">
					<td nowrap align="left">'.$locate->Translate("bank_account").'</td>
					<td align="left"><input type="text" id="bankaccount" name="bankaccount" size="35"  value="' . $customer['bankaccount'] . '"></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button  id="btnContinue" name="btnContinue"  onClick=\'xajax_updateCustomerLead(xajax.getFormValues("frmCustomerEdit"),"customer");return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>
				';
		$html .= '
				</table>
				</form>
				'.$locate->Translate("ob_fields").'
				';

		return $html;
	}

	/**
	*  update customer_leads table
	*
	*	@param $f			(array)		array contain customer fields.
	*	@return $res		(object) 		object
	*/
	
	function updateCustomerLeadRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "UPDATE customer_leads SET "
				."customer='".$f['customer']."', "
				."customertitle='".$f['customertitle']."', "
				."website='".$f['website']."', "
				."country='".$f['country']."', "
				."address='".$f['address']."', "
				."zipcode='".$f['zipcode']."', "
				."phone='".astercrm::getDigitsInStr($f['customerPhone'])."', "
				."phone_ext='".astercrm::getDigitsInStr($f['customerPhone_ext'])."', "
				."contact='".$f['customerContact']."', "
				."contactgender='".$f['customerContactGender']."', "
				."state='".$f['state']."', "
				."city='".$f['city']."', "
				."category='".$f['category']."', "
				."bankname='".$f['bankname']."', "
				."bankzip='".$f['bankzip']."', "
				."fax='".astercrm::getDigitsInStr($f['mainFax'])."', "
				."fax_ext='".astercrm::getDigitsInStr($f['mainFax_ext'])."', "
				."mobile='".astercrm::getDigitsInStr($f['mainMobile'])."', "
				."email='".$f['mainEmail']."', "
				."bankaccount='".$f['bankaccount']."', "
				."bankaccountname='".$f['bankaccountname']."' "
				//."note='".$f['note']."' "
				."WHERE id='".$f['customerid']."'";

		astercrm::events($query);
		$res =& $db->query($query);
		return $res;
	}

	function addNote($customerLid){
		global $locate,$db;
		$html .= '
				<form method="post" name="formNote" id="formNote">
				<table border="1" width="100%" class="adminlist">
					<tr>
						<td nowrap align="left">'.$locate->Translate("note").'</td>
						<td align="left">
							<textarea rows="4" cols="50" id="note" name="note" wrap="soft" style="overflow:auto"></textarea>
							<input type="hidden" value="'.$customerLid.'" name="customerid" id="customerid">
							<input type="hidden" value="0" name="contactid" id="contactid">
						</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("note_code").'</td>
						<td align="left"><select id="note_code" name="note_code">';

			$getAllNoteCodes =& astercrm::getAllNoteCodes();
			foreach($getAllNoteCodes as $tmp) {
				$html .='<option value="'.$tmp['code'].'">'.$tmp['code'].'</option>';
			}
		
			$html .='
						</select></td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("priority").'</td>
						<td align="left">
							<select id="priority" name="priority">
								<option value=0>0</option>
								<option value=1>1</option>
								<option value=2>2</option>
								<option value=3>3</option>
								<option value=4>4</option>
								<option value=5 selected>5</option>
								<option value=6>6</option>
								<option value=7>7</option>
								<option value=8>8</option>
								<option value=9>9</option>
								<option value=10>10</option>
							</select> 

							&nbsp;  <input type="radio" name="attitude"   value="10"/><img src="skin/default/images/10.gif" width="25px" height="25px" border="0" /> 
							<input type="radio" name="attitude" value="5"/><img src="skin/default/images/5.gif" width="25px" height="25px" border="0" /> 
							<input type="radio" name="attitude"  value="-1"/><img src="skin/default/images/-1.gif" width="25px" height="25px" border="0" />
							<input type="radio" name="attitude"  value="0" checked/> <img src="skin/default/images/0.gif" width="25px" height="25px" border="0" />
						</td>
					</tr>
					<tr>
						<td nowrap colspan=2 align=right><input type="button" id="btnAddNote" name="btnAddNote" value="'.$locate->Translate("continue").'" onclick="xajax_saveCustomerLeadNote(xajax.getFormValues(\'formNote\'));return false;"></td>
					</tr>
				';
			
		$html .='
				</table>
				</form>
				';
		return $html;
	}

	function saveCustomerLeadNote($f){
		global $db;
		$f = astercrm::variableFiler($f);
		$query= "INSERT INTO note_leads SET "
				."note='".$f['note']."', "
				."callerid='".$f['iptcallerid']."', "
				."attitude='".$f['attitude']."', "
				."priority=".$f['priority'].", "
				."private='".$f['private']."', "
				."cretime=now(), "
				."creby='".$_SESSION['curuser']['username']."', "
				."groupid = ".$_SESSION['curuser']['groupid'].", "
				."customerid=". $f['customerid'] . ", "
				."contactid=". $f['contactid'] .", "
				."codes='". $f['note_code']."' " ;

		astercrm::events($query);
		$res =& $db->query($query);
		if($res) {
			$noteId = mysql_insert_id();
			$sql = "UPDATE customer_leads SET last_note_id=$noteId WHERE id=".$f['customerid']." ";
			$res =& $db->query($sql);
		}
		
		return $res;
	}


	function showNoteLeads($customerid){
		global $db;
		$sql = "SELECT * FROM note_leads WHERE customerid=$customerid";
		astercrm::events($sql);
		$result = & $db->getAll($sql);
		$html = '<table border="1" width="100%" class="adminlist">';
		if(!empty($result)) {
			foreach($result as $tmp) {
				$html .= '<tr><td>'.$tmp['creby'].'</td><td>'.$tmp['note'].'</td><td>'.$tmp['codes'].'</td><td>'.$tmp['cretime'].'</td></tr>';
			}
		} else {
			$html .= '<tr><td></td><td></td><td></td><td></td></tr>';
		}
		$html .= '</table>';
		return $html;
	}

	function specialGetSql($searchContent,$searchField,$searchType=array(),$table,$fields = '',$leftjoins=array()){
		global $db;
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
				$query = 'SELECT '.$fieldstr.' FROM '.$table.' '.$leftStr.' WHERE '.$joinstr;
			}else{
				$query = 'SELECT * FROM '.$table.' '.$leftStr.' WHERE '.$joinstr;
			}
			
		}else {

			if($fieldstr != ''){
				$fieldstr=rtrim($fieldstr,',');
				$query = 'SELECT '.$fieldstr.' FROM '.$table.' '.$leftStr.' ';
			}else{
				$query = 'SELECT * FROM '.$table.'';
			}			
		}
		return $query;
	}
}
?>
