<?
/*******************************************************************************
* resellerrate.grid.inc.php

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List


* Revision 0.01  2007/11/21 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'resellerrate.common.php';
require_once 'include/astercrm.class.php';


class Customer extends astercrm
{

	/**
	*  Inserta un nuevo registro en la tabla.
	*
	*	@param $f	(array)		Arreglo que contiene los datos del formulario pasado.
	*	@return $res	(object) 	Devuelve el objeto con la respuesta de la sentencia SQL ejecutada del INSERT.

	*/
	
	function insertNewRate($f){
		global $db,$config;
		$f = astercrm::variableFiler($f);
		$sql= "INSERT INTO resellerrate SET "
				."dialprefix='".$f['dialprefix']."', "
				."numlen='".$f['numlen']."', "
				."destination='".$f['destination']."', "
				."rateinitial ='".$f['rateinitial']."',"
				."initblock='".$f['initblock']."',"			
				."billingblock='".$f['billingblock']."',"
				."connectcharge='".$f['connectcharge']."', "
				."addtime= now(), "
				."resellerid= ".$f['resellerid']." ";

		if($config['synchronize']['id_autocrement_byset']){
			$sql .= ",id='".$f['id']."' ";
		}

		Customer::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

	function updateRateRecord($f){
		global $db;
		$f = astercrm::variableFiler($f);
		
		$sql= "UPDATE resellerrate SET "
				."dialprefix='".$f['dialprefix']."', "
				."numlen='".$f['numlen']."', "
				."destination='".$f['destination']."', "
				."rateinitial ='".$f['rateinitial']."',"
				."initblock='".$f['initblock']."',"			
				."billingblock='".$f['billingblock']."',"
				."addtime= now(),"
				."connectcharge='".$f['connectcharge']."', "
				."resellerid= ".$f['resellerid']." "
				."WHERE id='".$f['id']."'";
//		print $sql;
//		exit;
		astercrm::events($sql);
		$res =& $db->query($sql);
		return $res;
	}

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
		
		$sql = "SELECT resellerrate.*, resellername FROM resellerrate  LEFT JOIN resellergroup ON resellergroup.id = resellerrate.resellerid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " (resellerid = ".$_SESSION['curuser']['resellerid']." OR resellerid = 0) ";
		}

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
		
		$sql = "SELECT resellerrate.*, resellername FROM resellerrate  LEFT JOIN resellergroup ON resellergroup.id = resellerrate.resellerid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " (resellerid = ".$_SESSION['curuser']['resellerid']." OR resellerid = 0)";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr." "
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
			$sql = "SELECT COUNT(*) AS numRows FROM resellerrate LEFT JOIN resellergroup ON resellergroup.id = resellerrate.resellerid ";
		}else{
		$sql = "SELECT COUNT(*) AS numRows FROM resellerrate LEFT JOIN resellergroup ON resellergroup.id = resellerrate.resellerid WHERE resellerid = '".$_SESSION['curuser']['resellerid']."' ";
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
			$sql = "SELECT COUNT(*) AS numRows FROM resellerrate LEFT JOIN resellergroup ON resellergroup.id = resellerrate.resellerid WHERE";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 ";
			}else{
				$sql .= " (resellerid = ".$_SESSION['curuser']['resellerid']." OR resellerid = 0)";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " AND ".$joinstr." ";
			}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);
		
		$sql = "SELECT resellerrate.*, resellername FROM resellerrate  LEFT JOIN resellergroup ON resellergroup.id = resellerrate.resellerid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}else{
			$sql .= " (resellerid = ".$_SESSION['curuser']['resellerid']." OR resellerid = 0)";
		}

		if ($joinstr!=''){
			$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
			$sql .= " AND ".$joinstr." "
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
		
			$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);

			$sql = "SELECT COUNT(*) AS numRows FROM resellerrate LEFT JOIN resellergroup ON resellergroup.id = resellerrate.resellerid WHERE";
			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 ";
			}else{
				$sql .= " (resellerid = ".$_SESSION['curuser']['resellerid']." OR resellerid = 0)";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " AND ".$joinstr." ";
			}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function formAdd(){
	
		global $locate,$config;
		$reseller = astercrm::getAll('resellergroup');
		while	($reseller->fetchInto($row)){
			if($config['synchronize']['display_synchron_server']){
				$row['resellername'] = astercrm::getSynchronDisplay($row['id'],$row['resellername']);
			}

			$options .= "<OPTION value='".$row['id']."'>".$row['resellername']."</OPTION>";
		}
		$options .= "<OPTION value='0'>"."All"."</OPTION>";


		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Prefix").'</td>
					<td align="left"><input type="text" id="dialprefix" name="dialprefix" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Length").'</td>
					<td align="left"><input type="text" id="numlen" name="numlen" size="10" maxlength="10" value="0"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Destination").'</td>
					<td align="left"><input type="text" id="destination" name="destination" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Connect Charge").'</td>
					<td align="left"><input type="text" id="connectcharge" name="connectcharge" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Init Block").'</td>
					<td align="left"><input type="text" id="initblock" name="initblock" size="25" maxlength="100"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Rate").'</td>
					<td align="left"><input type="text" id="rateinitial" name="rateinitial" size="25" maxlength="30"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Billing Block").'</td>
					<td align="left"><input type="text" id="billingblock" name="billingblock" size="25" maxlength="5" value="60"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Reseller").'</td>
					<td align="left">
						<select id="resellerid" name="resellerid">'
						.$options.
						'</select>
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_save(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("Continue").'</button></td>
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
		$rate =& Customer::getRecordByID($id,'resellerrate');
		$reseller = astercrm::getAll('resellergroup');

		$options .= '<select id="resellerid" name="resellerid">';
		$flag = false;
		while	($reseller->fetchInto($row)){
			if($config['synchronize']['display_synchron_server']){
				$row['resellername'] = astercrm::getSynchronDisplay($row['id'],$row['resellername']);
			}

			if ($row['id'] == $rate['resellerid']){
				$options .= "<OPTION value='".$row['id']."' selected>".$row['resellername']."</OPTION>";
				$flag = true;
			}else{
				$options .= "<OPTION value='".$row['id']."'>".$row['resellername']."</OPTION>";
			}
		}
		if ($flag == true){
			$options .= "<OPTION value='0'>".$locate->Translate("All")."</OPTION>";
		}else{
			$options .= "<OPTION value='0' selected>".$locate->Translate("All")."</OPTION>";
		}
		$options .= '</select>';

		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Prefix").'</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $rate['id'].'"><input type="text" id="dialprefix" name="dialprefix" size="25" maxlength="30" value="'.$rate['dialprefix'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Length").'</td>
					<td align="left"><input type="text" id="numlen" name="numlen" size="10" maxlength="10" value="'.$rate['numlen'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Dest").'</td>
					<td align="left"><input type="text" id="destination" name="destination" size="25" maxlength="30" value="'.$rate['destination'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Connect Charge").'</td>
					<td align="left"><input type="text" id="connectcharge" name="connectcharge" size="20" maxlength="20" value="'.$rate['connectcharge'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Init Block").'</td>
					<td align="left"><input type="text" id="initblock" name="initblock" size="25" maxlength="100" value="'.$rate['initblock'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Rate").'</td>
					<td align="left"><input type="text" id="rateinitial" name="rateinitial" size="25" maxlength="30" value="'.$rate['rateinitial'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Billing Block").'</td>
					<td align="left"><input type="text" id="billingblock" name="billingblock" size="25" maxlength="30" value="'.$rate['billingblock'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Reseller").'</td>
					<td align="left">
					'
						.$options.
					'
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_update(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("Continue").'</button></td>
				</tr>

			 </table>
			';

			

		$html .= '
				</form>
				*'.$locate->Translate("obligatory_fields").'
				';

		return $html;
	}
}
?>
