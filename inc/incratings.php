<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$dateadjust=='') $dateadjust=0;
if(@$dateformatstr=='') $dateformatstr = 'm/d/Y';
$admindatestr='Y-m-d';
if(@$admindateformat=='') $admindateformat=0;
if($admindateformat==1)
	$admindatestr='m/d/Y';
elseif($admindateformat==2)
	$admindatestr='d/m/Y';
if(@$storesessionvalue=='') $storesessionvalue='virtualstore'.time();
$themask = 'yyyy-mm-dd';
if($admindateformat==1)
	$themask='mm/dd/yyyy';
elseif($admindateformat==2)
	$themask='dd/mm/yyyy';
$success=TRUE;
$alreadygotadmin = getadminsettings();
$dorefresh=FALSE;
$rtprodid = '';
if(getpost('posted')=='1'){
	if(getpost('act')=='delete'){
		$sSQL = "SELECT rtProdID FROM ratings WHERE rtID=" . getpost('id');
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)) $rtprodid = $rs['rtProdID'];
		ect_free_result($result);
		$sSQL = "DELETE FROM ratings WHERE rtID=" . getpost('id');
		ect_query($sSQL) or ect_error();
		$dorefresh=TRUE;
	}elseif(getpost('act')=='quickupdate'){
		foreach(@$_POST as $objItem => $objValue){
			if(substr($objItem, 0, 4)=='pra_'){
				$theid = str_replace('ect_dot_xzq','.',substr($objItem, 4));
				$theval = trim(unstripslashes($objValue));
				$pract = getpost('pract');
				$sSQL = '';
				if($pract=='del')
					$sSQL = 'DELETE FROM ratings';
				elseif($pract=='app')
					$sSQL = 'UPDATE ratings SET rtApproved=' . (@$_POST['prb_' . $theid]=='1'?'1':'0');
				elseif($pract=='pby')
					$sSQL = "UPDATE ratings SET rtPosterName='" . escape_string($theval) . "'";
				elseif($pract=='pid')
					$sSQL = "UPDATE ratings SET rtProdID='" . escape_string($theval) . "'";
				elseif($pract=='hed')
					$sSQL = "UPDATE ratings SET rtHeader='" . escape_string($theval) . "'";
				elseif($pract=='rat')
					$sSQL = 'UPDATE ratings SET rtRating=' . $theval;
				if($sSQL!=''){
					$sSQL.=' WHERE rtID='.$theid;
					ect_query($sSQL) or ect_error();
				}
			}
		}
		if($success) $dorefresh=TRUE; else $errmsg = $yyPOErr . '<br />' . $errmsg;
	}elseif(getpost('act')=='domodify'){
		$sSQL = "UPDATE ratings SET " .
			"rtProdID='" . escape_string(getpost('rtprodid')) . "'," .
			"rtRating=" . (getpost('rtrating')!='' ? getpost('rtrating') : '1')  . ',' .
			"rtApproved=" . (getpost('rtapproved')=='yes' ? 1 : 0) . "," .
			'rtLanguage=' . (getpost('rtlanguage')!='' ? getpost('rtlanguage') : 0) . "," .
			"rtIPAddress='" . getpost('rtipaddress') . "'," .
			"rtPosterName='" . escape_string(getpost('rtpostername')) . "'," .
			"rtPosterEmail='" . escape_string(getpost('rtposteremail')) . "'," .
			"rtDate='" . date('Y-m-d', parsedate(getpost('rtdate'))) . "'," .
			"rtHeader='" . escape_string(getpost('rtheader')) . "'," .
			"rtComments='" . escape_string(getpost('rtcomments')) . "' " .
			"WHERE rtID=" . getpost('id');
		ect_query($sSQL) or ect_error();
		$rtprodid = getpost('rtprodid');
		$dorefresh=TRUE;
	}elseif(getpost('act')=='doaddnew'){
		$sSQL = "INSERT INTO ratings (rtProdID,rtRating,rtDate,rtApproved,rtLanguage,rtIPAddress,rtPosterName,rtPosterEmail,rtHeader,rtComments) VALUES (" .
			"'".escape_string(getpost('rtprodid'))."'," .
			getpost('rtrating')."," .
			"'" . date('Y-m-d', parsedate(getpost('rtdate'))) . "'," .
			(getpost('rtapproved')=='yes' ? 1 : 0) . ',' .
			(getpost('rtlanguage')!='' ? getpost('rtlanguage') : 0) . ',' .
			"'".getpost('rtipaddress')."'," .
			"'".escape_string(getpost('rtpostername'))."'," .
			"'".escape_string(getpost('rtposteremail'))."'," .
			"'".escape_string(getpost('rtheader'))."'," .
			"'".escape_string(getpost('rtcomments'))."')";
		ect_query($sSQL) or ect_error();
		$rtprodid = getpost('rtprodid');
		$dorefresh=TRUE;
	}elseif(getpost('act')=='updateratings'){
		print '<p align="center">' . $yyUpdat . '...</p>';
		flush();
		$sSQL = "SELECT rtProdID,COUNT(*) AS numratings,SUM(rtRating) AS totrating FROM ratings WHERE rtApproved<>0 GROUP BY rtProdID";
		$result=ect_query($sSQL) or ect_error();
		while($rs=ect_fetch_assoc($result)){
			$numratings = $rs['numratings'];
			$totrating = $rs['totrating'];
			if(is_null($numratings)) $numratings=0;
			if(is_null($totrating)) $totrating=0;
			$sSQL = "UPDATE products SET pNumRatings=".$numratings.",pTotRating=".$totrating." WHERE pID='" . escape_string($rs['rtProdID']) . "'";
			ect_query($sSQL) or ect_error();
		}
		ect_free_result($result);
		$dorefresh=TRUE;
	}
}elseif(getget('approve')=='yes'){
	$sSQL="UPDATE ratings SET rtApproved=1 WHERE rtID=" . getpost('id');
	ect_query($sSQL) or ect_error();
	$sSQL = "SELECT rtProdID FROM ratings WHERE rtID=" . getpost('id');
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $rtprodid = $rs['rtProdID'];
	ect_free_result($result);
}elseif(getget('unapprove')=='yes'){
	$sSQL="UPDATE ratings SET rtApproved=0 WHERE rtID=" . getpost('id');
	ect_query($sSQL) or ect_error();
	$sSQL = "SELECT rtProdID FROM ratings WHERE rtID=" . getpost('id');
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)) $rtprodid = $rs['rtProdID'];
	ect_free_result($result);
}
if($rtprodid!=''){
	$numratings = 0;
	$totrating = 0;
	$sSQL = "SELECT COUNT(*) AS numratings, SUM(rtRating) AS totrating FROM ratings WHERE rtApproved<>0 AND rtProdID='" . escape_string($rtprodid) . "'";
	$result=ect_query($sSQL) or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$numratings = $rs['numratings'];
		$totrating = $rs['totrating'];
		if(is_null($numratings) || is_null($totrating)){
			$numratings = 0;
			$totrating = 0;
		}
	}
	ect_free_result($result);
	$sSQL = "UPDATE products SET pNumRatings='".$numratings."',pTotRating='".$totrating."' WHERE pID='" . escape_string($rtprodid) . "'";
	ect_query($sSQL) or ect_error();
}
if($dorefresh){
	print '<meta http-equiv="refresh" content="1; url=adminratings.php';
	print '?stext=' . urlencode(getpost('stext')) . '&mindate=' . urlencode(getpost('mindate')) . '&maxdate=' . urlencode(getpost('maxdate')) . '&stype=' . getpost('stype') . '&approved=' . getpost('approved') . '&pg=' . getpost('pg');
	print '">';
}
if(getpost('act')=='modify' || getpost('act')=='addnew' || getpost('act')=='clone'){ ?>
<script type="text/javascript" src="popcalendar.js"></script>
<script type="text/javascript">
try{languagetext('<?php print @$adminlang?>');}catch(err){}
function formvalidator(theForm){
  return (true);
}
</script>
<?php	if(getpost('act')=='modify' || getpost('act')=='clone'){
			$rtID = getpost('id');
			$sSQL="SELECT rtProdID,rtRating,rtDate,rtApproved,rtLanguage,rtIPAddress,rtPosterName,rtPosterEmail,rtHeader,rtComments FROM ratings WHERE rtID=" . $rtID;
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				$rtProdID = $rs['rtProdID'];
				$rtRating = $rs['rtRating'];
				$rtDate = date($admindatestr, strtotime($rs['rtDate']));
				$rtApproved = $rs['rtApproved'];
				$rtLanguage = $rs['rtLanguage'];
				$rtIPAddress = $rs['rtIPAddress'];
				$rtPosterName = $rs['rtPosterName'];
				$rtPosterEmail = $rs['rtPosterEmail'];
				$rtHeader = $rs['rtHeader'];
				$rtComments = $rs['rtComments'];
			}
			ect_free_result($result);
			$sSQL = "SELECT pName FROM products WHERE pID='" . escape_string($rtProdID) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)) $pName = $rs['pName']; else $pName = 'Rating Not Found';
			ect_free_result($result);
		}else{
			$rtID = '';
			$rtProdID = '';
			$rtRating = 0;
			$rtDate = date($admindatestr, time() + ($dateadjust*60*60));
			$rtApproved = 0;
			$rtLanguage = 0;
			$rtIPAddress = getipaddress();
			$rtPosterName = '';
			$rtPosterEmail = '';
			$rtHeader = '';
			$rtComments = '';
			$pName = '';
		}
?>
	<form name="mainform" method="post" action="adminratings.php" onsubmit="return formvalidator(this)">
      <table border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
		<tr>
		  <td width="100%">
			<input type="hidden" name="posted" value="1" />
			<?php	if(getpost('act')=='modify'){ ?>
			<input type="hidden" name="act" value="domodify" />
			<input type="hidden" name="id" value="<?php print $rtID?>" />
			<?php	}else{ ?>
			<input type="hidden" name="act" value="doaddnew" />
			<?php	}
				writehiddenvar('stock', getpost('stock'));
				writehiddenvar('stext', getpost('stext'));
				writehiddenvar('mindate', getpost('mindate'));
				writehiddenvar('maxdate', getpost('maxdate'));
				writehiddenvar('approved', getpost('approved'));
				writehiddenvar('stype', getpost('stype'));
				writehiddenvar('pg', getpost('pg')); ?>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
			  <tr> 
                <td width="100%" colspan="4" align="center"><strong><?php print (getpost('act')=='clone'?$yyClone.': ':(getpost('act')=='modify'?$yyModify.': ':'')) . $yyMPRRev?></strong><br />&nbsp;</td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $redasterix.$yyPrId?>:</td><td><input type="text" name="rtprodid" size="15" value="<?php print $rtProdID?>" /></td>
			    <td align="right"><?php print $redasterix.$yyRatn?>:</td><td><select size="1" name="rtrating"><option value=""><?php print $yySelect?></option><?php
						for($rowcounter=0; $rowcounter<=10; $rowcounter++){
							print '<option value="'.$rowcounter.'"';
							if($rowcounter==$rtRating) print ' selected="selected"';
							print '>'.($rowcounter/2).' '.$yyStars.'</option>';
						} ?></select></td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $redasterix.$yyRatDat?>:</td><td><input type="text" name="rtdate" size="20" value="<?php print $rtDate?>" /> <input type="button" onclick="popUpCalendar(this, document.forms.mainform.rtdate, '<?php print $themask?>', -205)" value="DP" /></td>
			    <td align="right"><?php print $redasterix.$yyAppd?>:</td><td><select size="1" name="rtapproved"><option value="no"><?php print $yyNo?></option>"
				  <option value="yes"<?php if($rtApproved!=0) print ' selected="selected"'?>><?php print $yyYes?></option></select></td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $redasterix.$yyPostBy?>:</td><td><input type="text" name="rtpostername" size="25" value="<?php print htmlspecials($rtPosterName)?>" /></td>
			    <td align="right"><?php print $redasterix.$yyIPAdd?>:</td><td><input type="text" name="rtipaddress" size="25" value="<?php print htmlspecials($rtIPAddress)?>" /></td>
			  </tr>
			  <tr>
			    <td align="right"><?php print $redasterix.$yyHeadi?>:</td><td<?php print ($adminlanguages>0?'':' colspan="3"')?>><input type="text" name="rtheader" size="35" value="<?php print htmlspecials($rtHeader)?>" /></td>
<?php			if($adminlanguages>0){ ?>
			    <td align="right"><?php print $redasterix.$yyLanID?>:</td><td><select name="rtlanguage" size="1">
					<option value="0">1</option>
					<option value="1"<?php if($rtLanguage==1) print ' selected="selected"'?>>2</option>
<?php				if($adminlanguages>1){ ?>
					<option value="2"<?php if($rtLanguage==2) print ' selected="selected"'?>>3</option>
<?php				} ?>
					</select></td>
<?php			} ?>
			  </tr>
			  <tr>
			    <td align="right"><?php print $redasterix.$yyComme?>:</td><td colspan="3"><textarea name="rtcomments" cols="65" rows="8" wrap="virtual"><?php print htmlspecials($rtComments)?></textarea></td>
			  </tr>
			  <tr> 
                <td width="100%" colspan="4">
				  <p>&nbsp;</p>
                  <p align="center"><input type="submit" value="<?php print $yySubmit?>" />&nbsp;&nbsp;<input type="reset" value="<?php print $yyReset?>" /></p>
                </td>
			  </tr>
			</table>
		  </td>
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
                        <?php print $yyNoAuto?> <a href="adminratings.php<?php
							print '?rid=' . getpost('rid') . '&stock=' . getpost('stock') . '&stext=' . urlencode(getpost('stext')) . '&mindate=' . getpost('mindate') . '&maxdate=' . getpost('maxdate') . '&stype=' . getpost('stype') . '&approved=' . getpost('approved') . '&pg=' . getpost('pg');
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
	$jscript='';
	$pract=@$_COOKIE['practrat'];
	$stext=getrequest('stext');
	$stype=getrequest('stype');
	$approved=getrequest('approved');
	$modclone=@$_COOKIE['modclone']; ?>
<script type="text/javascript" src="popcalendar.js"></script>
<script type="text/javascript">
<!--
try{languagetext('<?php print @$adminlang?>');}catch(err){}
function setCookie(c_name,value,expiredays){
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}
function mr(id){
	document.mainform.action="adminratings.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "modify";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function cr(id){
	document.mainform.action="adminratings.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "clone";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function aprec(id){
	document.mainform.action="adminratings.php?approve=yes";
	document.mainform.id.value = id;
	document.mainform.act.value = "search";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function newrec(id){
	document.mainform.action="adminratings.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "addnew";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function dscnts(id){
	document.mainform.action="adminratings.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "discounts";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function startsearch(){
	document.mainform.action="adminratings.php";
	document.mainform.act.value = "search";
	document.mainform.stock.value = "";
	document.mainform.posted.value = "";
	document.mainform.submit();
}
function quickupdate(){
	if(document.mainform.pract.value=="del"){
		if(!confirm("<?php print jscheck($yyConDel)?>\n"))
			return;
	}
	document.mainform.action="adminratings.php";
	document.mainform.act.value = "quickupdate";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
function dr(id){
if(confirm("<?php print jscheck($yyConDel)?>\n")){
	document.mainform.action="adminratings.php";
	document.mainform.id.value = id;
	document.mainform.act.value = "delete";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
}
function updateratings(){
if(confirm("<?php print jscheck($yySureCa)?>\n")){
	document.mainform.action="adminratings.php";
	document.mainform.act.value = "updateratings";
	document.mainform.posted.value = "1";
	document.mainform.submit();
}
}
function changepract(obj){
	setCookie('practrat',obj[obj.selectedIndex].value,600);
	startsearch();
}
function checkboxes(docheck){
	if(document.getElementById("resultcounter")){
		maxitems=document.getElementById("resultcounter").value;
		for(index=0;index<maxitems;index++){
			document.getElementById("chkbx"+index).checked=docheck;
		}
	}
}
function changemodclone(modclone){
	setCookie('modclone',modclone[modclone.selectedIndex].value,600);
	startsearch();
}
// -->
</script>
<h2><?php print $yyAdmRat?></h2>
		<form name="mainform" method="post" action="adminratings.php">
		  <td width="100%">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="act" value="xxxxx" />
			<input type="hidden" name="stock" value="" />
			<input type="hidden" name="id" value="xxxxx" />
			<input type="hidden" name="pg" value="<?php print (getpost('act')=='search' ? '1' : getget('pg'))?>" />
			<table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3">
			  <tr> 
				<td class="cobhl" width="20%" align="right"><?php print $yySrchFr?>:</td>
				<td class="cobll" width="30%"><input type="text" name="stext" size="20" value="<?php print @$_REQUEST['stext']?>" /></td>
				<td class="cobhl" width="20%" align="right"><?php print $yyDatRan?>:</td>
				<td class="cobll" width="30%"><input type="text" name="mindate" size="10" value="<?php print @$_REQUEST['mindate']?>" />&nbsp;<input type="button" onclick="popUpCalendar(this, document.forms.mainform.mindate, '<?php print $themask?>', -205)" value="DP" />&nbsp;<?php print $yyTo?>:&nbsp;<input type="text" name="maxdate" size="10" value="<?php print @$_REQUEST['maxdate']?>" />&nbsp;<input type="button" onclick="popUpCalendar(this, document.forms.mainform.maxdate, '<?php print $themask?>', -205)" value="DP" /></td>
			  </tr>
			  <tr>
				<td class="cobhl"align="right"><?php print $yySrchTp?>:</td>
				<td class="cobll"><select name="stype" size="1">
					<option value=""><?php print $yySrchAl?></option>
					<option value="any"<?php if(@$_REQUEST['stype']=='any') print ' selected="selected"'?>><?php print $yySrchAn?></option>
					<option value="exact"<?php if(@$_REQUEST['stype']=='exact') print ' selected="selected"'?>><?php print $yySrchEx?></option>
					</select>
				</td>
				<td class="cobhl"align="right"><?php print $yyAppd?>:</td>
				<td class="cobll">
				  <select name="approved" size="1">
				  <option value="2"<?php if(@$_REQUEST['approved']=='2') print ' selected="selected"'?>><?php print $yyAll?></option>
				  <option value=""<?php if(@$_REQUEST['approved']=='') print ' selected="selected"'?>><?php print $yyNotApp?></option>
				  <option value="1"<?php if(@$_REQUEST['approved']=='1') print ' selected="selected"'?>><?php print $yyAppd?></option>
				  </select>
				</td>
			  </tr>
			  <tr>
				<td class="cobhl"><?php
					if($pract=='del' || $pract=='app'){ ?>
						<input type="button" value="<?php print $yyCheckA?>" onclick="checkboxes(true);" /> <input type="button" value="<?php print $yyUCheck?>" onclick="checkboxes(false);" />
<?php				}else
						print '&nbsp;' ?></td>
				<td class="cobll" colspan="3"><table width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
					  <td class="cobll" align="center"><input type="button" value="<?php print $yyLiRat?>" onclick="startsearch();" /> 
						<input type="button" value="<?php print $yyNewRat?>" onclick="newrec();" />
						<input type="button" value="<?php print $yyUpdPrR?>" onclick="updateratings();" />
					  </td>
					  <td class="cobll" height="26" width="20%" align="right">&nbsp;</td>
					</tr>
				  </table></td>
			  </tr>
			</table>
<br />
            <table width="100%" class="stackable admin-table-a sta-white">
<?php	function displayprodrow($xrs){
			global $yyAppro,$yyView,$yyClone,$yyDelete,$modclone,$admindatestr,$pract,$resultcounter,$yyStars;
			?><tr id="tr<?php print $resultcounter?>"<?php if($xrs['rtApproved']==0) print ' style="color:#FF0000"'?>><td class="minicell"><?php
				if($pract=='pby')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$xrs['rtID'].'" value="' . $xrs['rtPosterName'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='pid')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$xrs['rtID'].'" value="' . $xrs['rtProdID'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='hed')
					print '<input type="text" id="chkbx'.$resultcounter.'" size="18" name="pra_'.$xrs['rtID'].'" value="' . $xrs['rtHeader'] . '" tabindex="'.($resultcounter+1).'"/>';
				elseif($pract=='rat'){
					print '<select size="1" name="pra_'.$xrs['rtID'].'">';
					for($rowcounter=0; $rowcounter<=10; $rowcounter++){
							print '<option value="'.$rowcounter.'"';
							if($rowcounter==$xrs['rtRating']) print ' selected="selected"';
							print '>'.($rowcounter/2).' '.$yyStars.'</option>';
						}
					print '</select>';
				}elseif($pract=='app')
					print '<input type="hidden" name="pra_'.$xrs['rtID'].'" value="" /><input type="checkbox" id="chkbx'.$resultcounter.'" name="prb_'.$xrs['rtID'].'" value="1" tabindex="'.($resultcounter+1).'"' . ($xrs['rtApproved']?' checked="checked"':'') . '/>';
				elseif($pract=='del')
					print '<input type="checkbox" id="chkbx'.$resultcounter.'" name="pra_'.$xrs['rtID'].'" value="del" tabindex="'.($resultcounter+1).'"/>';
				else
					print '&nbsp;';
			?></td><td><?php
					print $xrs['rtProdID'];
			?></td><td><?php print '<a href="javascript:mr(' . $xrs['rtID'] . ')">' . htmlspecials($xrs['rtPosterName']) . '</a>';
			?></td><td><?php print htmlspecials($xrs['rtIPAddress']);
			?></td><td><?php print date($admindatestr, strtotime($xrs['rtDate']));
			?></td><td><?php
					print htmlspecials($xrs['rtHeader']);
			?></td><td class="minicell"><?php print $xrs['rtRating']/2;
			?></td><td class="minicell"><?php
					if($xrs['rtApproved']==0){
			?><input type="button" value="<?php print $yyAppro?>" onclick="aprec('<?php print str_replace(array("\\","'",'"'),array("\\\\","\'",'&quot;'),$xrs['rtID'])?>')" /><?php
					}else
						print '&nbsp;';
			?></td><td>-</td></tr><?php
			print "\r\n";
		}
		function displayheaderrow(){
			global $yyPrId,$yyPostBy,$yyIPAdd,$yyDateAd,$yyHeadi,$yyAppro,$yyModify,$yyDelete,$yyRatn,$pract,$yyAppd,$modclone; ?>
			<tr>
				<th class="minicell">
					<select name="pract" id="pract" size="1" onchange="changepract(this)">
					<option value="none">Quick Entry...</option>
					<option value="pby"<?php if($pract=='pby') print ' selected="selected"'?>><?php print $yyPostBy?></option>
					<option value="pid"<?php if($pract=='pid') print ' selected="selected"'?>><?php print $yyPrId?></option>
					<option value="hed"<?php if($pract=='hed') print ' selected="selected"'?>><?php print $yyHeadi?></option>
					<option value="rat"<?php if($pract=='rat') print ' selected="selected"'?>><?php print $yyRatn?></option>
					<option value="app"<?php if($pract=='app') print ' selected="selected"'?>><?php print $yyAppd?></option>
					<option value="" disabled="disabled">------------------</option>
					<option value="del"<?php if($pract=='del') print ' selected="selected"'?>><?php print $yyDelete?></option>
					</select></th>
				<th class="maincell"><?php print str_replace(' ','&nbsp;',$yyPrId)?></th>
				<th class="maincell"><?php print $yyPostBy?></th>
				<th class="maincell"><?php print str_replace(' ','&nbsp;',$yyIPAdd)?></th>
				<th class="maincell"><?php print str_replace(' ','&nbsp;',$yyDateAd)?></th>
				<th class="maincell"><?php print $yyHeadi?></th>
				<th class="minicell"><?php print $yyRatn?></th>
				<th class="minicell"><?php print $yyAppro?></th>
				<th class="minicell"><?php print $yyModify?></th>
			</tr>
<?php	}
		$whereand=' WHERE ';
		$sSQL = ' FROM ratings';
		if(trim(@$_REQUEST['approved'])!='2'){
			if(trim(@$_REQUEST['approved'])=='') $sSQL.=$whereand . 'rtApproved=0'; else $sSQL.=$whereand . 'rtApproved<>0';
			$whereand=' AND ';
		}
		$mindate = trim(@$_REQUEST['mindate']);
		$maxdate = trim(@$_REQUEST['maxdate']);
		if($mindate!='' || $maxdate!=''){
			if($mindate!='') $themindate = parsedate($mindate); else $themindate='';
			if($maxdate!='') $themaxdate = parsedate($maxdate); else $themaxdate='';
			if($themindate!='' && $themaxdate!=''){
				$sSQL.=$whereand . "rtDate BETWEEN '" . date('Y-m-d', $themindate) . "' AND '" . date('Y-m-d', $themaxdate) . "'";
				$whereand=" AND ";
			}elseif($themindate!=''){
				$sSQL.=$whereand . "rtDate >= '" . date('Y-m-d', $themindate) . "'";
				$whereand=" AND ";
			}elseif($themaxdate!=''){
				$sSQL.=$whereand . "rtDate <= '" . date('Y-m-d', $themaxdate) . "'";
				$whereand=" AND ";
			}
		}
		if(trim(@$_REQUEST['stext'])!=''){
			$Xstext = escape_string(@$_REQUEST['stext']);
			$aText = explode(' ',$Xstext);
			$aFields[0]='rtID';
			$aFields[1]='rtProdID';
			$aFields[2]='rtIPAddress';
			$aFields[3]='rtPosterName';
			$aFields[4]='rtPosterEmail';
			$aFields[5]='rtHeader';
			$aFields[6]='rtComments';
			if(@$_REQUEST['stype']=='exact'){
				$sSQL.=$whereand . "(rtID LIKE '%" . $Xstext . "%' OR rtProdID LIKE '%" . $Xstext . "%' OR rtIPAddress LIKE '%" . $Xstext . "%' OR rtPosterName LIKE '%" . $Xstext . "%' OR rtPosterEmail LIKE '%" . $Xstext . "%' OR rtHeader LIKE '%" . $Xstext . "%' OR rtComments LIKE '%" . $Xstext . "%') ";
				$whereand=' AND ';
			}else{
				$sJoin='AND ';
				if(@$_REQUEST['stype']=='any') $sJoin='OR ';
				$sSQL.=$whereand . '(';
				$whereand=' AND ';
				for($index=0; $index<=6; $index++){
					$sSQL.='(';
					$rowcounter=0;
					$arrelms=count($aText);
					foreach($aText as $theopt){
						if(is_array($theopt))$theopt=$theopt[0];
						$sSQL.=$aFields[$index] . " LIKE '%" . $theopt . "%' ";
						if(++$rowcounter < $arrelms) $sSQL.=$sJoin;
					}
					$sSQL.=') ';
					if($index < 6) $sSQL.='OR ';
				}
				$sSQL.=') ';
			}
		}
		$sSQL.=' ORDER BY rtDate DESC';
		if(@$adminproductsperpage=='') $adminproductsperpage=200;
		if(getget('pg')=='') $CurPage = 1; else $CurPage = (int)getget('pg');
		$tmpSQL = "SELECT COUNT(DISTINCT rtID) AS bar" . $sSQL;
		$sSQL = 'SELECT rtID,rtProdID,rtRating,rtDate,rtApproved,rtIPAddress,rtPosterName,rtPosterEmail,rtHeader' . $sSQL;
		$result=ect_query($tmpSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$iNumOfPages = ceil($rs['bar']/$adminproductsperpage);
		ect_free_result($result);
		$sSQL.=' LIMIT ' . ($adminproductsperpage*($CurPage-1)) . ', ' . $adminproductsperpage;
		$result=ect_query($sSQL) or ect_error();
		$resultcounter=0;
		if(ect_num_rows($result) > 0){
			$pblink = '<a class="ectlink" href="adminratings.php?stext=' . urlencode($stext) . '&stype=' . $stype . '&approved=' . $approved . '&mindate=' . $mindate . '&maxdate=' . $maxdate . '&pg=';
			if($iNumOfPages > 1) print '<tr><td colspan="9" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
			displayheaderrow();
			$addcomma='';
			while($rs=ect_fetch_assoc($result)){
				$jscript.='pa['.$resultcounter.']=[';
				displayprodrow($rs);
				$jscript.=$rs['rtID']."];\r\n";
				$addcomma=',';
				$resultcounter++;
			}
			if($iNumOfPages > 1) print '<tr><td colspan="9" align="center">' . writepagebar($CurPage,$iNumOfPages,$yyPrev,$yyNext,$pblink,FALSE) . '</td></tr>';
		}else
			print '<tr><td width="100%" colspan="9" align="center"><br />'.$yyItNone.'<br />&nbsp;</td></tr>';
		ect_free_result($result); ?>
			  <tr>
				<td align="center" style="white-space:nowrap"><?php if($resultcounter>0 && $pract!='' && $pract!='none') print '<input type="hidden" name="resultcounter" id="resultcounter" value="'.$resultcounter.'" /><input type="button" value="'.$yyUpdate.'" onclick="quickupdate()" /> <input type="reset" value="'.$yyReset.'" />'; else print '&nbsp;'?></td>
                <td width="100%" colspan="7" align="center"><br /><a href="admin.php"><strong><?php print $yyAdmHom?></strong></a><br /></td>
				<td>&nbsp;</td>
			  </tr>
            </table>
		  </form>
<script type="text/javascript">
/* <![CDATA[ */
var pa=[];
<?php print $jscript?>
for(var pidind in pa){
	var ttr=document.getElementById('tr'+pidind);
	ttr.cells[8].style.textAlign='center';
	ttr.cells[8].style.whiteSpace='nowrap';
	ttr.cells[8].innerHTML='<input type="button" value="M" style="width:30px" onclick="mr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyModify))?>" />&nbsp;' +
		'<input type="button" value="C" style="width:30px" onclick="cr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyClone))?>" />&nbsp;' +
		'<input type="button" value="X" style="width:30px" onclick="dr(\''+pa[pidind][0]+'\')" title="<?php print jsescape(htmlspecials($yyDelete))?>" />';
}
/* ]]> */
</script>
<?php
}
?>
