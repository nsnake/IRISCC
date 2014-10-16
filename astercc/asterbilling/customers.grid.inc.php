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

require_once 'customers.common.php';
require_once 'include/astercrm.class.php';

//ini_set('include_path',dirname($_SERVER["SCRIPT_FILENAME"])."/include");

// define database connection string
define('SQLCC', $config['customers']['dbtype']."://".$config['customers']['username'].":".$config['customers']['password']."@tcp+".$config['customers']['dbhost'].":".$config['customers']['dbport']."/".$config['customers']['dbname']."");

// set a global variable to save customers database connection
$GLOBALS['customers_db'] = DB::connect(SQLCC);
//print_R($GLOBALS['customers_db']);exit;
// need to check if db connected
if (DB::iserror($GLOBALS['customers_db'])){
	die("customer database:".$GLOBALS['customers_db']->getmessage());
}

// change database fetch mode
$GLOBALS['customers_db']->setFetchMode(DB_FETCHMODE_ASSOC);

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
		global $customers_db,$config;
		
		$sql = "SELECT * FROM ".$config['customers']['customertable']." ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " ";
		}//elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
		//	$sql .= " WHERE clid.resellerid = ".$_SESSION['curuser']['resellerid']." ";
		//}else{
		//	$sql .= " WHERE clid.groupid = ".$_SESSION['curuser']['groupid']." ";
		//}

		if($order == null){
			$sql .= " LIMIT $start, $limit";//.$_SESSION['ordering'];
		}else{
			$sql .= " ORDER BY $order ".$_SESSION['ordering']." LIMIT $start, $limit";
		}
		//echo $sql;exit;
		Customer::events($sql);

		$res =& $customers_db->query($sql);
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
		global $customers_db,$config;

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

		$sql = "SELECT * FROM ".$config['customers']['customertable']." WHERE ";
		//if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		//}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}
		
		Customer::events($sql);
		$res =& $customers_db->query($sql);
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
		global $customers_db,$config;
		
		//if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql = " SELECT COUNT(*) FROM ".$config['customers']['customertable']." ";
		//}//elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
		//	$sql = " SELECT COUNT(*) FROM clid LEFT JOIN accountgroup ON accountgroup.id = clid.groupid LEFT JOIN resellergroup ON resellergroup.id = clid.resellerid WHERE clid.resellerid = ".$_SESSION['curuser']['resellerid']." ";
		//}else{
		//	$sql = " SELECT COUNT(*) FROM clid LEFT JOIN accountgroup ON accountgroup.id = clid.groupid LEFT JOIN resellergroup ON resellergroup.id = clid.resellerid WHERE clid.groupid = ".$_SESSION['curuser']['groupid']." ";
		//}

		Customer::events($sql);
		$res =& $customers_db->getOne($sql);
		return $res;		
	}

	function &getNumRowsMore($filter = null, $content = null,$table){
		global $customers_db,$config;
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

		$sql = "SELECT COUNT(*) FROM ".$config['customers']['customertable']." WHERE ";
		//if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		//}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " AND ".$joinstr;
			}
		Customer::events($sql);
		$res =& $customers_db->getOne($sql);
		return $res;
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $customers_db,$config;
		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		$sql = "SELECT * FROM ".$config['customers']['customertable']." WHERE ";
		//if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		//}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr."  "
					." ORDER BY ".$order
					." ".$_SESSION['ordering']
					." LIMIT $start, $limit $ordering";
		}

		Customer::events($sql);
		$res =& $customers_db->query($sql);
		return $res;
	}

	function &getNumRowsMorewithstype($filter, $content,$stype,$table){
		global $customers_db,$config;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

		$sql = "SELECT COUNT(*) FROM ".$config['customers']['customertable']." WHERE ";
		//if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		//}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " AND ".$joinstr;
			}

		Customer::events($sql);
		$res =& $customers_db->getOne($sql);
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
				
		$pin = astercrm::generateUniquePin(intval($config['system']['pin_len']));
		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">				
				<tr>
					<td nowrap align="left">'.$locate->Translate("Pin").'*</td>
					<td align="left"><input type="text" id="pin" name="pin" size="25" maxlength="30" value="'.$pin.'" readonly><input type="hidden" id="pin" name="pin" value="'.$pin.'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("First name").'</td>
					<td align="left"><input type="text" id="first_name" name="first_name" size="25" maxlength="50"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Last name").'</td>
					<td align="left"><input type="text" id="last_name" name="last_name" size="25" maxlength="50"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Discount").'</td>
					<td align="left"><input type="radio" id="discount_type" name="discount_type" value="0" onclick="selectDiscountType(this.value);" checked>'.$locate->Translate("Dynamic").'<input type="radio" id="discount_type" name="discount_type" value="1" onclick="selectDiscountType(this.value);">'.$locate->Translate("Static").'&nbsp;<input type="text" id="discount" name="discount" size="15" maxlength="10" disabled></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_save(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("continue").'</button></td>
				</tr>

			 </table>
			';

		$html .='
			</form>
			*'.$locate->Translate("obligatory_fields").'
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
		global $customers_db,$config,$locate;
		
		$sql = "SELECT * FROM ".$config['customers']['customertable']." WHERE id = $id";
		//echo $sql;exit;
		
		$customer = $customers_db->getRow($sql);
		if($customer['discount'] == -1){
			$dynamic = 'checked';
			$static = '';
			$discount_abled = 'disabled';
			$discount_value = '';
		}else{
			$dynamic = '';
			$static = 'checked';
			$discount_abled = '';
			$discount_value = $customer['discount'];
		}
//print_r($customer);exit;
		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Pin").'*</td>
					<td align="left"><input type="text" id="pin" name="pin" size="25" maxlength="30" value="'.$customer['pin'].'" readonly><input type="hidden" id="pin" name="pin" value="'.$customer['pin'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("First name").'</td>
					<td align="left"><input type="text" id="first_name" name="first_name" size="25" maxlength="50" value="'.$customer['first_name'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Last name").'</td>
					<td align="left"><input type="text" id="last_name" name="last_name" size="25" maxlength="50" value="'.$customer['last_name'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Discount").'</td>
					<td align="left"><input type="radio" id="discount_type" name="discount_type" value="0" onclick="selectDiscountType(this.value);" '.$dynamic.'>'.$locate->Translate("Dynamic").'<input type="radio" id="discount_type" name="discount_type" value="1" onclick="selectDiscountType(this.value);" '.$static.'>'.$locate->Translate("Static").'&nbsp;<input type="text" id="discount" name="discount" size="15" maxlength="10" value="'.$discount_value.'" '.$discount_abled.'></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_update(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("Continue").'</button></td>
				</tr>
			 </table>
			';
		$html .= '
				</form>
				*'.$locate->Translate("Obligatory Fields").'
				';
		return $html;
	}

	function checkValues($pin){

		global $customers_db,$config;

		$sql = "SELECT id FROM ".$config['customers']['customertable']." WHERE pin=$pin";
					
		astercrm::events($sql);
		$id =& $customers_db->getOne($sql);
		return $id;		
	}

	function insertNewCustomer($f){
		global $customers_db,$config;
		$f = astercrm::variableFiler($f);
		
		$sql= "INSERT INTO ".$config['customers']['customertable']." SET "
				."pin='".$f['pin']."', "
				."first_name='".$f['first_name']."', "
				."last_name = '".$f['last_name']."', "
				."discount = '".$f['discount']."', "
				."cretime = now() ";
				//echo $sql;exit;
		astercrm::events($sql);
		$res =& $customers_db->query($sql);
		return $res;
	}

	function updateCustomer($f){
		global $customers_db,$config;
		//print_r($f);exit;
		$f = astercrm::variableFiler($f);
		
		$sql= "UPDATE ".$config['customers']['customertable']." SET "
				."first_name='".$f['first_name']."', "
				."last_name = '".$f['last_name']."', "
				."discount = '".$f['discount']." 'WHERE pin = '".$f['pin']."'";
	
		astercrm::events($sql);
		$res =& $customers_db->query($sql);
		return $res;
	}

	function deleteCustomer($id){
		global $customers_db,$config;
		$query = "DELETE FROM ".$config['customers']['customertable']." WHERE id = $id";
		astercrm::events($query);
		$res =& $customers_db->query($query);
		return $res;
	}
}
?>
