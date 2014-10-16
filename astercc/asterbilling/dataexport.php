<?
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0',false);
header('Pragma: no-cache');
session_cache_limiter('public, no-store');

session_set_cookie_params(0);
if (!session_id()) session_start();
setcookie('PHPSESSID', session_id());


if ($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'groupadmin' && $_SESSION['curuser']['usertype'] != 'reseller') 
	header("Location: systemstatus.php");

require_once ("db_connect.php");
require_once ('include/astercrm.class.php');
//$sql = $_REQUEST['hidSql'];
$sql = $_SESSION['export_sql'];
$table = $_REQUEST['table'];

if ($sql == '') exit;
	
if ($_SESSION['curuser']['usertype'] != 'admin'){
	if($_SESSION['curuser']['usertype'] == 'groupadmin'){
		if($table == 'callshoprate'){
			if (strpos(strtolower($sql),'where'))
				$sql .= " and (groupid = ".$_SESSION['curuser']['groupid']." OR (groupid = 0 AND (resellerid='".$_SESSION['curuser']['resellerid']."' OR resellerid=0)))";
			else
				$sql .= " where (groupid = ".$_SESSION['curuser']['groupid']." OR (groupid = 0 AND (resellerid='".$_SESSION['curuser']['resellerid']."' OR resellerid=0)))";
		}else{
			if (strpos(strtolower($sql),'where'))
				$sql .= " and groupid = ".$_SESSION['curuser']['groupid'];
			else
				$sql .= " where groupid = ".$_SESSION['curuser']['groupid'];
		}
	}elseif($_SESSION['curuser']['usertype'] == 'reseller'){
		if (strpos(strtolower($sql),'where'))
			$sql .= " and resellerid = ".$_SESSION['curuser']['resellerid'];
		else
			$sql .= " where resellerid = ".$_SESSION['curuser']['resellerid'];
	}
}

if(!function_exists('mb_convert_encoding')){
	echo "Please install php-mbstring!";exit;
}

ob_start();
header("charset=uft-8");   
header('Content-type:  application/force-download');
header('Content-Transfer-Encoding:  Binary');
header('Content-disposition:  attachment; filename=astercc.csv');
echo astercrm::exportDataToCSV($sql);
ob_end_flush();
unset($_SESSION['export_sql']);
?>