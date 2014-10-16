<?  
	header('Content-Type: text/html; charset=utf-8');
	require_once('systemstatus.common.php');
	require_once('systemstatus.server.php');	

	$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'systemstatus');

	$reseller = astercc::readField('resellergroup','resellername','id',$_SESSION['curuser']['resellerid']);
	
	$callshop = astercc::readField('accountgroup','groupname','id',$_SESSION['curuser']['groupid']);
	
	$group_row = astercrm::getRecord($_SESSION['curuser']['groupid'],'accountgroup');	
	
	if ( $group_row['grouplogo'] != '' && $group_row['grouplogostatus'] ){
		$logoPath = $config['system']['upload_file_path'].'/callshoplogo/'.$group_row['grouplogo'];
		if (is_file($logoPath)){
			$titleHtml = '<img src="'.$logoPath.'" style="float:left;" width="80" height="80">';
		}
	}
	if ( $group_row['grouptitle'] != ''){
		$titleHtml .= '<h1 style="padding: 0 0 0 0;position: relative;font-size: 16pt;">'.$group_row['grouptitle'].'</h1>';
	}
	if ( $group_row['grouptagline'] != ''){
		$titleHtml .= '<h2 style="padding: 0 0 0 0;position: relative;font-size: 11pt;">'.$group_row['grouptagline'].'</h2>';
	}

	if (strstr($_REQUEST['peer'],'local/')) { //for callback
		$peer = ltrim($peer,'local/');
		foreach ($_SESSION['callbacks'] as $key => $callback) {
			if( $key == $peer.$callback['legA'] && $callback['legB'] == $peer ){
				$leg = $callback['legA'];
			}
		}
	}else{
		$peer = trim($_REQUEST['peer']);
		$leg = trim($_REQUEST['leg']);
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Language" content="utf-8" />
		<LINK href="skin/default/css/layout.css" type=text/css rel=stylesheet>
		<LINK href="skin/default/css/dragresize.css" type=text/css rel=stylesheet>
		<script type="text/javascript" src="js/dragresize.js"></script>
		<script type="text/javascript" src="js/dragresizeInit.js"></script>
		<TITLE> <? echo $locate->Translate("Receipt").'-'; echo $peer;?></TITLE>
		<style rel="stylesheet" type="text/css" media="all" />
			body {
			margin: 9px;
			padding: 0;
			color: black;
			text-decoration: none;
			font-size: 12px;
			font-family: "Courier New";
			}
			
			#div_exten { 
					width:100px; 
					height:25px; 
					background:gainsboro; 
					margin-left: auto;
					margin-right:auto;
			} 

			#extenBtnU { line-height: 25px; list-style-type: none; margin-top:5px;margin:0;padding:0;}      
			#extenBtnU a { display: block; width: 100px; text-align:left; font-size:12px;} 
			#extenBtnU a:link { color:#145b7d; text-decoration:none;margin:0;}
			#extenBtnU a:link img { border:0px;} 
			#extenBtnU a:visited {color:#145b7d;text-decoration:none;margin:0;}  
			#extenBtnU a:visited img { border:0px;}
			#extenBtnU a:hover {color:#993300;text-decoration:none;}              
			#extenBtnU li {float: left;}                 
			#extenBtnU li a:hover{background:gainsboro;} 
			#extenBtnU li ul {line-height: 18px; background:white; list-style-type: none;text-align:left;left: -999em; width:85px; position: absolute;border-width: 1px;border-top-style: none;border-right-style: solid;border-bottom-style: solid;border-left-style: solid; padding:1px;}      
			#extenBtnU li ul a{display:block; width: 85px;text-align:left;padding-left:0px;}                 
			#extenBtnU li ul a:link {color:#0d5097; text-decoration:none; font-size:11px}                                   
			#extenBtnU li ul a:visited {color:#0d5097;text-decoration:none;font-size:11px}                                 
			#extenBtnU li ul a:hover {color:#FFF;text-decoration:none;font-weight:normal;background:lightblue ;}
			#extenBtnU li:hover ul {left: auto;}
			#extenBtnU li.sfhover ul {left: auto;}
		</style>
	</head>
 <SCRIPT LANGUAGE="JavaScript">
		<!--

		-->
 </SCRIPT>

 <BODY onload="dragresize.apply(document);">
 <? if (isset($titleHtml)){
		$titleHtml .= '';
		echo '<div id="divReceiptTitle" name="divReceiptTitle" style="position:relative;top:2px;height:80px;">'.$titleHtml.'</div><div style="position:relative;left:0px;display:block;"><hr color="#F1F1F1"></div>';
	}
	$xajax->printJavascript('include/');
?> 
	<div id="divPrint" align="right">
		<input type="button" onclick="window.close();opener.btnClearOnClick('<? echo $peer; ?>',document.getElementById('payType').value);" value="<? echo $locate->Translate("Pay");?>">&nbsp;
		<? echo $locate->Translate("by");?>&nbsp;
		<select id="payType" name="payType">
			<option value="cash"><? echo $locate->Translate("Cash");?></option>
			<option value="credit card"><? echo $locate->Translate("Credit card");?></option>
			<option value="debit card"><? echo $locate->Translate("Debit card");?></option>
			<option value="promotion"><? echo $locate->Translate("Promotion");?></option>
			<option value="other"><? echo $locate->Translate("Other");?></option>
		</select>&nbsp;<input type="button" value="<? echo $locate->Translate("Refresh");?>" onclick="window.location.reload();">
		<input type="button" onclick="document.getElementById('divPrint').style.display='none';window.print();document.getElementById('divPrint').style.display='';" value="<? echo $locate->Translate("Print");?>">&nbsp;&nbsp;
	</div>

	<div id="divMain" style="position:relative;">
	<div>&nbsp;<? echo $locate->Translate("Reseller");?>:&nbsp;<?echo $reseller;?>
	   <br>
	   &nbsp;<? echo $locate->Translate("Callshop");?>:&nbsp;<?echo $callshop;?>
	   <br>
	   &nbsp;<? echo $locate->Translate("Operator");?>:&nbsp;<?echo $_SESSION['curuser']['username'];?>
	   <? if($_REQUEST['customername'] != '') echo "<br>&nbsp;".$locate->Translate("Member")."&nbsp;:";?><?echo $_REQUEST['customername'];?>
	</div>
	</div>
	<div style="position:relative;">
  <table  width="100%" border="1" align="center" class="adminlist" style="font-size: 12px;">
    <tr><td colspan="6">&nbsp;</td></tr>
	<tr>
		<th width="15%"><? echo $locate->Translate("Phone");?></th>		
		<th width="20%"><? echo $locate->Translate("Start at");?></th>
		<th width="10%" align="center"><? echo $locate->Translate("Billsec");?></th>
		<th width="15%"><? echo $locate->Translate("Destination");?></th>
		<th width="20%"><? echo $locate->Translate("Rate");?></th>
		<th width="10%" align="center"><? echo $locate->Translate("Price");?></th>
		<th width="10%" align="center"><? echo $locate->Translate("Discount");?></th>
	</tr>
	<?
	
	  $total_price = 0;
	  $records = astercc::readUnbilled($peer,$leg,$_SESSION['curuser']['groupid']);
	  while	($records->fetchInto($myreceipt)) {
		  $bgcolor = '';
		  if($myreceipt['setfreecall'] == 'yes') {
			  $bgcolor = 'bgcolor="#d5c59f"';
              $myreceipt['credit'] = '0.00';
		  }
		  $ratedesc = astercc::readRateDesc($myreceipt['memo']).'&nbsp;';
		  $content = '<tr id="rcdr-'.$myreceipt['id'].'" '.$bgcolor.'>';
		  if ($peer == $myreceipt['dst']){
			  if ($myreceipt['billsec'] == 0)
				  $content .= '<td><div><UL id="extenBtnU"><LI><a href="###"><img src="images/noanswer.gif">'.$myreceipt['src'].'</a><UL><A href="javascript:void(null)" onclick="javascript:xajax_removeReceipt(\''.$myreceipt['id'].'\');">&nbsp;<font size="2px">'.$locate->Translate("Hidden").'</font></A></UL></LI></UL></div></td>';
			  else
				  $content .= '<td><UL id="extenBtnU"><LI><a href="###"><img src="images/inbound.gif">'.$myreceipt['src'].'</a><UL><A href="javascript:void(null)" onclick="javascript:xajax_setFreeCallPage(\''.$myreceipt['id'].'\')">&nbsp;<font size="2px">'.$locate->Translate("Free call").'</font></A><A href="javascript:void(null)" onclick="javascript:xajax_removeReceipt(\''.$myreceipt['id'].'\');">&nbsp;<font size="2px">'.$locate->Translate("Hidden").'</font></A></UL></LI></UL></td>';
		  }else{
			  if ($myreceipt['billsec'] == 0)
				  $content .= '<td><UL id="extenBtnU"><LI><a href="###"><img src="images/noanswer.gif">'.$myreceipt['dst'].'</a><UL><A href="javascript:void(null)" onclick="javascript:xajax_removeReceipt(\''.$myreceipt['id'].'\');">&nbsp;<font size="2px">'.$locate->Translate("Hidden").'</font></A></UL></LI></UL></td>';
			  else
				  $content .= '<td><UL id="extenBtnU"><LI><a href="###"><img src="images/outbound.gif">'.$myreceipt['dst'].'</a><UL><A href="javascript:void(null)" onclick="javascript:xajax_setFreeCallPage(\''.$myreceipt['id'].'\')">&nbsp;<font size="2px">'.$locate->Translate("Free call").'</font></A><A href="javascript:void(null)" onclick="javascript:xajax_removeReceipt(\''.$myreceipt['id'].'\');">&nbsp;<font size="2px">'.$locate->Translate("Hidden").'</font></A></UL></LI></UL></td>';
		  }
		  $content .= '
					<td>'.$myreceipt['calldate'].'</td>
					<td align="right">'.astercrm::FormatSec($myreceipt['billsec']).'</td>
					<td align="right">'.$myreceipt['destination'].'</td>
					<td align="right">'.$ratedesc.'</td>
					<td id="rprice-'.$myreceipt['id'].'" align="right">'.astercc::creditDigits($myreceipt['credit']).'</td>
					<td align="right">'.astercc::creditDigits($_REQUEST['discount'],3).'</td>
				</tr>';
		  echo $content;
		    if($myreceipt['setfreecall'] == 'no'){
			   $total_price += $myreceipt['credit'];
			}
	  }
	  $total_price_ori = $total_price;
      $total_price = $total_price * (1-$_REQUEST['discount']);
	  $total_price = astercc::creditDigits($total_price,2);
	?>
	<tr><td><? echo $locate->Translate("Total");?>:</td>
		<td colspan="5" align="right" id="total_price"><? echo $total_price; ?></td><input id="total_price_ori" type="hidden" value="<? echo $total_price_ori; ?>"><input id="discount" type="hidden" value="<? echo $_REQUEST['discount']; ?>">
	</tr>
  </table>
  </div>
  <div id="formDiv"  class="formDiv drsElement" 
				style="left: 250px; top: 200px;width:300px;"></div>
  <div id="copyright" style="background-repeat:repeat-x;height:64px;margin-top:10px;text-align:center;">
				<ul>
				<li>2007-2010 asterBilling</li>
				<li>version: 0.16 in asterCC 0.21</li>
				</ul>
  </div>
  </div>
 </BODY>
</html>
