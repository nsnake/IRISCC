<?php
//require_once ("db_connect.php");
//global $db;
if(!empty($_REQUEST['file'])){
	//$query = "SELECT * FROM monitorrecord WHERE id = ".$_REQUEST['file'];
	//if($row = $db->getRow($query)){
		$file = $_REQUEST['file'];
	//}else{
	//	die("<b>404 File not found!</b>");
	//}
}else{
	die("<b>404 File not found!</b>");
}

read_file($file);

function read_file($file){

    //First, see if the file exists
    if (!is_file($file)) { die("<b>404 File not found!</b>"); }

    //Gather relevent info about file
    $file_len = filesize($file);
    $fifile_lename = basename($file);
    $file_extension = strtolower(substr(strrchr($fifile_lename,"."),1));

    //This will set the Content-Type to the appropriate setting for the file
    switch( $file_extension ) {
      case "pdf": $contentType="application/pdf"; break;
      case "exe": $contentType="application/octet-stream"; break;
      case "zip": $contentType="application/zip"; break;
      case "doc": $contentType="application/msword"; break;
      case "xls": $contentType="application/vnd.ms-excel"; break;
      case "ppt": $contentType="application/vnd.ms-powerpoint"; break;
      case "gif": $contentType="image/gif"; break;
      case "png": $contentType="image/png"; break;
      case "jpeg":
      case "jpg": $contentType="image/jpg"; break;
      case "mp3": $contentType="application/force-download"; break;
      case "wav": $contentType="audio/x-wav"; break;
	  case "gsm": $contentType="audio/x-gsm"; break;
      case "mpeg":
      case "mpg":
      case "mpe": $contentType="video/mpeg"; break;
      case "mov": $contentType="video/quicktime"; break;
      case "avi": $contentType="video/x-msvideo"; break;

      case "php":
      case "htm":
      case "html":
      case "txt": die("<b>Cannot be used for ". $file_extension ." files!</b>"); break;

      default: $contentType="application/force-download";
    }

    //Begin writing headers
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: public");
    header("Content-Description: File Transfer");

    //Use the switch-generated Content-Type
    header("Content-Type: $contentType");

    //Force the download
    $header="Content-Disposition: attachment; filename=".$fifile_lename.";";
    header($header );
    header("Content-Transfer-Encoding: binary");
    header("Content-file_length: ".$file_len);
	if ($file = fopen($file, 'r')){
		while(!feof($file)){
			$streams = fread($file,'1024');
			echo $streams;
		}
		fclose($file);
	}
    exit;
}
?>