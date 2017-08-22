<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
$alreadygotadmin = getadminsettings();
$numshipmethods=10;
if(getpost('posted')=="1"){
	$admintweaks=0;
	if(is_array(@$_POST['admintweaks'])){
		foreach(@$_POST['admintweaks'] as $objValue)
			$admintweaks+=$objValue;
	}
	$adminlangsettings=0;
	if(is_array(@$_POST['adminlangsettings'])){
		foreach(@$_POST['adminlangsettings'] as $objValue)
			$adminlangsettings+=$objValue;
	}
	for($numfilters=0; $numfilters<=1; $numfilters++){
		$pfarr[$numfilters]=0;
		$pftext[$numfilters]='';
		$pftext2[$numfilters]='';
		$pftext3[$numfilters]='';
		for($index=0; $index<=5; $index++){
			if(@$_POST['filtercb'.$numfilters.'_'.$index]=='ON') $pfarr[$numfilters]+=pow(2, $index);
			if($index!=0){
				$pftext[$numfilters].='&';
				$pftext2[$numfilters].='&';
				$pftext3[$numfilters].='&';
			}
			$pftext[$numfilters].=str_replace('&','%26',@$_POST['filtertext'.$numfilters.'_'.$index]);
			$pftext2[$numfilters].=str_replace('&','%26',@$_POST['filtertext'.$numfilters.'_'.$index.'x2']);
			$pftext3[$numfilters].=str_replace('&','%26',@$_POST['filtertext'.$numfilters.'_'.$index.'x3']);
		}
	}
	$sortoptions=0;
	for($index=1; $index<=20; $index++){
		if(@$_POST['sortid'.$index]=="ON") $sortoptions+=pow(2,($index-1));
	}
	$sSQL = "UPDATE admin SET adminEmail='" . getpost('email') . "',adminStoreURL='" . getpost('url') . "' WHERE adminID=1";
	ect_query($sSQL) or ect_error();
	$sSQL = "UPDATE admin SET adminEmail='" . getpost('email') . "',adminStoreURL='" . getpost('url') . "',adminProdsPerPage='" . getpost('prodperpage') . "',adminShipping=" . getpost('shipping') . ",adminIntShipping=" . getpost('intshipping') . ",adminUSPSUser='" . getpost('USPSUser') . "',adminZipCode='" . getpost('zipcode') . "',adminCountry=" . getpost('countrySetting') . ",adminDelUncompleted=" . getpost('deleteUncompleted') . ",adminClearCart=" . getpost('adminClearCart') . ",adminPacking=" . getpost('packing') . ",adminStockManage=" . getpost('stockManage') . ",adminHandling=" . (is_numeric(getpost('handling'))?getpost('handling'):0) . ",adminHandlingPercent=" . (is_numeric(getpost('handlingpercent'))?getpost('handlingpercent'):0) . ",adminTweaks=" . $admintweaks . ",adminCanPostUser='" . getpost('adminCanPostUser') . "',smartPostHub='" . getpost('smartPostHub') . "',";
	if(getpost('emailconfirm')=='ON') $adminEmailConfirm=1; else $adminEmailConfirm=0;
	if(getpost('affilconfirm')=='ON') $adminEmailConfirm+=2;
	if(getpost('customerconfirm')=='ON') $adminEmailConfirm+=4;
	if(getpost('reviewconfirm')=='ON') $adminEmailConfirm+=8;
	$sSQL.='adminEmailConfirm=' . $adminEmailConfirm . ',';
	$sSQL.='adminUnits=' . ((int)getpost('adminUnits') + (int)getpost('adminDims'));
	for($index=1;$index<=3;$index++){
		$sSQL.=',currRate' . $index . '=' . (is_numeric(@$_POST['currRate' . $index])?@$_POST['currRate' . $index]:0);
		$sSQL.=',currSymbol' . $index . "='" . @$_POST['currSymbol' . $index] . "'";
	}
	$sSQL.=",currLastUpdate='" . escape_string(date('Y-m-d H:i:s', time()-100000)) . "'";
	$sSQL.=",currConvUser='" . escape_string(getpost('currConvUser')) . "'";
	$sSQL.=",currConvPw='" . escape_string(getpost('currConvPw')) . "'";
	$sSQL.=",cardinalProcessor='" . escape_string(getpost('cardinalprocessor')) . "'";
	$sSQL.=",cardinalMerchant='" . escape_string(getpost('cardinalmerchant')) . "'";
	$sSQL.=",cardinalPwd='" . escape_string(getpost('cardinalpwd')) . "'";
	$sSQL.=",adminlanguages='" . getpost('adminlanguages') . "'";
	$sSQL.=",adminlang='" . getpost('adminlang') . "'";
	$sSQL.=",storelang='" . getpost('storelang1') . '|' . getpost('storelang2') . '|' . getpost('storelang3') . "'";
	$sSQL.=",adminAltRates='" . getpost('adminAltRates') . "'";
	$sSQL.=", prodFilter=" . $pfarr[0];
	$sSQL.=",sideFilter=" . $pfarr[1];
	$sSQL.=",prodFilterOrder='" . escape_string(getpost("prodfilterorder0")) . "'";
	$sSQL.=",sideFilterOrder='" . escape_string(getpost("prodfilterorder1")) . "'";
	$sSQL.=",prodFilterText='" . escape_string($pftext[0]) . "'";
	$sSQL.=",sideFilterText='" . escape_string($pftext[1]) . "'";
	if(($adminlangsettings & 262144)==262144){
		if($adminlanguages>=1) $sSQL.=",prodFilterText2='" . escape_string($pftext2[0]) . "',sideFilterText2='" . escape_string($pftext2[1]) . "'";
		if($adminlanguages>=2) $sSQL.=",prodFilterText3='" . escape_string($pftext3[0]) . "',sideFilterText3='" . escape_string($pftext3[1]) . "'";
	}
	$sSQL.=",sortOrder=" . getpost('sortorder');
	$sSQL.=",sortOptions=" . $sortoptions;
	$sSQL.=",adminlangsettings='" . $adminlangsettings . "'";
	ect_query($sSQL) or ect_error();
	$sSQL = 'SELECT adminSecret FROM admin WHERE adminID=1';
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
	$currsecret=trim($rs['adminSecret']);
	ect_free_result($result);
	if($currsecret=='') ect_query("UPDATE admin SET adminSecret='its a real secret ".rand(1000000,9999999)." now' WHERE adminID=1") or ect_error();

	$altrateids = explode(',',getpost('altrateids'));
	$altrateuse = explode(',',getpost('altrateuse'));
	$altrateuseintl = explode(',',getpost('altrateuseintl'));
	$altratetext = explode(',',getpost('altratetext'));
	$altratetext2 = explode(',',getpost('altratetext2'));
	$altratetext3 = explode(',',getpost('altratetext3'));

	for($index=1; $index<=$numshipmethods; $index++){
		if($index==1 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyFlatShp;
		if($index==2 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyWghtShp;
		if($index==3 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyUSPS;
		if($index==4 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyUPS;
		if($index==5 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyPriShp;
		if($index==6 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyCanPos;
		if($index==7 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyFedex;
		if($index==8 && trim($altratetext[$index-1])=='') $altratetext[$index-1]='FedEx SmartPost';
		if($index==9 && trim($altratetext[$index-1])=='') $altratetext[$index-1]=$yyDHLShp;
		$altratetext[$index-1]=substr($altratetext[$index-1],0,200);
		$altratetext2[$index-1]=substr($altratetext2[$index-1],0,200);
		$altratetext3[$index-1]=substr($altratetext3[$index-1],0,200);
		if(trim($altratetext2[$index-1])=='') $altratetext2[$index-1]=$altratetext[$index-1];
		if(trim($altratetext3[$index-1])=='') $altratetext3[$index-1]=$altratetext[$index-1];

		$sSQL = "UPDATE alternaterates SET altratetext='".escape_string(urldecode($altratetext[$index-1]))."',altratetext2='".escape_string(urldecode($altratetext2[$index-1]))."',altratetext3='".escape_string(urldecode($altratetext3[$index-1]))."', usealtmethod=".$altrateuse[$index-1].",usealtmethodintl=".$altrateuseintl[$index-1].",altrateorder=".$index." WHERE altrateid=".$altrateids[$index-1];
		ect_query($sSQL) or ect_error();
	}
	print '<meta http-equiv="refresh" content="1; url=adminmain.php">';
}else{
	$allcurrencies="";
	$numcurrencies=0;
	$sSQL = "SELECT DISTINCT countryCurrency FROM countries ORDER BY countryCurrency";
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result))
		$allcurrencies[$numcurrencies++]=$rs;
	ect_free_result($result);
}
if(getpost('posted')=="1" && $success){ ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
				<td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
						<?php print $yyNoAuto?> <a href="adminmain.php"><strong><?php print $yyClkHer?></strong></a>.<br /><br />&nbsp;</td>
			  </tr>
			</table>
<?php
}else{
		$sSQL = "SELECT adminEmail,adminStoreURL,adminProdsPerPage,adminShipping,adminIntShipping,adminUSPSUser,smartPostHub,adminZipCode,adminEmailConfirm,adminCountry,adminUnits,adminDelUncompleted,adminClearCart,adminPacking,adminStockManage,adminHandling,adminHandlingPercent,adminTweaks,currRate1,currSymbol1,currRate2,currSymbol2,currRate3,currSymbol3,currConvUser,currConvPw,cardinalProcessor,cardinalMerchant,cardinalPwd,adminCanPostUser,adminlanguages,adminlang,storelang,adminlangsettings,adminAltRates,prodFilter,prodFilterOrder,prodFilterText,prodFilterText2,prodFilterText3,sideFilter,sideFilterOrder,sideFilterText,sideFilterText2,sideFilterText3,sortOrder,sortOptions FROM admin WHERE adminID=1";
		$result=ect_query($sSQL) or ect_error();
		$rsAdmin=ect_fetch_assoc($result);
		ect_free_result($result);
?>
<script type="text/javascript">
<!--
function formvalidator(theForm){
  if(theForm.prodperpage.value==""){
	alert("<?php print jscheck($yyPlsEntr . ' "' . $yyPPP)?>\".");
	theForm.prodperpage.focus();
	return (false);
  }
  var checkOK = "0123456789";
  var checkStr = theForm.prodperpage.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++){
	ch = checkStr.charAt(i);
	for (j = 0;  j < checkOK.length;  j++)
		if (ch==checkOK.charAt(j))
			break;
	if (j==checkOK.length){
		allValid = false;
			break;
	}
  }
  if (!allValid){
	alert("<?php print jscheck($yyOnlyNum . ' "' . $yyPPP)?>\".");
	theForm.prodperpage.focus();
	return (false);
  }
for(index=1;index<=3;index++){
  var checkOK = "0123456789.";
  var thisRate = eval("theForm.currRate" + index);
  var checkStr = thisRate.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++){
	ch = checkStr.charAt(i);
	for (j = 0;  j < checkOK.length;  j++)
		if (ch==checkOK.charAt(j))
			break;
	if (j==checkOK.length){
		allValid = false;
			break;
	}
  }
  if (!allValid){
	alert("<?php print jscheck($yyOnlyDec . ' "' . $yyConRat)?> " + index + "\".");
	thisRate.focus();
	return (false);
  }
}

  if(theForm.handling.value==""){
	alert('<?php print jscheck($yyPlsEntr . ' "' . $yyHanChg)?>\". <?php print jscheck($yyNoHan)?>');
	theForm.handling.focus();
	return (false);
  }
  var checkOK = "0123456789.";
  var checkStr = theForm.handling.value;
  var allValid = true;
  for (i = 0;  i < checkStr.length;  i++){
	ch = checkStr.charAt(i);
	for (j = 0;  j < checkOK.length;  j++)
		if (ch==checkOK.charAt(j))
			break;
	if (j==checkOK.length){
		allValid = false;
			break;
	}
  }
  if (!allValid){
	alert("<?php print jscheck($yyOnlyDec . ' "' . $yyHanChg)?>\".");
	theForm.handling.focus();
	return (false);
  }

	var altrateids="";
	var altrateuse="";
	var altrateuseintl="";
	var altratetext="";
	var altratetext2="";
	var altratetext3="";
	for(var index=1; index<=<?php print $numshipmethods?>; index++){
		altrateids+=(document.getElementById("altrateids"+index).value+',');
		altrateuse+=((document.getElementById("altrateuse"+index).checked?'1':'0')+',');
		altrateuseintl+=((document.getElementById("altrateuseintl"+index).checked?'1':'0')+',');
		altratetext+=(encodeURIComponent(document.getElementById("altratetext_"+index).value)+',');
		altratetext2+=(encodeURIComponent(document.getElementById("altratetext2_"+index).value)+',');
		altratetext3+=(encodeURIComponent(document.getElementById("altratetext3_"+index).value)+',');
	}
	altrateids=altrateids.substr(0,altrateids.length-1);
	altrateuse=altrateuse.substr(0,altrateuse.length-1);
	altrateuseintl=altrateuseintl.substr(0,altrateuseintl.length-1);
	altratetext=altratetext.substr(0,altratetext.length-1);
	altratetext2=altratetext2.substr(0,altratetext2.length-1);
	altratetext3=altratetext3.substr(0,altratetext3.length-1);
	// alert(altrateids+"\n"+altrateuse+"\n"+altrateuseintl+"\n"+altratetext+"\n"+altratetext2+"\n"+altratetext3);
	document.getElementById("altrateids").value=altrateids;
	document.getElementById("altrateuse").value=altrateuse;
	document.getElementById("altrateuseintl").value=altrateuseintl;
	document.getElementById("altratetext").value=altratetext;
	document.getElementById("altratetext2").value=altratetext2;
	document.getElementById("altratetext3").value=altratetext3;
	return (true);
}
<?php
	if(trim($rsAdmin['prodFilterOrder'])=='') $prodfilterorder='1,2,4,8,16,32'; else $prodfilterorder=trim($rsAdmin['prodFilterOrder']);
	if(trim($rsAdmin['sideFilterOrder'])=='') $sidefilterorder='1,2,4,8,16,32'; else $sidefilterorder=trim($rsAdmin['sideFilterOrder']);
	$prodfilterorder=replace($prodfilterorder.",","1,",""); // Because Manufacturer (1) is now included with attributes
	$sidefilterorder=replace($sidefilterorder.",","1,","");
	if(substr($prodfilterorder,-1)==',') $prodfilterorder=substr($prodfilterorder,0,-1);
	if(substr($sidefilterorder,-1)==',') $sidefilterorder=substr($sidefilterorder,0,-1);
?>
var currfilterorder=[];
currfilterorder[0]='<?php print $prodfilterorder?>'.split(',');
currfilterorder[1]='<?php print $sidefilterorder?>'.split(',');
function swapFilterTRows(whichfilter,fromrow,torow){
	var srtable=document.getElementById("filtertable"+whichfilter);
	if(srtable.moveRow){
		srtable.moveRow(fromrow+1,torow+1);
	}else{ // FF etc
		var firstRow=srtable.rows[fromrow+1];
		firstRow.parentNode.insertBefore(srtable.rows[torow+1],firstRow);
	}
}
function swapfilteritems(whichfilter,item){
	var thisrow=document.getElementById('filter'+whichfilter+'item'+item);
	var thistemphtml=thisrow.innerHTML;
	var thistempid=thisrow.id,currpos;
	for(var ii in currfilterorder[whichfilter]){
		if(currfilterorder[whichfilter][ii]==item) currpos=parseInt(ii);
	}
	if(currpos!=0){
		swapFilterTRows(whichfilter,currpos-1,currpos)
		var temporder=currfilterorder[whichfilter][currpos-1];
		currfilterorder[whichfilter][currpos-1]=currfilterorder[whichfilter][currpos]
		currfilterorder[whichfilter][currpos]=temporder;
		document.getElementById('prodfilterorder'+whichfilter).value=currfilterorder[whichfilter].join();
	}
	return false;
}
//-->
</script>
<form method="post" action="adminmain.php" onsubmit="return formvalidator(this)">
<input type="hidden" name="posted" value="1" />
<?php		if(! $success){ ?>
			  <p style="text-align:center"><br /><br /><span style="color:#FF0000"><?php print $errmsg?></span></p>
<?php		} ?>
	<h3 class="round_top half_top"><?php print $yyStoSet?></h3>
	<table class="admin-table-b keeptable">
		<tr>
		  <th colspan="2"><?php print $yyCsSym?></th>
		</tr>
		<tr>
		  <td><strong><?php print $yyCouSet?>: </strong></td>
		  <td><select name="countrySetting" size="1"><?php
				$sSQL = "SELECT countryID,countryName FROM countries WHERE countryLCID<>'' ORDER BY countryOrder DESC, countryName";
				$rsCountry=ect_query($sSQL) or ect_error();
				while($rs=ect_fetch_assoc($rsCountry)){
					print "<option value='" . $rs['countryID'] . "'";
					if($rsAdmin['adminCountry']==$rs['countryID']) print ' selected="selected"';
					print '>'. $rs['countryName'] . "</option>\n";
				}
				ect_free_result($rsCountry);
				  ?></select></td>
		</tr>
		<tr>
		  <td colspan="2"><?php print $yyURLEx . ' ' . $yyExample?><br /><?php
				$guessURL = 'http://' . @$_SERVER['SERVER_NAME'] . @$_SERVER['REQUEST_URI'];
				$guessURL = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']);
				$wherevs = strpos(strtolower($guessURL),'vsadmin');
				if($wherevs > 0)
					$guessURL = substr($guessURL, 0, $wherevs);
				else
					$guessURL = 'http://www.myurl.com/mystore/';
				print $guessURL;
						?></td>
		</tr>
		<tr>
		  <td><strong><?php print $yyStoreURL?>:</strong></td>
		  <td><input type="text" name="url" size="45" value="<?php print $rsAdmin['adminStoreURL']?>" />
		  </td>
		</tr>
		<tr>
		  <td colspan="2"><?php print $yyHMPPP?></td>
		</tr>
		<tr>
		  <td><strong><?php print $yyPPP?>:</strong></td>
		  <td><input type="text" name="prodperpage" size="10" value="<?php print $rsAdmin['adminProdsPerPage']?>" /></td>
		</tr>
		<tr>
		  <td><strong><?php print $yyDefSor?>:</strong></td>
		  <td><select name="sortorder" size="1">
			<option value="0"><?php print $yySelect?></option>
			<option value="1"<?php if($rsAdmin['sortOrder']==1) print ' selected="selected"'?>><?php print $yySortAl?></option>
			<option value="11"<?php if($rsAdmin['sortOrder']==11) print ' selected="selected"'?>><?php print $yySortAl.' (Desc.)'?></option>
			<option value="2"<?php if($rsAdmin['sortOrder']==2) print ' selected="selected"'?>><?php print $yySortID?></option>
			<option value="12"<?php if($rsAdmin['sortOrder']==12) print ' selected="selected"'?>><?php print $yySortID.' (Desc.)'?></option>
			<option value="3"<?php if($rsAdmin['sortOrder']==3) print ' selected="selected"'?>><?php print $yySortPA?></option>
			<option value="4"<?php if($rsAdmin['sortOrder']==4) print ' selected="selected"'?>><?php print $yySortPD?></option>
			<option value="6"<?php if($rsAdmin['sortOrder']==6) print ' selected="selected"'?>><?php print $yySortOA?></option>
			<option value="7"<?php if($rsAdmin['sortOrder']==7) print ' selected="selected"'?>><?php print $yySortOD?></option>
			<option value="8"<?php if($rsAdmin['sortOrder']==8) print ' selected="selected"'?>><?php print $yySortDA?></option>
			<option value="9"<?php if($rsAdmin['sortOrder']==9) print ' selected="selected"'?>><?php print $yySortDD?></option>
			<option value="10"<?php if($rsAdmin['sortOrder']==10) print ' selected="selected"'?>><?php print $yySortMa?></option>
			<option value="5"<?php if($rsAdmin['sortOrder']==5) print ' selected="selected"'?>><?php print $yySortNS?></option>
		  </select></td>
		</tr>
	</table>
	<h3 class="round_top half_top"><?php print $yyEmlSet?></h3>
	<table class="admin-table-b keeptable">
		  <tr>
			<th colspan="2"><?php print $yyLikeCE?></th>
		  </tr>
		  <tr>
			<td colspan="2">
				<div style="display:inline-block;width:20%"><input type="checkbox" name="emailconfirm" value="ON" <?php if(($rsAdmin['adminEmailConfirm'] & 1)==1) print 'checked="checked"'?> /> :<strong><?php print "New Order"?></strong></div>
				<div style="display:inline-block;width:20%"><input type="checkbox" name="affilconfirm" value="ON" <?php if(($rsAdmin['adminEmailConfirm'] & 2)==2) print 'checked="checked"'?> /> :<strong><?php print "New Affiliate"?></strong></div>
				<div style="display:inline-block;width:20%"><input type="checkbox" name="customerconfirm" value="ON" <?php if(($rsAdmin['adminEmailConfirm'] & 4)==4) print 'checked="checked"'?> /> :<strong><?php print "New Customer"?></strong></div>
				<div style="display:inline-block;width:20%"><input type="checkbox" name="reviewconfirm" value="ON" <?php if(($rsAdmin['adminEmailConfirm'] & 8)==8) print 'checked="checked"'?> /> :<strong><?php print "New Review"?></strong></div>
			</td>
		  </tr>
		  <tr>
			<td colspan="2"><?php print $yyCEAddr?></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyEmail?>:</strong></td>
			<td><input type="text" name="email" size="30" value="<?php print $rsAdmin['adminEmail']?>" /></td>
		  </tr>
	  </table>
<?php
	$prodfilter=array($rsAdmin['prodFilter'],$rsAdmin['sideFilter']);
	$filtertext=array(explode('&',$rsAdmin['prodFilterText']),explode('&',$rsAdmin['sideFilterText']));
	$filtertext2=array(explode('&',$rsAdmin['prodFilterText2']),explode('&',$rsAdmin['sideFilterText2']));
	$filtertext3=array(explode('&',$rsAdmin['prodFilterText3']),explode('&',$rsAdmin['sideFilterText3']));
	for($numfilters=0; $numfilters<=1; $numfilters++){
		for($index=0; $index<9; $index++){
			$filtertext[$numfilters][$index]=str_replace("%26","&",@$filtertext[$numfilters][$index]);
			$filtertext2[$numfilters][$index]=str_replace("%26","&",@$filtertext2[$numfilters][$index]);
			$filtertext3[$numfilters][$index]=str_replace("%26","&",@$filtertext3[$numfilters][$index]);
		}
	}
	$sortoptions=$rsAdmin['sortOptions'];
?>
<input type="hidden" name="prodfilterorder0" id="prodfilterorder0" value="<?php print $prodfilterorder?>" />
<input type="hidden" name="prodfilterorder1" id="prodfilterorder1" value="<?php print $sidefilterorder?>" />
<h3 class="round_top half_top"><?php print $yyPrFiBr?></h3>
	<table width="100%" cellspacing="2" cellpadding="2">
	  <tr>
<?php
	for($numfilters=0; $numfilters<=1; $numfilters++){ ?>
		<td width="50%">
	  <table class="admin-table-b keeptable" id="filtertable<?php print $numfilters?>">
		  <tr>
			<th colspan="3"><?php print ($numfilters==0?$yyFilSec:'Configuration for side filter bar (<a href="http://www.ecommercetemplates.com/proddetail.asp?prod=ECT-Side-Filter-Bar" style="color:#FF9966;font-weight:bold">IF INSTALLED</a>).')?></th>
		  </tr>
<?php	if($numfilters==0) $filterorderarray=explode(',',$prodfilterorder); else $filterorderarray=explode(',',$sidefilterorder);
		for($indexfilterorder=0; $indexfilterorder<count($filterorderarray); $indexfilterorder++){
			switch($filterorderarray[$indexfilterorder]){
			case 2: ?>
		  <tr id="filter<?php print $numfilters?>item2">
			<td width="19"><a href="#"><img src="adminimages/uparrow.png" alt="Move Up" onclick="return swapfilteritems(<?php print $numfilters?>,2)" /></a></td>
			<td><strong><?php print $yyFilScr?>: </strong></td>
			<td><input type="checkbox" name="filtercb<?php print $numfilters?>_1" value="ON" <?php if(($prodfilter[$numfilters] & 2)==2) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext<?php print $numfilters?>_1" size="20" maxlength="50" value="<?php print htmlspecials($filtertext[$numfilters][1])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext'.$numfilters.'_1x2" size="20" maxlength="50" value="'.htmlspecials($filtertext2[$numfilters][1]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext'.$numfilters.'_x3" size="20" maxlength="50" value="'.htmlspecials($filtertext3[$numfilters][1]).'" /> ';
				} ?></td>
		  </tr>
<?php		break;
			case 4 ?>
		  <tr id="filter<?php print $numfilters?>item4">
			<td width="19"><a href="#"><img src="adminimages/uparrow.png" alt="Move Up" onclick="return swapfilteritems(<?php print $numfilters?>,4)" /></a></td>
			<td><strong><?php print $yyFilPri?>: </strong></td>
			<td><input type="checkbox" name="filtercb<?php print $numfilters?>_2" value="ON" <?php if(($prodfilter[$numfilters] & 4)==4) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext<?php print $numfilters?>_2" size="20" maxlength="50" value="<?php print htmlspecials($filtertext[$numfilters][2])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext'.$numfilters.'_2x2" size="20" maxlength="50" value="'.htmlspecials($filtertext2[$numfilters][2]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext'.$numfilters.'_2x3" size="20" maxlength="50" value="'.htmlspecials($filtertext3[$numfilters][2]).'" /> ';
				} ?></td>
		  </tr>
<?php		break;
			case 8 ?>
		  <tr id="filter<?php print $numfilters?>item8">
			<td width="19"><a href="#"><img src="adminimages/uparrow.png" alt="Move Up" onclick="return swapfilteritems(<?php print $numfilters?>,8)" /></a></td>
			<td><strong><?php print $yyCusSor?>: </strong></td>
			<td><input type="checkbox" name="filtercb<?php print $numfilters?>_3" value="ON" <?php if(($prodfilter[$numfilters] & 8)==8) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext<?php print $numfilters?>_3" size="20" maxlength="50" value="<?php print htmlspecials($filtertext[$numfilters][3])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext'.$numfilters.'_3x2" size="20" maxlength="50" value="'.htmlspecials($filtertext2[$numfilters][3]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext'.$numfilters.'_3x3" size="20" maxlength="50" value="'.htmlspecials($filtertext3[$numfilters][3]).'" /> ';
				} ?></td>
		  </tr>
<?php		break;
			case 16 ?>
		  <tr id="filter<?php print $numfilters?>item16">
			<td width="19"><a href="#"><img src="adminimages/uparrow.png" alt="Move Up" onclick="return swapfilteritems(<?php print $numfilters?>,16)" /></a></td>
			<td><strong><?php print $yyProPag?>: </strong></td>
			<td><input type="checkbox" name="filtercb<?php print $numfilters?>_4" value="ON" <?php if(($prodfilter[$numfilters] & 16)==16) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext<?php print $numfilters?>_4" size="20" maxlength="50" value="<?php print htmlspecials($filtertext[$numfilters][4])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext'.$numfilters.'_4x2" size="20" maxlength="50" value="'.htmlspecials($filtertext2[$numfilters][4]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext'.$numfilters.'_4x3" size="20" maxlength="50" value="'.htmlspecials($filtertext3[$numfilters][4]).'" /> ';
				} ?></td>
		  </tr>
<?php		break;
			case 32 ?>
		  <tr id="filter<?php print $numfilters?>item32">
			<td width="19"><a href="#"><img src="adminimages/uparrow.png" alt="Move Up" onclick="return swapfilteritems(<?php print $numfilters?>,32)" /></a></td>
			<td><strong><?php print $yyFilKey?>: </strong></td>
			<td><input type="checkbox" name="filtercb<?php print $numfilters?>_5" value="ON" <?php if(($prodfilter[$numfilters] & 32)==32) print 'checked="checked" '?>/> <?php print $yyLabOpt?> <input type="text" name="filtertext<?php print $numfilters?>_5" size="20" maxlength="50" value="<?php print htmlspecials($filtertext[$numfilters][5])?>" />
		<?php	if(($adminlangsettings & 262144)==262144){
					if($adminlanguages>=1)	print '<input type="text" name="filtertext'.$numfilters.'_5x2" size="20" maxlength="50" value="'.htmlspecials($filtertext2[$numfilters][5]).'" /> ';
					if($adminlanguages>=2)	print '<input type="text" name="filtertext'.$numfilters.'_5x3" size="20" maxlength="50" value="'.htmlspecials($filtertext3[$numfilters][5]).'" /> ';
				} ?></td>
		  </tr>
<?php		}
		} ?>
		</table>
		  </td>
<?php
	} ?>
		</tr>
		<table>
		  <tr>
			<td width="19">&nbsp;</td>
			<td colspan="2">
			Options for Customer Defined Sort:
			</td>
		  </tr>
		  <tr>
			<td width="19">&nbsp;</td>
			<td colspan="2">
			<table width="100%"><tr>
			<td width="17%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid1" value="ON" <?php if(($sortoptions & pow(2,0))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortAl?></label></td>
			<td width="17%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid2" value="ON" <?php if(($sortoptions & pow(2,1))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortID?></label></td>
			<td width="17%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid3" value="ON" <?php if(($sortoptions & pow(2,2))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortPA?></label></td>
			<td width="17%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid6" value="ON" <?php if(($sortoptions & pow(2,5))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortOA?></label></td>
			<td width="17%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid8" value="ON" <?php if(($sortoptions & pow(2,7))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortDA?></label></td>
			<td width="15%" style="font-size:10px;border:0"><label><input type="checkbox" name="sortid5" value="ON" <?php if(($sortoptions & pow(2,4))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortNS?></label></td>
			</tr><tr>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid11" value="ON" <?php if(($sortoptions & pow(2,10))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortAl.' (Desc.)'?></label></td>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid12" value="ON" <?php if(($sortoptions & pow(2,11))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortID.' (Desc.)'?></label></td>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid4" value="ON" <?php if(($sortoptions & pow(2,3))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortPD?></label></td>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid7" value="ON" <?php if(($sortoptions & pow(2,6))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortOD?></label></td>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid9" value="ON" <?php if(($sortoptions & pow(2,8))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortDD?></label></td>
			<td style="font-size:10px;border:0"><label><input type="checkbox" name="sortid10" value="ON" <?php if(($sortoptions & pow(2,9))!=0) print 'checked="checked" '?>style="padding:0;margin:0;vertical-align:bottom;top:-1px;" /> <?php print $yySortMa?></label></td>
			</tr></table>
			</td>
		  </tr>
	  </table>
	<h3 class="round_top half_top"><?php print $yyShHaSe?></h3>
	<table class="admin-table-b keeptable">
		  <tr>
			<th colspan="2"><?php print $yySelShp?></th>
		  </tr>
		  <tr>
			<td><strong><?php print $yyShpTyp?>: </strong></td>
			<td><select name="shipping" size="1">
					<option value="0"><?php print $yyNoShp?></option>
					<option value="1" <?php if((int)($rsAdmin["adminShipping"])==1) print 'selected="selected"'?>><?php print $yyFlatShp?></option>
					<option value="2" <?php if((int)($rsAdmin["adminShipping"])==2) print 'selected="selected"'?>><?php print $yyWghtShp?></option>
					<option value="5" <?php if((int)($rsAdmin["adminShipping"])==5) print 'selected="selected"'?>><?php print $yyPriShp?></option>
					<option value="3" <?php if((int)($rsAdmin["adminShipping"])==3) print 'selected="selected"'?>><?php print $yyUSPS?></option>
					<option value="4" <?php if((int)($rsAdmin["adminShipping"])==4) print 'selected="selected"'?>><?php print $yyUPS?></option>
					<option value="6" <?php if((int)($rsAdmin["adminShipping"])==6) print 'selected="selected"'?>><?php print $yyCanPos?></option>
					<option value="7" <?php if((int)($rsAdmin["adminShipping"])==7) print 'selected="selected"'?>><?php print $yyFedex?></option>
					<option value="8" <?php if((int)($rsAdmin["adminShipping"])==8) print 'selected="selected"'?>>FedEx SmartPost</option>
					<option value="9" <?php if((int)($rsAdmin["adminShipping"])==9) print 'selected="selected"'?>><?php print $yyDHLShp?></option>
					<option value="10" <?php if((int)($rsAdmin["adminShipping"])==10) print 'selected="selected"'?>>Australia Post</option>
					</select></td>
		  </tr>
		  <tr>
			<td colspan="2"><?php print $yyWAltRa?></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyUseAlt?>: </strong></td>
			<td><select name="adminAltRates" size="1" onchange="showhidealtrates(this)">
				<option value="0"><?php print $yyNoAltR?></option>
				<option value="1"<?php if($rsAdmin['adminAltRates']==1) print 'selected="selected"'?>><?php print $yyAlRaMe?></option>
				<option value="2"<?php if($rsAdmin['adminAltRates']==2) print 'selected="selected"'?>><?php print $yyAlRaTo?></option>
				</select>
			</td>
		  </tr>
		  <tr id="altraterowtitle"<?php if($rsAdmin['adminAltRates']==0) print ' style="display:none"'?>>
			<td colspan="2"><?php print $yyAltSel?></td>
		  </tr>
		  <tr id="altraterow"<?php if($rsAdmin['adminAltRates']==0) print ' style="display:none"'?>>
			<td><strong><?php print $yyAltShp?>: </strong></td>
			<td>
			<input type="hidden" name="altrateids" id="altrateids" value="" />
			<input type="hidden" name="altrateuse" id="altrateuse" value="" />
			<input type="hidden" name="altrateuseintl" id="altrateuseintl" value="" />
			<input type="hidden" name="altratetext" id="altratetext" value="" />
			<input type="hidden" name="altratetext2" id="altratetext2" value="" />
			<input type="hidden" name="altratetext3" id="altratetext3" value="" />
			<table id="altshptable">
<?php			$index=1;
				$sSQL = "SELECT altrateid,altratename,altratetext,altratetext2,altratetext3,usealtmethod,usealtmethodintl FROM alternaterates ORDER BY (usealtmethod | usealtmethodintl) DESC,altrateorder,altrateid";
				$result=ect_query($sSQL) or ect_error();
				while($rs2=ect_fetch_assoc($result)){ ?>
				  <tr>
					<td>
					<input type="hidden" id="altrateids<?php print $index?>" value="<?php print $rs2['altrateid']?>" />
					<?php
						if($index==0){
							print '&nbsp;';
						}else{ ?>
					<a href="#"><img src="adminimages/uparrow.png" alt="Move Up" onclick="return swaptbrows(<?php print $index?>)" /></a>
<?php					} ?></td>
					<td><input type="checkbox" id="altrateuse<?php print $index?>" value="ON" <?php print ($rs2['usealtmethod']  ? 'checked="checked" ' : '')?>/></td>
					<td><input type="checkbox" id="altrateuseintl<?php print $index?>" value="ON" <?php print ($rs2['usealtmethodintl']  ? 'checked="checked" ' : '')?>/></td>
					<td><span id="methodname<?php print $index?>"><?php print $rs2['altratename'] ?></span></td>
					<td><input type="text" id="altratetext_<?php print $index?>" size="30" value="<?php print htmlspecials($rs2['altratetext']) ?>" /><br />
<?php					for($index2=2; $index2<=3; $index2++){
							if($index2<=($adminlanguages+1) && ($adminlangsettings & 65536)==65536){ ?>
					<input type="text" id="altratetext<?php print $index2?>_<?php print $index?>" size="30" value="<?php print htmlspecials($rs2['altratetext'.$index2]) ?>" /><br />
<?php						}else{ ?>
					<input type="hidden" id="altratetext<?php print $index2?>_<?php print $index?>" value="<?php print htmlspecials($rs2['altratetext'.$index2]) ?>" />
<?php						}
						} ?></td>
				  </tr>
<?php					$index++;
				}
				ect_free_result($result); ?>
				</table>
<script type="text/javascript">
<!--
function showhidealtrates(obj){
	if(obj.options[obj.selectedIndex].value=="0"){
		document.getElementById('altraterowtitle').style.display='none';
		document.getElementById('altraterow').style.display='none';
	}else{
		document.getElementById('altraterowtitle').style.display='';
		document.getElementById('altraterow').style.display='';
	}
}
function swaptbrows(rid){
	if(rid==1){
	}else{
		rid2=rid-1;
		var altrateids=document.getElementById("altrateids"+rid).value;
		var altrateuse=document.getElementById("altrateuse"+rid).checked;
		var altrateuseintl=document.getElementById("altrateuseintl"+rid).checked;
		var methodname=document.getElementById("methodname"+rid).innerHTML;
		var altratetext=document.getElementById("altratetext_"+rid).value;
		var altratetext2=document.getElementById("altratetext2_"+rid).value;
		var altratetext3=document.getElementById("altratetext3_"+rid).value;
		
		document.getElementById("altrateids"+rid).value=document.getElementById("altrateids"+rid2).value;
		document.getElementById("altrateuse"+rid).checked=document.getElementById("altrateuse"+rid2).checked;
		document.getElementById("altrateuseintl"+rid).checked=document.getElementById("altrateuseintl"+rid2).checked;
		document.getElementById("methodname"+rid).innerHTML=document.getElementById("methodname"+rid2).innerHTML;
		document.getElementById("altratetext_"+rid).value=document.getElementById("altratetext_"+rid2).value;
		document.getElementById("altratetext2_"+rid).value=document.getElementById("altratetext2_"+rid2).value;
		document.getElementById("altratetext3_"+rid).value=document.getElementById("altratetext3_"+rid2).value;
		
		document.getElementById("altrateids"+rid2).value=altrateids;
		document.getElementById("altrateuse"+rid2).checked=altrateuse;
		document.getElementById("altrateuseintl"+rid2).checked=altrateuseintl;
		document.getElementById("methodname"+rid2).innerHTML=methodname;
		document.getElementById("altratetext_"+rid2).value=altratetext;
		document.getElementById("altratetext2_"+rid2).value=altratetext2;
		document.getElementById("altratetext3_"+rid2).value=altratetext3;
	}
	return false;
}
//-->
</script>
			</td>
		  </tr>
		  <tr>
			<td colspan="2"><?php print $yySelShI?></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyShpTyp?>: </strong></td>
			<td><select name="intshipping" size="1">
					<option value="0"><?php print $yySamDom?></option>
					<option value="1" <?php if((int)($rsAdmin["adminIntShipping"])==1) print 'selected="selected"'?>><?php print $yyFlatShp?></option>
					<option value="2" <?php if((int)($rsAdmin["adminIntShipping"])==2) print 'selected="selected"'?>><?php print $yyWghtShp?></option>
					<option value="5" <?php if((int)($rsAdmin["adminIntShipping"])==5) print 'selected="selected"'?>><?php print $yyPriShp?></option>
					<option value="3" <?php if((int)($rsAdmin["adminIntShipping"])==3) print 'selected="selected"'?>><?php print $yyUSPS?></option>
					<option value="4" <?php if((int)($rsAdmin["adminIntShipping"])==4) print 'selected="selected"'?>><?php print $yyUPS?></option>
					<option value="6" <?php if((int)($rsAdmin["adminIntShipping"])==6) print 'selected="selected"'?>><?php print $yyCanPos?></option>
					<option value="7" <?php if((int)($rsAdmin["adminIntShipping"])==7) print 'selected="selected"'?>><?php print $yyFedex?></option>
					<option value="9" <?php if((int)($rsAdmin["adminIntShipping"])==9) print 'selected="selected"'?>><?php print $yyDHLShp?></option>
					<option value="10" <?php if((int)($rsAdmin["adminIntShipping"])==10) print 'selected="selected"'?>>Australia Post</option>
					</select></td>
		  </tr>
		  <tr>
			<td colspan="2"><?php print $yyHowPck?><br /><span style="font-size:10px"><?php print $yyOnlyAf?></span></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyPackPr?>: </strong></td>
			<td><select name="packing" size="1">
					<option value="0"><?php print $yyPckSep?></option>
					<option value="1" <?php if((int)($rsAdmin["adminPacking"])==1) print 'selected="selected"'?>><?php print $yyPckTog?></option>
					</select></td>
		  </tr>
		  <tr>
			<td colspan="2"><?php print $yyIfUSPS?><br />
				<span style="font-size:10px"><?php print $yyUPSForm?> <a href="adminupslicense.php"><?php print $yyHere?></a>.</span></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyUname?>: </strong></td>
			<td><input type="text" size="15" name="USPSUser" value="<?php print $rsAdmin['adminUSPSUser']?>" /></td>
		  </tr>
		   <tr>
			<td colspan="2"><?php print $yyEnMerI?></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyRetID?>: </strong></td>
			<td><input type="text" size="36" name="adminCanPostUser" value="<?php print $rsAdmin['adminCanPostUser']?>" /></td>
		  </tr>
		  <tr>
			<td colspan="2">If using FedEx SmartPost&reg; you need to enter your Hub ID here.</td>
		  </tr>
		  <tr>
			<td><strong>SmartPost Hub ID: </strong></td>
			<td><input type="text" size="15" name="smartPostHub" value="<?php print $rsAdmin['smartPostHub']?>" /></td>
		  </tr>
		  <tr>
			<td colspan="2"><?php print $yyEntZip?></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyZip?>: </strong></td>
			<td><input type="text" name="zipcode" size="10" value="<?php print $rsAdmin['adminZipCode']?>" /></td>
		  </tr>
		   <tr>
			<td colspan="2"><?php print $yyUPSUnt?></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyShpUnt?>: </strong><br /><br /><strong><?php print $yyDims?>: </strong></td>
			<td>  <select name="adminUnits" size="1">
					<option value="1" <?php if(((int)$rsAdmin["adminUnits"] & 3)==1) print 'selected="selected"'?>>LBS</option>
					<option value="0" <?php if(((int)$rsAdmin["adminUnits"] & 3)==0) print 'selected="selected"'?>>KGS</option>
					</select><br /><br />
				   <select name="adminDims" size="1">
					<option value="0"><?php print $yyNotSpe?></option>
					<option value="4" <?php if(((int)$rsAdmin["adminUnits"] & 12)==4) print 'selected="selected"'?>>IN</option>
					<option value="8" <?php if(((int)$rsAdmin["adminUnits"] & 12)==8) print 'selected="selected"'?>>CM</option>
					</select></td>
		  </tr>
		  <tr>
			<td colspan="2"><ul>
				  <li><span style="font-size:10px"><?php print $redasterix.$yyUntNote?></span></li>
				  <li><span style="font-size:10px"><?php print $redasterix.$yyUntNo2?></span></li></ul></td>
		  </tr>
		  <tr>
			<td colspan="2"><?php print $yyHandEx?></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyHanChg?>: </strong><br /><br /><strong><?php print $yyHanChg . ' (' . $yyPercen . ')'?>: </strong></td>
			<td><input type="text" name="handling" size="10" value="<?php print $rsAdmin['adminHandling']?>" /><br /><br /><input type="text" name="handlingpercent" size="10" style="text-align:right" value="<?php print $rsAdmin['adminHandlingPercent']?>" />%</td>
		  </tr>
	  </table>
	<h3 class="round_top half_top"><?php print $yyOrdMan?></h3>
	<table class="admin-table-b keeptable">
		  <tr>
			<th colspan="2"><?php print $yyStkMgt?></th>
		  </tr>
		  <tr>
			<td><strong><?php print $yyStock?>: </strong></td>
			<td><select name="stockManage" size="1">
					<option value="0"><?php print $yyNoStk?></option>
					<option value="1" <?php if((int)($rsAdmin['adminStockManage'])!=0) print 'selected="selected"'?>> &nbsp;&nbsp; <?php print $yyOn?></option>
					</select></td>
		  </tr>			  
		  <tr>
			<td colspan="2"><?php print $yyDelUnc?></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyDelAft?>:</strong></td>
			<td><select name="deleteUncompleted" size="1">
					<option value="0"><?php print $yyNever?></option>
					<option value="1" <?php if((int)($rsAdmin["adminDelUncompleted"])==1) print 'selected="selected"'?>>1 <?php print $yyDay?></option>
					<option value="2" <?php if((int)($rsAdmin["adminDelUncompleted"])==2) print 'selected="selected"'?>>2 <?php print $yyDays?></option>
					<option value="3" <?php if((int)($rsAdmin["adminDelUncompleted"])==3) print 'selected="selected"'?>>3 <?php print $yyDays?></option>
					<option value="4" <?php if((int)($rsAdmin["adminDelUncompleted"])==4) print 'selected="selected"'?>>4 <?php print $yyDays?></option>
					<option value="7" <?php if((int)($rsAdmin["adminDelUncompleted"])==7) print 'selected="selected"'?>>1 <?php print $yyWeek?></option>
					<option value="14" <?php if((int)($rsAdmin["adminDelUncompleted"])==14) print 'selected="selected"'?>>2 <?php print $yyWeeks?></option>
					</select><?php
			if(! (@$enableclientlogin==TRUE || @$forceclientlogin==TRUE)) writehiddenvar("adminClearCart",$rsAdmin['adminClearCart']) ?></td>
		  </tr>
<?php			if(@$enableclientlogin==TRUE || @$forceclientlogin==TRUE){ ?>
		  <tr>
			<td colspan="2"><?php print $yyRemLII?></td>
		  </tr>
		  <tr>
			<td><strong><?php print $yyDelAft?>:</strong></td>
			<td><select name="adminClearCart" size="1">
					<option value="0"><?php print $yyNever?></option>
					<option value="14" <?php if((int)$rsAdmin['adminClearCart']==14) print 'selected="selected"'?>>2 <?php print $yyWeek?></option>
					<option value="28" <?php if((int)$rsAdmin['adminClearCart']==28) print 'selected="selected"'?>>4 <?php print $yyWeek?></option>
					<option value="70" <?php if((int)$rsAdmin['adminClearCart']==70) print 'selected="selected"'?>>10 <?php print $yyWeek?></option>
					<option value="140" <?php if((int)$rsAdmin['adminClearCart']==140) print 'selected="selected"'?>>20 <?php print $yyWeek?></option>
					<option value="210" <?php if((int)$rsAdmin['adminClearCart']==210) print 'selected="selected"'?>>30 <?php print $yyWeek?></option>
					<option value="364" <?php if((int)$rsAdmin['adminClearCart']==364) print 'selected="selected"'?>>52 <?php print $yyWeek?></option>
					<option value="525" <?php if((int)$rsAdmin['adminClearCart']==525) print 'selected="selected"'?>>75 <?php print $yyWeek?></option>
					<option value="728" <?php if((int)$rsAdmin['adminClearCart']==728) print 'selected="selected"'?>>104 <?php print $yyWeek?></option>
					</select></td>
		  </tr>
<?php			} ?>
	  </table>
	<h3 class="round_top half_top"><?php print $yyLaSet?></h3>
	<table class="admin-table-b keeptable">
<tr><th colspan="2">Which languages do you wish to use</th></tr>
<tr>
<td><strong>Admin Language: </strong></td>
<td><select name="adminlang" size="1">
		<option value="">English</option>
		<option value="fr" <?php if($rsAdmin['adminlang']=='fr') print 'selected="selected"'?>>French / Fran&ccedil;ais</option>
		<option value="de" <?php if($rsAdmin['adminlang']=='de') print 'selected="selected"'?>>German / Deutsch</option>
		<option value="it" <?php if($rsAdmin['adminlang']=='it') print 'selected="selected"'?>>Italian / Italiano</option>
		<option value="nl" <?php if($rsAdmin['adminlang']=='nl') print 'selected="selected"'?>>Nederlands / Dutch</option>
		<option value="es" <?php if($rsAdmin['adminlang']=='es') print 'selected="selected"'?>>Spanish / Espa&ntilde;ol</option>
		</select></td>
</tr>
<tr>
<td><strong>Store Language: </strong></td>
<td><?php
		$storelang1=$storelang2=$storelang3='';
		$storelangarr=explode('|',$rsAdmin['storelang']);
		$storelang1=@$storelangarr[0];
		$storelang2=@$storelangarr[1];
		$storelang3=@$storelangarr[2];
		?><div><select name="storelang1" size="1">
		<option value="">English</option>
		<option value="dk" <?php if($storelang1=='dk') print 'selected="selected"'?>>Danish / Dansk</option>
		<option value="fr" <?php if($storelang1=='fr') print 'selected="selected"'?>>French / Fran&ccedil;ais</option>
		<option value="de" <?php if($storelang1=='de') print 'selected="selected"'?>>German / Deutsch</option>
		<option value="it" <?php if($storelang1=='it') print 'selected="selected"'?>>Italian / Italiano</option>
		<option value="nl" <?php if($storelang1=='nl') print 'selected="selected"'?>>Nederlands / Dutch</option>
		<option value="pt" <?php if($storelang1=='pt') print 'selected="selected"'?>>Portugese / Portugu&ecirc;s</option>
		<option value="es" <?php if($storelang1=='es') print 'selected="selected"'?>>Spanish / Espa&ntilde;ol</option>
		</select></div>
		<div id="storelang2"<?php if((int)$rsAdmin['adminlanguages']<1) print ' style="display:none"' ?>><select name="storelang2" size="1">
		<option value="">English</option>
		<option value="dk" <?php if($storelang2=='dk') print 'selected="selected"'?>>Danish / Dansk</option>
		<option value="fr" <?php if($storelang2=='fr') print 'selected="selected"'?>>French / Fran&ccedil;ais</option>
		<option value="de" <?php if($storelang2=='de') print 'selected="selected"'?>>German / Deutsch</option>
		<option value="it" <?php if($storelang2=='it') print 'selected="selected"'?>>Italian / Italiano</option>
		<option value="nl" <?php if($storelang2=='nl') print 'selected="selected"'?>>Nederlands / Dutch</option>
		<option value="pt" <?php if($storelang2=='pt') print 'selected="selected"'?>>Portugese / Portugu&ecirc;s</option>
		<option value="es" <?php if($storelang2=='es') print 'selected="selected"'?>>Spanish / Espa&ntilde;ol</option>
		</select></div>
		<div id="storelang3"<?php if((int)$rsAdmin['adminlanguages']<2) print ' style="display:none"' ?>><select name="storelang3" size="1">
		<option value="">English</option>
		<option value="dk" <?php if($storelang3=='dk') print 'selected="selected"'?>>Danish / Dansk</option>
		<option value="fr" <?php if($storelang3=='fr') print 'selected="selected"'?>>French / Fran&ccedil;ais</option>
		<option value="de" <?php if($storelang3=='de') print 'selected="selected"'?>>German / Deutsch</option>
		<option value="it" <?php if($storelang3=='it') print 'selected="selected"'?>>Italian / Italiano</option>
		<option value="nl" <?php if($storelang3=='nl') print 'selected="selected"'?>>Nederlands / Dutch</option>
		<option value="pt" <?php if($storelang3=='pt') print 'selected="selected"'?>>Portugese / Portugu&ecirc;s</option>
		<option value="es" <?php if($storelang3=='es') print 'selected="selected"'?>>Spanish / Espa&ntilde;ol</option>
		</select></div></td>
</tr>
<tr><td colspan="2"><?php print $yyHowLan?></td></tr>
<tr>
<td><strong><?php print $yyNumLan?>: </strong></td>
<td><select name="adminlanguages" size="1" onchange="document.getElementById('storelang3').style.display=this.selectedIndex<2?'none':'';document.getElementById('storelang2').style.display=this.selectedIndex<1?'none':'';">
					<option value="0">1</option>
					<option value="1" <?php if((int)($rsAdmin["adminlanguages"])==1) print 'selected="selected"'?>>2</option>
					<option value="2" <?php if((int)($rsAdmin["adminlanguages"])==2) print 'selected="selected"'?>>3</option>
					</select></td>
</tr>
<tr>
<td colspan="2"><?php print $yyWhMull?><br />
<span style="font-size:10px"><?php print $yyLonrel?></span></td>
</tr>
<tr>
<td><strong><?php print $yyLaSet?>: </strong></td>
<td><select name="adminlangsettings[]" size="5" multiple="multiple">
					<option value="1" <?php if(((int)$rsAdmin['adminlangsettings'] & 1)==1) print 'selected="selected"'?>><?php print $yyPrName?></option>
					<option value="2" <?php if(((int)$rsAdmin['adminlangsettings'] & 2)==2) print 'selected="selected"'?>><?php print $yyDesc?></option>
					<option value="4" <?php if(((int)$rsAdmin['adminlangsettings'] & 4)==4) print 'selected="selected"'?>><?php print $yyLnDesc?></option>
					<option value="1048576" <?php if(((int)$rsAdmin['adminlangsettings'] & 1048576)==1048576) print 'selected="selected"'?>><?php print $yyStaNam?></option>
					<option value="8" <?php if(((int)$rsAdmin['adminlangsettings'] & 8)==8) print 'selected="selected"'?>><?php print $yyCntNam?></option>
					<option value="16" <?php if(((int)$rsAdmin['adminlangsettings'] & 16)==16) print 'selected="selected"'?>><?php print $yyPOName?></option>
					<option value="32" <?php if(((int)$rsAdmin['adminlangsettings'] & 32)==32) print 'selected="selected"'?>><?php print $yyPOChoi?></option>
					<option value="64" <?php if(((int)$rsAdmin['adminlangsettings'] & 64)==64) print 'selected="selected"'?>><?php print $yyOrdSta?></option>
					<option value="128" <?php if(((int)$rsAdmin['adminlangsettings'] & 128)==128) print 'selected="selected"'?>><?php print $yyPayMet?></option>
					<option value="256" <?php if(((int)$rsAdmin['adminlangsettings'] & 256)==256) print 'selected="selected"'?>><?php print $yyCatNam?></option>
					<option value="512" <?php if(((int)$rsAdmin['adminlangsettings'] & 512)==512) print 'selected="selected"'?>><?php print $yyCatDes?></option>
					<option value="524288" <?php if(((int)$rsAdmin['adminlangsettings'] & 524288)==524288) print 'selected="selected"'?>>Category Header</option>
					<option value="1024" <?php if(((int)$rsAdmin['adminlangsettings'] & 1024)==1024) print 'selected="selected"'?>><?php print $yyDisTxt?></option>
					<option value="2048" <?php if(((int)$rsAdmin['adminlangsettings'] & 2048)==2048) print 'selected="selected"'?>><?php print $yyCatURL?></option>
					<option value="4096" <?php if(((int)$rsAdmin['adminlangsettings'] & 4096)==4096) print 'selected="selected"'?>><?php print $yyEmlHdr?></option>
					<option value="8192" <?php if(((int)$rsAdmin['adminlangsettings'] & 8192)==8192) print 'selected="selected"'?>><?php print $yyManURL?></option>
					<option value="16384" <?php if(((int)$rsAdmin['adminlangsettings'] & 16384)==16384) print 'selected="selected"'?>><?php print $yyManDsc?></option>
					<option value="32768" <?php if(((int)$rsAdmin['adminlangsettings'] & 32768)==32768) print 'selected="selected"'?>><?php print $yyContReg?></option>
					<option value="65536" <?php if(((int)$rsAdmin['adminlangsettings'] & 65536)==65536) print 'selected="selected"'?>><?php print $yyAltShM?></option>
					<option value="131072" <?php if(((int)$rsAdmin['adminlangsettings'] & 131072)==131072) print 'selected="selected"'?>><?php print $yySeaCri?></option>
					<option value="262144" <?php if(((int)$rsAdmin['adminlangsettings'] & 262144)==262144) print 'selected="selected"'?>>Filter Bar</option>
					</select></td>
</tr>
</table>

<h3 class="round_top half_top"><?php print 'Cardinal Commerce'?></h3>
<table class="admin-table-b keeptable">
	<tr>
	<th colspan="2"><?php print $yyCaCoAc?></th>
	</tr>
	<tr>
	<td><strong><?php print "Cardinal Processor ID"?>: </strong></td>
	<td><input type="text" name="cardinalprocessor" size="30" value="<?php print htmlspecials($rsAdmin['cardinalProcessor'])?>" /></td>
	</tr>
	<tr>
	<td><strong><?php print "Cardinal Merchant ID"?>: </strong></td>
	<td><input type="text" name="cardinalmerchant" size="30" value="<?php print htmlspecials($rsAdmin['cardinalMerchant'])?>" /></td>
	</tr>
	<tr>
	<td><strong><?php print "Cardinal Transaction Password"?>: </strong></td>
	<td><input type="text" name="cardinalpwd" size="30" value="<?php print htmlspecials($rsAdmin['cardinalPwd'])?>" /></td>
	</tr>
</table>

	<h3 class="round_top half_top"><?php print $yyCurenc?></h3>
	<table class="admin-table-b keeptable">
<tr>
<th colspan="2"><?php print $yy3CurCon?><br />
<span style="font-size:10px"><?php print $yyNo3Con?></span></th>
</tr>
<tr>
<td><strong><?php print $yyConv?> 1: </strong></td>
<td>&nbsp;<?php print $yyRate?> <input type="text" name="currRate1" size="10" value="<?php if($rsAdmin["currRate1"] != 0) print $rsAdmin["currRate1"]?>" />&nbsp;&nbsp;&nbsp;Symbol <select name="currSymbol1" size="1"><option value="">None</option>
  <?php	for($index=0; $index<$numcurrencies; $index++){
							print "<option value='" . $allcurrencies[$index]['countryCurrency'] . "'";
							if($rsAdmin["currSymbol1"]==$allcurrencies[$index]['countryCurrency']) print ' selected="selected"';
							print ">" . $allcurrencies[$index]['countryCurrency'] . "</option>\n";
						} ?></select></td>
</tr>
<tr>
<td><strong><?php print $yyConv?> 2: </strong></td>
<td>&nbsp;<?php print $yyRate?> <input type="text" name="currRate2" size="10" value="<?php if($rsAdmin["currRate2"] != 0) print $rsAdmin["currRate2"]?>" />&nbsp;&nbsp;&nbsp;Symbol <select name="currSymbol2" size="1"><option value="">None</option>
  <?php	for($index=0; $index<$numcurrencies; $index++){
							print "<option value='" . $allcurrencies[$index]['countryCurrency'] . "'";
							if($rsAdmin["currSymbol2"]==$allcurrencies[$index]['countryCurrency']) print ' selected="selected"';
							print ">" . $allcurrencies[$index]['countryCurrency'] . "</option>\n";
						} ?></select></td>
</tr>
<tr>
  <td><strong><?php print $yyConv?> 3: </strong></td>
  <td>&nbsp;<?php print $yyRate?> <input type="text" name="currRate3" size="10" value="<?php if($rsAdmin["currRate3"] != 0) print $rsAdmin["currRate3"]?>" />&nbsp;&nbsp;&nbsp;Symbol <select name="currSymbol3" size="1"><option value="">None</option>
  <?php	for($index=0; $index<$numcurrencies; $index++){
							print "<option value='" . $allcurrencies[$index]['countryCurrency'] . "'";
							if($rsAdmin["currSymbol3"]==$allcurrencies[$index]['countryCurrency']) print ' selected="selected"';
							print ">" . $allcurrencies[$index]['countryCurrency'] . "</option>\n";
						} ?></select></td>
</tr>
<tr>
<td colspan="2"><font size="1"><?php print $yyAutoLogin?></td>
</tr>
<tr>
<td><strong><?php print $yyUname?>: </strong><br /><br /><strong><?php print $yyPass?>: </strong></td>
<td><input type="text" name="currConvUser" size="15" value="<?php print $rsAdmin['currConvUser']?>" /><br /><br /><input type="text" name="currConvPw" size="15" value="<?php print $rsAdmin['currConvPw']?>" /></td>
</tr>
</table>
<div align="center"><input type="submit" value="Submit" />&nbsp; &nbsp;<input type="reset" value="Reset" /><br />&nbsp;</div>
</form>
<?php } ?>
