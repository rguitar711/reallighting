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
$editzones = (($shipType==2 || $shipType==5 || $adminIntShipping==2 || $adminIntShipping==5 || $alternateratesweightbased) && $splitUSZones);
if(getget('setcatax')=='true'){
	ect_query("UPDATE states SET stateTax=0 WHERE stateCountryID=2 AND stateAbbrev='AB'") or ect_error();
	ect_query("UPDATE states SET stateTax=7 WHERE stateCountryID=2 AND stateAbbrev='BC'") or ect_error();
	ect_query("UPDATE states SET stateTax=7 WHERE stateCountryID=2 AND stateAbbrev='MB'") or ect_error();
	ect_query("UPDATE states SET stateTax=8 WHERE stateCountryID=2 AND stateAbbrev='NB'") or ect_error();
	ect_query("UPDATE states SET stateTax=8 WHERE stateCountryID=2 AND stateAbbrev='NF'") or ect_error();
	ect_query("UPDATE states SET stateTax=0 WHERE stateCountryID=2 AND stateAbbrev='NT'") or ect_error();
	ect_query("UPDATE states SET stateTax=10 WHERE stateCountryID=2 AND stateAbbrev='NS'") or ect_error();
	ect_query("UPDATE states SET stateTax=0 WHERE stateCountryID=2 AND stateAbbrev='NU'") or ect_error();
	ect_query("UPDATE states SET stateTax=8 WHERE stateCountryID=2 AND stateAbbrev='ON'") or ect_error();
	ect_query("UPDATE states SET stateTax=9 WHERE stateCountryID=2 AND stateAbbrev='PE'") or ect_error();
	ect_query("UPDATE states SET stateTax=9.975 WHERE stateCountryID=2 AND stateAbbrev='QC'") or ect_error();
	ect_query("UPDATE states SET stateTax=5 WHERE stateCountryID=2 AND stateAbbrev='SK'") or ect_error();
	ect_query("UPDATE states SET stateTax=0 WHERE stateCountryID=2 AND stateAbbrev='YT'") or ect_error();
	ect_query('UPDATE countries SET countryTax=5 WHERE countryID=2') or ect_error();
}
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
		if($editzones)
			$sSQL = "UPDATE states SET stateEnabled=" . $cena . ",stateTax=" . $tax . ",stateFreeShip=" . $fsa . ",stateZone=" . getpost('zon') . " WHERE stateID=" . getpost('id');
		else
			$sSQL = "UPDATE states SET stateEnabled=" . $cena . ",stateTax=" . $tax . ",stateFreeShip=" . $fsa . " WHERE stateID=" . getpost('id');
		ect_query($sSQL) or ect_error();
	}
	if($success)
		print '<meta http-equiv="refresh" content="1; url=adminstate.php">';
}elseif(getpost('doeditstates')!=''){
	$nextfreeid=1;
	foreach(@$_POST as $objItem => $objValue){
		if(substr($objItem,0,9)=='stateName'){
			$stateID=(int)substr($objItem, 9);
			if(trim($objValue)=='')
				$sSQL="DELETE FROM states WHERE stateID=" . $stateID;
			else{
				$sSQL="UPDATE states SET stateName='" . escape_string($objValue) . "'";
				for($index=2; $index <= $adminlanguages+1; $index++){
					if(($adminlangsettings & 1048576)==1048576) $sSQL.=',stateName' . $index . "='" . escape_string(@$_POST['state' . $index . 'Name' . $stateID]) . "'";
				}
				$sSQL.=' WHERE stateID=' . $stateID;
			}
			ect_query($sSQL);
		}elseif(substr($objItem,0,12)=='stateNewName'){
			$rowid=(int)substr($objItem, 12);
			$stateName=trim($objValue);
			if($stateName!=''){
				$stateName2=trim(@$_POST['state2Name'.$rowid]);
				$stateName3=trim(@$_POST['state3Name'.$rowid]);
				if($stateName2=='') $stateName2=$stateName;
				if($stateName3=='') $stateName3=$stateName;
				$gotstateid=FALSE;
				while(! $gotstateid){
					$result=ect_query("SELECT stateID FROM states WHERE stateID=" . $nextfreeid) or ect_error();
					if(ect_num_rows($result)==0) $gotstateid=TRUE; else $nextfreeid++;
					ect_free_result($result);
				}
				$sSQL = "INSERT INTO states (stateID,stateName,stateName2,stateName3,stateCountryID,stateEnabled) VALUES (" .
					$nextfreeid . "," .
					"'" . escape_string($stateName) . "'," .
					"'" . escape_string($stateName2) . "'," .
					"'" . escape_string($stateName3) . "', " . getpost('thiscountry') . ',1)';
				ect_query($sSQL);
			}
		}
	}
	print '<meta http-equiv="refresh" content="1; url=adminstate.php?thiscountry=' . getpost('thiscountry') . '" />';
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
		$sSQL = "UPDATE states SET stateEnabled=" . $cena . " WHERE stateID IN (" . implode(',', $theids) . ")";
	elseif($setallact=='allfsa')
		$sSQL = "UPDATE states SET stateFreeShip=" . $fsa . " WHERE stateID IN (" . implode(',', $theids) . ")";
	elseif($setallact=='alltax'){
		if(! is_numeric($tax)){
			$success=FALSE;
			$errmsg = $yyNum100 . ' "' . $yyTax . '".';
		}elseif($tax > 100 || $tax < 0){
			$success=FALSE;
			$errmsg = $yyNum100 . ' "' . $yyTax . '".';
		}else
			$sSQL = "UPDATE states SET stateTax=" . $tax . " WHERE stateID IN (" . implode(',', $theids) . ")";
	}elseif($setallact=='allpos')
		$sSQL = "UPDATE states SET stateOrder=" . $pos . " WHERE stateID IN (" . implode(',', $theids) . ")";
	elseif($setallact=='allzone')
		$sSQL = "UPDATE states SET stateZone=" . $zone . " WHERE stateID IN (" . implode(',', $theids) . ")";
	if($success)
		ect_query($sSQL) or ect_error();
}
$sSQL = "SELECT pzID,pzName FROM postalzones WHERE pzName<>'' AND pzID>100";
$result=ect_query($sSQL) or ect_error();
while($rs=ect_fetch_assoc($result))
	$allzones[$numzones++] = $rs;
ect_free_result($result);
if(getpost('doeditstates')!=''){ ?>
			<p align="center"><br />&nbsp;<br />&nbsp;<br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="adminstate.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br /><br /><br /></p>
<?php
}elseif((getpost('posted')=='1' || getpost('setallact')!='') && ! $success){ ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold">Some records could not be updated.</span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table>
<?php
}elseif(getget('id')!=''){ ?>
		  <form name="mainform" method="post" action="adminstate.php">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="id" value="<?php print getget('id')?>" />
			<input type="hidden" name="thiscountry" value="<?php print @$_REQUEST['thiscountry']?>" />
			<table width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><strong><?php print $yyStaAdm?></strong><br /><br />
				<span style="font-size:10px"><?php print $yyFSANot?><br />&nbsp;</span></td>
			  </tr>
<?php
	$sSQL = "SELECT stateID,stateName,stateEnabled,stateTax,stateZone,stateFreeShip FROM states WHERE stateID='" . escape_string(getget('id')) . "'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		?>
			  <tr>
				<td align="right" width="50%"><strong><?php print $yyStaNam?></strong></td>
				<td><strong><?php print $rs["stateName"]?></strong></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyEnable?></strong></td>
				<td><input type="checkbox" name="ena"<?php if((int)$rs["stateEnabled"]==1) print ' checked="checked"' ?> /></td>
			  </tr>
			  <tr>
				<td align="right"><strong><?php print $yyTax?></strong></td>
				<td><input type="text" name="tax" value="<?php print (double)$rs["stateTax"]?>" size="4" />%</td>
			  </tr>
			  <tr>
				<td align="right"><strong><acronym title="<?php print $yyFSApp?>"><?php print $yyFSApp . ' ('.$yyFSA.')'?></acronym></strong></td>
				<td><input type="checkbox" name="fsa"<?php if((int)$rs["stateFreeShip"]==1) print ' checked="checked"'?> /></td>
			  </tr>
<?php	if($editzones){ ?>
			  <tr>
				<td align="right"><strong><?php print $yyPZone;?></strong></td>
<?php		$foundzone=FALSE;
			print '<td><select name="zon" size="1">';
			for($index=0; $index < $numzones; $index++){
				print '<option value="' . $allzones[$index]['pzID'] . '"';
				if($rs['stateZone']==$allzones[$index]['pzID']){
					print ' selected="selected"';
					$foundzone=TRUE;
				}
				print '>' . $allzones[$index]['pzName'] . "</option>\n";
			}
			if(!$foundzone)print '<option value="0" selected="selected"><?php print $yyUndef?></option>';
			print '</select></td>';
		}
	}
	ect_free_result($result); ?>
			  </tr>
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
	if(! is_numeric(@$_REQUEST['thiscountry'])) $thiscountry=$origCountryID; else $thiscountry=(int)$_REQUEST['thiscountry'];
	$forcezonesplit=(($thiscountry==1 || $thiscountry==2) && @$usandcasplitzones);
	if($editzones) $colspan='7'; else $colspan='6';
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
function doaddrow(){
var rownumber = document.getElementById("maxidvalue").value;
opttable = document.getElementById('statestable');
newrow = opttable.insertRow(opttable.rows.length-1);
if((parseInt(rownumber)%2)==0)newrow.className='cobhl';else newrow.className='cobll';
newcell = newrow.insertCell(0);
newcell.align='center';
newcell.innerHTML = '<input type="text" name="stateNewName'+rownumber+'" size="30" value="" />';
<?php		$rowcounter=1;
			for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 1048576)==1048576){ ?>
newcell = newrow.insertCell(<?php print $rowcounter?>);
newcell.align='center';
newcell.innerHTML = '<input type="text" name="stateNew<?php print $index?>Name'+rownumber+'" size="30" value="" />';
<?php				$rowcounter++;
				}
			} ?>
newcell = newrow.insertCell(<?php print $rowcounter?>);
newcell.align='center';
newcell.innerHTML = '-';
document.getElementById("maxidvalue").value = parseInt(rownumber)+1;
}
function addmorerows(){
	numextrarows = document.getElementById("numextrarows").value;
	numextrarows = parseInt(numextrarows);
	if(isNaN(numextrarows))numextrarows=1;
	if(numextrarows==0)numextrarows=1;
	if(numextrarows>100)numextrarows=100;
	for(index=0;index<numextrarows;index++){
		doaddrow();
	}
}
/* ]]> */
</script>
	<table width="100%">
	  <tr>
		<td align="center">
		  <form name="mainform" method="post" action="adminstate.php">
		  	<input type="hidden" name="setallact" id="setallact" value="" />
            <table width="100%" border="0" cellspacing="3" cellpadding="3">
			  <tr>                <td align="center" colspan="2"><strong><?php print $yyStaAdm?></strong><br /><br />
<?php
	if(getget('editstates')!='1'){ ?>
				<span style="font-size:10px"><?php print $yyFSANot?><br />&nbsp;</span>
<?php
	} ?>
				</td>
			  </tr>
<?php
	if(getget('editstates')!='1'){ ?>
			  <tr>
                <td align="right" valign="top">
				  <table width="340" border="0" cellspacing="1" cellpadding="3" class="cobtbl">
					<tr height="30"><td class="cobhl" colspan="3" align="center"><strong><?php print $yyWitSel?>...</strong></td></tr>
					<tr height="30"><td class="cobhl" align="right"><strong><?php print $yyEnable?>:</strong></td><td class="cobll" align="left"><select name="allenable" size="1"><option value="ON"><?php print $yyYes?></option><option value=""><?php print $yyNo?></option></select></td><td class="cobll" align="center"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allenable')" /></td></tr>
<?php	if($thiscountry==$origCountryID || $forcezonesplit){
			if($thiscountry==$origCountryID){ ?>
					<tr height="30"><td class="cobhl" align="right"><strong><?php print $yyTax?>:</strong></td><td class="cobll" align="left"><input type="text" name="alltax" size="5" />%</td><td class="cobll" align="center"><input type="button" value="<?php print $yySubmit?>" onclick="setall('alltax')" /></td></tr>
					<tr height="30"><td class="cobhl" align="right"><strong><?php print $yyFSApp?>:</strong></td><td class="cobll" align="left"><select name="allfsa" size="1"><option value="ON"><?php print $yyYes?></option><option value=""><?php print $yyNo?></option></select></td><td class="cobll" align="center"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allfsa')" /></td></tr>
<?php		}
			if($editzones){ ?>
					<tr height="30"><td class="cobhl" align="right"><strong><?php print $yyPZone?>:</strong></td><td class="cobll" align="left"><select name="allzone" size="1">
<?php			for($index=0; $index < $numzones; $index++){
					print '<option value="' . $allzones[$index]['pzID'] . '"';
					print '>' . $allzones[$index]['pzName'] . "</option>\n";
				} ?>
					</select></td><td class="cobll" align="center"><input type="button" value="<?php print $yySubmit?>" onclick="setall('allzone')" /></td></tr>
<?php		}
			if($thiscountry==$origCountryID && $thiscountry==2){ ?>
					<tr height="30"><td class="cobll" colspan="3" align="center"><input type="button" value="Please click here to set Canadian tax rates" onclick="if(confirm('We make every effort to keep these Tax Rates up to date, but rates change\nfrequently. Please check the tax rates and inform us of any changes.'))document.location='adminstate.php?thiscountry=2&setcatax=true'" /></td></tr>
<?php		}
		} ?>
				  </table>
				</td><td align="left" valign="top" width="50%">
				  <table width="340" border="0" cellspacing="1" cellpadding="3" class="cobtbl">
					<tr height="30"><td class="cobhl" colspan="3" align="center"><strong><?php print $yyStaCou?>...</strong></td></tr>
					<tr height="30"><td class="cobhl" align="right"><strong><?php print $yyCountry?>:</strong></td>
					<td class="cobll" align="left"><select size="1" name="thiscountry" id="thiscountry" onchange="document.location='adminstate.php?thiscountry='+this[this.selectedIndex].value"><?php
						$gotstates='';
						$sSQL="SELECT DISTINCT countryID,countryName FROM countries INNER JOIN states ON countries.countryID=states.stateCountryID ORDER BY countryName";
						$result=ect_query($sSQL) or ect_error();
						while($rs=ect_fetch_assoc($result)){
							print '<option value="' . $rs['countryID'] . '"' . ($thiscountry==$rs['countryID'] ? ' selected="selected"' : '') . '>' . htmlspecials($rs['countryName']) . '</option>';
							$gotstates.= $rs['countryID'] . ',';
						}
						ect_free_result($result);
						$sSQL="SELECT countryID,countryName FROM countries " . ($gotstates!='' ? 'WHERE countryID NOT IN (' . substr($gotstates,0,-1) . ") " : '') . "ORDER BY countryName";
						print '<option value="" disabled="disabled">----------------------</option>';
						$result=ect_query($sSQL) or ect_error();
						while($rs=ect_fetch_assoc($result)){
							print '<option value="' . $rs['countryID'] . '"' . ($thiscountry==$rs['countryID'] ? ' selected="selected"' : '') . '>' . htmlspecials($rs['countryName']) . '</option>';
						}
						ect_free_result($result);
						if(is_numeric(getget('loadstates')))
							ect_query("UPDATE countries SET loadStates=" . getget('loadstates') . " WHERE countryID=" . $thiscountry) or ect_error();
						$sSQL = "SELECT loadStates FROM countries WHERE countryID=" . $thiscountry;
						$result=ect_query($sSQL) or ect_error();
						if($rs=ect_fetch_assoc($result)) $loadstates=$rs['loadStates']; else $loadstates=0;
						ect_free_result($result);
					?></select></td></tr>
					<tr height="30"><td class="cobhl" align="right"><strong><?php print $yyLoadSt?>:</strong></td>
					<td class="cobll" align="left"><select size="1" name="loadstates" onchange="document.location='adminstate.php?thiscountry='+document.getElementById('thiscountry')[document.getElementById('thiscountry').selectedIndex].value+'&loadstates='+this[this.selectedIndex].value">
					<option value="0"><?php print $yyNo?></option>
<?php //					<option value="1"<% if loadstates=1 then print " selected=""selected"""?>>Dynamically</option>
?>
					<option value="2"<?php if($loadstates==2) print ' selected="selected"'?>><?php print $yyYes?></option>
					<option value="-1"<?php if($loadstates==-1) print ' selected="selected"'?>>Not Required</option>
					</select>
					</td></tr>
					<tr height="30"><td class="cobhl" align="right"><strong><?php print $yyEdiSta?>:</strong></td>
					<td class="cobll" align="left"><input type="button" value="<?php print $yySubmit?>" onclick="document.location='adminstate.php?thiscountry='+document.getElementById('thiscountry')[document.getElementById('thiscountry').selectedIndex].value+'&editstates=1'" /></td></tr>
				  </table>
				</td>
			  </tr>
<?php
	} ?>
			</table>
<?php
	if(getget('editstates')=='1' && is_numeric(getget('thiscountry'))){ ?>
		  <input type="hidden" name="doeditstates" value="1" />
		  <input type="hidden" name="thiscountry" value="<?php print getget('thiscountry')?>" />
<?php	$sSQL="SELECT countryName FROM countries WHERE countryID=" . getget('thiscountry');
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)) $countryName=$rs['countryName']; else $countryName="UNDEFINED";
		ect_free_result($result);
?>
			<p align="center">You are editing states for the country: <strong><?php print $countryName?></strong><br />&nbsp;</p>
			<table border="0" cellspacing="1" cellpadding="3" class="cobtbl" id="statestable">
			  <tr height="30">
				<td align="center" class="cobhl"><strong><?php print $yyStaNam?></strong></td>
<?php	$colspan=2;
		for($index=2; $index <= $adminlanguages+1; $index++){
			if(($adminlangsettings & 1048576)==1048576){
				$colspan++;
?><td align="center" class="cobhl"><strong><?php print $yyStaNam.' '.$index?></strong></td><?php
			}
		} ?>
				<td class="cobhl" align="center"><strong>&nbsp;<?php print $yyEnable?>&nbsp;</strong></td>
			  </tr>
			
<?php	$sSQL = "SELECT stateID,stateName,stateName2,stateName3,stateEnabled FROM states WHERE stateCountryID=" . $thiscountry . " ORDER BY stateName";
		$result=ect_query($sSQL) or ect_error();
		$hasrows=(ect_num_rows($result)>0);
		while($rs=ect_fetch_assoc($result)){
			if(@$bgcolor=="cobhl") $bgcolor='cobll'; else $bgcolor='cobhl';
			?><tr align="center" class="<?php print $bgcolor?>">
<td align="center"><input type="text" size="30" name="stateName<?php print $rs['stateID']?>" value="<?php print htmlspecials($rs['stateName'])?>" /></td>
<?php			for($index=2; $index <= $adminlanguages+1; $index++){
					if(($adminlangsettings & 1048576)==1048576){
?><td align="center"><input type="text" size="30" name="state<?php print $index?>Name<?php print $rs['stateID']?>" size="25" value="<?php print htmlspecials($rs['stateName'.$index])?>" /></td><?php
					}
				} ?>
<td><?php	if($rs['stateEnabled']==1) print $yyYes; else print '&nbsp;'?></td></tr>
<?php
		}
		ect_free_result($result); ?>
			  <tr height="30">
				<td class="cobll" colspan="<?php print $colspan?>" align="center"><input type="hidden" name="maxidvalue" id="maxidvalue" value="1" /><input type="text" name="numextrarows" id="numextrarows" value="10" size="4" /> <input type="button" value="<?php print $yyMore . ' ' . $yyLLStat?>" onclick="addmorerows()" />&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="<?php print $yySubmit?>" /> <input type="button" value="<?php print $yyCancel?>" onclick="document.location='adminstate.php?thiscountry=<?php print getget('thiscountry')?>'" /></td>
			  </tr>
			</table>
<?php	if(! $hasrows)
			print '<script type="text/javascript">addmorerows();</script>' . "\r\n";
	}else{ ?>
<br />
            <table width="100%" class="stackable admin-table-a sta-white">
			  <tr>
				<th class="minicell"><input type="checkbox" id="xdocheckall" value="1" onclick="docheckall()" /></th>
				<th class="maincell"><?php print $yyStaNam?></th>
				<th class="minicell"><?php print $yyEnable?></th>
				<th class="minicell"><?php print $yyTax?></th>
				<th class="minicell"><acronym title="<?php print $yyFSApp?>"><?php print $yyFSA?></acronym></th>
<?php	if($editzones) print '<th class="minicell">' . $yyPZone . '</th>';
		if($thiscountry==$origCountryID || $forcezonesplit) print '<th class="minicell">' . $yyModify . '</th>'; ?>
			  </tr><?php
		$theids = @$_POST['ids'];
		$bgcolor='cobhl';
		$sSQL = "SELECT stateID,stateName,stateEnabled,stateTax,stateZone,stateFreeShip FROM states WHERE stateCountryID='" . escape_string($thiscountry) . "' ORDER BY stateEnabled DESC,stateName";
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result)==0){
			print '<tr><td align="center" class="cobll" colspan="' . ($editzones?7:6) . '"><p>No states have been defined. To create states for this country please click on the button for Edit States above.</p></td></tr>';
		}
		while($rs=ect_fetch_assoc($result)){
			if($bgcolor=='cobhl') $bgcolor='cobll'; else $bgcolor='cobhl';
		?><tr align="center" class="<?php print $bgcolor?>">
<td align="center"><input type="checkbox" name="ids[]" value="<?php print $rs['stateID']?>" <?php
			if(is_array($theids)){
				foreach($theids as $anid){
					if($anid==$rs['stateID']){
						print 'checked="checked" ';
						break;
					}
				}
			}
			?>/></td>
<td align="left"><?php
			if((int)$rs['stateEnabled']==1) print '<strong>';
			print $rs['stateName'];
			if((int)$rs['stateEnabled']==1) print '</strong>';?></td>
<td><?php	if((int)$rs['stateEnabled']==1) print $yyYes; else print '&nbsp;';?></td>
<?php		if($thiscountry!=$origCountryID && ! $forcezonesplit)
				print '<td>-</td><td>-</td><td>-</td>';
			else{ ?>
<td><?php		if((double)$rs['stateTax']!=0) print (double)$rs['stateTax'].'%'; else print '&nbsp;';?></td>
<td><?php		if((int)$rs['stateFreeShip']==1) print $yyYes; else print '&nbsp;';?></td>
<?php			if($editzones){
					if((int)$rs['stateEnabled']!=1)
						print '<td>-</td>';
					else{
						$foundzone=FALSE;
						for($index=0; $index < $numzones; $index++){
							if($rs['stateZone']==$allzones[$index]['pzID']){
								print '<td>' . $allzones[$index]['pzName'] . '</td>';
								$foundzone=TRUE;
							}
						}
						if(!$foundzone)print '<td>' . $yyUndef . '</td>';
					}
				}
				print '<td><input type="button" onclick="document.location=\'adminstate.php?id='.$rs['stateID'].'\'" value="' . $yyModify . '"/></td>';
			}
			print '</tr>';
		}
		ect_free_result($result); ?>
			</table>
<?php
	} ?>
		  <p align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</p>
		  </form>
		</td>
	  </tr>
	</table>
<?php
}
?>