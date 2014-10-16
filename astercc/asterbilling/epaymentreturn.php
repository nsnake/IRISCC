<?php
require_once ("db_connect.php");
//require_once ("profile.common.php");
require_once ('include/localization.class.php');
require_once ("include/astercrm.class.php");
require_once ('include/paypal.class.php');  // include the class file

$locate=new Localization('en','US','profile');

	$p = new paypal_class;             // initiate an instance of the class

	$p->paypal_url = $config['epayment']['paypal_payment_url'];
	
	if ($p->validate_ipn($config['epayment']['ipn_log'])) {
          
         // Payment has been recieved and IPN is verified.  This is where you
         // update your database to activate or process the order, or setup
         // the database with the user's order details, email an administrator,
         // etc.  You can access a slew of information via the ipn_data() array.
  
         // Check the paypal documentation for specifics on what information
         // is available in the IPN POST variables.  Basically, all the POST vars
         // which paypal sends, which we send back for validation, are now stored
         // in the ipn_data() array.
  
         // For this example, we'll just email ourselves ALL the data.
		 if($p->ipn_data['custom'] != ''){
			 $payer = explode(':',$p->ipn_data['custom']);
			 $userid = $payer['0'];
			 $uesrtype = $payer['1'];
			 $resellerid = $payer['2'];
			 $groupid = $payer['3'];

			 if($config['epayment']['ipn_log']){
				$loghandle = fopen("upload/paypalipn-epayment.log",'rb');
				$oricontent = fread($loghandle,filesize("upload/paypalipn-epayment.log"));
				fclose($loghandle);
				$loghandle = fopen("upload/paypalipn-epayment.log",'w');
				$date = '#####'.date("Y-m-d H:i:s");
				$logstr = $oricontent.$date.'-IPN txn_id:'.$p->ipn_data['txn_id'].'|receiver_id:'.$p->ipn_data['receiver_id']."\n";
				$logstr .= 'userid:'.$userid."\n";
				$logstr .= 'uesrtype:'.$uesrtype."\n";
				$logstr .= 'resellerid:'.$resellerid."\n";
				$logstr .= 'groupid:'.$groupid."\n";
			 }
			 
			 $reseller_row = astercrm::getRecordByID($resellerid,'resellergroup');
			 
			 if($uesrtype == 'reseller'){
				$account = astercrm::getRecordByID($userid,'account');
				$srcCredit = $reseller_row['curcredit'];
				if($config['epayment']['callshop_pay_fee']){
					$credit = $p->ipn_data['mc_gross'] - $p->ipn_data['mc_fee'];					
				}else{
					$credit = $p->ipn_data['mc_gross'];
				}
				$updateCurCredit = $srcCredit - $p->ipn_data['mc_gross'];
				$sql = "UPDATE resellergroup SET curcredit = curcredit - ".$credit." WHERE id = '".$account['resellerid']."'";
				$mailto = $config['epayment']['notify_mail'];				
				$mailTitle = $locate->Translate('Reseller').': '.$account['username'].' '.$locate->Translate('Paymented').' '.$config['epayment']['currency_code'].' '.$p->ipn_data['mc_gross'].' '.$locate->Translate('for').' '.$config['epayment']['item_name'].','.$locate->Translate('Please check it').' - ipn';

			 }elseif($uesrtype == 'groupadmin'){
				$account = astercrm::getRecordByID($userid,'account');
				$group_row = astercrm::getRecordByID($account['groupid'],'accountgroup');
				$srcCredit = $group_row['curcredit'];
				if($reseller_row['callshop_pay_fee']){
					$credit = $p->ipn_data['mc_gross'] - $p->ipn_data['mc_fee'];					
				}else{
					$credit = $p->ipn_data['mc_gross'];
				}
				$updateCurCredit = $srcCredit - $p->ipn_data['mc_gross'];
				$sql = "UPDATE accountgroup SET curcredit = curcredit - $credit WHERE id = '".$account['groupid']."'";
				$mailto = $reseller_row['epayment_notify_mail'];
				$mailTitle = $locate->Translate('Callshop').': '.$account['username'].' '.$locate->Translate('Paymented').' '.$config['epayment']['currency_code'].' '.$p->ipn_data['mc_gross'].' '.$locate->Translate('for').' '.$reseller_row['epayment_item_name'].','.$locate->Translate('Please check it').' - ipn';
			}

			if($config['epayment']['ipn_log']){
				$logstr .= "txn_id- ".$p->ipn_data['txn_id'].'| updateCurCreditSQL:'.$sql."\n";
				//fwrite($loghandle,$logstr);
				//fclose($loghandle);
			}

			$txn_res = astercrm::getRecordByField('epayment_txn_id',$p->ipn_data['txn_id'],'credithistory');

			//if($config['epayment']['ipn_log']){
			//	$loghandle = fopen("upload/paypalipn-epayment.log",'rb');
			//	$oricontent = fread($loghandle,filesize("upload/paypalipn-epayment.log"));
			//	fclose($loghandle);
			//	$loghandle = fopen("upload/paypalipn-epayment.log",'w');
			//	$logstr = $oricontent;
			//}
			
			// check that txn_id has not been previously processed
			if($txn_res['id'] > 0){
				if($config['epayment']['ipn_log']){
					$logstr .= "txn_res: txn_id- ".$p->ipn_data['txn_id']."| Already processed\n";
					fwrite($loghandle,$logstr);
					fclose($loghandle);
				}
				exit();
			}else{
				$res = $db->query($sql);
				if($res){
					if($config['epayment']['ipn_log']){
						$logstr .= "txn_id- ".$p->ipn_data['txn_id']."| Update credit success\n";
					}
					$credithistory_sql = "INSERT INTO credithistory SET modifytime=now(),	resellerid='".$account['resellerid']."',groupid='".$account['groupid']."',srccredit='".$srcCredit."',modifystatus='reduce',modifyamount='".$credit."',comment='Recharge By Paypal',operator='".$userid."',epayment_txn_id='".$p->ipn_data['txn_id']."'";

					if($config['epayment']['ipn_log']){
						$logstr .= "txn_id- ".$p->ipn_data['txn_id']." Insert credit history SQL: ".$credithistory_sql."\n";
						fwrite($loghandle,$logstr);
						fclose($loghandle);
					}

					$credithistory_res=$db->query($credithistory_sql);
					
				}else{
					if($config['epayment']['ipn_log']){
						$logstr .= "txn_id- ".$p->ipn_data['txn_id']."| Update credit failed\n";
						fwrite($loghandle,$logstr);
						fclose($loghandle);
					}
				}
				$subject = 'Instant Payment Notification - Recieved Payment';
				$to = $mailto;    //  your email
				$body =  "An instant payment notification was successfully recieved\n";
				$body .= "from ".$p->ipn_data['payer_email'].", send by asterbilling on ".date('m/d/Y');
				$body .= " at ".date('g:i A')."\n\n";
				$body .= $mailTitle."\n\nDetails:\n";

				foreach ($p->ipn_data as $key => $value) {
					if($key != '' && $key != 'custom')	$body .= "\n$key: $value"; 
				}
				mail($to, $subject, $body);				
			}
		 }		
    }
	//echo "ok";exit;
?>