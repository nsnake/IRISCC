<?php
/*******************************************************************************
* db_connect.php
* database connect file
* Function Desc
	use PEAR to connect database, define a global database variable: $db


* Revision 0.044  2007/09/7 17:55:00  last modified by solo
* Desc: add some comments

********************************************************************************/

// If you have the PEAR PHP package, you can comment the next line.
ini_set('include_path',dirname($_SERVER["SCRIPT_FILENAME"])."/include");

require_once 'DB.php';
require_once 'PEAR.php';
require_once 'config.php';

// define database connection string
define('SQLC', $config['database']['dbtype']."://".$config['database']['username'].":".$config['database']['password']."@".$config['database']['dbhost']."/".$config['database']['dbname']."");

// set a global variable to save database connection
$GLOBALS['db'] = DB::connect(SQLC);

// need to check if db connected
if (DB::iserror($GLOBALS['db'])){
	die($GLOBALS['db']->getmessage());
}

// change database fetch mode
$GLOBALS['db']->setFetchMode(DB_FETCHMODE_ASSOC);

?>