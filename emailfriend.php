<?php
session_cache_limiter('none');
session_start();
ob_start();
include 'vsadmin/db_conn_open.php';
include 'vsadmin/includes.php';
include 'vsadmin/inc/incfunctions.php';
$alreadygotadmin=getadminsettings();
if(@$storelang=='') $storelang='en'; // de dk en es fr it nl pt
include 'vsadmin/inc/languagefile_'.$storelang.'.php';
		if(@$inlinepopups!=TRUE){ ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php print jsenc($xxEmFrnd)?></title>
<link rel="stylesheet" type="text/css" href="style.css" />
<meta name="robots" content="noindex,nofollow" />
</head>
<body style="margin: 5px 5px 5px 5px;">
<?php	}else{
header('Cache-Control: no-cache');
header('Pragma: no-cache');
?>
<table width="500" border="0" id="emftable" style="position:absolute">
	<tr>
	  <td align="center" id="efrcell">
<?php	}
if(@$multiemfblockmessage=='') $multiemfblockmessage="I'm sorry. We are experiencing temporary difficulties at the moment. Please try again later.";
if(@$_REQUEST['askq']=='1' && @$useaskaquestion==TRUE) $isaskquestion=TRUE; else $isaskquestion=FALSE;
$extraparams=0;
function checkemfuserblock(){
	global $blockmultiemf;
	if(@$blockmultiemf=='') $blockmultiemf=20;
	$multiemfblocked=FALSE;
	$theip=@$_SERVER['REMOTE_ADDR'];
	if($theip=='') $theip='none';
	if(@$blockmultiemf!=''){
		ect_query("DELETE FROM multibuyblock WHERE lastaccess<'" . date('Y-m-d H:i:s', time()-(60*60*24)) . "'") or ect_error();
		$sSQL="SELECT ssdenyid,sstimesaccess FROM multibuyblock WHERE ssdenyip='EMF " . trim(escape_string($theip)) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			ect_query("UPDATE multibuyblock SET sstimesaccess=sstimesaccess+1,lastaccess='" . date('Y-m-d H:i:s', time()) . "' WHERE ssdenyid=" . $rs['ssdenyid']) or ect_error();
			if($rs['sstimesaccess'] >= $blockmultiemf) $multiemfblocked=TRUE;
		}else{
			ect_query("INSERT INTO multibuyblock (ssdenyip,lastaccess) VALUES ('EMF " . trim(escape_string($theip)) . "','" . date('Y-m-d H:i:s', time()) . "')") or ect_error();
		}
		ect_free_result($result);
	}
	if($theip=='none' || ip2long($theip)==FALSE)
		$sSQL='SELECT dcid FROM ipblocking LIMIT 0,1';
	else
		$sSQL='SELECT dcid FROM ipblocking WHERE (dcip1=' . ip2long($theip) . ' AND dcip2=0) OR (dcip1 <= ' . ip2long($theip) . ' AND ' . ip2long($theip) . ' <= dcip2 AND dcip2 <> 0)';
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result) > 0)
		$multiemfblocked=TRUE;
	return($multiemfblocked);
}
if(getpost('posted')=='1'){
	$success=TRUE;
	$referer=@$_SERVER['HTTP_REFERER'];
	$host=@$_SERVER['HTTP_HOST'];
	if(@$useemailfriend!=TRUE && @$useaskaquestion!=TRUE){
		$xxEFThk='<strong><font color="#FF0000">Email Friend / Ask a Question not enabled.</font></strong>';
	}elseif(strpos($referer, $host)===FALSE || getpost('efcheck')!=@$_SESSION['eftimestampcheck'] || (time()-$_SESSION['eftimestamp']) > (60*60)){
		$xxEFThk='<strong><font color="#FF0000">I\'m sorry but your email could not be sent at this time.</font></strong>';
		ob_end_clean();
		header('HTTP/1.1 401 Unauthorized');
		exit;
	}elseif(checkemfuserblock()){
		$xxEFThk='<strong><font color="#FF0000">' . $multiemfblockmessage . '</font></strong>';
		ob_end_clean();
		header('HTTP/1.1 403 Forbidden');
		exit;
	}else{
		$theprodid=substr(getpost('efid'),0,50);
		if($isaskquestion && @$useaskaquestion==TRUE)
			$friendsemail=$emailAddr;
		elseif(@$useemailfriend==TRUE && strlen(getpost('friendsemail'))<50)
			$friendsemail=str_replace(array("\r","\n"),'',getpost('friendsemail'));
		else
			$friendsemail='';
		$yourname=str_replace(array("\r","\n"), '', substr(getpost('yourname'),0,50));
		$youremail=str_replace(array("\r","\n"), '', substr(getpost('youremail'),0,50));
		$yourcomments=str_replace("\r\n",$emlNl,substr(getpost('yourcomments'),0,2000));
		if($isaskquestion){
			$seBody='PID: ' . getpost('origprodid') . $emlNl . $xxAskQue . ': ' . $yourname . $emlNl . $emlNl . $yourcomments . $emlNl;
			for($index=1;$index<=9;$index++){
				eval('$askquestionparam=@$askquestionparam'.$index.';');
				if($askquestionparam!='')
					$seBody.=$askquestionparam . ': ' . str_replace("\r\n",$emlNl,substr(getpost('askquestionparam' . $index),0,2000)) . $emlNl;
			}
			$thesubject=$xxAsqSub;
		}else{
			$seBody=$xxEFYF1 . $yourname . ' (' . $youremail . ')' . $xxEFYF2;
			if(getpost('yourcomments')!=''){
				$seBody.=$xxEFYF3 . $emlNl;
				$seBody.=$yourcomments . $emlNl;
			}else
				$seBody.='.' . $emlNl;
			$produrl='';
			if($theprodid!=''){
				$sSQL='SELECT pID,'.getlangid('pName',1).",pStaticPage,pStaticURL FROM products WHERE pID='" . escape_string($theprodid) . "'";
				$result=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result)) $produrl=getdetailsurl($rs['pID'],$rs['pStaticPage'],$rs[getlangid('pName',1)],$rs['pStaticURL'],'','');
				ect_free_result($result);
			}
			if(@$htmlemails==true){
				$storeLink=$storeurl;
				if(getpost('efid')!='') $storeLink.=$produrl;
				$seBody.=$emlNl . '<a href="' . $storeLink . '">' . $storeLink . '</a>';
			}else{
				$seBody.=$emlNl . $storeurl;
				if(getpost('efid')!='') $seBody.=$produrl;
			}
			$thesubject=$yourname . $xxEFRec;
		}
		$seBody.=$emlNl;
		if($friendsemail!='') dosendemail($friendsemail, $emailAddr, $youremail, $thesubject, $seBody);
	}
?>
<br />
  <table class="cobtbl emftbl" border="0" cellspacing="1" cellpadding="3" width="<?php print (@$inlinepopups==TRUE?'500':'100%')?>">
	<tr>
	  <td class="cobll emfll" colspan="2" align="center" width="100%"><p>&nbsp;</p>
	  <p><?php print ($isaskquestion?$xxAsqThk:$xxEFThk)?></p>
	  <p><?php print jsenc($xxClkClo)?></p>
	  <p>&nbsp;</p>
	  <?php print imageorbutton(@$imgefclose, jsenc($xxClsWin), '', (@$inlinepopups==TRUE?"document.body.removeChild(document.getElementById('efrdiv'))":'javascript:self.close()'), TRUE); ?>
	  <p>&nbsp;</p>
	  </td>
	</tr>
  </table>
<?php
}else{
	$eftimestamp=time();
	$eftimestampcheck=md5($eftimestamp.'This is a check'.$adminSecret.' A b H k');
	$_SESSION['eftimestamp']=$eftimestamp;
	$_SESSION['eftimestampcheck']=$eftimestampcheck;
	if(@$inlinepopups!=TRUE) emailfriendjavascript();
?>
<form id="efform" method="post" action="emailfriend.php" onsubmit="return efformvalidator(this)">
  <input type="hidden" name="posted" value="1" />
  <input type="hidden" id="efid" name="efid" value="<?php print htmlspecialchars(@$_GET['id'])?>" />
  <input type="hidden" id="efcheck" name="efcheck" value="<?php print $eftimestampcheck?>" />
  <input type="hidden" id="askq" name="askq" value="<?php print ($isaskquestion?"1":"")?>" />
  <table class="cobtbl emftbl" border="0" cellspacing="1" cellpadding="7" width="<?php print (@$inlinepopups==TRUE?'500':'100%')?>">
	<tr>
	  <td class="cobhl emfhl" bgcolor="#EBEBEB" colspan="2" align="center" width="100%" height="30"><?php print ($isaskquestion?$xxAskQue:$xxEmFrnd)?></td>
	</tr>
    <tr>
		<td class="cobll emfll" width="100%" align="left"><?php print ($isaskquestion?$xxAQBlr:$xxEFBlr)?><br />
      <br /><?php print $redstar . jsenc($xxEFNam)?><br /><input type="text" id="yourname" name="yourname" size="30" /><br />
      <?php print $redstar . jsenc($xxEFEm)?><br /><input type="text" id="youremail" name="youremail" size="30" /><br />
<?php
	if(! $isaskquestion){
		print $redstar . jsenc($xxEFFEm)?><br /><input type="text" id="friendsemail" name="friendsemail" size="30" /><br />
<?php
	}else{
		for($index=1;$index<=9;$index++){
			eval('$askquestionparam=@$askquestionparam'.$index.';');
			eval('$askquestionrequired=@$askquestionrequired'.$index.';');
			eval('$askquestionhtml=@$askquestionhtml'.$index.';');
			if($askquestionparam!=''){
				$extraparams++;
				if($askquestionrequired) print $redstar;
				print $askquestionparam . '<br />';
				if($askquestionhtml!='') print str_replace('ectfield','askquestionparam'.$index,$askquestionhtml) . '<br />'; else print '<input type="text" id="askquestionparam'.$index.'" name="askquestionparam'.$index.'" size="30" /><br />';
			}
		}
		$theproduct=trim(substr(@$_GET['id'],0,50));
		$sSQL='SELECT '.getlangid('pName',1)." FROM products WHERE pID='" . escape_string($theproduct) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result))
			$theproduct.=' - ' . $rs[getlangid('pName',1)];
		ect_free_result($result);
		writehiddenidvar('origprodid',$theproduct);
	} ?>
    <?php print $redstar . jsenc($xxEFCmt)?><br /><textarea id="yourcomments" name="yourcomments" cols="46" rows="6"><?php print ($isaskquestion ? htmlspecials(str_replace('%nl%',"\r\n",$xxAskCom) . $theproduct) : '')?></textarea>
		<p align="center"><?php
	if(@$inlinepopups==TRUE)
		print imageorbutton(@$imgefsend, jsenc($xxSend), '', 'dosendefdata()', TRUE);
	else
		print imageorsubmit(@$imgefsend, jsenc($xxSend), '');
	print '&nbsp;&nbsp;';
	print imageorbutton(@$imgefclose, jsenc($xxClsWin), '', (@$inlinepopups==TRUE?"document.body.removeChild(document.getElementById('efrdiv'))":'javascript:self.close()'), TRUE);
?></p>
      </td>
	</tr>
  </table>
</form>
<?php
	if(@$inlinepopups==TRUE){ ?>
	</td>
  </tr>
</table>
<?php
	}else{ ?>
<script type="text/javascript">
window.resizeBy(0,<?php print $extraparams*40 ?>);
</script>
<?php
	}
}
if(@$inlinepopups!=TRUE){ ?>
</body>
</html>
<?php
} ?>