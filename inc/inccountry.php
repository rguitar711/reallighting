<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
$success=TRUE;
$numzones=0;
$alreadygotadmin = getadminsettings();
$alternateratesweightbased=FALSE;
if($adminAltRates>0){
	$sSQL = "SELECT altrateid FROM alternaterates WHERE (altrateid=2 OR altrateid=5) AND (usealtmethod<>0 OR usealtmethodintl<>0)";
	$result=ect_query($sSQL) or ect_error();
	$alternateratesweightbased=(ect_num_rows($result)>0);
	ect_free_result($result);
}
$editzones = ($shipType==2 || $shipType==5 || $adminIntShipping==2 || $adminIntShipping==5 || $alternateratesweightbased);
if(getpost('posted')=='1'){
	$cena=0;
	if(getpost('ena')!='') $cena=1;
	$fsa=0;
	if(getpost('fsa')!='') $fsa=1;
	$tax = getpost('tax');
	if(! is_numeric($tax)){
		$success=FALSE;
		$errmsg = $yyNum100 . ' "' . $yyTax . '".';
	}elseif($tax > 100 || $tax < 0){
		$success=FALSE;
		$errmsg = $yyNum100 . ' "' . $yyTax . '".';
	}else{
		$sSQL = "UPDATE countries SET countryEnabled=" . $cena . ",countryTax=" . $tax . ",countryOrder=" . getpost('pos') . ",countryFreeShip=" . $fsa . ",countryLCID='" . getpost('lcid') . "'";
		if($editzones)$sSQL.=",countryZone=" . getpost('zon');
		if(getpost('countryname')!='') $sSQL.=",countryName='".escape_string(getpost('countryname'))."'";
		if(getpost('countryname2')!='') $sSQL.=",countryName2='".escape_string(getpost('countryname2'))."'";
		if(getpost('countryname3')!='') $sSQL.=",countryName3='".escape_string(getpost('countryname3'))."'";
		$sSQL.="WHERE countryID=" . getpost('id');
		ect_query($sSQL) or ect_error();
	}
	if($success)
		print '<meta http-equiv="refresh" content="1; url=admincountry.php">';
}elseif(getpost('setallact')!=''){
	$setallact = getpost('setallact');
	$theids = @$_POST['ids'];
	$cena=0;
	if(getpost('allenable')=='ON') $cena=1;
	$fsa=0;
	if(getpost('allfsa')!='') $fsa=1;
	$tax = getpost('alltax');
	$pos = getpost('allpos');
	$zone = getpost('allzone');
	if($setallact=='allenable')
		$sSQL = "UPDATE countries SET countryEnabled=" . $cena . " WHERE countryID IN (" . implode(',', $theids) . ")";
	elseif($setallact=='allfsa')
		$sSQL = "UPDATE countries SET countryFreeShip=" . $fsa . " WHERE countryID IN (" . implode(',', $theids) . ")";
	elseif($setallact=='alltax'){
		if(! is_numeric($tax)){
			$success=FALSE;
			$errmsg = $yyNum100 . ' "' . $yyTax . '".';
		}elseif($tax > 100 || $tax < 0){
			$success=FALSE;
			$errmsg = $yyNum100 . ' "' . $yyTax . '".';
		}else
			$sSQL = "UPDATE countries SET countryTax=" . $tax . " WHERE countryID IN (" . implode(',', $theids) . ")";
	}elseif($setallact=='allpos')
		$sSQL = "UPDATE countries SET countryOrder=" . $pos . " WHERE countryID IN (" . implode(',', $theids) . ")";
	elseif($setallact=='allzone')
		$sSQL = "UPDATE countries SET countryZone=" . $zone . " WHERE countryID IN (" . implode(',', $theids) . ")";
	if($success)
		ect_query($sSQL) or ect_error();
}
$sSQL = "SELECT pzID,pzName FROM postalzones WHERE pzName<>'' AND pzID<100";
$result=ect_query($sSQL) or ect_error();
while($rs=ect_fetch_assoc($result))
	$allzones[$numzones++] = $rs;
ect_free_result($result);
if((getpost('posted')=='1' || getpost('setallact')!='') && ! $success){ ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold">Some records could not be updated.</span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table>
<?php
}elseif(getget('id')!='' && is_numeric(getget('id'))){ ?>
		  <form name="mainform" method="post" action="admincountry.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="id" value="<?php print getget('id')?>" />
			<table width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyCntAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2"><ul>
				<li>You should not normally have to edit country names directly apart from regional variations such as &quot;United Kingdom&quot; for &quot;Great Britain&quot;.</li>
				<li>There are scripts available for adding foreign language country names.</li>
				<li>Do not edit country names if using the USPS shipping carrier as this carrier relies on the country name for rates.</li>
				</ul></td>
			  </tr>
<?php
	$sSQL = "SELECT countryID,countryName,countryName2,countryName3,countryEnabled,countryTax,countryOrder,countryZone,countryFreeShip,countryLCID FROM countries WHERE countryID='" . escape_string(getget('id')) . "' ORDER BY countryOrder DESC,countryName";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		?>
			  <tr>
				<td align="right" width="50%"><strong><?php print $yyCntNam?></strong></td>
				<td><input type="text" name="countryname" value="<?php print htmlspecials($rs['countryName'])?>" size="30" /></td>
			  </tr>
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 8)==8){ ?>
			  <tr>
				<td align="right" width="50%"><strong><?php print $yyCntNam.' '.$index?></strong></td>
				<td><input type="text" name="countryname<?php print $index?>" value="<?php print htmlspecials($rs['countryName'.$index])?>" size="30" /></td>
			  </tr>
<?php			}
			} ?>
			  <tr>
				<td align="right"><strong><?php print $yyEnable?></strong></td>
				<td><input type="checkbox" name="ena"<?php if((int)$rs["countryEnabled"]==1) print ' checked="checked"' ?> /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyTax?></strong></td>
				<td><input type="text" name="tax" value="<?php print (double)$rs["countryTax"]?>" size="4" />%</td>
			  </tr>
			  <tr>
				<td align="right"><strong><acronym title="<?php print $yyFSApp?>"><?php print $yyFSApp . ' ('.$yyFSA.')'?></acronym></strong></td>
				<td><input type="checkbox" name="fsa"<?php if((int)$rs["countryFreeShip"]==1) print ' checked="checked"'?> /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyPosit?></strong></td>
				<td><select name="pos" size="1">
<option value="0"><?php print $yyAlphab?></option>
<option value="1"<?php if((int)$rs['countryOrder']==1) print ' selected="selected"' ?>><?php print $yyTop?></option>
<option value="2"<?php if((int)$rs['countryOrder']==2) print ' selected="selected"' ?>><?php print $yyTop.'+'?></option>
<option value="3"<?php if((int)$rs['countryOrder']==3) print ' selected="selected"' ?>><?php print $yyTop.'++'?></option></select></td>
			  </tr>
<?php	if($editzones){ ?>
			  <tr>
				<td align="right"><strong><?php print $yyPZone;?></strong></td>
<?php		$foundzone=FALSE;
			print '<td><select name="zon" size="1">';
			for($index=0; $index < $numzones; $index++){
				print '<option value="' . $allzones[$index]['pzID'] . '"';
				if($rs['countryZone']==$allzones[$index]['pzID']){
					print ' selected="selected"';
					$foundzone=TRUE;
				}
				print '>' . $allzones[$index]['pzName'] . "</option>\n";
			}
			if(!$foundzone)print '<option value="0" selected="selected"><?php print $yyUndef?></option>';
			print '</select></td>';
		} ?>
			  </tr>
			  <tr>
				<td align="right"><strong>Locale ID (Do not change)</strong></td>
				<td><input type="text" name="lcid" value="<?php print $rs['countryLCID']?>" size="6" /></td>
			  </tr>
<?php
	}
	ect_free_result($result); ?>
			  <tr> 
                <td width="100%" colspan="2" align="center">
				  <p>&nbsp;</p>
                  <p><input type="submit" value="<?php print $yySubmit?>" />&nbsp;&nbsp;<input type="reset" value="<?php print $yyReset?>" /><br />&nbsp;</p>
                </td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="2" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a></td>
			  </tr>
			</table>
		  </form>
<?php
}else{
	if($editzones) $colspan='8'; else $colspan='7';
?>
<script type="text/javascript">
/* <![CDATA[ */
function docheckall(){
	allcbs = document.getElementsByName('ids[]');
	mainidchecked = document.getElementById('xdocheckall').checked;
	for(i=0;i<allcbs.length;i++) {
		allcbs[i].checked=mainidchecked;
	}
	return(true);
}
function setall(theact){
	allcbs = document.getElementsByName('ids[]');
	var onechecked=false;
	for(i=0;i<allcbs.length;i++) {
		if(allcbs[i].checked)onechecked=true;
	}
	if(onechecked){
		document.getElementById('setallact').value=theact;
		document.forms.mainform.submit();
	}else{
		alert("<?php print jscheck($yyNoSelO)?>");
	}
}
/* ]]> */
</script>
		  <form name="mainform" method="post" action="admincountry.php">
		  	<input type="hidden" name="setallact" id="setallact" value="" />
            <table width="100%" border="0" cellspacing="1" cellpadding="1">
			  <tr> 
                <td width="100%" align="center"><strong><?php print $yyCntAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr> 
                <td align="center">
				  <table border="0" cellspacing="1" cellpadding="3" class="cobtbl">
					<tr><td class="cobhl" colspan="3" align="center"><strong><?php print $yyWitSel?>...</strong></td></tr>
					<tr><td class="cobhl" align="right"><strong><?php print $yyEnable?>:</strong></td><td class="cobll" align="left"><select name="allenable" size="1"><option value="ON"><?php print $yyYes?></option><option value=""><?php print $yyNo?></option></select></td><td class="cobll"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allenable')" /></td></tr>
					<tr><td class="cobhl" align="right"><strong><?php print $yyTax?>:</strong></td><td class="cobll" align="left"><input type="text" name="alltax" size="5" />%</td><td class="cobll"><input type="button" value="<?php print $yySubmit?>" onclick="setall('alltax')" /></td></tr>
					<tr><td class="cobhl" align="right"><strong><?php print $yyFSApp?>:</strong></td><td class="cobll" align="left"><select name="allfsa" size="1"><option value="ON"><?php print $yyYes?></option><option value=""><?php print $yyNo?></option></select></td><td class="cobll"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allfsa')" /></td></tr>
					<tr><td class="cobhl" align="right"><strong><?php print $yyPosit?>:</strong></td><td class="cobll" align="left"><select name="allpos" size="1" >
						<option value="0"><?php print $yyAlphab?></option>
						<option value="1"><?php print $yyTop?></option>
						<option value="2"><?php print $yyTop.'+'?></option>
						<option value="3"><?php print $yyTop.'++'?></option></select>
					</td><td class="cobll"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allpos')" /></td></tr>
<?php
	if($editzones){ ?>
					<tr><td class="cobhl" align="right"><strong><?php print $yyPZone?>:</strong></td><td class="cobll" align="left"><select name="allzone" size="1">
<?php	for($index=0; $index < $numzones; $index++){
			print '<option value="' . $allzones[$index]['pzID'] . '"';
			print '>' . $allzones[$index]['pzName'] . "</option>\n";
		} ?>
					</select></td><td class="cobll"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allzone')" /></td></tr>
<?php
	} ?>
				  </table><br />
				</td>
			  </tr>
			</table>
<br />
            <table width="100%" class="stackable admin-table-a sta-white">
			  <tr>
				<th class="minicell"><input type="checkbox" id="xdocheckall" value="1" onclick="docheckall()" /></th>
				<th class="maincell"><?php print $yyCntNam?></th>
				<th class="minicell"><?php print $yyEnable?></th>
				<th class="minicell"><?php print $yyTax?></th>
				<th class="minicell"><acronym title="<?php print $yyFSApp?>"><?php print $yyFSA?></acronym></th>
				<th class="minicell"><?php print $yyPosit?></th>
<?php
	if($editzones) print '<th class="minicell"><strong>' . $yyPZone . '</strong></th>' ?>
				<th class="minicell"><strong><?php print $yyModify?></strong></th>
			  </tr><?php
	$theids = @$_POST['ids'];
	$bgcolor='cobhl';
	$sSQL = "SELECT countryID,countryName,countryEnabled,countryTax,countryOrder,countryZone,countryFreeShip FROM countries ORDER BY countryEnabled DESC,countryOrder DESC,countryName";
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		if($bgcolor=='cobhl') $bgcolor='cobll'; else $bgcolor='cobhl';
		?><tr align="center" class="<?php print $bgcolor?>">
<td align="center"><input type="checkbox" name="ids[]" value="<?php print $rs['countryID']?>" <?php
		if(is_array($theids)){
			foreach($theids as $anid){
				if($anid==$rs['countryID']){
					print 'checked="checked" ';
					break;
				}
			}
		}
		?>/></td>
<td align="left"><?php
		if((int)$rs['countryEnabled']==1) print '<strong>';
		print $rs['countryName'];
		if((int)$rs['countryEnabled']==1) print '</strong>';?></td>
<td><?php
		if((int)$rs['countryEnabled']==1) print $yyYes; else print '&nbsp;';?></td>
<td><?php
		if((double)$rs['countryTax']!=0) print (double)$rs['countryTax'].'%'; else print '&nbsp;';?></td>
<td><?php
		if((int)$rs['countryFreeShip']==1) print $yyYes; else print '&nbsp;';?></td>
<td><?php
		if((int)$rs['countryEnabled']!=1)
			print '-';
		elseif((int)$rs['countryOrder']==1)
			print $yyTop;
		elseif((int)$rs['countryOrder']==2)
			print $yyTop.'+';
		elseif((int)$rs['countryOrder']==3)
			print $yyTop.'++';
		else
			print $yyAlphab;
		print '</td>';
		if($editzones){
			if((int)$rs['countryEnabled']!=1)
				print '<td>-</td>';
			else{
				$foundzone=FALSE;
				for($index=0; $index < $numzones; $index++){
					if($rs['countryZone']==$allzones[$index]['pzID']){
						print '<td>' . $allzones[$index]['pzName'] . '</td>';
						$foundzone=TRUE;
					}
				}
				if(!$foundzone)print '<td>' . $yyUndef . '</td>';
			}
		}
		print '<td>';
		print '<input type="button" onclick="document.location=\'admincountry.php?id='.$rs['countryID'].'\'" value="' . $yyModify . '"/>';
		print '</td></tr>';
	}
	ect_free_result($result);
?>
			  <tr> 
                <td class="cobll" width="100%" colspan="<?php print $colspan?>" align="center"><br />
                          <a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
<?php
}
?>
