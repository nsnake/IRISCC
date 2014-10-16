<?php
	require_once ("checkout.common.php");
	require_once ("db_connect.php");
	require_once ('include/asterevent.class.php');
	require_once ('include/common.class.php');
	include ('openflash/php-ofc-library/open-flash-chart.php');
	global $locate;

	function arr_max($arr)
	{
		if(!is_array($arr))
		exit;
		$tmp=$arr[0];
		for($i=1;$i<count($arr);$i++){
			if($tmp>$arr[$i]){
				$tmp=$tmp;
			}else{
				$tmp=$arr[$i];
			}
		}
		return $tmp;
	}

	function arr_min($arr)
	{
		if(!is_array($arr))
		exit;
		$tmp=$arr[0];
		for($i=1;$i<count($arr);$i++){
			if($tmp<$arr[$i]){
				$tmp=$tmp;
			}else{
				$tmp=$arr[$i];
			}
		}
		return $tmp;
	}

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
	$aFormValues['action']=$arr_action[0];
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

	if ($aFormValues['listType'] == "sumyear"){
		$x_title=$locate->Translate("Sum by year");
		$i=0;
		for ($year = $syear; $year<=$eyear;$year++){
			$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], "$year-1-1 00:00:00","$year-12-31 23:59:59");
			$x_date[]=''.$year. ' '.$locate->Translate("Year");
			if ($res->fetchInto($myreport)){
				$result = parseReport($myreport);
				$ary['recordNum'] = $result['data']['recordNum'];
				$ary['seconds'] = $result['data']['seconds'];
				$ary['credit'] = $result['data']['credit'];
				$ary['callshopcredit'] = $result['data']['callshopcredit'];
				$ary['resellercredit'] = $result['data']['resellercredit'];
				$ary['markup']=$ary['callshopcredit']-$ary['resellercredit'];
				switch ($aFormValues['action']){
					case "num":
						$title= $locate->Translate("Calls");
						$data[$i]=intval($ary['recordNum']);
						break;
					case "time":
						$title= $locate->Translate("Billsec");
						$data[$i]=intval($ary['seconds']);
						break;
					case "total":
						$title= $locate->Translate("Amount");
						$data[$i]=intval($ary['credit']);
						break;
					case "group":
						$title= $locate->Translate("Callshop");
						$data[$i]=intval($ary['callshopcredit']);
						break;
					case "cost":
						$title= $locate->Translate("Reseller Cost");
						$data[$i]=intval($ary['resellercredit']);
						break;
					case "gain":
						$title= $locate->Translate("Markup");
						$data[$i]=intval($ary['markup']);
						break;
				}
			}
			$i++;
		}
	}elseif ($aFormValues['listType'] == "summonth"){
		$x_title=$locate->Translate("Sum by Month");
			$year = $syear;
			for ($month = 1;$month<=12;$month++){
				$x_date[]="".substr($year,-2).','.$month;
				$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], "$year-$month-1 00:00:00","$year-$month-31 23:59:59");
				if ($res->fetchInto($myreport)){
					$result = parseReport($myreport);
					$ary['recordNum'] = $result['data']['recordNum'];
					$ary['seconds'] = $result['data']['seconds'];
					$ary['credit'] = $result['data']['credit'];
					$ary['callshopcredit'] = $result['data']['callshopcredit'];
					$ary['resellercredit'] = $result['data']['resellercredit'];
					$ary['markup']=$ary['callshopcredit']-$ary['resellercredit'];
					switch ($aFormValues['action']){
						case "num":
							$title= $locate->Translate("Calls");
							$data[]=intval($ary['recordNum']);
							break;
						case "time":
							$title= $locate->Translate("Billsec");
							$data[]=intval($ary['seconds']);
							break;
						case "total":
							$title= $locate->Translate("Amount");
							$data[]=intval($ary['credit']);
							break;
						case "group":
							$title= $locate->Translate("Callshop");
							$data[]=intval($ary['callshopcredit']);
							break;
						case "cost":
							$title= $locate->Translate("Reseller Cost");
							$data[]=intval($ary['resellercredit']);
							break;
						case "gain":
							$title= $locate->Translate("Markup");
							$data[]=intval($ary['markup']);
							break;
					}
				}
			}
	}elseif ($aFormValues['listType'] == "sumday"){
		$x_title=$locate->Translate("Sum by Day");
		for ($day = $sday;$day<=31;$day++){
			$x_date[]="".$day;
			$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], "$syear-$smonth-$day 00:00:00","$syear-$smonth-$day 23:59:59");
			if ($res->fetchInto($myreport)){
				$result = parseReport($myreport);
				$ary['recordNum'] = $result['data']['recordNum'];
				$ary['seconds'] = $result['data']['seconds'];
				$ary['credit'] = $result['data']['credit'];
				$ary['callshopcredit'] = $result['data']['callshopcredit'];
				$ary['resellercredit'] = $result['data']['resellercredit'];
				$ary['markup']=$ary['callshopcredit']-$ary['resellercredit'];
				switch ($aFormValues['action']){
					case "num":
						$title= $locate->Translate("Calls");
						$data[]=intval($ary['recordNum']);
						break;
					case "time":
						$title= $locate->Translate("Billsec");
						$data[]=intval($ary['seconds']);
						break;
					case "total":
						$title= $locate->Translate("Amount");
						$data[]=intval($ary['credit']);
						break;
					case "group":
						$title= $locate->Translate("Callshop");
						$data[]=intval($ary['callshopcredit']);
						break;
					case "cost":
						$title= $locate->Translate("Reseller Cost");
						$data[]=intval($ary['resellercredit']);
						break;
					case "gain":
						$title= $locate->Translate("Markup");
						$data[]=intval($ary['markup']);
						break;

				}
			}
		}

	}elseif ($aFormValues['listType'] == "sumhour"){
		$x_title=$locate->Translate("Sum by Hour");
		for ($hour = 0;$hour<=23;$hour++){
			$x_date[]="".$hour;
			$res = astercc::readReport($aFormValues['resellerid'], $aFormValues['groupid'], $aFormValues['sltBooth'], "$syear-$smonth-$sday $hour:00:00","$syear-$smonth-$sday $hour:59:59");
			if ($res->fetchInto($myreport)){
				$result = parseReport($myreport);
				$ary['recordNum'] = $result['data']['recordNum'];
				$ary['seconds'] = $result['data']['seconds'];
				$ary['credit'] = $result['data']['credit'];
				$ary['callshopcredit'] = $result['data']['callshopcredit'];
				$ary['resellercredit'] = $result['data']['resellercredit'];
				$ary['markup']=$ary['callshopcredit']-$ary['resellercredit'];
				switch ($aFormValues['action']){
					case "num":
						$title= $locate->Translate("Calls");
						$data[]=intval($ary['recordNum']);
						break;
					case "time":
						$title= $locate->Translate("Billsec");
						$data[]=intval($ary['seconds']);
						break;
					case "total":
						$title= $locate->Translate("Amount");
						$data[]=intval($ary['credit']);
						break;
					case "group":
						$title= $locate->Translate("Callshop");
						$data[]=intval($ary['callshopcredit']);
						break;
					case "cost":
						$title= $locate->Translate("Reseller Cost");
						$data[]=intval($ary['resellercredit']);
						break;
					case "gain":
						$title= $locate->Translate("Markup");
						$data[]=intval($ary['markup']);
						break;
				}
			}
		}

	}

	$title = new title($title);
	$title->set_style( "{font-size: 20px; color: #A2ACBA; text-align: center;}" );

	$bar = new bar_3d();

	$bar->set_values($data);

	$bar->colour = '#D54C78';

	$x_labels = new x_axis_labels();


	$x_labels->set_labels($x_date);


	$x_axis = new x_axis();
	$x_axis->set_3d(3);
	$x_axis->colour = '#909090';
	$x_axis->set_labels($x_labels);


	$x_legend = new x_legend($x_title);
	$x_legend->set_style( '{font-size: 20px; color: #778877}' );

	$y_axis = new y_axis();
	$max=arr_max($data);
	$mix=arr_min($data);

	$per=round($max/10);
	$max=round($max+$per+$per);
	$temp ='$y_axis->set_range(0,'.$max.','.$per.');';
	eval($temp);
	$chart = new open_flash_chart();
	$chart->set_title( $title );
	$chart->add_element( $bar );
	$chart->set_x_axis( $x_axis );
	$chart->set_x_legend($x_legend);
	$chart->set_y_axis( $y_axis );



	echo $chart->toPrettyString();
	?>
