<?php
//header('Content-Type: text/html; charset=utf-8');
//header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
//header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
//header('Cache-Control: post-check=0, pre-check=0',false);
//header('Pragma: no-cache');
session_cache_limiter('public, no-store');

session_set_cookie_params(0);
if (!session_id()) session_start();
setcookie('PHPSESSID', session_id());


if ($_SESSION['curuser']['usertype'] != 'admin' &&$_SESSION['curuser']['usertype'] != 'groupadmin'  && !is_array($_SESSION['curuser']['privileges'])) 
	header("Location: portal.php");

require_once ("db_connect.php");
require_once ('include/astercrm.class.php');
require_once ('include/localization.class.php');
require_once ('include/PHPExcel.php');
require_once ('include/PHPExcel/IOFactory.php');
$sql = $_REQUEST['hidSql'];
$table = $_REQUEST['maintable'];
$type = $_REQUEST['exporttype'];
$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],$table);


if ($sql == '' && $table != 'report') exit;

if($type == 'exportcsv'){
	$filename = 'astercrm.csv';
}else{
	$filename = 'astercrm.xls';
}

if ($_SESSION['curuser']['usertype'] != 'admin' && $table != 'report' && $table != 'ticket_details' &&  $table != 'diallist_dup' && $table != 'clientinformation'){
	if (strpos(strtolower($sql),'where')){
		if($table != ''){ //判断是否传了主表名
			$sql .= " and $table.groupid = ".$_SESSION['curuser']['groupid'];
			$filename = $table;
		}else{
			$sql .= " and groupid = ".$_SESSION['curuser']['groupid'];
		}
	}else{
		if($table != ''){//判断是否传了主表名
			$sql .= " where $table.groupid = ".$_SESSION['curuser']['groupid'];
			$filename = $table;
		}else{
			$sql .= " where groupid = ".$_SESSION['curuser']['groupid'];
		}
	}
}

if($table != ''){ //判断是否传了主表名
	if($type == 'exportcsv'){
		$filename = $table.'.csv';
	}else{
		$filename = $table.'.xls';
	}
}
if($table != 'report') $res = $db->query($sql);
global $locate;

if($table == 'report'){
	$groupid = 0;
	$accountid = 0;
	if(!empty($_REQUEST['groupid'])) $groupid = $_REQUEST['groupid'];
	if(!empty($_REQUEST['accountid'])) $accountid = $_REQUEST['accountid'];
	$rows = astercrm::readReportAgent($groupid,$accountid,$_REQUEST['sdate'],$_REQUEST['edate']);
	$data = array();
	if($rows['type'] == 'grouplist'){
		foreach($rows as $key => $row){
			$data_tmp = array();
			if($key != 'type'){
				$hour = intval($row['seconds'] / 3600);
				if($hour < 3) $data_tmp['color'] = 'FF0000';
				$minute = intval($row['seconds'] % 3600 / 60);
				$sec = intval($row['seconds'] % 60);
				$asr = round($row['arecordNum']/$row['recordNum'] * 100,2);
				$acd = round($row['seconds']/$row['arecordNum'],2);
				$acdminute = intval($acd / 60);
				$acdsec = intval($acd % 60);
				$data_tmp[$locate->Translate("groupname")] = $row['groupname'];
				$data_tmp[$locate->Translate("total calls")] = $row['recordNum'];
				$data_tmp[$locate->Translate("answered calls")] = $row['arecordNum'];
				$data_tmp[$locate->Translate("answered duration")] =$hour.$locate->Translate("hour").$minute.$locate->Translate("minute").$sec.$locate->Translate("sec");
				$data_tmp[$locate->Translate("ASR")] = $asr.'%';
				$data_tmp[$locate->Translate("ACD")] = $acdminute.$locate->Translate("minute").$acdsec.$locate->Translate("sec");
				array_push($data,$data_tmp);
			}
		}
	}else{
				$group = astercrm::getRecordByID($groupid,"astercrm_accountgroup");
				foreach($rows as $key => $row){
					if($key != 'type'){
						$hour = intval($row['seconds'] / 3600);
						if($hour < 3 ) $data_tmp['color'] = 'FF0000';
						$minute = intval($row['seconds'] % 3600 / 60);
						$sec = intval($row['seconds'] % 60);
						$asr = round($row['arecordNum']/$row['recordNum'] * 100,2);
						$acd = round($row['seconds']/$row['arecordNum'],2);
						$acdminute = intval($acd / 60);
						$acdsec = intval($acd % 60);

						$data_tmp[$locate->Translate("groupname")] = $group['groupname'];
						$data_tmp[$locate->Translate("username")] = $row['username'];
						$data_tmp[$locate->Translate("name")] = $row['name'];
						$data_tmp[$locate->Translate("total calls")] = $row['recordNum'];
						$data_tmp[$locate->Translate("answered calls")] = $row['arecordNum'];
						$data_tmp[$locate->Translate("answered duration")] =$hour.$locate->Translate("hour").$minute.$locate->Translate("minute").$sec.$locate->Translate("sec");
						$data_tmp[$locate->Translate("ASR")] = $asr.'%';
						$data_tmp[$locate->Translate("ACD")] = $acdminute.$locate->Translate("minute").$acdsec.$locate->Translate("sec");
						array_push($data,$data_tmp);
					}
				}
				
	}
}else{
	while( $res->fetchinto($row) ) {
		//print_r($row);exit;
		$data[] = str_replace("\n","",$row);
	}
}
//print_r($data);exit;
if($type != 'exportcsv'){
	$objPHPExcel = new PHPExcel();

			$objPHPExcel->setActiveSheetIndex(0);
			$hfer = '&C&HExported from My DB Unclassified/FOUO';
			$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddHeader($hfer);
			$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter($hfer);
			$i = 1;
			$colA = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
			$colorcell = '';
			foreach($data as $row){
				$m = 0;
				$n = 0;
				$s = 0; //是否处于单列号状态
				foreach($row as $k => $v){
					if($k == 'color') continue;
					if($m < 26 && $s == 0){
						$cols = $colA[$m];
						$m++;
					}else{
						
						$s = 1;
						if($m%26 == 0 && $m != 0) $m = 0;					
						
						if($n%26 == 0 && $n!=0){
							$n = 0;					
						}
						$cols = $colA[$m].$colA[$n];

						if($n == 25) $m++;
						$n++;
					}

					if($i == 1){
						$objPHPExcel->getActiveSheet()->setCellValue($cols.$i,$k);
						if(is_numeric($v)) $isnumber[$cols] = 1;
						$objPHPExcel->getActiveSheet()->setCellValue($cols.($i+1),$v);
						if(!empty($row['color']) && $k == $locate->Translate("answered duration") ){
							$colorcell = $cols.($i+1).":".$cols.($i+1);
							$BackgroundColor = new PHPExcel_Style_Color();
							$BackgroundColor->setRGB($row['color']);				
									
							$objPHPExcel->getActiveSheet()->getStyle($colorcell)->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID );
							$objPHPExcel->getActiveSheet()->getStyle($colorcell)->getFill()->setStartColor($BackgroundColor);
						}
					}else{
						//$objPHPExcel->getActiveSheet()->mergeCells(’A18:E22′);合并单元格方法
						//给单元格赋背景色
						if(!empty($row['color']) && $k == $locate->Translate("answered duration") ){
							$colorcell = $cols.($i+1).":".$cols.($i+1);
							$BackgroundColor = new PHPExcel_Style_Color();
							$BackgroundColor->setRGB($row['color']);				
									
							$objPHPExcel->getActiveSheet()->getStyle($colorcell)->getFill()->setFillType( PHPExcel_Style_Fill::FILL_SOLID );
							$objPHPExcel->getActiveSheet()->getStyle($colorcell)->getFill()->setStartColor($BackgroundColor);
						}
						if(is_numeric($v)) $isnumber[$cols] = 1;
						$objPHPExcel->getActiveSheet()->setCellValue($cols.($i+1),$v);
						
					}							
				}
				$i++;
			}
			foreach($isnumber AS $key => $value ){
				$objPHPExcel->getActiveSheet()->getColumnDimension($key)->setAutoSize(true);
			}

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

	header("charset=uft-8");   
	header('Content-type:  application/force-download');
	header('Content-Transfer-Encoding:  Binary');
	header('Content-disposition:  attachment; filename='.$filename);
	//echo astercrm::exportDataToCSV($sql,$table);

	//unset($_SESSION['export_sql']);
	$objWriter->save('php://output');
}else{
	ob_start();
	header("charset=uft-8");   
	header('Content-type:  application/force-download');
	header('Content-Transfer-Encoding:  Binary');
	header('Content-disposition:  attachment; filename='.$filename);
	#echo astercrm::exportDataToCSV($sql,$table);
	$first = 'yes';
	foreach ($data as $row) {
		$first_line = '';
		foreach ($row as $key => $val){
			if($first == 'yes'){
				if($table = 'surveyresult'){
					//if()
				}
				$first_line .= '"'.$key.'"'.',';
			}
			//if ($val != mb_convert_encoding($val,"UTF-8","UTF-8"))
			//		$val='"'.mb_convert_encoding($val,"UTF-8","GB2312").'"';
			#$txtstr .= "\t".str_replace(",","\",\"",$val)."\t".',';
			#$txtstr .= '"'.$val.'"'.',';
			$txtstr .= '"'.str_replace('"','""',$val).'",';
		}
		if($first_line != ''){
			$first_line .= "\n";
			$txtstr = $first_line.$txtstr;
			$first = 'no';
		}			
		$txtstr .= "\n";
	}
	echo $txtstr;
	ob_end_flush();
}
?>