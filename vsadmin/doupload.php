<SCRIPT language="php">
@include 'adminsession.php';
session_cache_limiter('none');
session_start();
ob_start();
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protect under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
include "db_conn_open.php";
include "includes.php";
include "inc/languageadmin.php";
if(@$storesessionvalue=="") $storesessionvalue="virtualstore";
if(@$_SESSION["loggedon"] != $storesessionvalue || @$disallowlogin==TRUE){
	if(@$_SERVER["HTTPS"] == "on" || @$_SERVER["SERVER_PORT"] == "443")$prot='https://';else $prot='http://';
	header('Location: '.$prot.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/login.php');
	exit;
}
$isprinter=FALSE;
</SCRIPT>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>Admin Upload</title>
<link rel="stylesheet" type="text/css" href="adminstyle.css"/>
</head>
<body>
<?php
function dogetfilepath($impath, $imname){
	if(substr($impath, 0, 1)=='/' || substr($impath, 0, 1)=='\\')
		$thepath = (realpath($_SERVER['DOCUMENT_ROOT'].$impath));
	else
		$thepath = (realpath('../' . $impath));
	if(substr($thepath, -1)!='/' && substr($thepath, -1)!='\\') $thepath .= '/';
	return($thepath . $imname);
}
function validextension($lfn){
	if(substr($lfn, -4)=='.gif' || substr($lfn, -4)=='.jpg' || substr($lfn, -5)=='.jpeg' || substr($lfn, -4)=='.png') // || substr($lfn, -4)=='.bmp' || substr($lfn, -4)=='.art' || substr($lfn, -4)=='.wmf' || substr($lfn, -4)=='.emf' || substr($lfn, -4)=='.mov' || substr($lfn, -4)=='.xbm' || substr($lfn, -4)=='.avi' || substr($lfn, -4)=='.mpg' || substr($lfn, -5)=='.mpeg')
		return(TRUE);
	else
		return(FALSE);
}
function validimagecontent($tf){
	if(ord($tf[0])==0xFF && ord($tf[1])==0xD8) return(TRUE); // JPEG
	if(ord($tf[0])==0x89 && ord($tf[1])==0x50 && ord($tf[2])==0x4E && ord($tf[3])==0x47) return(TRUE); // PNG
	$first6 = strtolower(substr($tf, 0, 6));
	if($first6=='gif87a' || $first6=='gif89a') return(TRUE); // GIF
	return(FALSE);
}
function writeimagejs($impath, $fdname, $whichfield){
	$impath = str_replace('\\', '/', $impath);
	if(substr($impath, -1)!='/') $impath .= '/';
	$impath .= $fdname;
	//$fieldnumber = str_replace('pImage', '', str_replace('pGiantImage', '', str_replace('pLargeImage', '', @$_POST['imagefield'])));
	if($whichfield==0) // Populate Caller
		$thefield=@$_POST['imagefield'];
	elseif($whichfield==1){ // Populate Small Image
		$thefield=str_replace('gtim', 'smim', str_replace('lgim', 'smim', @$_POST['imagefield']));
	}else{ // ($whichfield==2) Populate Large Image
		$thefield=str_replace('gtim', 'lgim', @$_POST['imagefield']);
	}
	//$thefield=@$_POST['imagefield'];
	print '<script type="text/javascript">';
	//if($whichfield==1 && $fieldnumber!='') print "if(window.opener.document.getElementById('x_pImage1').value!='1') window.opener.moreimages('', 'pImage', '');\r\n";
	//if($whichfield==2 && $fieldnumber!='') print "if(window.opener.document.getElementById('x_pLargeImage1').value!='1') window.opener.moreimages('', 'pLargeImage', '');\r\n";
	if($thefield=='smim0') print 'window.opener.document.getElementById("pImage").value="'.$impath.'";';
	if($thefield=='lgim0') print 'window.opener.document.getElementById("pLargeImage").value="'.$impath.'";';
	if($thefield=='gtim0') print 'window.opener.document.getElementById("pGiantImage").value="'.$impath.'";';
	print 'window.opener.document.getElementById("'.$thefield.'").value="'.$impath.'";';
	print '</script>';
}
function showfiledetails($fdsuccess, $fdname, $fdsize){
	global $yyDetai,$yyFileUp,$yyNoWrFl,$yyChkFP;
	if($fdsuccess){
		print '<p align="center">&nbsp;<br /><strong>'.$yyFileUp.'</strong><br />&nbsp;<br />';
		print $yyDetai . ': ' . $fdname . ' (' . $fdsize . ' bytes)</p>';
	}else
		writeerror($yyNoWrFl.'<br /><br />'.$yyChkFP.'<br />('.@$_POST['defimagepath'].')');
}
function writeerror($theerr){
	print '<p align="center">&nbsp;<br /><strong>ERROR! '.$theerr.'</strong></p>';
	print '<p>&nbsp;</p>';
	print '<p style="text-align:center;font-weight:bold">Debug Information</p>';
	print '<p style="text-align:center">post_max_size:' . ini_get('post_max_size') . '</p>';
	print '<p style="text-align:center">upload_max_filesize:' . ini_get('upload_max_filesize') . '</p>';
	print '<p style="text-align:center">file_uploads:' . ini_get('file_uploads') . '</p>';
	print '<p>&nbsp;</p>';
}
if(substr(@$_SESSION['loggedonpermissions'],5,1)!='X')
	print '<table width="100%" border="0" bgcolor=""><tr><td width="100%" colspan="4" align="center"><p>&nbsp;</p><p>&nbsp;</p><p><strong>'.$yyOpFai.'</strong></p><p>&nbsp;</p><p>'.$yyNoPer.' <br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><a href="admin.php"><strong>'.$yyAdmHom.'</strong></a>.</p><p>&nbsp;</p></td></tr></table>';
else{
	$success=FALSE;
	print '<p>&nbsp;</p>';
	$imagefile = $_FILES['imagefile'];
	if($imagefile['error']=='1'){
		$max_size = ini_get('upload_max_filesize');
		$errmsg='Image File was not uploaded successfully. This could be that it is larger than the maximum upload size or that the temporary upload directory is not writeable.';
		$errmsg.=' '.'The maximum upload filesize is ' . $max_size . '.';
	}else{
		if(strpos($imagefile['type'], 'image/')!==FALSE && validextension(strtolower($imagefile['name']))){
			$filepath = dogetfilepath(@$_POST['defimagepath'], $imagefile['name']);
			$tfile = file_get_contents($imagefile['tmp_name']);
			if(validimagecontent($tfile)){
				if(@$_POST['newdim0']!='' && @$_POST['thumbdim0']!=''){
					$id = getimagesize($imagefile['tmp_name']);
					$imtype = $id[2];
					// Calculate new width and height
					if($_POST['thumbdim0']=='1'){ // Width
						$newwidth = (double)$_POST['newdim0'];
						$newheight = $id[1] / ($id[0] / $newwidth);
					}elseif($_POST['thumbdim0']=='2'){ // Height
						$newheight = (double)$_POST['newdim0'];
						$newwidth = $id[0] / ($id[1] / $newheight);
					}
					$imres = imagecreatefromstring($tfile);
					$newimres = imagecreatetruecolor($newwidth, $newheight);
					imagecopyresampled($newimres, $imres, 0, 0, 0, 0, $newwidth, $newheight, $id[0], $id[1]);
					if($imtype==IMAGETYPE_JPEG){
						$success = @imagejpeg($newimres, $filepath, 80);
					}elseif($imtype==IMAGETYPE_GIF){
						$success = @imagegif($newimres, $filepath);
					}elseif($imtype==IMAGETYPE_PNG){
						$success = @imagepng($newimres, $filepath);
					}else
						writeerror($yyIlFlT);
					imagedestroy($imres);
					imagedestroy($newimres);
					if($success){
						writeimagejs(@$_POST['defimagepath'], $imagefile['name'], 0);
						showfiledetails(TRUE, $imagefile['name'], $imagefile['size']);
					}else
						writeerror($yyNoWrFl.'<br /><br />'.$yyChkFP.'<br />('.@$_POST['defimagepath'].')');
				}else{
					if($fd = @fopen($filepath, 'wb')){
						if(fwrite($fd, $tfile, $imagefile['size'])!==FALSE){
							fclose($fd);
							writeimagejs(@$_POST['defimagepath'], $imagefile['name'], 0);
							showfiledetails(TRUE, $imagefile['name'], $imagefile['size']);
							$success=TRUE;
						}else
							writeerror($yyNoWrFl.'<br /><br />'.$yyChkFP.'<br />('.@$_POST['defimagepath'].')');
					}else
						writeerror($yyNoWrFl.'<br /><br />'.$yyChkFP.'<br />('.@$_POST['defimagepath'].')');
				}
			}else
				writeerror($yyIlFlT);
		}else
			writeerror($yyIlFlT);
		setcookie('newdim0',trim(@$_POST['newdim0']),time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
		setcookie('thumbdim0',trim(@$_POST['thumbdim0']),time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
	}
	if($success){
		for($index=1; $index<=2; $index++){
			if(@$_POST['newdim'.$index]!='' && @$_POST['thumbdim'.$index]!=''){
				$id = getimagesize($imagefile['tmp_name']);
				$imtype = $id[2];
				// Calculate new width and height
				if($_POST['thumbdim'.$index]=='1'){ // Width
					$newwidth = (double)$_POST['newdim'.$index];
					$newheight = $id[1] / ($id[0] / $newwidth);
				}elseif($_POST['thumbdim'.$index]=='2'){ // Height
					$newheight = (double)$_POST['newdim'.$index];
					$newwidth = $id[0] / ($id[1] / $newheight);
				}
				$imres = imagecreatefromstring($tfile);
				$lastdot = strrpos($filepath, '.');
				if($lastdot!==FALSE){
					$thesuffix = @$_POST['suffix'.$index];
					if($thesuffix=='') $thesuffix='_small';
					$thumbname = substr_replace($filepath, $thesuffix.'.', $lastdot, 1);
					$newimres = imagecreatetruecolor($newwidth, $newheight);
					imagecopyresampled($newimres, $imres, 0, 0, 0, 0, $newwidth, $newheight, $id[0], $id[1]);
					if($imtype==IMAGETYPE_JPEG){
						imagejpeg($newimres, $thumbname, 80);
					}elseif($imtype==IMAGETYPE_GIF){
						imagegif($newimres, $thumbname);
					}elseif($imtype==IMAGETYPE_PNG){
						imagepng($newimres, $thumbname);
					}
					if(@$_POST['populate']=='ON') writeimagejs(@$_POST['defimagepath'], basename($thumbname), $index);
				}
				imagedestroy($imres);
				imagedestroy($newimres);
			}
			if(trim(@$_POST['hasrow'.$index])){
				setcookie('newdim'.$index,trim(@$_POST['newdim'.$index]),time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
				setcookie('suffix'.$index,trim(@$_POST['suffix'.$index]),time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
				setcookie('thumbdim'.$index,trim(@$_POST['thumbdim'.$index]),time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
			}
			if($index==1) setcookie('populate',trim(@$_POST['populate']),time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
		}
	}
	print '<p align="center"><a href="javascript:window.close()"><strong>'.$yyClsWin.'</strong></a></p>';
} ?>
</body>
</html>
