<?php
require_once ('include/common.class.php');
Common::read_ini_file("astercrm.conf.php",$config);

error_reporting($config['error_report']['error_report_level']);

define("LOG_ENABLED", $config['system']['log_enabled']); // Enable debug
define("FILE_LOG", $config['system']['log_file_path']);  // File to output debug message.
?>