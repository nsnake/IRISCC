<?
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
				<hr style="clear:both;border: 0px;width: 80%;">
				<div align="center">
					<table class="copyright" id="tblCopyright">
					<tr>
						<td>
							©2007-2012 astercc - <a href="http://www.astercc.org" target="_blank">asterbilling home</a><br>
							version: 0.18 in asterCC 0.22
						</td>
					</tr>
					</table>
				</dvi>
				';
		return $html;
	}

	function generateManageNav($skin){
		global $locate_common,$config;
/*



	
		$html .= "<a href='contact.php' >".$locate_common->Translate("contact_manager")."</a> | ";
		
		$html .= "<a href='note.php' >".$locate_common->Translate("note_manager")."</a> | ";
		
		$html .= "<a href='diallist.php' >".$locate_common->Translate("diallist_manager")."</a> | ";

*/
		$html .= '<div id="pagewidth">';
		$html .= '<div class="">';
		$html .= $locate_common->Translate("Username").':'.$_SESSION['curuser']['username'];
		$html .= '&nbsp;&nbsp;'.$locate_common->Translate("User Type").':'.$_SESSION['curuser']['usertype'];
		$html .= '</div>';

		$html .= '<div id="header"><ul>';

		$aryMenu = array();
		$aryMenu['account'] = array("link"=>"account.php","title"=> $locate_common->Translate("Account"));
		$aryMenu['accountgroup'] = array("link"=>"accountgroup.php","title"=> $locate_common->Translate("Account Group"));
		$aryMenu['resellergroup'] = array("link"=>"resellergroup.php","title"=> $locate_common->Translate("Reseller Group"));
		$aryMenu['report'] = array("link"=>"checkout.php","title"=> $locate_common->Translate("Report"));
//		$aryMenu['statistic'] = array("link"=>"statistic.php","title"=> $locate_common->Translate("Statistic"));
		$aryMenu['customerrate'] = array("link"=>"rate.php","title"=> $locate_common->Translate("Rate to Customer"));
		$aryMenu['callshoprate'] = array("link"=>"callshoprate.php","title"=> $locate_common->Translate("Rate to Callshop"));
		$aryMenu['resellerrate'] = array("link"=>"resellerrate.php","title"=> $locate_common->Translate("Rate to Reseller"));
		$aryMenu['clid'] = array("link"=>"clid.php","title"=> $locate_common->Translate("Clid"));
		$aryMenu['import'] = array("link"=>"import.php","title"=> $locate_common->Translate("Import"));
		$aryMenu['cdr'] = array("link"=>"cdr.php","title"=> $locate_common->Translate("CDR"));
		$aryMenu['credithistory'] = array("link"=>"credithistory.php","title"=> $locate_common->Translate("Credit History"));
		if($config['customers']['enable']){
			$aryMenu['customers'] = array("link"=>"customers.php","title"=> $locate_common->Translate("customer"));		
			$aryMenu['discount'] = array("link"=>"discount.php","title"=> $locate_common->Translate("discount"));	
		}

		$aryMenu['account_log'] = array("link"=>"account_log.php","title"=> $locate_common->Translate("account_log"));

		$aryMenu['system'] = array("link"=>"system.php","title"=> $locate_common->Translate("system"));
		$aryMenu['profile'] = array("link"=>"profile.php","title"=> $locate_common->Translate("profile"));
		$aryMenu['systemstatus'] = array("link"=>"systemstatus.php","title"=> $locate_common->Translate("systemstatus"));
		$aryMenu['curcdr'] = array("link"=>"curcdr.php","title"=> $locate_common->Translate("curcdr"));
		$aryMenu['delete_rate'] = array("link"=>"delete_rate.php","title"=> $locate_common->Translate("delete_rate"));

		if ($_SESSION['curuser']['usertype'] == 'admin'){
			if($config['customers']['enable']){
				$aryCurMenu =array('account','accountgroup','resellergroup','report','customerrate','callshoprate','resellerrate','clid','import','cdr','credithistory','customers','discount','account_log','curcdr','system','delete_rate');
			}else{
				$aryCurMenu =array('account','accountgroup','resellergroup','report','customerrate','callshoprate','resellerrate','clid','import','cdr','credithistory','account_log','curcdr','system','delete_rate');
			}
			$html .= common::generateNavMenu($aryMenu,$aryCurMenu);
		}elseif($_SESSION['curuser']['usertype'] == 'reseller'){
			if($config['customers']['enable']){
				$aryCurMenu = array('account','accountgroup','report','customerrate','callshoprate','resellerrate','clid','import','cdr','credithistory','customers','discount','profile','curcdr');
			}else{
				$aryCurMenu = array('account','accountgroup','report','customerrate','callshoprate','resellerrate','clid','import','cdr','credithistory','profile','curcdr');
			}
			$html .= common::generateNavMenu($aryMenu,$aryCurMenu);
		}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
			if($config['customers']['enable']){
				if($config['system']['sysstatus_new_window'] == 'yes'){
					$aryCurMenu = array('account','report','customerrate','callshoprate','clid','import','cdr','credithistory','customers','discount','profile','curcdr');
				}else{
					$aryCurMenu = array('account','report','customerrate','callshoprate','clid','import','cdr','credithistory','customers','discount','profile','systemstatus','curcdr');
				}
			}else{
				if($config['system']['sysstatus_new_window'] == 'yes'){
					$aryCurMenu = array('account','report','customerrate','callshoprate','clid','import','cdr','credithistory','profile','curcdr');
				}else{
					$aryCurMenu = array('account','report','customerrate','callshoprate','clid','import','cdr','credithistory','profile','systemstatus','curcdr');
				}
			}
			$html .= common::generateNavMenu($aryMenu,$aryCurMenu);
		}elseif($_SESSION['curuser']['usertype'] == 'clid'){
			$aryCurMenu = array('clid','cdr','credithistory','curcdr');
			$html .= common::generateNavMenu($aryMenu,$aryCurMenu);
		}else{ // operator
			if($config['customers']['enable']){
				if($config['system']['sysstatus_new_window'] == 'yes'){
					$aryCurMenu = array('report','customerrate','customers','discount','curcdr');
				}else{
					$aryCurMenu = array('report','customerrate','customers','discount','systemstatus','curcdr');
				}
			}else{
				if($config['system']['sysstatus_new_window'] == 'yes'){
					$aryCurMenu = array('report','customerrate','curcdr');
				}else{
					$aryCurMenu = array('report','customerrate','systemstatus','curcdr');
				}
			}
			$html .= common::generateNavMenu($aryMenu,$aryCurMenu);
		}

		if($_SESSION['curuser']['usertype'] == 'clid'){
			$html .= '<li><a href="login.php" onclick="if (confirm(\''.$locate_common->Translate("are u sure to exit").'?\')){}else{return false;}">'.$locate_common->Translate("Logout").'</li>';
		}else{
			if ($_SESSION['curuser']['usertype'] == 'admin' or $_SESSION['curuser']['usertype'] == 'reseller'){
				$html .= '<li><a href="manager_login.php" onclick="if (confirm(\''.$locate_common->Translate("are u sure to exit").'?\')){}else{return false;}">'.$locate_common->Translate("Logout").'</li>';
			}
		}
		$html .= '</ul></div>';
		$html .= '</div>';
		return $html;
	}

	function generateNavMenu($aryMenu,$aryCurMenu = ''){
		$html = '';
		if ($aryCurMenu == ""){
			foreach ($aryMenu as $key=>$val){
				$html  .= '<li><a title="'.$aryMenu[$key]['title'].'" href="'.$aryMenu[$key]['link'].'" class="'.$key.'">'.$aryMenu[$key]['title'].'</a></li>';
			}
		}else{
			foreach ($aryCurMenu as $key){
				$html  .= '<li><a title="'.$aryMenu[$key]['title'].'" href="'.$aryMenu[$key]['link'].'" class="'.$key.'">'.$aryMenu[$key]['title'].'</a></li>';
			}
		}
		//echo $html;exit;
		return $html;
	}

//	生成显示一个数组内容的HTML代码
	function generateTabelHtml($aDyadicArray,$thArray = null){
		if (!is_Array($aDyadicArray))
			return '';
		$html .= "<table class='myTable'>";
		$myArray = array_shift($aDyadicArray);
		foreach ($myArray as $field){
			$html .= "<th>";
			$html .= $field;
			$html .= "</th>";
		}

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