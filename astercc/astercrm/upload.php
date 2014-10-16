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

if ($_SESSION['curuser']['usertype'] != 'admin' && $_SESSION['curuser']['usertype'] != 'reseller' && $_SESSION['curuser']['usertype'] != 'groupadmin' && !is_array($_SESSION['curuser']['privileges']['import']))
	header("Location: login.php");

$GLOBALS['locate']=new Localization($_SESSION['curuser']['country'],$_SESSION['curuser']['language'],'import');

if(isset($_POST['CHECK']) && trim($_POST['CHECK']) == '1'){
	$upload_msg = '';
	$is_vaild = 0;

	if($_FILES['excel']['name'] != ''){ //上传的文件
		$upload_type = $_FILES['excel']['type'];
		$original_name = $_FILES['excel']['name'];
		$type = substr($original_name,-3);
		$file_name = $_SESSION['curuser']['groupid'].'_'.time().'.'.$type;

		if ( "xls" == $type || "csv" == $type)
		{
			if (!move_uploaded_file($_FILES['excel']['tmp_name'], $config['system']['upload_file_path'] . $file_name))
			{
				$upload_msg = $locate->Translate('failed');  //失败提示
			}else{
				$sql = "INSERT INTO uploadfile SET filename = '$file_name',originalname='$original_name',cretime=now(),creby='".$_SESSION['curuser']['username']."',groupid='".$_SESSION['curuser']['groupid']."', type='astercrm'";

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
		//$detectFileCode = mb_detect_encoding(file_get_contents($config['system']['upload_file_path'].$file_name),'UTF-8');
		$detectFileCode = chkCode(file_get_contents($config['system']['upload_file_path'].$file_name));
		$upload_msg =$locate->Translate('file').' '.$original_name.' '.$locate->Translate('uploadsuccess')."!<br />";
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

function chkCode($string){
	$code = array('ASCII', 'GBK', 'UTF-8');
	foreach($code as $c){
		if($string === iconv('UTF-8', $c, iconv($c, 'UTF-8', $string))){
			return $c;
		}
	}
	return null;
}


?>
<SCRIPT LANGUAGE="JavaScript">
	var curFileCode = "<?php echo $detectFileCode; ?>";
	if(curFileCode != 'UTF-8') {
		alert("<?php $locate->Translate('Please convert the file to UTF-8')?>");
	}
	var msg = "<?php echo $upload_msg; ?><br />";
	//alert(msg);
	window.parent.document.getElementById("divMessage").innerHTML = msg;//msg;
	//alert ("<?php $_FILES['excel']['name']?>");
	window.parent.showDivMainRight("<?php $file_name?>");
	window.parent.document.getElementById('btnUpload').disabled = false;
	window.parent.document.getElementById('btnUpload').value="<?php $locate->Translate('upload')?>";
	window.parent.document.getElementById('hidFileName').value="<?php $file_name?>";
</SCRIPT>