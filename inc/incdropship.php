<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$storesessionvalue=="") $storesessionvalue="virtualstore".time();
if(@$_SESSION['loggedon']!=$storesessionvalue||@$disallowlogin==TRUE||!@$GLOBALS['incfunctionsdefined']) exit;
if(@$dateadjust=="") $dateadjust=0;
if(@$dateformatstr=="") $dateformatstr = "m/d/Y";
$admindatestr="Y-m-d";
if(@$admindateformat=="") $admindateformat=0;
if($admindateformat==1)
	$admindatestr="m/d/Y";
elseif($admindateformat==2)
	$admindatestr="d/m/Y";
$addsuccess = TRUE;
$success = TRUE;
$showaccount = TRUE;
$dorefresh = FALSE;
$alreadygotadmin = getadminsettings();
if(getpost('act')=="domodify"){
	$sSQL = "UPDATE dropshipper SET dsEmail='" . escape_string(getpost('email')) . "'," .
		"dsName='" . escape_string(getpost('name')) . "'," .
		"dsAddress='" . escape_string(getpost('address')) . "'," .
		"dsCity='" . escape_string(getpost('city')) . "'," .
		"dsState='" . escape_string(getpost('state')) . "'," .
		"dsCountry='" . escape_string(getpost('country')) . "'," .
		"dsZip='" . escape_string(getpost('zip')) . "'," .
		"dsAction=" . escape_string(getpost('dsAction')) . "," .
		"dsEmailHeader='" . escape_string(str_replace(array('<br>', '<br/>', '<br />'), '%nl%', getpost('dsEmailHeader'))) . "' " .
		"WHERE dsID=" . escape_string(getpost('dsID'));
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}elseif(getpost('act')=="doaddnew"){
	$sSQL = "INSERT INTO dropshipper (dsEmail,dsName,dsAddress,dsCity,dsState,dsCountry,dsZip,dsAction,dsEmailHeader) VALUES (" .
		"'" . escape_string(getpost('email')) . "'," .
		"'" . escape_string(getpost('name')) . "'," .
		"'" . escape_string(getpost('address')) . "'," .
		"'" . escape_string(getpost('city')) . "'," .
		"'" . escape_string(getpost('state')) . "'," .
		"'" . escape_string(getpost('country')) . "'," .
		"'" . escape_string(getpost('zip')) . "'," .
		"" . escape_string(getpost('dsAction')) . "," .
		"'" . escape_string(str_replace(array('<br>', '<br/>', '<br />'), '%nl%', getpost('dsEmailHeader'))) . "')";
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}elseif(getpost('act')=="delete"){
	$sSQL = "DELETE FROM dropshipper WHERE dsID=" . getpost('id');
	ect_query($sSQL) or ect_error();
	$sSQL = "UPDATE products SET pDropship=0 WHERE pDropship=" . getpost('id');
	ect_query($sSQL) or ect_error();
	$dorefresh=TRUE;
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="2; url=admindropship.php">';
?>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
                        <?php print $yyNoAuto?> <a href="admindropship.php"><strong><?php print $yyClkHer?></strong></a>.<br />
                        <br /><br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
      </table>
<?php
}elseif(getpost('act')=="modify" || getpost('act')=="addnew"){
	if(getpost('act')=="modify"){
		$dsID=getpost('id');
		$sSQL = "SELECT dsName,dsAddress,dsCity,dsState,dsZip,dsCountry,dsEmail,dsAction,dsEmailHeader FROM dropshipper WHERE dsID=" . $dsID;
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			$dsName = $rs["dsName"];
			$dsAddress = $rs["dsAddress"];
			$dsCity = $rs["dsCity"];
			$dsState = $rs["dsState"];
			$dsZip = $rs["dsZip"];
			$dsCountry = $rs["dsCountry"];
			$dsEmail = $rs["dsEmail"];
			$dsAction = $rs["dsAction"];
			$dsEmailHeader = $rs['dsEmailHeader'];
		}
		ect_free_result($result);
	}else{
		$dsName = "";
		$dsAddress = "";
		$dsCity = "";
		$dsState = "";
		$dsZip = "";
		$dsCountry = "";
		$dsEmail = "";
		$dsAction = 0;
		$dsEmailHeader='';
	}
?>
<script type="text/javascript">
<!--
function checkform(frm){
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
return (true);
}
//-->
</script>
	  <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
        <tr> 
          <td width="100%">
		    <form method="post" action="admindropship.php" onsubmit="return checkform(this)">
		<?php	if(getpost('act')=="modify"){ ?>
			<input type="hidden" name="act" value="domodify" />
		<?php	}else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
		<?php	} ?>
			<input type="hidden" name="dsID" value="<?php print $dsID?>" />
			  <table width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
				  <td width="100%" align="center" colspan="4"><strong><?php print $yyDSAdm?></strong><br /></td>
				</tr>
				<tr>
				  <td width="20%" align="right"><strong><?php print $redasterix.$yyName?>:</strong></td>
				  <td width="30%" align="left"><input type="text" name="name" size="20" value="<?php print $dsName?>" /></td>
				  <td width="20%" align="right"><strong><?php print $redasterix.$yyEmail?>:</strong></td>
				  <td width="30%" align="left"><input type="text" name="email" size="25" value="<?php print $dsEmail?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyAddress?>:</strong></td>
				  <td align="left"><input type="text" name="address" size="20" value="<?php print $dsAddress?>" /></td>
				  <td align="right"><strong><?php print $redasterix.$yyCity?>:</strong></td>
				  <td align="left"><input type="text" name="city" size="20" value="<?php print $dsCity?>" /></td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyState?>:</strong></td>
				  <td align="left"><input type="text" name="state" size="20" value="<?php print $dsState?>" /></td>
				  <td align="right"><strong><?php print $redasterix.$yyCountry?>:</strong></td>
				  <td align="left"><select name="country" size="1">
<?php
function show_countries($tcountry){
	$sSQL = 'SELECT countryName FROM countries ORDER BY countryOrder DESC, countryName';
	$result=ect_query($sSQL) or ect_error();
	while($rs=ect_fetch_assoc($result)){
		print "<option value='" . htmlspecials($rs['countryName']) . "'";
		if($tcountry==$rs['countryName'])
			print ' selected="selected"';
		print '>' . $rs['countryName'] . "</option>\n";
	}
	ect_free_result($result);
}
show_countries($dsCountry);
?>
					</select>
				  </td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $redasterix.$yyZip?>:</strong></td>
				  <td align="left"><input type="text" name="zip" size="10" value="<?php print $dsZip?>" /></td>
				  <td align="right"><strong><?php print $yyActns?>:</strong></td>
				  <td align="left"><select name="dsAction" size="1">
					<option value="0"><?php print $yyNoAct?></option>
					<option value="1"<?php if($dsAction==1) print ' selected="selected"'?>><?php print $yySendEM?></option>
					</select>
				  </td>
				</tr>
				<tr>
				  <td align="right"><strong><?php print $yyEmlHea?>:</strong></td>
				  <td align="left" colspan="3"><input type="text" name="dsEmailHeader" size="60" value="<?php print str_replace('"', '&quot;', $dsEmailHeader)?>" /></td>
				</tr>
				<tr>
				  <td width="100%" colspan="4">&nbsp;<br />
					<span style="font-size:10px"><ul><li><?php print $yyDSInf?></li><li><?php print $yyDSIn2?></li></ul></span>
				  </td>
				</tr>
				<tr>
				  <td width="50%" align="center" colspan="4"><input type="submit" value="<?php print $yySubmit?>" /> <input type="reset" value="<?php print $yyReset?>" /> </td>
				</tr>
			  </table>
			</form>
		  </td>
        </tr>
      </table>
<?php
}else{
	$thetime=time() + ($dateadjust*60*60);
	if(getpost('sd')!='')
		$sd = getpost('sd');
	elseif(getget('sd')!='')
		$sd = getget('sd');
	else
		$sd = date($admindatestr, mktime(0, 0, 0, date("m",$thetime), 1, date("Y",$thetime)));
	if(getpost('ed')!='')
		$ed = getpost('ed');
	elseif(getget('ed')!='')
		$ed = getget('ed');
	else
		$ed = date($admindatestr, $thetime);
	$sd = parsedate($sd);
	$ed = parsedate($ed);
	if($sd > $ed) $ed = $sd;
?>
<script type="text/javascript">
<!--
function modrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.submit();
}
function newrec(id) {
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.submit();
}
function delrec(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.submit();
}
}
// -->
</script>
<?php		if(! $success){ ?>
<p style="text-align:center"><?php print '<span style="color:#FF0000">' . $errmsg . '</span>' ?></p>
<?php		} ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="2">
			  <tr>
				<td width="100%" align="center" colspan="6"><h2><?php print $yyDSAdm?></h2></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="6" align="center">
					<form method="post" action="admindropship.php">
				<strong><?php print $yyAffBet?>:</strong> <input type="text" size="12" name="sd" value="<?php print date($admindatestr, $sd)?>" /> <strong><?php print $yyAnd?>:</strong> <input type="text" size="12" name="ed" value="<?php print date($admindatestr, $ed)?>" /> <input type="submit" value="Go" /><br />&nbsp;
					</form>
				</td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="6" align="center">
					<form method="post" action="admindropship.php">
				<p><strong><?php print $yyAffFrm?>:</strong> <select name="sd" size="1"><?php
					$thetime = time() + ($dateadjust*60*60);
					$dayToday = date("d",$thetime);
					$monthToday = date("m",$thetime);
					$yearToday = date("Y",$thetime);
					for($index=$dayToday; $index > 0; $index--){
						$thedate = mktime(0, 0, 0, $monthToday, $index, $yearToday);
						$thedatestr = date($admindatestr, $thedate);
						print "<option value='" . $thedatestr . "'";
						if($thedate==$sd) print " selected";
						print ">" . $thedatestr . "</option>\n";
					}
					for($index=1; $index<=12; $index++){
						$thedate = mktime(0,0,0,$monthToday-$index,1,$yearToday);
						$thedatestr = date($admindatestr, $thedate);
						print "<option value='" . $thedatestr . "'";
						if($thedate==$sd) print " selected";
						print ">" . $thedatestr . "</option>\n";
					}
				?></select> <strong><?php print $yyTo?>:</strong> <select name="ed" size="1"><?php
					$dayToday = date("d",$thetime);
					$monthToday = date("m",$thetime);
					$yearToday = date("Y",$thetime);
					for($index=$dayToday; $index > 0; $index--){
						$thedate = mktime(0, 0, 0, $monthToday, $index, $yearToday);
						$thedatestr = date($admindatestr, $thedate);
						print "<option value='" . $thedatestr . "'";
						if($thedate==$ed) print " selected";
						print ">" . $thedatestr . "</option>\n";
					}
					for($index=1; $index<=12; $index++){
						$thedate = mktime(0,0,0,$monthToday-$index,1,$yearToday);
						$thedatestr = date($admindatestr, $thedate);
						print "<option value='" . $thedatestr . "'";
						if($thedate==$ed) print " selected";
						print ">" . $thedatestr . "</option>\n";
					}
				?></select> <input type="submit" value="Go" /><br />&nbsp;</p>
					</form>
				</td>
			  </tr>
		  <form name="mainform" method="post" action="admindropship.php">
			<input type="hidden" name="id" value="xxx" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="ed" value="<?php print date($admindatestr, $ed)?>" />
			<input type="hidden" name="sd" value="<?php print date($admindatestr, $sd)?>" />
            <table width="100%" class="stackable admin-table-a sta-white">
				<tr>
				  <th class="minicell"><?php print $yyID?></th>
				  <th class="maincell"><?php print $yyName?></th>
				  <th class="maincell"><?php print $yyEmail?></th>
				  <th class="aright"><?php print $yyTotSal?></th>
				  <th class="minicell"><?php print $yyModify?></th>
				  <th class="minicell"><?php print $yyDelete?></th>
				</tr>
<?php
	$sSQL = "SELECT dsID,dsName,dsEmail FROM dropshipper ORDER BY dsName";
	$alldata=ect_query($sSQL) or ect_error();
	if(ect_num_rows($alldata)==0){
?>
				<tr>
				  <td width="100%" align="center" colspan="6"><br />&nbsp;<br /><strong><?php print $yyNoAff?></strong><br />&nbsp;</td>
				</tr>
<?php
	}else{
		$bgcolor='';
		while($rs=ect_fetch_assoc($alldata)){
			$sSQL = "SELECT SUM(cartProdPrice*cartQuantity) AS sumSale FROM cart INNER JOIN products ON cart.cartProdID=products.pID WHERE pDropship=" . $rs["dsID"] . " AND cartCompleted=1 AND cartDateAdded BETWEEN '" . date("Y-m-d", $sd) . "' AND '" . date("Y-m-d", $ed) . " 23:59:59'";
			$alldata2=ect_query($sSQL) or ect_error();
			$rs2=ect_fetch_assoc($alldata2);
			ect_free_result($alldata2);
			if(! is_numeric($rs2['sumSale'])) $rs2['sumSale']=0;
			$sSQL = "SELECT SUM(coPriceDiff*cartQuantity) AS sumSale FROM cartoptions INNER JOIN cart ON cartoptions.coCartID=cart.cartID INNER JOIN products ON cart.cartProdID=products.pID WHERE pDropship=" . $rs["dsID"] . " AND cartCompleted=1 AND cartDateAdded BETWEEN '" . date("Y-m-d", $sd) . "' AND '" . date("Y-m-d", $ed) . " 23:59:59'";
			$alldata3=ect_query($sSQL) or ect_error();
			$rs3=ect_fetch_assoc($alldata3);
			if(is_numeric($rs3['sumSale'])) $rs2['sumSale']+=$rs3['sumSale'];
			ect_free_result($alldata3);
			if(@$bgcolor=='altdark') $bgcolor='altlight'; else $bgcolor='altdark';
?>
				<tr class="<?php print $bgcolor?>">
				  <td class="minicell"><?php print $rs["dsID"]?></td>
				  <td><?php print $rs["dsName"]?></td>
				  <td><a href="mailto:<?php print $rs["dsEmail"]?>"><?php print $rs["dsEmail"]?></a></td>
				  <td class="aright"><?php if($rs2['sumSale']==0) print "-"; else print FormatEuroCurrency($rs2["sumSale"])?></td>
				  <td class="minicell"><input type="button" value="Modify" onclick="modrec('<?php print $rs["dsID"]?>')" /></td>
				  <td class="minicell"><input type="button" value="Delete" onclick="delrec('<?php print $rs["dsID"]?>')" /></td>
				</tr><?php
		}
	}
	ect_free_result($alldata);
?>
				<tr> 
				  <td width="100%" colspan="6" align="center"><br /><input type="button" value="<?php print $yyAddNew?>" onclick="newrec()" /><br />&nbsp;</td>
				</tr>
				<tr> 
				  <td width="100%" colspan="6" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br /><br />&nbsp;</td>
				</tr>
			  </form>
			</table>
<?php
}
?>