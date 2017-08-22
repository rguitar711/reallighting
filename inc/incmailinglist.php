<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$dateformatstr=='') $dateformatstr='m/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
$themask='yyyy-mm-dd';
if($admindateformat==1)
	$themask='mm/dd/yyyy';
elseif($admindateformat==2)
	$themask='dd/mm/yyyy';

if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
if(@$mailinglistpurgedays=='') $mailinglistpurgedays=32;
$alreadygotadmin=getadminsettings();
$dorefresh=FALSE;
$haserror=FALSE;
if(strtolower($adminencoding)=='iso-8859-1') $raquo='»'; else $raquo='>';
function writemenulevel($id,$itlevel){
	global $allcatsa,$numcats,$thecat,$raquo;
	if($itlevel<10){
		for($wmlindex=0; $wmlindex < $numcats; $wmlindex++){
			if($allcatsa[$wmlindex]['topSection']==$id){
				print "<option value='" . $allcatsa[$wmlindex]['sectionID'] . "'";
				if(is_array($thecat)){
					foreach($thecat as $catid){
						if($allcatsa[$wmlindex]['sectionID']==(int)$catid) print ' selected="selected"';
					}
				}
				print '>';
				for($index=0; $index < $itlevel-1; $index++)
					print $raquo . ' ';
				print $allcatsa[$wmlindex]['sectionWorkingName'] . "</option>\r\n";
				if($allcatsa[$wmlindex]['rootSection']==0) writemenulevel($allcatsa[$wmlindex]['sectionID'],$itlevel+1);
			}
		}
	}
}
function hiddenparams(){
	if(is_array($ordstate=@$_REQUEST['ordstate'])) $ordstate=implode(',',$ordstate);
	if(is_array($ordcountry=@$_REQUEST['ordcountry'])) $ordcountry=implode(',',$ordcountry);
	if(is_array($smanufacturer=@$_REQUEST['smanufacturer'])) $smanufacturer=implode(',',$smanufacturer);
	if(is_array($thecat=@$_REQUEST['scat'])) $thecat=implode(',',$thecat);
	writehiddenvar('stext', getpost('stext'));
	writehiddenvar('mindate', getpost('mindate'));
	writehiddenvar('maxdate', getpost('maxdate'));
	writehiddenvar('listem', getpost('listem'));
	writehiddenvar('stype', getpost('stype'));
	writehiddenvar('pg', getpost('pg'));
	writehiddenvar('id', getpost('id'));
	writehiddenvar('ordstate', $ordstate);
	writehiddenvar('ordcountry', $ordcountry);
	writehiddenvar('smanufacturer', $smanufacturer);
	writehiddenvar('scat', $thecat);
	writehiddenvar('stsearch', getpost('stsearch'));
	writehiddenvar('swholesale', getpost('swholesale'));
	writehiddenvar('sortorder', getpost('sort'));
}
if(getpost('posted')=='1'){
	if(getpost('act')=='confirm'){
		$sSQL="UPDATE mailinglist SET isconfirmed=1 WHERE email='" . escape_string(getpost('id')) . "'";
		ect_query($sSQL) or ect_error();
	}elseif(getpost('act')=='delete'){
		$sSQL="DELETE FROM mailinglist WHERE email='" . escape_string(getpost('id')) . "'";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='doaddnew'){
		$sSQL="INSERT INTO mailinglist (email,mlName,isconfirmed,mlConfirmDate,mlIPAddress) VALUES ('" . escape_string(strtolower(getpost('email'))) . "','" . escape_string(getpost('mlname')) . "',1,'".date('Y-m-d', time())."','".escape_string(getipaddress())."')";
		@ect_query($sSQL);
		$dorefresh=TRUE;
	}elseif(getpost('act')=='domodify'){
		if(strtolower(getpost("email"))!=strtolower(getpost("id"))){
			$sSQL="SELECT email FROM mailinglist WHERE email='" . escape_string(strtolower(getpost('email'))) . "'";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0) $haserror=TRUE;
			ect_free_result($result);
		}
		if($haserror)
			$errormessage='Cannot rename email from &quot;' . htmlspecials(getpost('id')) . '&quot; to &quot;' . htmlspecials(getpost('email')) . '&quot; as that address is already in use.';
		else{
			$sSQL="UPDATE mailinglist SET email='" . escape_string(strtolower(getpost('email'))) . "',mlName='" . escape_string(getpost('mlname')) . "' WHERE email='" . escape_string(strtolower(getpost('id'))) . "'";
			ect_query($sSQL) or ect_error();
			$dorefresh=TRUE;
		}
	}elseif(getpost('act')=='purgeunconfirmed'){
		$sSQL="DELETE FROM mailinglist WHERE isconfirmed=0 AND mlConfirmDate<'".date('Y-m-d', time()-($mailinglistpurgedays*60*60*24))."'";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='clearsent'){
		$sSQL="UPDATE mailinglist SET emailsent=0";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminmailinglist.php' .
		'?stext=' . urlencode(getpost('stext')) .
		'&ordstate=' . urlencode(getpost('ordstate')) .
		'&ordcountry=' . urlencode(getpost('ordcountry')) .
		'&smanufacturer=' . urlencode(getpost('smanufacturer')) .
		'&scat=' . urlencode(getpost('scat')) .
		'&stsearch=' . getpost('stsearch') .
		'&swholesale=' . getpost('swholesale') .
		'&sortorder=' . getpost('sortorder') .
		'&stype=' . urlencode(getpost('stype')) .
		'&listem=' . urlencode(getpost('listem')) .
		'&mindate=' . getpost('mindate') .
		'&maxdate=' . getpost('maxdate') .
		'&pg=' . urlencode(getpost('pg'));
	print '">';
}
if(getpost('posted')=='1' && $haserror){
	print '<div style="padding:50px;text-align:center">' . $yySorErr . '</div>';
	print '<div style="padding:50px;text-align:center;color:#FF1010">' . $errormessage . '</div>';
	print '<div style="padding:50px;text-align:center"><input type="button" onclick="history.go(-1)" value="' . $yyClkBac . '" /></div>';
}elseif(getpost('posted')=='1' && getpost('act')=='dosendem'){
	@set_time_limit(1800);
	$breatherseconds=(@$debugmode?10:300);
?>
<script type="text/javascript">
/* <![CDATA[ */
function breatherfunction(){
	var breathersecs=document.getElementById('breathersecs');
	document.getElementById('emerrordiv').innerHTML='<?php print 'Taking breather. Sending next batch in <span id="breathersecs">'.$breatherseconds.'</span> seconds.'?><br /><br />'+document.getElementById('emerrordiv').innerHTML;
	setTimeout('document.getElementById(\'breatherform\').submit();',<?php print ($breatherseconds*1000)?>);
	setInterval('document.getElementById(\'breathersecs\').innerHTML=Math.max(parseInt(document.getElementById(\'breathersecs\').innerHTML)-1,0)',1000);
}
function nextbatchfunction(){
	document.getElementById('emerrordiv').innerHTML+='<div style="text-align:center"><input type="button" value="Send Next Batch Now" onclick="document.getElementById(\'breatherform\').submit();" /></div>';
	document.getElementById('totalsentsofar').value='';
}
/* ]]> */
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminmailinglist.php" onsubmit="return formvalidator(this)">
<?php		writehiddenvar("posted", "1");
			writehiddenvar("act", "dosendem");
			hiddenparams(); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="1">
			  <tr>
                <td align="center"><strong><?php print $yyMaLiMa?> - Sending Emails</strong></td>
			  </tr>
			  <tr> 
                <td align="center"><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />
				<strong>Please do not refresh this page</strong>
				<br />&nbsp;<br />Sending email: <span name="sendspan" id="sendspan">1</span>
				<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><div id="emerrordiv"></div><br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;
				</td>
			  </tr>
			  <tr>
                <td align="center"><br />
                          <a href="adminmailinglist.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
      </table>
<?php
	if(getpost('emformat')=='1' || getpost('emformat')=='2') $htmlemails=TRUE; else $htmlemails=FALSE;
	$sendemailerrnum=0;
	$sendemailerrdesc='';
	$batchesof=getpost('batchesof');
	$takebreather=(getpost("takebreather")=="ON");
	$totalsentsofar=getpost("totalsentsofar");
	$takingbreather=FALSE;
	if(! is_numeric($totalsentsofar)) $totalsentsofar=0;
	setcookie('EMAILBATCHNUM', getpost('batchesof'), time()+86400000, '/', '', @$_SERVER['HTTPS']=='on');
	if(! is_numeric($batchesof) || $batchesof=='') $batchesof=0; else $batchesof=(int)$batchesof;
	if($htmlemails==TRUE) $emlNl='<br />'; else $emlNl="\n";
	$theemail=getpost('theemail');
	$fromemail=getpost('fromemail');
	$unsubscribe=(getpost('unsubscribe')=='ON');
	$unsublink='';
	print '</div>'; // to match the div that encloses this include file.
	$index=0;
	if(@$customheaders==''){
		$customheaders="MIME-Version: 1.0\n";
		$customheaders.="From: %from% <%from%>\n";
		if(@$htmlemails==TRUE)
			$customheaders.='Content-type: text/html; charset='.$emailencoding."\n";
		else
			$customheaders.='Content-type: text/plain; charset='.$emailencoding."\n";
	}else{
		if($htmlemails==TRUE)
			$customheaders=str_replace('text/plain', 'text/html', $customheaders);
		else
			$customheaders=str_replace('text/html', 'text/plain', $customheaders);
	}
	$rowcounter=0;
	$sSQL='SELECT email,mlName FROM mailinglist WHERE 1=1 ';
	if(getpost('sendto')=='0'){
		$sSQL="SELECT adminEmail AS email,'Admin' AS mlName FROM admin";
		$batchesof=0;
		$takebreather=FALSE;
	}elseif(getpost('sendto')=='1'){
		$sSQL.="AND selected<>0 ";
	}elseif(getpost('sendto')=='2'){
		// Nothing - entire DB
	}elseif(getpost('sendto')=='3'){
		$sSQL='SELECT affilEmail AS email,affilName AS mlName FROM affiliates WHERE 1=1 ';
		$unsubscribe=FALSE;
	}
	if(($batchesof!=0 || $takebreather) && getpost('sendto')!='3')
		$sSQL.='AND emailsent=0 ';
	if(getpost('sendto')!='0'&&getpost('sendto')!='3'){
		if(! @$noconfirmationemail==TRUE) $sSQL.='AND isconfirmed<>0 ';
		$sSQL.=' ORDER BY email';
	}
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		if($unsubscribe){
			$unsublink=$emlNl . $emlNl . $yyToUnsu . $emlNl;
			$thelink=$storeurl . 'cart.php?unsubscribe=' . $rs['email'];
			if(@$htmlemails==TRUE) $thelink='<a class="unsubscribe" href="' . $thelink . '">' . $thelink . '</a>';
			$unsublink.=$thelink;
		}
		if(! @$debugmode) dosendemail($rs['email'], $fromemail, '', getpost('emailsubject'), replaceemailtxt($theemail,'%name%',$rs['mlName'],$replaceone) . $unsublink);
		if($sendemailerrnum!=0)
			print '<script type="text/javascript">document.getElementById(\'emerrordiv\').innerHTML+=\'Could not send: ' . jsspecials($rs['email']) . ' : ' . $sendemailerrdesc . "<br />';</script>\r\n";
		else
			ect_query("UPDATE mailinglist SET emailsent=1 WHERE email='".escape_string($rs['email'])."'") or ect_error();
		if($batchesof!=0){
			if($totalsentsofar+$index>=$batchesof) break;
		}
		if($index % 50==0 || $index==1 || $index==10){
			print '<script type="text/javascript">document.getElementById(\'sendspan\').innerHTML=' . ($totalsentsofar+$index) . ";</script>\r\n";
			flush();
		}
		if($index==50 && $takebreather){
			print '<script type="text/javascript">breatherfunction();</script>' . "\r\n";
			$takingbreather=TRUE;
			break;
		}
		$index++;
	}
	$hassentall=($rs==FALSE);
	if($takebreather || $batchesof!=0){ ?>
<form method="post" id="breatherform" action="adminmailinglist.php">
<?php	foreach($_POST as $key=>$val){
			if($key!='totalsentsofar') print whv($key,$val);
		}
		if(is_numeric(getpost('totalsentsofar'))) $totalsentsofar=(int)getpost('totalsentsofar'); else $totalsentsofar=0;
		writehiddenidvar('totalsentsofar',$totalsentsofar+$index); ?>
</form>
<?php
	}
	if($batchesof!=0 && ! $hassentall & ! $takingbreather){
		print '<script language="javascript" type="text/javascript">nextbatchfunction();</script>' . "\r\n";
	}
	ect_free_result($result);
	print '<script type="text/javascript">document.getElementById(\'sendspan\').innerHTML=\'' . ($totalsentsofar+$index) . " - All Done!';</script>\r\n";
	print '</body></html>';
	flush();
	exit;
}elseif(getpost('posted')=='1' && getpost('act')=='sendem'){ ?>
<script type="text/javascript">
/* <![CDATA[ */
function formvalidator(theForm){
<?php	if(@$htmleditor=='ckeditor'){ ?>
	if(wasusingfck){
		var inst=theemailfck;
		var sValue=inst.getData();
		if(sValue=='<br />') sValue='';
		document.getElementById("theemail").value=sValue;
	}
<?php	}elseif(@$htmleditor=='fckeditor'){ ?>
	if(wasusingfck){
		var inst=FCKeditorAPI.GetInstance("theemailfck");
		var sValue=inst.GetHTML();
		if(sValue=='<br />') sValue='';
		document.getElementById("theemail").value=sValue;
	}
<?php	} ?>
if (theForm.fromemail.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyFrmEm)?>\".");
theForm.fromemail.focus();
return(false);
}
if (theForm.emailsubject.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yySubjc)?>\".");
theForm.emailsubject.focus();
return(false);
}
if (theForm.theemail.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyMessag)?>\".");
if(!wasusingfck) theForm.theemail.focus();
return(false);
}
if (theForm.sendto.selectedIndex!=0){
	if(!confirm("<?php print jscheck($yyCanSpm)?>")){
		return(false);
	}
}
<?php	if(@$htmleditor=='fckeditor'||@$htmleditor=='ckeditor'){ ?>
	if(wasusingfck){
		document.getElementById("fckrow").style.display='none';
		document.getElementById("textarearow").style.display='';
	}
<?php	} ?>
return(true);
}
var wasusingfck=false;
function changeemailformat(obj){
<?php	if(@$htmleditor=='ckeditor'){ ?>
	var inst=CKEDITOR.instances.theemailfck;
	if(obj.selectedIndex==2){
		if(!wasusingfck){
			inst.setData(document.getElementById("theemail").value);
			document.getElementById("fckrow").style.display='';
			document.getElementById("textarearow").style.display='none';
		}
		wasusingfck=true;
	}else{
		if(wasusingfck){
			var sValue=inst.getData();
			if(sValue=='<br />') sValue='';
			document.getElementById("theemail").value=sValue;
			document.getElementById("fckrow").style.display='none';
			document.getElementById("textarearow").style.display='';
		}
		wasusingfck=false;
	}
<?php	}elseif(@$htmleditor=='fckeditor'){ ?>
	var inst=FCKeditorAPI.GetInstance("theemailfck");
	if(obj.selectedIndex==2){
		if(!wasusingfck){
			inst.SetHTML(document.getElementById("theemail").value);
			document.getElementById("fckrow").style.display='';
			document.getElementById("textarearow").style.display='none';
		}
		wasusingfck=true;
	}else{
		if(wasusingfck){
			var sValue=inst.GetHTML();
			if(sValue=='<br />') sValue='';
			document.getElementById("theemail").value=sValue;
			document.getElementById("fckrow").style.display='none';
			document.getElementById("textarearow").style.display='';
		}
		wasusingfck=false;
	}
<?php	} ?>
}
/* ]]> */
</script>
<?php	if(@$htmleditor=='ckeditor'){ ?>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<?php	}elseif(@$htmleditor=='fckeditor'){ ?>
<script type="text/javascript" src="fckeditor.js"></script>
<script type="text/javascript">
/* <![CDATA[ */
function FCKeditor_OnComplete(editorInstance){
	editorInstance.Events.AttachEvent('OnBlur', FCKeditor_OnBlur);
	editorInstance.Events.AttachEvent('OnFocus', FCKeditor_OnFocus);
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnBlur(editorInstance){
	editorInstance.ToolbarSet.Collapse();
}
function FCKeditor_OnFocus(editorInstance){
	editorInstance.ToolbarSet.Expand();
}
var sBasePath=document.location.pathname.substring(0,document.location.pathname.lastIndexOf('adminmailinglist.php'));
/* ]]> */
</script>
<?php	} ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminmailinglist.php" onsubmit="return formvalidator(this)">
<?php		$batchsent=0; $numselected=0;
			$sSQL='SELECT COUNT(*) AS batchsent FROM mailinglist WHERE emailsent<>0';
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if(! is_null($rs['batchsent'])) $batchsent=$rs['batchsent'];
			}
			ect_free_result($result);
			$sSQL='SELECT COUNT(*) AS numselected FROM mailinglist WHERE selected<>0';
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if(! is_null($rs['numselected'])) $numselected=$rs['numselected'];
			}
			ect_free_result($result);
			writehiddenvar("posted", "1");
			writehiddenvar("act", "dosendem");
			hiddenparams(); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr> 
                <td colspan="2" align="center" height="34"><strong><?php print $yyMaLiMa.' - '.$yySeEma?></strong></td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yySenTo?>:</td>
				<td align="left"><select name="sendto" size="1">
							<option value="0"><?php print $yyAdmEm?></option>
<?php	if($numselected>0) print '<option value="1">'.$yySelEm.' (' . $numselected . ')</option>'; ?>
							<option value="2"><?php print $yyEntML?></option>
							<option value="3"><?php print $yyEntAL?></option>
						</select>
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yyEmlFm?>:</td>
				<td align="left"><select name="emformat" size="1" onchange="changeemailformat(this)">
							<option value="0"><?php print $yyText?></option>
							<option value="1">HTML</option>
<?php			if(@$htmleditor=='fckeditor'){ ?>
							<option value="2">HTML Using FCK Editor</option>
<?php			}elseif(@$htmleditor=='ckeditor'){ ?>
							<option value="2">HTML Using CK Editor</option>
<?php			} ?>
					</select>
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34">Send in batches of:</td>
				<td align="left"><select name="batchesof" size="1">
							<option value="0">Unlimited</option>
							<option value="2"<?php if(@$_COOKIE['EMAILBATCHNUM']=='2') print ' selected="selected"'?>>2</option>
							<option value="50"<?php if(@$_COOKIE['EMAILBATCHNUM']=='50') print ' selected="selected"'?>>50</option>
							<option value="100"<?php if(@$_COOKIE['EMAILBATCHNUM']=='100') print ' selected="selected"'?>>100</option>
							<option value="150"<?php if(@$_COOKIE['EMAILBATCHNUM']=='150') print ' selected="selected"'?>>150</option>
							<option value="200"<?php if(@$_COOKIE['EMAILBATCHNUM']=='200') print ' selected="selected"'?>>200</option>
							<option value="300"<?php if(@$_COOKIE['EMAILBATCHNUM']=='300') print ' selected="selected"'?>>300</option>
							<option value="400"<?php if(@$_COOKIE['EMAILBATCHNUM']=='400') print ' selected="selected"'?>>400</option>
							<option value="500"<?php if(@$_COOKIE['EMAILBATCHNUM']=='500') print ' selected="selected"'?>>500</option>
							<option value="750"<?php if(@$_COOKIE['EMAILBATCHNUM']=='750') print ' selected="selected"'?>>750</option>
							<option value="1000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='1000') print ' selected="selected"'?>>1000</option>
							<option value="1500"<?php if(@$_COOKIE['EMAILBATCHNUM']=='1500') print ' selected="selected"'?>>1500</option>
							<option value="2000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='2000') print ' selected="selected"'?>>2000</option>
							<option value="3000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='3000') print ' selected="selected"'?>>3000</option>
							<option value="4000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='4000') print ' selected="selected"'?>>4000</option>
							<option value="5000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='5000') print ' selected="selected"'?>>5000</option>
							<option value="10000"<?php if(@$_COOKIE['EMAILBATCHNUM']=='10000') print ' selected="selected"'?>>10000</option>
					</select>
<?php		if($batchsent!=0) print ' (' . $batchsent . ' Sent)' ?>
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34">Take Breather every 50 emails:</td>
				<td align="left"><input type="checkbox" name="takebreather" value="ON" />
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yyFrmEm?>:</td>
				<td align="left"><input type="text" name="fromemail" size="40" value="<?php print $emailAddr?>" />
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yySubjc?>:</td>
				<td align="left"><input type="text" name="emailsubject" size="40" />
				</td>
			  </tr>
			  <tr>
				<td align="right" height="34"><?php print $yyUnsubL?>:</td>
				<td align="left"><input type="checkbox" name="unsubscribe" value="ON" checked="checked" />
				</td>
			  </tr>
<?php			if(@$htmleditor=="fckeditor"||@$htmleditor=="ckeditor"){ ?>
			  <tr id="fckrow" style="display:none">
				<td align="right" height="34">&nbsp;</td>
				<td align="left"><textarea name="theemailfck" id="theemailfck" cols="70" rows="35"></textarea></td>
			  </tr>
<?php			} ?>
			  <tr id="textarearow">
				<td align="right" height="34">&nbsp;</td>
				<td align="left"><textarea name="theemail" id="theemail" cols="70" rows="35"></textarea></td>
			  </tr>
			  <tr>
                <td colspan="2" align="center" height="34"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</td>
			  </tr>
			  <tr>
                <td colspan="2" align="center" height="34"><br />
                          <a href="adminmailinglist.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
      </table>
<?php
	if(@$htmleditor=='fckeditor'||@$htmleditor=="ckeditor"){
		if(@$pathtossl!='' && (@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443')){
			if(substr($pathtossl,-1) != "/") $storeurl=$pathtossl . "/"; else $storeurl=$pathtossl;
		}
		$pathtovsadmin=dirname(@$_SERVER['PHP_SELF']);
		print '<script type="text/javascript">';
		if(@$htmleditor=='ckeditor'){
			print "var theemailfck=CKEDITOR.replace('theemailfck',{width: 660,height: 800,toolbarStartupExpanded : false, toolbar : 'Basic', filebrowserBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserImageBrowseUrl : 'ckeditor/filemanager/browser/default/browser.html?Type=Image&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserFlashBrowseUrl :'ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=".$pathtovsadmin."/ckeditor/filemanager/connectors/php/connector.php',filebrowserUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=File',filebrowserImageUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Image',filebrowserFlashUploadUrl:'".$pathtovsadmin."/ckeditor/filemanager/connectors/php/upload.php?Type=Flash'});\r\n";
			print "theemailfck.on('instanceReady',function(event){var myToolbar='Basic';event.editor.on( 'beforeMaximize', function(){if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_ON && myToolbar != 'Basic'){theemailfck.setToolbar('Basic');myToolbar='Basic';theemailfck.execCommand('toolbarCollapse');}else if(event.editor.getCommand('maximize').state==CKEDITOR.TRISTATE_OFF && myToolbar != 'Full'){theemailfck.setToolbar('Full');myToolbar='Full';theemailfck.execCommand('toolbarCollapse');}});event.editor.on('contentDom', function(e){event.editor.document.on('blur', function(){if(!theemailfck.isToolbarCollapsed){theemailfck.execCommand('toolbarCollapse');theemailfck.isToolbarCollapsed=true;}});event.editor.document.on('focus',function(){if(theemailfck.isToolbarCollapsed){theemailfck.execCommand('toolbarCollapse');theemailfck.isToolbarCollapsed=false;}});});theemailfck.fire('contentDom');theemailfck.isToolbarCollapsed=true;});\r\n";
		}else
			print "var oFCKeditor=new FCKeditor('theemailfck');oFCKeditor.Height=400;oFCKeditor.BasePath=sBasePath;oFCKeditor.Config.BaseHref='".$storeurl."';oFCKeditor.ToolbarSet='Basic';oFCKeditor.ReplaceTextarea();\r\n";
		print '</script>';
	}
}elseif(getpost('posted')=='1' && (getpost('act')=='modify' || getpost('act')=='addnew')){
?>
<script type="text/javascript">
<!--
function formvalidator(theForm){
if (theForm.email.value==""){
alert("<?php print jscheck($yyPlsEntr . ' "' . $yyEmail)?>\".");
theForm.email.focus();
return(false);
}
return(true);
}
//-->
</script>
<?php
		if(getpost('act')=='modify'){
			$email=getpost('id');
			$sSQL="SELECT isconfirmed,mlConfirmDate,mlIPAddress,mlName FROM mailinglist WHERE email='" . escape_string($email) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$dateadded=$rs['mlConfirmDate'];
				$ipaddress=$rs['mlIPAddress'];
				$mlname=$rs['mlName'];
			}
			ect_free_result($result);
		}else{
			$email='';
			$mlname='';
		}
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
		  <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminmailinglist.php" onsubmit="return formvalidator(this)">
<?php		writehiddenvar('posted', '1');
			if(getpost('act')=='modify') writehiddenvar('act', 'domodify'); else writehiddenvar('act', 'doaddnew');
			hiddenparams(); ?>
            <table width="100%" border="0" cellspacing="2" cellpadding="2">
			  <tr>
                <td width="100%" colspan="2" align="center" height="34"><strong><?php print $yyMaLiMa;
			if(getpost('act')=='modify') print ' - ' . htmlspecials(getpost('id'))?></strong></td>
			  </tr>
			  <tr>
				<td align="right" height="34"><strong><?php print $yyName?>:</strong></td>
				<td align="left"><input type="text" name="mlname" size="34" value="<?php print htmlspecials($mlname)?>" /></td>
			  </tr>
			  <tr>
				<td align="right" height="34"><strong><?php print $yyEmail?>:</strong></td>
				<td align="left"><input type="text" name="email" size="34" value="<?php print htmlspecials($email)?>" /></td>
			  </tr>
<?php	if(getpost('act')=='modify'){ ?>
			  <tr>
				<td align="right" height="34"><strong><?php print $yyDateAd?>:</strong></td>
				<td align="left"><?php print $dateadded?></td>
			  </tr>
			  <tr>
				<td align="right" height="34"><strong><?php print $yyIPAdd?>:</strong></td>
				<td align="left"><?php print $ipaddress?></td>
			  </tr>
<?php	} ?>
			  <tr>
                <td width="100%" colspan="2" align="center" height="34"><br /><input type="submit" value="<?php print $yySubmit?>" />&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</td>
			  </tr>
			  <tr>
                <td width="100%" colspan="2" align="center" height="34"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
      </table>
<?php
}elseif(getpost('posted')=='1' && getpost('act')!='confirm' && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminmailinglist.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br /><br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(getpost('posted')=='1' && getpost('act')!='confirm'){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}else{
	$sSQL='SELECT count(*) AS thecount FROM mailinglist';
	if(@$noconfirmationemail!=TRUE) $sSQL.=' WHERE isconfirmed<>0';
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $numemails=$rs['thecount']; else $numemails=0;
	ect_free_result($result);
	$sSQL='SELECT count(*) AS thecount FROM mailinglist WHERE emailsent<>0';
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $numsentemails=$rs['thecount']; else $numsentemails=0;
	ect_free_result($result);
	$ordstate=@$_REQUEST['ordstate'];
	if(! is_array($ordstate)){
		if($ordstate!='') $ordstate=explode(',',$ordstate);
	}
	$ordcountry=@$_REQUEST['ordcountry'];
	if(! is_array($ordcountry)){
		if($ordcountry!='') $ordcountry=explode(',',$ordcountry);
	}
	$smanufacturer=@$_REQUEST['smanufacturer'];
	if(! is_array($smanufacturer)){
		if($smanufacturer!='') $smanufacturer=explode(',',$smanufacturer);
	}
	$thecat=@$_REQUEST['scat'];
	if(! is_array($thecat)){
		if($thecat!='') $thecat=explode(',',$thecat);
	}
	$stext=trim(@$_REQUEST['stext']);
	$stype=trim(@$_REQUEST['stype']);
	$stsearch=trim(@$_REQUEST['stsearch']);
	$swholesale=trim(@$_REQUEST['swholesale']);
	$sortorder=trim(@$_REQUEST['sort']);
?>
<script type="text/javascript" src="popcalendar.js"></script>
<script type="text/javascript">
<!--
try{languagetext('<?php print @$adminlang?>');}catch(err){}
function mrec(id) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.id.value=id;
	document.mainform.act.value="modify";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
function crec(id) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.id.value=id;
	document.mainform.act.value="confirm";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
function newrec(id) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.id.value=id;
	document.mainform.act.value="addnew";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
function sendem(id) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value="sendem";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
function drec(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.action="adminmailinglist.php";
	document.mainform.id.value=id;
	document.mainform.act.value="delete";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value="search";
	document.mainform.listem.value="";
	document.mainform.posted.value="";
	document.mainform.submit();
}
function listem(thelet){
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value="search";
	document.mainform.listem.value=thelet;
	document.mainform.posted.value="";
	document.mainform.submit();
}
function removeuncon(){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value="purgeunconfirmed";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
}
function clearsent(){
if(confirm("<?php print jscheck($yySureCa)?>")) {
	document.mainform.action="adminmailinglist.php";
	document.mainform.act.value="clearsent";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
}
function checkact(tmen){
	tact=tmen[tmen.selectedIndex].value;
	if(tact=='CSL') clearsent();
	if(tact=='ROU') removeuncon();
	if(tact=='DUS'){
		document.mainform.action="dumporders.php";
		document.mainform.act.value="dumpemails";
		document.mainform.submit();
	}
	if(tact=='DUE'){
		document.mainform.action="dumporders.php?entirelist=1";
		document.mainform.act.value="dumpemails";
		document.mainform.submit();
	}
	tmen.selectedIndex=0;
}
function changesortorder(men){
	var thesort=men[men.selectedIndex].value;
	document.mainform.action="adminmailinglist.php<?php if(getpost('act')=='search' || getget('pg')!='') print '?pg=1&'; else print '?'?>sort="+thesort;
	document.mainform.act.value="search";
	document.mainform.listem.value="";
	document.mainform.posted.value="";
	document.mainform.submit();
}
// -->
</script>
		  <form name="mainform" method="post" action="adminmailinglist.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="listem" value="<?php print @$_REQUEST['listem']?>" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print (getpost('act')=='search' ? '1' : getget('pg'))?>" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr>
				<td class="cobhl" colspan="4" align="center"><strong><?php
					print $numemails . ' ' . 'Emails - ';
					print '<a href="javascript:listem(\'#\')">#</a> ';
					for($index=0; $index < 26; $index++){
						print '<a href="javascript:listem(\'' . chr(65+$index) . '\')">' . chr(65+$index) . '</a> ';
					}
				?></strong></td>
			  </tr>
			  <tr> 
				<td class="cobhl" width="25%" align="right"><select name="stsearch" size="1">
					<option value="srchemail"><?php print $yySrchFr.': '.$yyEmail?></option>
					<option value="srchprodid" <?php if($stsearch=="srchprodid") print 'selected="selected"'?>><?php print $yySrchFr.': '.$yyPrId?></option>
					<option value="srchprodname" <?php if($stsearch=="srchprodname") print 'selected="selected"'?>><?php print $yySrchFr.': '.$yyPrName?></option>
					</select></td>
				<td class="cobll"><input type="text" name="stext" size="20" value="<?php print $stext?>" />
					<select name="stype" size="1">
					<option value=""><?php print $yySrchAl?></option>
					<option value="any" <?php if($stype=="any") print 'selected="selected"'?>><?php print $yySrchAn?></option>
					<option value="exact" <?php if($stype=="exact") print 'selected="selected"'?>><?php print $yySrchEx?></option>
					</select>
				</td>
				<td class="cobhl" align="right"><select name="swholesale" size="1" style="float:left">
					<option value=""><?php print $yyAll?></option>
					<option value="wholesale" <?php if($swholesale=="wholesale") print 'selected="selected"'?>><?php print $yyWholes?></option>
					<option value="nonwholesale" <?php if($swholesale=="nonwholesale") print 'selected="selected"'?>><?php print $yyNoWhol?></option>
					</select><?php print $yyDatRan?>:</td>
				<td class="cobll"><input type="text" name="mindate" size="10" value="<?php print @$_REQUEST['mindate']?>" />&nbsp;<input type="button" onclick="popUpCalendar(this, document.forms.mainform.mindate, '<?php print $themask?>', -205)" value="DP" />&nbsp;<?php print $yyTo?>:&nbsp;<input type="text" name="maxdate" size="10" value="<?php print @$_REQUEST['maxdate']?>" />&nbsp;<input type="button" onclick="popUpCalendar(this, document.forms.mainform.maxdate, '<?php print $themask?>', -205)" value="DP" /></td>
			  </tr>
			  <tr>
				<td class="cobhl" width="25%" align="center"><strong><?php print $yySection?></strong>&nbsp;&nbsp;<input type="checkbox" name="notsection" value="ON" <?php if(getpost('notsection')=="ON") print 'checked="checked"'?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" width="25%" align="center"><strong><?php print $yyManuf?></strong>&nbsp;&nbsp;<input type="checkbox" name="notmanufacturer" value="ON" <?php if(getpost('notmanufacturer')=="ON") print 'checked="checked"'?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" width="25%" align="center"><strong><?php print $yyState?></strong>&nbsp;&nbsp;<input type="checkbox" name="notstate" value="ON" <?php if(getpost('notstate')=="ON") print 'checked="checked"'?>/><strong>...<?php print $yyNot?></strong></td>
				<td class="cobhl" width="25%" align="center"><strong><?php print $yyCountry?></strong>&nbsp;&nbsp;<input type="checkbox" name="notcountry" value="ON" <?php if(getpost('notcountry')=="ON") print 'checked="checked"'?>/><strong>...<?php print $yyNot?></strong></td>
			  </tr>
			  <tr>
				<td class="cobll" align="center"><select name="scat[]" size="5" multiple="multiple"><?php
						$sSQL="SELECT sectionID,sectionWorkingName,topSection,rootSection FROM sections " . (@$adminonlysubcats==TRUE ? "WHERE rootSection=1 ORDER BY sectionWorkingName" : "ORDER BY sectionOrder");
						$allcats=ect_query($sSQL) or ect_error();
						$lasttsid=-1;
						$numcats=0;
						while($row=ect_fetch_assoc($allcats))
							$allcatsa[$numcats++]=$row;
						ect_free_result($allcats);
						if($numcats > 0){
							if(@$adminonlysubcats==TRUE){
								for($index=0;$index<$numcats;$index++){
									print '<option value="' . $allcatsa[$index]['sectionID'] . '"';
									if(is_array($thecat)){
										foreach($thecat as $catid){
											if($allcatsa[$index]['sectionID']==$catid) print ' selected="selected"';
										}
									}
									print '>' . $allcatsa[$index]['sectionWorkingName'] . "</option>\n";
								}
							}else
								writemenulevel(0,1);
						} ?>
					  </select></td>
				<td class="cobll" align="center"><select name="smanufacturer[]" size="5" multiple="multiple"><?php
						$sSQL='SELECT scID,scName FROM searchcriteria WHERE scGroup=0 ORDER BY scName';
						$result=ect_query($sSQL) or ect_error();
						while($rs=ect_fetch_assoc($result)){
							print '<option value="' . htmlspecials($rs['scID']) . '"';
							if(is_array($smanufacturer)){
								foreach($smanufacturer as $objValue){
									if($objValue==$rs['scID']) print ' selected="selected"';
								}
							}
							print '>' . $rs['scName'] . "</option>\n";
						}
						ect_free_result($result); ?></select></td>
				<td class="cobll" align="center"><select name="ordstate[]" size="5" multiple="multiple"><?php
						$sSQL="SELECT stateID,stateName,stateAbbrev FROM states WHERE stateEnabled=1 AND stateCountryID=" . $origCountryID . " ORDER BY stateName";
						$result=ect_query($sSQL) or ect_error();
						while($rs=ect_fetch_assoc($result)){
							print '<option value="' . htmlspecials(@$usestateabbrev==TRUE?$rs['stateAbbrev']:$rs['stateName']) . '"';
							if(is_array($ordstate)){
								foreach($ordstate as $objValue){
									if($objValue==(@$usestateabbrev==TRUE?$rs['stateAbbrev']:$rs['stateName'])) print ' selected="selected"';
								}
							}
							print '>' . $rs['stateName'] . "</option>\n";
						}
						ect_free_result($result); ?></select></td>
				<td class="cobll" align="center"><select name="ordcountry[]" size="5" multiple="multiple"><?php
						$sSQL="SELECT countryID,countryName FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC, countryName";
						$result=ect_query($sSQL) or ect_error();
						while($rs=ect_fetch_assoc($result)){
							print '<option value="' . htmlspecials($rs["countryName"]) . '"';
							if(is_array($ordcountry)){
								foreach($ordcountry as $objValue){
									if($objValue==$rs['countryName']) print ' selected="selected"';
								}
							}
							print '>' . $rs['countryName'] . "</option>\n";
						}
						ect_free_result($result); ?></select></td>
			  </tr>
			  <tr>
				<td class="cobhl" align="center"><select onchange="checkact(this)">
						<option value=""><?php print $yyAct?>...</option>
						<option value="CSL">Clear &quot;Sent&quot; List<?php if($numsentemails!=0) print ' ('.$numsentemails.')'?></option>
<?php
	$mlcount=0;
	$sSQL="SELECT COUNT(*) AS mlcount FROM mailinglist WHERE isConfirmed=0 AND mlConfirmDate<'".date('Y-m-d', time()-($mailinglistpurgedays*60*60*24))."'";
	$result=ect_query($sSQL) or print_sql_error();
	if($rs=ect_fetch_assoc($result)){
		if(! is_null($rs['mlcount'])) $mlcount=$rs['mlcount'];
	}
	ect_free_result($result);
	if($mlcount > 0) print '<option value="ROU">Remove Old Unconfirmed ('.$mlcount.')</option>' ?>
						<option value="DUS">Dump <?php print $yySelEm?></option>
						<option value="DUE">Dump <?php print $yyEntML?></option>
						</select></td>
				<td class="cobll" colspan="3" align="center">
						<select name="sort" size="1" onchange="changesortorder(this)">
						<option value="naa"<?php if($sortorder=='naa') print ' selected="selected"'?>>Sort - Name ASC</option>
						<option value="nad"<?php if($sortorder=='nad') print ' selected="selected"'?>>Sort - Name DESC</option>
						<option value=""<?php if($sortorder=='') print ' selected="selected"'?>>Sort - Email ASC</option>
						<option value="emd"<?php if($sortorder=='emd') print ' selected="selected"'?>>Sort - Email DESC</option>
						<option value="daa"<?php if($sortorder=='daa') print ' selected="selected"'?>>Sort - Date ASC</option>
						<option value="dad"<?php if($sortorder=='dad') print ' selected="selected"'?>>Sort - Date DESC</option>
						<option value="coa"<?php if($sortorder=='coa') print ' selected="selected"'?>>Sort - Confirmed ASC</option>
						<option value="cod"<?php if($sortorder=='cod') print ' selected="selected"'?>>Sort - Confirmed DESC</option>
						<option value="nsf"<?php if($sortorder=='nsf') print ' selected="selected"'?>>No Sort (Fastest)</option>
						</select>
						<input type="button" value="<?php print $yyListRe?>" onclick="startsearch();" /> &nbsp;
						<input type="button" value="Add Email" onclick="newrec();" />
						<input type="button" value="Send Emails To List" onclick="sendem();" />
				</td>
			  </tr>
			</table>
<br />
            <table width="100%" class="stackable admin-table-a sta-white">
<?php
	if(getpost('act')=='search' || getget('pg')!='' || getpost('act')=='confirm'){
		function displayprodrow($xrs){
			global $yyModify, $yyDelete, $bgcolor, $noconfirmationemail, $yyConfrm;
?><tr class="<?php print $bgcolor?>"><td><?php print htmlspecials($xrs['mlName']) ?>&nbsp;</td><td><?php print htmlspecials($xrs['email']) ?></td><td><?php print htmlspecials($xrs['mlConfirmDate']) ?></td>
<td class="minicell"><?php if(@$noconfirmationemail!=TRUE && $xrs['isconfirmed']==0) print '<input type="button" value="'.$yyConfrm.'" onclick="crec(\''.str_replace(array("'",'"'),array("\'",'&quot;'),$xrs['email']).'\')" />'; else print '&nbsp;'?></td>
<td class="minicell"><input type="button" value="<?php print $yyModify?>" onclick="mrec('<?php print jsspecials($xrs['email'])?>')" /></td>
<td class="minicell"><input type="button" value="<?php print $yyDelete?>" onclick="drec('<?php print jsspecials($xrs['email'])?>')" /></td></tr>
<?php	}
		function displayheaderrow(){
			global $yyName,$yyEmail,$yyModify,$yyDelete,$noconfirmationemail,$yyConfrm; ?>
			<tr>
				<th class="maincell"><?php print $yyName?></th>
				<th class="maincell"><?php print $yyEmail?></th>
				<th class="maincell">Date</th>
				<th class="minicell"><?php if(@$noconfirmationemail!=TRUE) print $yyConfrm; else print '&nbsp;'?></th>
				<th class="minicell"><?php print $yyModify?></th>
				<th class="minicell"><?php print $yyDelete?></th>
			</tr>
<?php	}
		$rowcounter=0;
		$sSQL='SELECT DISTINCT email,mlName,isconfirmed,mlConfirmDate FROM mailinglist ';
		if(($stext!='' && ($stsearch=='srchprodid' || $stsearch=='srchprodname')) || $thecat!='' || $smanufacturer!='' || $ordstate!='' || $ordcountry!='') $sSQL.='INNER JOIN orders ON mailinglist.email=orders.ordEmail INNER JOIN cart ON orders.ordID=cart.cartOrderID INNER JOIN products ON cart.cartProdID=products.pId ';
		$whereand='WHERE';
		if(trim(@$_REQUEST['listem'])!=''){
			if(@$_REQUEST['listem']=='#')
				$sSQL.="WHERE (email < 'A') ";
			else
				$sSQL.="WHERE (email LIKE '" . escape_string(@$_REQUEST['listem']) . "%') ";
			$whereand='AND';
		}elseif($stext!=''){
			$sText=escape_string($stext);
			$aText=explode(' ', $sText);
			$arrelms=count($aText);
			if($stype=="exact"){
				$sSQL.=$whereand . " (email LIKE '%" . $sText . "%') ";
				$whereand='AND';
			}else{
				if($stype=="any") $sJoin="OR "; else $sJoin="AND ";
				$sSQL.=$whereand . ' (';
				$whereand='AND';
				foreach($aText as $theopt){
					if(is_array($theopt))$theopt=$theopt[0];
					if($stsearch=='srchemail'||$stsearch=='') $sSQL.="email ";
					if($stsearch=='srchprodid') $sSQL.="cartProdId ";
					if($stsearch=='srchprodname') $sSQL.="cartProdName ";
					$sSQL.=" LIKE '%" . $theopt . "%' ";
					if(++$rowcounter < $arrelms) $sSQL.=$sJoin;
				}
				$sSQL.=') ';
			}
		}
		if(is_array($thecat)){
			$sectionids=getsectionids(implode(',',$thecat), TRUE);
			if($sectionids!=''){
				$sSQL.= $whereand . ' ' . (getpost('notsection')=='ON'?'NOT ':'') . "(products.pSection IN (" . $sectionids . ")) ";
				$whereand='AND';
			}
		}
		if(is_array($smanufacturer)){
			$sSQL.= $whereand . ' ' . (getpost('notmanufacturer')=='ON'?'NOT ':'') . "(products.pManufacturer IN (" . implode(',',$smanufacturer) . ")) ";
			$whereand='AND';
		}
		if(is_array($ordstate)){
			$sSQL.= $whereand . ' ' . (getpost('notstate')=='ON'?'NOT ':'') . "(ordState IN ('" . implode("','", $ordstate) . "')) ";
			$whereand='AND';
		}
		if(is_array($ordcountry)){
			$sSQL.= $whereand . ' ' . (getpost('notcountry')=='ON'?'NOT ':'') . "(ordCountry IN ('" . implode("','",$ordcountry) . "')) ";
			$whereand='AND';
		}
		$mindate=trim(@$_REQUEST['mindate']);
		$maxdate=trim(@$_REQUEST['maxdate']);
		if($mindate!='' || $maxdate!=''){
			if($mindate!='') $themindate=parsedate($mindate); else $themindate='';
			if($maxdate!='') $themaxdate=parsedate($maxdate); else $themaxdate='';
			if($themindate!='' && $themaxdate!=''){
				$sSQL.=$whereand . " mlConfirmDate BETWEEN '" . date('Y-m-d', $themindate) . "' AND '" . date('Y-m-d', $themaxdate) . "'";
				$whereand=" AND ";
			}elseif($themindate!=''){
				$sSQL.=$whereand . " mlConfirmDate >= '" . date('Y-m-d', $themindate) . "'";
				$whereand=" AND ";
			}elseif($themaxdate!=''){
				$sSQL.=$whereand . " mlConfirmDate <= '" . date('Y-m-d', $themaxdate) . "'";
				$whereand=" AND ";
			}
		}
		if($whereand=='WHERE'){
			ect_query("UPDATE mailinglist SET selected=1") or ect_error();
		}else{
			ect_query("UPDATE mailinglist SET selected=0") or ect_error();
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				ect_query("UPDATE mailinglist SET selected=1 WHERE email='" . escape_string($rs['email']) . "'") or ect_error();
			}
			ect_free_result($result);
		}
		if($swholesale=='nonwholesale'){
			$sSQL="SELECT DISTINCT email FROM mailinglist LEFT JOIN customerlogin ON mailinglist.email=customerlogin.clEmail WHERE selected<>0 AND (clActions&8)=8";
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				ect_query("UPDATE mailinglist SET selected=0 WHERE email='" . escape_string($rs['email']) . "'") or ect_error();
			}
			ect_free_result($result);
		}elseif($swholesale=='wholesale'){
			$sSQL="SELECT DISTINCT email FROM mailinglist LEFT JOIN customerlogin ON mailinglist.email=customerlogin.clEmail WHERE selected<>0 AND ((clActions&8)<>8 OR clActions IS NULL)";
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				ect_query("UPDATE mailinglist SET selected=0 WHERE email='" . escape_string($rs['email']) . "'") or ect_error();
			}
			ect_free_result($result);
		}
		$thesort=' ORDER BY email';
		if($sortorder=='emd') $thesort=' ORDER BY email DESC';
		if($sortorder=='naa') $thesort=' ORDER BY mlName';
		if($sortorder=='nad') $thesort=' ORDER BY mlName DESC';
		if($sortorder=='daa') $thesort=' ORDER BY mlConfirmDate';
		if($sortorder=='dad') $thesort=' ORDER BY mlConfirmDate DESC';
		if($sortorder=='coa') $thesort=' ORDER BY isconfirmed,email';
		if($sortorder=='cod') $thesort=' ORDER BY isconfirmed DESC,email';
		if($sortorder=='nsf') $thesort='';
		$sSQL='SELECT DISTINCT email,mlName,isconfirmed,mlConfirmDate FROM mailinglist WHERE selected<>0' . $thesort;
		if(! @is_numeric(getget('pg')))
			$CurPage=1;
		else
			$CurPage=(int)getget('pg');
		if(@$adminemailsperpage=='') $adminemailsperpage=200;
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0) $iNumOfPages=ceil(ect_num_rows($result)/$adminemailsperpage); else $iNumOfPages=0;
		ect_free_result($result);
		$sSQL.=' LIMIT ' . ($adminemailsperpage*($CurPage-1)) . ', ' . $adminemailsperpage;
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result) > 0){
			$ordstatearr='';
			if(is_array($ordstate)){
				$ordstatearr=implode(',',$ordstate);
			}
			$ordcountryarr='';
			if(is_array($ordcountry)){
				$ordcountryarr=implode(',',$ordcountry);
			}
			$smanufacturerarr='';
			if(is_array($smanufacturer)){
				$smanufacturerarr=implode(',',$smanufacturer);
			}
			$thecatarr='';
			if(is_array($thecat)){
				$thecatarr=implode(',',$thecat);
			}
			$pblink='<a href="adminmailinglist.php?stext=' . urlencode($stext) . '&stype=' . $stype . '&ordstate=' . urlencode($ordstatearr) . '&ordcountry=' . urlencode($ordcountryarr) . '&smanufacturer=' . urlencode($smanufacturerarr) . '&scat=' . urlencode($thecatarr) . '&stsearch=' . urlencode($stsearch) . '&swholesale=' . urlencode($swholesale) . '&mindate=' . $mindate . '&maxdate=' . $maxdate . '&pg=';
			if($iNumOfPages > 1) print '<tr><td colspan="6" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			displayheaderrow();
			while($rs=ect_fetch_assoc($result)){
				if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark';
				displayprodrow($rs);
			}
			if($iNumOfPages > 1) print '<tr><td colspan="6" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
		}else{
			print '<tr><td width="100%" colspan="6" align="center"><br />' . $yyItNone . '<br />&nbsp;</td></tr>';
		}
		ect_free_result($result);
	}else{
		$selectedunsent=0;
		$sSQL="SELECT COUNT(*) AS selectedunsent FROM mailinglist WHERE selected<>0 AND emailsent=0";
		if(@$noconfirmationemail!=TRUE) $sSQL.= ' AND isconfirmed<>0';
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)) $selectedunsent=$rs['selectedunsent']; else $selectedunsent=0;
		ect_free_result($result);
		if($selectedunsent!=0){ ?>
			<tr> 
                <td width="100%" colspan="6" align="center"><br />
                          <?php print $selectedunsent?> Unsent from previous search<br />&nbsp;</td>
			  </tr>
<?php	}
	} ?>
			  <tr> 
                <td width="100%" colspan="6" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
<?php
}
?>
