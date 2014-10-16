<?php
/*******************************************************************************
* asterisk.server.php
* astercrm asterisk class

* Revision 0.0456  2007/11/9 10:33:00  modified by solo
* Desc: change .abc to .call

* Revision 0.0451  2007/10/24 20:33:00  modified by solo
* Desc: add function sendCall

********************************************************************************/
require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'phpagi-asmanager.php');

class Asterisk extends AGI_AsteriskManager{

	function dropCall($sID,$arrayPara){
		if (!isset($arrayPara['MaxRetries']) || $arrayPara['MaxRetries'] == '')
			$arrayPara['MaxRetries'] = 0;

		$callfile = "";
		$callfile = $callfile."Channel:".$arrayPara['Channel']."\r\n";
		$callfile = $callfile."WaitTime:".$arrayPara['WaitTime']."\r\n";
		$callfile = $callfile."Extension:".$arrayPara['Exten']."\r\n";
		$callfile = $callfile."Context:".$arrayPara['Context']."\r\n";
		$callfile = $callfile."Priority:".$arrayPara['Priority']."\r\n";
		$callfile = $callfile."MaxRetries:".$arrayPara['MaxRetries']."\r\n";
		$callfile = $callfile."CallerID:".$arrayPara['CallerID']."\r\n";
		$callfile = $callfile."ActionID:".$arrayPara['ActionID']."\r\n";

		// send accountcode	added by solo 2007-11-9
		if (isset($arrayPara['Account']))
			$callfile = $callfile."Account:".$arrayPara['Account']."\r\n";
		else
			$callfile = $callfile."Account:".$arrayPara['CallerID']."\r\n";	


		if ($arrayPara['Variable'] != '')
			foreach ( split("\|",$arrayPara['Variable']) as $strVar)
				$callfile = $callfile."SetVar: $strVar\r\n";


		$filename="/tmp/$sID.call";
		$handle=fopen($filename,"w+");
		fwrite($handle,$callfile);

//		system("chown asterisk.asterisk /tmp/$filename");
		@chmod   ($filename,   0777);
		system("mv $filename /var/spool/asterisk/outgoing/");
		return $callfile;
	}

	function sendCall($channel,
                       $exten=NULL, $context=NULL, $priority=NULL,
                       $application=NULL, $data=NULL,
                       $timeout=NULL, $callerid=NULL, $variable=NULL, $account=NULL, $async=NULL, $actionid=NULL){
		
      $req = "Action: Originate\r\n";
      $parameters = array('Channel'=>$channel);

      if($exten) $parameters['Exten'] = $exten;
	
	  //$parameters['Accountcode'] = $exten;

      if($context) $parameters['Context'] = $context;
      if($priority) $parameters['Priority'] = $priority;

      if($application) $parameters['Application'] = $application;
      if($data) $parameters['Data'] = $data;

      if($timeout) $parameters['WaitTime'] = $timeout;
      if($callerid) $parameters['CallerID'] = $callerid;
      if($variable) $parameters['Variable'] = $variable;
	  if($account) 
		  $parameters['Account'] = $account;
	  else
		  $parameters['Account'] = $callerid;


      if(!is_null($async)) $parameters['Async'] = ($async) ? 'true' : 'false';
      if($actionid) $parameters['ActionID'] = $actionid;
      foreach($parameters as $var=>$val)
        $req .= "$var: $val\r\n";
      $req .= "\r\n";

			//print $req;exit;
			fwrite($this->socket, $req);
	  return;
	}

	function getSipChannels(){
		global $config;
		$myAsterisk = new Asterisk();
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();
		$channels = $myAsterisk->Command("sip show channels");
		$myAsterisk->disconnect();
		return  $channels['data'];
	}

	function getChannels($verbose = null){
		global $config;
		$myAsterisk = new Asterisk();
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();
		$channels = $myAsterisk->Command("show channels");	
		$myAsterisk->disconnect();
		return  $channels['data'];
	}

	function execute($command){
		global $config;
		$myAsterisk = new Asterisk();
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();
		$channels = $myAsterisk->Command($command);	
		$myAsterisk->disconnect();
		return  $channels['data'];
	}

	function getCommandData($command){
		global $config;
		$myAsterisk = new Asterisk();
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();
		$channels = $myAsterisk->Command("show channels verbose");	
		$myAsterisk->disconnect();
		return  $channels['data'];
	}

	function getPeerIP($name, $type = 'sip'){
		global $config;
		$myAsterisk = new Asterisk();
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();
		$peer = $myAsterisk->Command($type." show peer ".$name);	
		$myAsterisk->disconnect();
		$peer = $peer['data'];
		$peer =split(chr(10),$peer);
		return $peer[31];
	}

	function getPeerStatus($name, $type = 'sip'){
		global $config;
		$myAsterisk = new Asterisk();
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();
		$peer = $myAsterisk->Command($type." show peer ".$name);	
		$myAsterisk->disconnect();
		$peer = $peer['data'];
		$peer =split(chr(10),$peer);
		return $peer[37];
	}

	
	function reloadAsterisk(){
		global $config;
		$myAsterisk = new Asterisk();
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();
		$r = $myAsterisk->Command(" reload ");	
		$myAsterisk->disconnect();
		return $r;
	}

	function restartAsterisk(){
		global $config;
		$myAsterisk = new Asterisk();
		$myAsterisk->config['asmanager'] = $config['asterisk'];
		$res = $myAsterisk->connect();
		$myAsterisk->Command(" restart now ");	
		//$myAsterisk->disconnect();
		return;
	}

	/*
	*	$spy:		监听方
	*	$exten:		被监听方
	*/
	function chanSpy($spy, $exten){
/*
Action: originate 
Channel: Local/300 
WaitTime: 30 
CallerId: "Web Call" <8881> 
Application: ChanSpy 
Data: IAX2/100|q
Async: yes 
*/
/*
$channel,
                       $exten=NULL, $context=NULL, $priority=NULL,
                       $application=NULL, $data=NULL,
                       $timeout=NULL, $callerid=NULL, $variable=NULL, $account=NULL, $async=NULL, $actionid=NULL
*/
		Asterisk::sendCall("local/$spy",null,null,null,"ChanSpy",$exten."|qb",30);
	}

	function zapSpy(){
	}

}
?>
