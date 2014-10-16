	<?php
	require_once ("checkout.common.php");
	require_once ("db_connect.php");
	require_once ('include/asterevent.class.php');
	require_once ('include/common.class.php');
	include ('openflash/php-ofc-library/open-flash-chart.php');
	global $locate;
	$color=array('#1F8FA1','#848484','#ffccff','#CACFBE','#DEF799','#FF33C9','#FF653F','#669900','#ffcc99','#ffccff','#99ccff','#ffcc00');
	
	function parseReport($myreport){
		global $locate;
		$ary['recordNum'] = $myreport['recordNum'];
		$ary['seconds'] = $myreport['seconds'];
		$ary['credit'] = $myreport['credit'];
		$ary['callshopcredit'] = $myreport['callshopcredit'];
		$ary['resellercredit'] = $myreport['resellercredit'];
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
			$ary['markup'] = $myreport['callshopcredit'] - $myreport['resellercredit'];
		}else if ($_SESSION['curuser']['usertype'] == 'groupadmin'){
			$ary['markup'] = $myreport['credit'] - $myreport['callshopcredit'];
		}else if ($_SESSION['curuser']['usertype'] == 'operator'){
	
		}
		$result['data'] = $ary;
		return $result;
	}
	
	$action=$_GET['action'];
	$arr_action=explode("V",$action);
	$action_value=$arr_action[0];
	$aFormValues['action']=$arr_action[0];
	if($aFormValues['action']=="markup")
	$aFormValues['action']="callshopcredit";
	$aFormValues['resellerid']=$arr_action[1];
	$aFormValues['groupid']=$arr_action[2];
	$aFormValues['sltBooth']=$arr_action[3];
	$aFormValues['sdate']=$arr_action[4];
	$aFormValues['edate']=$arr_action[5];
	$aFormValues['listType']=$arr_action[6];
	$aFormValues['hidCurpeer']=$arr_action[7];
	
	if ($aFormValues['sltBooth'] == '' && $aFormValues['hidCurpeer'] != ''){
		$aFormValues['sltBooth'] = $aFormValues['hidCurpeer'];
	}
	list ($syear,$smonth,$sday) = split("[ -]",$aFormValues['sdate']);
	$syear = (int)$syear;
	$smonth = (int)$smonth;
	$sday = (int)$sday;
	list ($eyear,$emonth,$eday) = split("[ -]",$aFormValues['edate']);
	$eyear = (int)$eyear;
	$emonth = (int)$emonth;
	$eday = (int)$eday;
	$color=array('#1F8FA1','#1F8FA1','#848484','#ffccff','#CACFBE','#DEF799','#FF33C9','#FF653F','#669900','#ffcc99','#ffccff','#99ccff','#ffcc00');
	$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], $aFormValues['sdate'],$aFormValues['edate'],'destination',$aFormValues['action'],'limit');
	$ii=1;
	while	($res->fetchInto($row)){
		$row['markup']=$row['callshopcredit'] - $row['resellercredit'];
		$col=$color[$ii];
		if ($_SESSION['curuser']['usertype'] == 'admin' || $_SESSION['curuser']['usertype'] == 'reseller'){
			
			switch ($action_value){
				case "recordNum":
					$title_val=$locate->Translate("Calls");
					$title= "".$row['destination'];
					$pie_value=intval($row['recordNum']);
					break;
				case "seconds":
					$title_val=$locate->Translate("Billsec");
					$title=  "".$row['destination'];
					$pie_value=intval($row['seconds']);
					break;
				case "credit":
					$title_val=$locate->Translate("Amount");
					$title=  "".$row['destination'];
					$pie_value=intval($row['credit']);
					break;
				case "callshopcredit":
					$title_val=$locate->Translate("Callshop");
					$title=  "".$row['destination'];
					$pie_value=intval($row['callshopcredit']);
					break;
				case "resellercredit":
					$title_val=$locate->Translate("Reseller Cost");
					$title=  "".$row['destination'];
					$pie_value=intval($row['resellercredit']);
					break;
				case "markup":
					$title_val=$locate->Translate("Markup");
					$title=  "".$row['destination'];
					$pie_value=intval($row['markup']);
					break;
			}
			$tmp = new pie_value($pie_value,$title);
			$tmp->set_colour($col);
			$d[] = $tmp;
	
		}else if ($_SESSION['curuser']['usertype'] == 'groupadmin'){
			switch ($action_value){
				case "recordNum":
					$title_val=$locate->Translate("Calls");
					$title= "".$row['destination'];
					$pie_value=intval($row['recordNum']);
					break;
				case "seconds":
					$title_val=$locate->Translate("Billsec");
					$title=  "".$row['destination'];
					$pie_value=intval($row['seconds']);
					break;
				case "credit":
					$title_val=$locate->Translate("Amount");
					$title=  "".$row['destination'];
					$pie_value=intval($row['credit']);
					break;
				case "callshopcredit":
					$title_val=$locate->Translate("Callshop");
					$title=  "".$row['destination'];
					$pie_value=intval($row['callshopcredit']);
					break;
				case "markup":
					$title_val=$locate->Translate("Markup");
					$title=  "".$row['destination'];
					$pie_value=intval($row['markup']);
					break;
			}
			$tmp = new pie_value($pie_value,$title);
			$tmp->set_colour($col);
			$d[] = $tmp;
		}else if ($_SESSION['curuser']['usertype'] == 'operator'){
		switch ($action_value){
				case "recordNum":
					$title_val=$locate->Translate("Calls");
					$title= "".$row['destination'];
					$pie_value=intval($row['recordNum']);
					break;
				case "seconds":
					$title_val=$locate->Translate("Billsec");
					$title=  "".$row['destination'];
					$pie_value=intval($row['seconds']);
					break;
				case "credit":
					$title_val=$locate->Translate("Amount");
					$title=  "".$row['destination'];
					$pie_value=intval($row['credit']);
					break;
			}
			$tmp = new pie_value($pie_value,$title);
			$tmp->set_colour($col);
			$d[] = $tmp;
		}
	
		$ii++;
	}
	$pie = new pie();
	//$pie->set_start_angle(5);
	$pie->set_animate( true );
	$pie->set_label_colour( '#432BAF' );
	$pie->set_gradient_fill();
	switch ($action_value){
				case "recordNum":
					$pie->set_tooltip( '#label#<br>#val# (#percent#)' );
					break;
				case "seconds":
					$pie->set_tooltip( '#label#<br>#val# (#percent#)' );
					break;
				default:
				$pie->set_tooltip( '#label#<br>$#val# (#percent#)' );
					break;
			}
	
	$pie->set_colours(array('#1F8FA1','#848484','#CACFBE','#ffcc00','#ffcc99','#ffccff','#99ccff','#DEF799','#FF33C9','#FF653F','#669900','#ffcc00','#ffcc99','#ffccff','#99ccff','#1F8FA1','#848484','#CACFBE','#ffcc00','#ffcc99','#ffccff','#99ccff','#DEF799','#FF33C9','#FF653F','#669900','#ffcc00','#ffcc99','#ffccff','#99ccff') );
	$pie->set_no_labels();
	$pie->set_values($d);
	//$pie->set_
	
	$title = new title($title_val);
	$chart = new open_flash_chart();
	$chart->set_title( $title );
	$chart->add_element($pie);
	
	
	echo $chart->toPrettyString();
	?>
