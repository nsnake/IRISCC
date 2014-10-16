<?php
/*******************************************************************************
* profile.server.php

* 配置管理系统后台文件
* profile background management script

* Function Desc
	provide profile management script

* 功能描述
	提供配置管理脚本

* Function Desc
		init				初始化页面元素

* Revision 0.0057  2009/03/28 15:47:00  last modified by donnie
* Desc: page created
********************************************************************************/
require_once ("db_connect.php");
require_once ("profile.common.php");
require_once ("include/astercrm.class.php");
require_once ("include/paypal.class.php");
require_once ('include/xajaxGrid.inc.php');

/**
*  initialize page elements
*
*/

function init($get=''){	
	global $config,$locate;
	$objResponse = new xajaxResponse();

	if($get != ''){
		$get = rtrim($get,',');
		$get = split(',',$get);
		foreach($get as $item_tmp){
			$item = split(':',$item_tmp);
			$get_item[$item[0]] = $item[1];
		}
	}

	$rechargeEable = true;
	if($_SESSION['curuser']['usertype'] == 'reseller'){		
	
		$paymentinfoHtml = paymentInfoHtml();
		$objResponse->addAssign("paymentInfo","innerHTML",$paymentinfoHtml);

		if( $config['epayment']['epayment_status'] != 'enable' || $config['epayment']['paypal_payment_url'] == '' || $config['epayment']['paypal_account'] == '' || $config['epayment']['pdt_identity_token'] == '' || $config['epayment']['asterbilling_url'] == '' || $config['epayment']['amount'] == '' || $config['epayment']['currency_code'] == ''){
			$rechargeEable = false;
		}else{
			$identity_token = $config['epayment']['pdt_identity_token'];
			$receiver_email = $config['epayment']['paypal_account'];
			$currency_code = $config['epayment']['currency_code'];
		}
	}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
		$reseller_row = astercrm::getRecordByID($_SESSION['curuser']['resellerid'],'resellergroup');
		
		if($reseller_row['epayment_status'] != 'enable'){
			$rechargeEable = false;
		}else{			
			$identity_token = $reseller_row['epayment_identity_token'];
			$receiver_email = $reseller_row['epayment_account'];
			$currency_code = $config['epayment']['currency_code'];
		}
	}	
	$objResponse->addAssign("divNav","innerHTML",common::generateManageNav($skin,$_SESSION['curuser']['country'],$_SESSION['curuser']['language']));

	$objResponse->addAssign("divCopyright","innerHTML",common::generateCopyright($skin));

	$infoHtml = InfomationHtml();
	$objResponse->addAssign("info","innerHTML",$infoHtml);

	if($rechargeEable){
		$rechargeInfoHtml = 
				'<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="600">
				  <tr>
					<td width="26%" height="39" class="td font" align="center">'.$locate->Translate('Recharge By Paypal').'
					</td>
					<td width="74%" class="td font" align="center">&nbsp;</td>
				  </tr>
					<tr><td height="10" class="td"></td>
					<td class="td font" align="center">&nbsp;</td>
				  </tr>
				</table>
				<table border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="600">
					<tr bgcolor="#F7F7F7">
					<td align="center" valign="top"><b>';

		if($get_item["action"] == 'success'){
			if($get_item['tx'] != ''){
				$txn_res = astercrm::getRecordByField('epayment_txn_id',$get_item['tx'],'credithistory');
				
			// check that txn_id has not been previously processed
				if($txn_res['id'] > 0){
					$rechargeInfoHtml .= $locate->Translate('payment_success');
				}else{			

					if( $identity_token != ''){
						$p = new paypal_class;
						$p->verify_url = $config['epayment']['paypal_verify_url'];
						if($config['epayment']['pdt_log']){
							$return = $p->paypal_pdt_return($get_item['tx'],$identity_token,true);
						}else{
							$return = $p->paypal_pdt_return($get_item['tx'],$identity_token);
						}

						if($return['flag'] == 'SUCCESS'){
							$errorFlag = 0;
							// check that receiver_email is your Primary PayPal email
							if($return['pdt']['receiver_email'] != $receiver_email){
								$rechargeInfoHtml .= $locate->Translate('payment_receiver_error').'</br>';
								$errorFlag += 1;
							}

							// check that payment_amount/payment_currency are correct
							if($return['pdt']['mc_currency'] != $currency_code){
								$rechargeInfoHtml .= $locate->Translate('payment_currency_error').'</br>';
								$errorFlag += 1;
							}

							if($return['pdt']['payment_status'] == "Completed"){
								
								if($errorFlag > 0){
									$rechargeInfoHtml .= $locate->Translate('payment_order_error')."</br>".$locate->Translate('payment_may_completed');
								}else{
									// process Order 不再用pdt处理订单,等待ipn处理
									//$process_res = processOrder($return['pdt']);
									sleep(1);
									$infoHtml = InfomationHtml();
									$objResponse->addAssign("info","innerHTML",$infoHtml);
									$rechargeInfoHtml .= $locate->Translate('payment_success');		
								}
							}else{
								$rechargeInfoHtml .= $locate->Translate('payment_failed');
							}
							
						}else{ //PDT return failed
							$rechargeInfoHtml .= $locate->Translate('payment_return_failed');
						}
					}			
				}
			}else{
				$rechargeInfoHtml = rechargeHtml();
				$objResponse->addAssign("rechargeInfo","innerHTML",$rechargeInfoHtml);
				return $objResponse;
			}
		}elseif($get_item["action"] == 'cancel'){
			$rechargeInfoHtml .= $locate->Translate('payment_canceled');
		}else{		
			$rechargeInfoHtml = rechargeHtml();
			$objResponse->addAssign("rechargeInfo","innerHTML",$rechargeInfoHtml);
			return $objResponse;
		}

		$rechargeInfoHtml.=	'</b>&nbsp;&nbsp;&nbsp;<a href="profile.php" >'.$locate->Translate('Return').'</a></td></tr></table>';
		
		$objResponse->addAssign("rechargeInfo","innerHTML",$rechargeInfoHtml);
	}

	return $objResponse;
}

function InfomationHtml(){
	global $locate;
	if($_SESSION['curuser']['usertype'] == 'reseller'){
		$reseller_row = astercrm::getRecordByID($_SESSION['curuser']['resellerid'],'resellergroup');
		$balance = $reseller_row['creditlimit'] - $reseller_row['curcredit'];
		$html = '<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="600">
				  <tr>
					<td width="25%" height="39" class="td font" align="left">
						'.$locate->Translate('Reseller Infomation').'
					</td>
					<td width="75%" class="td font" align="center">&nbsp;</td>
				  </tr>
					<tr><td height="10" class="td"></td>
					<td class="td font" align="center">&nbsp;</td>
				  </tr>
				</table>
				<table border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="600"> 
				  <tr bgcolor="#F7F7F7">
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Reseller name').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$reseller_row['resellername'].'</b></div></td>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Accountcode').':&nbsp;&nbsp;</div></td>
					<td width="30%" align="center" valign="top" >'.$reseller_row['accountcode'].'</td>
				  </tr>
				  <tr>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Limittype').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$reseller_row['limittype'].'</b></td>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Allowcallback').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$reseller_row['allowcallback'].'</b></td>	
				  </tr>
				  <tr bgcolor="#F7F7F7">
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Callshop cost').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$reseller_row['credit_group'].'</b></td>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Clid cost').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$reseller_row['credit_clid'].'</b></td>	
				  </tr>
				  <tr>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Total cost').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$reseller_row['credit_reseller'].'</b></td>	
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Current cost').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$reseller_row['curcredit'].'</b></td>	
				  </tr>
				  <tr bgcolor="#F7F7F7">
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Credit limit').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$reseller_row['creditlimit'].'</b></td>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Balance').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$balance.'</b></td>    	
				  </tr>
			</table>';
		
	}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
		$group_row = astercrm::getRecordByID($_SESSION['curuser']['groupid'],'accountgroup');
		$balance = $group_row['creditlimit'] - $group_row['curcredit'];
		$html = '<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="600">
				  <tr>
					<td width="25%" height="39" class="td font" align="left">
						'.$locate->Translate('Group Infomation').'
					</td>
					<td width="75%" class="td font" align="center">&nbsp;</td>
				  </tr>
					<tr><td height="10" class="td"></td>
					<td class="td font" align="center">&nbsp;</td>
				  </tr>
				</table>
				<table border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="600"> 
				  <tr bgcolor="#F7F7F7">
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Group name').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$group_row['groupname'].'</b></div></td>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Accountcode').':&nbsp;&nbsp;</div></td>
					<td width="30%" align="center" valign="top" >'.$group_row['accountcode'].'</td>
				  </tr>
				  <tr>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Limittype').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$group_row['limittype'].'</b></td>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Allowcallback').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$group_row['allowcallback'].'</b></td>	
				  </tr>
				  <tr bgcolor="#F7F7F7">
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Credit limit').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$group_row['creditlimit'].'</b></td>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Clid cost').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$group_row['credit_clid'].'</b></td>	
				  </tr>
				  <tr>
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Total cost').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$group_row['credit_group'].'</b></td>	
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Current cost').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$group_row['curcredit'].'</b></td>	
				  </tr>
				  <tr bgcolor="#F7F7F7">					
					<td width="20%" align="right" valign="top" >'.$locate->Translate('Balance').':&nbsp;&nbsp;</td>
					<td width="30%" align="center" valign="top" ><b>'.$balance.'</b></td>
					<td></td>
					<td></td>
				  </tr>
			</table>';
	}
	return $html;
}

function paymentInfoHtml(){
	global $locate,$config;
	$reseller_row = astercrm::getRecordByID($_SESSION['curuser']['resellerid'],'resellergroup');
	$html = '<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="600">
			  <tr>
				<td width="46%" height="39" class="td font" align="left">'.
					$locate->Translate('Online Payment Receiving Infomation').'
				</td>
				<td width="54%" class="td font" align="center">&nbsp;</td>
			  </tr>
				<tr><td height="10" class="td"></td>
				<td class="td font" align="center">&nbsp;</td>
			  </tr>
			</table>
			<table border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="600">
			  <tr bgcolor="#F7F7F7">
				<td width="25%" align="right" valign="top" >'.$locate->Translate('Online payment').':&nbsp;&nbsp;</td>
				<td width="75%" align="center" valign="top" ><b>'.$reseller_row['epayment_status'].'</b></td>
			  </tr>
			  <tr>
				<td width="25%" align="right" valign="top" >'.$locate->Translate('Paypal account').':&nbsp;&nbsp;</td>
				<td width="75%" align="center" valign="top" ><b>'.$reseller_row['epayment_account'].'</b></td>
			  </tr>
			  <tr bgcolor="#F7F7F7">
				<td width="25%" align="right" valign="top" >'.$locate->Translate('Paypal payment url').':&nbsp;&nbsp;</td>
				<td width="75%" align="center" valign="top" ><b>'.$config['epayment']['paypal_payment_url'].'</b></td>
			  </tr>
			  <tr bgcolor="#F7F7F7">
				<td width="25%" align="right" valign="top" >'.$locate->Translate('Paypal verify url').':&nbsp;&nbsp;</td>
				<td width="75%" align="center" valign="top" ><b>'.$config['epayment']['paypal_verify_url'].'</b></td>
			  </tr>
			  <tr>
				<td  align="right" valign="top" >'.$locate->Translate('Paypal identity token').':&nbsp;&nbsp;</td>
				<td  align="center" valign="top" ><b>'.$reseller_row['epayment_identity_token'].'</b></td>
			  </tr>
			  <tr bgcolor="#F7F7F7">
				<td  align="right" valign="top" >'.$locate->Translate('Item name').':&nbsp;&nbsp;</td>
				<td  align="center" valign="top" ><b>'.$reseller_row['epayment_item_name'].'</b></td>
			  </tr>			  
			  <tr>
				<td align="right" valign="top" >'.$locate->Translate('Available amount').':&nbsp;&nbsp;</td>
				<td align="center" valign="top" ><b>'.$reseller_row['epayment_amount_package'].'</b></td>	
			  </tr>
			  <tr bgcolor="#F7F7F7">
				<td align="right" valign="top" >'.$locate->Translate('Currency code').':&nbsp;&nbsp;</td>
				<td align="center" valign="top" ><b>'.$config['epayment']['currency_code'].'</b></td>
			  </tr>
			  <tr bgcolor="#F7F7F7">
				<td align="right" valign="top" >'.$locate->Translate('Notify email').':&nbsp;&nbsp;</td>
				<td align="center" valign="top" ><b>'.$reseller_row['epayment_notify_mail'].'</b></td>
			  </tr>
			  <tr bgcolor="#F7F7F7">
				<td align="right" valign="top" >'.$locate->Translate('Callshop pay fee').':&nbsp;&nbsp;</td>
				<td align="center" valign="top" ><b>'.$locate->Translate($reseller_row['callshop_pay_fee']).'</b></td>
			  </tr>
			  <tr>
				<td align="right" valign="top" colspan="2"><input type="button" id="epayment_edit" name="epayment_edit" value="'.$locate->Translate('Edit').'" onclick="xajax_resellerPaymentInfoEdit();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
			  </tr>
			</table>';
	return $html;
}

function rechargeHtml(){
	global $config,$locate;

	$html = '<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="600">
			  <tr>
				<td width="26%" height="39" class="td font" align="left">
					'.$locate->Translate('Recharge By Paypal').'
				</td>
				<td width="74%" class="td font" align="center">&nbsp;</td>
			  </tr>
				<tr><td height="10" class="td"></td>
				<td class="td font" align="center">&nbsp;</td>
			  </tr>
			</table>
			<table border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="600">';

	if($_SESSION['curuser']['usertype'] == 'reseller'){

		$html .='<tr bgcolor="#F7F7F7">
				<td align="center" valign="top" ><span id="recharge_item_name" name="recharge_item_name">'.$config['epayment']['item_name'].'</span>:&nbsp;&nbsp;<span id="recharge_currency_code" id="recharge_currency_code">'.$config['epayment']['currency_code'].'</span>&nbsp;&nbsp;<select id="amount" name="amount">';

		$amountP = split(',',$config['epayment']['amount']);

	}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){

		$reseller_row = astercrm::getRecordByID($_SESSION['curuser']['resellerid'],'resellergroup');
		$html .='<tr bgcolor="#F7F7F7">
				<td align="center" valign="top" ><span id="recharge_item_name" name="recharge_item_name">'.$reseller_row['epayment_item_name'].'</span>:&nbsp;&nbsp;<span id="recharge_currency_code" id="recharge_currency_code">'.$config['epayment']['currency_code'].'</span>&nbsp;&nbsp;<select id="amount" name="amount">';

		$amountP = split(',',$reseller_row['epayment_amount_package']);		
	}

	foreach ($amountP as $amount ){
		if(is_numeric($amount)) {
			$amount = round($amount,2);
			$option .= '<option value="'.$amount.'">'.$amount.'</option>';
		}
	}
	$html .= $option.'</select>&nbsp;&nbsp;<input type="button"	value="'.$locate->Translate('Recharge By Paypal').'" onclick="rechargeByPaypal();"></td>
			  </tr>
			</table>';
	return $html;
}

function rechargeByPaypal($amount){
	global $config,$locate;

	$objResponse = new xajaxResponse();
	if(!is_numeric($amount)) {
		$objResponse->addAlert($locate->Translate('Please select amount'));
		return $objResponse;
	}

	$paypal_charge = array();
	if($_SESSION['curuser']['usertype'] == 'reseller'){
		if( $config['epayment']['epayment_status'] != 'enable' || $config['epayment']['paypal_payment_url'] == '' || $config['epayment']['paypal_account'] == '' || $config['epayment']['pdt_identity_token'] == '' || $config['epayment']['asterbilling_url'] == '' || $config['epayment']['paypal_verify_url'] == '' || $config['epayment']['currency_code'] == ''){
			$objResponse->addAlert($locate->Translate('The system does not support online payment'));
			return $objResponse;
		}else{
			$p = new paypal_class;
			$p->paypal_url = $config['epayment']['paypal_payment_url'];
			$p->add_field('business',$config['epayment']['paypal_account']);
			$this_url = $_SERVER['HTTP_REFERER'];
			$this_url = split('\?',$this_url);
			$this_url = $this_url['0'];
			$p->add_field('return',$this_url.'?action=success');
			$p->add_field('cancel_return',$this_url.'?action=cancel');
			$p->add_field('notify_url',$config['epayment']['asterbilling_url']."/epaymentreturn.php");
			$p->add_field('item_name',$config['epayment']['item_name']);
			$p->add_field('item_number',$_SESSION['curuser']['resellerid']);
			$p->add_field('amount',$amount);
			$p->add_field('mc_currency',$config['epayment']['currency_code']);
			$p->add_field('currency_code',$config['epayment']['currency_code']);
			//custum field userid:usertype:resellerid:gruopid
			$p->add_field('custom',$_SESSION['curuser']['userid'].':reseller:'.$_SESSION['curuser']['resellerid'].':'.$_SESSION['curuser']['groupid']);
			
		}
	}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){

		$reseller_row = astercrm::getRecordByID($_SESSION['curuser']['resellerid'],'resellergroup');

		if($reseller_row['epayment_status'] != 'enable'){
			$objResponse->addAlert($locate->Translate('The reseller does not support online payment'));
			return $objResponse;
		}else{
			$p = new paypal_class;
			$p->paypal_url = $config['epayment']['paypal_payment_url'];;
			$p->add_field('business',$reseller_row['epayment_account']);
			$this_url = $_SERVER['HTTP_REFERER'];
			$this_url = split('\?',$this_url);
			$this_url = $this_url['0'];
			$p->add_field('return',$this_url.'?action=success');
			$p->add_field('cancel_return',$this_url.'?action=cancel');
			$p->add_field('notify_url',$config['epayment']['asterbilling_url']."/epaymentreturn.php");
			$p->add_field('item_name',$reseller_row['epayment_item_name']);
			$p->add_field('item_number',$_SESSION['curuser']['groupid']);
			$p->add_field('amount',$amount);
			$p->add_field('mc_currency',$config['epayment']['currency_code']);
			$p->add_field('currency_code',$config['epayment']['currency_code']);
			//custum field userid:usertype:resellerid:gruopid
			$p->add_field('custom',$_SESSION['curuser']['userid'].':groupadmin:'.$_SESSION['curuser']['resellerid'].':'.$_SESSION['curuser']['groupid']);
		}
	}	

	$paymentHtml .= '<table border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#F0F0F0" width="600">
		  <tr>
			<td width="26%" height="39" class="td font" align="center">
				'.$locate->Translate('Recharge By Paypal').'
			</td>
			<td width="74%" class="td font" align="center">&nbsp;</td>
		  </tr>
			<tr><td height="10" class="td"></td>
			<td class="td font" align="center">&nbsp;</td>
		  </tr>
		</table>
		<table border="0" align="center" cellpadding="1" cellspacing="1" bgcolor="#F0F0F0" id="menu" width="600">
		<tr bgcolor="#F7F7F7">
		<td align="center" valign="top"><b>'.$locate->Translate('Please wait your credit order is processing').'...</b>'; 

	$paymentHtml .= $p->submit_paypal_post();
	$paymentHtml .= '</td></tr></table>';

	$objResponse->addAssign("rechargeInfo","innerHTML",$paymentHtml);
	$objResponse->addScript("document.getElementById('paymentForm').submit()");
	return $objResponse;
}

function processOrder($pdt){
	global $db,$config,$locate;
	
	$reseller_row = astercrm::getRecordByID($_SESSION['curuser']['resellerid'],'resellergroup');

	if($_SESSION['curuser']['usertype'] == 'reseller'){
		$srcCredit = $reseller_row['curcredit'];
		if($config['epayment']['callshop_pay_fee']){
			$creditBeforeFee = $srcCredit - $pdt['mc_gross'];
			$updateCurCredit = $srcCredit - $pdt['mc_gross'] + $pdt['mc_fee'];
		}else{
			$updateCurCredit = $srcCredit - $pdt['mc_gross'];
		}
		$sql = "UPDATE resellergroup SET curcredit = $updateCurCredit WHERE id = '".$_SESSION['curuser']['resellerid']."'";
		$mailto = $config['epayment']['notify_mail'];
		$mailTitle = $locate->Translate('Reseller').': '.$_SESSION['curuser']['username'].' '.$locate->Translate('Paymented').' '.$config['epayment']['currency_code'].$pdt['mc_gross'].' '.$locate->Translate('for').' '.$config['epayment']['item_name'].','.$locate->Translate('Please check it').' -pdt';
	}elseif($_SESSION['curuser']['usertype'] == 'groupadmin'){
		$group_row = astercrm::getRecordByID($_SESSION['curuser']['groupid'],'accountgroup');
		$srcCredit = $group_row['curcredit'];
		if($reseller_row['callshop_pay_fee'] == 'no'){
			$updateCurCredit = $srcCredit - $pdt['mc_gross'];
		}else{
			$creditBeforeFee = $srcCredit - $pdt['mc_gross'];
			$updateCurCredit = $srcCredit - $pdt['mc_gross'] + $pdt['mc_fee'];
		}
		$sql = "UPDATE accountgroup SET curcredit = $updateCurCredit WHERE id='".$_SESSION['curuser']['groupid']."'";
		$mailto = $reseller_row['epayment_notify_mail'];
		$mailTitle = $locate->Translate('Callshop').': '.$_SESSION['curuser']['username'].' '.$locate->Translate('Paymented').' '.$config['epayment']['currency_code'].$pdt['mc_gross'].' '.$locate->Translate('for').' '.$reseller_row['epayment_item_name'].','.$locate->Translate('Please check it').' -pdt';
	}

	$res = $db->query($sql);

	if($res){
		$credithistory_sql = "INSERT INTO credithistory SET modifytime=now(), resellerid='".$_SESSION['curuser']['resellerid']."',groupid='".$_SESSION['curuser']['groupid']."',srccredit='".$srcCredit."',modifystatus='reduce',modifyamount='".$pdt['mc_gross']."',comment='Recharge By Paypal',operator='".$_SESSION['curuser']['userid']."',epayment_txn_id='".$pdt['txn_id']."'";
		$credithistory_res=$db->query($credithistory_sql);
		if(($_SESSION['curuser']['usertype'] == 'groupadmin' && $reseller_row['callshop_pay_fee'] == 'yes') || ($_SESSION['curuser']['usertype'] == 'reseller' && $config['epayment']['callshop_pay_fee'])){
			$credithistory_sql = "INSERT INTO credithistory SET modifytime=now() + 1, resellerid='".$_SESSION['curuser']['resellerid']."',groupid='".$_SESSION['curuser']['groupid']."',srccredit='".$creditBeforeFee."',modifystatus='add',modifyamount='".$pdt['mc_fee']."',comment='Fees By Paypal',operator='".$_SESSION['curuser']['userid']."',epayment_txn_id='".$pdt['txn_id']."'";
			$credithistory_res=$db->query($credithistory_sql);
		}
	}

	$subject = 'Instant Payment Notification - Recieved Payment';
	$to = $mailto;    //  your email
	$body =  "An instant payment notification was successfully recieved\n";
	$body .= "from ".$pdt['payer_email'].", send by asterbilling on ".date('m/d/Y');
	$body .= " at ".date('g:i A')."\n\n";
	$body .= $mailTitle."\n\nDetails:\n";

	foreach ($pdt as $key => $value) {
		if($key != '' && $key != 'custom')	$body .= "\n$key: $value"; 
	}
	mail($to, $subject, $body);

	return $res;
}

function resellerPaymentInfoEdit(){
	global $db,$config,$locate;

	$objResponse = new xajaxResponse();

	if($_SESSION['curuser']['usertype'] == 'reseller'){
		$reseller_row = astercrm::getRecordByID($_SESSION['curuser']['resellerid'],'resellergroup');
		$html = Table::Top( $locate->Translate("Edit Payment Receiving Infomation"),"formDiv"); 
		if($reseller_row['epayment_status'] == 'enable'){
			$enable = 'checked';
		}else{
			$diable = 'checked';
		}

		if($reseller_row['callshop_pay_fee'] == 'yes'){
			$yesVal = 'checked';
		}else{
			$noVal = 'checked';
		}
		
		$html .= '
				<!-- No edit the next line -->
				<form method="post" name="f" id="f">
				
				<table border="1" width="100%" class="adminlist">
					<tr>
						<td nowrap align="left">'.$locate->Translate("Paypal payment url").'</td>
						<td align="left">'.$config['epayment']['paypal_payment_url'].'</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Paypal verify url").'</td>
						<td align="left">'.$config['epayment']['paypal_verify_url'].'</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Online payment").'</td>
						<td align="left"><input type="radio" id="epayment_status" name="epayment_status" value="enable" '.$enable.'>'.$locate->Translate("Enable").'<input type="radio" id="epayment_status" name="epayment_status"  value="disable" '.$diable.'>'.$locate->Translate("Disable").'</td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Paypal account").'</td>
						<td align="left"><input type="text" id="epayment_account" name="epayment_account" size="35"  value="'.$reseller_row['epayment_account'].'"></td>
					</tr>					
					<tr>
						<td nowrap align="left">'.$locate->Translate("Paypal identity token").'</td>
						<td align="left"><input type="text" id="epayment_identity_token" name="epayment_identity_token" size="35" value="'.$reseller_row['epayment_identity_token'].'"></td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Item name").'</td>
						<td align="left"><input type="text" id="epayment_item_name" name="epayment_item_name" size="35" value="'.$reseller_row['epayment_item_name'].'"></td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Available amount").'</td>
						<td align="left"><input type="text" id="epayment_amount_package" name="epayment_amount_package" size="35"  maxlength="30" value="'.$reseller_row['epayment_amount_package'].'"></td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Notify email").'</td>
						<td align="left"><input type="text" id="epayment_notify_mail" name="epayment_notify_mail" size="35" value="'.$reseller_row['epayment_notify_mail'].'"></td>
					</tr>
					<tr>
						<td nowrap align="left">'.$locate->Translate("Callshop pay fee").'</td>
						<td align="left"><input type="radio" id="callshop_pay_fee" name="callshop_pay_fee" value="yes" '.$yesVal.'>'.$locate->Translate("Yes").'<input type="radio" id="callshop_pay_fee" name="callshop_pay_fee"  value="no" '.$noVal.'>'.$locate->Translate("No").'</td>
					</tr>
					<tr>
						<td colspan="2" align="center"><button id="submitButton" onClick=\'xajax_resellerPaymentInfoUpdate(xajax.getFormValues("f"));return false;\'>'.$locate->Translate("Continue").'</button></td>
					</tr>
				 </table></form>';
		$html .= Table::Footer();
		$objResponse->addAssign("formDiv", "style.visibility", "visible");
		$objResponse->addAssign("formDiv", "innerHTML", $html);
	}

	return $objResponse->getXML();
}

function resellerPaymentInfoUpdate($f){
	global $db,$config,$locate;
	
	$objResponse = new xajaxResponse();

	if($_SESSION['curuser']['usertype'] == 'reseller'){
		if($f['epayment_status'] == 'enable'){
			
			if($config['epayment']['paypal_payment_url'] == '' || $config['epayment']['paypal_verify_url'] == '' || $config['epayment']['currency_code'] == '' || $config['epayment']['asterbilling_url'] == ''){
				$objResponse->addAlert($locate->Translate('The system does not support online payment'));
				return $objResponse;
			}

			if($f['epayment_account'] == '' || $f['epayment_identity_token'] == '' || $f['epayment_item_name'] == '' || $f['epayment_amount_package'] == '' || $f['epayment_notify_mail'] == ''){
				$objResponse->addAlert($locate->Translate('All item can not be blank if enabaled online payment'));
				return $objResponse;
			}
		}

		$amounts = explode(',',$f['epayment_amount_package']);
		foreach($amounts as $value){
			if(is_numeric($value)){
				$amount .= round($value,2).',';
			}
		}
		$amount = rtrim($amount,',');

		$sql = "UPDATE resellergroup SET epayment_status='".$f['epayment_status']."',epayment_account='".$f['epayment_account']."',epayment_item_name='".$f['epayment_item_name']."',epayment_identity_token='".$f['epayment_identity_token']."',epayment_amount_package='".$amount."',epayment_notify_mail='".$f['epayment_notify_mail']."',callshop_pay_fee='".$f['callshop_pay_fee']."' WHERE id='".$_SESSION['curuser']['resellerid']."'";
		$res = $db->query($sql);

		$objResponse->addAssign("formDiv", "style.visibility", "hidden");
		$objResponse->addAssign("formDiv", "innerHTML", '');
		if($res == 1){					
			$objResponse->addAlert($locate->Translate('Payment Receiving Infomation has been updated'));
			$paymentinfoHtml = paymentInfoHtml();
			$objResponse->addAssign("paymentInfo","innerHTML",$paymentinfoHtml);
		}else{
			$objResponse->addAlert($locate->Translate('Payment Receiving Infomation update failed'));
		}

	}
	return $objResponse->getXML();
}

$xajax->processRequests();
?>
