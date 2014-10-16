<?php
/*******************************************************************************
* common.class.php
* 通用类
* common class

* Public Functions List

			generateCopyright	生成版权信息HTML代码
			generateManageNav	生成管理界面导航HTML代码
			generateTabelHtml	生成表格HTML代码
			read_ini_file
			write_ini_file

* Private Functions List


* Revision 0.0456  2007/11/15  modified by solo
* Desc: add two new function to operate ini file

* Revision 0.045  2007/10/18  modified by solo
* Desc: page created


********************************************************************************/
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0',false);
header('Pragma: no-cache');
session_cache_limiter('public, no-store');

session_set_cookie_params(0);
if (!session_id()) session_start();
setcookie('PHPSESSID', session_id());


require_once ('localization.class.php');

if ($_SESSION['curuser']['country'] != '' ){
	$GLOBALS['locate_common']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'common.class');
}else{
	$GLOBALS['locate_common']=new Localization('en','US','common.class');
}


class Common{

	function generateCopyright($skin){
		global $locate_common;

		$html .='
				<div class="end">
				<ul>
				<li>2007-2012 asterCRM - <a href="http://www.astercc.org" target="_blank">asterCRM home</a></li>
				<li>version: 0.09 in asterCC 0.22</li>
				</ul>
				</div>
				';
		return $html;
	}

	function generateManageNav($skin,$curcountry = 'en',$curuserlanguage = 'US'){
		//global $locate_common;
//		echo $curcountry;exit;
		$locate_common=new Localization($curcountry,$curuserlanguage,'common.class');
/*



	
		$html .= "<a href='contact.php' >".$locate_common->Translate("contact_manager")."</a> | ";
		
		$html .= "<a href='note.php' >".$locate_common->Translate("note_manager")."</a> | ";
		
		$html .= "<a href='diallist.php' >".$locate_common->Translate("diallist_manager")."</a> | ";

*/		
		$html = '
<div class="top_banner">
	<ul>
		<li><a href="import.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'import\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/import.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/import_sml.gif" alt="import" name="import" width="71" height="126" border="0" id="import" /></a></li>
		<li><a href="surveyresult.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'statisic\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/statisic.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/statisic_sml.gif" alt="statisic" name="statisic" width="71" height="126" border="0" id="statisic" /></a></li>
		<li><a href="account.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'extension\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/extension.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/extension_sml.gif" alt="extension" name="extension" width="71" height="126" border="0" id="extension" /></a></li>
		<li><a href="customer.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'customer\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/customer.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/customer_sml.gif" alt="customer" name="customer" width="71" height="126" border="0" id="customer" /></a></li>
		<li><a href="predictivedialer.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'dialer\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/dialer.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/dialer_sml.gif" alt="dialer" name="dialer" width="71" height="126" border="0" id="dialer" /></a></li>
		<li><a href="systemstatus.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'system\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/system.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/system_sml.gif" alt="system" name="system" width="71" height="126" border="0" id="system" /></a></li>
		<li><a href="survey.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'survey\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/survey.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/survey_sml.gif" alt="survey" name="survey" width="71" height="126" border="0" id="survey" /></a></li>
		<li><a href="diallist.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'diallist\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/diallist.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/diallist_sml.gif" alt="diallist" name="diallist" width="71" height="126" border="0" id="diallist" /></a></li>
		<li><a href="preferences.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'preference\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/preference.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/preference_sml.gif" alt="preference" name="preference" width="71" height="126" border="0" id="preference" /></a></li>
		<li><a href="portal.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'back\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/back.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/back_sml.gif" alt="back" name="back" width="71" height="126" border="0" id="back" /></a></li>
		<li><a href="login.php" onmouseout="MM_swapImgRestore()" onmouseover="MM_swapImage(\'logout\',\'\',\'skin/default/images_'.$_SESSION['curuser']['country'].'/logout.gif\',1)"><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/logout_sml.gif" alt="logout" name="logout" width="71" height="126" border="0" id="logout" /></a></li>
		<li><img src="skin/default/images_'.$_SESSION['curuser']['country'].'/logo_bn.gif"/></li>
	</ul>
</div>
<div style="clear:both">
	<a href="trunkinfo.php">'.$locate_common->Translate("Trunkinfo").'</a>&nbsp;&nbsp;&nbsp;<a href="cdr.php">'.$locate_common->Translate("CDR").'</a>&nbsp;&nbsp;&nbsp;<a href="speeddial.php">'.$locate_common->Translate("SpeedDial").'</a>&nbsp;&nbsp;&nbsp;<a href="report.php">'.$locate_common->Translate("Report").'</a>&nbsp;&nbsp;&nbsp;<a href="campaignresult.php">'.$locate_common->Translate("Campaign Result").'</a>&nbsp;&nbsp;&nbsp;<a href="queuestatus.php">'.$locate_common->Translate("Queue Status").'</a>&nbsp;&nbsp;&nbsp;<a href="agent.php">'.$locate_common->Translate("Agent Settings").'</a>&nbsp;&nbsp;&nbsp;<a href="knowledge.php">'.$locate_common->Translate("knowledge").'</a>&nbsp;&nbsp;&nbsp;<a href="dnc.php">'.$locate_common->Translate("DNC list").'</a>&nbsp;&nbsp;&nbsp;<a href="ticketcategory.php">'.$locate_common->Translate("Ticket Category").'</a>&nbsp;&nbsp;&nbsp;<a href="useronline.php">'.$locate_common->Translate("User Online").'</a>&nbsp;&nbsp;&nbsp;<a href="user_online.php">'.$locate_common->Translate("UserOnline Report").'</a>&nbsp;&nbsp;&nbsp;<a href="codes.php">'.$locate_common->Translate("Code").'</a>&nbsp;&nbsp;&nbsp;<a href="sms_templates.php">'.$locate_common->Translate("SMS Templates").'</a>&nbsp;&nbsp;&nbsp;<a href="user_types.php">'.$locate_common->Translate("User Type").'</a>&nbsp;&nbsp;&nbsp;<a href="agent_queue_logs.php">'.$locate_common->Translate("Agent Queue Log").'</a>
</div><br>
				';
		return $html;
	}

//	生成显示一个数组内容的HTML代码
	function generateTabelHtml($aDyadicArray,$thArray = null){
		if (!is_Array($aDyadicArray))
			return '';
		$html .= '<table class="groups_channel"  border="0" cellpadding="0" cellspacing="0"  width="98%">';
		$myArray = array_shift($aDyadicArray);
		$html .="<tr>";
		foreach ($myArray as $field){
			$html .= "<th>";
			$html .= $field;
			$html .= "</th>";
		}
		$html .="</tr>";

		foreach ($aDyadicArray as $myArray){
			$html .="<tr>";
			foreach ($myArray as $field){
				$html .= "<td>";
				$html .= $field;
				$html .= "</td>";
			}
			$html .="</tr>";
		}
		$html .= "</table>";
		return $html;
	}

    function read_ini_file($f, &$r)
    {
        $null = "";
        $r=$null;
        $first_char = "";
        $sec=$null;
        $comment_chars=";#";
        $num_comments = "0";
        $num_newline = "0";

        //Read to end of file with the newlines still attached into $f
        $f = @file($f);
        if ($f === false) {
            return -2;
        }
        // Process all lines from 0 to count($f)
        for ($i=0; $i<@count($f); $i++)
        {
            $w=@trim($f[$i]);
            $first_char = @substr($w,0,1);
            if ($w)
            {
                if ((@substr($w,0,1)=="[") and (@substr($w,-1,1))=="]") {
                    $sec=@substr($w,1,@strlen($w)-2);
                    $num_comments = 0;
                    $num_newline = 0;
                }
                else if ((stristr($comment_chars, $first_char) == true)) {
                    $r[$sec]["Comment_".$num_comments]=$w;
                    $num_comments = $num_comments +1;
                }                
                else {
                    // Look for the = char to allow us to split the section into key and value
                    $w=@explode("=",$w);
                    $k=@trim($w[0]);
                    unset($w[0]);
                    $v=@trim(@implode("=",$w));
                    // look for the new lines
                    if ((@substr($v,0,1)=="\"") and (@substr($v,-1,1)=="\"")) {
                        $v=@substr($v,1,@strlen($v)-2);
                    }
                    
                    $r[$sec][$k]=$v;
                    
                }
            }
            else {
                $r[$sec]["Newline_".$num_newline]=$w;
                $num_newline = $num_newline +1;
            }
        }
        return 1;
    }

    function beginsWith( $str, $sub ) {
        return ( substr( $str, 0, strlen( $sub ) ) === $sub );
    } 

	
    function write_ini_file($path, $assoc_arr) {
        $content = "";
        foreach ($assoc_arr as $key=>$elem) {
            if (is_array($elem)) {
                if ($key != '') {
                    $content .= "[".$key."]\r\n";                    
                }
                
                foreach ($elem as $key2=>$elem2) {
                    if (Common::beginsWith($key2,'Comment_') == 1 && Common::beginsWith($elem2,';')) {
                        $content .= $elem2."\r\n";
                    }
                    else if (Common::beginsWith($key2,'Newline_') == 1 && ($elem2 == '')) {
                        $content .= $elem2."\r\n";
                    }
                    else {
                        $content .= $key2." = ".$elem2."\r\n";
                    }
                }
            }
            else {
                $content .= $key." = ".$elem."\r\n";
            }
        }

        if (!$handle = fopen($path, 'w')) {
            return -2;
        }
        if (!fwrite($handle, $content)) {
            return -2;
        }
        fclose($handle);
        return 1;
    }

}
?>