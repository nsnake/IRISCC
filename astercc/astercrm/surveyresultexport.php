<?php
header('Content-Type: text/html; charset=utf-8');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0',false);
header('Pragma: no-cache');
session_cache_limiter('public, no-store');

session_set_cookie_params(0);
if (!session_id()) session_start();
setcookie('PHPSESSID', session_id());


if ($_SESSION['curuser']['usertype'] != 'admin' &&$_SESSION['curuser']['usertype'] != 'groupadmin' && !is_array($_SESSION['curuser']['privileges']['surveyresult'])) 
	header("Location: portal.php");

require_once ("db_connect.php");
require_once ('include/astercrm.class.php');
$sql = trim(strtolower($_REQUEST['hidSql']));
$table = trim(strtolower($_REQUEST['maintable']));

//echo $sql.$table;exit;
$sql = " 1 $sql ";

if ($_SESSION['curuser']['usertype']  != "admin" ){
	if($table != '')//判断是否传了主表名
		$sql .= " and $table.groupid = ".$_SESSION['curuser']['groupid'];
	else
		$sql .= " and groupid = ".$_SESSION['curuser']['groupid'];
}

#error_reporting(E_ALL);
error_reporting($_SESSION['error_report']);
global $db;
ob_start();
header("charset=uft-8");   
header('Content-type:  application/force-download');
header('Content-Transfer-Encoding:  Binary');
header('Content-disposition:  attachment; filename=astercrm.csv');


$commonHeader = '"CustomerName","Address","Zipcode","City","State","Country","Phone"';

$commonQuery = "SELECT customer.customer AS CustomerName,customer.Address ,customer.Zipcode ,customer.City ,customer.State ,customer.Country ,customer.Phone , surveyresult.* FROM surveyresult LEFT JOIN customer ON customer.id = surveyresult.customerid LEFT JOIN contact ON contact.id = surveyresult.contactid LEFT JOIN survey ON survey.id = surveyresult.surveyid LEFT JOIN campaign ON campaign.id = surveyresult.campaignid";



// 首先查看该条件下包含几个survey
$query = "$commonQuery WHERE $sql GROUP BY surveyid ORDER BY surveyid ASC, id ASC";
$res = $db->query($query);

while($res->fetchinto($row)){
	$surveyid = $row['surveyid'];
	//echo "surveyid = $surveyid <BR>";
	// generate header
	$header = $commonHeader;
	$aryHeader = array();
	$query = "SELECT * FROM surveyoptions WHERE  surveyid='$surveyid' ORDER BY optiontype ASC";
	$surveyoptions_res = $db->query($query);
	$i = 0;
	while($surveyoptions_res->fetchinto($row)){
		$header .= ',"'.$row['surveyoption'].'"';
		$i++;
		$aryHeader[$i] = $row['surveyoption'];
	}
#	echo "<br>";
	echo "$header\n";
#	echo "<br>";
	$query = "$commonQuery  WHERE $sql AND surveyresult.surveyid = '$surveyid' GROUP BY phonenumber, customerid, surveyid";
	$surveyresult_res = $db->query($query);
	$line = "";
#	print $query;
	while($surveyresult_res->fetchinto($row)){
		$aryContent = array();

		$line = '"'.$row['CustomerName'].'","'.$row['Address'].'","'.$row['Zipcode'].'","'.$row['City'].'","'.$row['State'].'","'.$row['Country'].'","'.$row['Phone'].'"';

		$query = "SELECT * from surveyresult  WHERE phonenumber = '".$row['phonenumber']."' AND customerid = '".$row['customerid']."' AND surveyid = '".$row['surveyid']."' ";

#		print $query;
#			print "<br>";
		$detailRes = $db->query($query);
		while($detailRes->fetchinto($detailRow)){
			$flag = 0;
			foreach ($aryHeader as $key => $val){
				if ($detailRow["surveyoption"] == ""){
					// dialer检测的放到最后
					$aryContent[$i] = $detailRow['surveynote'];
					$flag = 1;
				}else if (trim($detailRow["surveyoption"]) == trim($val)){
					if ($detailRow['itemcontent'] != "")
						$aryContent[$key] = $detailRow['itemcontent'];
					else
						$aryContent[$key] = $detailRow['surveynote'];
					$flag = 1;
				}
				if ($flag ==1) break;
			}
			if ($flag == 0) $aryContent[$i] = $detailRow['surveynote'];
		}
		for ($j = 1;$j<=$i;$j++){
			$line .= ',"'.$aryContent[$j].'"';
		}
		echo "$line\n";
#		echo "<br>";
	}
}
//die;

//echo astercrm::exportDataToCSV($sql);
ob_end_flush();
?>