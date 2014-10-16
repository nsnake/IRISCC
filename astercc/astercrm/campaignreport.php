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


if ($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin' && !is_array($_SESSION['curuser']['privileges']['campaign'])) 
	header("Location: portal.php");
require_once ("db_connect.php");
require_once ('include/astercrm.class.php');
$campaignid = trim(strtolower($_REQUEST['campaignid']));
$category = trim(strtolower($_REQUEST['category']));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
	<LINK href="skin/default/css/style.css" type=text/css rel=stylesheet>
	<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
	</head>
	<body>
<center>
<table border="0" width="600">
<tr><td>
<?php
#error_reporting(E_ALL);
#error_reporting(0);
global $db;

$query  = "SELECT * FROM campaign WHERE id = '$campaignid' ";
$campaign_row = $db->getRow($query);
if (!$campaign_row){
	print "no such campaign";
	die;
}
$total = 0;
echo '<table border="1" width="100%" class="adminlist" align="center">';
if ($category == "call_result_analysis"){
	echo '<tr><th colspan=3>Call Result Analysis</tr>';
	echo '<tr><th>Result</th><th>Count</th><th>Detail</th></tr>';
	# get all call result from campaign but skip top level
	$query = "SELECT campaignresult, count(*) as campaigncount FROM campaigndialedlist WHERE campaignid = '$campaignid' GROUP BY campaignresult ORDER BY campaigncount DESC";
	$res = $db->query($query);
	while ($res->fetchInto($row)) {
		echo '<tr><td>'.$row['campaignresult'].'</td><td>'.$row['campaigncount'].'</td><td><a href="campaignreport.php?category=campaignresult&campaignid='.$campaignid.'&campaignresult='.$row['campaignresult'].'"  target="_blank">Detail</a></td></tr>';
		$total += $row['campaigncount'];
	}
	echo '<tr><td>Total:</td><td>'.$total.'</td></tr>';
}else if ($category == "hit_rate_analysis"){
	echo '<tr><th colspan=2>Hit Rate Analysis</tr>';
	echo '<tr><th>Dialed Time</th><th>Count</th></tr>';

	$query = "SELECT trytime, COUNT(*) as trytimecount FROM campaigndialedlist GROUP BY trytime";
	$res = $db->query($query);
	while ($res->fetchInto($row)) {
		echo '<tr><td>'.$row['trytime'].'</td><td>'.$row['trytimecount'].'</td></tr>';
		$total += $row['trytimecount'];
	}
	echo '<tr><td>Total:</td><td>'.$total.'</td></tr>';
}else if ($category == "referals_vs_contacts"){
	echo '<tr><th colspan=2>Referals vs Contacts</tr>';
	echo '<tr><th>Creby</th><th>Count</th></tr>';

	$query = "SELECT creby, COUNT(*) as crebycount FROM campaigndialedlist GROUP BY creby";
	$res = $db->query($query);
	while ($res->fetchInto($row)) {
		echo '<tr><td>'.$row['creby'].'</td><td>'.$row['crebycount'].'</td></tr>';
		$total += $row['crebycount'];
	}
	echo '<tr><td>Total:</td><td>'.$total.'</td></tr>';
}else if ($category == "campaignresult"){
	$campaignresult = $_REQUEST['campaignresult'];
	echo '<tr><th colspan=2>Campaignresult Detail</tr>';
	echo '<tr><th>Creby</th><th>Count</th></tr>';

	$query = "SELECT resultby, COUNT(*) as resultbycount FROM campaigndialedlist WHERE campaignresult = '$campaignresult' GROUP BY resultby";
	$res = $db->query($query);
	while ($res->fetchInto($row)) {
		echo '<tr><td>'.$row['resultby'].'</td><td>'.$row['resultbycount'].'</td></tr>';
		$total += $row['resultbycount'];
	}
	echo '<tr><td>Total:</td><td>'.$total.'</td></tr>';
}else if($category == "agents"){
	echo '<tr><th colspan=2>Agents</tr>';
	echo '<tr><th>Resultby</th><th>Duration</th></tr>';

	$query = "SELECT SUM(billsec) as duration, resultby FROM campaigndialedlist WHERE campaignresult = '$campaignresult' GROUP BY resultby";
	$res = $db->query($query);
	while ($res->fetchInto($row)) {
		echo '<tr><td>'.$row['resultby'].'</td><td>'.$row['duration'].'</td></tr>';
		$total += $row['duration'];
	}
	echo '<tr><td>Total:</td><td>'.$total.'</td></tr>';
}

echo '</table>';
?>
</td></tr>
</table>
</center>
	</body>
</html>
