<?php
// Tanslate to chinese by Donnie
require_once ("managerportal.common.php");
require_once ("db_connect.php");
require_once ("include/common.class.php");

function init(){
	global $locate,$config;
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));
	return $objResponse;
}

$xajax->processRequests();
?>
