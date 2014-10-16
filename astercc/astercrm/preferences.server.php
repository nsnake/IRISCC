<?php
/*******************************************************************************
* preferences.server.php

* 配置管理系统后台文件
* preferences background management script

* Function Desc
	provide preferences management script

* 功能描述
	提供配置管理脚本

* Function Desc
		init				初始化页面元素
		initIni				从配置文件中读取信息填充页面上的input对象
		initLocate			初始化页面上的说明信息
		savePreferences		保存配置文件
		checkDb				检查数据库是否能正确连接
		checkAMI			检查AMI是否能正确连接
		checkSys			检查系统参数是否正确
							目前仅检查了上传目录是否可写

* Revision 0.0456  2007/11/12 15:47:00  last modified by solo
* Desc: page created
********************************************************************************/

require_once ("db_connect.php");
require_once ("preferences.common.php");
require_once ("include/asterisk.class.php");

/**
*  initialize page elements
*
*/

function init(){
	$objResponse = new xajaxResponse();
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));
	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));
	$objResponse->loadXML(initLocate());
	$objResponse->loadXML(initIni());
	return $objResponse;
}

function initIni(){
	global $config;

	$objResponse = new xajaxResponse();

	//database section
	$objResponse->addAssign("iptDbDbtype","value",$config["database"]["dbtype"]);
	$objResponse->addAssign("iptDbDbhost","value",$config["database"]["dbhost"]);
	$objResponse->addAssign("iptDbDbname","value",$config["database"]["dbname"]);
	$objResponse->addAssign("iptDbUsername","value",$config["database"]["username"]);
	$objResponse->addAssign("iptDbPassword","value",$config["database"]["password"]);
	
	//asterisk section
	$objResponse->addAssign("iptAsServer","value",$config["asterisk"]["server"]);
	$objResponse->addAssign("iptAsPort","value",$config["asterisk"]["port"]);
	$objResponse->addAssign("iptAsUsername","value",$config["asterisk"]["username"]);
	$objResponse->addAssign("iptAsSecret","value",$config["asterisk"]["secret"]);
	$objResponse->addAssign("iptAsMonitorpath","value",$config["asterisk"]["monitorpath"]);
	$objResponse->addAssign("iptAsMonitorformat","value",$config["asterisk"]["monitorformat"]);

	//system section
	$objResponse->addAssign("iptSyseventtype","value",$config["system"]["eventtype"]);
	$objResponse->addAssign("iptSysLogEnabled","value",$config["system"]["log_enabled"]);

	//print $config["system"]["log_enabled"];
	//exit;
	$objResponse->addAssign("iptSysLogFilePath","value",$config["system"]["log_file_path"]);
	$objResponse->addAssign("iptSysAsterccPath","value",$config["system"]["astercc_path"]);
	$objResponse->addAssign("iptSysOutcontext","value",$config["system"]["outcontext"]);
	$objResponse->addAssign("iptSysIncontext","value",$config["system"]['incontext']);

	$objResponse->addAssign(
			"iptSysStop_work_verify",
			"value",
			$config["system"]["stop_work_verify"]);

	$objResponse->addAssign(
			"iptSysPredialerExtension",
			"value",
			$config["system"]["predialer_extension"]);

	$objResponse->addAssign(
			"iptSysPhoneNumberLength",
			"value",
			$config["system"]["phone_number_length"]);
	$objResponse->addAssign(
			"iptSysSmartMatchRemove",
			"value",
			$config["system"]["smart_match_remove"]);
	$objResponse->addAssign(
			"iptSysStatusCheckInterval",
			"value",
			$config["system"]["status_check_interval"]);

	$objResponse->addAssign(
			"iptSysTrimPrefix",
			"value",
			$config["system"]["trim_prefix"]);
	$objResponse->addAssign("iptSysAllowDropcall","value",$config["system"]["allow_dropcall"]);
	$objResponse->addAssign("iptSysAllowSameData","value",$config["system"]["allow_same_data"]);

	$objResponse->addAssign("iptSysPortalDisplayType","value",$config["system"]["portal_display_type"]);

	$objResponse->addAssign("iptSysExtensionPannel","value",$config["system"]["extension_pannel"]);
	$objResponse->addAssign("iptSysTransferPannel","value",$config["system"]["transfer_pannel"]);
	$objResponse->addAssign("iptSysMonitorPannel","value",$config["system"]["monitor_pannel"]);
	$objResponse->addAssign("iptSysDialPannel","value",$config["system"]["dial_pannel"]);
	$objResponse->addAssign("iptSysMissionPannel","value",$config["system"]["mission_pannel"]);
	$objResponse->addAssign("iptSysDiallistPannel","value",$config["system"]["diallist_pannel"]);

	$objResponse->addAssign("iptSysPopUpWhenDialOut","value",$config["system"]["pop_up_when_dial_out"]);

	$objResponse->addAssign("iptSysPopUpWhenDialIn","value",$config["system"]["pop_up_when_dial_in"]);

	$objResponse->addAssign("iptSysBrowserMaximizeWhenPopUp","value",$config["system"]["browser_maximize_when_pop_up"]);

	$objResponse->addAssign("iptSysFirstring","value",$config["system"]["firstring"]);
	$objResponse->addAssign("iptSysEnableExternalCrm","value",$config["system"]["enable_external_crm"]);

	$objResponse->addAssign("iptSysEnableContact","value",$config["system"]["enable_contact"]);
	$objResponse->addAssign("iptSysDetailLevel","value",$config["system"]["detail_level"]);

	$objResponse->addAssign("iptSysOpenNewWindow","value",$config["system"]["open_new_window"]);

	$objResponse->addAssign("iptSysExternalCrmDefaultUrl","value",$config["system"]["external_crm_default_url"]);

	$objResponse->addAssign("iptSysExternalCrmUrl","value",$config["system"]["external_crm_url"]);

	$objResponse->addAssign("iptSysUploadFilePath","value",$config["system"]["upload_file_path"]);
	
	$objResponse->addAssign("iptEnable_surveynote","value",$config["survey"]["enable_surveynote"]);
	$objResponse->addAssign("iptClose_popup_after_survey","value",$config["survey"]["close_popup_after_survey"]);

	$objResponse->addAssign("iptPopup_diallist","value",$config["diallist"]["popup_diallist"]);

	$objResponse->addAssign("iptGooglemapkey","value",$config["google-map"]["key"]);
	$objResponse->addAssign("iptErrorReportLevel","value",$config["error_report"]["error_report_level"]);

	Common::read_ini_file($config['system']['astercc_path'].'/astercc.conf',$asterccConfig);
	$objResponse->addAssign("asterccLicenceto","value",$asterccConfig["licence"]["licenceto"]);
	$objResponse->addAssign("asterccChannels","value",$asterccConfig["licence"]["channel"]);
	$objResponse->addAssign("asterccKey","value",$asterccConfig["licence"]["key"]);

	$objResponse->addAssign("iptSysAuto_note_popup","value",$config["system"]["auto_note_popup"]);
	if($config["system"]["highest_priority_note"]){
		$objResponse->addAssign("iptSysHighest_priority_note","checked","true");
	} else {
		$objResponse->addAssign("iptSysHighest_priority_note","checked","");
	}
	if($config["system"]["lastest_priority_note"]) {
		$objResponse->addAssign("iptSysLastest_priority_note","checked","true");
	} else {
		$objResponse->addAssign("iptSysLastest_priority_note","checked","");
	}
	
	$objResponse->addAssign("iptSysDefault_share_note","value",$config["system"]["default_share_note"]);
	$objResponse->addAssign("iptSysCustomer_leads","value",$config["system"]["customer_leads"]);
	$objResponse->addAssign("iptSysEnableCode","value",$config["system"]["enable_code"]);
	$objResponse->addAssign("iptSysUpdateOnlineInterval","value",$config["system"]["update_online_interval"]);

	//sms
	$objResponse->addAssign("iptSysEnableSMS","value",$config["system"]["enable_sms"]);

	//require_reason_when_pause
	$objResponse->addAssign("iptSysRequireReasonWhenPause","value",$config["system"]["require_reason_when_pause"]);

	//iptCreateTicket 
	$objResponse->addAssign("iptSysCreateTicket","value",$config["system"]["create_ticket"]);

	//iptSysEnableSocket 
	$objResponse->addAssign("iptSysEnableSocket","value",$config["system"]["enable_socket"]);
	//iptSysPort 
	$objResponse->addAssign("iptSysFixPort","value",$config["system"]["fix_port"]);
	//iptSysSocketUrl 
	$objResponse->addAssign("iptSysSocketUrl","value",$config["system"]["socket_url"]);
	$objResponse->addAssign("iptSysExportCustomerFieldsInDialedlist","value",$config["system"]["export_customer_fields_in_dialedlist"]);

	$objResponse->addAssign("iptSysAllowPopupWhenAlreadyPopup","value",$config["system"]["allow_popup_when_already_popup"]);

	//enable_formadd_popup
	$objResponse->addAssign("iptSysEnableFormAddPopup","value",$config["system"]["enable_formadd_popup"]);

	return $objResponse;
}

function initLocate(){
	global $locate;

	$objResponse = new xajaxResponse();

	//database section
	$objResponse->addAssign("divDbDbtype","innerHTML",$locate->Translate('db_dbtype'));
	$objResponse->addAssign("divDbDbhost","innerHTML",$locate->Translate('db_dbhost'));
	$objResponse->addAssign("divDbDbname","innerHTML",$locate->Translate('db_dbname'));
	$objResponse->addAssign("divDbUsername","innerHTML",$locate->Translate('db_username'));
	$objResponse->addAssign("divDbPassword","innerHTML",$locate->Translate('db_password'));

	//asterisk section
	$objResponse->addAssign("divAsServer","innerHTML",$locate->Translate('as_server'));
	$objResponse->addAssign("divAsPort","innerHTML",$locate->Translate('as_port'));
	$objResponse->addAssign("divAsUsername","innerHTML",$locate->Translate('as_username'));
	$objResponse->addAssign("divAsSecret","innerHTML",$locate->Translate('as_secret'));
	$objResponse->addAssign(
				"divAsMonitorpath",
				"innerHTML",
				$locate->Translate('as_monitorpath'));
	$objResponse->addAssign(
				"divAsMonitorformat",
				"innerHTML",
				$locate->Translate('as_monitorformat'));


	//system section
	$objResponse->addAssign("divSyseventtype","innerHTML",$locate->Translate('Sys_eventtype'));
	$objResponse->addAssign("divSysLogEnabled","innerHTML",$locate->Translate('sys_log_enabled'));
	$objResponse->addAssign("divSysLogFilePath","innerHTML",$locate->Translate('sys_log_file_path'));
	$objResponse->addAssign("divSysAsterccPath","innerHTML",$locate->Translate('astercc_path'));
	$objResponse->addAssign("divSysOutcontext","innerHTML",$locate->Translate('sys_outcontext'));
	$objResponse->addAssign("divSysIncontext","innerHTML",$locate->Translate('sys_incontext'));

	$objResponse->addAssign(
			"divSysStop_work_verify",
			"innerHTML",
			$locate->Translate('Sys_Stop_work_verify'));

	$objResponse->addAssign(
			"divSysPredialerExtension",
			"innerHTML",
			$locate->Translate('sys_predialer_extension'));

	$objResponse->addAssign(
			"divSysPhoneNumberLength",
			"innerHTML",
			$locate->Translate('sys_phone_number_length'));

	$objResponse->addAssign(
			"divSysSmartMatchRemove",
			"innerHTML",
			$locate->Translate('smart_match_remove'));

	$objResponse->addAssign(
			"divSysStatusCheckInterval",
			"innerHTML",
			$locate->Translate('status_check_interval'));

	$objResponse->addAssign(
			"divSysTrimPrefix",
			"innerHTML",
			$locate->Translate('sys_trim_prefix'));
	$objResponse->addAssign("divSysAllowDropcall","innerHTML",$locate->Translate('sys_allow_dropcall'));
	$objResponse->addAssign("divSysAllowSameData","innerHTML",$locate->Translate('sys_allow_same_data'));

	$objResponse->addAssign("divSysPortalDisplayType","innerHTML",$locate->Translate('sys_portal_display_type'));

	$objResponse->addAssign("divSysAgentPannelSetting","innerHTML",$locate->Translate('sys_agent_pannel_setting'));

	$objResponse->addAssign("divSysPopUpWhenDialOut","innerHTML",$locate->Translate('sys_pop_up_when_dial_out'));

	$objResponse->addAssign("divSysPopUpWhenDialIn","innerHTML",$locate->Translate('sys_pop_up_when_dial_in'));

	$objResponse->addAssign("divSysBrowserMaximizeWhenPopUp","innerHTML",$locate->Translate('sys_browser_maximize_when_pop_up'));

	$objResponse->addAssign("divSysFirstring","innerHTML",$locate->Translate('sys_firstring'));
	$objResponse->addAssign("divSysEnableExternalCrm","innerHTML",$locate->Translate('sys_enable_external_crm'));

	$objResponse->addAssign("divSysEnableContact","innerHTML",$locate->Translate('sys_enable_contact'));

	$objResponse->addAssign("divSysDetailLevel","innerHTML",$locate->Translate('read group database or system database'));

	$objResponse->addAssign("divSysOpenNewWindow","innerHTML",$locate->Translate('sys_open_new_window'));

	$objResponse->addAssign("divSysExternalCrmDefaultUrl","innerHTML",$locate->Translate('sys_external_crm_default_url'));

	$objResponse->addAssign("divSysExternalCrmUrl","innerHTML",$locate->Translate('sys_external_crm_url'));

	$objResponse->addAssign("divSysUploadFilePath","innerHTML",$locate->Translate('sys_upload_file_path'));

	$objResponse->addAssign("divSysAuto_note_popup","innerHTML",$locate->Translate('if_auto_popup_note_info'));
	$objResponse->addAssign("divSysHighest_priority_note","innerHTML",$locate->Translate('if_popup_the_highest_priority_note_info'));
	$objResponse->addAssign("divSysLastest_priority_note","innerHTML",$locate->Translate('if_popup_the_lastest_priority_note_info'));

	$objResponse->addAssign("divSysDefault_share_note","innerHTML",$locate->Translate('if_share_note_default'));
	
	$objResponse->addAssign("divEnable_surveynote","innerHTML",$locate->Translate('enable_surveynote'));
	$objResponse->addAssign("divClose_popup_after_survey","innerHTML",$locate->Translate('close_popup_after_survey'));
	$objResponse->addAssign("divPopup_diallist","innerHTML",$locate->Translate('popup_diallist'));

	$objResponse->addAssign("divSysEnableCode","innerHTML",$locate->Translate('if_enable_code'));
	$objResponse->addAssign("divSysUpdateOnlineInterval","innerHTML",$locate->Translate('the_smaller_the_value_the_more_accurate'));
	
	//sms
	$objResponse->addAssign("divSysEnableSMS","innerHTML",$locate->Translate('enable_sms_pop'));

	//sms
	$objResponse->addAssign("divSysRequireReasonWhenPause","innerHTML",$locate->Translate('require_reason_when_pause'));

	//iptSysCreateTicket
	$objResponse->addAssign("divSysCreateTicket","innerHTML",$locate->Translate('create_ticket'));

	//divSysCreateTicket 
	$objResponse->addAssign("divSysEnableSocket","innerHTML",$locate->Translate('enable_socket'));
	//divSysFixPort 
	$objResponse->addAssign("divSysFixPort","innerHTML",$locate->Translate('fix_port'));
	//divSysSocketUrl 
	$objResponse->addAssign("divSysSocketUrl","innerHTML",$locate->Translate('socket_url'));
	$objResponse->addAssign("divSysExportCustomerFieldsInDialedlist","innerHTML",$locate->Translate('export_customer_fields_in_dialedlist'));

	$objResponse->addAssign("divSysAllowPopupWhenAlreadyPopup","innerHTML",$locate->Translate('allow_popup_when_already_popup'));

	//enable_formadd_popup
	$objResponse->addAssign("divSysEnableFormAddPopup","innerHTML",$locate->Translate('enable_formadd_popup'));
	
	return $objResponse;
}

function savePreferences($aFormValues){
	global $config,$locate;
	//print_r($aFormValues);exit;
	//exit;
	$objResponse = new xajaxResponse();
	//Common::read_ini_file("astercrm.conf.php",$myPreferences);
	$myPreferences = $config;
	//database section
	$myPreferences['database']['dbtype'] = $aFormValues['iptDbDbtype'];
	$myPreferences['database']['dbhost'] = $aFormValues['iptDbDbhost'];
	//print $aFormValues['iptDbDbhost'];
	$myPreferences['database']['dbname'] = $aFormValues['iptDbDbname'];
	$myPreferences['database']['username'] = $aFormValues['iptDbUsername'];
	$myPreferences['database']['password'] = $aFormValues['iptDbPassword'];

	//asterisk section
	$myPreferences['asterisk']['server'] = $aFormValues['iptAsServer'];
	$myPreferences['asterisk']['port'] = $aFormValues['iptAsPort'];
	$myPreferences['asterisk']['username'] = $aFormValues['iptAsUsername'];
	$myPreferences['asterisk']['secret'] = $aFormValues['iptAsSecret'];
	$myPreferences['asterisk']['monitorpath'] = $aFormValues['iptAsMonitorpath'];
	$myPreferences['asterisk']['monitorformat'] = $aFormValues['iptAsMonitorformat'];
	//system section
	$myPreferences['system']['log_enabled'] = $aFormValues['iptSysLogEnabled'];
	$myPreferences['system']['log_file_path'] = $aFormValues['iptSysLogFilePath'];
	$myPreferences['system']['astercc_conf_path'] = $aFormValues['iptSysAsterccConfPath'];
	$myPreferences['system']['outcontext'] = $aFormValues['iptSysOutcontext'];
	$myPreferences['system']['incontext'] = $aFormValues['iptSysIncontext'];
	$myPreferences['system']['eventtype'] = $aFormValues['iptSyseventtype'];
	$myPreferences['system']['stop_work_verify'] = $aFormValues['iptSysStop_work_verify'];



	$myPreferences['system']['phone_number_length'] = $aFormValues['iptSysPhoneNumberLength'];
	$myPreferences['system']['smart_match_remove'] = $aFormValues['iptSysSmartMatchRemove'];
	$myPreferences['system']['status_check_interval'] = $aFormValues['iptSysStatusCheckInterval'];
	$myPreferences['system']['trim_prefix'] = $aFormValues['iptSysTrimPrefix'];
	$myPreferences['system']['allow_dropcall'] = $aFormValues['iptSysAllowDropcall'];
	$myPreferences['system']['allow_same_data'] = $aFormValues['iptSysAllowSameData'];
	$myPreferences['system']['portal_display_type'] = $aFormValues['iptSysPortalDisplayType'];

	$myPreferences['system']['extension_pannel'] = $aFormValues['iptSysExtensionPannel'];
	$myPreferences['system']['transfer_pannel'] = $aFormValues['iptSysTransferPannel'];
	$myPreferences['system']['monitor_pannel'] = $aFormValues['iptSysMonitorPannel'];
	$myPreferences['system']['dial_pannel'] = $aFormValues['iptSysDialPannel'];
	$myPreferences['system']['mission_pannel'] = $aFormValues['iptSysMissionPannel'];
	$myPreferences['system']['diallist_pannel'] = $aFormValues['iptSysDiallistPannel'];

	$myPreferences['system']['pop_up_when_dial_out'] = $aFormValues['iptSysPopUpWhenDialOut'];
	$myPreferences['system']['pop_up_when_dial_in'] = $aFormValues['iptSysPopUpWhenDialIn'];
	$myPreferences['system']['browser_maximize_when_pop_up'] = $aFormValues['iptSysBrowserMaximizeWhenPopUp'];
	$myPreferences['system']['firstring'] = $aFormValues['iptSysFirstring'];
	$myPreferences['system']['enable_external_crm'] = $aFormValues['iptSysEnableExternalCrm'];
	$myPreferences['system']['enable_contact'] = $aFormValues['iptSysEnableContact'];
	$myPreferences['system']['detail_level'] = $aFormValues['iptSysDetailLevel'];
	$myPreferences['system']['open_new_window'] = $aFormValues['iptSysOpenNewWindow'];
	$myPreferences['system']['external_crm_default_url'] = $aFormValues['iptSysExternalCrmDefaultUrl'];
	$myPreferences['system']['external_crm_url'] = $aFormValues['iptSysExternalCrmUrl'];
	$myPreferences['system']['upload_file_path'] = $aFormValues['iptSysUploadFilePath'];

	$myPreferences['system']['auto_note_popup'] = $aFormValues['iptSysAuto_note_popup'];
	
	$myPreferences['system']['highest_priority_note'] = ($aFormValues['iptSysHighest_priority_note'] == 'on'?1:0);
	$myPreferences['system']['lastest_priority_note'] = ($aFormValues['iptSysLastest_priority_note'] == 'on'?1:0);

	$myPreferences['system']['default_share_note'] = $aFormValues['iptSysDefault_share_note'];
	$myPreferences['system']['customer_leads'] = $aFormValues['iptSysCustomer_leads'];
	$myPreferences['system']['enable_code'] = $aFormValues['iptSysEnableCode'];
	$myPreferences['system']['update_online_interval'] = $aFormValues['iptSysUpdateOnlineInterval'];
	$myPreferences['system']['enable_sms'] = $aFormValues['iptSysEnableSMS'];
	$myPreferences['system']['require_reason_when_pause'] = $aFormValues['iptSysRequireReasonWhenPause'];
	$myPreferences['system']['create_ticket'] = $aFormValues['iptSysCreateTicket'];
	$myPreferences['system']['enable_socket'] = $aFormValues['iptSysEnableSocket'];
	$myPreferences['system']['fix_port'] = $aFormValues['iptSysFixPort'];
	$myPreferences['system']['socket_url'] = $aFormValues['iptSysSocketUrl'];

	$myPreferences['system']['export_customer_fields_in_dialedlist'] = $aFormValues['iptSysExportCustomerFieldsInDialedlist'];

	$myPreferences['system']['allow_popup_when_already_popup'] = $aFormValues['iptSysAllowPopupWhenAlreadyPopup'];

	//enable_formadd_popup
	$myPreferences['system']['enable_formadd_popup'] = $aFormValues['iptSysEnableFormAddPopup'];

	$myPreferences['survey']['enable_surveynote'] = $aFormValues['iptEnable_surveynote'];
	$myPreferences['survey']['close_popup_after_survey'] = $aFormValues['iptClose_popup_after_survey'];
	$myPreferences['diallist']['popup_diallist'] = $aFormValues['iptPopup_diallist'];

	$myPreferences['google-map']['key'] = $aFormValues['iptGooglemapkey'];
	if($aFormValues['iptErrorReportLevel'] == '' || !preg_match('/^[\d]{0,4}$/',$aFormValues['iptErrorReportLevel'])) {
		$aFormValues['iptErrorReportLevel'] = 0;
	}
	$myPreferences['error_report']['error_report_level'] = $aFormValues['iptErrorReportLevel'];

	if (Common::write_ini_file("astercrm.conf.php",$myPreferences) >0)
		$objResponse->addAlert($locate->Translate('save_success'));
	else
		$objResponse->addAlert($locate->Translate('save_failed'));
	return $objResponse;
}

//检查数据库连接
function checkDb($aFormValues){
	global $locate;
	$objResponse = new xajaxResponse();
	$sqlc = $aFormValues['iptDbDbtype']."://".$aFormValues['iptDbUsername'].":".$aFormValues['iptDbPassword']."@".$aFormValues['iptDbDbhost']."/".$aFormValues['iptDbDbname']."";

	// set a global variable to save database connection
	$dbtest = DB::connect($sqlc);

	// need to check if db connected
	if (DB::iserror($dbtest)){
		$objResponse->addAssign("divDbMsg","innerHTML","<span class='failed'>".$locate->Translate('db_connect_failed')."</span>");
	}else{
		$objResponse->addAssign("divDbMsg","innerHTML","<span class='passed'>".$locate->Translate('db_connect_success')."</span>");
	}
	return $objResponse;

}

//检查AMI连接
function checkAMI($aFormValues){
	global $locate;
	$objResponse = new xajaxResponse();
	$myAsterisk = new Asterisk();
	
	$myConfig['server'] = $aFormValues["iptAsServer"];
	$myConfig['port'] = $aFormValues["iptAsPort"];
	$myConfig['username'] = $aFormValues["iptAsUsername"];
	$myConfig['secret'] =  $aFormValues["iptAsSecret"];

	$myAsterisk->config['asmanager'] = $myConfig;

	$res = $myAsterisk->connect();
	if ($res){
		$objResponse->addAssign("divAsMsg","innerHTML","<span class='passed'>".$locate->Translate('AMI_connect_success')."</span");
	}else{
		$objResponse->addAssign("divAsMsg","innerHTML","<span class='failed'>".$locate->Translate('AMI_connect_failed')."</span>");
	}

	return $objResponse;
}


function checkSys($aFormValues){
	global $locate;
	$objResponse = new xajaxResponse();

	//check directory permittion
	if (is_writable($aFormValues['iptSysUploadFilePath'])){
		$objResponse->addAssign("divSysMsg","innerHTML","<span class='passed'>".$locate->Translate('Upload Folder Writable')."</span");
	}else{
		$objResponse->addAssign("divSysMsg","innerHTML","<span class='failed'>".$locate->Translate('permission_error')."</span");
	}
		
	return $objResponse;
}

function saveLicence($aFormValues){
	global $config,$locate;
	$objResponse = new xajaxResponse();

	if(!file_exists($config['system']['astercc_path'].'/astercc.conf')){
		$objResponse->addAlert($locate->Translate('astercc_conf_non').$config['system']['astercc_path']);
		return $objResponse;
	}
	
	if(!file_exists($config['system']['astercc_path'].'/astercc')){
		$objResponse->addAlert($locate->Translate('astercc_non').$config['system']['astercc_path']);
		return $objResponse;
	}

	Common::read_ini_file($config['system']['astercc_path'].'/astercc.conf',$asterccConfig);

	$asterccConfig['licence']['licenceto'] = $aFormValues['asterccLicenceto'];
	$asterccConfig['licence']['channel'] = $aFormValues['asterccChannels'];
	$asterccConfig['licence']['key'] = $aFormValues['asterccKey'];

	if (Common::write_ini_file($config['system']['astercc_path'].'/astercc.conf',$asterccConfig) > 0){
		$rval = exec($config['system']['astercc_path'].'/astercc -t',$asterccMsg);
		$asterccMsg = implode("\n",$asterccMsg);

		if ( stristr($asterccMsg,'Success') === FALSE ) { //check key if vaild
			$objResponse->addAlert($asterccMsg);
		}else{
			$objResponse->addAlert($locate->Translate('update_licence_success'));
		}
	}else{
		$objResponse->addAlert($locate->Translate('update_licence_failed'));
	}
	return $objResponse;
}

function systemAction($type){
	global $locate;
	$objResponse = new xajaxResponse();
	if($_SESSION['curuser']['usertype'] != 'admin') return $objResponse;

	$myAsterisk = new Asterisk();
	if($type == 'reload'){
		$r = $myAsterisk->reloadAsterisk();
		$objResponse->addAssign("divAsMsg","innerHTML","<span class='passed'>".$locate->Translate('asterisk have been reloaded')."</span");
	}elseif($type == "restart"){
		$myAsterisk->restartAsterisk();
		$objResponse->addAssign("divAsMsg","innerHTML","<span class='passed'>".$locate->Translate('asterisk have been restart')."</span");
	}elseif($type == "reboot"){
		exec ('sudo /sbin/shutdown -r now');
		$objResponse->addAssign("divSysMsg","innerHTML","<span class='passed'>".$locate->Translate('Server is rebooting')."...</span");
	}elseif($type == "shutdown"){
		exec ('sudo /sbin/shutdown -h now');
		$objResponse->addAssign("divSysMsg","innerHTML","<span class='passed'>".$locate->Translate('Server is shuting down')."...</span");
	}
	return $objResponse;
}

$xajax->processRequests();
?>
