<?
/*******************************************************************************
* rate.grid.inc.php

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				18 Oct 2007

* Functions List


* Revision 0.01  2007/11/21 13:15:00  last modified by solo
* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'rate.common.php';
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
		$sql= "INSERT INTO myrate SET "
				."dialprefix = '".$f['dialprefix']."', "
				."numlen = '".$f['numlen']."', "
				."destination = '".$f['destination']."', "
				."rateinitial = '".$f['rateinitial']."', "
				."initblock = '".$f['initblock']."', "			
				."billingblock = '".$f['billingblock']."',"
				."connectcharge = '".$f['connectcharge']."', "
				."addtime = now(), "
				."groupid = '".$f['groupid']."', "
				."resellerid = '".$f['resellerid']."' ";
		
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
		
		$sql= "UPDATE myrate SET "
				."dialprefix = '".$f['dialprefix']."', "
				."numlen = '".$f['numlen']."', "
				."destination = '".$f['destination']."', "
				."rateinitial = '".$f['rateinitial']."', "
				."initblock = '".$f['initblock']."', "			
				."billingblock = '".$f['billingblock']."', "
				."connectcharge= '".$f['connectcharge']."', "
				."groupid = '".$f['groupid']."', "
				."resellerid = '".$f['resellerid']."', "
				."addtime= now() "
				."WHERE id = ".$f['id'];
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
		
		$sql = "SELECT myrate.*, groupname,resellername FROM myrate LEFT JOIN accountgroup ON accountgroup.id = myrate.groupid LEFT JOIN resellergroup ON resellergroup.id = myrate.resellerid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0)";
		}else{
			$sql .= " ( (myrate.groupid = ".$_SESSION['curuser']['groupid']." OR myrate.groupid = 0) AND (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0) )";
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
		
		$sql = "SELECT myrate.*, groupname,resellername FROM myrate  LEFT JOIN accountgroup ON accountgroup.id = myrate.groupid LEFT JOIN resellergroup ON resellergroup.id = myrate.resellerid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0) ";
		}else{
			$sql .= " ( (myrate.groupid = ".$_SESSION['curuser']['groupid']." OR myrate.groupid = 0) AND (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0) ) ";
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
			$sql .= " SELECT COUNT(*) FROM myrate ";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " SELECT COUNT(*) FROM myrate WHERE resellerid = ".$_SESSION['curuser']['resellerid']." OR resellerid = 0 ";
		}else{
			$sql .= " SELECT COUNT(*) FROM myrate WHERE (groupid = ".$_SESSION['curuser']['groupid']." OR groupid = 0) AND (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0)";
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
			$sql = "SELECT COUNT(*) AS numRows FROM myrate LEFT JOIN accountgroup ON accountgroup.id = myrate.groupid LEFT JOIN resellergroup ON resellergroup.id = myrate.resellerid WHERE";

			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 ";
			}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
				$sql .= " (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0) ";
			}else{
				$sql .= " ( (myrate.groupid = ".$_SESSION['curuser']['groupid']." OR myrate.groupid = 0) AND (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0) )";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " AND ".$joinstr." ";
			}else {
				$sql .= " 1 ";
			}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function &getRecordsFilteredMorewithstype($start, $limit, $filter, $content, $stype,$order,$table){
		global $db;

		$joinstr = astercrm::createSqlWithStype($filter,$content,$stype);
		
		$sql = "SELECT myrate.*, groupname,resellername FROM myrate  LEFT JOIN accountgroup ON accountgroup.id = myrate.groupid LEFT JOIN resellergroup ON resellergroup.id = myrate.resellerid WHERE ";

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$sql .= " 1 ";
		}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
			$sql .= " (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0) ";
		}else{
			$sql .= " ( (myrate.groupid = ".$_SESSION['curuser']['groupid']." OR myrate.groupid = 0) AND (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0) ) ";
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

			$sql = "SELECT COUNT(*) AS numRows FROM myrate LEFT JOIN accountgroup ON accountgroup.id = myrate.groupid LEFT JOIN resellergroup ON resellergroup.id = myrate.resellerid WHERE";

			if ($_SESSION['curuser']['usertype'] == 'admin'){
				$sql .= " 1 ";
			}elseif ($_SESSION['curuser']['usertype'] == 'reseller'){
				$sql .= " (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0) ";
			}else{
				$sql .= " ( (myrate.groupid = ".$_SESSION['curuser']['groupid']." OR myrate.groupid = 0) AND (myrate.resellerid = ".$_SESSION['curuser']['resellerid']." OR myrate.resellerid = 0) )";
			}

			if ($joinstr!=''){
				$joinstr=ltrim($joinstr,'AND'); //去掉最左边的AND
				$sql .= " AND ".$joinstr." ";
			}else {
				$sql .= " 1 ";
			}

		Customer::events($sql);
		$res =& $db->getOne($sql);
		return $res;
	}

	function formAdd(){
		global $locate,$config;

		$reselleroptions = '';
		$reseller = astercrm::getAll('resellergroup');

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$reselleroptions .= '<select id="resellerid" name="resellerid" onchange="setGroup();">';
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

		$group = astercrm::getAll('accountgroup','resellerid',$_SESSION['curuser']['resellerid']);
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
			$groupoptions .= '<select id="groupid" name="groupid">';
			$groupoptions .= "<OPTION value='0'></OPTION>";
			while	($group->fetchInto($row)){
				if($config['synchronize']['display_synchron_server']){
					$row['groupname'] = astercrm::getSynchronDisplay($row['id'],$row['groupname']);
				}
				
				$groupoptions .= "<OPTION value='".$row['id']."'>".$row['groupname']."</OPTION>";
			}
			$groupoptions .= '</select>';
		}else{
			while	($group->fetchInto($row)){
				if ($row['id'] == $_SESSION['curuser']['groupid']){
					if($config['synchronize']['display_synchron_server']){
						$row['groupname'] = astercrm::getSynchronDisplay($row['id'],$row['groupname']);
					}

					$groupoptions .= $row['groupname'].'<input type="hidden" value="'.$row['id'].'" name="groupid" id="groupid">';
					break;
				}
			}
		}


	$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("Prefix").'</td>
					<td align="left"><input type="text" id="dialprefix" name="dialprefix" size="15" maxlength="30" onKeyUp="xajax_showBuyRate(this.value);" onclick="xajax_showBuyRate(this.value);">&nbsp;<span id="spanShowBuyRate" name="spanShowBuyRate"></span></td>
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
					<td align="left">'
						.$reselleroptions.
					'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'</td>
					<td align="left">'
						.$groupoptions.
					'</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<button id="submitButton" onClick=\'xajax_save(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("Continue").'</button>
					</td>
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
		$rate =& Customer::getRecordByID($id,'myrate');
		/*
		$group = astercrm::getAll('accountgroup');

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$groupoptions .= '<select id="groupid" name="groupid">';
			while	($group->fetchInto($row)){
				if ($row['id'] == $rate['groupid']){
					$groupoptions .= "<OPTION value='".$row['id']."' selected>".$row['groupname']."</OPTION>";
				}else{
					$groupoptions .= "<OPTION value='".$row['id']."'>".$row['groupname']."</OPTION>";
				}
			}
			$groupoptions .= '</select>';
		}else{
			while	($group->fetchInto($row)){
				if ($row['id'] == $_SESSION['curuser']['groupid']){
					$groupoptions .= $row['groupname'].'<input type="hidden" value="'.$row['id'].'" name="groupid" id="groupid">';
					break;
				}
			}
		}
		*/
		$reselleroptions = '';
		$reseller = astercrm::getAll('resellergroup');

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			$reselleroptions .= '<select id="resellerid" name="resellerid" onchange="setGroup();">';
			$reselleroptions .= '<option value="0"></option>';
			while	($reseller->fetchInto($row)){
				if($config['synchronize']['display_synchron_server']){
					$row['resellername'] = astercrm::getSynchronDisplay($row['id'],$row['resellername']);
				}

				if ($row['id'] == $rate['resellerid']){
					$reselleroptions .= "<OPTION value='".$row['id']."' selected>".$row['resellername']."</OPTION>";
				}else{
					$reselleroptions .= "<OPTION value='".$row['id']."' >".$row['resellername']."</OPTION>";
				}
			}
			$reselleroptions .= '</select>';
		}else{
			while	($reseller->fetchInto($row)){
				if ($row['id'] == $rate['resellerid']){
					if($config['synchronize']['display_synchron_server']){
						$row['resellername'] = astercrm::getSynchronDisplay($row['id'],$row['resellername']);
					}

					$reselleroptions .= $row['resellername'].'<input type="hidden" value="'.$row['id'].'" name="resellerid" id="resellerid">';
					break;
				}
			}
		}

		$group = astercrm::getAll('accountgroup','resellerid',$rate['resellerid']);
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
			$groupoptions .= '<select id="groupid" name="groupid">';
			$groupoptions .= "<OPTION value='0'></OPTION>";
			while	($group->fetchInto($row)){
				if($config['synchronize']['display_synchron_server']){
					$row['groupname'] = astercrm::getSynchronDisplay($row['id'],$row['groupname']);
				}

				if ($row['id'] == $rate['groupid']){
					$groupoptions .= "<OPTION value='".$row['id']."' selected>".$row['groupname']."</OPTION>";
				}else{
					$groupoptions .= "<OPTION value='".$row['id']."' >".$row['groupname']."</OPTION>";
				}
			}
			$groupoptions .= '</select>';
		}else{
			while	($group->fetchInto($row)){
				if ($row['id'] == $rate['groupid']){
					if($config['synchronize']['display_synchron_server']){
						$row['groupname'] = astercrm::getSynchronDisplay($row['id'],$row['groupname']);
					}

					$groupoptions .= $row['groupname'].'<input type="hidden" value="'.$row['id'].'" name="groupid" id="groupid">';
					break;
				}
			}
		}


		$html = '
			<!-- No edit the next line -->
			<form method="post" name="f" id="f">
			
			<table border="1" width="100%" class="adminlist">
				<tr>
					<td nowrap align="left">'.$locate->Translate("prefix").'</td>
					<td align="left"><input type="hidden" id="id" name="id" value="'. $rate['id'].'"><input type="text" id="dialprefix" name="dialprefix" size="25" maxlength="30" value="'.$rate['dialprefix'].'" onKeyUp="xajax_showBuyRate(this.value);" onclick="xajax_showBuyRate(this.value);">&nbsp;<span id="spanShowBuyRate" name="spanShowBuyRate"></span></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("length").'</td>
					<td align="left"><input type="text" id="numlen" name="numlen" size="10" maxlength="10" value="'.$rate['numlen'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Destination").'</td>
					<td align="left"><input type="text" id="destination" name="destination" size="25" maxlength="30" value="'.$rate['destination'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Connect charge").'</td>
					<td align="left"><input type="text" id="connectcharge" name="connectcharge" size="20" maxlength="20" value="'.$rate['connectcharge'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Init block").'</td>
					<td align="left"><input type="text" id="initblock" name="initblock" size="25" maxlength="100" value="'.$rate['initblock'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Rate").'</td>
					<td align="left"><input type="text" id="rateinitial" name="rateinitial" size="25" maxlength="30" value="'.$rate['rateinitial'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Billing block").'</td>
					<td align="left"><input type="text" id="billingblock" name="billingblock" size="25" maxlength="30" value="'.$rate['billingblock'].'"></td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Reseller").'</td>
					<td align="left">'
						.$reselleroptions.
					'</td>
				</tr>
				<tr>
					<td nowrap align="left">'.$locate->Translate("Group").'</td>
					<td align="left">
					'
						.$groupoptions.
					'
					</td>
				</tr>
				<tr>
					<td colspan="2" align="center">
						<button id="submitButton" onClick=\'xajax_update(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("Continue").'</button>
					</td>
				</tr>

			 </table>
			';

			

		$html .= '
				</form>
				*'.$locate->Translate("obligatory_fields").'
				';

		return $html;
	}

	function shortUpdateGrid($groupid,$resellerid){
		global $locate;
		$html = '<table border="0" width="99%" style="line-height: 30px;" class="adminlist"><tbody><tr><th class="title" ><b>'.$locate->Translate("Shortcut update rate").'</b></th></tr><tbody></table>
				<table border="0" width="99%" style="line-height: 25px; padding: 0px;" class="adminlist" ><tbody>';
		$ratelist = astercc::searchRateForShortUpdate($groupid,$resellerid);
		$flag = 0;
		$class="row0";
		foreach($ratelist as $rate_row){
			$flag++;
			if($flag%2 == 0) $tr .= '<td style="cursor: pointer;" width="10%">&nbsp;&nbsp;</td>';
			
			$tr .= '<td style="cursor: pointer;" width="10%">'.$rate_row['mdialprefix'].'</td><td style="cursor: pointer;" width="10%">'.$rate_row['mdestination'].'</td><td style="cursor: pointer;" width="10%">'.$rate_row['crateinitial'].'</td><td style="cursor: pointer;" width="10%"><input type="text" value="'.$rate_row['mrateinitial'].'" size="10" id="'.$rate_row['mid'].'-mrateinitial" onKeyUp="filedFilter(this,\'numeric\');"></td><td style="cursor: pointer;" width="10%"><input type="button" value="'.$locate->Translate("Update").'" onclick="shortcutUpdateSave(\''.$rate_row['mid'].'\');"></td>';
			
			if($flag%2 != 0){
				$tr = '<tr class="'.$class.'">'.$tr;
				if($class == 'row1') 
					$class = 'row0';
				else
					$class = 'row1';
			}else{
				$tr = $tr.'</tr>';
				$html .= $tr;
				$tr = '';				
			}
		}
		
		if($flag%2 != 0) $html .= $tr.'<td></td><td colspan="5"></td></tr>';
		$html .= '</tbody></table>';
		return $html;
	}
}
?>
