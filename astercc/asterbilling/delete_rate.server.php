<?php
/*******************************************************************************
* delete_rate.server.php


* Function Desc

* 功能描述

* Function Desc
* Date				08 May 2012

* Desc: page created

********************************************************************************/

require_once ("db_connect.php");
require_once ('delete_rate.grid.inc.php');
require_once ('include/xajaxGrid.inc.php');
require_once ('include/common.class.php');
require_once ('include/asterevent.class.php');
require_once ("delete_rate.common.php");
require_once ('include/asterisk.class.php');

/**
*  initialize page elements
*
*/

function init(){
	global $locate;

	$objResponse = new xajaxResponse();
	
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));
	$objResponse->addAssign("delTable","value","");
	$objResponse->addAssign("delType","value","all");
	$objResponse->addAssign("delObject","value",'all');
	
	return $objResponse;
}

function changeTable($value){
	global $locate;
	$objResponse = new xajaxResponse();
	
	if($value == 'resellerrate'){
		$delTypeHtml = '<select id="delType" name="delType" onchange="xajax_getObjectData(this.value);"><option value="all">'.$locate->Translate("all").'</option><option value="system">'.$locate->Translate("system").'</option><option value="reseller">'.$locate->Translate("reseller").'</option></select>';
	} else {
		$delTypeHtml = '<select id="delType" name="delType" onchange="xajax_getObjectData(this.value);"><option value="all">'.$locate->Translate("all").'</option><option value="system">'.$locate->Translate("system").'</option><option value="reseller">'.$locate->Translate("reseller").'</option><option value="group">'.$locate->Translate("group").'</option></select>';
	}
	
	$objResponse->addAssign("delTypeBtn","innerHTML",$delTypeHtml);
	$objResponse->addAssign("delType","value",'all');
	$objResponse->addAssign("delObjectBtn","innerHTML",'<select id="delObject" name="delObject"><option value="all">'.$locate->Translate("all").'</option></select>');
	return $objResponse->getXML();
}

function getObjectData($object){
	global $locate;
	
	$objResponse = new xajaxResponse();
	
	if($object == 'all'){
		$optionHtml = '<select id="delObject" name="delObject"><option value="all">'.$locate->Translate('all').'</option></select>';
	} else if($object == 'system'){
		$optionHtml = '<select id="delObject" name="delObject"><option value="default">'.$locate->Translate('default').'</option></select>';
	} else if($object == 'reseller'){
		$optionHtml = & Customer::getObjectHtml('resellergroup');
	} else if($object == 'group'){
		$optionHtml = & Customer::getObjectHtml('accountgroup');
	}
	
	$objResponse->addAssign("delObjectBtn","innerHTML",$optionHtml);
	return $objResponse->getXML();
}

function searchRate($f){
	global $locate;
	$objResponse = new xajaxResponse();

	$delTable = $f['delTable'];
	$delType = $f['delType'];
	$delObject = $f['delObject'];

	if(empty($delTable)){
		$objResponse->addAlert($locate->Translate("Please select a rate table"));
		return $objResponse;
	}
	
	$rateHtml = Customer::searchRateHtml($delTable,$delType,$delObject);
	
	$objResponse->addAssign("searchRateList","innerHTML",$rateHtml);
	$objResponse->addAssign("delRateBtn","innerHTML","<input type=\"button\" value=\"".$locate->Translate("delete")."\" onclick=\"if (confirm('".$locate->Translate("Are you sure you want to delete this rate")."?')) xajax_deleteRate(document.getElementById('deleteSql').value,document.getElementById('historySql').value);return false;\" />");

	return $objResponse->getXML();
}

function deleteRate($deleteSql,$historySql){
	global $locate,$db,$config;
	$objResponse = new xajaxResponse();
	
	
	if(empty($deleteSql)){
		$objResponse->addAlert($locate->Translate("Can not delete this rate"));
		return $objResponse;
	}

	//if enable the synchronizatioin
	if($config['synchronize']['delete_by_use_history']){
		Customer::events($historySql);
		$insertResult = $db->query($historySql);
		if($insertResult <= 0){
			$objResponse->addAlert($locate->Translate("delete failed synchronization"));
			return $objResponse;
		}
	}

	Customer::events($deleteSql);
	$result = $db->query($deleteSql);
	if($result){
		$objResponse->addAlert($locate->Translate("Delete success").','.$locate->Translate("please remember to restart asterrc"));
		$objResponse->addAssign("searchRateList","innerHTML",$locate->Translate("page_rate_tips").'&nbsp;&nbsp;<input type="button" value="'.$locate->Translate("restart asterrc").'" onclick="restartAsterrc();return false;" />');
	} else {
		$objResponse->addAlert($locate->Translate("Delete failed"));
	}

	return $objResponse;
}

function restartAsterrc(){
	global $locate;
	$objResponse = new xajaxResponse();
	if($_SESSION['curuser']['usertype'] != 'admin') return $objResponse;

	$myAsterisk = new Asterisk();
	$pso = exec("ps -ef |grep -v grep |grep -E /asterr[a-z]{0,1\}[.\ ]+-d |awk '{print $2}'");
		
	$rk = exec("sudo /opt/asterisk/scripts/astercc/asterrc -k",$rkd);

	$rd = exec("sudo /opt/asterisk/scripts/astercc/asterrc -d",$rdd,$rdv);

	$psn = exec("ps -ef |grep -v grep |grep -E /asterr[a-z]{0,1\}[.\ ]+-d |awk '{print $2}'");
	if($psn == ''){
		$objResponse->addAlert($locate->Translate('start asterrc failed'));
	}elseif($psn != $pso){
		$objResponse->addAlert($locate->Translate('asterrc have been restart'));
	}elseif($psn == $pso ){
		$objResponse->addAlert($locate->Translate('asterrc restart failed'));
	}
	
	return $objResponse;
}

$xajax->processRequests();
?>
