<?php
if($_SESSION['error_report'] != '' && isset($_SESSION['error_report'])) {
	#error_reporting($_SESSION['error_report']);
} else {
	require_once ('include/common.class.php');
	Common::read_ini_file("astercrm.conf.php",$config);

	#error_reporting($config['error_report']['error_report_level']);
}

class Localization{
	var $filePath;

	function Localization($language,$country,$page){
		$this->filePath = "language/".$page."_".$language."_".$country.".php";
	}
	

	function Translate($str){
		$source = $str;
		$filePath = dirname(dirname(__FILE__))."/include/".$this->filePath;
		if (file_exists($filePath)){
			require $this->filePath;
			$str =str_replace(" ","_",strtolower($str));
			if ($$str != "")
				return $$str;
			else
				return $source;
		}else{
			return $str;
		}
	}
}
?>