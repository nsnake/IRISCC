<?php
/*******************************************************************************
* upload.php
* 上传excel文件
* upload excel file
* 功能描述
* Function Desc
	上传csv、xls格式文件


* Revision 0.045  2007/10/22 13:34:00  modified by yunshida
* Desc: 
* 描述: 取消了使用模板

* Revision 0.045  2007/10/22   modified by yunshida
* Desc: page create
* 描述: 页面建立
  
********************************************************************************/
header("content-type:text/html;charset=utf-8");
session_start();
require_once ('include/localization.class.php');
require_once ("include/excel.class.php");
include_once('config.php');
require_once ("db_connect.php");
require_once ('include/astercrm.class.php');

if ($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'reseller' && $_SESSION['curuser']['usertype'] != 'groupadmin') 
	header("Location: manager_login.php");

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'import');

//for group logo
if ( isset($_POST['LogoUploadFlag']) && trim($_POST['LogoUploadFlag']) == '1' ){

	//$allow_type = array('image/png','image/jpg','image/gif','image/jpeg','image/pjpeg');

	if($_FILES['file']['error'] > 0) {
		echo '!error:';
		switch($_FILES['file']['error'])
		{
			case 1: echo "<script>alert('".$locate->Translate("Logo file is too large")."');</script>";
			break;
			case 2: echo "<script>alert('".$locate->Translate("Logo file is too large")."');</script>";
			break;
			case 3: echo "<script>alert('".$locate->Translate("Logo upload faild")."');</script>";
			break;
			case 4: echo "<script>alert('".$locate->Translate("Logo upload faild")."');</script>";
			break;
		}
		exit;
	}

	if ($_FILES['callshopLogo']['size'] > 2000000) {
		echo "<script>alert('".$locate->Translate("Logo file size must less than")."2M');</script>";
		exit;
	}

	if (is_uploaded_file($_FILES['callshopLogo']['tmp_name'])){

		if (strstr($_FILES['callshopLogo']['type'],'image')){
			$file = split('\.',$_FILES['callshopLogo']['name']);
			$file_count = count($file) -1;
			$file_ext = $file[$file_count];
			$filename = 'callshoplogo_'.$_REQUEST['group_id'].'.'.$file_ext;
			$filepath = $config['system']['upload_file_path'].'/callshoplogo/';

			if(!is_dir($filepath)){
				
				if(!mkdir($filepath)){
					echo "<script>alert('".$locate->Translate("Logo upload faild please check your upload directory")."');</script>";
					exit;
				}

			}

			if(!move_uploaded_file($_FILES['callshopLogo']['tmp_name'], $filepath.$filename)){
				echo "<script>alert('".$locate->Translate("Logo upload faild1")."');</script>";
			}else{
				echo "<script>alert('".$locate->Translate("Logo upload success")."');</script>";
				$group_row = astercrm::getRecord($_REQUEST['group_id'],'accountgroup');

				if ( $group_row['grouplogo'] != $filename ){
					unlink($filepath.$group_row['grouplogo']);
					astercrm::updateField('accountgroup','grouplogo',$filename,$_REQUEST['group_id'],'id');
				}
				//echo "<script>window.parent.location.reload();</script>";
			}
		}else{
			echo "<script>alert('".$locate->Translate("Logo file must be a image file")."');</script>";
		}
	}
	exit;
}


//for import
if(isset($_POST['CHECK']) && trim($_POST['CHECK']) == '1'){
	$upload_msg = '';
	$is_vaild = 0;

	if($_FILES['excel']['name'] != ''){ //上传的文件
		$upload_type = $_FILES['excel']['type'];
		$original_name = $_FILES['excel']['name'];
		$type = substr($original_name,-3);
		$file_name = $_SESSION['curuser']['resellerid'].'_'.$_SESSION['curuser']['groupid'].'_'.time().'.'.$type;
		if ( "xls" == $type || "csv" == $type)
		{
			if (!move_uploaded_file($_FILES['excel']['tmp_name'], $config['system']['upload_file_path'] . $file_name)) 
			{
				$upload_msg = $locate->Translate('failed');  //失败提示
			}else{
				$sql = "INSERT INTO uploadfile SET filename = '$file_name',originalname='$original_name',cretime=now(),creby='".$_SESSION['curuser']['username']."',resellerid = '".$_SESSION['curuser']['resellerid']."',groupid='".$_SESSION['curuser']['groupid']."', type='asterbilling'";

				$res = $db->query($sql);
			}
		}else {
			$upload_msg .= $locate->Translate('cantup');  //失败提示
		}
	}else{ //选择的已存在的文件
		$sql = "SELECT * FROM uploadfile WHERE id = ".$_POST['filelist'];
		$file = $db->getRow($sql);
		$file_name = $file['filename'];
		$original_name = $file['originalname'];
		$type = substr($file_name,-3);
	}
		
	if ( $upload_msg == '' ) //未发生错误
	{
		$upload_msg =$locate->Translate('file').' '.$file_name.' '.$locate->Translate('uploadsuccess')."!<br />";
		if($type == 'csv'){
			$handleup = fopen($config['system']['upload_file_path'] . $file_name,"r");
			$row = 0;
			while($data = fgetcsv($handleup, 1000, ",")){
			   $row++;
			}
			if($row > 8){
				$upload_msg .= " <font>".$locate->Translate('have').' '.$row.' '.$locate->Translate('default')."</font>";
			}else{
				$upload_msg .= " <font>".$locate->Translate('have').' '.$row.' '.$locate->Translate('recrod')."</font>";
			}
		}elseif($type == 'xls'){
			Read_Excel_File($config['system']['upload_file_path'] . $file_name,$return);
			$xlsrow = count($return[Sheet1]);
			if($xlsrow > 8){
				$upload_msg .= " <font>".$locate->Translate('have').' '.$xlsrow.' '.$locate->Translate('default')."</font>";
			}else{
				$upload_msg .= " <font>".$locate->Translate('have').' '.$xlsrow.' '.$locate->Translate('recrod')."</font>";
			}
		}
	}
}
else
{
	$upload_msg = $locate->Translate('feifa');
}

?>
<SCRIPT LANGUAGE="JavaScript">
	var msg = "<? echo $upload_msg; ?><br />";
	window.parent.document.getElementById("divMessage").innerHTML = msg;//msg;
	//alert ("<?=$_FILES['excel']['name']?>");
	window.parent.showDivMainRight("<?=$file_name?>");
	window.parent.document.getElementById('btnUpload').disabled = false;
	window.parent.document.getElementById('btnUpload').value="<?=$locate->Translate('upload')?>";
	window.parent.document.getElementById('hidFileName').value="<?=$file_name?>";
</SCRIPT>