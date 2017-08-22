<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr=='') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
$success = TRUE;
$showaccount = TRUE;
$dorefresh = FALSE;
$alreadygotadmin = getadminsettings();
if(getpost('act')=='quickupdate'){
	foreach(@$_POST as $objItem => $objValue){
		if(substr($objItem, 0, 4)=='pra_'){
			$theid = str_replace('ect_dot_xzq','.',substr($objItem, 4));
			$theval = trim(unstripslashes($objValue));
			$pract = getpost('pract');
			$sSQL = '';
			if($pract=='del'){
				ect_query("DELETE FROM affiliates WHERE affilID='" . escape_string($theid) . "'") or ect_error();
				$sSQL = '';
			}
		}
	}
	$dorefresh=TRUE;
}elseif(getpost('editaction')=='modify'){
	if(getpost('affilid')!=getpost('origaffilid')){
		$sSQL = "SELECT affilID FROM affiliates WHERE affilID='" . escape_string(getpost('affilid'))."'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)>0){ $errmsg=$yyAffDup; $success=FALSE; }
		ect_free_result($result);
	}
	if($success){
		$sSQL = "UPDATE affiliates SET affilID='" . escape_string(getpost('affilid')) . "',";
		if(getpost('affilpw')!='') $sSQL .="affilPW='" . escape_string(dohashpw(getpost('affilpw'))) . "',";
		$sSQL.="affilEmail='" . escape_string(getpost('email')) . "'," .
			"affilName='" . escape_string(getpost('name')) . "'," .
			"affilAddress='" . escape_string(getpost('address')) . "'," .
			"affilCity='" . escape_string(getpost('city')) . "'," .
			"affilState='" . escape_string(getpost('state')) . "'," .
			"affilCountry='" . escape_string(getpost('country')) . "'," .
			"affilZip='" . escape_string(getpost('zip')) . "',";
		if(! is_numeric(getpost('affilcommision')))
			$sSQL.='affilCommision=0,';
		else
			$sSQL.='affilCommision=' . getpost('affilcommision') . ',';
		if(getpost('affildate')!='')
			$sSQL.="affilDate='" . date('Y-m-d', parsedate(getpost('affildate'))) . "',";
		else
			$sSQL.="affilDate='" . date('Y-m-d', time() + ($dateadjust*60*60)) . "',";
		$sSQL.='affilInform=' . (getpost('inform')=='ON' ? '1 ' : '0 ');
		$sSQL.="WHERE affilID='" . escape_string(getpost('affilid')) . "'";
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}
}elseif(getpost('editaction')=='addnew'){
	$sSQL = "SELECT affilID FROM affiliates WHERE affilID='" . escape_string(getpost('affilid')) . "'";
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result)>0){ $errmsg=$yyAffDup; $success=FALSE; }
	ect_free_result($result);
	if($success){
		$sSQL = 'INSERT INTO affiliates (affilID,affilPW,affilEmail,affilName,affilAddress,affilCity,affilState,affilCountry,affilZip,affilCommision,affilDate,affilInform) VALUES (';
		$sSQL.="'" . escape_string(getpost('affilid')) . "'," .
			"'" . escape_string(dohashpw(getpost('affilpw'))) . "'," .
			"'" . escape_string(getpost('email')) . "'," .
			"'" . escape_string(getpost('name')) . "'," .
			"'" . escape_string(getpost('address')) . "'," .
			"'" . escape_string(getpost('city')) . "'," .
			"'" . escape_string(getpost('state')) . "'," .
			"'" . escape_string(getpost('country')) . "'," .
			"'" . escape_string(getpost('zip')) . "',";
		if(! is_numeric(getpost('affilcommision')))
			$sSQL.='0,';
		else
			$sSQL.=getpost('affilcommision') . ',';
		if(getpost('affildate')!='')
			$sSQL.="'" . date('Y-m-d', parsedate(getpost('affildate'))) . "',";
		else
			$sSQL.="'" . date('Y-m-d', time() + ($dateadjust*60*60)) . "',";
		$sSQL.=(getpost('inform')=='ON' ? '1 ' : '0 ') . ')';
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}
}elseif(getpost('editaction')=='delete'){
	$sSQL = "DELETE FROM affiliates WHERE affilID='" . escape_string(getpost('affilid')) . "'";
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}elseif(getpost('editaction')=='editaffil'){
	$sSQL = "UPDATE orders SET ordAffiliate='" . escape_string(getpost('affilid')) . "' WHERE ordID='" . escape_string(getpost('id')) . "'";
	ect_query($sSQL) or ect_error();
}elseif(getpost('editaction')=='removeaffil'){
	$sSQL = "UPDATE orders SET ordAffiliate='' WHERE ordAffiliate='" . escape_string(getpost('affilid')) . "'";
	ect_query($sSQL) or ect_error();
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminaffil.php';
	print '?stext=' . urlencode(getpost('stext')) . '&sd=' . @$_REQUEST['sd'] . '&ed=' . @$_REQUEST['ed'] . '&stype=' . getpost('stype') . '&resorder=' . getpost('resorder') . '&pg=1';
	print '">';
}
if(getpost('act')=='modify' || getpost('act')=='addnew'){
	if(getpost('act')=='modify'){
		$sSQL = "SELECT affilName,affilPW,affilAddress,affilCity,affilState,affilZip,affilCountry,affilEmail,affilInform,affilCommision,affilDate FROM affiliates WHERE affilID='" . escape_string(getpost('id')) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$affilID = getpost('id');
			$affilName = $rs['affilName'];
			$affilPW = '';
			$affilAddress = $rs['affilAddress'];
			$affilCity = $rs['affilCity'];
			$affilState = $rs['affilState'];
			$affilZip = $rs['affilZip'];
			$affilCountry = $rs['affilCountry'];
			$affilEmail = $rs['affilEmail'];
			$affilInform = ((int)$rs['affilInform'])==1;
			$affilCommision = $rs['affilCommision'];
			$affilDate = date($admindatestr, strtotime($rs['affilDate']));
		}
		ect_free_result($result);
	}else{
		$affilID = '';
		$affilName = '';
		$affilPW = '';
		$affilAddress = '';
		$affilCity = '';
		$affilState = '';
		$affilZip = '';
		$affilCountry = '';
		$affilEmail = '';
		$affilInform = 0;
		$affilCommision = 0;
		$affilDate = date($admindatestr, time() + ($dateadjust*60*60));
	}
?>
<script type="text/javascript">
<!--
function checkform(frm){
if(frm.affilid.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyAffId)?>\".");
	frm.affilid.focus();
	return (false);
}
var checkOK = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
var checkStr = frm.affilid.value;
var allValid = true;
for (i = 0;  i < checkStr.length;  i++){
    ch = checkStr.charAt(i);
    for (j = 0;  j < checkOK.length;  j++)
      if (ch==checkOK.charAt(j))
        break;
    if (j==checkOK.length)
    {
      allValid = false;
      break;
    }
}
if (!allValid){
    alert("<?php print jscheck($yyOnlyAl . ' "' . $yyAffId)?>\" field.");
    frm.affilid.focus();
    return (false);
}
<?php	if(getpost('act')!='modify'){ ?>
if(frm.affilpw.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyPass)?>\".");
	frm.affilpw.focus();
	return (false);
}
<?php	} ?>
if(frm.name.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyName)?>\".");
	frm.name.focus();
	return (false);
}
if(frm.email.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyEmail)?>\".");
	frm.email.focus();
	return (false);
}
if(frm.address.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyAddress)?>\".");
	frm.address.focus();
	return (false);
}
if(frm.city.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyCity)?>\".");
	frm.city.focus();
	return (false);
}
if(frm.state.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyState)?>\".");
	frm.state.focus();
	return (false);
}
if(frm.zip.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyZip)?>\".");
	frm.zip.focus();
	return (false);
}
var checkOK = "0123456789.";
var checkStr = frm.affilcommision.value;
var allValid = true;
for (i = 0;  i < checkStr.length;  i++){
    ch = checkStr.charAt(i);
    for (j = 0;  j < checkOK.length;  j++)
      if (ch==checkOK.charAt(j))
        break;
    if (j==checkOK.length)
    {
      allValid = false;
      break;
    }
}
if (!allValid){
    alert("<?php print jscheck($yyOnlyDec . ' "' . $yyCommis)?>\".");
    frm.affilcommision.focus();
    return (false);
}
return (true);
}
//-->
</script>
		  <form method="post" action="adminaffil.php" onsubmit="return checkform(this)">
			<input type="hidden" name="origaffilid" value="<?php print htmlspecials($affilID)?>" />
			<input type="hidden" name="editaction" value="<?php print (getpost('act')=='modify' ? 'modify' : 'addnew')?>" />
			<input type="hidden" name="stext" value="<?php print getpost('stext')?>" />
			<input type="hidden" name="sd" value="<?php print getpost('sd')?>" />
			<input type="hidden" name="ed" value="<?php print getpost('ed')?>" />
			<input type="hidden" name="resorder" value="<?php print getpost('resorder')?>" />
			<input type="hidden" name="posted" value="1" />
			  <table width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="4"><strong><?php print $yyAffAdm?></strong></td>
				</tr>
				<tr>
				  <td width="25%" align="right"><strong><?php print $redasterix.$yyAffId?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="affilid" size="20" value="<?php print htmlspecials($affilID)?>" /></td>
				  <td width="25%" align="right"><strong><?php print (getpost('act')=='modify'?$yyReset.' '.$yyPass:$redasterix.$yyPass)?>:</strong></td>
				  <td width="25%" align="left"><input type="text" name="affilpw" size="20" value="" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyName?>:</strong></td>
				  <td align="left"><input type="text" name="name" size="20" value="<?php print htmlspecials($affilName)?>" /></td>
				  <td align="right"><strong><?php print $redasterix.$yyEmail?>:</strong></td>
				  <td align="left"><input type="text" name="email" size="25" value="<?php print htmlspecials($affilEmail)?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyAddress?>:</strong></td>
				  <td align="left"><input type="text" name="address" size="20" value="<?php print htmlspecials($affilAddress)?>" /></td>
				  <td align="right"><strong><?php print $redasterix.$yyCity?>:</strong></td>
				  <td align="left"><input type="text" name="city" size="20" value="<?php print htmlspecials($affilCity)?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyState?>:</strong></td>
				  <td align="left"><input type="text" name="state" size="20" value="<?php print htmlspecials($affilState)?>" /></td>
				  <td align="right"><strong><?php print $redasterix.$yyCountry?>:</strong></td>
				  <td align="left"><select name="country" size="1">
<?php
function show_countries($tcountry){
	$sSQL = 'SELECT countryName FROM countries ORDER BY countryOrder DESC, countryName';
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		print "<option value='" . htmlspecials($rs['countryName']) . "'";
		if($tcountry==$rs['countryName'])
			print ' selected';
		print '>' . $rs['countryName'] . "</option>\n";
	}
	ect_free_result($result);
}
show_countries(@$affilCountry)
?>
					</select>
				  </td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyZip?>:</strong></td>
				  <td align="left"><input type="text" name="zip" size="10" value="<?php print htmlspecials($affilZip)?>" /></td>
				  <td align="right"><strong>Inform me:</strong></td>
				  <td align="left"><input type="checkbox" name="inform" value="ON" <?php if($affilInform) print "checked";?> /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $yyCommis?>:</strong></td>
				  <td align="left"><input type="text" name="affilcommision" size="6" value="<?php print htmlspecials($affilCommision)?>" />%</td>
				  <td align="right"><strong><?php print $yyDate?>:</strong></td>
				  <td align="left"><input type="text" name="affildate" size="10" value="<?php print htmlspecials($affilDate)?>" /></td>
				</tr>
				<tr>
				  <td width="100%" colspan="4">
					<span style="font-size:10px"><ul><li><?php print $yyAffInf?></li></ul></span>
				  </td>
				</tr>
				<tr>
				  <td width="50%" align="center" colspan="4"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /></td>
				</tr>
			  </table>
			</form>
<?php
}elseif(getpost('posted')=='1' && $success){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminaffil.php<?php
							print "?rid=" . getpost('rid') . "&stock=" . getpost('stock') . "&stext=" . urlencode(getpost('stext')) . "&sd=" . getpost('sd') . "&ed=" . getpost('ed') . "&stype=" . getpost('stype') . "&approved=" . getpost('approved') . "&pg=" . getpost('pg');
						?>"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(getpost('posted')=='1'){ ?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}else{
	$pract=@$_COOKIE['practaf'];
	$hasdaterange=FALSE;
	if(trim(@$_REQUEST['sd'])!=''){ $thefromdate=parsedate($_REQUEST['sd']); $hasdaterange=TRUE; }
	if(trim(@$_REQUEST['ed'])=='') $thetodate=time()+($dateadjust*60*60); else $thetodate=parsedate($_REQUEST['ed']);
	if(FALSE){
		$hasdaterange=FALSE;
		$errmsg=$yyDatInv;
	}
	if($hasdaterange){
		$thetodate+=(60*60*24);
	}
	$sText = escape_string(@$_REQUEST['stext']);
	$findinvalids = (trim(@$_REQUEST['stype'])=='invalid');
	$themask = 'yyyy-mm-dd';
	if($admindateformat==1)
		$themask='mm/dd/yyyy';
	elseif($admindateformat==2)
		$themask='dd/mm/yyyy';

	$numaffiliates=0;
	$sSQL = 'SELECT COUNT(*) AS thecount FROM affiliates';
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		if(! is_null($rs['thecount'])) $numaffiliates=$rs['thecount'];
	}
	ect_free_result($result);
	$alldata = '';
	if($findinvalids){
		$sSQL = "SELECT ordAffiliate,ordID,ordDate,ordReferer,ordQueryStr,ordTotal FROM orders LEFT JOIN affiliates ON orders.ordAffiliate=affiliates.affilID WHERE ordAffiliate<>'' AND NOT (ordAffiliate IS NULL) AND affilID IS NULL";
		if($hasdaterange) $sSQL.=" AND ordDate BETWEEN '".date('Y-m-d', $thefromdate)."' AND '".date('Y-m-d', $thetodate)."'";
		if($sText!='') $sSQL.=" AND (ordAffiliate LIKE '%" . $sText . "%' OR ordName LIKE '%" . $sText . "%')";
		$sSQL.=' ORDER BY ordID DESC';
		$alldata=ect_query($sSQL) or ect_error();
	}else{
		$affillist='';
		if($hasdaterange){
			$addcomma='';
			$sSQL = "SELECT DISTINCT ordAffiliate FROM orders WHERE ordStatus>=3 AND ordAffiliate<>'' AND NOT (ordAffiliate IS NULL) AND ordDate BETWEEN '".date('Y-m-d', $thefromdate)."' AND '".date('Y-m-d', $thetodate)."'";
			if($sText!='') $sSQL.=" AND ordAffiliate LIKE '%" . $sText . "%'";
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				$affillist.=$addcomma."'".str_replace(array("'",'<'), '', $rs['ordAffiliate']) . "'";
				$addcomma=',';
			}
			ect_free_result($result);
		}
		if($affillist!=''){
			$sSQL = "SELECT affilID,affilName,affilPW,affilEmail,affilCommision,SUM(ordTotal-ordDiscount) AS affilQuant,affilDate FROM affiliates LEFT JOIN orders ON affiliates.affilID=orders.ordAffiliate WHERE ordStatus>=3 AND affilID IN (".$affillist.")";
			if($hasdaterange) $sSQL.=" AND ordDate BETWEEN '".date('Y-m-d', $thefromdate)."' AND '".date('Y-m-d', $thetodate)."'";
			$sSQL.=' GROUP BY affilID,affilName,affilPW,affilEmail,affilCommision';
			if(@$_REQUEST['resorder']=='1') $sSQL.=' ORDER BY affilID'; else $sSQL.=' ORDER BY affilQuant DESC';
		}else{
			$sSQL = 'SELECT affilID,affilName,affilPW,affilEmail,affilCommision,0 AS affilQuant,affilDate FROM affiliates';
			if($sText!=''){
				$sSQL.=" WHERE affilID LIKE '%" . $sText . "%' OR affilName LIKE '%" . $sText . "%' OR affilEmail LIKE '%" . $sText . "%'";
			}
			$sSQL.=' ORDER BY affilID';
		}
		if(! ($hasdaterange && $affillist==''))
			$alldata=ect_query($sSQL) or ect_error();
	}
?>
<script type="text/javascript" src="popcalendar.js"></script>
<script type="text/javascript">
<!--
try{languagetext('<?php print @$adminlang?>');}catch(err){}
function setCookie(c_name,value,expiredays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
function mrec(id){
	document.mainform.action="adminaffil.php";
	document.mainform.id.value=id;
	document.mainform.act.value="modify";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.action="adminaffil.php";
	document.mainform.id.value=id;
	document.mainform.act.value="addnew";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
function delrec(id){
if (confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.affilid.value=id;
	document.mainform.act.value="search";
	document.mainform.editaction.value="delete";
	document.mainform.submit();
}
}
function dumpinventory(){
	document.mainform.action="dumporders.php";
	document.mainform.act.value="dumpaffiliate";
	document.mainform.submit();
}
function startsearch(){
	document.mainform.action="adminaffil.php";
	document.mainform.act.value="search";
	document.mainform.stock.value="";
	document.mainform.posted.value="";
	document.mainform.submit();
}
function quickupdate(){
	if(document.mainform.pract.value=="del"){
		if(!confirm("<?php print jscheck($yyConDel)?>\n"))
			return;
	}
	document.mainform.action="adminaffil.php";
	document.mainform.act.value="quickupdate";
	document.mainform.posted.value="1";
	document.mainform.submit();
}
function proccod(tmen,ordid,affid){
	theact=tmen[tmen.selectedIndex].value;
	if(theact=="1"){
		newwin=window.open("adminorders.php?id="+ordid,"Orders","menubar=no, scrollbars=yes, width=800, height=680, directories=no,location=no,resizable=yes,status=no,toolbar=no");
	}else if(theact=="2"){
		if((affid=prompt("Please enter the new affiliate id for this order.",affid))!=null){
			document.mainform.action="adminaffil.php";
			document.mainform.act.value="search";
			document.mainform.editaction.value="editaffil";
			document.mainform.id.value=ordid;
			document.mainform.affilid.value=affid;
			document.mainform.posted.value="";
			document.mainform.submit();
		}
	}else if(theact=="3"){
		if(confirm("<?php print jscheck($yySureCa)?>")){
			document.mainform.action="adminaffil.php";
			document.mainform.act.value="search";
			document.mainform.editaction.value="editaffil";
			document.mainform.id.value=ordid;
			document.mainform.affilid.value="";
			document.mainform.posted.value="";
			document.mainform.submit();
		}
	}else if(theact=="4"){
		if(confirm("Are you sure you want to remove all instances of affiliate code: "+affid)){
			document.mainform.action="adminaffil.php";
			document.mainform.act.value="search";
			document.mainform.editaction.value="removeaffil";
			document.mainform.affilid.value=affid;
			document.mainform.posted.value="";
			document.mainform.submit();
		}
	}
	tmen.selectedIndex=0;
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
function changepract(obj){
	setCookie('practaf',obj[obj.selectedIndex].value,600);
	startsearch();
}
// -->
</script>
<h2><?php print $yyAdmAff.' ('.$numaffiliates.')'?></h2>
	<form name="mainform" method="post" action="adminaffil.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="" />
			<input type="hidden" name="stock" value="" />
			<input type="hidden" name="id" value="" />
			<input type="hidden" name="editaction" value="" />
			<input type="hidden" name="affilid" value="" />
			<input type="hidden" name="pg" value="<?php print (getpost('act')=='search' ? '1' : getget('pg'))?>" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr> 
				<td class="cobhl" width="20%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" width="30%"><input type="text" name="stext" size="20" value="<?php print @$_REQUEST['stext']?>" /></td>
				<td class="cobhl" width="20%" align="right"><?php print $yyAffBet?>:</td>
				<td class="cobll" width="30%" style="white-space:nowrap"><input type="text" name="sd" size="10" value="<?php print @$_REQUEST['sd']?>" />&nbsp;<input type="button" onclick="popUpCalendar(this, document.forms.mainform.sd, '<?php print $themask?>', -205)" value="DP" />&nbsp;<?php print $yyAnd?>&nbsp;<input type="text" name="ed" size="10" value="<?php print @$_REQUEST['ed']?>" />&nbsp;<input type="button" onclick="popUpCalendar(this, document.forms.mainform.ed, '<?php print $themask?>', -205)" value="DP" /></td>
			  </tr>
			  <tr>
				<td class="cobhl"align="right"><?php
					if($pract=="del" || $pract=="app"){ ?>
						<input type="button" value="<?php print $yyCheckA?>" onclick="checkboxes(true);" style="float:left" />
<?php				}
					print $yySrchTp?>:</td>
				<td class="cobll"><select name="stype" size="1">
					<option value="">Valid Affiliates</option>
					<option value="invalid"<?php if(@$_REQUEST['stype']=='invalid') print ' selected="selected"'?>>Invalid Affilates</option>
					</select>
				</td>
				<td class="cobhl"align="right"><?php print $yyResOrd?>:</td>
				<td class="cobll">
				  <select name="resorder" size="1">
				  <option value=""><?php print $yyTotSal?></option>
				  <option value="1" <?php if(@$_REQUEST['resorder']=="1") print ' selected="selected"'?>><?php print $yyAffId?></option>
				  </select>
				</td>
			  </tr>
			  <tr>
				<td class="cobhl">&nbsp;</td>
				<td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
					  <td class="cobll" align="center"><input type="button" value="<?php print $yyListRe?>" onclick="startsearch();" /> 
						<input type="button" value="<?php print $yyNewAff?>" onclick="newrec();" />
						<input type="button" value="<?php print $yyAffRep?>" onclick="dumpinventory()" />
					  </td>
					  <td class="cobll" height="26" width="20%" align="right">&nbsp;</td>
					</tr>
				  </table></td>
			  </tr>
			</table>
<?php
	if(@$_REQUEST['act']=='search' || getget('pg')!=''){
		$resultcounter=0;
		$hasheader=FALSE;
		if($findinvalids)
			$extcols=6;
		else{
			if($hasdaterange) $extcols=7; else $extcols=5;
		}
?>
			<table width="100%" class="stackable admin-table-a sta-white">
<?php	if($findinvalids){ ?>
				<tr>
				  <th><strong><?php print $yyAffId?></strong></th>
				  <th align="center"><strong><?php print $yyOrdId?></strong></th>
				  <th align="center"><strong><?php print $yyDate?></strong></th>
				  <th align="center"><strong><?php print $yyWebURL?></strong></th>
				  <th align="right"><strong><?php print $yyAmount?></strong></th>
				  <th class="minicell"><strong><?php print $yyAct?></strong></th>
				</tr>
<?php	}else{ ?>
				<tr>
				  <th class="minicell">
					<select name="pract" id="pract" size="1" onchange="changepract(this)">
					<option value="none">Quick Entry...</option>
					<option value="" disabled="disabled">------------------</option>
					<option value="del"<?php if($pract=='del') print ' selected="selected"'?>><?php print $yyDelete?></option>
					</select></th>
				  <th><strong><?php print $yyAffId?></strong></th>
				  <th><strong><?php print $yyName?></strong></th>
				  <th><strong><?php print $yyEmail?></strong></th>
<?php		if($hasdaterange){ ?>
				  <th align="right"><strong><?php print str_replace(' ', '&nbsp;', $yyTotSal)?></strong></th>
				  <th align="right"><strong><?php print $yyCommis?></strong></th>
<?php		} ?>
				  <th class="minicell"><strong><?php print $yyDelete?></strong></th>
				</tr>
<?php	}
		if($alldata=='' || ect_num_rows($alldata)==0){ ?>
				<tr>
				  <td width="100%" align="center" colspan="<?php print $extcols?>"><br />&nbsp;<br /><strong><?php print $yyItNone?></strong><br />&nbsp;</td>
				</tr>
<?php	}else{
			$totsales=0;
			$totcomission=0;
			$hasheader=TRUE;
			while($rs=ect_fetch_assoc($alldata)){
				if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark'; ?>
				<tr class="<?php print $bgcolor?>">
<?php			if($findinvalids){ ?>
				  <td><strong><?php print htmlspecials($rs['ordAffiliate'])?></strong></td>
				  <td align="right"><?php print htmlspecials($rs['ordID'])?>&nbsp;</td>
				  <td align="right"><?php print date($admindatestr, strtotime($rs['ordDate']))?>&nbsp;</td>
				  <td><?php
						$fullurl = $rs['ordReferer'].(trim($rs['ordQueryStr'])!='' ? '?'.$rs['ordQueryStr'] : '');
						if($fullurl!='') print '<a href="'.$fullurl.'" title="'.$fullurl.'" target="_blank">'.substr($fullurl, 0, 50).(strlen($fullurl)>50?'...':'').'</a>';
				?></td>
				  <td align="right"><?php print FormatEuroCurrency($rs['ordTotal'])?></td>
				  <td align="right"><select size="1" onchange="proccod(this,'<?php print $rs['ordID']?>','<?php print htmlspecials($rs['ordAffiliate'])?>')">
				  <option value=""><?php print $yySelect?></option>
				  <option value="1"><?php print $yyVieDet?></option>
				  <option value="2">Edit Code</option>
				  <option value="3">Remove Code</option>
				  <option value="4">Remove All</option>
				  </select></td>
<?php			}else{ ?>
				  <td class="minicell"><?php
					if($pract=='del')
						print '<input type="checkbox" id="chkbx'.$resultcounter.'" name="pra_'.htmlspecials($rs['affilID']).'" value="del" tabindex="'.($resultcounter+1).'"/>';
					else
						print '&nbsp;';
				?></td><td><a href="javascript:mrec('<?php print jsspecials($rs['affilID'])?>')"><strong><?php print htmlspecials($rs['affilID'])?></strong></a>
<?php				if(time()-strtotime($rs['affilDate']) < (7*60*60*24)) print ' <span style="color:#FF0000">' . '**'.$yyNew.'**' . '</span>'?>
				  </td>
				  <td><?php print htmlspecials($rs['affilName'])?></td>
				  <td><a href="mailto:<?php print htmlspecials($rs['affilEmail'])?>"><?php print htmlspecials($rs['affilEmail'])?></a></td>
<?php				if($hasdaterange){ ?>
				  <td align="right"><?php if(! is_numeric($rs['affilQuant'])) print "-"; else{ print FormatEuroCurrency($rs['affilQuant']); $totsales+=$rs['affilQuant']; } ?></td>
				  <td align="right"><?php if(! is_numeric($rs['affilQuant']) || $rs['affilCommision']==0) print "-"; else{ print FormatEuroCurrency(($rs['affilCommision']*$rs['affilQuant']) / 100.0); $totcomission+=(($rs['affilCommision']*$rs['affilQuant']) / 100.0); }?></td>
<?php				} ?>
				  <td class="minicell"><input type="button" value="<?php print $yyDelete?>" onclick="delrec('<?php print jsspecials($rs['affilID'])?>')" /></td>
<?php			} ?>
				</tr>
<?php			$resultcounter++;
			}
 			if($totsales>0 || $totcomission>0){ ?>
				<tr><td colspan="3">&nbsp;</td><td align="right"><?php print FormatEuroCurrency($totsales)?></td><td align="right"><?php print FormatEuroCurrency($totcomission)?></td><td>&nbsp;</td></tr>
<?php		}
		} ?>
			  <tr>
<?php	if($hasheader){ ?>
				<td align="center" style="white-space:nowrap"><?php if($resultcounter>0 && $pract!='' && $pract!='none') print '<input type="hidden" name="resultcounter" id="resultcounter" value="'.$resultcounter.'" /><input type="button" value="'.$yyUpdate.'" onclick="quickupdate()" /> <input type="reset" value="'.$yyReset.'" />'; else print '&nbsp;'?></td>
<?php	} ?>
                <td width="100%" colspan="<?php print $extcols-($hasheader?1:0)?>" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
			</table>
<?php
	}else
		print '&nbsp;<br />&nbsp;<br />&nbsp;<br />';
	if($alldata!='') ect_free_result($alldata);
?>
	</form>
<?php
}
?>