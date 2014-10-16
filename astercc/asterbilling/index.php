<?
	require_once('config.php');
	global $config;
	session_start();
	
	if (!isset($_SESSION['curuser'])){
		if($config['system']['useindex'] == 'admin')
			header("Location: admin.php");
		else
			header("Location: login.php");
	}else{
			header("Location: cdr.php");
	}

?>