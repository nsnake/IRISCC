<?
/*******************************************************************************
* delete_rate.grid.inc.php

* @author			Solo Fu <solo.fu@gmail.com>
* @classVersion		1.0
* @date				08 May 2012

* Functions List

* Desc: page created

********************************************************************************/

require_once 'db_connect.php';
require_once 'delete_rate.common.php';
require_once 'include/astercrm.class.php';


class Customer extends astercrm {
	
	function getObjectHtml($table){
		global $db,$locate,$config;
		
		$sql = "SELECT * FROM $table ; ";
		
		Customer::events($sql);
		$res =& $db->query($sql);
		
		$html = '<select id="delObject" name="delObject">';
		if($table == 'accountgroup'){
			$html .= '<option value="default">'.$locate->Translate('default').'</option>';
		}

		while($res->fetchInto($row)){
			$html .= '<option value="'.$row['id'].'">';

			if($config['synchronize']['display_synchron_server']){
				if($table == 'resellergroup') {
					$html .= astercrm::getSynchronDisplay($row['id'],$row['resellername']);
				} else {
					$html .= astercrm::getSynchronDisplay($row['id'],$row['groupname']);
				}
			} else {
				if($table == 'resellergroup') {
					$html .= $row['resellername'];
				} else {
					$html .= $row['groupname'];
				}
			}
			
			$html .= '</option>';
		}
		$html .= '</select>';
		return $html;
	}

	function searchRateHtml($table,$type,$object){
		global $db,$locate,$config;

		if($table == 'resellerrate'){
			$sql = "SELECT $table.*,resellergroup.resellername FROM $table LEFT JOIN resellergroup ON resellergroup.id = $table.resellerid WHERE 1";
		} else {
			$sql = "SELECT $table.*,resellergroup.resellername,accountgroup.groupname FROM $table LEFT JOIN resellergroup ON resellergroup.id = $table.resellerid LEFT JOIN accountgroup ON accountgroup.id = $table.groupid WHERE 1";
		}
		
		$totalSql = "SELECT count(*) FROM $table WHERE 1 ";
		$insertSql = "INSERT INTO ${table}_history SELECT * FROM $table WHERE 1 ";
		$deleteSql = "DELETE FROM $table WHERE 1 ";

		$tmpSql = '';
		if($type == 'all'){
			$tmpSql .= " ";
		} else if($type == 'system'){
			$tmpSql .= " AND $table.resellerid = 0 ";
		} else if($type == 'reseller'){
			$tmpSql .= " AND $table.resellerid = '$object' ";
		} else if($type == 'group'){
			if($object == 'default'){
				$tmpSql .= " AND $table.groupid = 0 ";
			} else {
				$tmpSql .= " AND $table.groupid = '$object' ";
			}
		}
		$insertSql .= $tmpSql;

		//search total rate by this conditions
		Customer::events($totalSql.$tmpSql);
		$totalRes = & $db->query($totalSql.$tmpSql);
		$totalRes->fetchInto($totalResult,DB_FETCHMODE_ORDERED);

		$deleteSql = $deleteSql.$tmpSql;//delete sql
		
		$sql = $sql.$tmpSql." limit 20 ; ";//show 20 data on the page
		
		Customer::events($sql);
		$result = & $db->query($sql);

		//&nbsp;<input type="button" value="'.$locate->Translate("delete").'" onclick="if (confirm(\''.$locate->Translate("Are you sure you want to delete this rate").'?\')) xajax_deleteRate(document.getElementById(\'deleteSql\').value);return false;" />
		$dataHtml = 
			'<div>'.$locate->Translate('Rate Amount is').'&nbsp;'.$totalResult[0].','.$locate->Translate('default show 20 data').'</div>
			<table border="1" class="adminlist">
				<tr>
					<th width="" class="title">'.$locate->Translate('id').'</th>
					<th width="" class="title">'.$locate->Translate('prefix').'</th>
					<th width="" class="title">'.$locate->Translate('length').'</th>
					<th width="" class="title">'.$locate->Translate('destination').'</th>
					<th width="" class="title">'.$locate->Translate('connect_charge').'</th>
					<th width="" class="title">'.$locate->Translate('init_block').'</th>
					<th width="" class="title">'.$locate->Translate('rate').'</th>
					<th width="" class="title">'.$locate->Translate('billing_block').'</th>
					<th width="" class="title">'.$locate->Translate('group').'</th>
					<th width="" class="title">'.$locate->Translate('reseller').'</th>
					<th width="" class="title">'.$locate->Translate('addtime').'</th>
				</tr>';
		
		$i = 0;
		while($result->fetchInto($row)){
			$j = 0;
			if($i%2 == 0){
				$j = 1;
			}

			if($config['synchronize']['display_synchron_server']){
				$html .= astercrm::getSynchronDisplay($row['id'],$row['id']);
			}
			
			$dataHtml .= 
				'<tr class="row'.$j.'" id="gridRow'.$i.'">
					<td style="cursor: pointer;" id="gridRow1Col1">'.$row['id'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col2">'.$row['dialprefix'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col3">'.$row['numlen'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col4">'.$row['destination'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col5">'.$row['connectcharge'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col6">'.$row['initblock'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col7">'.$row['rateinitial'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col8">'.$row['billingblock'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col9">'.$row['groupname'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col10">'.$row['resellername'].'</td>
					<td style="cursor: pointer;" id="gridRow1Col11">'.$row['addtime'].'</td>
				</tr>';
		}
		$dataHtml .= '</table><input type="hidden" id="deleteSql" value="'.$deleteSql.'" /><input type="hidden" id="historySql" value="'.$insertSql.'" />';
		
		return $dataHtml;
	}
}
?>
