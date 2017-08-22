<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net

//redirect if no store address id 


if(empty($_SESSION['addId']))
{
	header('Location:stores.php');
}


if(!@$GLOBALS['incfunctionsdefined']){print 'No incfunctions.php file';exit;}
global $alreadygotadmin,$pathtossl,$forceloginonhttps;
include './vsadmin/inc/incemail.php';
$cartisincluded=TRUE;
include './vsadmin/inc/inccart.php';
if(@$_SERVER['CONTENT_LENGTH']!='' && $_SERVER['CONTENT_LENGTH'] > 10000) exit;
$success=TRUE;
if(@$dateformatstr=='') $dateformatstr='m/d/Y';
$ordGrandTotal=$ordTotal=$ordStateTax=$ordHSTTax=$ordCountryTax=$ordShipping=$ordHandling=$ordDiscount=0;
$ordID=$affilID=$ordCity=$ordState=$ordCountry=$ordDiscountText=$ordEmail='';
$nonhomecountries=FALSE;
$digidownloads=FALSE;
$allcountries='';
$warncheckspamfolder=FALSE;
if(@$enableclientlogin!=TRUE && @$forceclientlogin!=TRUE){
	$success=FALSE;
	$errmsg="Client login not enabled";
}
if(@$pathtossl!=''){
	if(substr($pathtossl,-1)!='/') $pathtossl.='/';
}else
	$pathtossl='';
$pagename=htmlentities(basename($_SERVER['PHP_SELF']));
if(@$forceloginonhttps) $thisaction=$pathtossl . basename(@$_SERVER['PHP_SELF']); else $thisaction=@$_SERVER['PHP_SELF'];
$alreadygotadmin=getadminsettings();
?>
<script type="text/javascript">
/* <![CDATA[ */
function vieworder(theid){
	document.forms.mainform.action.value="vieworder";
	document.forms.mainform.theid.value=theid;
	document.forms.mainform.submit();
}
function editaddress(theid){
	document.forms.mainform.action.value="editaddress";
	document.forms.mainform.theid.value=theid;
	document.forms.mainform.submit();
}
function newaddress(){
	document.forms.mainform.action.value="newaddress";
	document.forms.mainform.submit();
}
function deletelocation(theid){
	if(confirm("<?php print jscheck($GLOBALS['xxDelAdd'])?>")){
	document.forms.mainform.action.value="deletelocation";
	document.forms.mainform.theid.value=theid;
	document.forms.mainform.submit();
	}
}
function editaccount(){
	document.forms.mainform.action.value="editaccount";
	document.forms.mainform.submit();
}
function deleteaddress(theid){
	if(confirm("<?php print jscheck($GLOBALS['xxDelAdd'])?>")){
		document.forms.mainform.action.value="deleteaddress";
		document.forms.mainform.theid.value=theid;
		document.forms.mainform.submit();
	}
}
function createlist(){
if(document.forms.mainform.listname.value==''){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxLisNam'])?>\".");
	document.forms.mainform.listname.focus();
	return(false);
}else{
	document.forms.mainform.action.value="createlist";
	document.forms.mainform.submit();
}
}
function deletelist(theid){
	if(confirm("<?php print jscheck($GLOBALS['xxDelLis'])?>")){
		document.forms.mainform.action.value="deletelist";
		document.forms.mainform.theid.value=theid;
		document.forms.mainform.submit();
	}
}
/* ]]> */
</script>
<?php
	if(getpost('doresetpw')=="1"){
		$sSQL="SELECT clID FROM customerlogin WHERE clEmail='".escape_string(getpost('rst'))."' AND clPw='".escape_string(getpost('rsk'))."'";
	
	
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)) $clid=$rs['clID']; else $clid='';
		if(getpost('newpw')=='') $clid='';
		ect_free_result($result);
		if($clid!='') ect_query("UPDATE customerlogin SET clPw='".escape_string(dohashpw(getpost('newpw')))."' WHERE clID=" . $clid) or ect_error();
?>	  <div class="ectdiv ectclientlogin">
		<div class="ectdivhead"><?php print $GLOBALS['xxCusAcc']?></div>
		  <div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $GLOBALS['xxForPas']?></div>
			<div class="ectdivright"><?php print ($clid==''?$GLOBALS['xxEmNtFn']:$GLOBALS['xxPasRsS']) ?></div>
		  </div>
		  <div class="ectdiv2column"><?php
		if($clid!='')
			print imageorbutton(@$imglogin,$GLOBALS['xxLogin'],'login',(@$forceloginonhttps?$pathtossl:'').'cart.php?mode=login',FALSE);
		else
			print imageorbutton(@$imggoback,$GLOBALS['xxGoBack'],'goback','history.go(-1)',TRUE); ?></div>
	  </div>
<?php
	}elseif(getget('rst')!='' && getget('rsk')!=''){
		$sSQL="SELECT clID FROM customerlogin WHERE clEmail='".escape_string(getget('rst'))."' AND clPw='".escape_string(getget('rsk'))."'";
		$result=ect_query($sSQL) or ect_error();

		if(ect_num_rows($result)>0) $success=TRUE; else $success=FALSE;
		ect_free_result($result);

		if(! $success){ ?>
			<div class="ectdiv ectclientlogin">
				<div class="ectdivhead"><?php print $GLOBALS['xxCusAcc']?></div>
				<div class="ectdivcontainer">
					<div class="ectdivleft"><?php print $GLOBALS['xxForPas']?></div>
					<div class="ectdivright"><?php print $GLOBALS['xxSorRes']?></div>
				</div>
				<div class="ectdiv2column"><?php print imageorbutton(@$imgcancel,$GLOBALS['xxCancel'],'cancel',$storeurl,FALSE) ?></div>
			</div>
			<?php	}else{ 
				
				
				
				}
				
				?>
						<script type="text/javascript">
						/* <![CDATA[ */
						function checknewpw(frm){
						if(frm.newpw.value==""){
							alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxNewPwd'])?>\".");
							frm.newpw.focus();
							return(false);
						}
						var newpw=frm.newpw.value;
						var newpw2=frm.newpw2.value;
						if(newpw!=newpw2){
							alert("<?php print jscheck($GLOBALS['xxPwdMat'])?>");
							frm.newpw.focus();
							return(false);
						}
						return true;
						}
						/* ]]> */
						</script>
	<form method="post" name="mainform" action="<?php print $thisaction?>" onsubmit="return checknewpw(this)">
	<input type="hidden" name="doresetpw" value="1" />
	<input type="hidden" name="rst" value="<?php print str_replace('"','',getget('rst'))?>" />
	<input type="hidden" name="rsk" value="<?php print str_replace('"','',getget('rsk'))?>" />
	  <div class="ectdiv ectclientlogin">
		<div class="ectdivhead"><?php print $GLOBALS['xxCusAcc'] . ' ' . $GLOBALS['xxForPas']?></div>
		  <div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $GLOBALS['xxNewPwd']?></div>
			<div class="ectdivright"><input type="password" size="20" name="newpw" value="" autocomplete="off" /></div>
		  </div>
		  <div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $GLOBALS['xxRptPwd']?></div>
			<div class="ectdivright"><input type="password" size="20" name="newpw2" value="" autocomplete="off" /></div>
		  </div>
		  <div class="ectdiv2column"><?php print imageorsubmit(@$imgsubmit,$GLOBALS['xxSubmt'],'submit').' '.imageorbutton(@$imgcancel,$GLOBALS['xxCancel'],'cancel',$storeurl,FALSE)?></div>
	  </div>
	</form>
<?php	
	}elseif(getget('action')=='logout'){
		$_SESSION['clientID']=NULL; unset($_SESSION['clientID']);
		$_SESSION['clientUser']=NULL; unset($_SESSION['clientUser']);
		$_SESSION['clientActions']=NULL; unset($_SESSION['clientActions']);
		$_SESSION['clientLoginLevel']=NULL; unset($_SESSION['clientLoginLevel']);
		$_SESSION['clientPercentDiscount']=NULL; unset($_SESSION['clientPercentDiscount']);
		ectsetcookie('WRITECLL', 'x', 100, '/', '');
		ectsetcookie('WRITECLP', 'y', 100, '/', '');
		if(@$clientlogoutref!='')
			$refURL=$clientlogoutref;
		else
			$refURL=$GLOBALS['xxHomeURL'];
		print '<meta http-equiv="refresh" content="3; url=' . $refURL . '">';
?>
		<div class="ectdiv ectclientlogin">
		  <div class="ectmessagescreen">
			<div><?php print $GLOBALS['xxLOSuc']?></div>
			<div><?php print $GLOBALS['xxAutFo']?></div>
			<div><?php print $GLOBALS['xxForAut']?> <a class="ectlink" href="<?php print $refURL?>"><?php print $GLOBALS['xxClkHere']?></a>.</div>
		  </div>
		</div>
<?php	
	}elseif(getpost('action')=='dolostpassword'){
		$theemail=cleanupemail(getpost('email'));
		$sSQL="SELECT clPW FROM customerlogin WHERE clEmail<>'' AND clEmail='" . escape_string($theemail) . "'";
	
		$result=ect_query($sSQL) or ect_error();
		if(ect_num_rows($result) > 0){
			$rs=ect_fetch_assoc($result);
			if(@$htmlemails==TRUE) $emlNl='<br />'; else $emlNl="\n";
			$tlink=$storeurl . $pagename . "?rst=" . $theemail . "&rsk=" . $rs['clPW'];
			if(@$htmlemails==TRUE) $tlink='<a href="' . $tlink . '">' . $tlink . '</a>';
			dosendemail($theemail, $emailAddr, '', $GLOBALS['xxForPas'], $GLOBALS['xxLosPw1'] . $emlNl . $storeurl . $emlNl . $emlNl . $GLOBALS['xxResPas'] . $emlNl . $tlink . $emlNl . $emlNl . $GLOBALS['xxLosPw3'] . $emlNl);
			$success=TRUE;
		}else{
			$success=FALSE;
		}
		ect_free_result($result); ?>
	  <form method="post" name="mainform" action="<?php print $thisaction?>">
	  <div class="ectdiv ectclientlogin">
		<div class="ectdivhead"><?php print $GLOBALS['xxCusAcc']?></div>
		  <div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $GLOBALS['xxForPas']?></div>
			<div class="ectdivright"><?php if($success) print $GLOBALS['xxSenPw']; else print $GLOBALS['xxSorPw']; ?></div>
		  </div>
		  <div class="ectdiv2column"><?php
		if($success)
			print imageorbutton(@$imglogin,$GLOBALS['xxLogin'],'login',(@$forceloginonhttps?$pathtossl:'') . 'cart.php?mode=login',FALSE);
		else
			print imageorbutton(@$imggoback,$GLOBALS['xxGoBack'],'goback','history.go(-1)',TRUE);
		?></div>
	  </div>
	  </form>
<?php
	}elseif(getget('mode')=='lostpassword'){ ?>
	  <form method="post" name="mainform" action="<?php print $thisaction?>">
	  <input type="hidden" name="action" value="dolostpassword" />
	  <div class="ectdiv ectclientlogin">
		<div class="ectdivhead"><?php print $GLOBALS['xxCusAcc']?></div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $GLOBALS['xxForPas']?></div>
			<div class="ectdivright"><?php print $GLOBALS['xxEntEm']?></div>
		</div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $GLOBALS['xxEmail']?></div>
			<div class="ectdivright"><input type="text" name="email" size="31" /></div>
		</div>
		<div class="ectdiv2column"><?php print imageorsubmit(@$imgsubmit,$GLOBALS['xxSubmt'],'submit')?></div>
	  </div>
	  </form>
<?php
	}elseif(@$_SESSION['clientID']==''){ ?>
	  <div class="ectdiv ectclientlogin">
		<div class="ectdivhead"><?php print $GLOBALS['xxCusAcc']?></div>
		<div class="ectmessagescreen">
			<div><?php print $GLOBALS['xxMusLog']?></div>
			<div><?php print imageorbutton(@$imglogin,$GLOBALS['xxLogin'],'login',(@$forceloginonhttps?$pathtossl:'')."cart.php?mode=login&amp;refurl=".urlencode(@$_SERVER['PHP_SELF']),FALSE)?></div>
		</div>
	  </div>
<?php
	}else{ // is logged in
		if(getpost('action')=='vieworder'){ ?>
	  <div class="ectdiv ectclientlogin">
		  <div class="clientloginvieworder"><?php
			$ordID=str_replace("'",'',getpost('theid'));
			if(is_numeric($ordID)) $success=TRUE; else $success=FALSE;
			if($success){
				$sSQL="SELECT ordID FROM orders WHERE ordID=" . $ordID . " AND ordClientID=" . $_SESSION['clientID'];
				$result=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result)==0) $success=FALSE;
				ect_free_result($result);
			}
			if($success){
				$GLOBALS['xxThkYou']=imageorbutton(@$imgbackacct,$GLOBALS['xxBack'],'backacct','history.go(-1)',TRUE);
				$GLOBALS['xxRecEml']='';
				$thankspagecontinue='javascript:history.go(-1)';
				$GLOBALS['xxCntShp']=$GLOBALS['xxBack'];
				$imgcontinueshopping=@$imgbackacct;
				do_order_success($ordID,$emailAddr,FALSE,TRUE,FALSE,FALSE,FALSE);
			}else{
				$errtext="Sorry, could not find a matching order.";
				order_failed();
			} ?>
		  </div>
	  </div>
<?php	}elseif(getpost('action')=='doeditaccount'){
			$oldpw=dohashpw(getpost('oldpw'));
			$newpw=getpost('newpw');
			$newpw2=getpost('newpw2');
			$clientuser=getpost('name');
			$clientemail=cleanupemail(getpost('email'));
			$allowemail=getpost('allowemail');
			$sSQL="SELECT clPW,clEmail FROM customerlogin WHERE clID=" . $_SESSION['clientID'];
			$result=ect_query($sSQL) or ect_error();
			$rs=ect_fetch_assoc($result);
			ect_free_result($result);
			$oldpassword=$rs['clPW'];
			$oldemail=$rs['clEmail'];
			$success=TRUE;
			if($newpw!='' || $newpw2!=''){
				if($oldpw!=$oldpassword){
					$success=FALSE;
					$errmsg=$GLOBALS['xxExNoMa'];
				}
			}
			if($oldemail != $clientemail){
				$sSQL="SELECT clID FROM customerlogin WHERE clEmail='" . escape_string($clientemail) . "'";
				$result=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result) > 0){
					$success=FALSE;
					$errmsg=$GLOBALS['xxEmExi'];
				}
				ect_free_result($result);
			}
			if($success){
				$sSQL='UPDATE customerlogin SET ';
				$sSQL.="clUserName='" . escape_string($clientuser) . "',";
				$sSQL.="clEmail='" . escape_string($clientemail) . "'";
				if($newpw!='') $sSQL.=",clPW='" . escape_string(dohashpw($newpw)) . "'";
				$sSQL.=" WHERE clID=" . $_SESSION['clientID'];
				ect_query($sSQL) or ect_error();
				if($allowemail=='ON'){
					addtomailinglist($clientemail,$clientuser);
					if($oldemail != $clientemail) ect_query("DELETE FROM mailinglist WHERE email='" . escape_string($oldemail) . "'");
				}else{
					ect_query("DELETE FROM mailinglist WHERE email='" . escape_string($clientemail) . "'");
					ect_query("DELETE FROM mailinglist WHERE email='" . escape_string($oldemail) . "'");
				}
				$_SESSION['clientUser']=$clientuser;
				print '<meta http-equiv="Refresh" content="2; URL=' . $_SERVER['PHP_SELF'] . '">';
			}
?>
	<form method="post" name="mainform" action="<?php print $thisaction?>">
	  <div class="ectdiv ectclientlogin">
		<div class="ectdivhead"><?php print $GLOBALS['xxCusAcc']?></div>
		<div class="ectdiv2column<?php print ($success?'':' ectwarning')?>"><?php if($success) print $GLOBALS['xxUpdSuc']; else print $errmsg ?></div>
		<div class="ectdiv2column"><?php
		if($success)
			print imageorsubmit(@$imgcustomeracct,$GLOBALS['xxCusAcc'],'customeracct');
		else
			print imageorbutton(@$imggoback,$GLOBALS['xxGoBack'],'goback','history.go(-1)',TRUE);
		?></div>
	  </div>
	</form>
<?php	}elseif(getpost('action')=='editaccount'){
			if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.basename($_SERVER['PHP_SELF']).(@$_SERVER['QUERY_STRING']!='' ? '?'.$_SERVER['QUERY_STRING'] : '')); exit; }
?>
<script type="text/javascript">
/* <![CDATA[ */
var checkedfullname=false;
function checknewaccount(){
frm=document.forms.mainform;
if(frm.name.value==""||frm.name.value=="<?php print $GLOBALS['xxFirNam']?>"){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . @$usefirstlastname ? $GLOBALS['xxFirNam'] : $GLOBALS['xxName'])?>\".");
	frm.name.focus();
	return (false);
}
gotspace=false;
var checkStr=frm.name.value;
for (i=0; i < checkStr.length; i++){
	if(checkStr.charAt(i)==" ")
		gotspace=true;
}
if(!checkedfullname && !gotspace){
	alert("<?php print jscheck($GLOBALS['xxFulNam'] . ' "' . $GLOBALS['xxName'])?>\".");
	frm.name.focus();
	checkedfullname=true;
	return (false);
}
if(frm.email.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxEmail'])?>\".");
	frm.email.focus();
	return (false);
}
var regex=/[^@]+@[^@]+\.[a-z]{2,}$/i;
if(!regex.test(frm.email.value)){
	alert("<?php print jscheck($GLOBALS['xxValEm'])?>");
	frm.email.focus();
	return (false);
}
var newpw=frm.newpw.value;
var newpw2=frm.newpw2.value;
if(newpw!='' && newpw!=newpw2){
	alert("<?php print jscheck($GLOBALS['xxPwdMat'])?>");
	frm.newpw.focus();
	return(false);
}
return true;
}
/* ]]> */
</script>
		<form method="post" name="mainform" action="<?php print $thisaction?>" onsubmit="return checknewaccount()">
		<input type="hidden" name="action" value="doeditaccount" />
                
		<div class="ectdiv ectclientlogin">
		  <div class="ectdivhead"><?php print $GLOBALS['xxAccDet'] ?></div>
		  <div class="clientlogineditaccount">
<?php		$sSQL="SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,loyaltyPoints FROM customerlogin WHERE clID=" . $_SESSION['clientID'];
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)) $theemail=$rs['clEmail']; else $_SESSION['clientID']='';
			ect_free_result($result);
			$sSQL="SELECT email FROM mailinglist WHERE email='" . escape_string(@$theemail) . "'";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0) $allowemail=1; else $allowemail=0;
			ect_free_result($result);
?>
			<div class="ectdivcontainer">
				<div class="ectdivleft"><?php print $GLOBALS['xxName']?></div>
				<div class="ectdivright"><input type="text" size="30" name="name" value="<?php print htmlspecials($_SESSION['clientUser'])?>" /></div>
			</div>
<?php		if(@$nounsubscribe!=TRUE){ ?>
			<div class="ectdivcontainer">
				<div class="ectdivleft"><input type="checkbox" name="allowemail" value="ON"<?php if($allowemail!=0) print ' checked="checked"'?> /></div>
				<div class="ectdivright"><div><?php print $GLOBALS['xxAlPrEm']?></div><div style="font-size:10px"><?php print $GLOBALS['xxNevDiv']?></div></div>
			</div>
<?php		} ?>
			<div class="ectdivcontainer">
				<div class="ectdivleft"><?php print $GLOBALS['xxEmail']?></div>
				<div class="ectdivright"><input type="text" size="30" name="email" value="<?php print $theemail?>" /></div>
			</div>
			<div class="ectdivhead"><?php print $GLOBALS['xxPwdChg']?></div>
			<div class="ectdivcontainer">
				<div class="ectdivleft"><?php print $GLOBALS['xxOldPwd']?></div>
				<div class="ectdivright"><input type="password" size="20" name="oldpw" value="" autocomplete="off" /></div>
			</div>
			<div class="ectdivcontainer">
				<div class="ectdivleft"><?php print $GLOBALS['xxNewPwd']?></div>
				<div class="ectdivright"><input type="password" size="20" name="newpw" value="" autocomplete="off" /></div>
			</div>
			<div class="ectdivcontainer">
				<div class="ectdivleft"><?php print $GLOBALS['xxRptPwd']?></div>
				<div class="ectdivright"><input type="password" size="20" name="newpw2" value="" autocomplete="off" /></div>
			</div>
			<div class="ectdiv2column"><?php print imageorsubmit(@$imgsubmit,$GLOBALS['xxSubmt'],'submit').' '.imageorbutton(@$imgcancel,$GLOBALS['xxCancel'],'cancel','history.go(-1)',TRUE)?></div>
		  </div>
		</div>
		</form>
<?php	}elseif(getpost('action')=='editaddress' || getpost('action')=='newaddress'){
			$addID=str_replace("'",'',getpost('theid'));
			if(!is_numeric($addID))$addID=0;
			$addIsDefault='';
			$addName='';
			$addLastName='';
			$addAddress='';
			$addAddress2='';
			$addState='';
			$addCity='';
			$addZip='';
			$addPhone='';
			$addCountry='';
			$addExtra1='';
			$addExtra2='';
			$sSQL='SELECT stateID FROM states INNER JOIN countries ON states.stateCountryID=countries.countryID WHERE countryEnabled<>0 AND stateEnabled<>0 AND (loadStates=2 OR countryID=' . $origCountryID . ') ORDER BY stateCountryID,stateName';
			$result=ect_query($sSQL) or ect_error();
			$hasstates=(ect_num_rows($result)>0);
			ect_free_result($result);
			$sSQL="SELECT countryName,countryOrder,".getlangid("countryName",8).",countryID,loadStates FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC," . getlangid("countryName",8);
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				$allcountries[$numallcountries++]=$rs;
			}
			ect_free_result($result);
			for($index=0;$index<$numallcountries;$index++){
				if($allcountries[$index]['loadStates']==0){ $nonhomecountries=TRUE; break; }
			}
			if(! $nonhomecountries){
				for($index=0;$index<$numallcountries;$index++){
					if($allcountries[$index]['loadStates']>0){
						$sSQL="SELECT stateID FROM states WHERE stateEnabled<>0 AND stateCountryID=" . $allcountries[$index]['countryID'];
						$result=ect_query($sSQL) or ect_error();
						if(ect_num_rows($result)==0) $nonhomecountries=TRUE;
						ect_free_result($result);
						if($nonhomecountries) break;
					}
				}
			}
			if(getpost('action')=='editaddress'){
				$sSQL="SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry,addExtra1,addExtra2 FROM address WHERE addID=" . $addID . " AND addCustID='" . $_SESSION['clientID'] . "' ORDER BY addIsDefault";
				$result=ect_query($sSQL) or ect_error();
				if($rs=ect_fetch_assoc($result)){
					$addIsDefault=$rs['addIsDefault'];
					$addName=$rs['addName'];
					$addLastName=$rs['addLastName'];
					$addAddress=$rs['addAddress'];
					$addAddress2=$rs['addAddress2'];
					$addState=$rs['addState'];
					$ordState=$addState;
					$addCity=$rs['addCity'];
					$addZip=$rs['addZip'];
					$addPhone=$rs['addPhone'];
					$addCountry=$rs['addCountry'];
					$addExtra1=$rs['addExtra1'];
					$addExtra2=$rs['addExtra2'];
				}
				ect_free_result($result);
			} ?>
	<form method="post" name="mainform" action="<?php print $thisaction?>" onsubmit="return checkform(this)">
	<input type="hidden" name="action" value="<?php if(getpost('action')=='editaddress') print "doeditaddress"; else print "donewaddress" ?>" />
	<input type="hidden" name="theid" value="<?php print $addID?>" />
	  <div class="ectdiv ectclientlogin">
		<div class="ectdivhead"><?php print $GLOBALS['xxEdAdd']?></div>
		<?php	if(trim(@$extraorderfield1)!=''){ ?>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print (@$extraorderfield1required==TRUE ? $redstar : '') . $extraorderfield1 ?></div>
			<div class="ectdivright"><?php if(@$extraorderfield1html!='') print $extraorderfield1html; else print '<input type="text" name="ordextra1" id="ordextra1" size="20" value="' . htmlspecials($addExtra1) . '" />'?></div>
		</div>
		<?php	} ?>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $redstar . $GLOBALS['xxName']?></div>
			<div class="ectdivright"><?php
		if(@$usefirstlastname){
			$thestyle='';
			if($addName=='' && $addLastName==''){ $addName=$GLOBALS['xxFirNam']; $addLastName=$GLOBALS['xxLasNam']; $thestyle='style="color:#BBBBBB" '; }
			print '<input type="text" name="name" size="11" value="'.htmlspecials($addName).'" alt="'.$GLOBALS['xxFirNam'].'" onfocus="if(this.value==\''.$GLOBALS['xxFirNam'].'\'){this.value=\'\';this.style.color=\'\';}" '.$thestyle.'/> <input type="text" name="lastname" size="11" value="'.htmlspecials($addLastName).'" alt="'.$GLOBALS['xxLasNam'].'" onfocus="if(this.value==\''.$GLOBALS['xxLasNam'].'\'){this.value=\'\';this.style.color=\'\';}" '.$thestyle.'/>';
		}else
			print '<input type="text" name="name" id="name" size="20" value="'.htmlspecials($addName).'" />';
		?></div>
		</div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $redstar . $GLOBALS['xxAddress']?></div>
			<div class="ectdivright"><input type="text" name="address" id="address" size="25" value="<?php print htmlspecials($addAddress)?>" /></div>
		</div>
		<?php	if(@$useaddressline2==TRUE){ ?>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $GLOBALS['xxAddress2']?></div>
			<div class="ectdivright"><input type="text" name="address2" id="address2" size="25" value="<?php print htmlspecials($addAddress2)?>" /></div>
		</div>
		<?php	} ?>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $redstar . $GLOBALS['xxCity']?></div>
			<div class="ectdivright"><input type="text" name="city" id="city" size="20" value="<?php print htmlspecials($addCity)?>" /></div>
		</div>
		<?php	if($hasstates || $nonhomecountries){ ?>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print replace($redstar,'<span','<span id="statestar"')?><span id="statetxt"><?php print $GLOBALS['xxState']?></span></div>
			<div class="ectdivright"><select name="state" id="state" size="1" onchange="dosavestate('')"><?php $havestate=show_states($addState) ?></select><input type="text" name="state2" id="state2" size="20" value="<?php if(! $havestate) print htmlspecials($addState)?>" /></div>
		</div>
		<?php	} ?>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $redstar . $GLOBALS['xxCountry']?></div>
			<div class="ectdivright"><select name="country" id="country" size="1" onchange="checkoutspan('')" ><?php show_countries($addCountry,FALSE) ?></select></div>
		</div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print replace($redstar,'<span','<span id="zipstar"') . '<span id="ziptxt">' . $GLOBALS['xxZip'] . '</span>'?></div>
			<div class="ectdivright"><input type="text" name="zip" id="zip" size="10" value="<?php print htmlspecials($addZip)?>" /></div>
		</div>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print $redstar . $GLOBALS['xxPhone']?></div>
			<div class="ectdivright"><input type="text" name="phone" id="phone" size="20" value="<?php print htmlspecials($addPhone)?>" /></div>
		</div>
		<?php	if(trim(@$extraorderfield2)!=''){ ?>
		<div class="ectdivcontainer">
			<div class="ectdivleft"><?php print (@$extraorderfield2required==true ? $redstar : '') . $extraorderfield2 ?></div>
			<div class="ectdivright"><?php if(@$extraorderfield2html!='') print $extraorderfield2html; else print '<input type="text" name="ordextra2" id="ordextra2" size="20" value="' . htmlspecials($addExtra2) . '" />'?></div>
		</div>
		<?php	} ?>
		<div class="ectdiv2column"><?php print imageorsubmit(@$imgsubmit,$GLOBALS['xxSubmt'],'submit').' '.imageorbutton(@$imgcancel,$GLOBALS['xxCancel'],'cancel','history.go(-1)',TRUE)?></div>
	  </div>
	</form>
<script type="text/javascript">
/* <![CDATA[ */
var checkedfullname=false;
function zipoptional(cntobj){
var cntid=cntobj[cntobj.selectedIndex].value;
if(cntid==85 || cntid==91 || cntid==154 || cntid==200)return true; else return false;
}
function stateoptional(cntobj){
var cntid=cntobj[cntobj.selectedIndex].value;
if(false<?php
$result=ect_query('SELECT countryID FROM countries WHERE countryEnabled<>0 AND loadStates<0') or ect_error();
while($rs=ect_fetch_assoc($result)) print '||cntid==' . $rs['countryID'];
ect_free_result($result);
?>)return true; else return false;
}
function checkform(frm)
{
<?php if(trim(@$extraorderfield1)!='' && @$extraorderfield1required==true){ ?>
if(frm.ordextra1.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $extraorderfield1)?>\".");
	frm.ordextra1.focus();
	return (false);
}
<?php } ?>
if(frm.name.value==""||frm.name.value=="<?php print $GLOBALS['xxFirNam']?>"){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . @$usefirstlastname ? $GLOBALS['xxFirNam'] : $GLOBALS['xxName'])?>\".");
	frm.name.focus();
	return (false);
}
<?php	if(@$usefirstlastname){ ?>
if(frm.lastname.value==""||frm.lastname.value=="<?php print $GLOBALS['xxLasNam']?>"){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxLasNam'])?>\".");
	frm.lastname.focus();
	return (false);
}
<?php	}else{ ?>
gotspace=false;
var checkStr=frm.name.value;
for (i=0; i < checkStr.length; i++){
	if(checkStr.charAt(i)==" ")
		gotspace=true;
}
if(!checkedfullname && !gotspace){
	alert("<?php print jscheck($GLOBALS['xxFulNam'] . ' "' . $GLOBALS['xxName'])?>\".");
	frm.name.focus();
	checkedfullname=true;
	return (false);
}
<?php	} ?>
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
if(stateoptional(document.getElementById('country'))){
	}else if(stateselectordisabled[0]==false){
<?php
	if($hasstates){ ?>
	if(frm.state.selectedIndex==0){
		alert("<?php print jscheck($GLOBALS['xxPlsSlct']) . ' '?>" + document.getElementById('statetxt').innerHTML);
		frm.state.focus();
		return(false);
	}
<?php	} ?>
	}else{
<?php	if($nonhomecountries){ ?>
	if(frm.state2.value==""){
		alert("<?php print jscheck($GLOBALS['xxPlsEntr'])?> \"" + document.getElementById('statetxt').innerHTML + "\".");
		frm.state2.focus();
		return(false);
	}
<?php	} ?>}
if(frm.zip.value=="" && ! zipoptional(document.getElementById('country'))){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxZip'])?>\".");
	frm.zip.focus();
	return(false);
}
if(frm.phone.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $GLOBALS['xxPhone'])?>\".");
	frm.phone.focus();
	return (false);
}
<?php if(trim(@$extraorderfield2)!='' && @$extraorderfield2required==TRUE){ ?>
if(frm.ordextra2.value==""){
	alert("<?php print jscheck($GLOBALS['xxPlsEntr'] . ' "' . $extraorderfield2)?>\".");
	frm.ordextra2.focus();
	return (false);
}
<?php } ?>
return (true);
}
<?php if(@$termsandconditions==TRUE){ ?>
function showtermsandconds(){
newwin=window.open("termsandconditions.php","Terms","menubar=no, scrollbars=yes, width=420, height=380, directories=no,location=no,resizable=yes,status=no,toolbar=no");
}
<?php } ?>
var savestate=0;
var ssavestate=0;
function dosavestate(shp){
	thestate=eval('document.forms.mainform.'+shp+'state');
	eval(shp+'savestate=thestate.selectedIndex');
}
function checkoutspan(shp){
	document.getElementById(shp+'zipstar').style.display=(zipoptional(document.getElementById(shp+'country'))?'none':'');
	document.getElementById(shp+'statestar').style.display=(stateoptional(document.getElementById(shp+'country'))?'none':'');<?php
	if($hasstates){
		print "thestate=document.getElementById(shp+'state');\r\n";
		print "dynamiccountries(document.getElementById(shp+'country'),shp);\r\n";
	}
	print "if(stateselectordisabled[shp=='s'?1:0]==false&&!stateoptional(document.getElementById(shp+'country'))){\r\n";
	print "if(document.getElementById(shp+'state2'))document.getElementById(shp+'state2').style.display='none';\r\n";
	if($hasstates){
		print "thestate.disabled=false;\r\n";
		print "eval('thestate.selectedIndex='+shp+'savestate');\r\n";
		print "document.getElementById(shp+'state').style.display='';\r\n";
	} ?>
}else{<?php
	print "if(document.getElementById(shp+'state2'))document.getElementById(shp+'state2').style.display='';\r\n";
	if($hasstates){ ?>
		document.getElementById(shp+'state').style.display='none';
		if(thestate.disabled==false){
		thestate.disabled=true;
		eval(shp+'savestate=thestate.selectedIndex');
		thestate.selectedIndex=0;}
<?php
	} ?>
}}
<?php
	createdynamicstates('SELECT stateAbbrev,stateName,stateName2,stateName3,stateCountryID,countryName FROM states INNER JOIN countries ON states.stateCountryID=countries.countryID WHERE countryEnabled<>0 AND stateEnabled<>0 AND (loadStates=2 OR countryID=' . $origCountryID . ') ORDER BY stateCountryID,' . getlangid('stateName',1048576));
	print "checkoutspan('');setinitialstate('');\r\n";
?>/* ]]> */
</script>
<?php	}elseif((getpost('action')=='createlist' && getpost('listname')!='') || getpost('action')=='deletelist' || getpost('action')=='deleteaddress' ||  getpost('action')=='deletelocation' || getpost('action')=='doeditaddress' || getpost('action')=='donewaddress'){
			$addID=str_replace("'",'',getpost('theid'));
			if(!is_numeric($addID))$addID=0;
			$ordName=getpost('name');
			$ordLastName=getpost('lastname');
			$ordAddress=getpost('address');
			$ordAddress2=getpost('address2');
			$ordState=getpost('state2');
			if(getpost('state')!='')
				$ordState=getpost('state');
			$ordCity=getpost('city');
			$ordZip=getpost('zip');
			$ordPhone=getpost('phone');
			$ordCountry=getcountryfromid(getpost('country'));
			$ordExtra1=getpost('ordextra1');
			$ordExtra2=getpost('ordextra2');
			$headertext='';
			if(getpost('action')=='createlist' && @$enablewishlists==TRUE){
				$headertext=$GLOBALS['xxLisMan'];
				$listaccess=md5(time() . getpost('listname') . $adminSecret);
				$sSQL="INSERT INTO customerlists (listName,listOwner,listAccess) VALUES ('" . escape_string(getpost('listname')) . "'," . $_SESSION['clientID'] . ",'" . escape_string($listaccess) . "')";
				ect_query($sSQL) or ect_error();
			}elseif(getpost('action')=='deletelist' && @$enablewishlists==TRUE){
				$headertext=$GLOBALS['xxLisMan'];
				$sSQL="DELETE FROM customerlists WHERE listID=" . $addID . " AND listOwner=" . $_SESSION['clientID'];
				ect_query($sSQL) or ect_error();
				$sSQL="DELETE FROM cart WHERE cartListID=" . $addID . " AND cartClientID=" . $_SESSION['clientID'];
				ect_query($sSQL) or ect_error();
			}elseif(getpost('action')=='deleteaddress'){
				$headertext=$GLOBALS['xxAddMan'];
				$sSQL="DELETE FROM address WHERE addID=" . $addID . " AND addCustID=" . $_SESSION['clientID'];
				ect_query($sSQL) or ect_error();
			}elseif(getpost('action')=='donewaddress'){
				$headertext=$GLOBALS['xxAddMan'];
				$sSQL="INSERT INTO address (addCustID,addIsDefault,addName,addLastName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,addExtra1,addExtra2) VALUES (" . $_SESSION['clientID'] . ",0,'" . escape_string($ordName) . "','" . escape_string($ordLastName) . "','" . escape_string($ordAddress) . "','" . escape_string($ordAddress2) . "','" . escape_string($ordCity) . "','" . escape_string($ordState) . "','" . escape_string($ordZip) . "','" . escape_string($ordCountry) . "','" . escape_string($ordPhone) . "','" . escape_string($ordExtra1) . "','" . escape_string($ordExtra2) . "')";
				ect_query($sSQL) or ect_error();
			}elseif(getpost('action')=='doeditaddress'){
				$headertext=$GLOBALS['xxAddMan'];
				$sSQL="UPDATE address SET addName='" . escape_string($ordName) . "',addLastName='" . escape_string($ordLastName) . "',addAddress='" . escape_string($ordAddress) . "',addAddress2='" . escape_string($ordAddress2) . "',addCity='" . escape_string($ordCity) . "',addState='" . escape_string($ordState) . "',addZip='" . escape_string($ordZip) . "',addCountry='" . escape_string($ordCountry) . "',addPhone='" . escape_string($ordPhone) . "',addExtra1='" . escape_string($ordExtra1) . "',addExtra2='" . escape_string($ordExtra2) . "' WHERE addCustID=" . $_SESSION['clientID'] . " AND addID=" . $addID;
				ect_query($sSQL) or ect_error();
			}elseif(getpost('action')=='deletelocation'){
				$sSQL="DELETE FROM productlocation WHERE id=" . $addID . " AND clientid=" . $_SESSION['clientID'];			
				ect_query($sSQL) or ect_error();
				
			}
			print '<meta http-equiv="Refresh" content="2; URL=' . $_SERVER['PHP_SELF'] . '">';
?>	  <div class="ectdiv ectclientlogin">
		<div class="ectmessagescreen">
			<div class="ectdivhead"><?php print $headertext?></div>
			<div><?php print $GLOBALS['xxUpdSuc']?></div>
		</div>
	  </div>
<?php	}else{ ?>
<script type="text/javascript">
/* <![CDATA[ */
var currstate=[];
currstate['ad']='none';
currstate['am']='none';
currstate['gr']='none';
currstate['om']='none';
currstate['pl']='none';
function showhidesection(sect){
	var elem=document.getElementsByTagName('div');
	currstate[sect]=currstate[sect]=='none'?'':'none';
	for(var i=0; i<elem.length; i++){
		var classes=elem[i].className;
		if(classes.indexOf(sect+'formrow')!=-1) elem[i].style.display=currstate[sect];
	}
	document.getElementById('sectimage'+sect).src=currstate[sect]=='none'?'images/arrow-down.png':'images/arrow-up.png';
	return false;



}
/* ]]> */</script>
		  <form method="post" name="mainform" action="<?php print $thisaction?>">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="action" value="none" />
			<input type="hidden" name="theid" value="" />
			<div class="ectdiv ectclientlogin">
				<div class="ectdivhead" onclick="showhidesection('ad')"><a href="#" onclick="return false"><?php print $GLOBALS['xxAccDet']?></a><a href="#" onclick="return false"><img id="sectimagead" src="images/arrow-down.png" style="float:right;margin-right:15px" /></a></div>
				<div class="adformrow" style="display:none">
				  <div class="ectclientloginaccount">
<?php		$sSQL="SELECT clID,clUserName,clActions,clLoginLevel,clPercentDiscount,clEmail,loyaltyPoints FROM customerlogin WHERE clID=" . $_SESSION['clientID'];
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){ $theemail=$rs['clEmail']; $loyaltypointtotal=$rs['loyaltyPoints']; } else $theemail='ACCOUNT DELETED';
			ect_free_result($result);
			$sSQL="SELECT email,isconfirmed FROM mailinglist WHERE email='" . escape_string($theemail) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){ $allowemail=1; $isconfirmed=$rs['isconfirmed']; }else{ $allowemail=0; $isconfirmed=FALSE; }
			ect_free_result($result); ?>
					<div class="ectdivcontainer">
						<div class="ectdivleft"><?php print $GLOBALS['xxName']?></div>
						<div class="ectdivright"><?php print htmlspecials($_SESSION['clientUser'])?></div>
					</div>
<?php		if(@$nounsubscribe!=TRUE){ ?>
					<div class="ectdivcontainer">
						<div class="ectdivleft"><?php print $GLOBALS['xxAlPrEm']?><div style="font-size:10px"><?php print $GLOBALS['xxNevDiv']?></div></div>
						<div class="ectdivright"><?php if(@$noconfirmationemail!=TRUE && $allowemail!=0 && $isconfirmed==0) print $GLOBALS['xxWaiCon']; else print '<input type="checkbox" name="allowemail" value="ON"' . ($allowemail!=0 ? ' checked="checked"' : '') . ' disabled="disabled" />'; ?></div>
					</div>
<?php		} ?>
					<div class="ectdivcontainer">
						<div class="ectdivleft"><?php print $GLOBALS['xxEmail']?></div>
						<div class="ectdivright"><?php print $theemail?></div>
					</div>
<?php		if(@$loyaltypoints!=''){ ?>
					<div class="ectdivcontainer">
						<div class="ectdivleft"><?php print $GLOBALS['xxLoyPoi']?></div>
						<div class="ectdivright"><?php print $loyaltypointtotal?></div>
					</div>
<?php		} ?>
					<div class="ectdiv2column"><ul><li><?php print $GLOBALS['xxChaAcc']?> <a class="ectlink" href="javascript:editaccount()"><?php print $GLOBALS['xxClkHere']?></a>.</li></ul></div>
				  </div>
				</div>
<?php		// Address Management
?>  		  <div class="ectdivhead" onclick="showhidesection('am')"><a href="#" onclick="return false"><?php print $GLOBALS['xxAddMan']?></a><a href="#" onclick="return false"><img id="sectimageam" src="images/arrow-down.png" style="float:right;margin-right:15px" /></a></div>
			  <div class="amformrow" style="display:none">
				  <div class="ectclientloginaddress">
<?php		$sSQL="SELECT addID,addIsDefault,addName,addLastName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry FROM address WHERE addCustID=" . $_SESSION['clientID'] . " ORDER BY addIsDefault";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0){
				while($rs=ect_fetch_assoc($result)){
					print '<div class="ectdivcontainer">';
						print '<div class="ectdivleft">' . htmlspecials(trim($rs['addName'].' '.$rs['addLastName'])) . "<br />" . htmlspecials($rs['addAddress']) . (trim($rs['addAddress2'])!='' ? '<br />' . htmlspecials($rs['addAddress2']) : '') . "<br /> " . htmlspecials($rs['addCity']) . ", " . htmlspecials($rs['addState']) . ($rs['addZip']!='' ? '<br />' . htmlspecials($rs['addZip']) : '') . '<br />' . htmlspecials($rs['addCountry']) . '</div>';
						print '<div class="ectdivright"><ul><li><a class="ectlink" href="javascript:editaddress(' . $rs['addID'] . ')">' . $GLOBALS['xxEdAdd'] . '</a><br /><br /></li><li><a class="ectlink" href="javascript:deleteaddress(' . $rs['addID'] . ')">' . $GLOBALS['xxDeAdd'] . '</a></li></ul></div>';
					print '</div>';
				}
			}else{
				print '<div class="ectdiv2column">' . $GLOBALS['xxNoAdd'] . '</div>';
			}
			ect_free_result($result);
?>
					<div class="ectdiv2column"><ul><li><?php print $GLOBALS['xxPCAdd']?> <a class="ectlink" href="javascript:newaddress()"><?php print $GLOBALS['xxClkHere']?></a>.</li></ul></div>
				  </div>
			  </div>
			








                 <?php //product location management ?>

				<div class="ectdivhead" onclick="showhidesection('pl')"><a href="#" onclick="return false"><?php print 'Location Management'?></a><a href="#" onclick="return false"><img id="sectimagepl" src="images/arrow-down.png" style="float:right;margin-right:15px" /></a></div>
                      
				<?php	  	
							if(getpost('location')){
							$sql = "INSERT INTO productlocation (clientid, location, addID)  VALUES (" . $_SESSION['clientID'] . ",'" . escape_string(getpost('location')) . "', '" . $_SESSION['addId'] . "')";
							ect_query($sql) or ect_error();
							} 
				?>

				<?php

							$sSQL = "SELECT location, id FROM productlocation WHERE clientid = " .  $_SESSION['clientID'] . " AND addID = '" . $_SESSION['addId'] . "' ORDER BY  location ASC";
							$result=ect_query($sSQL) or ect_error();
		
			
				?>

					  
					   <form method="post" name="mainform" action="<?php print $thisaction?>">
						<input type="hidden" name="posted" value="1" />
					
					
			
						 
						
					   <div class="plformrow" style="display:none">
					   <div>
					   <table>
					   <tr><td><b>Locations:</b></td></tr>
					   <?php
					   	while($rs=ect_fetch_assoc($result)){
						print '<tr><td>&nbsp;</td><td>' .$rs['location'] . '</td><td>&nbsp;</td><td> <a class="ectlink" href="javascript:deletelocation(' . $rs['id'] . ')">Delete</a></tr>';
						   }
						ect_free_result($result);

						  ?>
						</table>
						
					
						<br/>
						<br/>


					   <table>
					   <tr><td>Add Location:</td><td><input type="text" name="location" /></td><td>&nbsp;</td><td><input type="submit" value="Add" name="submit_product_location" /></td></tr>
					 
					  
					   </table>


					   </form>


						</div>
					   </div>
					   

                                
                                
<?php		// Gift Registry Management
			if(@$enablewishlists==TRUE){
?>			  <div class="ectdivhead" onclick="showhidesection('gr')"><a href="#" onclick="return false"><?php print $GLOBALS['xxLisMan']?></a><a href="#" onclick="return false"><img id="sectimagegr" src="images/arrow-down.png" style="float:right;margin-right:15px" /></a></div>
			  <div class="grformrow" style="display:none">
				  <div class="ectclientlogingiftreg">
<?php			$sSQL="SELECT listID,listName,listAccess FROM customerlists WHERE listOwner=" . $_SESSION['clientID'] . " ORDER BY listName";
				$result=ect_query($sSQL) or ect_error();
				if(ect_num_rows($result)>0){
					while($rs=ect_fetch_assoc($result)){
						$numitems=0;
						$sSQL="SELECT COUNT(*) AS numitems FROM cart WHERE cartListID=" . $rs['listID'];
						$result2=ect_query($sSQL) or ect_error();
						if($rs2=ect_fetch_assoc($result2))
							if(! is_null($rs2['numitems'])) $numitems=$rs2['numitems'];
						ect_free_result($result2);
						print '<div class="ectdivcontainer">';
							print '<div class="ectdivleft">' . htmlspecials(trim($rs['listName'])) . ' (' . $numitems . ')</div>';
							print '<div class="ectdivright"><ul><li><a class="ectlink" href="javascript:deletelist(' . $rs['listID'] . ')">' . $GLOBALS['xxDelGRe'] . '</a></li>';
							if($numitems>0) print '<li><a href="cart.php?mode=sc&lid=' . $rs['listID'] . '">' . $GLOBALS['xxVieGRe'] . '</a></li>';
							print '</ul></div>';
						print '</div>';
						print '<div class="ectdiv2column">' . $GLOBALS['xxPubAcc'] . ':<br />' . $storeurl . 'cart.php?pli=' . $rs['listID'] . '&pla=' . $rs['listAccess'] . '</div>';
					}
				}else
					print '<div classectdiv2column">' . $GLOBALS['xxNoGRe'] . '</div>';
				ect_free_result($result);
?>					<div class="ectdivcontainer">
						<div class="ectdivleft"><input type="text" name="listname" size="40" maxlength="50" /></div>
						<div class="ectdivright"><?php print imageorbutton(@$imgcreatelist,'Create New List','createlist','createlist()',TRUE)?></div>
					</div>
				  </div>
			  </div>
<?php		}
			// Order Management
?>			  <div class="ectdivhead" onclick="showhidesection('om')"><a href="#" onclick="return false"><?php print $GLOBALS['xxOrdMan']?></a><a href="#" onclick="return false"><img id="sectimageom" src="images/arrow-down.png" style="float:right;margin-right:15px" /></a></div>
			  <div class="omformrow" style="display:none">
				  <div class="ectclientloginorders" style="display:table">
<?php		$hastracknum=FALSE;
			$sSQL="SELECT ordID FROM orders WHERE ordClientID=" . $_SESSION['clientID'] . " AND ordTrackNum<>''";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0) $hastracknum=TRUE;
			ect_free_result($result); ?>
					<div style="display:table-row">
						<div style="display:table-cell"><?php print $GLOBALS['xxOrdId']?></div>
						<div style="display:table-cell"><?php print $GLOBALS['xxDate']?></div>
						<div style="display:table-cell"><?php print $GLOBALS['xxStatus']?></div>
<?php		if($hastracknum) print '<div style="display:table-cell">' . $GLOBALS['xxTraNum'] . '</div>'; ?>
						<div style="display:table-cell"><?php print $GLOBALS['xxGndTot']?></div>
						<div style="display:table-cell"><?php print $GLOBALS['xxCODets']?></div>
					</div>
<?php
			$sSQL="SELECT ordID,ordDate,ordTrackNum,ordTotal,ordStateTax,ordCountryTax,ordShipping,ordHSTTax,ordHandling,ordDiscount," . getlangid('statPublic',64) . " FROM orders LEFT OUTER JOIN orderstatus ON orders.ordStatus=orderstatus.statID WHERE ordStatus<>1 AND ordClientID=" . $_SESSION['clientID'] . " ORDER BY ordDate";
			$result=ect_query($sSQL) or ect_error();
			if(ect_num_rows($result)>0){
				while($rs=ect_fetch_assoc($result)){
					print '<div style="display:table-row">' .
						'<div style="display:table-cell">' . $rs['ordID'] . '</div>' .
						'<div style="display:table-cell">' . date($dateformatstr, strtotime($rs['ordDate'])) . '</div>' .
						'<div style="display:table-cell">' . $rs[getlangid("statPublic",64)] . '</div>';
					if($hastracknum) print '<div style="display:table-cell">' . ($rs['ordTrackNum']!=''?$rs['ordTrackNum']:'&nbsp;') . '</div>';
					print '<div style="display:table-cell">' . FormatEuroCurrency(($rs['ordTotal']+$rs['ordStateTax']+$rs['ordCountryTax']+$rs['ordShipping']+$rs['ordHSTTax']+$rs['ordHandling'])-$rs['ordDiscount']) . '</div>' .
						'<div style="display:table-cell"><a class="ectlink" href="javascript:vieworder(' . $rs['ordID'] . ')">' . $GLOBALS['xxClkHere'] . '</a></div>' .
					'</div>';
				}
			}else
				print '<div>' . $GLOBALS['xxNoOrd'] . '</div>';
			ect_free_result($result);
?>
				  </div>
			  </div>
			</div>
		  </form>
<script type="text/javascript">
/* <![CDATA[ */
if(document.location.hash=='#ord')showhidesection('om');
else if(document.location.hash=='#list')showhidesection('gr');
else if(document.location.hash=='#add')showhidesection('am');
else if(document.location.hash=='#acct')showhidesection('ad');
/* ]]> */</script>
<?php	}
	} ?>
