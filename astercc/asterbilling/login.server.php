<?php
/*******************************************************************************
********************************************************************************/
require_once ("login.common.php");
require_once ("db_connect.php");
require_once ('include/asterisk.class.php');
require_once ('include/common.class.php');
require_once ('include/astercrm.class.php');

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
	global $config;

	list ($_SESSION['curuser']['country'],$_SESSION['curuser']['language']) = split ("_", $aFormValues['locate']);	
	//get locate parameter
	$locate=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'login');	

	$objResponse = new xajaxResponse();
	global $locate;
	if ($config['system']['validcode'] == 'yes'){
		if (trim($aFormValues['code']) != $_SESSION["Checknum"]){
			$objResponse->addAlert('Invalid code');
			$objResponse->addScript('init();');
			return $objResponse;
		}
	}

	if (trim($aFormValues['username']) == "")
	{
		$objResponse->addAlert($locate->Translate("username_cannot_be_blank"));
		$objResponse->addScript('init();');
		return $objResponse;
	}
	if (trim($aFormValues['password']) == "")
	{
		$objResponse->addAlert($locate->Translate("password_cannot_be_blank"));
		$objResponse->addScript('init();');
		return $objResponse;
	}

	if (array_key_exists("username",$aFormValues))
	{
		if (ereg("[0-9a-zA-Z]+",$aFormValues['username']) && ereg("[0-9a-zA-Z]+",$aFormValues['password']))
		{
		  // passed
			return processAccountData($aFormValues);
		}else{
		  // error
			$objResponse->addAlert($locate->Translate("invalid_string"));
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

	list($_SESSION['curuser']['country'],$_SESSION['curuser']['language']) = split ("_", $language);	//get locate parameter

	$locate=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'login');			//init localization class
	$objResponse->addAssign("titleDiv","innerHTML",$locate->Translate("User title"));
	$objResponse->addAssign("usernameDiv","innerHTML",$locate->Translate("username")."&nbsp;&nbsp;&nbsp;");
	$objResponse->addAssign("passwordDiv","innerHTML",$locate->Translate("password")."&nbsp;&nbsp;&nbsp;");
	$objResponse->addAssign("remembermeDiv","innerHTML",$locate->Translate("Remember me"));
	$objResponse->addAssign("validcodeDiv","innerHTML",$locate->Translate("Valid Code")."&nbsp;&nbsp;&nbsp;");
	$objResponse->addAssign("loginButton","value",$locate->Translate("submit"));
	$objResponse->addAssign("loginButton","disabled",false);
	$objResponse->addAssign("onclickMsg","value",$locate->Translate("please_waiting"));
	$objResponse->addScript("xajax.$('username').focus();");
	$objResponse->addScript("imgCode = new Image;imgCode.src = 'showimage.php';document.getElementById('imgCode').src = imgCode.src;");

	if (isset($_COOKIE["username"])){
		$username = $_COOKIE["username"];
		$checked = true;
	}
	if (isset($_COOKIE["password"])) $password = $_COOKIE["password"];

	$objResponse->addAssign("username","value",$username);
	$objResponse->addAssign("password","value",$password);
	$objResponse->addAssign("rememberme","checked",$checked);
	$objResponse->addAssign("locate","value",$language);

	$objResponse->addAssign("divCopyright","innerHTML",Common::generateCopyright($skin));
	unset($_SESSION['curuser']);
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
	global $db,$config;

	list ($_SESSION['curuser']['country'],$_SESSION['curuser']['language']) = split ("_", $aFormValues['locate']);	
	//get locate parameter
	$locate=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'login');	

	$objResponse = new xajaxResponse();
	
	$bError = false;
	
	$loginError = false;

	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
		if ($_SERVER["HTTP_CLIENT_IP"]) {
			$proxy = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$proxy = $_SERVER["REMOTE_ADDR"];
		}
	} else {
		if (isset($_SERVER["HTTP_CLIENT_IP"])) {
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$ip = $_SERVER["REMOTE_ADDR"];
		}
	}

	$log = array();
	$log['action'] = 'login';
	$log['ip'] = $ip;
	$log['username'] = $aFormValues['username'];
	$log['usertype'] = 'clid';

	$query = "SELECT * FROM account_log WHERE ip='".$ip."' AND action='login' ORDER BY id DESC LIMIT 1";
	$res = $db->query($query);
	if($res->fetchInto($this_ip_log)){
		$failedtimes = $this_ip_log['failedtimes'];
	}

	if($failedtimes >= $config['system']['max_incorrect_login']  && $config['system']['max_incorrect_login'] > 0){
		$objResponse->addAlert($locate->Translate("login failed,your ip is locked for login"));
		$objResponse->addAssign("loginButton","value",$locate->Translate("submit"));
		$objResponse->addAssign("loginButton","disabled",false);
		return $objResponse;
	}

	if (!$bError)
	{	$query = "SELECT * from clid where clid ='".$aFormValues['username']."'";
		$res = $db->query($query);
		if($res->fetchInto($clid))
		{
			$log['account_id'] = $clid['id'];			

			if($clid['pin']  == $aFormValues['password'])
			{
				$log['status'] = 'success';
				$log['failedtimes'] = 0;
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
				$_SESSION['curuser']['username'] = trim($aFormValues['username']);
				$_SESSION['curuser']['usertype'] = "clid";
				$_SESSION['curuser']['clidid'] = $clid['id'];
				$_SESSION['curuser']['groupid'] = $clid['groupid'];
				list($_SESSION['curuser']['country'],$_SESSION['curuser']['language']) = split ("_", $aFormValues['locate']);

				$configstatus = common::read_ini_file($config['system']['astercc_path'].'/astercc.conf',$asterccConfig);
				if ($configstatus == -2){
					$html = "(fail to read ".$config['system']['astercc_path']."/astercc.conf)";	
					return $html;
				}else{
					$billingfield= trim($asterccConfig['system']['billingfield'] );
					if($billingfield == 'accountcode'){
						$_SESSION['curuser']['billingfield'] = $billingfield;
					}
				}
				//$objResponse->addAlert($locate->Translate("login_success"));
				$objResponse->addScript('window.location.href="cdr.php";');	
			}else{
				$log['failedtimes'] = $failedtimes + 1;
				$log['status'] = 'failed';
				$log['failedcause'] = 'incorrect password';
				$loginError = true;
			}
		}else{
			$log['failedtimes'] = $failedtimes + 1;
			$log['account_id'] = 0;
			$log['status'] = 'failed';
			$log['failedcause'] = 'notexistent clid';
			$loginError = true;
		}
		astercrm::insertAccountLog($log);
		if (!$loginError){
			return $objResponse;
		} else {
			$objResponse->addAlert($locate->Translate("login_failed"));
			$objResponse->addAssign("loginButton","value",$locate->Translate("submit"));
			$objResponse->addAssign("loginButton","disabled",false);
			return $objResponse;
		}
	} else {
		$objResponse->addAssign("loginButton","value",$locate->Translate("submit"));
		$objResponse->addAssign("loginButton","disabled",false);
	}
	
	return $objResponse;
}

$xajax->processRequests();
?>