<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr=='') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
$alreadygotadmin = getadminsettings();
$dorefresh=FALSE;
function dodeletecert($gcid){
	$sSQL = "SELECT gcCartID FROM giftcertificate WHERE gcID='" . escape_string($gcid) . "'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$cartID = $rs['gcCartID'];
		if($cartID!=0) ect_query("DELETE FROM cart WHERE cartCompleted=0 AND cartID=".$cartID) or ect_error();
	}
	ect_free_result($result);
	$sSQL = "DELETE FROM giftcertificate WHERE gcID='" . escape_string($gcid) . "'";
	ect_query($sSQL) or ect_error();
	$sSQL = "DELETE FROM giftcertsapplied WHERE gcaGCID='" . escape_string($gcid) . "'";
	ect_query($sSQL) or ect_error();
}
if(getpost('posted')=="1" || getget('act')=='deleteassoc'){
	if(getpost('act')=='confirm'){
		$sSQL = "UPDATE giftcertificate SET gcAuthorized=1 WHERE gcID='" . escape_string(getpost('id')) . "'";
		ect_query($sSQL) or ect_error();
	}elseif(getpost('act')=='delete'){
		dodeletecert(getpost('id'));
		$dorefresh=TRUE;
	}elseif(getpost('act')=='quickupdate'){
		foreach(@$_POST as $objItem => $objValue){
			if(substr($objItem, 0, 4)=='pra_'){
				$theid = str_replace('ect_dot_xzq','.',substr($objItem, 4));
				$theval = trim(unstripslashes($objValue));
				$pract = getpost('pract');
				$sSQL = '';
				if($pract=='del'){
					dodeletecert($theid);
					$sSQL = '';
				}
				if($sSQL!=''){
					$sSQL.=' WHERE rtID='.$theid;
					ect_query($sSQL) or ect_error();
				}
			}
		}
		$dorefresh=TRUE;
	}elseif(getget('act')=='deleteassoc'){
		if(getget('refund')=='true'){
			$sSQL = "SELECT gcaAmount FROM giftcertsapplied WHERE gcaGCID='" . getget('id') . "' AND gcaOrdID=" . getget('ord');
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$sSQL = "UPDATE giftcertificate SET gcRemaining=gcRemaining+" . $rs['gcaAmount'] . " WHERE gcID='" . getget('id') . "'";
				ect_query($sSQL) or ect_error();
			}
			ect_free_result($result);
		}
		$sSQL = "DELETE FROM giftcertsapplied WHERE gcaGCID='" . getget('id') . "' AND gcaOrdID=" . getget('ord');
		ect_query($sSQL) or ect_error();
	}elseif(getpost('act')=="doaddnew"){
		$sSQL = "SELECT gcID FROM giftcertificate WHERE gcID='" . strtoupper(escape_string(getpost('gcid'))) . "'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0) $success=FALSE; $errmsg = 'Duplicate Gift Certificate ID';
		ect_free_result($result);
		if($success){
			$sSQL = 'INSERT INTO giftcertificate (gcID,gcFrom,gcTo,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,';
			if(getpost('gcdateused')<>"") $sSQL.="gcDateUsed,";
			$sSQL.="gcAuthorized,gcMessage) VALUES (" .
				"'" . strtoupper(escape_string(getpost('gcid'))) . "'" .
				",'" . escape_string(getpost('gcfrom')) . "'" .
				",'" . escape_string(getpost('gcto')) . "'" .
				",'" . escape_string(getpost('gcemail')) . "'" .
				"," . escape_string(getpost('gcorigamount')) .
				"," . escape_string(getpost('gcremaining')) .
				",'" . (getpost('gcdatecreated')!='' ? date('Y-m-d', parsedate(getpost('gcdatecreated'))) : date('Y-m-d')) . "'";
			if(getpost('gcdateused')!='') $sSQL.=",'" . date('Y-m-d', parsedate(getpost('gcdateused'))) . "'";
			$sSQL.="," . escape_string(getpost('gcauthorized')) .
			",'" . escape_string(getpost('gcmessage')) . "')";
			ect_query($sSQL) or ect_error();
			$dorefresh=TRUE;
			
			if(getpost('emailrecipient')=='ON'){
				$sSQL = 'SELECT '.getlangid('giftcertsubject',4096).','.getlangid('giftcertemail',4096).','.getlangid('giftcertsendersubject',4096).','.getlangid('giftcertsender',4096).' FROM emailmessages WHERE emailID=1';
				$result2=ect_query($sSQL) or ect_error();
				if($rs2=ect_fetch_assoc($result2)){
					$giftcertsubject = trim($rs2[getlangid('giftcertsubject',4096)]);
					$emailBody = trim($rs2[getlangid('giftcertemail',4096)]);
					$senderSubject = trim($rs2[getlangid('giftcertsendersubject',4096)]);
					$senderBody = trim($rs2[getlangid('giftcertsender',4096)]);
				}
				ect_free_result($result2);
				$emailBody = str_replace('%toname%', getpost('gcto'), $emailBody);
				$emailBody = str_replace('%fromname%', getpost('gcfrom'), $emailBody);
				$emailBody = str_replace('%value%', FormatEuroCurrency(getpost('gcorigamount')), $emailBody);
				$emailBody = replaceemailtxt($emailBody, '%message%', getpost('gcmessage'), $replaceone);
				$emailBody = str_replace('%storeurl%', $storeurl, $emailBody);
				$emailBody = str_replace('%certificateid%', getpost('gcid'), $emailBody);
				$emailBody = str_replace('<br />', $emlNl, $emailBody);
				dosendemail(getpost('gcemail'), $emailAddr, '', str_replace('%fromname%', getpost('gcfrom'), $giftcertsubject), $emailBody);
			//	$senderBody = str_replace('%toname%', getpost('gcto'), $senderBody);
			//	dosendemail($custEmail, $sEmail, '', str_replace('%toname%', getpost('gcto'), $senderSubject), $senderBody . $emlNl . $emailBody);
			}
		}
	}elseif(getpost('act')=="domodify"){
		$sSQL = "UPDATE giftcertificate SET " .
			"gcID='" . strtoupper(escape_string(getpost('gcid'))) . "'" .
			",gcFrom='" . escape_string(getpost('gcfrom')) . "'" .
			",gcTo='" . escape_string(getpost('gcto')) . "'" .
			",gcEmail='" . escape_string(getpost('gcemail')) . "'" .
			",gcOrigAmount=" . escape_string(getpost('gcorigamount')) .
			",gcRemaining=" . escape_string(getpost('gcremaining')) .
			",gcDateCreated='" . (getpost('gcdatecreated')!='' ? date('Y-m-d', parsedate(getpost('gcdatecreated'))) : date('Y-m-d')) . "'";
		if(getpost('gcdateused')!='') $sSQL.=",gcDateUsed='" . date('Y-m-d', parsedate(getpost('gcdateused'))) . "'";
		$sSQL.=",gcAuthorized=" . escape_string(getpost('gcauthorized')) .
			",gcMessage='" . escape_string(getpost('gcmessage')) . "'" .
			" WHERE gcID='" . strtoupper(escape_string(getpost('id'))) . "'";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=="purgeunconfirmed"){
		$sSQL = "DELETE FROM giftcertificate WHERE isconfirmed=0 AND mlConfirmDate<'" . date('Y-m-d', time()-($mailinglistpurgedays*60*60*24)) . "'";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=admingiftcert.php';
	print '?stext=' . urlencode(getpost('stext')) . '&stype=' . getpost('stype') . '&status=' . getpost('status') . '&pg=' . getpost('pg');
	print '">';
}
if(getget('id')!='' || (getpost('posted')=='1' && (getpost('act')=='modify' || getpost('act')=='addnew' || getpost('act')=='clone'))){
?>
<script type="text/javascript">
<!--
function getgcchar(){
	var gcchar='';
	while(gcchar=="" || gcchar=="O" || gcchar=="I" || gcchar=="Q"){
		gcchar = String.fromCharCode('A'.charCodeAt(0)+Math.round(Math.random()*25));
	}
	return(gcchar);
}
function randomgc(){
	var rannum = Math.floor((Math.random()*899999999)+100000000);
	rannum = getgcchar() + getgcchar() + rannum + getgcchar();
	document.getElementById("gcid").value=rannum;
}
function formvalidator(theForm){
if (theForm.gcid.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyCerNum)?>\".");
theForm.gcid.focus();
return (false);
}
if (theForm.gcto.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyTo)?>\".");
theForm.gcto.focus();
return (false);
}
if (theForm.gcfrom.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyFrom)?>\".");
theForm.gcfrom.focus();
return (false);
}
if (theForm.gcemail.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyEmail)?>\".");
theForm.gcemail.focus();
return (false);
}
if (theForm.gcorigamount.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyOriAmt)?>\".");
theForm.gcorigamount.focus();
return (false);
}
if (theForm.gcremaining.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyRemain)?>\".");
theForm.gcremaining.focus();
return (false);
}
return (true);
}
//-->
</script>
<script type="text/javascript" src="popcalendar.js"></script>
<script type="text/javascript">try{languagetext('<?php print @$adminlang?>');}catch(err){}</script>
		  <form name="mainform" method="post" action="admingiftcert.php" onsubmit="return formvalidator(this)">
<?php		writehiddenvar("posted", "1");
			if(getpost('act')=='modify' || getget('id')!='')
				writehiddenvar("act", "domodify");
			else
				writehiddenvar("act", "doaddnew");
			writehiddenvar("stext", getpost('stext'));
			writehiddenvar("status", getpost('status'));
			writehiddenvar("stype", getpost('stype'));
			writehiddenvar("pg", getpost('pg'));
			writehiddenvar("id", @$_REQUEST['id']); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print (getpost('act')=='clone'?$yyClone.': ':(getpost('act')=='modify'?$yyModify.': ':'')) . $yyGCMan . "<br />&nbsp;" ?></strong></td>
			  </tr>
<?php	if(getpost('act')=='modify' || getpost('act')=='clone' || getget('id')!=''){
			$sSQL = "SELECT gcID,gcTo,gcFrom,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,gcDateUsed,gcAuthorized,gcMessage,gcCartID FROM giftcertificate WHERE gcID='" . escape_string(@$_REQUEST['id']) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$gcid = $rs['gcID'];
				$gcto = $rs['gcTo'];
				$gcfrom = $rs['gcFrom'];
				$gcemail = $rs['gcEmail'];
				$gcorigamount = $rs['gcOrigAmount'];
				$gcremaining = $rs['gcRemaining'];
				$gcdatecreated = $rs['gcDateCreated'];
				if(is_null($gcdatecreated)) $gcdatecreated = date($admindatestr, time() + ($dateadjust*60*60)); else $gcdatecreated = date($admindatestr, strtotime($gcdatecreated));
				$gcdateused = $rs['gcDateUsed'];
				if(is_null($gcdateused)) $gcdateused = date($admindatestr, time() + ($dateadjust*60*60)); else $gcdateused = date($admindatestr, strtotime($gcdateused));
				$gcauthorized = $rs['gcAuthorized'];
				$gcmessage = $rs['gcMessage'];
				$gccartid = $rs['gcCartID'];
			}
			ect_free_result($result); ?>
<?php	}else{
			$gcid = "";
			$gcto = "";
			$gcfrom = "";
			$gcemail = "";
			$gcorigamount = "";
			$gcremaining = "";
			$gcdatecreated = date($admindatestr, time() + ($dateadjust*60*60));
			$gcdateused = "";
			$gcauthorized = 0;
			$gcmessage = "";
			$gccartid = 0;
		}
		$themask = 'yyyy-mm-dd';
		if($admindateformat==1)
			$themask='mm/dd/yyyy';
		elseif($admindateformat==2)
			$themask='dd/mm/yyyy'; ?>
			  <tr>
				<td align="right"><p><strong><?php print $yyCerNum?>:</strong></td>
				<td align="left"><?php
		if(getpost('act')=="modify")
			print '<input type="hidden" name="gcid" id="gcid" value="'.htmlspecials($gcid).'" /><strong>' . htmlspecials($gcid) . '</strong>';
		else
			print '<input type="text" name="gcid" id="gcid" size="22" value="'.htmlspecials($gcid).'" /> <input type="button" value="Random" onclick="randomgc()" /></td>';
?>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyTo?>:</strong></td>
				<td align="left"><input type="text" name="gcto" size="34" value="<?php print htmlspecials($gcto)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyFrom?>:</strong></td>
				<td align="left"><input type="text" name="gcfrom" size="34" value="<?php print htmlspecials($gcfrom)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyEmail?>:</strong></td>
				<td align="left"><input type="text" name="gcemail" size="34" value="<?php print htmlspecials($gcemail)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyOriAmt?>:</strong></td>
				<td align="left"><input type="text" name="gcorigamount" size="10" value="<?php print htmlspecials($gcorigamount)?>" <?php if(getpost('act')=='addnew') print "onchange=\"document.getElementById('gcremaining').value=this.value\" " ?>/></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyRemain?>:</strong></td>
				<td align="left"><input type="text" id="gcremaining" name="gcremaining" size="10" value="<?php print htmlspecials($gcremaining)?>" /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyDatPur?>:</strong></td>
				<td align="left"><input type="text" name="gcdatecreated" size="10" value="<?php print $gcdatecreated?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.mainform.gcdatecreated, '<?php print $themask?>', -200)" value='DP' /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyDatUsd?>:</strong></td>
				<td align="left"><input type="text" name="gcdateused" size="10" value="<?php print $gcdateused?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.mainform.gcdateused, '<?php print $themask?>', -200)" value='DP' /></td>
			  </tr>
			  <tr>
				<td align="right"><p><strong><?php print $yyAuthd?>:</strong></td>
				<td align="left"><select name="gcauthorized" size="1">
						<option value="0"><?php print $yyNo?></option>
						<option value="1" <?php if($gcauthorized!=0||getpost('act')=='addnew') print 'selected' ?>><?php print $yyYes?></option></select>
				</td>
			  </tr>
<?php	if(getpost('act')=='addnew'){ ?>
			  <tr>
				<td align="right"><p><strong>Email Recipient:</strong></td>
				<td align="left"><input type="checkbox" name="emailrecipient" value="ON" /></td>
			  </tr>
<?php	} ?>
			  <tr>
				<td align="right"><p><strong><?php print $yyMessag?>:</strong></td>
				<td align="left"><textarea name="gcmessage" cols="60" rows="5" wrap="virtual"><?php print $gcmessage?></textarea></td>
			  </tr>
<?php	if($gccartid!=0){
			$sSQL = "SELECT cartOrderID FROM cart WHERE cartID=" . $gccartid;
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)) $gcorderid = $rs['cartOrderID']; else $gcorderid = 0;
			ect_free_result($result);
?>
			  <tr>
				<td align="right"><p><strong><?php print $yyPurOrd?>:</strong></td>
				<td align="left"><?php if($gcorderid==0) print $yyUncOrd; else print '('.$gcorderid.') <a href="adminorders.php?id='.$gcorderid.'">'.$yyClkVw.'.</a>'?></td>
			  </tr>
<?php	}
	if($gcid!=''){
		$sSQL = "SELECT gcaOrdID,gcaAmount FROM giftcertsapplied WHERE gcaGCID='".$gcid."'";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){ ?>
			  <tr>
				<td align="right"><p><strong><?php print $yyConOrd?>:</strong></td>
				<td align="left"><?php print FormatEuroCurrency($rs['gcaAmount']) . ' (' . $rs['gcaOrdID']. ') <input type="button" value="' . $yyView . '" onclick="document.location=\'adminorders.php?id=' . $rs['gcaOrdID'] . '\'" /> <input type="button" value="' . $yyDelete . '" onclick="document.location=\'admingiftcert.php?act=deleteassoc&ord=' . $rs['gcaOrdID']. '&id='.$gcid.'\'" /> <input type="button" value="'.$yyDelRef.'" onclick="document.location=\'admingiftcert.php?act=deleteassoc&refund=true&ord='.$rs['gcaOrdID'].'&id='.$gcid.'\'" />'?></td>
			  </tr>
<?php	}
		ect_free_result($result);
	}
?>
			  <tr>
                <td width="100%" colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</td>
			  </tr>
			  <tr>
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
<?php
}elseif(getpost('posted')=="1" && getpost('act')!="confirm" && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="admingiftcert.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;<br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(getpost('posted')=="1" && getpost('act')!="confirm"){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a><p>&nbsp;</p><p>&nbsp;</p></td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}else{
	$jscript='';
	$sSQL = "SELECT count(*) AS thecount FROM giftcertificate WHERE gcRemaining>0";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $numemails = $rs['thecount']; else $numemails=0;
	ect_free_result($result);
	$modclone = @$_COOKIE['modclone'];
	$pract=@$_COOKIE['practgc'] ?>
<script type="text/javascript">
<!--
function setCookie(c_name,value,expiredays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
function mr(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function cr(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "clone";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function crec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "confirm";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function newrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function sendem(id) {
	document.mainform.act.value = "sendem";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function dr(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")) {
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="admingiftcert.php";
	document.mainform.act.value = "search";
	document.mainform.listem.value = "";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function quickupdate(){
	if(document.mainform.pract.value=="del"){
		if(!confirm("<?php print jscheck($yyConDel)?>\n"))
			return;
	}
	document.mainform.action="admingiftcert.php";
	document.mainform.act.value = "quickupdate";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function listem(thelet){
	document.mainform.action="admingiftcert.php";
	document.mainform.act.value = "search";
	document.mainform.listem.value = thelet;
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function removeuncon(){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.act.value = "purgeunconfirmed";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
}
function changepract(obj){
	setCookie('practgc',obj[obj.selectedIndex].value,600);
	startsearch();
}
var currcheck=true;
function checkboxes(){
	if(document.getElementById("resultcounter")){
		maxitems=document.getElementById("resultcounter").value;
		for(index=0;index<maxitems;index++){
			document.getElementById("chkbx"+index).checked=currcheck;
		}
		currcheck=!currcheck;
	}
}
function changemodclone(modclone){
	setCookie('modclone',modclone[modclone.selectedIndex].value,600);
	startsearch();
}
// -->
</script>
<h2><?php print $yyAdmGif?></h2>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		<form name="mainform" method="post" action="admingiftcert.php">
		  <td width="100%">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="listem" value="<?php print @$_REQUEST['listem']?>" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print (getpost('act')=="search" ? "1" : getget('pg'))?>" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
				<td class="cobhl" colspan="4" align="center"><strong><?php
					print $numemails . " " . $yyActGC;
				?><strong></td>
			  </tr>
			  <tr> 
				<td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print @$_REQUEST['stext']?>" /></td>
				<td class="cobhl" align="right"><?php print $yyStatus?>:</td>
				<td class="cobll"><select name="status" size="1">
					<option value="any">All Certificates</option>
					<option value="" <?php if(@$_REQUEST['status']=='') print 'selected'?>><?php print $yyActGC?></option>
					<option value="spent" <?php if(@$_REQUEST['status']=="spent") print 'selected'?>><?php print $yyInaGC?></option>
					</select>
				</td>
			  </tr>
			  <tr>
				<td class="cobhl" align="right"><?php
					if($pract=="del" || $pract=="app"){ ?>
						<input type="button" value="<?php print $yyCheckA?>" onclick="checkboxes(true);" style="float:left" />
<?php				}
					print $yySrchTp?>:</td>
				<td class="cobll"><select name="stype" size="1">
					<option value=""><?php print $yySrchAl?></option>
					<option value="any" <?php if(@$_REQUEST['stype']=="any") print 'selected'?>><?php print $yySrchAn?></option>
					<option value="exact" <?php if(@$_REQUEST['stype']=="exact") print 'selected'?>><?php print $yySrchEx?></option>
					</select>
				</td>
				<td class="cobll" colspan="2" align="center">
						<input type="button" value="<?php print $yyListRe?>" onclick="startsearch();" />
						<input type="button" value="New Gift Certificate" onclick="newrec();" />
				</td>
			  </tr>
			</table>
<br />
	  <table width="100%" class="stackable admin-table-a sta-white">
<?php
	$resultcounter=0;
	$hasheader=FALSE;
	if(getpost('act')=='search' || getget('pg')!='' || getpost('act')=='confirm'){
		function displayprodrow($xrs){
			global $yyModify,$yyDelete,$modclone,$resultcounter,$pract,$jscript;
			if($xrs['gcAuthorized']!=0){ $startstyle=''; $endstyle=''; } else{ $startstyle='<span style="color:#FF0000">'; $endstyle='</span>'; }
			$jscript.='pa['.$resultcounter.']=[';
			?><tr id="tr<?php print $resultcounter?>"><td class="minicell"><?php
				if($pract=='del')
					print '<input type="checkbox" id="chkbx'.$resultcounter.'" name="pra_'.$xrs['gcID'].'" value="del" tabindex="'.($resultcounter+1).'"/>';
				else
					print '&nbsp;';
			?></td><td><?php print $startstyle . htmlspecials($xrs['gcID']) . $endstyle?></td>
			<td><?php print $startstyle . htmlspecials($xrs['gcTo']) . $endstyle?></td>
			<td><?php print $startstyle . htmlspecials($xrs['gcFrom']) . $endstyle?></td>
			<td><?php print $startstyle . FormatEuroCurrency($xrs['gcOrigAmount']) . $endstyle?></td>
			<td><?php print $startstyle . FormatEuroCurrency($xrs['gcRemaining']) . $endstyle?></td>
<td><?php print $startstyle . htmlspecials($xrs['gcDateCreated']) . $endstyle?></td><td align="center">-</td></tr>
<?php	}
		function displayheaderrow(){
			global $yyCerNum,$yyTo,$yyFrom,$yyAmount,$yyRemain,$yyDate,$yyModify,$yyDelete,$modclone,$pract; ?>
			<tr>
				<th class="minicell">
					<select name="pract" id="pract" size="1" onchange="changepract(this)">
					<option value="none">Quick Entry...</option>
					<option value="" disabled="disabled">------------------</option>
					<option value="del"<?php if($pract=='del') print ' selected="selected"'?>><?php print $yyDelete?></option>
					</select></th>
				<th class="maincell"><?php print $yyCerNum?></th>
				<th class="maincell"><?php print $yyTo?></th>
				<th class="maincell"><?php print $yyFrom?></th>
				<th class="maincell"><?php print $yyAmount?></th>
				<th class="maincell"><?php print $yyRemain?></th>
				<th class="maincell"><?php print $yyDate?></th>
				<th class="minicell"><?php print $yyModify?></th>
			</tr>
<?php	}
		$whereand = ' WHERE ';
		$sSQL = 'SELECT gcID,gcTo,gcFrom,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,gcDateUsed,gcAuthorized FROM giftcertificate ';
		if(trim(@$_REQUEST['stext'])!=''){
			$sText = escape_string(@$_REQUEST['stext']);
			$Xstext = escape_string(@$_REQUEST['stext']);
			$aText = explode(' ',$Xstext);
			$aFields[0]="gcID";
			$aFields[1]="gcTo";
			$aFields[2]="gcFrom";
			$aFields[3]="gcEmail";
			if(@$_REQUEST['stype']=="exact")
				$sSQL.=$whereand . " (gcID LIKE '%" . $Xstext . "%' OR gcTo LIKE '%" . $Xstext . "%' OR gcFrom LIKE '%" . $Xstext . "%' OR gcEmail LIKE '%" . $Xstext . "%') ";
			else{
				if(@$_REQUEST['stype']=="any") $sJoin="OR "; else $sJoin="AND ";
				$sSQL.=$whereand . "(";
				$whereand=' AND ';
				for($index=0;$index<=3;$index++){
					$sSQL.="(";
					$rowcounter=0;
					$arrelms=count($aText);
					foreach($aText as $theopt){
						if(is_array($theopt))$theopt=$theopt[0];
						$sSQL.=$aFields[$index] . " LIKE '%" . $theopt . "%' ";
						if(++$rowcounter < $arrelms) $sSQL.=$sJoin;
					}
					$sSQL.=") ";
					if($index < 3) $sSQL.="OR ";
				}
				$sSQL.=") ";
			}
			$whereand = " AND";
		}
		if(trim(@$_REQUEST['status'])==''){
			$sSQL.=$whereand . " (gcRemaining>0 AND gcAuthorized<>0)";
			$whereand = " AND";
		}elseif(trim(@$_REQUEST['status'])=='spent'){
			$sSQL.=$whereand . " (gcRemaining<=0 OR gcAuthorized=0)";
			$whereand = " AND";
		}
		$sSQL.=" ORDER BY gcDateCreated";
		if(! @is_numeric(getget('pg')))
			$CurPage = 1;
		else
			$CurPage = (int)getget('pg');
		if(@$admingiftcertsperpage=='')$admingiftcertsperpage=100;
		$tmpSQL = str_replace('SELECT gcID,gcTo,gcFrom,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,gcDateUsed,gcAuthorized', 'SELECT COUNT(*) AS bar', $sSQL);
		$allprods=ect_query($tmpSQL) or ect_error();
		$rs=ect_fetch_assoc($allprods);
		$iNumOfPages = ceil($rs['bar']/$admingiftcertsperpage);
		ect_free_result($allprods);
		$sSQL.=' LIMIT ' . ($admingiftcertsperpage*($CurPage-1)) . ', ' . $admingiftcertsperpage;
		$result=ect_query($sSQL) or ect_error();
		$resultcounter=0;
		if(ect_num_rows($result) > 0){
			$pblink = '<a href="admingiftcert.php?status=' . @$_REQUEST['status'] . '&stext=' . urlencode(@$_REQUEST['stext']) . '&stype=' . @$_REQUEST['stype'] . '&pg=';
			if($iNumOfPages > 1) print '<tr><td colspan="7" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			$hasheader=TRUE;
			displayheaderrow();
			$addcomma='';
			while($rs=ect_fetch_assoc($result)){
				displayprodrow($rs);
				$jscript.="'".$rs['gcID']."'];\r\n";
				$addcomma=',';
				$resultcounter++;
			}
			if($iNumOfPages > 1) print '<tr><td colspan="7" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
		}else{
			print '<tr><td width="100%" colspan="7" align="center"><br />' . $yyItNone . '<br />&nbsp;</td></tr>';
		}
		ect_free_result($result);
	} ?>
			  <tr>
<?php	if($hasheader){ ?>
				<td align="center" style="white-space:nowrap"><?php if($resultcounter>0 && $pract!='' && $pract!='none') print '<input type="hidden" name="resultcounter" id="resultcounter" value="'.$resultcounter.'" /><input type="button" value="'.$yyUpdate.'" onclick="quickupdate()" /> <input type="reset" value="'.$yyReset.'" />'; else print '&nbsp;'?></td>
<?php	} ?>
                <td width="100%" colspan="7" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table></td>
		  </form>
<script type="text/javascript">
/* <![CDATA[ */
var pa=[];
<?php print $jscript?>
for(var pidind in pa){
	var ttr=document.getElementById('tr'+pidind);
	ttr.cells[7].style.textAlign='center';
	ttr.cells[7].style.whiteSpace='nowrap';
	ttr.cells[7].innerHTML='<input type="button" value="M" style="width:30px" onclick="mr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyModify))?>" />&nbsp;' +
		'<input type="button" value="C" style="width:30px" onclick="cr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyClone))?>" />&nbsp;' +
		'<input type="button" value="X" style="width:30px" onclick="dr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyDelete))?>" />';
}
/* ]]> */
</script>
        </tr>
      </table>
<?php
}
?>