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
if(getpost('act')=="domodify"){
	for($index=0; $index < 70; $index++){
		$statusid=trim(@$_POST["statusid" . $index]);
		if($statusid!=''){
			$statPrivate = escape_string(getpost('privstatus' . $index));
			$statPublic = escape_string(getpost('pubstatus' . $index));
			if($statPublic=="") $statPublic = $statPrivate;
			$sSQL = "UPDATE orderstatus SET statPrivate='" . $statPrivate . "',statPublic='" . $statPublic . "'";
			if(@$_POST['emailstatus' . $index]=='1') $sSQL.=',emailstatus=1'; else $sSQL.=',emailstatus=0';
			for($index2=2; $index2 <= $adminlanguages+1; $index2++){
				if(($adminlangsettings & 64)==64) $sSQL.=",statPublic" . $index2 . "='" . escape_string(getpost('pubstatus' . $index . 'x' . $index2)) . "'";
			}
			$sSQL.=" WHERE statID=" . $statusid;
			ect_query($sSQL) or ect_error();
		}
	}
	print '<meta http-equiv="refresh" content="3; url=admin.php">';
}
?>
<script type="text/javascript">
<!--
function formvalidator(theForm){
for(index=0;index<=3;index++){
theelm=eval('theForm.privstatus'+index);
if(theelm.value==""){
alert("Please enter a value in the field \"Private Text (Status " + (index+1) + ")\".");
theelm.focus();
return (false);
}
}
return (true);
}
//-->
</script>
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
<?php	if(getpost('act')=="domodify" && $success){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><strong><?php print $yyUpdSuc?></strong><br /><br /><?php print $yyNowFrd?><br /><br />
				<?php print $yyNoAuto?> <a href="admin.php"><strong><?php print $yyClkHer?></strong></a>.<br /><br />&nbsp;
                </td>
			  </tr>
			</table></td>
        </tr>
<?php	}elseif(getpost('act')=="domodify"){ ?>
        <tr>
          <td width="100%">
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="2" align="center"><br /><span style="color:#FF0000;font-weight:bold"><?php print $yyOpFai?></span><br /><br /><?php print $errmsg?><br /><br />
				<a href="javascript:history.go(-1)"><strong><?php print $yyClkBac?></strong></a></td>
			  </tr>
			</table></td>
        </tr>
<?php	}else{
			if(($adminlangsettings & 64) != 64) $numcols=6; else $numcols=6+$adminlanguages; ?>
        <tr>
          <td width="100%" align="center">
		  <form name="mainform" method="post" action="adminordstatus.php" onsubmit="return formvalidator(this)">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="domodify" />
            <table width="500" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="<?php print $numcols?>" align="center"><br /><strong><?php print $yyOSAdm?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
				<td align="center" valign="top" width="50"><strong>&nbsp;</strong></td>
				<td align="center" valign="top"><strong>&nbsp;</strong></td>
				<td align="center" valign="top"><strong><?php print $yyPrTxt?></strong></td>
				<td align="center" valign="top"><strong><?php print $yyPubTxt?></strong></td>
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 64)==64) print '<td align="center" valign="top"><strong>' . $yyPubTxt . " " . $index . '</strong></td>';
			} ?>
				<td align="center" valign="top"><strong><?php print str_replace(' ','&nbsp;',$yySendEM)?></strong></td>
				<td align="center" valign="top" width="50"><strong>&nbsp;</strong></td>
			  </tr>
<?php
	$sSQL = "SELECT statID,statPrivate,statPublic,statPublic2,statPublic3,emailstatus FROM orderstatus ORDER BY statID";
	$result=ect_query($sSQL) or ect_error();
	$rowcounter=0;
	while($rs=ect_fetch_assoc($result)){
		if($rs["statID"]==4){ ?>
			  <tr> 
                <td width="100%" colspan="<?php print $numcols?>" align="center"><span style="font-size:10px"><?php print $yyOSExp1?></span></td>
			  </tr>
<?php	} ?>
			  <tr>
				<td align="center" valign="top"><strong>&nbsp;&nbsp;&nbsp;&nbsp;</strong></td>
				<td align="right"><input type="hidden" name="statusid<?php print $rowcounter?>" value="<?php print $rs["statID"] ?>" /><?php print $yyStatus?>&nbsp;<?php print $rowcounter?>:</td>
				<td align="center"><input type="text" size="20" name="privstatus<?php print $rowcounter?>" value="<?php print htmlspecials(trim($rs["statPrivate"])) ?>" /></td>
				<td align="center"><input type="text" size="20" name="pubstatus<?php print $rowcounter?>" value="<?php print htmlspecials(trim($rs["statPublic"])) ?>" /></td>
<?php		for($index=2; $index <= $adminlanguages+1; $index++){
				if(($adminlangsettings & 64)==64) print '<td align="center"><input type="text" size="20" name="pubstatus' . $rowcounter . "x" . $index . '" value="' . htmlspecials(trim($rs["statPublic" . $index])) . '" /></td>';
			} ?>
				<td align="center"><input type="checkbox" name="emailstatus<?php print $rowcounter?>" value="1" <?php if($rs['emailstatus']!=0) print 'checked="checked" '?>/></td>
				<td align="center" valign="top"><strong>&nbsp;&nbsp;&nbsp;&nbsp;</strong></td>
			  </tr>
<?php	$rowcounter++;
	}
	ect_free_result($result); ?>
			  <tr> 
                <td width="100%" colspan="<?php print $numcols?>" align="center"><input type="submit" value="<?php print $yySubmit?>" /></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="<?php print $numcols?>" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br />&nbsp;</td>
			  </tr>
            </table>
		  </form>
		  </td>
        </tr>
<?php	} ?>
      </table>