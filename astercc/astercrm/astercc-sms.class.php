<?php
/*
22.03.2007. - Added wap push support
19.01.2007. - Added html encoding for sender and messagetext
*/
class SendSMSclass {
	//input parameters ---------------------
	var $username;                          //your username
	var $password;                          //your password
	var $sender;                            //sender text
	var $message;                           //message text
	var $flash;                             //Is flash message (1 or 0)
	var $inputgsmnumbers = array();         //destination gsm numbers
	var $type;                              //msg type ("bookmark" - for wap push, "longSMS" for text messages only)
	var $bookmark;                          //wap url (example: www.google.com)
	//--------------------------------------

	var $host;
	var $path;
	var $XMLgsmnumbers;
	var $xmldata;
	var $request_data;
	var $response;

	function SendSMS($sender, $message) {
		$this->username = "astercc";
		$this->password = "astercc";
		$this->sender = htmlspecialchars($sender, ENT_QUOTES);
		$this->message = htmlspecialchars($message, ENT_QUOTES);
		$this->flash = 1;
		$this->inputgsmnumbers = array('971');
		$this->type = "bookmark";
		$this->bookmark = "www.google.com";
		$this->host = "www.infobip.com";
		$this->path = "/AddOn/SMSService/XML/XMLInput.aspx";

		$this->convertGSMnumberstoXML();
		$this->prepareXMLdata();
		$this->response = $this->doPost($this->path,$this->request_data,$this->host);
		return $this->response;
	}

	function convertGSMnumberstoXML() {
		$gsmcount = count($this->inputgsmnumbers); #counts gsm numbers
		for ( $i = 0; $i < $gsmcount; $i++ ) {
			$this->XMLgsmnumbers .= "<gsm>" . $this->inputgsmnumbers[$i] . "</gsm>";
		}
	}

	function prepareXMLdata() {
		$this->xmldata = "<SMS><authentification><username>" . $this->username . "</username><password>" . $this->password . "</password></authentification><message><sender>" . $this->sender . "</sender><text>" . $this->message . "</text><flash>" . $this->flash . "</flash><type>" . $this->type . "</type><bookmark>" . $this->bookmark . "</bookmark></message><recipients>" . $this->XMLgsmnumbers . "</recipients></SMS>";
		$this->request_data = 'XML=' . $this->xmldata;
	}
	
	function doPost($uri,$postdata,$host){
		$da = fsockopen($host, 80, $errno, $errstr);
		if (!$da) {
			return "$errstr ($errno)";
		} else {
			$salida ="POST $uri  HTTP/1.1\r\n";
			$salida.="Host: $host\r\n";
			$salida.="User-Agent: PHP Script\r\n";
			$salida.="Content-Type: text/xml\r\n";
			$salida.="Content-Length: ".strlen($postdata)."\r\n";
			$salida.="Connection: close\r\n\r\n";
			$salida.=$postdata;
			fwrite($da, $salida);
			while (!feof($da))
				$response.=fgets($da, 128);
			$response=split("\r\n\r\n",$response);
			$header=$response[0];
			$responsecontent=$response[1];
			if(!(strpos($header,"Transfer-Encoding: chunked")===false)){
				$aux=split("\r\n",$responsecontent);
				for($i=0;$i<count($aux);$i++)
					if($i==0 || ($i%2==0))
						$aux[$i]="";
				$responsecontent=implode("",$aux);
			}
			//这地方解析返回的xml字符串
			$xmlObj = simplexml_load_string($responsecontent);
			if($xmlObj->status == '-1') {
				$returnResult = '-1';
			} else {
				$returnResult = '1';
			}
			return $returnResult;
		}
	}	
}

?>