<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $alreadygotadmin,$thesessionid;
if(@$prodid==''){
	if(trim(@$explicitid)!='') $prodid=trim($explicitid); else $prodid=trim(@$_REQUEST['prod']);
}
if($prodid!=$giftcertificateid && $prodid!=$donationid) $prodid=$giftcertificateid;
$WSP=$OWSP='';
$TWSP='pPrice';
$iNumOfPages = 0;
if(@$dateadjust=='') $dateadjust=0;
$thesessionid=getsessionid();
$alreadygotadmin = getadminsettings();
get_wholesaleprice_sql();
if(@$_SESSION["clientLoginLevel"]!='') $minloglevel=$_SESSION["clientLoginLevel"]; else $minloglevel=0;
$validitem=TRUE;
if(getpost('posted')=='1'){
	$validitem = (is_numeric(getpost('amount')) && getpost('amount')!='');
	if($validitem) $validitem = (double)getpost('amount')>0;
	if($validitem){
		$prodname = ($prodid==$giftcertificateid?$GLOBALS['xxGifCtc']:$GLOBALS['xxDonat']);
		$sSQL = 'SELECT '.getlangid('pName',1)." FROM products WHERE pID='" . escape_string($prodid) . "'";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)) $prodname = $rs[getlangid('pName',1)];
		ect_free_result($result);
		$sSQL = 'INSERT INTO cart (cartSessionID,cartClientID,cartProdID,cartQuantity,cartCompleted,cartProdName,cartProdPrice,cartOrderID,cartDateAdded) VALUES (';
		$sSQL.="'" . escape_string($thesessionid) . "','" . (@$_SESSION['clientID']!='' ? escape_string($_SESSION['clientID']) : 0) . "','" . escape_string($prodid) . "',";
		$sSQL.="1,0,'" . escape_string($prodname) . "','" . escape_string(is_numeric(getpost('amount')) ? getpost('amount') : 10) . "',0,";
		$sSQL.="'" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "')";
		ect_query($sSQL) or ect_error();
		$cartid=ect_insert_id();
		if($prodid==$giftcertificateid){
			// Create GC id
			$gotunique=FALSE;
			srand((double)microtime()*1000000);
			while(! $gotunique){
				$sequence = getgcchar() . getgcchar() . rand(100000000, 999999999) . getgcchar();
				$sSQL = "SELECT gcID FROM giftcertificate WHERE gcID='" . $sequence . "'";
				$result=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result)==0) $gotunique = TRUE;
				ect_free_result($result);
			}
			$sSQL = 'INSERT INTO giftcertificate (gcID,gcTo,gcFrom,gcEmail,gcOrigAmount,gcRemaining,gcDateCreated,gcCartID,gcAuthorized,gcMessage) VALUES (';
			$sSQL.="'" . $sequence . "',";
			$sSQL.="'" . escape_string(getpost('toname')) . "',";
			$sSQL.="'" . escape_string(getpost('fromname')) . "',";
			$sSQL.="'" . escape_string(getpost('toemail')) . "',";
			$sSQL.="0,0,";
			$sSQL.="'" . date('Y-m-d H:i:s', time() + ($dateadjust*60*60)) . "',";
			$sSQL.=$cartid . ",0,";
			$sSQL.="'" . escape_string(str_replace(array("\r\n","\n"),'<br />',getpost('gcmessage'))) . "')";
			ect_query($sSQL) or ect_error();
		}else{
			if(getpost('fromname')!=''){
				$sSQL = "INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (".$cartid.",0,'".escape_string($GLOBALS['xxFrom']) . "','".escape_string(substr(getpost('fromname'),0,255))."',0,0)";
				ect_query($sSQL) or ect_error();
			}
			if(getpost('gcmessage')!=''){
				$sSQL = "INSERT INTO cartoptions (coCartID,coOptID,coOptGroup,coCartOption,coPriceDiff,coWeightDiff) VALUES (".$cartid.",0,'".escape_string($GLOBALS['xxMessag']) . "','".escape_string(substr(getpost('gcmessage'),0,255))."',0,0)";
				ect_query($sSQL) or ect_error();
			}
		}
		if(ob_get_length()!==FALSE)
			header('Location: ' . $storeurl . 'cart.php');
		else
			print '<meta http-equiv="Refresh" content="0; URL=cart.php">';
	}
}
if(getpost('posted')!='1' || !$validitem){
	if($prodid==$giftcertificateid){
		if(@$giftcertificateminimum=='') $giftcertificateminimum=5;
?>
<script type="text/javascript">
/* <![CDATA[ */
function checkastring(thestr,validchars){
  for (i=0; i < thestr.length; i++){
    ch = thestr.charAt(i);
    for (j = 0;  j < validchars.length;  j++)
      if (ch==validchars.charAt(j))
        break;
    if (j==validchars.length)
	  return(false);
  }
  return(true);
}
function formvalECTspecials(frm){
if(frm.amount.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxAmount'])?>\".");
	frm.amount.focus();
	return(false);
}
if (!checkastring(frm.amount.value,"0123456789.,")){
	alert("<?php print jscheck($GLOBALS['xxOnlyDec'] . ' "' . $GLOBALS['xxAmount'])?>\".");
	frm.amount.focus();
	return(false);
}
if(frm.amount.value<<?php print $giftcertificateminimum?>){
	alert("<?php print jscheck($GLOBALS['xxGCMini']) . ' ' . FormatEuroCurrency($giftcertificateminimum)?>.");
	frm.amount.focus();
	return(false);
}
if(frm.toname.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxTo'])?>\".");
	frm.toname.focus();
	return(false);
}
if(frm.fromname.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxFrom'])?>\".");
	frm.fromname.focus();
	return(false);
}

if(frm.toemail.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxReEmai'])?>\".");
	frm.toemail.focus();
	return(false);
}
var regex = /[^@]+@[^@]+\.[a-z]{2,}$/i;
if(!regex.test(frm.toemail.value)){
	alert("<?php print jscheck($GLOBALS['xxValEm'])?>");
	frm.toemail.focus();
	return(false);
}
if(frm.toemail2.value!=frm.toemail.value){
	alert("<?php print jscheck($GLOBALS['xxEmCNMa'])?>.");
	frm.toemail2.focus();
	return(false);
}
return (true);
}
/* ]]> */
</script>
	<form method="post" action="<?php print htmlentities(@$_SERVER['PHP_SELF'])?>" onsubmit="return formvalECTspecials(this)">
	<input type="hidden" name="posted" value="1" />
	<input type="hidden" name="prod" value="<?php print $giftcertificateid?>" />
      <div class="ectdiv ectgiftcerts">
        <div class="ectdivhead"><?php print $GLOBALS['xxGCPurc']?></div>
<?php	if(getpost('posted')=='1'){ ?>
        <div class="ectdiv2column ectwarning"><?php print $GLOBALS['xxAmtNov']?></div>
<?php	} ?>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><label for="amount"><?php print $GLOBALS['xxAmount']?></label></div>
			<div class="ectdivright"><input type="text" name="amount" id="amount" size="4" maxlength="10" value="<?php print htmlspecials(getpost('amount'))?>" /></div>
        </div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><label for="toname"><?php print $GLOBALS['xxTo']?></label></div>
			<div class="ectdivright"><input type="text" name="toname" id="toname" size="25" maxlength="50" value="<?php print htmlspecials(getpost('toname'))?>" /></div>
        </div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><label for="fromname"><?php print $GLOBALS['xxFrom']?></label></div>
			<div class="ectdivright"><input type="text" name="fromname" id="fromname" size="25" maxlength="50" value="<?php print htmlspecials(getpost('fromname'))?>" /></div>
        </div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><label for="toemail"><?php print $GLOBALS['xxReEmai']?></label></div>
			<div class="ectdivright"><input type="text" name="toemail" id="toemail" size="25" maxlength="50" value="<?php print htmlspecials(getpost('toemail'))?>" /></div>
        </div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><label for="toemail2"><?php print $GLOBALS['xxCReEma']?></label></div>
			<div class="ectdivright"><input type="text" name="toemail2" id="toemail2" size="25" maxlength="50" value="<?php print htmlspecials(getpost('toemail2'))?>" /></div>
        </div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><label for="gcmessage"><?php print $GLOBALS['xxMessag']?></label></div>
			<div class="ectdivright"><textarea name="gcmessage" id="gcmessage" cols="35" rows="4"><?php print htmlspecials(getpost('gcmessage'))?></textarea></div>
        </div>
		<div class="ectdiv2column"><?php print imageorsubmit(@$GLOBALS['imggcsubmit'],$GLOBALS['xxSubmt'],'gcsubmit')?></div>
      </div>
	</form>
<?php
	}else{ ?>
<script type="text/javascript">
/* <![CDATA[ */
function checkastring(thestr,validchars){
  for (i=0; i < thestr.length; i++){
    ch = thestr.charAt(i);
    for (j = 0;  j < validchars.length;  j++)
      if (ch==validchars.charAt(j))
        break;
    if (j==validchars.length)
	  return(false);
  }
  return(true);
}
function formvalECTspecials(frm){
if(frm.amount.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxAmount'])?>\".");
	frm.amount.focus();
	return(false);
}
if (!checkastring(frm.amount.value,"0123456789.,")){
	alert("<?php print jscheck($GLOBALS['xxOnlyDec'] . ' "' . $GLOBALS['xxAmount'])?>\".");
	frm.amount.focus();
	return(false);
}
if(frm.gcmessage.value.length>255){
	alert("<?php print jscheck($GLOBALS['xxPrd255'])?>");
	frm.gcmessage.focus();
	return(false);
}
return (true);
}
/* ]]> */
</script>
<?php	if(! @$isincluded){ ?>
	<form method="post" action="<?php print htmlentities(@$_SERVER['PHP_SELF'])?>" onsubmit="return formvalECTspecials(this)">
<?php	} ?>
	<input type="hidden" name="posted" value="1" />
	<input type="hidden" name="prod" value="<?php print $donationid?>" />
      <div class="ectdiv ectdonations">
		<div class="ectdivhead"><?php print $GLOBALS['xxMakDon']?></div>
<?php	if(getpost('posted')=='1'){ ?>
        <div class="ectdiv2column ectwarning"><?php print $GLOBALS['xxAmtNov']?></div>
<?php	} ?>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $redasterix?><label for="amount"><?php print $GLOBALS['xxAmount']?></label></div>
			<div class="ectdivright"><input type="text" name="amount" id="amount" size="6" maxlength="10" value="<?php print htmlspecials(getpost('amount'))?>" /></div>
        </div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><label for="fromname"><?php print $GLOBALS['xxFrom']?></label></div>
			<div class="ectdivright"><input type="text" name="fromname" id="fromname" size="25" maxlength="50" value="<?php print htmlspecials(getpost('fromname'))?>" /></div>
        </div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><label for="gcmessage"><?php print $GLOBALS['xxMessag']?></label></div>
			<div class="ectdivright"><textarea name="gcmessage" id="gcmessage" cols="35" rows="4"><?php print htmlspecials(getpost('gcmessage'))?></textarea></div>
        </div>
		<div class="ectdiv2column"><?php print imageorsubmit(@$GLOBALS['imgdonationsubmit'],$GLOBALS['xxSubmt'],'donationsubmit')?></div>
      </div>
<?php	if(! @$isincluded){ ?>
	</form>
<?php	}
	}
}
?>