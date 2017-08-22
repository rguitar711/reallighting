<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
$sSQL = "";
$alreadygotadmin = getadminsettings();
if(@$maxloginlevels=='') $maxloginlevels=5;
$dorefresh=FALSE;
if(getpost('posted')=='1'){
	if(getpost('act')=='delete'){
		$sSQL = 'DELETE FROM cpnassign WHERE cpaCpnID=' . getpost('id');
		ect_query($sSQL) or ect_error();
		$sSQL = 'DELETE FROM coupons WHERE cpnID=' . getpost('id');
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='domodify'){
		$sSQL = "UPDATE coupons SET cpnName='" . escape_string(getpost('cpnName')) . "'";
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1024)==1024) $sSQL.=",cpnName" . $index . "='" . escape_string(getpost('cpnName' . $index)) . "'";
			}
			if(getpost('cpnWorkingName')!='')
				$sSQL.=",cpnWorkingName='" . escape_string(getpost('cpnWorkingName')) . "'";
			else
				$sSQL.=",cpnWorkingName='" . escape_string(getpost('cpnName')) . "'";
			if(getpost('cpnIsCoupon')=='0')
				$sSQL.=",cpnNumber='',";
			else
				$sSQL.=",cpnNumber='" . escape_string(getpost('cpnNumber')) . "',";
			$sSQL.='cpnType=' . getpost('cpnType') . ',';
			if(getpost('cpnEndDate')=='Expired')
				$sSQL.="cpnEndDate='" . date('Y-m-d',time()-(30*60*60*24)) . "',";
			elseif(is_numeric(getpost('cpnEndDate')))
				$sSQL.="cpnEndDate='" . date('Y-m-d',time()+((int)getpost('cpnEndDate')*60*60*24)) . "',";
			else
				$sSQL.="cpnEndDate='3000-01-01',";
			if(is_numeric(getpost('cpnDiscount')) && getpost('cpnType') != '0')
				$sSQL.='cpnDiscount=' . getpost('cpnDiscount') . ',';
			else
				$sSQL.='cpnDiscount=0,';
			if(is_numeric(getpost('cpnThreshold')))
				$sSQL.='cpnThreshold=' . getpost('cpnThreshold') . ',';
			else
				$sSQL.='cpnThreshold=0,';
			if(is_numeric(getpost('cpnThresholdMax')))
				$sSQL.='cpnThresholdMax=' . getpost('cpnThresholdMax') . ',';
			else
				$sSQL.='cpnThresholdMax=0,';
			if(is_numeric(getpost('cpnThresholdRepeat')))
				$sSQL.='cpnThresholdRepeat=' . getpost('cpnThresholdRepeat') . ',';
			else
				$sSQL.='cpnThresholdRepeat=0,';
			if(is_numeric(getpost('cpnQuantity')))
				$sSQL.='cpnQuantity=' . getpost('cpnQuantity') . ',';
			else
				$sSQL.='cpnQuantity=0,';
			if(is_numeric(getpost('cpnQuantityMax')))
				$sSQL.='cpnQuantityMax=' . getpost('cpnQuantityMax') . ',';
			else
				$sSQL.='cpnQuantityMax=0,';
			if(is_numeric(getpost('cpnQuantityRepeat')))
				$sSQL.='cpnQuantityRepeat=' . getpost('cpnQuantityRepeat') . ',';
			else
				$sSQL.='cpnQuantityRepeat=0,';
			if(getpost('cpnNumAvail')!='' && is_numeric(getpost('cpnNumAvail')))
				$sSQL.='cpnNumAvail=' . getpost('cpnNumAvail') . ',';
			else
				$sSQL.='cpnNumAvail=30000000,';
			if(getpost('cpnType')=='0')
				$sSQL.='cpnCntry=' . getpost('cpnCntry') . ',';
			else
				$sSQL.='cpnCntry=0,';
			$cpnLoginLevel=(int)getpost('cpnLoginLevel');
			if(getpost('cpnLoginLt')=='1') $cpnLoginLevel=-1-$cpnLoginLevel;
			$sSQL.='cpnLoginLevel='.$cpnLoginLevel.',';
			if(is_numeric(getpost('cpnHandling'))) $sSQL.='cpnHandling=' . getpost('cpnHandling') . ',';
			$sSQL.='cpnIsCoupon=' . getpost('cpnIsCoupon') . ',';
			if(getpost('cpnType')=='0')
				$sSQL.='cpnSitewide=1';
			else
				$sSQL.='cpnSitewide=' . getpost('cpnSitewide');
			$sSQL.=' WHERE cpnID=' . getpost('id');
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='doaddnew'){
		$sSQL = 'INSERT INTO coupons (cpnName';
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1024)==1024) $sSQL.=',cpnName' . $index;
			}
			$sSQL.=",cpnWorkingName,cpnNumber,cpnType,cpnEndDate,cpnDiscount,cpnThreshold,cpnThresholdMax,cpnThresholdRepeat,cpnQuantity,cpnQuantityMax,cpnQuantityRepeat,cpnNumAvail,cpnCntry,cpnLoginLevel,cpnHandling,cpnIsCoupon,cpnSitewide) VALUES (";
			$sSQL.="'" . escape_string(getpost('cpnName')) . "',";
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1024)==1024) $sSQL.="'" . escape_string(getpost('cpnName' . $index)) . "',";
			}
			if(getpost('cpnWorkingName')!='')
				$sSQL.="'" . escape_string(getpost('cpnWorkingName')) . "',";
			else
				$sSQL.="'" . escape_string(getpost('cpnName')) . "',";
			if(getpost('cpnIsCoupon')=='0')
				$sSQL.="'',";
			else
				$sSQL.="'" . escape_string(getpost('cpnNumber')) . "',";
			$sSQL.=getpost('cpnType') . ',';
			if(getpost('cpnEndDate')=='Expired')
				$sSQL.="'" . date('Y-m-d',time()-(30*60*60*24)) . "',";
			elseif(is_numeric(getpost('cpnEndDate')))
				$sSQL.="'" . date('Y-m-d',time()+((int)getpost('cpnEndDate')*60*60*24)) . "',";
			else
				$sSQL.="'3000-01-01',";
			if(is_numeric(getpost('cpnDiscount')) && getpost('cpnType') != '0')
				$sSQL.=getpost('cpnDiscount') . ',';
			else
				$sSQL.='0,';
			if(is_numeric(getpost('cpnThreshold')))
				$sSQL.=getpost('cpnThreshold') . ',';
			else
				$sSQL.='0,';
			if(is_numeric(getpost('cpnThresholdMax')))
				$sSQL.=getpost('cpnThresholdMax') . ',';
			else
				$sSQL.='0,';
			if(is_numeric(getpost('cpnThresholdRepeat')))
				$sSQL.=getpost('cpnThresholdRepeat') . ',';
			else
				$sSQL.='0,';
			if(is_numeric(getpost('cpnQuantity')))
				$sSQL.=getpost('cpnQuantity') . ',';
			else
				$sSQL.='0,';
			if(is_numeric(getpost('cpnQuantityMax')))
				$sSQL.=getpost('cpnQuantityMax') . ',';
			else
				$sSQL.='0,';
			if(is_numeric(getpost('cpnQuantityRepeat')))
				$sSQL.=getpost('cpnQuantityRepeat') . ',';
			else
				$sSQL.='0,';
			if(getpost('cpnNumAvail')!='' && is_numeric(getpost('cpnNumAvail')))
				$sSQL.=getpost('cpnNumAvail') . ',';
			else
				$sSQL.='30000000,';
			if(getpost('cpnType')=='0')
				$sSQL.=getpost('cpnCntry') . ',';
			else
				$sSQL.='0,';
			$cpnLoginLevel=(int)getpost('cpnLoginLevel');
			if(getpost('cpnLoginLt')=='1') $cpnLoginLevel=-1-$cpnLoginLevel;
			$sSQL.=$cpnLoginLevel.',';
			if(is_numeric(getpost('cpnHandling'))) $sSQL.=getpost('cpnHandling') . ','; else $sSQL.='0,';
			$sSQL.=getpost('cpnIsCoupon') . ',';
			if(getpost('cpnType')=='0')
				$sSQL.='1)';
			else
				$sSQL.=getpost('cpnSitewide') . ')';
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}
	if($dorefresh)
		print '<meta http-equiv="refresh" content="1; url=admindiscounts.php?stext='.urlencode(@$_REQUEST['stext']).'&stype='.@$_REQUEST['stype'].'&scpds='.@$_REQUEST['scpds'].'&sefct='.@$_REQUEST['sefct'].'&sort='.@$_REQUEST['sort'].'&pg='.@$_REQUEST['pg'].'" />';
}
?>
<script type="text/javascript">
<!--
var savebg, savebc, savecol;
function formvalidator(theForm){
  if(theForm.cpnName.value==""){
    alert("<?php print jscheck($yyPlsEntr . ' "' . $yyDisTxt)?>\".");
    theForm.cpnName.focus();
    return (false);
  }
  if(theForm.cpnName.value.length > 255){
    alert("<?php print jscheck($yyMax255 . ' "' . $yyDisTxt)?>\".");
    theForm.cpnName.focus();
    return (false);
  }
  if(theForm.cpnType.selectedIndex!=0){
	if(theForm.cpnDiscount.value==""){
	  alert("<?php print jscheck($yyPlsEntr . ' "' . $yyDscAmt)?>\".");
	  theForm.cpnDiscount.focus();
	  return (false);
	}
	if(theForm.cpnType.selectedIndex==2){
	  if(theForm.cpnDiscount.value < 0 || theForm.cpnDiscount.value > 100){
		alert("<?php print jscheck($yyNum100 . ' "' . $yyDscAmt)?>\".");
		theForm.cpnDiscount.focus();
		return (false);
	  }
	}
  }
  if(theForm.cpnIsCoupon.selectedIndex==1){
	if(theForm.cpnNumber.value==""){
	  alert("<?php print jscheck($yyPlsEntr . ' "' . $yyCpnCod)?>\".");
	  theForm.cpnNumber.focus();
	  return (false);
	}
	var regex=/^[0-9A-Za-z\_\-]+$/;
	if (!regex.test(theForm.cpnNumber.value)){
		alert("<?php print jscheck($yyAlpha2 . ' "' . $yyCpnCod)?>\".");
		theForm.cpnNumber.focus();
		return (false);
	}
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnNumAvail.value)){
	alert("<?php print jscheck($yyOnlyNum . ' "' . $yyNumAvl)?>\".");
	theForm.cpnNumAvail.focus();
	return (false);
  }
  if(theForm.cpnNumAvail.value!='' && theForm.cpnNumAvail.value > 1000000){
    alert("<?php print jscheck($yyNumMil . ' "' . $yyNumAvl)?>\"<?php print jscheck($yyOrBlank)?>");
    theForm.cpnNumAvail.focus();
    return (false);
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnEndDate.value)&&theForm.cpnEndDate.value!="Expired"){
	alert("<?php print jscheck($yyOnlyNum . ' "' . $yyDaysAv)?>\".");
	theForm.cpnEndDate.focus();
	return (false);
  }
  var regex=/^[0-9\.]*$/;
  if (!regex.test(theForm.cpnThreshold.value)){
	alert("<?php print jscheck($yyOnlyDec . ' "' . $yyMinPur)?>\".");
	theForm.cpnThreshold.focus();
	return (false);
  }
  var regex=/^[0-9\.]*$/;
  if (!regex.test(theForm.cpnThresholdRepeat.value)){
	alert("<?php print jscheck($yyOnlyDec . ' "' . $yyRepEvy)?>\".");
	theForm.cpnThresholdRepeat.focus();
	return (false);
  }
  var regex=/^[0-9\.]*$/;
  if (!regex.test(theForm.cpnThresholdMax.value)){
	alert("<?php print jscheck($yyOnlyDec . ' "' . $yyMaxPur)?>\".");
	theForm.cpnThresholdMax.focus();
	return (false);
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnQuantity.value)){
	alert("<?php print jscheck($yyOnlyNum . ' "' . $yyMinQua)?>\".");
	theForm.cpnQuantity.focus();
	return (false);
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnQuantityRepeat.value)){
	alert("<?php print jscheck($yyOnlyNum . ' "' . $yyRepEvy)?>\".");
	theForm.cpnQuantityRepeat.focus();
	return (false);
  }
  var regex=/^[0-9]*$/;
  if (!regex.test(theForm.cpnQuantityMax.value)){
	alert("<?php print jscheck($yyOnlyNum . ' "' . $yyMaxQua)?>\".");
	theForm.cpnQuantityMax.focus();
	return (false);
  }
  var regex=/^[0-9\.]*$/;
  if (!regex.test(theForm.cpnDiscount.value)){
	alert("<?php print jscheck($yyOnlyDec . ' "' . $yyDscAmt)?>\".");
	theForm.cpnDiscount.focus();
	return (false);
  }
  document.mainform.cpnNumber.disabled=false;
  document.mainform.cpnDiscount.disabled=false;
  document.mainform.cpnCntry.disabled=false;
  document.mainform.cpnHandling.disabled=false;
  document.mainform.cpnSitewide.disabled=false;
  document.mainform.cpnThresholdRepeat.disabled=false;
  document.mainform.cpnQuantityRepeat.disabled=false;
  return (true);
}
function couponcodeactive(forceactive){
	if(document.mainform.cpnIsCoupon.selectedIndex==0){
		document.mainform.cpnNumber.style.backgroundColor="#DDDDDD";
		document.mainform.cpnNumber.disabled=true;
	}
	else if(document.mainform.cpnIsCoupon.selectedIndex==1){
		document.mainform.cpnNumber.style.backgroundColor=savebg;
		document.mainform.cpnNumber.disabled=false;
	}
}
function changecouponeffect(forceactive){
	if(document.mainform.cpnType.selectedIndex==0){
		document.mainform.cpnDiscount.style.backgroundColor="#DDDDDD";
		document.mainform.cpnDiscount.disabled=true;

		document.mainform.cpnCntry.style.backgroundColor=savebg;
		document.mainform.cpnCntry.disabled=false;
		
		document.mainform.cpnHandling.style.backgroundColor=savebg;
		document.mainform.cpnHandling.disabled=false;

		document.mainform.cpnSitewide.style.backgroundColor="#DDDDDD";
		document.mainform.cpnSitewide.disabled=true;
	}else{
		document.mainform.cpnDiscount.style.backgroundColor=savebg;
		document.mainform.cpnDiscount.disabled=false;

		document.mainform.cpnCntry.style.backgroundColor="#DDDDDD";
		document.mainform.cpnCntry.disabled=true;
		
		document.mainform.cpnHandling.style.backgroundColor="#DDDDDD";
		document.mainform.cpnHandling.disabled=true;

		document.mainform.cpnSitewide.style.backgroundColor=savebg;
		document.mainform.cpnSitewide.disabled=false;
	}
	if(document.mainform.cpnType.selectedIndex==1){
		document.mainform.cpnThresholdRepeat.style.backgroundColor=savebg;
		document.mainform.cpnThresholdRepeat.disabled=false;

		document.mainform.cpnQuantityRepeat.style.backgroundColor=savebg;
		document.mainform.cpnQuantityRepeat.disabled=false;
	}else{
		document.mainform.cpnThresholdRepeat.style.backgroundColor="#DDDDDD";
		document.mainform.cpnThresholdRepeat.disabled=true;

		document.mainform.cpnQuantityRepeat.style.backgroundColor="#DDDDDD";
		document.mainform.cpnQuantityRepeat.disabled=true;
	}
}
function setloglev(isequal){
var tobj=document.getElementById('cpnLoginLevel');
if(isequal.selectedIndex==0)
	tobj[0].text="<?php print $yyNoRes?>";
else
	tobj[0].text="<?php print $yyLiLev . ' 0'?>";
}
//-->
</script>
<?php
if(getpost('posted')=='1' && (getpost('act')=='modify' || getpost('act')=='clone' || getpost('act')=='addnew')){
	if((getpost('act')=='modify' || getpost('act')=='clone') && is_numeric(getpost('id'))){
		$sSQL = "SELECT cpnName,cpnName2,cpnName3,cpnWorkingName,cpnNumber,cpnType,cpnEndDate,cpnDiscount,cpnThreshold,cpnThresholdMax,cpnThresholdRepeat,cpnQuantity,cpnQuantityMax,cpnQuantityRepeat,cpnNumAvail,cpnCntry,cpnIsCoupon,cpnSitewide,cpnHandling,cpnLoginLevel FROM coupons WHERE cpnID=" . getpost('id');
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$cpnName = $rs['cpnName'];
		for($index=2; $index <= $adminlanguages+1; $index++)
			$cpnNames[$index] = $rs['cpnName' . $index];
		$cpnWorkingName = $rs['cpnWorkingName'];
		$cpnNumber = $rs['cpnNumber'];
		$cpnType = $rs['cpnType'];
		$cpnEndDate = $rs['cpnEndDate'];
		$cpnDiscount = $rs['cpnDiscount'];
		$cpnThreshold = $rs['cpnThreshold'];
		$cpnThresholdMax = $rs['cpnThresholdMax'];
		$cpnThresholdRepeat = $rs['cpnThresholdRepeat'];
		$cpnQuantity = $rs['cpnQuantity'];
		$cpnQuantityMax = $rs['cpnQuantityMax'];
		$cpnQuantityRepeat = $rs['cpnQuantityRepeat'];
		$cpnNumAvail = $rs['cpnNumAvail'];
		$cpnCntry = $rs['cpnCntry'];
		$cpnIsCoupon = $rs['cpnIsCoupon'];
		$cpnSitewide = $rs['cpnSitewide'];
		$cpnHandling = $rs['cpnHandling'];
		$cpnLoginLevel = $rs['cpnLoginLevel'];
		$cpnLoginLt = ($cpnLoginLevel<0);
		$cpnLoginLevel = abs($cpnLoginLevel);
		ect_free_result($result);
	}else{
		$cpnName = '';
		for($index=2; $index <= $adminlanguages+1; $index++)
			$cpnNames[$index] = '';
		$cpnWorkingName = '';
		$cpnNumber = '';
		$cpnType = 0;
		$cpnEndDate = '3000-01-01 00:00:00';
		$cpnDiscount = '';
		$cpnThreshold = 0;
		$cpnThresholdMax = 0;
		$cpnThresholdRepeat = 0;
		$cpnQuantity = 0;
		$cpnQuantityMax = 0;
		$cpnQuantityRepeat = 0;
		$cpnNumAvail = 30000000;
		$cpnCntry = 0;
		$cpnIsCoupon = 0;
		$cpnSitewide = 0;
		$cpnHandling = 0;
		$cpnLoginLevel = 0;
		$cpnLoginLt = FALSE;
	}
?>
		  <form name="mainform" method="post" action="admindiscounts.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
		<?php	if(getpost('act')=="modify" && is_numeric(getpost('id'))){ ?>
			<input type="hidden" name="act" value="domodify" />
			<input type="hidden" name="id" value="<?php print getpost('id')?>" />
		<?php	}else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
		<?php	}
			writehiddenvar('scpds', getpost('scpds'));
			writehiddenvar('sefct', getpost('sefct'));
			writehiddenvar('stext', getpost('stext'));
			writehiddenvar('sort', getpost('sort'));
			writehiddenvar('stype', getpost('stype'));
			writehiddenvar('pg', getpost('pg'));?>
            <table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyDscNew?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyCpnDsc?>:</strong></td>
				<td width="60%"><select name="cpnIsCoupon" size="1" onchange="couponcodeactive(false);">
					<option value="0"><?php print $yyDisco?></option>
					<option value="1" <?php if((int)$cpnIsCoupon==1) print 'selected="selected"' ?>><?php print $yyCoupon?></option>
					</select></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDscEff?>:</strong></td>
				<td width="60%"><select name="cpnType" size="1" onchange="changecouponeffect(false);">
					<option value="0"><?php print $yyFrSShp?></option>
					<option value="1" <?php if((int)$cpnType==1) print 'selected="selected"' ?>><?php print $yyFlatDs?></option>
					<option value="2" <?php if((int)$cpnType==2) print 'selected="selected"' ?>><?php print $yyPerDis?></option>
					</select></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDisTxt?>:</strong></td>
				<td width="60%"><input type="text" name="cpnName" size="30" value="<?php print htmlspecials($cpnName)?>" /></td>
			  </tr>
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1024)==1024){ ?>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDisTxt . " " . $index?>:</strong></td>
				<td width="60%"><input type="text" name="cpnName<?php print $index?>" size="30" value="<?php print htmlspecials($cpnNames[$index])?>" /></td>
			  </tr>
<?php			}
			} ?>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyWrkNam?>:</strong></td>
				<td width="60%"><input type="text" name="cpnWorkingName" size="30" value="<?php print htmlspecials($cpnWorkingName)?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyCpnCod?>:</strong></td>
				<td width="60%"><input type="text" name="cpnNumber" size="30" value="<?php print $cpnNumber?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyNumAvl?>:</strong></td>
				<td width="60%"><input type="text" name="cpnNumAvail" size="10" value="<?php if((int)$cpnNumAvail != 30000000) print $cpnNumAvail?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDaysAv?>:</strong></td>
				<td width="60%"><input type="text" name="cpnEndDate" size="10" value="<?php
				if($cpnEndDate != '3000-01-01 00:00:00')
					if(strtotime($cpnEndDate)-strtotime(date('Y-m-d')) < 0) print "Expired"; else print floor((strtotime($cpnEndDate)-time())/(60*60*24))+1; ?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyMinPur?>:</strong></td>
				<td width="60%"><input type="text" name="cpnThreshold" size="10" value="<?php if((int)$cpnThreshold>0) print $cpnThreshold?>" /> <strong><?php print $yyRepEvy?>:</strong> <input type="text" name="cpnThresholdRepeat" size="10" value="<?php if((int)$cpnThresholdRepeat > 0) print $cpnThresholdRepeat?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyMaxPur?>:</strong></td>
				<td width="60%"><input type="text" name="cpnThresholdMax" size="10" value="<?php if((int)$cpnThresholdMax>0) print $cpnThresholdMax?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyMinQua?>:</strong></td>
				<td width="60%"><input type="text" name="cpnQuantity" size="10" value="<?php if((int)$cpnQuantity>0) print $cpnQuantity?>" /> <strong><?php print $yyRepEvy?>:</strong> <input type="text" name="cpnQuantityRepeat" size="10" value="<?php if((int)$cpnQuantityRepeat > 0) print $cpnQuantityRepeat?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyMaxQua?>:</strong></td>
				<td width="60%"><input type="text" name="cpnQuantityMax" size="10" value="<?php if((int)$cpnQuantityMax>0) print $cpnQuantityMax?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyDscAmt?>:</strong></td>
				<td width="60%"><input type="text" name="cpnDiscount" size="10" value="<?php print $cpnDiscount?>" /></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyScope?>:</strong></td>
				<td width="60%"><select name="cpnSitewide" size="1">
					<option value="0"><?php print $yyIndCat?></option>
					<option value="3" <?php if((int)$cpnSitewide==3) print 'selected="selected"' ?>><?php print $yyDsCaTo?></option>
					<option value="2" <?php if((int)$cpnSitewide==2) print 'selected="selected"' ?>><?php print $yyGlInPr?></option>
					<option value="1" <?php if((int)$cpnSitewide==1) print 'selected="selected"' ?>><?php print $yyGlPrTo?></option>
					</select></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyAplHan?>:</strong></td>
				<td width="60%"><select name="cpnHandling" size="1">
					<option value="0"><?php print $yyNo?></option>
					<option value="1" <?php if((int)$cpnHandling!=0) print 'selected="selected"' ?>><?php print $yyYes?></option>
					</select></td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyLiLev?>:</strong></td>
				<td width="60%">
					<select name="cpnLoginLt" size="1" onchange="setloglev(this)">
					<option value="0">&gt;=</option>
					<option value="1" <?php if($cpnLoginLt) print 'selected="selected"' ?>>=</option>
					</select>
					<select name="cpnLoginLevel" id="cpnLoginLevel" size="1">
						<option value="0"><?php print ($cpnLoginLt?$yyLiLev . ' 0':$yyNoRes)?></option>
<?php				for($index=1; $index<= $maxloginlevels; $index++){
						print '<option value="' . $index . '"';
						if(($cpnLoginLt && $cpnLoginLevel-1==$index) || (! $cpnLoginLt && $cpnLoginLevel==$index)) print ' selected="selected"';
						print '>' . $yyLiLev . ' ' . $index . '</option>';
					} ?>
						<option value="127"<?php if($cpnLoginLevel==127) print ' selected="selected"'?>><?php print $yyDisabl?></option>
					</select>
				</td>
			  </tr>
			  <tr>
				<td width="40%" align="right"><strong><?php print $yyRestr?>:</strong></td>
				<td width="60%"><select name="cpnCntry" size="1">
					<option value="0"><?php print $yyAppAll?></option>
					<option value="1" <?php if((int)$cpnCntry==1) print 'selected="selected"' ?>><?php print $yyYesRes?></option>
					</select></td>
			  </tr>
			  <tr>
                <td width="100%" colspan="2" align="center"><br /><input type="submit" value="<?php print $yySubmit?>" /><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />
                          &nbsp;</td>
			  </tr>
            </table>
		  </form>
<script type="text/javascript">
<!--
savebg=document.mainform.cpnNumber.style.backgroundColor;
couponcodeactive(false);
changecouponeffect(false);
//-->
</script>
<?php
}elseif(getpost('posted')=="1" && $success){ ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="admindiscounts.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br />&nbsp;</td>
			  </tr>
			</table>
<?php
}elseif(getpost('posted')=="1"){ ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table>
<?php
}else{
	$sortorder=@$_REQUEST['sort'];
	$modclone = @$_COOKIE['modclone'];
	$jscript=''; ?>
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
	document.mainform.submit();
}
function cr(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "clone";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function dr(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")) {
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
function startsearch(){
	document.mainform.action="admindiscounts.php";
	document.mainform.act.value="search";
	document.mainform.posted.value="";
	document.mainform.submit();
}
function changemodclone(modclone){
	setCookie('modclone',modclone[modclone.selectedIndex].value,600);
	startsearch();
}
// -->
</script>
<h2><?php print $yyAdmCoD?></h2>
	<form name="mainform" method="post" action="admindiscounts.php">
	<input type="hidden" name="posted" value="1" />
	<input type="hidden" name="act" value="xxxxx" />
	<input type="hidden" name="id" value="xxxxx" />
	<input type="hidden" name="pg" value="<?php print (getpost('act')=='search' ? '1' : getget('pg'))?>" />
	<input type="hidden" name="selectedq" value="1" />
	<input type="hidden" name="newval" value="1" />
	<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
	  <tr height="30"> 
		<td class="cobhl" width="25%" align="right"><?php print $yySrchFr?>:</td>
		<td class="cobll" width="25%"><input type="text" name="stext" size="20" value="<?php print @$_REQUEST['stext']?>" /></td>
		<td class="cobhl" width="25%" align="right"><?php print $yySrchTp?>:</td>
		<td class="cobll" width="25%"><select name="stype" size="1">
			<option value=""><?php print $yySrchAl?></option>
			<option value="any"<?php if(@$_REQUEST['stype']=="any") print ' selected="selected"'?>><?php print $yySrchAn?></option>
			<option value="exact"<?php if(@$_REQUEST['stype']=="exact") print ' selected="selected"'?>><?php print $yySrchEx?></option>
			</select>
		</td>
	  </tr>
	  <tr height="30"> 
		<td class="cobhl" width="25%" align="right"><?php print $yyCpnDsc?>:</td>
		<td class="cobll" width="25%"><select name="scpds" size="1">
			<option value=""><?php print $yyAll?></option>
			<option value="cpn"<?php if(@$_REQUEST['scpds']=="cpn") print ' selected="selected"'?>><?php print $yyCoupon?></option>
			<option value="dsc"<?php if(@$_REQUEST['scpds']=="dsc") print ' selected="selected"'?>><?php print $yyDisco?></option>
			</select>
		</td>
		<td class="cobhl" width="25%" align="right"><?php print $yyDscEff?>:</td>
		<td class="cobll" width="25%"><select name="sefct" size="1">
			<option value=""><?php print $yyAll?></option>
			<option value="frshp"<?php if(@$_REQUEST['sefct']=="frshp") print ' selected="selected"'?>><?php print $yyFrSShp?></option>
			<option value="fltra"<?php if(@$_REQUEST['sefct']=="fltra") print ' selected="selected"'?>><?php print $yyFlatDs?></option>
			<option value="percd"<?php if(@$_REQUEST['sefct']=="percd") print ' selected="selected"'?>><?php print $yyPerDis?></option>
			</select>
		</td>
	  </tr>
	  <tr height="30">
		<td class="cobhl">&nbsp;</td>
		<td class="cobll" colspan="3" align="center">
				<select name="sort" size="1">
				<option value="">Sort - <?php print $yyDisTxt?></option>
				<option value="dam"<?php if($sortorder=="dam") print ' selected="selected"'?>>Sort - <?php print $yyDscAmt?></option>
				<option value="dex"<?php if($sortorder=="dex") print ' selected="selected"'?>>Sort - <?php print $yyExpDat?></option>
				</select>
				<input type="submit" value="List Discounts" onclick="startsearch();" />
				<input type="button" value="<?php print $yyNewDsc?>" onclick="newrec()" />
	  </tr>
	</table>
<br />
            <table width="100%" class="stackable admin-table-a sta-white">
<?php
	function displayheaderrow(){
		global $yyWrkNam,$yyType,$yyExpDat,$yyGlobal,$yyClone,$yyModify,$yyDelete,$modclone; ?>
			  <tr>
				<th class="maincell"><strong><?php print $yyWrkNam?></strong></th>
				<th class="minicell"><strong><?php print $yyType?></strong></th>
				<th class="minicell"><strong><?php print $yyExpDat?></strong></th>
				<th class="minicell"><strong><?php print $yyGlobal?></strong></th>
				<th class="minicell"><?php print $yyModify?></th>
			  </tr>
<?php
	}
	if(getpost('act')=='search' || getget('pg')!=''){
		$hassearch=FALSE;
		$sSQL = '';
		$whereand=' WHERE ';
		if(trim(@$_REQUEST['scpds'])!=''){
			if(@$_REQUEST['scpds']=='cpn') $sSQL.=$whereand . 'cpnIsCoupon<>0'; else $sSQL.=$whereand . 'cpnIsCoupon=0';
			$whereand=' AND ';
		}
		if(trim(@$_REQUEST['sefct'])!=''){
			if(@$_REQUEST['sefct']=='frshp')
				$sSQL.=$whereand . 'cpnType=0';
			elseif(@$_REQUEST['sefct']=='fltra')
				$sSQL.=$whereand . 'cpnType=1';
			else
				$sSQL.=$whereand . 'cpnType=2';
			$whereand=' AND ';
		}
		if(trim(@$_REQUEST['stext'])!=''){
			$Xstext = escape_string(@$_REQUEST['stext']);
			$aText = explode(' ',$Xstext);
			$maxsearchindex=1;
			$aFields[0]='cpnName';
			$aFields[1]='cpnWorkingName';
			if(@$_REQUEST['stype']=='exact'){
				$sSQL.=$whereand . "(cpnName LIKE '%".$Xstext."%' OR cpnWorkingName LIKE '%".$Xstext."%') ";
				$whereand=' AND ';
			}else{
				$sJoin='AND ';
				if(@$_REQUEST['stype']=='any') $sJoin='OR ';
				$sSQL.=$whereand . '(';
				$whereand=' AND ';
				for($index=0;$index<=$maxsearchindex;$index++){
					$sSQL.='(';
					$rowcounter=0;
					$arrelms=count($aText);
					foreach($aText as $theopt){
						if(is_array($theopt))$theopt=$theopt[0];
						$sSQL.=$aFields[$index] . " LIKE '%" . $theopt . "%' ";
						if(++$rowcounter < $arrelms) $sSQL.=$sJoin;
					}
					$sSQL.=') ';
					if($index < $maxsearchindex) $sSQL.='OR ';
				}
				$sSQL.=') ';
			}
		}
		if($sortorder=='dam')
			$sSQL.=' ORDER BY cpnDiscount,cpnWorkingName';
		elseif($sortorder=='dex')
			$sSQL.=' ORDER BY cpnEndDate,cpnWorkingName';
		else
			$sSQL.=' ORDER BY cpnWorkingName';
		if(! @is_numeric(getget('pg')))
			$CurPage = 1;
		else
			$CurPage = (int)getget('pg');
		if(@$admindiscountsperpage=='') $admindiscountsperpage=600;
		$tmpSQL = 'SELECT COUNT(*) AS bar FROM coupons' . $sSQL;
		$sSQL = 'SELECT cpnID,cpnWorkingName,cpnSitewide,cpnIsCoupon,cpnEndDate FROM coupons' . $sSQL;
		$sSQL.=' LIMIT ' . ($admindiscountsperpage*($CurPage-1)) . ", $admindiscountsperpage";
		$allprods=ect_query($tmpSQL) or ect_error();
		$rs=ect_fetch_assoc($allprods);
		$iNumOfPages = ceil($rs['bar']/$admindiscountsperpage);
		ect_free_result($allprods);
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result) > 0){
			$Count=0;
			$pblink = '<a href="admindiscounts.php?stext='.urlencode(@$_REQUEST['stext'])."&stype=".@$_REQUEST['stype']."&scpds=".@$_REQUEST['scpds']."&sefct=".@$_REQUEST['sefct']."&sort=".$sortorder.'&pg=';
			if($iNumOfPages > 1) print '<tr><td colspan="5" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '<br />&nbsp;</td></tr>';
			displayheaderrow();
			while($alldata=ect_fetch_assoc($result)){
				$jscript.='pa['.$Count.']=['; ?>
				<tr id="tr<?php print $Count?>">
					<td class="maincell"><?php print $alldata['cpnWorkingName']?></td>
					<td class="minicell"><?php	if($alldata['cpnIsCoupon']==1) print $yyCoupon; else print $yyDisco;?></td>
					<td class="minicell"><?php	if($alldata['cpnEndDate']=='3000-01-01 00:00:00')
													print $yyNever;
												elseif(strtotime($alldata['cpnEndDate'])-strtotime(date('Y-m-d')) < 0)
													print '<span style="color:#FF0000">' . $yyExpird . '</span>';
												else
													print date("Y-m-d",strtotime($alldata['cpnEndDate'])); ?></td>
<td class="minicell"><?php if($alldata['cpnSitewide']==1 || $alldata['cpnSitewide']==2) print $yyYes; else print $yyNo; ?></td><td>-</td></tr>
<?php			$jscript.=$alldata['cpnID']."];\r\n";
				$Count++;
			}
			if($iNumOfPages > 1) print '<tr><td colspan="5" align="center"><br />' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
		}else{ ?>
			  <tr> 
                <td width="100%" colspan="5" align="center"><br /><strong><?php print $yyNoDsc?></strong><br />&nbsp;</td>
			  </tr>
<?php	}
		ect_free_result($result);
	}
?>			  <tr> 
                <td width="100%" colspan="5" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
	</form>
<script type="text/javascript">
/* <![CDATA[ */
var pa=[];
<?php print $jscript?>
for(var pidind in pa){
	var ttr=document.getElementById('tr'+pidind);
	ttr.cells[4].style.textAlign='center';
	ttr.cells[4].style.whiteSpace='nowrap';
	ttr.cells[4].innerHTML='<input type="button" value="M" style="width:30px" onclick="mr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyModify))?>" />&nbsp;' +
		'<input type="button" value="C" style="width:30px" onclick="cr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyClone))?>" />&nbsp;' +
		'<input type="button" value="X" style="width:30px" onclick="dr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyDelete))?>" />';
}
/* ]]> */
</script>
<?php
}
?>