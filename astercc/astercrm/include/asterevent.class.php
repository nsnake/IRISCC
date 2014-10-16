<?php
/*******************************************************************************
* asterevent.class.php
* asterisk事件处理类
* asterisk events class

* Public Functions List
			
			checkNewCall			检查是否有新的电话
			checkCallStatus			检查通话的状态
			checkExtensionStatus	读取所有分机的状态, 并返回HTML结果
			events					日志记录

* Private Functions List

			listStatus				生成列表格式的分机状态代码
			tableStatus				生成表格世的分机状态代码
			getEvents				从数据库中读取事件
			events					日志记录函数
			getCallerID				用于外部来电时获取主叫号码
			getInfoBySrcID			用于呼出时获取主叫号码
			getInfoByDestID			未使用
			checkLink				检查呼叫是否连接
			checkHangup				检查呼叫是否挂断
			checkIncoming			检查是否有来电
			checkDialout			检查是否有向外的呼叫

* Revision 0.046  2007/1/15 14:45:00  modified by solo
* Desc: changed checkIncoming function, try catch incoming calls by dial event

* Revision 0.0456  2007/11/7 14:45:00  modified by solo
* Desc: add chanspy triger on extension panel 

* Revision 0.0456  2007/11/1 11:54:00  modified by solo
* Desc: add callerid in extension status, when click the callerid, 
* it could show user information if it's stored before

* Revision 0.0456  2007/10/31 10:54:00  modified by solo
* Desc: return channel information when there's dialin or dialout events

* Revision 0.0456  2007/10/31 10:13:00  modified by solo
* Desc: replace DescUniqueID with DestUniqueID 

* Revision 0.0451  2007/10/26 13:46:00  modified by solo
* Desc: 
* 描述: 修改了 listStatus和tableStatus, 增加了点击分机上的列表拨号功能


* Revision 0.045  2007/10/15 10:55:00  modified by solo
* Desc: 
* 描述: 修改了 checkIncoming , checkLink , checkHangup , checkDialout 函数的SQL语句, 修改为仅查询最近10秒的结果

* Revision 0.044  2007/09/12 10:55:00  modified by solo
* Desc: 
* 描述: 修改了 checkIncoming 和 checkDialout 函数的SQL语句


* Revision 0.044  2007/09/12 10:55:00  modified by solo
* Desc: 
* 描述: 修改了 getInfoBySrcID 函数 将数据集的排序顺序改为ASC

* Revision 0.044  2007/09/11 10:55:00  modified by solo
* Desc: fix extension status bug when user switch between user interface and admin interface
* 描述: 修正了分机状态显示的bug(如果用户在管理员界面和用户界面之间切换，分级状态列表会出现问题)

* Revision 0.044  2007/09/11 10:55:00  modified by solo
* Desc: add some comments
* 描述: 增加了一些注释信息

* Revision 0.044  2011/10/26 10:55:00  modified by solo
* Desc: return srcname at line 156,201,269,274. Related to the function are checkNewCall and checkCallStatus
* 描述: 增加了返回 srcname 在行 156,201,269,274 处修改,涉及到的函数有 checkNewCall 和 checkCallStatus


********************************************************************************/

/** \brief asterEvent Class
*

*
* @author	Solo Fu <solo.fu@gmail.com>
* @version	1.0
* @date		13 Auguest 2007
*/

class asterEvent extends PEAR
{

/*
	check if there's a new call for the extension, 
	could be incoming or dial out

	@param	$curid					(int)		only check data after index(curid)
	@param	$exten					(string)	only check data about this extension or channel

	return	$call					(array)	
			$call['status']			(string)	'','incoming','dialout'
			$call['curid']			(int)		current id
			$call['callerid']		(string)	caller id/callee id
			$call['uniqueid']		(string)	uniqueid for the new call
*/

	function checkNewCall($curid,$exten,$channel = '',$agent = ''){
		global $db,$config;
		
		if ($config['system']['eventtype'] == 'curcdr'){

			//$query = "SELECT * FROM curcdr WHERE (src = '$exten' OR dst = '$exten' OR dstchan = 'agent/$agent' OR srcchan LIKE '$channel-%' OR dstchan LIKE '$channel-%' OR srcchan LIKE 'local/".$exten."@%' OR dstchan LIKE 'local/".$exten."@%') AND dstchan != '' AND srcchan != '' AND dst != '' AND src != '' AND src !='<unknown>' AND id > $curid ";

			$query = "SELECT * FROM curcdr WHERE (src = '$exten' OR dst = '$exten' OR srcchan = 'agent/$agent' OR dstchan = 'agent/$agent' OR srcchan LIKE '$channel-%' OR dstchan LIKE '$channel-%') AND dstchan != '' AND srcchan != '' AND id > $curid ";

			//echo $query;exit;

			$res = $db->query($query);
			asterEvent::events($query);
			if ($res->fetchInto($list)) {
				//if dstchan does not include dst, then clear dst(for process transfer call )
				if( !strstr($list['dstchan'],$list['dst']) ) {					
					$dst_tmp = $list['dst'];
					$list['dst'] = '';
				}

				if($list['didnumber'] != '' ){
					$didnumber = $list['didnumber'];
				}else{
					//$sql = "SELECT didnumber FROM curcdr WHERE srcchan = '".$list['didnumber']."' AND didnumber != ''";
					//if($res_did = $db->getone($sql)) $didnumber = $res_did;
				}

				if ((strstr($list['srcchan'],$channel) && !strstr($list['srcchan'],'local')) OR $list['src'] == $exten OR $list['srcchan'] == "agent/".$agent) {// dial out
					//if($list['src'] != $exten){
					//	$query = "update curcdr set src='$exten' WHERE id='".$list['id']."'";
						//$db->query($query);
					//}
					$call['status'] = 'dialout';
					if($list['dst'] == ''){
						$call['callerid'] = $dst_tmp;
					}else{
						$call['callerid'] = trim($list['dst']);
					}
					$call['didnumber'] = $didnumber;
					$call['uniqueid'] = trim($list['srcuid']);
					$call['curid'] = trim($list['id']);
					$call['callerChannel'] = $list['srcchan'];
					$call['calleeChannel'] = $list['dstchan'];
					$call['calldate'] = $list['starttime'];
					$call['queue'] = $list['queue'];
					if($call['uniqueid'] == ''){
						//$call['uniqueid'] = trim($list['dstuid']);
					}
					//检查onhold 通话
					$sql = "SELECT * FROM hold_channel WHERE agentchan='".$list['srcchan']."' ORDER BY id DESC LIMIT 1";
					$holds = $db->getrow($sql);
					$call['hold'] = $holds;
					 
					$call['srcname'] = $list['srcname'];
					
					//print_r($call);exit;
					return $call;
				}elseif ((strstr($list['dstchan'],$channel) && !strstr($list['srcchan'],'local')) OR $list['dst'] == $exten OR $list['dstchan'] == "agent/".$agent ){	//OR strstr($list['dst'],$agent)	//dial in

					//if($list['dst'] != $exten){
						//$query = "update curcdr set dst='$exten' WHERE id='".$list['id']."'";
						//$db->query($query);
					//}
					
					$call['callerChannel'] = $list['srcchan'];
					$call['calleeChannel'] = $list['dstchan'];
					$call['didnumber'] = $didnumber;
					$call['status'] = 'incoming';
					$call['callerid'] = trim($list['src']);
					$call['uniqueid'] = trim($list['srcuid']);
					$call['curid'] = trim($list['id']);
					$call['calldate'] = $list['starttime'];
					$call['queue'] = $list['queue'];
					if(strstr($list['srcchan'],'local/')){
						$query = "SELECT * FROM curcdr WHERE src='".$list['src']."' AND id < '".$list['id']."' ORDER BY id ASC LIMIT 1";
						
						$lega = $db->getrow($query);
						//print_r($lega);exit;
						if($lega['id'] > 0){
							if($lega['dst'] != '' && $lega['dstchan'] != ''){
								$call['callerid'] = trim($lega['dst']);
							}
							if($lega['dstchan'] != ''){
								$call['callerChannel'] = $lega['dstchan'];
							}else{
								$call['callerChannel'] = $lega['srcchan'];
							}

							if($call['didnumber'] == ''){
								$call['didnumber'] = $lega['didnumber'];
							}
						}
					}

					$sql = "SELECT * FROM hold_channel WHERE agentchan='".$list['dstchan']."' ORDER BY id DESC LIMIT 1";
					$holds = $db->getrow($sql);
					$call['hold'] = $holds;

					$call['srcname'] = $list['srcname'];

					return $call;
				}
			}else{
				//检查onhold 通话
				$sql = "SELECT * FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."' ORDER BY id DESC LIMIT 1";
				$holds = $db->getrow($sql);
				$call['hold'] = $holds;
				//$sql = "DELETE FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."'";
				//$db->query($sql);
			}

			$call['status'] = '';
			$call['curid'] = $curid;

			return $call;
		}
		$call =& asterEvent::checkIncoming($curid,$exten);

		if ($call['status'] == 'incoming' && $call['callerid'] != '' ){
			return $call;
		}
	
		$call =& asterEvent::checkDialout($curid,$exten,$call['curid']);

		return $call;
	}

/*
	check call status after it started
	@param	$curid					(int)		only check data after index(curid)
	@param	$uniqueid				(string)	check events which id is $uniqueid
	return	$call					(array)	
			$call['status']			(string)	'','hangup','link'
			$call['curid']			(int)		current id
			$call['callerChannel']	(string)	caller channel (if status is link)
			$call['calleeChannel']	(string)	callee channel (if status is link)
*/
	function checkCallStatus($curid,$uniqueid){
		global $db,$config;
		$exten = $_SESSION['curuser']['extension'];
		$channel = $_SESSION['curuser']['channel'];
		$agent = $_SESSION['curuser']['agent'];
		//echo $curid;exit;

		if ($config['system']['eventtype'] == 'curcdr'){

			//$query = "SELECT * FROM curcdr WHERE (srcuid = '$uniqueid' OR dstuid = '$uniqueid') AND (src = '$exten' OR dst = '$exten' OR dstchan = 'agent/$agent' OR srcchan LIKE '$channel-%' OR dstchan LIKE '$channel-%' OR srcchan LIKE 'local/".$exten."@%' OR dstchan LIKE 'local/".$exten."@%') AND dstchan != '' AND srcchan != '' AND dst != '' AND src != '' ";
			$query = "SELECT * FROM curcdr WHERE (srcuid = '$uniqueid' OR dstuid = '$uniqueid') AND (src = '$exten' OR dst = '$exten' OR srcchan = 'agent/$agent' OR dstchan = 'agent/$agent' OR srcchan LIKE '$channel-%' OR dstchan LIKE '$channel-%' ) AND dstchan != '' AND srcchan != '' AND dst != '' AND src != '' OR id = $curid";
			//echo $query;exit;
			$res = $db->query($query);
			asterEvent::events($query);
			if ($res->fetchInto($list)) {
				//检查onhold 通话
				$sql = "SELECT * FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."' ORDER BY id DESC LIMIT 1";
				$holds = $db->getrow($sql);
				$call['hold'] = $holds;
				
				if (strtolower($list['disposition']) == 'link'){

					$call['callerChannel'] = $list['srcchan'];
					$call['calleeChannel'] = $list['dstchan'];
					$call['consultnum'] = $list['dst'];
					$call['queue'] = $list['queue'];

					$call['status'] = 'link';
					$call['didnumber'] = $list['didnumber'];
					$call['srcname'] = $list['srcname'];
				}else{
					$call['status'] = '';
					$call['queue'] = $list['queue'];
					$call['didnumber'] = $list['didnumber'];
					$call['srcname'] = $list['srcname'];
				}
			}else{
				//检查onhold 通话
				$sql = "SELECT * FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."' ORDER BY id DESC LIMIT 1";
				$holds = $db->getrow($sql);
				$call['hold'] = $holds;
				//$sql = "DELETE FROM hold_channel WHERE accountid='".$_SESSION['curuser']['accountid']."'";
				//$db->query($sql);
				$call['status'] = 'hangup';
			}
			$call['id'] = $curid;
			return $call;
		}

		// check if hangup
		$call =& asterEvent::checkHangup($curid,$uniqueid);

		if ($call['status'] == 'hangup')
			return $call;

		// check if linked
		$call =& asterEvent::checkLink($curid,$uniqueid);
	
		return $call;
	}

/*
	check extension status
	@param	$curid					(int)		only check data after index(curid)
	@param	$type					(string)	list | table
	return	$html					(string)	HTML code for extension status
*/

	function checkExtensionStatus($curid, $type = 'list',$curhover){
		global $db,$config;
		/* 
			if type is list, then only check some specific extension
			or else we get extension list from events
		*/
		$panellist = array();
		$panelphones = array();
		if ($type == 'list'){
			$i = 0;
			foreach($_SESSION['curuser']['extensions'] as $value ){
				$row = astercrm::getRecordByField('username',$value,'astercrm_account');
				$panellist[$row['username']]['extension'] = $row['extension'];
				$panellist[$row['username']]['agent'] = $row['agent'];
				$panellist[$row['username']]['channel'] = $row['channel'];
				$panelphones[] = $row['extension'];
				$i++;
			}
			//$_SESSION['curuser']['extensions_session'] = $panellist;
		}else{
			$alluser = astercrm::getall('astercrm_account');
			while($alluser->fetchinto($row)){
				$panellist[$row['username']]['extension'] = $row['extension'];
				$panellist[$row['username']]['agent'] = $row['agent'];
				$panellist[$row['username']]['channel'] = $row['channel'];
				$panelphones[] = $row['extension'];
			}
		}

		if (!isset($_SESSION['extension_status'])){
			$status = array();
			$callerid = array();
			$direction = array();
		}else{
			/*
			because there could be no all extension status data in events
			we need to inherit status from sessions
			*/
			$status = $_SESSION['extension_status'];
			$callerid = $_SESSION['callerid'];
			$direction = $_SESSION['direction'];
			$srcchan = $_SESSION['srcchan'];
			$dstchan = $_SESSION['dstchan'];
		}

		if (!isset($panelphones) or $panelphones == '') $panelphones = array();

		if($config['system']['eventtype'] == 'curcdr'){
			//read all peer status in table peerstatus and save to array $phone_status
			$events =& asterEvent::getPeerstatus(0);
			$phone_status = array();
			while ($events->fetchInto($list)) {
				list($tech,$peer) = split('/',$list['peername']);
				$phone_status[$peer] = $list['status'];
			}
			foreach ( $panellist as $username => $phone ) {
				$query = "SELECT * FROM curcdr WHERE (src = '".$phone['extension']."' OR dst = '".$phone['extension']."' OR srcchan = 'agent/".$phone['agent']."' OR dstchan = 'agent/".$phone['agent']."' OR srcchan LIKE '".$phone['channel']."-%' OR dstchan LIKE '".$phone['channel']."-%') AND dstchan != '' AND srcchan != '' AND dst != '' AND src != '' ORDER BY id ASC";
				$res = $db->query($query);
				if ($res->fetchInto($cdrrow)) {

					if ($status[$username] == 1) continue;

					//for check click to transfer
					if( !strstr($cdrrow['dstchan'],$cdrrow['dst']) ) {
						$dst_tmp = trim($cdrrow['dst']);
						$cdrrow['dst'] = '';
					}

						if ($status[$list['peer']] == 1) continue;
						if (strstr($cdrrow['src'],$phone['extension']) OR strstr($cdrrow['srcchan'],$phone['channel']) OR $cdrrow['srcchan'] == "agent/".$phone['agent'] ) {	// dial out
							if ( $cdrrow['didnumber'] != '' ) {
								$callerid[$username] = trim($cdrrow['didnumber']);
							}else{
								if ( trim($cdrrow['dst']) != '')
									$callerid[$username] = trim($cdrrow['dst']);
								else
									$callerid[$username] = $dst_tmp;
							}
							$direction[$username] = "dialout";
							$status[$username] = 1;
							$srcchan[$username] = trim($cdrrow['srcchan']);
							$dstchan[$username] = trim($cdrrow['dstchan']);

						}elseif (strstr($cdrrow['dst'],$phone['extension']) OR strstr($cdrrow['dstchan'],$phone['channel']) OR $cdrrow['dstchan'] == "agent/".$phone['agent']) {		//dial in

							$callerid[$username] = trim($cdrrow['src']);
							$direction[$username] = "dialin";
							$status[$username] = 1;
							$srcchan[$username] = trim($cdrrow['srcchan']);
							$dstchan[$username] = trim($cdrrow['dstchan']);

						}else{
							$callerid[$username] = '';
							$direction[$username] = '';
							$status[$username] = 0;
						}
				}else{
					if ($phone_status[$phone['extension']] == 'unknown' || $phone_status[$phone['extension']] == 'unreachable' || $phone_status[$phone['extension']] == '' || $phone_status[$phone['extension']] == 'unregistered') {
						$status[$username] = 2;
					}elseif ($phone_status[$phone['extension']] == 'reachable' || $phone_status[$phone['extension']] == 'registered' || strstr($phone_status[$phone['extension']],'ok')) {
						$status[$username] = 0;
					}
					$callerid[$username] = '';
					$direction[$username] = '';
				}
			}
		}else{
			$events =& asterEvent::getEvents($curid);
			while ($events->fetchInto($list)) {
				$data  = trim($list['event']);
				list($event,$event_val,$ev,$priv,$priv_val,$pv,$chan,$chan_val,$cv,$stat,$stat_val,$sv,$extra) = split(" ", $data, 13);				
	//			if (strtolower(substr($chan_val,0,3)) != "sip" && strtolower(substr($chan_val,0,3)) != "iax") continue;	// also we check iax peer status
				if (strtolower(substr($chan_val,0,3)) != "sip") continue;			
				if (substr($event_val,0,10) == "PeerStatus") {
					if (!in_array($chan_val,$phones)) $phones[] = $chan_val;
					if (substr($stat_val,0,11) == "unreachable")  { $status[$chan_val] = 2; continue; }
					if (substr($stat_val,0,12) == "unregistered") { $status[$chan_val] = 2; continue; }
					if (substr($stat_val,0,9)  == "reachable")    {
						if ($status[$chan_val] == 1) continue;
						$status[$chan_val] = 0;
						continue;
					}
					if (substr($stat_val,0,12) == "registered")   { 
						if ($status[$chan_val] == 1) continue; 
						$status[$chan_val] = 0; 
						continue;
					}
					if (!isset($status[$chan_val])) $status[$chan_val] = 0;
					continue;
				} 

				if (substr($event_val,0,10) == "Newchannel") {
					$peer_val = split("-",$chan_val);
					if (!in_array($peer_val[0],$panelphones)) $panelphones[] = $peer_val[0];
					$status[$peer_val[0]] = 1;
					
					//get unique id
					//add by solo 2007-11-1
					$extra = split("  ", $extra);
					foreach ($extra as $temp){
						if (preg_match("/^Uniqueid:/",$temp)){
							$uniqueid = substr($temp,9);
							$callerid[$peer_val[0]] =& asterEvent::getCallerID($uniqueid);
							$direction[$peer_val[0]] = "dialin";
						}
					}

					if ($callerid[$peer_val[0]] == 0 ){	// it's a dial out
						$srcInfo = & asterEvent::getInfoBySrcID($uniqueid);
						$callerid[$peer_val[0]] = $srcInfo['Extension'];
						$direction[$peer_val[0]] = "dialout";
					}
					//**************************

					continue;
				} 
				if (substr($event_val,0,8) == "Newstate") {
					$peer_val = split("-",$chan_val);
					if (!in_array($peer_val[0],$panelphones)) $panelphones[] = $peer_val[0];
					$status[$peer_val[0]] = 1;
					continue;
				} 
				if (substr($event_val,0,6) == "Hangup") {
					$peer_val = split("-",$chan_val);
					if (!in_array($peer_val[0],$panelphones)) $panelphones[] = $peer_val[0];
					$status[$peer_val[0]] = 0;
					$callerid[$peer_val[0]] = "";
					continue;
			   } 
			} 
		}
		
		if ($type == 'list'){
			if (!isset($_SESSION['curuser']['extensions']) or $_SESSION['curuser']['extensions'] == ''){
				$phones = array();
			}else{
				//$phones = $_SESSION['curuser']['extensions'];
				$phones = $panellist;
			}
			//print_r($phones);print_r($status);print_r($callerid);print_r($direction);exit;
			$action =& asterEvent::listStatus($phones,$status,$callerid,$direction,$srcchan,$dstchan);
		}else{
			//$_SESSION['curuser']['extensions_session'] = $phones;
			$action =& asterEvent::tableStatus($panellist,$status,$callerid,$direction,$srcchan,$dstchan,$curhover);
		}
		$_SESSION['extension_status'] = $status;
		$_SESSION['callerid'] = $callerid;
		$_SESSION['direction'] = $direction;
		$_SESSION['srcchan'] = $srcchan;
		$_SESSION['dstchan'] = $dstchan;
		$html .= $action;
		return $html;
	}
	
	/*
	for now table mode could be used in administror interface
	allow to spy extension
	but no click-to-call

	@param	$phones					(array)		phone list
	@param	$status					(array)		status list for phones
	@param	$callerid				(array)		callerid list for phones
	@param	$direction				(array)		direction list for phones
	return	$html					(string)	HTML code for extension status

	*/
	function &tableStatus($phones,$status,$callerid,$direction,$srcchan = null,$dstchan = null,$curhover=''){
		//print_r($srcchan);exit;
		global $locate;
		$action .= '<table width="100%" cellpadding=2 cellspacing=2 border=0>';
		$action .= '<tr>';
		$i=0;
		foreach ($phones as $username => $row) {
			$exten = $row['extension'];
			if($_SESSION['curuser']['usertype'] == 'groupadmin'){
				if(!in_array($username,$_SESSION['curuser']['memberNames'])) continue;
			}

			if ( (($i %  6) == 0) && ($i != 0) ) $action .= "</tr><tr>";
			$action .= "<td align=center ><br><div id=\"".$username."\" onmouseover=\"document.getElementById('curhover').value=this.id;\" style=\"width:100px;height:25px;\"><div id='div_exten' >";
			if (isset($status[$username])) {
				if ($status[$username] == 2) {
					$action .= "<UL id='extenBtnU'><LI id=\"".$username."\" ><a href='###'>".$username."</a><UL><A href='###'>&nbsp;-<font size='2px'>".$row['extension']."</font>-</A><A href='###' onclick=\"dial('".$exten."','callee');return false;\">&nbsp;-<font size='2px'>".$locate->Translate('Dial')."</font>-</A></UL></LI>";
				}else {
					if ($status[$username] == 1) {
						#print_r($direction);print_r($srcchan);print_r($dstchan);exit;
						if($direction[$username] == 'dialin'){
							$spychan = $dstchan[$username];
						}else{
							$spychan = $srcchan[$username];
						}
						if($username == $curhover){
							$id = 'extenBtnRV';
						}else{
							$id='extenBtnR';
						}						

						$action .= "<UL id='".$id."'><LI id=\"".$username."\" ><a href='###' >".$username."</a><UL><A href='###'>&nbsp;-<font size='2px'>".$row['extension']."</font>-</A><A href='###' onclick=\"xajax_chanspy (".$_SESSION['curuser']['extension'].",'".$spychan."');return false;\">&nbsp;-<font size='2px'>".$locate->Translate('Spy')."</font>-</A><A href='###' onclick=\"xajax_chanspy (".$_SESSION['curuser']['extension'].",'".$spychan."','w');return false;\" >&nbsp;-<font size='2px'>".$locate->Translate('Whisper')."</font>-</A>";
						if($_SESSION['asterisk']['paramdelimiter'] == ','){
							$action .= "<A href='###' onclick=\"xajax_chanspy (".$_SESSION['curuser']['extension'].",'".$spychan."','B');return false;\" >&nbsp;-<font size='2px'>".$locate->Translate('Barge')."</font>-</A>";
						}else{
							$action .= "<A href='###' onclick=\"xajax_barge ('".$srcchan[$username]."','".$dstchan[$username]."');return false;\" >&nbsp;-<font size='2px'>".$locate->Translate('Barge')."</font>-</A>";
						}

						$action .= "<A href='###' onclick=\"hangup ('".$srcchan[$username]."','".$dstchan[$username]."');return false;\" >&nbsp;-<font size='2px'>".$locate->Translate('Hangup')."</font>-</A></UL></LI>";
					}
					else {
						$action .= "<UL id='extenBtnG'><LI id=\"".$username."\" ><a href='###'>".$username."</a><UL><A href='###'>&nbsp;-<font size='2px'>".$row['extension']."</font>-</A><A href='###' onclick=\"dial('".$exten."','callee');return false;\">&nbsp;-<font size='2px'>".$locate->Translate('Dial')."</font>-</A></UL></LI>";
					}
				}
			}
			else {
				$action .= "<UL id='extenBtnB'><LI id=\"".$username."\" ><a href='###'>".$username."</a><UL><A href='###'>&nbsp;-<font size='2px'>".$row['extension']."</font>-</A><A href='###' onclick=\"dial('".$exten."','callee');return false;\">&nbsp;-<font size='2px'>".$locate->Translate('Dial')."</font>-</A></UL></LI>";
			}
			$action .= "</UL></div></div>";

			if ($status[$username] == 1) {
				//$action .= "<span align=left>";
				if($id != 'extenBtnRV'){
					$action .= $direction[$username];
					$action .= "<BR>".$callerid[$username]."";
				}
				//$action .= "</span>";
			}
			$action .=  "</td>\n";
			$i++;
		}
		$action .= '</tr></table><br>';
		return $action;
	}

	/*
	for now this mode could be used in extension panel in agent interface
	allow to spy extension (when busy) and click-to-call (when idle)
	*/

	function &listStatus($phones,$status,$callerid,$direction,$srcchan,$dstchan){
		global $locate;
		$action .= '<table width="100%" cellpadding=2 cellspacing=2 border=0>';
		foreach ($phones as $username => $row) {
			//print_r($username);exit;
			$action .= "<tr><td align=center><div id='div_exten'>";
	
			if (isset($status[$username])) {
				if ($status[$username] == 2) {
					$action .= "<UL id='extenBtnU'><LI><a href='###'>".$username."</a><UL><A href='###'>&nbsp;-<font size='2px'>".$row['extension']."</font>-</A><A href='###' onclick=\"dial('".$row['extension']."','');return false;\">&nbsp;-<font size='2px'>".$locate->Translate('Dial')."</font>-</A><A href='###' onclick=\"bargeInvite ('".$row['extension']."');return false;\" >&nbsp;-<font size='2px'>".$locate->Translate('Invite')."</font>-</A></UL></LI>";					
				}else {
					if ($status[$username] == 1) {
						if($direction[$username] == 'dialin'){
							$spychan = $dstchan[$username];
						}else{
							$spychan = $srcchan[$username];
						}
						$action .= "<UL id='extenBtnR'><LI><a href='###' >".$username."</a><UL><A href='###'>&nbsp;-<font size='2px'>".$row['extension']."</font>-</A>";
						if($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'groupadmin')
						$action .= "<A href='###' onclick=\"xajax_chanspy (".$_SESSION['curuser']['extension'].",'".$spychan."');return false;\">&nbsp;-<font size='2px'>".$locate->Translate('Spy')."</font>-</A>";
						$action .= "<A href='###' onclick=\"xajax_chanspy (".$_SESSION['curuser']['extension'].",'".$spychan."','w');return false;\" >&nbsp;-<font size='2px'>".$locate->Translate('Whisper')."</font>-</A></UL></LI>";					
					}else {
						$action .= "<UL id='extenBtnG'><LI><a href='###' >".$username."</a><UL><A href='###'>&nbsp;-<font size='2px'>".$row['extension']."</font>-</A><A href='###' onclick=\"dial('".$row['extension']."','');return false;\">&nbsp;-<font size='2px'>".$locate->Translate('Dial')."</font>-</A><A href='###' onclick=\"bargeInvite ('".$row['extension']."');return false;\" >&nbsp;-<font size='2px'>".$locate->Translate('Invite')."</font>-</A></UL></LI>";
					}
				}
			}else {
				$action .= "<UL id='extenBtnB'>
												<LI><a href='###' >".$username."</a>
													<UL><A href='###'>&nbsp;-<font size='2px'>".$row['extension']."</font>-</A>
														<A href='###' onclick=\"dial('".$row['extension']."','');return false;\">&nbsp;-<font size='2px'>".$locate->Translate('Dial')."</font>-</A><A href='###' onclick=\"bargeInvite ('".$row['extension']."');return false;\" >&nbsp;-<font size='2px'>".$locate->Translate('Invite')."</font>-</A>
													</UL>
												</LI>";
			}

			$action .= "</UL></div>";

			if ($status[$username] == 1) {
				//$action .= "<span align=left>";
				$action .= $direction[$username];
				$action .= "<BR><a href=? onclick=\"xajax_getContact('".$callerid[$username]."');return false;\">".$callerid[$username]."</a>";
				//$action .= "</span>";
			}

			$action .=  "</td></tr>\n";
		 }

		 $action .= '</table>';
		 		// echo $action; exit;
		return $action;
	}

/*
	get events from database
	@param	$curid					(int)		only check data after index(curid)
	return	$res					(array)	
*/

	function &getEvents($curid){
		global $db,$config;
		$query = "SELECT * FROM events WHERE id > $curid order by id";
		
		asterEvent::events($query);
		$res = $db->query($query);
		//$db->disconnect();
		return $res;
	}

	function &getPeerstatus($curid){
		global $db,$config;
//		$phone_str = '';
//		foreach($phones as $value){
//			$phone_str .= " OR peer = 'SIP/".trim($value)."' ";
//		}
//
//		if($phone_str != '')
//			$query = "SELECT * FROM peerstatus WHERE ".ltrim($phone_str," OR");
//		else
//			$query = "SELECT * FROM peerstatus WHERE id = '0'";

		$query = "SELECT * FROM peerstatus ";

		asterEvent::events($query);
		$res = $db->query($query);
		//$db->disconnect();
		return $res;
	}

/*
	check if a call linked
	@param	$curid					(int)		only check data after index(curid)
	@param	$uniqueid				(string)	uniqueid for the current call
	return	$call					(array)	
			$call['status']			(string)	'', 'link'
			$call['curid']			(int)		current id
			$call['callerChannel']	(string)	caller channel
			$call['calleeChannel']	(string)	callee channel
*/

	function &checkLink($curid,$uniqueid){
		global $db;
		// SELECT "1997-12-31 23:59:59" + INTERVAL 1 SECOND; 

//		$query = "SELECT * FROM events WHERE event LIKE 'Event: Link%' AND event LIKE '%" . $uniqueid. "%' AND id > $curid AND timestamp > (now()-INTERVAL 10 SECOND) order by id desc ";

		$query = "SELECT * FROM events WHERE event LIKE 'Event: Link%' AND event LIKE '%" . $uniqueid. "%' AND id > $curid AND timestamp >  '".date ("Y-m-d H:i:s" ,time()-10)."' order by id desc ";

		//asterEvent::events($query);
		$res = $db->query($query);
		
		if ($res->fetchInto($list)) {
			$flds	= split("  ",$list['event']);
//			print_r($flds);
//			exit;
			$call['callerChannel'] = trim(substr($flds[2],9));
			$call['callerChannel'] = split(",",$call['callerChannel']);
			$call['callerChannel'] = $call['callerChannel'][0];

			$call['calleeChannel'] = trim(substr($flds[3],9));

			$call['status'] = 'link';
			$call['curid'] = $list['id'];

			//检查是否是local事件
			//如果是local, 返回状态为空闲
			//if (strstr($call['callerChannel'],'Local')){
			//	//print_r($call['callerChannel']);
			//	$call['status'] = '';
			//}

		} else
			$call['status'] = '';

		return $call;
	}

/*
	check if a call hangup
	@param	$curid					(int)		only check data after index(curid)
	@param	$uniqueid				(string)	uniqueid for the current call
	return	$call					(array)	
			$call['status']			(string)	'','hangup'
			$call['curid']			(int)		current id
*/

	function &checkHangup($curid,$uniqueid){
		global $db;
		//$query = "SELECT * FROM events WHERE event LIKE '%Hangup%' AND event LIKE '%" . $uniqueid . "%' AND timestamp > (now()-INTERVAL 10 SECOND) AND id> $curid order by id desc ";

		$query = "SELECT * FROM events WHERE event LIKE '%Hangup%' AND event LIKE '%" . $uniqueid . "%' AND timestamp > '".date ("Y-m-d H:i:s" ,time()-10)."' AND id> $curid order by id desc ";

		//asterEvent::events($query);
		$res = $db->query($query);
//		print $res->numRows();
//		print "ok";
//		exit;
		if ($res->fetchInto($list)) {
			$flds	= split("  ",$list['event']);
			$call['status'] = 'hangup';
			$call['curid'] = $list['id'];
		} else
			$call['status'] = '';

		return $call;
	}

/*
	check if there's a new incoming call
	@param	$curid					(int)		only check data after index(curid)
	@param	$exten					(string)	only check data about extension
	return	$call					(array)	
			$call['status']			(string)	'','incoming'
			$call['curid']			(int)		current id
			$call['callerid']		(string)	caller id/callee id
			$call['uniqueid']		(string)	uniqueid for the new call
			$call['callerChannel']		(string)	channel who start the call
*/

	function &checkIncoming($curid,$exten){
		global $db;

		//$pasttime = date ("Y-m-d H:i:s" ,time() - 10);
		$query = "SELECT id FROM events ORDER BY timestamp desc limit 0,1";
		//asterEvent::events($query);
		$maxid = $db->getOne($query);
		if (!$maxid){
			$call['curid'] = 0;
			return $call;
		}

		//$query = "SELECT * FROM events WHERE (event LIKE 'Event: Newchannel % Channel: %".$exten."% % State: Ringing%' ) AND timestamp > '".date ("Y-m-d H:i:s" ,time() - 10)."' AND id > " . $curid . "  AND id < ".$maxid." order by id desc limit 0,1";

		$query = "SELECT * FROM events WHERE event LIKE 'Event: Dial% Destination: %".$exten."%' AND id > " . $curid . " AND id <= ".$maxid." AND timestamp > '".date ("Y-m-d H:i:s" ,time() - 10)."' ORDER BY id desc limit 0,1";	
		asterEvent::events($query);


//		$query = "SELECT * FROM events WHERE (event LIKE 'Event: New% % Channel: %".$exten."% % State: Ring%' ) AND id > " . $curid . " AND id <= ".$maxid." order by id desc limit 0,1";

//		asterEvent::events($query);
		$res = $db->query($query);
//		$list = $db->getRow($query);
//		asterEvent::events("incoming:".$res->numRows());

		if ($res->fetchInto($list)) {
			$id        = $list['id'];
			$timestamp = $list['timestamp'];
			$event     = $list['event'];
			$flds      = split("  ",$event);
			$c         = count($flds);
			$callerid  = '';
			$transferid= '';

			//if ($flds[3] == 'State: Ringing'){
				//for($i=0;$i<$c;++$i) {
					//if (strstr($flds[$i],"Channel:"))	
					//	$channel = substr($flds[$i],8);

					//if (strstr($flds[$i],"CallerID:"))	
					//	$callerid = substr($flds[$i],9);

					//if (strstr($flds[$i],"Uniqueid:")){	
					//		$uniqueid = substr($flds[$i],9);
					//		$callerid =& asterEvent::getCallerID($uniqueid);
				//	}
			//	}
			//}
			
			//if ($callerid == '')	//	if $callerid is null, the call should be transfered
			//	$callerid = $transferid;
			$SrcChannel = trim(substr($flds[2],7));			//add by solo 2007/10/31
			$DestChannel = trim(substr($flds[3],12));		//add by solo 2007/10/31

			$call['callerChannel'] = $SrcChannel;
			$call['calleeChannel'] = $DestChannel;
			$SrcUniqueID = trim(substr($flds[6],12));
			$DestUniqueID = trim(substr($flds[7],13));
			$callerid = trim(substr($flds[4],9));

			if (preg_match_all("/^local\/(.*)\@/",$SrcChannel,$match) && $callerid == $_SESSION['curuser']['extension'])
				$callerid = trim($match[1][0]);
			
			asterEvent::events("incoming from:".$callerid);


			if ($id > $curid) 
				$curid = $id;

			$call['status'] = 'incoming';
			$call['callerid'] = trim($callerid);
			$call['uniqueid'] = trim($SrcUniqueID);
			$call['curid'] = trim($curid);
		} else{
			$call['status'] = '';
			$call['curid'] = $maxid;
		}

		return $call;
	}

/*
	check if there's a new dial out
	@param	$curid					(int)		only check data after index(curid)
	@param	$exten					(string)	only check data about extension
	return	$call					(array)	
			$call['status']			(string)	'','incoming'
			$call['curid']			(int)		current id
			$call['callerid']		(string)	caller id/callee id
			$call['uniqueid']		(string)	uniqueid for the new call
			$call['callerChannel']	(string)	source channel
			$call['calleeChannel']	(string)	destination channel
*/

	function &checkDialout($curid,$exten,$maxid){
		global $db;
//		$query = "SELECT * FROM events WHERE event LIKE 'Event: Dial% Source: %".$exten."%' AND id > " . $curid . " AND id < ".$maxid." AND timestamp > '".date ("Y-m-d H:i:s" ,time()-10)."' order by id desc limit 0,1";	

		$query = "SELECT * FROM events WHERE event LIKE 'Event: Dial% Source: %".$exten."%' AND id > " . $curid . " AND id <= ".$maxid." AND timestamp > '".date ("Y-m-d H:i:s" ,time() - 10)."' ORDER BY id desc limit 0,1";	
		asterEvent::events($query);

		$res = $db->query($query);
//		asterEvent::events("dialout:".$res->numRows());
//		print $query;
//		exit;
//		print_r($res);
		if ($res->fetchInto($list)) {
			$id        = $list['id'];
			$timestamp = $list['timestamp'];
			$event     = $list['event'];
			$flds      = split("  ",$event);
			$callerid  = '';

/*
Event: Dial  Privilege: call,all
Source: Local/13909846473@from-sipuser-47d9,2  
Destination: SIP/trunk1-ec3f  
CallerID: 8000  
CallerIDName: <unknown>  
SrcUniqueID: 1193886661.15682  
DestUniqueID: 1193886661.15683
*/
			$SrcUniqueID = trim(substr($flds[6],12));
			$DestUniqueID = trim(substr($flds[7],13));
			$SrcChannel = trim(substr($flds[2],7));			//add by solo 2007/10/31
			$DestChannel = trim(substr($flds[3],12));		//add by solo 2007/10/31

			$srcInfo = & asterEvent::getInfoBySrcID($SrcUniqueID);
			$callerid = $srcInfo['Extension'];
			asterEvent::events("dialout: ".$event);

			if (preg_match_all("/^local\/(.*)\@/",$SrcChannel,$match))
				$callerid = trim($match[1][0]);


			if ($id > $curid) 
				$curid = $id;

			$call['status'] = 'dialout';
			$call['callerid'] = trim($callerid);
			$call['uniqueid'] = $SrcUniqueID;
			$call['curid'] = trim($curid);

			//add by solo 2007/10/31
			//******************
			$call['callerChannel'] = $SrcChannel;
			$call['calleeChannel'] = $DestChannel;
			//******************

		} else{
			$call['status'] = '';
			$call['curid'] = $maxid;
		}

		return $call;
	}

/*
	get more information from events table by DestUniqueID
	@param	$DestUniqueID			(string)	DestUniqueID field in manager event
	return	$call					(array)	
			$call['status']			(string)	'','found'
			$call['Extension']		(string)	extension which unique id is $DestUniqueID
			$call['Channel']		(string)	channel which unique id is $DestUniqueID
*/

	function &getInfoByDestID($DestUniqueID){
		global $db;
		$DestUniqueID = trim($DestUniqueID);
		$query  = "SELECT * FROM events WHERE event LIKE '%Uniqueid: $DestUniqueID%' AND event LIKE 'Event: Newcallerid%' ORDER BY id DESC";
		//asterEvent::events($query);
		$res = $db->query($query);
		if ($res->fetchInto($list)){
			$event = $list['event'];
			$flds = split("  ",$event);

			foreach ($flds as $myFld) {
				if (strstr($myFld,"CallerID:")){	
					$call['Extension'] = substr($myFld,9);
				} 
				if (strstr($myFld,"Channel:")){	
					$call['Channel'] = substr($myFld,8);
				} 

			}
			$call['status'] = 'found';
		} else
			$call['status'] = '';

		return $call;
	}

/*
	get more information from events table by SrcUniqueID
	@param	$SrcUniqueID			(string)	SrcUniqueID field in manager event
	return	$call					(array)	
			$call['status']			(string)	'','found'
			$call['Extension']		(string)	extension which unique id is $SrcUniqueID
			$call['Channel']		(string)	channel which unique id is $SrcUniqueID
*/

	function &getInfoBySrcID($SrcUniqueID){
		global $db;
		$SrcUniqueID = trim($SrcUniqueID);
		$query  = "SELECT * FROM events WHERE event LIKE '%Uniqueid: $SrcUniqueID%' AND event LIKE 'Event: Newexten%' ORDER BY id ASC";
		//asterEvent::events($query);
		$res = $db->query($query);
		if ($res->fetchInto($list)){
			$event = $list['event'];
			$flds = split("  ",$event);

			foreach ($flds as $myFld) {
				if (strstr($myFld,"Extension:")){	
					$call['Extension'] = substr($myFld,10);
				} 
				if (strstr($myFld,"Channel:")){	
					$call['Channel'] = substr($myFld,8);
				} 

			}
			$call['status'] = 'found';
		} else
			$call['status'] = '';

		return $call;
	}

/*
	get callerid for incoming calls
	@param	$uniqueid				(string)	
	return	$callerid				(string)	
*/

	function &getCallerID($uniqueid){
		global $db;
		$uniqueid = trim($uniqueid);
		$query  = "SELECT * FROM events WHERE event LIKE '%DestUniqueID: $uniqueid%' ORDER BY id DESC";
		$res = $db->query($query);

		if ($res->fetchInto($list)){
			$event = $list['event'];
			$flds = split("  ",$event);

			foreach ($flds as $myFld) {
				if (strstr($myFld,"CallerID:")){	
					return substr($myFld,9);
				} 
			}
		}

		return 0;
	}

/*
	for log
	@param	$events					(string)	things want to be logged
	return	null								nothing to be returned
*/
	function events($event = null){
		//if(LOG_ENABLED){
			$now = date("Y-M-d H:i:s");
   		
			$fd = fopen ("/tmp/asterEvent.log",'a');
			$log = $now." ".$_SERVER["REMOTE_ADDR"] ." - $event \n";
	   		fwrite($fd,$log);
   			fclose($fd);
		//}
	}

}
?>