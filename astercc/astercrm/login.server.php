<?php

/*******************************************************************************
* login.server.php
* 用户登入程序文件
* user login function page

* Public Functions List
									processForm

* Private Functions List
									processAccountData

* Revision 0.0456  2007/11/12 10:49:00  modified by solo
* Desc: add $_SESSION['curuser']['channel'], $_SESSION['curuser']['accountcode']

* Revision 0.045  2007/10/8 14:21:00  modified by solo
* Desc: add string check

* Revision 0.044  2007/09/10 14:21:00  modified by solo
* Desc: add $_SESSION['curuser']['usertype'] to save user type: admin | user
* 描述: 增加了保存用户权限的变量: admin | user, 保存在变量$_SESSION['curuser']['usertype']


* Revision 0.044  2007/09/7 19:55:00  modified by solo
* Desc: modify function init, use unset() to clean session, which means everytime user visit login page, he will log out automaticly
* 描述: 修改了init函数, 使用 unset() 函数清除session, 每当用户访问login时, 都会视为自动登出

* Revision 0.044  2007/09/7 17:55:00  modified by solo
* Desc: add some comments
* 描述: 增加了一些注释信息


********************************************************************************/
require_once ("login.common.php");
require_once ("db_connect.php");
require_once ('include/asterisk.class.php');
require_once ('include/astercrm.class.php');
require_once ('include/common.class.php');

/**
*  function to process form data
*
*  	@param $aFormValues	(array)			login form data
															$aFormValues['username']
															$aFormValues['password']
															$aFormValues['locate']
*	@return $objResponse
*/

function processForm($aFormValues)
{
	global $locate;
	$objResponse = new xajaxResponse();
	list ($_SESSION['curuser']['country'],$_SESSION['curuser']['language']) = split ("_", $aFormValues['locate']);

	//get locate parameter
	$locate=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'login');			//init localization class

	if (trim($aFormValues['username']) == "")
	{
		$objResponse->addAlert($locate->Translate("Username cannot be blank"));
		$objResponse->addScript('init();');
		return $objResponse;
	}
	if (trim($aFormValues['password']) == "")
	{
		$objResponse->addAlert($locate->Translate("Password cannot be blank"));
		$objResponse->addScript('init();');
		return $objResponse;
	}

	if (array_key_exists("username",$aFormValues))
	{
		if (ereg("[0-9a-zA-Z\@\.]+",$aFormValues['username']) && ereg("[0-9a-zA-Z]+",$aFormValues['password']))
		{
		  // passed
			return processAccountData($aFormValues);
		}else{
		  // error
			$objResponse->addAlert($locate->Translate("Invalid string"));
			$objResponse->addScript('init();');
			return $objResponse;
		}

	} else{
		$objResponse = new xajaxResponse();
		return $objResponse;
	}
}

/**
*  function to init login page
*
*  	@param $aFormValues	(array)			login form data
															$aFormValues['username']
															$aFormValues['password']
															$aFormValues['locate']
*	@return $objResponse
*  @session
															$_SESSION['curuser']['country']
															$_SESSION['curuser']['language']
*  @global
															$locate
*/

function init($aFormValue){

	$objResponse = new xajaxResponse();
	global $locate,$config;

	if (isset($_COOKIE["language"])) {
		$language = $_COOKIE["language"];
	}else{
		$language = $aFormValue['locate'];
	}

	list($_SESSION['curuser']['country'],$_SESSION['curuser']['language']) = split ("_", $language);

	//get locate parameter
	$locate=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'login');			//init localization class

	$login_div = '<img src="skin/default/images_'.$_SESSION['curuser']['country'].'/login.gif" onclick="document.getElementById(\'loginForm\').submit();" />';//onclick="form.submit(\'loginForm\');"

	$objResponse->addAssign("titleDiv","innerHTML",$locate->Translate("Title"));
	$objResponse->addAssign("logintip","innerHTML",$locate->Translate("logintip"));
	$objResponse->addAssign("usernameDiv","innerHTML",$locate->Translate("Username"));
	$objResponse->addAssign("passwordDiv","innerHTML",$locate->Translate("Password"));
	$objResponse->addAssign("remembermeDiv","innerHTML",$locate->Translate("Remember me"));
	$objResponse->addAssign("languageDiv","innerHTML",$locate->Translate("Language"));
	$objResponse->addAssign("loginDiv","innerHTML",$login_div);
	//$objResponse->addAssign("loginButton","value",$locate->Translate("Submit"));
	//$objResponse->addAssign("loginButton","disabled",false);
	//$objResponse->addAssign("onclickMsg","value",$locate->Translate("Please waiting"));

	$objResponse->addScript("xajax.$('username').focus();");
	$objResponse->addAssign("divCopyright","innerHTML",Common::generateCopyright($skin));
	//print_r($_COOKIE);exit;
	if (isset($_COOKIE["username"])){
		$username = $_COOKIE["username"];
		$checked = true;
	}
	if (isset($_COOKIE["password"])) $password = $_COOKIE["password"];

	$objResponse->addAssign("username","value",$username);
	$objResponse->addAssign("password","value",$password);
	$objResponse->addAssign("rememberme","checked",$checked);
	$objResponse->addAssign("locate","value",$language);

	/* --------------------- 计算用户在线时间 ------------------------*/
	if(!empty($_SESSION['curuser'])) {
		$identity = astercrm::calculateAgentOntime('logout',$_SESSION['curuser']['username']);
		if($identity != '') {
			unset($_SESSION['curuser']);
			unset($_SESSION['status']);
		}
	} else {
		unset($_SESSION['curuser']);
		unset($_SESSION['status']);
	}



	return $objResponse;
}

function setLang($f){
	$objResponse = new xajaxResponse();
	if (isset($_COOKIE["language"])) setcookie("language", $f['locate'], time() + 94608000);
	$objResponse->addScript("init()");
	return $objResponse;
}

/**
*  function to verify user data
*
*  	@param $aFormValues	(array)			login form data
															$aFormValues['username']
															$aFormValues['password']
															$aFormValues['locate']
*	@return $objResponse
*  @session
															$_SESSION['curuser']['username']
															$_SESSION['curuser']['extension']
															$_SESSION['curuser']['extensions']
															$_SESSION['curuser']['country']
															$_SESSION['curuser']['language']
															$_SESSION['curuser']['channel']
															$_SESSION['curuser']['accountcode']
*/
function processAccountData($aFormValues)
{
	global $db,$locate,$config;

	$objResponse = new xajaxResponse();

	$bError = false;

	$loginError = false;
	list($_SESSION['curuser']['country'],$_SESSION['curuser']['language']) = split ("_", $aFormValues['locate']);
	$locate=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'login');

	/* check whether the pear had been installed */
	$pear_exists_result = class_exists('PEAR');
	if(empty($pear_exists_result)) {
		$objResponse->addAlert($locate->Translate("Please install php pear"));
		return $objResponse;
	}

	if (!$bError)
	{
		//$query = "SELECT * FROM account WHERE username='" . $aFormValues['username'] . "'";
		//$res = $db->query($query);

		$row = astercrm::getRecordByField("username",$aFormValues['username'],"astercrm_account");
		if ($row['id'] != '' ){
			if ($row['password'] == $aFormValues['password'])
			{
				$identity = astercrm::calculateAgentOntime('login',trim($aFormValues['username']));
				if($identity){
					$update = astercrm::updateAgentOnlineTime('login',date('Y-m-d H:i:s'),$row['id']);
				}

				if ($aFormValues['rememberme'] == "forever"){
				// set cookies for three years
					setcookie("username", $aFormValues['username'], time() + 94608000);
					setcookie("password", $aFormValues['password'], time() + 94608000);
					setcookie("language", $aFormValues['locate'], time() + 94608000);
				}else{
				// destroy cookies
					setcookie("username", "", time()-3600);
					setcookie("password", "", time()-3600);
					setcookie("language", "", time()-3600);
					$username = '';
					$password = '';
					$language = 'en_US';
					$checked = false;
				}

				$_SESSION = array();
				$_SESSION['curuser']['username'] = trim($aFormValues['username']);
				$_SESSION['curuser']['extension'] = $row['extension'];
				$_SESSION['curuser']['usertype'] = $row['usertype'];
				$_SESSION['curuser']['usertype_id'] = $row['usertype_id'];
				$_SESSION['curuser']['accountid'] = $row['id'];
				$_SESSION['curuser']['accountcode'] = $row['accountcode'];
				$_SESSION['curuser']['agent'] = $row['agent'];
				$_SESSION['curuser']['callerid'] = trim($row['callerid']);
				$_SESSION['curuser']['update_online_interval'] = date("Y-m-d H:i:s");

				// added by solo 2007-10-90
				$_SESSION['curuser']['channel'] = $row['channel'];
				$_SESSION['curuser']['extensions'] = array();
				$_SESSION['curuser']['groupid'] = $row['groupid'];

				$privilege = array();
				if($row['usertype_id'] > 0){
					$privileges = $db->getAll("SELECT * FROM user_privileges WHERE user_type_id='".$row['usertype_id']."'");

					foreach($privileges as $p){
						$privilege[$p['page']][$p['action']] = 1;
					}
				}

				$_SESSION['curuser']['privileges'] = $privilege;

				if ($row['extensions'] != ''){
					$_SESSION['curuser']['extensions'] = split(',',$row['extensions']);
				}
				//check extensions if exists in account table
				foreach($_SESSION['curuser']['extensions'] as $key => $value){
					$exten_row = astercrm::getRecordByField("username",$value,"astercrm_account");
					if($exten_row['id'] == '' ){
						unset($_SESSION['curuser']['extensions'][$key]);
					}
				}

				// if it's a group admin, then add all group extension to it
				if ($row['usertype'] == 'groupadmin' || is_array($_SESSION['curuser']['privileges']['systemstatus'])  || is_array($_SESSION['curuser']['privileges']['import'])){
					$_SESSION['curuser']['memberExtens'] = array();
					$_SESSION['curuser']['memberNames'] = array();
					$_SESSION['curuser']['memberAgents'] = array();
					$groupList = astercrm::getGroupMemberListByID($row['groupid']);
					while	($groupList->fetchInto($groupRow)){
						$_SESSION['curuser']['memberExtens'][] = $groupRow['extension'];
						$_SESSION['curuser']['memberNames'][] = $groupRow['username'];
						if($groupRow['agent'] != ''){
							$_SESSION['curuser']['memberAgents'][] = $groupRow['agent'];
						}
					}
				}

				list($_SESSION['curuser']['country'],$_SESSION['curuser']['language']) = split ("_", $aFormValues['locate']);

				// get group information
				$_SESSION['curuser']['group'] = astercrm::getRecordByField("groupid",$row['groupid'],"astercrm_accountgroup");
				if($row['dialinterval'] != 0) {
					$_SESSION['curuser']['dialinterval'] = $row['dialinterval'];
				}else {
					$row_group = astercrm::getRecordByField("groupid",$row['groupid'],"astercrm_accountgroup");
					$_SESSION['curuser']['dialinterval'] = $_SESSION['curuser']['group']['agentinterval'];
				}

				if($_SESSION['curuser']['groupid'] > 0){
					$sql = "SELECT id,campaignname,queuename,queue_context,use_ext_chan FROM campaign WHERE queuename != '' AND groupid='".$_SESSION['curuser']['groupid']."' AND enable= 1 ORDER BY queuename ASC";
					$result = & $db->query($sql);

					$dataArray = array();
					while($row = $result->fetchRow()) {
						$dataArray[$row['id']] = $row;
					}
					$_SESSION['curuser']['campaign_queue'] = $dataArray;
				}

/*
	if you dont want check manager status and show device status when user login
	please uncomment these three line
*/
				//$objResponse->addAlert($locate->Translate("Login success"));
//				if($_SESSION['curuser']['agent'] != ''){
//					$msg = $locate->Translate("choose user mode");
//					$objResponse->addScript("selectmode('".$msg."')");
//					return $objResponse;
//				}
				//$_SESSION['error_report'] = $config['error_report']['error_report_level'];
				//$objResponse->addScript('window.location.href="portal.php";');
				//return $objResponse;


				//check AMI connection
				$myAsterisk = new Asterisk();
				$myAsterisk->config['asmanager'] = $config['asterisk'];

				$res = $myAsterisk->connect();


				//$html .= $locate->Translate("server_connection_test");
				if ($res){
					//$html .= '<font color=green>'.$locate->Translate("pass").'</font><br>';
					//$html .= '<b>'.$_SESSION['curuser']['extension'].' '.$locate->Translate("device_status").'</b><br>';
					//$html .= asterisk::getPeerIP($_SESSION['curuser']['extension']).'<br>';
					//$html .= asterisk::getPeerStatus($_SESSION['curuser']['extension']).'<br>';
					$v = $myAsterisk->Command("core show version");
					$v = explode(' ',$v['data']);
					$version = $v['2'];
					$_SESSION['asterisk']['version'] = $version;
					$version_arr = split('\.',$version);
					if($version_arr['1'] > 4){
						$_SESSION['asterisk']['paramdelimiter'] = ',';
					}else{
						$_SESSION['asterisk']['paramdelimiter'] = '|';
					}
				}else{
					$_SESSION['asterisk']['paramdelimiter'] = '|';
					//$html .= '<font color=red>'.$locate->Translate("no_pass").'</font>';
				}

				$_SESSION['error_report'] = $config['error_report']['error_report_level'];

				//clear socket_url session to
				$_SESSION['socket_url_flag'] = 'yes';
				$objResponse->addScript('window.location.href="portal.php";');
				return $objResponse;

				$html .= '<input type="button" value="'.$locate->Translate("continue").'" id="btnContinue" name="btnContinue" onclick="window.location.href=\'portal.php\';">';
				$objResponse->addAssign("formDiv","innerHTML",$html);
				$objResponse->addClear("titleDiv","innerHTML");
				$objResponse->addScript("xajax.$('btnContinue').focus();");

			} else{
				$loginError = true;
			}
		} else{
				$loginError = true;
		}


		if (!$loginError){
			return $objResponse;
		} else {
			$objResponse->addAlert($locate->Translate("login_failed"));
			$objResponse->addAssign("loginButton","value",$locate->Translate("Submit"));
			$objResponse->addAssign("loginButton","disabled",false);
			return $objResponse;
		}
	} else {
		$objResponse->addAssign("loginButton","value",$locate->Translate("Submit"));
		$objResponse->addAssign("loginButton","disabled",false);
	}

	return $objResponse;
}

#清除$_SESSION['curuser']['agent']值
//function clearDynamicMode() {
//	//echo $_SESSION['curuser']['agent'];exit;
//	$objResponse = new xajaxResponse();
//	$_SESSION['curuser']['agent'] = '';
//	$objResponse->addScript('window.location.href="portal.php";');
//	return $objResponse;
//}

$xajax->processRequests();
?>