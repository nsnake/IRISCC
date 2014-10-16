<?
/*******************************************************************************
* astercc.class.php
* asterisk events class, read datas from table: curcdr
**/

class astercc extends PEAR
{
	function generateResellerFile(){
		global $db,$config;
		if ($config['system']['sipfile'] == '')	return;

		$content = '';
		$filename = $config['system']['sipfile'];
		
		$query = "SELECT id FROM resellergroup ";
		$reseller_list = $db->query($query);

		while	($reseller_list->fetchInto($reseller)){
			$content .= "#include ".$filename."_".$reseller['id'].".conf\n";
		}

		$filename = $filename.".conf";

		$fp=fopen($filename,"w");
		if (!$fp){
			print "file: $filename open failed, please check the file permission";
			exit;
		}
		fwrite($fp,$content);
	}

	function generatePeersFile($resellerid){
		global $db,$config;

		if ($config['system']['sipfile'] == '')	return;
		$accountcode = '';
		$clid_context = '';
		
		$query = "SELECT * FROM resellergroup WHERE id = $resellerid";
		$reseller = $db->getRow($query);
		$accountcode = $reseller['accountcode'];
		$clid_context = $reseller['clid_context'];

		$configstatus = common::read_ini_file($config['system']['astercc_path'].'/astercc.conf',$asterccConfig);
		if ($asterccConfig['system']['billingfield'] != 'accountcode'){
			$accountcode = $reseller['accountcode'];
		}

		$query = "SELECT * FROM accountgroup WHERE resellerid = $resellerid";
		$group_list = $db->query($query);
		$content = '';
		while	($group_list->fetchInto($group)){
			if($group['accountcode'] != '' && $asterccConfig['system']['billingfield'] != 'accountcode'){
				$accountcode = $group['accountcode'];
			}

			$query = "SELECT * FROM clid WHERE groupid = ".$group['id']." ORDER BY clid ASC";
			$clid_list = $db->query($query);

			while	($clid_list->fetchInto($row)){
				if($asterccConfig['system']['billingfield'] == 'accountcode'){
					$accountcode = $row['clid'];
				}
				$content .= "[".$row['clid']."]\n";

				foreach ($config['sipbuddy'] as  $key=>$value){
					if($clid_context != '' && strtolower(trim($key)) == 'context'){
						$content .= "$key = $clid_context\n";
						continue;
					}
					if ($key != '' && $value != '')
						$content .= "$key = $value\n";
				}
				if($accountcode != "" && $accountcode != "''"){
					$content .= "accountcode = ".$accountcode."\n";
				}
				$content .= "secret = ".$row['pin']."\n";
				$content .= "callerid = \"".$row['clid']."\" <".$row['clid'].">\n";
				$content .= "accountcode = ".$row['accountcode']."\n\n";

			}
		}

		$filename = $config['system']['sipfile']."_$resellerid.conf";

		$fp=fopen($filename,"w");
		if (!$fp){
			print "file: $filename open failed, please check the file permission";
			exit;
		}
		fwrite($fp,$content);
	}

	function checkPeerStatus($groupid,$peers){
		global $config;
		$curChans =& astercc::getCurChan($groupid);
		#print_r($curChans);exit;
		$status =  array();
		while ($curChans->fetchInto($list)) {
			//print_r($list);exit;
			// 检查src或者dst是否在peers里
			
			if($_SESSION['curuser']['billingfield'] == 'accountcode'){
				if (astercc::array_exist($list['accountcode'],$peers)){
					$status[$list['accountcode']] = $list;
					$status[$list['accountcode']]['direction'] = 'outbound';
				}
			}else{
				if (astercc::array_exist($list['src'], $peers) || astercc::array_exist($list['dst'], $peers)){
					$status[$list['src']] = $list;
					$status[$list['src']]['direction'] = 'outbound';
				}else{
					// 使用srcchan作为src
					if (ereg("\/(.*)-", $list['srcchan'], $myAry) ){
						$status[$myAry[1]] = $list;
						$status[$myAry[1]]['direction'] = 'outbound';
					}
				}
			}
			$status[$list['dst']] = $list;
			$status[$list['dst']]['direction'] = 'inbound';
		}
		return $status;
	}

	function array_exist($value,$ary){
		foreach ($ary as $val){
			if ($val == $value){
				return true;
			}
		}
		return false;
	}

	function creditDigits($credit){
		return number_format($credit,2);
	}

	function setCreditLimit($channel,$creditlimit){
		global $db;
		$query = "UPDATE curcdr SET creditlimit = '$creditlimit' WHERE srcchan = '$channel' ";
		astercc::events($query);
		$res = $db->query($query);
		return $db->affectedRows();
	}

	function setStatus($clid,$status){
		global $db;
		$query = "UPDATE clid SET status = $status, addtime = now()  WHERE clid = '$clid'";
		astercc::events($query);
		$res = $db->query($query);
		//print_r($res);
		//print $query;
		//exit;
		return $db->affectedRows();
	}

	function getCallback($groupid){
		global $db;
		$query = "SELECT * FROM curcdr WHERE groupid = $groupid AND LEFT(srcchan,6) = 'local/'";
		astercc::events($query);
		$res = $db->query($query);
		return $res;
	}

	function insertNewCallback($f){
		global $db;
		$sql= "INSERT INTO callback SET "
				."lega='".$f['lega']."', "
				."legb='".$f['legb']."', "
				."credit='".$f['credit']."',"
				."groupid='".$f['groupid']."', "
				."addtime= now() ";
		$res =& $db->query($sql);
		astercc::events($query);
		return $res;
	}

	function getCurChan($groupid){
		global $db;
		$query = "SELECT * FROM curcdr WHERE groupid = $groupid";
		#$condition = '';
		#foreach ($peers as $peer){
		#	$condition .= " src = '".$peer."' OR";
		#}
		#$query .= substr($condition,0,-2); // delete the last "AND"
		astercc::events($query);
		$res = $db->query($query);
		return $res;
	}

	function getAll($table,$field = '', $value = ''){
		global $db;
		if (trim($field) != '' && trim($value) != ''){
			$query = "SELECT * FROM $table WHERE $field = '$value' ";
		}else{
			$query = "SELECT * FROM $table ";
		}
		astercc::events($query);
		$res = $db->query($query);
		return $res;
	}

	function getCurLocalChan($chan, $groupid){
		global $db;
		$query = "SELECT * FROM curcdr WHERE srcchan LIKE '$chan%' AND groupid = $groupid ORDER BY starttime ASC";
//		print $query;
//		exit;
		astercc::events($query);
		$res = $db->query($query);
		return $res;
	}

	function events($event = null){
		if(LOG_ENABLED){
			$now = date("Y-M-d H:i:s");
   		$fd = fopen (FILE_LOG,'a');
			$log = $now." ".$_SERVER["REMOTE_ADDR"] ." - $event \n";
	   	fwrite($fd,$log);
   		fclose($fd);
		}
	}

	function searchRate($dst,$groupid, $resellerid, $tbl = 'myrate',$type="prefix"){
		global $db;
		$dst = trim($dst);
		if ($groupid == '' || $groupid == '-1') {
			#print "invalid identity";
			return;
		}
		
		if($type == "prefix"){
			if($tbl == "resellerrate")
				$sql = "SELECT * FROM $tbl WHERE resellerid = $resellerid";
			else
				$sql = "SELECT * FROM $tbl WHERE groupid = $groupid AND resellerid = $resellerid";
			//echo $sql;exit;
			astercc::events($sql);
			$rates = & $db->query($sql);

			$maxprefix = '';
			$myrate = array();
			$default = '';

			while ($rates->fetchInto($list)) {
				#print "start\n";
				if ($list['dialprefix'] == 'default'){
					$default = $list;
					continue;
				}

				$prefixlength = strlen($list['dialprefix']);
				if (substr($dst,0,$prefixlength) == $list['dialprefix']){
					if ($prefixlength > strlen($maxprefix)){
						$myrate = $list;
						$maxprefix = $list['dialprefix'];
					}
				}
			}


			if ($maxprefix == '' && $default == ''){ // did get rate from group

				if ($groupid == "0" && $resellerid == "0") {
					//print "done\n";
					//exit;
					return;
				}
				
				if ($groupid != "0" && $resellerid != "0") {
					//print "here\n";
					return astercc::searchRate($dst,"0",$resellerid,$tbl);		
				}

				if ($groupid == 0 && $resellerid != 0) {
					//print "ok";
					//exit;
					return astercc::searchRate($dst,"0","0",$tbl,'prefix');		
				}

				//return astercc::readRate($dst,$groupid, $tbl);
			}
		
			if ($maxprefix == ''){
				return $default;
			}else{
				return $myrate;
			}
		}else{

			$sql = "SELECT * FROM $tbl WHERE groupid = $groupid AND resellerid = $resellerid AND destination = '".$dst."'";
			//echo $sql;exit;
			astercc::events($sql);
			$rates = & $db->getRow($sql);

			if($rates['id'] != ''){
				return $rates;
			}elseif($groupid != 0 && $resellerid != 0){
				return astercc::searchRate($dst,"0","0",$tbl,'dest');
			}else{
				return;
			}
		}
	}

	function setBilled($id,$payment='',$costomerid=0,$discount=0.0000){
		global $db,$config;
		// move the record from mycdr to historycdr
		// get the record from mycdr
		$sql = "SELECT * FROM mycdr WHERE id = $id ";
		astercc::events($sql);
		$cdr = &$db->getRow($sql);
		$credit = $cdr['credit'];
		// insert the record to historycdr
		if($config['system']['useHistoryCdr'] == 1){
			$sql = "INSERT INTO historycdr SET calldate = '".$cdr['calldate']."', src = '".$cdr['src']."', `dst` = '".$cdr['dst']."',`srcname` = '".$cdr['srcname']."', `channel` = '".$cdr['channel']."', `dstchannel` = '".$cdr['dstchannel']."',`didnumber` = '".$cdr['didnumber']."', `duration` = '".$cdr['duration']."', `billsec` = '".$cdr['billsec']."', `billsec_leg_a` = '".$cdr['billsec_leg_a']."', `disposition` = '".$cdr['disposition']."', `accountcode` = '".$cdr['accountcode']."', `userfield` = 'BILLED', `srcuid` = '".$cdr['srcuid']."', `dstuid` = '".$cdr['dstuid']."',`queue` = '".$cdr['queue']."', `calltype` = '".$cdr['calltype']."', `credit` = '".$cdr['credit']."', `callshopcredit` = '".$cdr['callshopcredit']."', `resellercredit` = '".$cdr['resellercredit']."', `groupid` = '".$cdr['groupid']."', `resellerid` = '".$cdr['resellerid']."', `userid` = '".$cdr['userid']."', `accountid` = '".$cdr['accountid']."', `destination` = '".$cdr['destination']."', `memo` = '".$cdr['memo']."',`dialstring` = '".$cdr['dialstring']."',children = '".$cdr['children']."',ischild = '".$cdr['ischild']."',processed = '".$cdr['processed']."',customerid = $costomerid,crm_customerid = '".$cdr['crm_customerid']."',contactid = '".$cdr['contactid']."', discount = $discount ,payment='".$payment."',note='".$cdr['note']."',setfreecall='".$cdr['setfreecall']."',astercrm_groupid='".$cdr['astercrm_groupid']."',hangupcause='".$cdr['hangupcause']."',hangupcausetxt='".$cdr['hangupcausetxt']."',dialstatus='".$cdr['dialstatus']."'";
		}else {
			$sql = "UPDATE mycdr SET userfield = 'BILLED' ,customerid = $costomerid, discount = $discount , payment='".$payment."' WHERE id = $id ";
		}
		astercc::events($sql);
		$cdr = &$db->query($sql);

		// remove the record from mycdr
		if($config['system']['useHistoryCdr'] == 1){
			$sql ="DELETE FROM mycdr WHERE id =$id ";
			astercc::events($sql);
			$res = $db->query($sql);
		}
		return $credit;
	}

	function setAllBilled($resellerid,$groupid,$clidid){
		global $db,$config;

		if($clidid > 0){
			$sql = "SELECT * FROM mycdr WHERE src = '$booth' OR dst = '$booth'";
		}elseif($groupid > 0){
			$sql = "SELECT * FROM mycdr WHERE groupid = $groupid ";
		}elseif($resellerid > 0){
			$sql = "SELECT * FROM mycdr WHERE resellerid = $resellerid ";
		}else{
			$sql = "SELECT * FROM mycdr";
		}
		astercc::events($sql);
		$cdrs = &$db->query($sql);

		// insert the record to historycdr
		if($config['system']['useHistoryCdr'] == 1){
			while ($cdrs->fetchInto($cdr)) {
				$sql = "INSERT INTO historycdr SET calldate = '".$cdr['calldate']."', src = '".$cdr['src']."', `dst` = '".$cdr['dst']."',`srcname` = '".$cdr['srcname']."', `channel` = '".$cdr['channel']."', `dstchannel` = '".$cdr['dstchannel']."',`didnumber` = '".$cdr['didnumber']."', `duration` = '".$cdr['duration']."', `billsec` = '".$cdr['billsec']."', `billsec_leg_a` = '".$cdr['billsec_leg_a']."', `disposition` = '".$cdr['disposition']."', `accountcode` = '".$cdr['accountcode']."', `userfield` = 'BILLED', `srcuid` = '".$cdr['srcuid']."', `dstuid` = '".$cdr['dstuid']."',`queue` = '".$cdr['queue']."', `calltype` = '".$cdr['calltype']."', `credit` = '".$cdr['credit']."', `callshopcredit` = '".$cdr['callshopcredit']."', `resellercredit` = '".$cdr['resellercredit']."', `groupid` = '".$cdr['groupid']."', `resellerid` = '".$cdr['resellerid']."', `userid` = '".$cdr['userid']."', `accountid` = '".$cdr['accountid']."', `destination` = '".$cdr['destination']."', `memo` = '".$cdr['memo']."',`dialstring` = '".$cdr['dialstring']."',children = '".$cdr['children']."',ischild = '".$cdr['ischild']."',processed = '".$cdr['processed']."',customerid = 0,crm_customerid = '".$cdr['crm_customerid']."',contactid = '".$cdr['contactid']."', discount = 0 ,note='".$cdr['note']."',setfreecall='".$cdr['setfreecall']."',astercrm_groupid='".$cdr['astercrm_groupid']."',hangupcause='".$cdr['hangupcause']."',hangupcausetxt='".$cdr['hangupcausetxt']."',dialstatus='".$cdr['dialstatus']."'";

				astercc::events($sql);
				$res = &$db->query($sql);
				$sql ="DELETE FROM mycdr WHERE id='".$cdr['id']."' ";
				astercc::events($sql);
				$res = $db->query($sql);
			}
		}else {
			while ($cdrs->fetchInto($cdr)) {
				$sql = "UPDATE mycdr SET userfield = 'BILLED' WHERE id = '".$cdr['id']."' ";
				astercc::events($sql);
				$res = &$db->query($sql);
			}
		}
		
		return 1;
	}

	function readUnbilled($peer,$leg = null,$groupid){
		global $db,$config;
		if($config['system']['booth_cdr_order'] == 'calldate_ASC'){
			$cdr_order = 'ASC';
		} else {
			$cdr_order = 'DESC';
		}

		if ($leg == null){
			

			if($_SESSION['curuser']['billingfield'] == 'accountcode'){
				$query = "SELECT * FROM mycdr WHERE (src = '$peer' OR dst = '$peer' OR accountcode='$peer') AND userfield = 'UNBILLED' AND groupid = $groupid ORDER BY calldate $cdr_order";
			}else{
				$query = "SELECT * FROM mycdr WHERE (src = '$peer' OR dst = '$peer' OR accountcode='$peer') AND userfield = 'UNBILLED' AND groupid = $groupid ORDER BY calldate $cdr_order";
			}
		}else{
			/*
			$query = 'SELECT * FROM cdr WHERE 
				src = "'.$peer.' AND dst="'.$leg.'"" 
				AND userfield = "UNBILLED" ORDER BY calldate';
				*/
			$query = "SELECT * FROM mycdr WHERE channel LIKE 'local/$peer%' AND src = '$leg' AND userfield = 'UNBILLED' AND groupid = $groupid ORDER BY calldate $cdr_order";
			//	print $query;
			//	exit;
		}
		
		astercc::events($query);
		$res = $db->query($query);
		return $res;
	}

function readAll($resellerid, $groupid, $peer, $sdate = null , $edate = null){
	global $db,$config;
	/*
	if ($peer == 'callback'){
		if ($_SESSION['curuser']['usertype'] == 'admin')
			$query = "SELECT * FROM mycdr WHERE LEFT(channel,6) = 'Local/' ";
		else if ( $_SESSION['curuser']['usertype'] == 'reseller' )
			$query = "SELECT * FROM mycdr WHERE resellerid = ".$_SESSION['curuser']['resellerid']." AND LEFT(channel,6) = 'Local/' ";
		else
			$query = "SELECT * FROM mycdr WHERE groupid = ".$_SESSION['curuser']['groupid']." AND LEFT(channel,6) = 'Local/' ";
	}else{
		if ($_SESSION['curuser']['usertype'] == 'admin')
			$query = "SELECT * FROM mycdr WHERE src LIKE '$peer%' ";
		else if ( $_SESSION['curuser']['usertype'] == 'reseller' )
			$query = "SELECT * FROM mycdr WHERE src LIKE '$peer%' AND resellerid = ".$_SESSION['curuser']['resellerid']." ";
		else
			$query = "SELECT * FROM mycdr WHERE src LIKE '$peer%' AND groupid = ".$_SESSION['curuser']['groupid']." ";
	}
	*/ 
	if($config['system']['useHistoryCdr'] == 1){
		$query = "SELECT * FROM mycdr WHERE 1 ";
	}else{
		$query = "SELECT * FROM mycdr WHERE 1 AND userfield = 'UNBILLED' ";
	}

		if ($resellerid != '' and $resellerid != '0'){
			$query .= " AND resellerid = $resellerid ";
		}else{
			$query .= " AND resellerid != -1 ";
		}

		if ($groupid != '' and $groupid != '0'){
			$query .= " AND groupid = $groupid ";
		}else{
			$query .= " AND groupid != -1 ";
		}

		if ($peer != '' and $peer != '0'){
			if ($peer == "-1"){
				$query .= " AND LEFT(channel,6) = 'local/' ";
			}else{
				$query .= " AND (src = '$peer' OR dst = '$peer') ";
			}
		}

		if ($sdate != null){
			$query .= " AND calldate >= '$sdate' ";
		}

	if ($edate != null){
		$query .= " AND calldate <= '$edate' ";
	}

	$query .= " ORDER BY calldate";
	//print $query;exit;
	astercc::events($query);
	$res = $db->query($query);
	return $res;
}
	function readReport($resellerid, $groupid, $booth, $sdate, $edate, $groupby = '',$orderby='',$limit=''){
		global $db,$config;
		$table = 'mycdr';
		if($config['system']['useHistoryCdr'] == 1){
			$table = 'historycdr';
		}

		if ($groupby == ""){
			$query = "SELECT count(*) as recordNum, sum(billsec) as seconds, sum(billsec_leg_a) as billsec_leg_a, sum(credit) as credit, sum(callshopcredit) as callshopcredit, sum(resellercredit) as resellercredit FROM $table WHERE calldate >= '$sdate' AND  calldate <= '$edate' ";
		}else{
			$query = "SELECT count(*) as recordNum, sum(billsec) as seconds, sum(billsec_leg_a) as billsec_leg_a, sum(credit) as credit, sum(callshopcredit) as callshopcredit, sum(resellercredit) as resellercredit, $groupby FROM $table WHERE calldate >= '$sdate' AND  calldate <= '$edate' ";
		}
		
		if (($groupid == '' || $groupid == 0) && ($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator')){
			$groupid = $_SESSION['curuser']['groupid'];
		}

		if ( ($resellerid == '' || $resellerid == 0) && $_SESSION['curuser']['usertype'] == 'reseller' ){
			$resellerid = $_SESSION['curuser']['resellerid'];
		}

		if ($resellerid != 0 && $resellerid != '')
			$query .= " AND resellerid = $resellerid ";
		else
			$query .= " AND resellerid != -1 ";

		if ($groupid != 0 && $groupid != '')
			$query .= " AND groupid = $groupid ";
		else
			$query .= " AND groupid != -1 ";

		if ($booth != 0 && $booth != ''){
			if ($booth == '-1'){
				$query .= " AND LEFT(channel,6) = 'local/' ";
			}else{
				$query .= " AND (src = '$booth' OR dst = '$booth')";
			}
		}		
		#exit;
		if ($groupby != ""){
			$query .= " GROUP BY $groupby";
		}
		if ($orderby != ""){
			$query .= " ORDER BY $orderby desc";
		}
		if ($limit == "limit"){
			$query .= " limit 0,10 ";
		}

		astercc::events($query);
		$res = $db->query($query);
		return $res;
	}

	function readAnsweredNum($resellerid, $groupid, $booth, $sdate, $edate){
		global $db,$config;
		$table = 'mycdr';
		if($config['system']['useHistoryCdr'] == 1){
			$table = 'historycdr';
		}

		$query = "SELECT count(*) as answeredNum FROM $table WHERE (disposition = 'ANSWERED' OR billsec_leg_a >0) AND calldate >= '$sdate' AND  calldate <= '$edate' ";
				
		if ( ($groupid == '' || $groupid == 0) && ($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator')){
			$groupid = $_SESSION['curuser']['groupid'];
		}

		if ( ($resellerid == '' || $resellerid == 0) && $_SESSION['curuser']['usertype'] == 'reseller' ){
			$resellerid = $_SESSION['curuser']['resellerid'];
		}

		if ($resellerid != 0 && $resellerid != '')
			$query .= " AND resellerid = $resellerid ";
		else
			$query .= " AND resellerid != -1 ";

		if ($groupid != 0 && $groupid != '')
			$query .= " AND groupid = $groupid ";
		else
			$query .= " AND groupid != -1 ";

		if ($booth != 0 && $booth != ''){
			if ($booth == '-1'){
				$query .= " AND LEFT(channel,6) = 'local/' ";
			}else{
				$query .= " AND src = '$booth' OR dst = '$booth'";
			}
		}		
		#exit;
		//print $query;exit;
		astercc::events($query);
		$res = $db->getOne($query);
		return $res;
	}

	function readAmount($id,$peer = null, $sdate = null, $edate = null, $field = 'credit'){
		global $db;
		$curYear = Date("Y");
		$curMonth = Date("m");

		if ($sdate == null){
			//$sdate = "$curYear-$curMonth-01 00:00:00";
		}

		if ($edate == null){
			//$edate = "$curYear-$curMonth-31 23:59:59";
		}

		if ($field == 'resellercredit'){
			if ($peer == null)
				$query = "SELECT SUM($field) FROM historycdr WHERE resellerid = $id ";
			else
				$query = "SELECT SUM($field) FROM historycdr WHERE resellerid = $id ";
		}else{
			if ($peer == null)
				$query = "SELECT SUM($field) FROM historycdr WHERE groupid = $id ";
			else
				$query = "SELECT SUM($field) FROM historycdr WHERE groupid = $id ";
		}

		if ($sdate != null)
			$query .= " AND calldate >= '$sdate' ";

		if ($edate != null)
			$query .= " AND calldate <= '$edate' ";
		astercc::events($query);
		$one = $db->getOne($query);
		return $one;
	}


	function readRateDesc($memo,$type=''){
		global $locate;
		if (!is_array($memo)){
			$memo = split("\n",$memo,4);
			if ( $memo[0] != ''){
				foreach ($memo as $val){
					$tmp = split(":",$val);
					$rate[$tmp[0]] = $tmp[1];
				}
			}else{
				return "";
			}
		}else{
			$rate = $memo;
		}
		if ($rate['initblock'] != 0){
			$desc .= floor($rate['connectcharge']*10000)/10000 . ' '.$locate->Translate("for first").' ' . $rate['initblock'] . ' '.$locate->Translate("seconds");
		}
		if($type == ''){
			$desc .= ' <br/>';
		}else{
			$desc .= '&nbsp;&nbsp;';
		}
		if ($rate['billingblock'] != 0){
			$desc .= floor(($rate['billingblock'] * $rate['rateinitial'] / 60)*10000)/10000 . ' '.$locate->Translate("per").' ' . $rate['billingblock'] . ' '.$locate->Translate("seconds");
		}
		return $desc;
	}

	function readField($table,$field,$identity,$value){
		global $db;
		if (is_numeric($value))
			$query = "SELECT $field FROM $table WHERE $identity = $value";
		else
			$query = "SELECT $field FROM $table WHERE $identity = '$value'";
		$one = $db->getOne($query);
		return $one;
	}

	function readRecord($table,$identity,$value){
		global $db;
		if (is_numeric($value))
			$query = "SELECT * FROM $table WHERE $identity = $value";
		else
			$query = "SELECT * FROM $table WHERE $identity = '$value'";
		$row = $db->getRow($query);
		return $row;
	}

	function calculatePrice($billsec,$rate){

		$destination = $rate['destination'];
		$rateinitial = $rate['rateinitial'];
		$initblock	 = $rate['initblock'];
		$billingblock = $rate['billingblock'];
		
		if ($billsec > 0 ){
	
			$price += $rate['connectcharge'];
			$billsec -= $rate['initblock'];
	

			if ($billsec > 0){
				if ($rate['billingblock'] != 0){
					if ($billsec % $rate['billingblock'] != 0 )
						$billblock = intval($billsec / $rate['billingblock']) + 1;
					else
						$billblock = intval($billsec / $rate['billingblock']);
				}else{
				}
					
				$price += $billblock * ($rate['billingblock'] * $rate['rateinitial']/60);
			}
		}

		return $price;
	}

	function calculateLimitSec($creditLimit,$rate){

		$destination = $rate['destination'];
		$rateinitial = $rate['rateinitial'];
		$initblock	 = $rate['initblock'];
		$billingblock = $rate['billingblock'];
		
		if ($billsec > 0 ){
	
			$price += $rate['connectcharge'];
			$billsec -= $rate['initblock'];
	

			if ($billsec > 0){
				if ($rate['billingblock'] != 0){
					if ($billsec % $rate['billingblock'] != 0 )
						$billblock = intval($billsec / $rate['billingblock']) + 1;
					else
						$billblock = intval($billsec / $rate['billingblock']);
				}else{
				}
					
				$price += $billblock * ($rate['billingblock'] * $rate['rateinitial']/60);
			}
		}

		return $price;
	}
	
	function readReportPie($resellerid, $groupid, $booth, $sdate, $edate, $groupby = '',$orderby=''){
		global $db,$config;
		$table = 'mycdr';
		if($config['system']['useHistoryCdr'] == 1){
			$table = 'historycdr';
		}
       if ($resellerid == 0 || $resellerid == ''){
			$query = "SELECT count(*) as recordNum, sum(billsec) as seconds, sum(credit) as credit, sum(callshopcredit) as callshopcredit, sum(resellercredit) as resellercredit, resellerid as gid  FROM $table WHERE calldate >= '$sdate' AND  calldate <= '$edate' ";
		}
		else{
			if ($groupid == 0 || $groupid == ''){
				$query = "SELECT count(*) as recordNum, sum(billsec) as seconds, sum(credit) as credit, sum(callshopcredit) as callshopcredit, sum(resellercredit) as resellercredit, groupid as gid FROM $table WHERE calldate >= '$sdate' AND  calldate <= '$edate' ";
				}else{
				$query = "SELECT count(*) as recordNum, sum(billsec) as seconds, sum(credit) as credit, sum(callshopcredit) as callshopcredit, sum(resellercredit) as resellercredit, src as gid FROM $table WHERE calldate >= '$sdate' AND  calldate <= '$edate' ";	
				}
		}
				
		if ( ($groupid == '' || $groupid == 0) && ($_SESSION['curuser']['usertype'] == 'groupadmin' || $_SESSION['curuser']['usertype'] == 'operator')){
			$groupid = $_SESSION['curuser']['groupid'];
		}

		if ( ($resellerid == '' || $resellerid == 0) && $_SESSION['curuser']['usertype'] == 'reseller' ){
			$resellerid = $_SESSION['curuser']['resellerid'];
		}

		if ($resellerid != 0 && $resellerid != '')
			$query .= " AND resellerid = $resellerid ";
		else
			$query .= " AND resellerid != -1 ";

		if ($groupid != 0 && $groupid != '')
			$query .= " AND groupid = $groupid ";
		else
			$query .= " AND groupid != -1 ";

		if ($booth != 0 && $booth != ''){
			if ($booth == '-1'){
				$query .= " AND LEFT(channel,6) = 'local/' ";
			}else{
				$query .= " AND src = '$booth' OR dst = '$booth'";
			}
		}		
	
		if ($resellerid == 0 || $resellerid == ''){
			$query .= " group by resellerid ";
		}
		else{
			if ($groupid == 0 || $groupid == ''){
			$query .= " group by groupid ";
			}else{
			$query .= " group by src ";	
			}
		}
		
		if ($orderby != ""){
			$query .= " ORDER BY $orderby desc";
		}
		
		astercc::events($query);
		$res = $db->query($query);
		return $res;
	}

	function searchRateForShortUpdate($groupid, $resellerid){
		global $db;

		$sql = "SELECT myrate.id as mid,myrate.dialprefix as mdialprefix,myrate.destination as mdestination,myrate.connectcharge as mconnectcharge,myrate.initblock as minitblock,myrate.rateinitial as mrateinitial,myrate.rateinitial as mrateinitial,myrate.billingblock as mbillingblock,myrate.groupid as mgroupid,myrate.resellerid as mresellerid,callshoprate.id as cid,callshoprate.dialprefix as cdialprefix,callshoprate.connectcharge as cconnectcharge,callshoprate.initblock as cinitblock,callshoprate.rateinitial as crateinitial,callshoprate.rateinitial as crateinitial,callshoprate.billingblock as cbillingblock,callshoprate.groupid as cgroupid,callshoprate.resellerid as cresellerid FROM myrate LEFT JOIN callshoprate ON myrate.dialprefix = callshoprate.dialprefix WHERE callshoprate.dialprefix != '' AND myrate.dialprefix != 'default' ";
		
		astercc::events($sql);
		$rates = & $db->query($sql);
		
		$allprefix = array();
		$ratelist = array();
		while ($rates->fetchInto($list)) {
			
			if(in_array($list['mdialprefix'],$allprefix)){

				if($list['cgroupid'] != $gruopid && $list['cresellerid'] != $resellerid){
					//echo $list['cresellerid'].$groupid;
					continue;
				}else{
					foreach($allprefix as $key => $value){
						if($list['mdialprefix'] == $value){
							$curkey = $key;
							break;
						}
					}
					if($ratelist[$curkey]['cresellerid'] == $resellerid){
						if($ratelist[$curkey]['cgroupid'] == $gruopid){
							continue;
						}
					}
					$ratelist[$curkey] = $list;
					continue;	
				}
			}
			$ratelist[] = $list;
			$allprefix[] = $list['mdialprefix'];
		}
		return $ratelist;
	}
}
?>