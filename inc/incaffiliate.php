<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $pathtossl,$alreadygotadmin,$forceloginonhttps;
if(@$_SERVER['CONTENT_LENGTH']!='' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
$addsuccess = TRUE;
$success = TRUE;
$showaccount = TRUE;
if(@$pathtossl!=''){
	if(substr($pathtossl,-1)!='/') $pathtossl.='/';
}else
	$pathtossl='';
$alreadygotadmin = getadminsettings();
if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.basename($_SERVER['PHP_SELF']).(@$_SERVER['QUERY_STRING']!='' ? '?'.$_SERVER['QUERY_STRING'] : '')); exit; }
$theaffilid = preg_replace('/[\W]/', '', getpost('affilid'));
if(getpost('editaction')!=''){
	if($theaffilid==''){
		$addsuccess = FALSE;
	}elseif(getpost('editaction')=='modify'){
		$sSQL='UPDATE affiliates SET ';
		if(getpost('affilpw')!='') $sSQL.="affilPW='" . escape_string(dohashpw(getpost('affilpw'))) . "',";
		$sSQL.="affilEmail='" . escape_string(getpost('email')) . "'," .
			"affilName='" . escape_string(getpost('name')) . "'," .
			"affilAddress='" . escape_string(getpost('address')) . "'," .
			"affilCity='" . escape_string(getpost('city')) . "'," .
			"affilState='" . escape_string(getpost('state')) . "'," .
			"affilCountry='" . escape_string(getpost('country')) . "'," .
			"affilZip='" . escape_string(getpost('zip')) . "',";
		$sSQL.='affilInform='.(getpost('inform')=="ON"?1:0) . " WHERE affilID='" . escape_string($theaffilid) . "'";
		if(!ect_query($sSQL)){
			$addsuccess=FALSE;
			$xxAffUse='There was a problem updating your affiliate details. Please try again.';
		}
	}elseif(getpost('editaction')=='new'){
		$sSQL = "SELECT affilID FROM affiliates WHERE affilID='" . escape_string($theaffilid) . "'";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result) > 0) $addsuccess=FALSE;
		ect_free_result($result);
		if($addsuccess){
			$sSQL='INSERT INTO affiliates (affilID,affilPW,affilEmail,affilName,affilAddress,affilCity,affilState,affilCountry,affilZip,affilCommision,affilDate,affilInform) VALUES (' .
				"'" . escape_string($theaffilid) . "'," .
				"'" . escape_string(dohashpw(getpost('affilpw'))) . "'," .
				"'" . escape_string(getpost('email')) . "'," .
				"'" . escape_string(getpost('name')) . "'," .
				"'" . escape_string(getpost('address')) . "'," .
				"'" . escape_string(getpost('city')) . "'," .
				"'" . escape_string(getpost('state')) . "'," .
				"'" . escape_string(getpost('country')) . "'," .
				"'" . escape_string(getpost('zip')) . "',";
			if(@$defaultcommission!=''){
				$sSQL.=$defaultcommission . ',';
				$_SESSION['affilCommision']=(double)$defaultcommission;
			}else{
				$sSQL.='0,';
				$_SESSION['affilCommision']=0;
			}
			$sSQL.="'" . date('Y-m-d') . "'," . (getpost('inform')=='ON'?1:0) . ') ';
			if(!ect_query($sSQL)){
				$addsuccess=FALSE;
				$xxAffUse='There was a problem entering your affiliate details. Please try again.';
			}
			if($addsuccess){
				if(($GLOBALS['adminEmailConfirm'] & 2)==2){
					$emailmessage='There has been a new affiliate signup at your store: ' . $theaffilid . $emlNl .
						'Email: ' . getpost('email') . $emlNl .
						'Name: ' . getpost('name') . $emlNl .
						'Address: ' . getpost('address') . $emlNl .
						'City: ' . getpost('city') . $emlNl .
						'State: ' . getpost('state') . $emlNl .
						'Country: ' . getpost('country') . $emlNl .
						'Zip: ' . getpost('zip') . $emlNl;
					dosendemail($emailAddr,$emailAddr,getpost('Email'),'New Affiliate Signup',$emailmessage);
				}
				print '<meta http-equiv="Refresh" content="0; URL=affiliate.php">';
			}
		}
	}
	if($addsuccess){
		$_SESSION['xaffilid'] = $theaffilid;
		if(getpost('affilpw')!='') $_SESSION['xaffilpw'] = dohashpw(getpost('affilpw'));
		$_SESSION['xaffilName'] = getpost('name');
	}
}elseif(getpost('act')=='affillogin'){
	$sSQL = "SELECT affilID,affilName,affilCommision,affilPW FROM affiliates WHERE affilID='" . escape_string($theaffilid) . "' AND affilPW='" . escape_string(dohashpw(getpost('affilpw'))) . "'";
	$result=ect_query($sSQL) or ect_error();
	if(ect_num_rows($result)>0){
		$rs=ect_fetch_assoc($result);
		$_SESSION["xaffilid"] = $theaffilid;
		$_SESSION["xaffilpw"] = $rs['affilPW'];
		$_SESSION["xaffilName"] = $rs["affilName"];
		$_SESSION["affilCommision"] = (double)$rs["affilCommision"];
		$showaccount=FALSE;
	}else
		$success=FALSE;
	ect_free_result($result);
	if($success){
		print '<meta http-equiv="Refresh" content="3; URL=affiliate.php">';
?>
			<form method="post" action="affiliate.php">
			  <div class="ectdiv">
				<div class="ectdivhead"><?php print $GLOBALS['xxAffPrg'] . " " . $GLOBALS['xxWelcom'] . " " . htmlspecials($_SESSION['xaffilName'])?>.</div>
				<div class="ectmessagescreen"><p><?php print $GLOBALS['xxAffLog']?></p>
					<p><?php print $GLOBALS['xxForAut']?> <a class="ectlink" href="affiliate.php"><?php print $GLOBALS['xxClkHere']?></a>.</p>
				</div>
			  </div>
			</form>
<?php
	}
}elseif(getpost('act')=='logout'){
	$_SESSION['xaffilid'] = '';
	$_SESSION['xaffilpw'] = '';
	$_SESSION['xaffilName'] = '';
}
if(getpost('act')=='newaffil' || (getpost('act')=='editaffil' && trim(@$_SESSION['xaffilid'])!='') || ! $addsuccess){
	$showaccount=FALSE;
?>
<script type="text/javascript">
<!--
function checkform(frm){
if(frm.affilid.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxAffID'])?>\".");
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
	alert("<?php print jscheck($GLOBALS['xxAlphaNu'] . ' "' . $GLOBALS['xxAffID'])?>\".");
	frm.affilid.focus();
	return (false);
}
<?php	if(getpost('act')!='editaffil'){ ?>
if(frm.affilpw.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxPwd'])?>\".");
	frm.affilpw.focus();
	return (false);
}
<?php	} ?>
if(frm.name.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxName'])?>\".");
	frm.name.focus();
	return (false);
}
if(frm.email.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxEmail'])?>\".");
	frm.email.focus();
	return (false);
}
if(frm.address.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxAddress'])?>\".");
	frm.address.focus();
	return (false);
}
if(frm.city.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxCity'])?>\".");
	frm.city.focus();
	return (false);
}
if(frm.state.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxAllSta'])?>\".");
	frm.state.focus();
	return (false);
}
if(frm.zip.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxZip'])?>\".");
	frm.zip.focus();
	return (false);
}
return (true);
}
//-->
</script>
<?php
	$sAffilName = "";
	$sAffilPW = "";
	$sAffilid = "";
	$sAffilAddress = "";
	$sAffilCity = "";
	$sAffilState = "";
	$sAffilZip = "";
	$sAffilCountry = "";
	$sAffilEmail = "";
	$sAffilInform = FALSE;
	if(! $addsuccess){
		$sAffilName = getpost('name');
		$sAffilPW = '';
		$sAffilid = getpost('affilid');
		$sAffilAddress = getpost('address');
		$sAffilCity = getpost('city');
		$sAffilState = getpost('state');
		$sAffilZip = getpost('zip');
		$sAffilCountry = getpost('country');
		$sAffilEmail = getpost('email');
		$sAffilInform = getpost('inform')=="ON";
	}elseif(getpost('act')=='editaffil' && trim(@$_SESSION["xaffilid"])!=''){
		$sSQL = "SELECT affilName,affilPW,affilAddress,affilCity,affilState,affilZip,affilCountry,affilEmail,affilInform FROM affiliates WHERE affilID='" . escape_string(@$_SESSION["xaffilid"]) . "' AND affilPW='" . escape_string(@$_SESSION["xaffilpw"]) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$sAffilName = $rs["affilName"];
			$sAffilPW = $rs["affilPW"];
			$sAffilAddress = $rs["affilAddress"];
			$sAffilCity = $rs["affilCity"];
			$sAffilState = $rs["affilState"];
			$sAffilZip = $rs["affilZip"];
			$sAffilCountry = $rs["affilCountry"];
			$sAffilEmail = $rs["affilEmail"];
			$sAffilInform = ((int)$rs["affilInform"])==1;
		}
		ect_free_result($result);
	}
?>			<form method="post" action="<?php if(@$forceloginonhttps) print $pathtossl?>affiliate.php" onsubmit="return checkform(this)">
			  <div class="ectdiv ectaffiliate">
				<div class="ectdivhead"><?php print $GLOBALS['xxAffDts']?></div>
<?php if(! $addsuccess){ ?>
				<div class="ectdiv2column ectwarning"><?php print $GLOBALS['xxAffUse']?></div>
<?php } ?>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $redasterix.$GLOBALS['xxAffID']?></div>
				  <div class="ectdivright"><?php
					if(getpost('act')=='editaffil' && trim(@$_SESSION['xaffilid'])!=''){
						print htmlspecials(trim(@$_SESSION['xaffilid']));
						?><input type="hidden" name="affilid" size="20" value="<?php print htmlspecials(trim(@$_SESSION['xaffilid']))?>" />
						  <input type="hidden" name="editaction" value="modify" /><?php
					}else{
						?><input type="text" name="affilid" size="20" value="<?php print htmlspecials($sAffilid)?>" />
						  <input type="hidden" name="editaction" value="new" /><?php
					} ?></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print (getpost('act')=='editaffil'?$GLOBALS['xxReset'].' '.$GLOBALS['xxPwd']:$redasterix.$GLOBALS['xxPwd'])?></div>
				  <div class="ectdivright"><input type="password" name="affilpw" size="20" value="" autocomplete="off" /></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $redasterix.$GLOBALS['xxName']?></div>
				  <div class="ectdivright"><input type="text" name="name" size="20" value="<?php print htmlspecials($sAffilName)?>" /></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $redasterix.$GLOBALS['xxEmail']?></div>
				  <div class="ectdivright"><input type="text" name="email" size="25" value="<?php print htmlspecials($sAffilEmail)?>" /></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $redasterix.$GLOBALS['xxAddress']?></div>
				  <div class="ectdivright"><input type="text" name="address" size="20" value="<?php print htmlspecials($sAffilAddress)?>" /></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $redasterix.$GLOBALS['xxCity']?></div>
				  <div class="ectdivright"><input type="text" name="city" size="20" value="<?php print htmlspecials($sAffilCity)?>" /></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $redasterix.$GLOBALS['xxAllSta']?></div>
				  <div class="ectdivright"><input type="text" name="state" size="20" value="<?php print htmlspecials($sAffilState)?>" /></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $redasterix.$GLOBALS['xxCountry']?></div>
				  <div class="ectdivright"><select name="country" size="1"><?php
function show_countries($tcountry){
	$sSQL = 'SELECT countryName,countryOrder,'.getlangid('countryName',8).' AS countryName FROM countries ORDER BY countryOrder DESC,' . getlangid('countryName',8);
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		print "<option value='" . htmlspecials($rs['countryName']) . "'";
		if($tcountry==$rs['countryName'])
			print ' selected';
		print '>' . $rs['countryName'] . "</option>\n";
	}
	ect_free_result($result);
}
show_countries(@$sAffilCountry)
?></select>
				  </div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $redasterix.$GLOBALS['xxZip']?></div>
				  <div class="ectdivright"><input type="text" name="zip" size="10" value="<?php print htmlspecials($sAffilZip)?>" /></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $GLOBALS['xxInfMe']?></div>
				  <div class="ectdivright"><input type="checkbox" name="inform" value="ON" <?php if($sAffilInform) print "checked"?> /></div>
				</div>
				<div class="ectdiv2column"><ul><li><?php print $GLOBALS['xxInform']?></li></ul></div>
				<div class="ectdiv2column"><?php
					print imageorsubmit(@$imgsubmit,$GLOBALS['xxSubmt'],'submit');
					if(getpost('act')=='editaffil' && trim(@$_SESSION['xaffilid'])!=''){
						print '<br /><br />' . imageorbutton(@$imgbackacct,$GLOBALS['xxBack'],'backacct','history.go(-1)',TRUE);
					} ?></div>
			  </div>
			</form>
<?php
}
if($showaccount){
	if(@$_SESSION['xaffilid']==''){
?>			<form method="post" name="mainform" action="<?php if(@$forceloginonhttps) print $pathtossl?>affiliate.php">
			<input type="hidden" name="act" id="act" value="xxx" />
			  <div class="ectdiv ectaffiliate">
				<div class="ectdivhead"><?php print $GLOBALS['xxAffPrg']?></div>
<?php if(! $success){ ?>
				<div class="ectdiv2column ectwarning"><?php print $GLOBALS['xxAffNo']?></div>
<?php } ?>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $GLOBALS['xxAffID']?></div>
				  <div class="ectdivright"><input type="text" name="affilid" size="20" value="<?php print htmlspecials(getpost('affilid'))?>" /></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $GLOBALS['xxPwd']?></div>
				  <div class="ectdivright"><input type="password" name="affilpw" size="20" value="<?php print htmlspecials(getpost('affilpw'))?>" autocomplete="off" /></div>
				</div>
				<div class="ectdiv2column"><?php print imageorbutton(@$imgnewaffiliate,$GLOBALS['xxNewAct'],'newaffiliate',"document.getElementById('act').value='newaffil';document.forms.mainform.submit();",TRUE) . ' ' . imageorsubmit(@$imgaffiliatelogin,$GLOBALS['xxAffLI'].'" onclick="document.getElementById(\'act\').value=\'affillogin\'','affiliatelogin')?></div>
			  </div>
			</form>
<?php
	}else{
		$lastmonth = mktime (0,0,0,date("m")-1,date("d"), date("Y"));
		$totalDay=0.0;
		$totalYesterday=0.0;
		$totalMonth=0.0;
		$totalLastMonth=0.0;
		
		$sSQL = "SELECT Sum(ordTotal-ordDiscount) as theCount FROM orders WHERE ordStatus>=3 AND ordAffiliate='" . escape_string(@$_SESSION["xaffilid"]) . "' AND ordDate BETWEEN '" . date("Y-m-d") . "' AND '" . date("Y-m-d") . " 23:59:59'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result))
			$totalDay = $rs["theCount"];
		ect_free_result($result);
		$sSQL = "SELECT Sum(ordTotal-ordDiscount) as theCount FROM orders WHERE ordStatus>=3 AND ordAffiliate='" . escape_string(@$_SESSION["xaffilid"]) . "' AND ordDate BETWEEN '" . date("Y-m-d", time()-(60*60*24)) . "' AND '" . date("Y-m-d") . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result))
			$totalYesterday = $rs["theCount"];
		ect_free_result($result);
		$sSQL = "SELECT Sum(ordTotal-ordDiscount) as theCount FROM orders WHERE ordStatus>=3 AND ordAffiliate='" . escape_string(@$_SESSION["xaffilid"]) . "' AND ordDate BETWEEN '" . date("Y-m-01") . "' AND '" . date("Y-m-d") . " 23:59:59'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result))
			$totalMonth = $rs["theCount"];
		ect_free_result($result);
		$sSQL = "SELECT Sum(ordTotal-ordDiscount) as theCount FROM orders WHERE ordStatus>=3 AND ordAffiliate='" . escape_string(@$_SESSION["xaffilid"]) . "' AND ordDate BETWEEN '" . date("Y-m-01", $lastmonth) . "' AND '" . date("Y-m-01") . " 00:00:00'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result))
			$totalLastMonth = $rs["theCount"];
		ect_free_result($result);
		if(is_null($totalDay)) $totalDay=0.0;
		if(is_null($totalYesterday)) $totalYesterday=0.0;
		if(is_null($totalMonth)) $totalMonth=0.0;
		if(is_null($totalLastMonth)) $totalLastMonth=0.0;
?>		<form method="post" name="mainform" action="affiliate.php">
		<input type="hidden" name="act" value="" />
			<div class="ectdiv ectaffiliate">
				<div class="ectdivhead"><?php print $GLOBALS['xxAffPrg'] . ' ' . $GLOBALS['xxWelcom'] . ' ' . htmlspecials(@$_SESSION['xaffilName'])?>.</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $GLOBALS['xxTotTod']?></div>
				  <div class="ectdivright"><?php print FormatEuroCurrency($totalDay);
				  if($_SESSION["affilCommision"]!=0) print ' = ' . FormatEuroCurrency(($totalDay * $_SESSION["affilCommision"]) / 100.0) . ' ' . $GLOBALS['xxCommis'];?></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $GLOBALS['xxTotYes']?></div>
				  <div class="ectdivright"><?php print FormatEuroCurrency($totalYesterday);
				  if($_SESSION["affilCommision"]!=0) print ' = ' . FormatEuroCurrency(($totalYesterday * $_SESSION["affilCommision"]) / 100.0) . ' ' . $GLOBALS['xxCommis'];?></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $GLOBALS['xxTotMTD']?></div>
				  <div class="ectdivright"><?php print FormatEuroCurrency($totalMonth);
				  if($_SESSION["affilCommision"]!=0) print ' = ' . FormatEuroCurrency(($totalMonth * $_SESSION["affilCommision"]) / 100.0) . ' ' . $GLOBALS['xxCommis'];?></div>
				</div>
				<div class="ectdivcontainer">
				  <div class="ectdivleft"><?php print $GLOBALS['xxTotLM']?></div>
				  <div class="ectdivright"><?php print FormatEuroCurrency($totalLastMonth);
				  if($_SESSION["affilCommision"]!=0) print ' = ' . FormatEuroCurrency(($totalLastMonth * $_SESSION["affilCommision"]) / 100.0) . ' ' . $GLOBALS['xxCommis'];?></div>
				</div>
				<div class="ectdiv2column"><?php print imageorsubmit(@$imglogout,$GLOBALS['xxLogout'].'" onclick="document.forms.mainform.act.value=\'logout\'','logout') . ' ' . imageorsubmit(@$imgeditaffiliate,$GLOBALS['xxEdtAff'].'" onclick="document.forms.mainform.act.value=\'editaffil\'','editaffiliate')?></div>
				<div class="ectdiv2column">
					<ul>
					  <li><?php print $GLOBALS['xxAffLI1']?> products.php?PARTNER=<?php print htmlspecials(trim(@$_SESSION['xaffilid']))?></li>
					  <li><?php print $GLOBALS['xxAffLI2']?></li>
					  <?php if($_SESSION["affilCommision"]==0){ ?>
					  <li><?php print $GLOBALS['xxAffLI3']?></li>
					  <?php } ?>
					</ul>
				</div>
			</div>
		</form>
<?php
	}
}
?>