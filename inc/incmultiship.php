<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protect under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
//HCS WTD 08 Feb 2009 CODE FOR ACL 5.7.0
//include "./vsadmin/inc/incemail.php";
include "incemail.php";
/*if(@$_SERVER['CONTENT_LENGTH'] != '' && $_SERVER['CONTENT_LENGTH'] > 10000 && $_SESSION["loggedon"] != $storesessionvalue)  exit;
	$This = "customer.php";
  $Security_Required = true; 
  include("incsecurity.php");*/

 

  if($_SESSION['clientID'] == ''){ ?>
    <div class="ectdiv ectclientlogin">
    <div class="ectdivhead"><?php print 'Shipping Page' ?></div>
    <div class="ectmessagescreen">
    <div><?php print $GLOBALS['xxMusLog']?></div>
    <div><?php print imageorbutton(@$imglogin,$GLOBALS['xxLogin'],'login',(@$forceloginonhttps?$pathtossl:'')."cart.php?mode=login&amp;refurl=".urlencode(@$_SERVER['PHP_SELF']),FALSE) ?></div>
    </div>
	</div>
<?php
	}
	else
	{
//  If ($usecustnav) {include("inccustnav2.php"); }
$flnm=$_SERVER['SCRIPT_NAME'];
$flnm=explode("/",$flnm);
$flnm=array_reverse($flnm);
if($flnm[0]=="manage_store.php")
	$clientID=$_REQUEST['clientID'];
else
{
	if(!empty($_SESSION['clientID']))
		$clientID=$_SESSION['clientID'];
	else
		$clientID=$_REQUEST['clientID'];
}
$success=TRUE;
$digidownloads=FALSE;
$allstates='';
$allcountries='';
function show_states($tstate){
	global $xxOutState,$allstates,$numallstates,$usestateabbrev;
	$foundmatch=FALSE;
	if($xxOutState!='') print '<option value="">' . $xxOutState . '</option>';
	for($index=0;$index<$numallstates;$index++){
		print '<option value="' . str_replace('"','&quot;',(@$usestateabbrev==TRUE?$allstates[$index]['stateAbbrev']:$allstates[$index]['stateName'])) . '"';
		if($tstate==$allstates[$index]['stateName'] || $tstate==$allstates[$index]['stateAbbrev']){
			print ' selected';
			$foundmatch=TRUE;
		}
		print '>' . $allstates[$index]['stateName'] . "</option>\n";
	}
	return $foundmatch;
}
function show_countries($tcountry){
	global $numhomecountries,$nonhomecountries,$allcountries,$numallcountries;
	for($index=0;$index<$numallcountries;$index++){
		print '<option value="' . str_replace('"','&quot;',$allcountries[$index]["countryName"]) . '"';
		if($tcountry==$allcountries[$index]["countryName"]) print " selected";
		print '>' . $allcountries[$index][2] . "</option>\n";
	}
}

?>
<script language="javascript" type="text/javascript">
<!--
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
function editaccount(){
	document.forms.mainform.action.value="editaccount";
	document.forms.mainform.submit();
}
function deleteaddress(theid){
	if(confirm("<?php print $xxDelAdd?>")){
		document.forms.mainform.action.value="deleteaddress";
		document.forms.mainform.theid.value=theid;
		document.forms.mainform.submit();
	}
}
function populateShipAdd()
{
	document.getElementById('saddress').value=document.getElementById('address').value;
	document.getElementById('scity').value=document.getElementById('city').value;
	document.getElementById('sstate').value = document.getElementById('state').value;
	if(document.getElementById('sstate').value=="")document.getElementById('sstate').selectedIndex=0;
	document.getElementById('sstate2').value=document.getElementById('state2').value;
	document.getElementById('scountry').value= document.getElementById('country').value;
	document.getElementById('szip').value=document.getElementById('zip').value;
	document.getElementById('sphone').value=document.getElementById('phone').value;
	
}
//--></script>
	  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3" >
<?php
		if(@$_POST['action']=='editaddress' || @$_POST['action']=='newaddress' || $_GET['act']=="ed"){
			if(trim($_GET['act'])!="" && is_numeric($_GET['addid']))
				$addID =$_GET['addid'];
			else
				$addID = str_replace("'",'',@$_POST['theid']);
			$addIsDefault='';
			$addName='';
			$addAddress='';
			$addAddress2='';
			$addState='';
			$addCity='';
			$addZip='';
			$addPhone='';
			$addCountry='';
			$addExtra1='';
			$addExtra2='';
			$havestate=FALSE;
			$sSQL = "SELECT stateName,stateAbbrev FROM states WHERE stateEnabled=1 ORDER BY stateName";
			$result = ect_query($sSQL) or ect_error();
			$numallstates=0;
			$numallcountries=0;
			while($rs=ect_fetch_assoc($result))
				$allstates[$numallstates++]=$rs;
			mysqli_free_result($result);
			$numhomecountries = 0;
			$nonhomecountries = 0;
			$sSQL = "SELECT countryName,countryOrder,".getlangid("countryName",8)." FROM countries WHERE countryEnabled=1 ORDER BY countryOrder DESC," . getlangid("countryName",8);
			
			$result =ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				$allcountries[$numallcountries++]=$rs;
				if($rs["countryOrder"]==2)$numhomecountries++;else $nonhomecountries++;
			}
			mysqli_free_result($result);
			if(@$_POST['action']=='editaddress' ||  $_GET['act']=="ed"){
				$sSQL = "SELECT addID,addIsDefault,addName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry,saddAddress,saddAddress2,saddState,saddCity,saddZip,saddPhone,saddCountry,addExtra1,addExtra2 FROM address WHERE addID=" . $addID . " AND addCustID='" . $clientID . "' ORDER BY addIsDefault";
				$result = ect_query($sSQL) or ect_error();;
				if($rs=ect_fetch_assoc($result)){
					$addIsDefault=$rs['addIsDefault'];
					$addName=$rs['addName'];
					$addAddress=$rs['addAddress'];
					$addAddress2=$rs['addAddress2'];
					$addState=$rs['addState'];
					$addCity=$rs['addCity'];
					$addZip=$rs['addZip'];
					$addPhone=$rs['addPhone'];
					
					$saddAddress=$rs['saddAddress'];
					$saddAddress2=$rs['saddAddress2'];
					$saddState=$rs['saddState'];
					$saddCity=$rs['saddCity'];
					$saddZip=$rs['saddZip'];
					$saddPhone=$rs['saddPhone'];
					
					$addCountry=$rs['addCountry'];
					$saddCountry=$rs['saddCountry'];
					$addExtra1=$rs['addExtra1'];
					$addExtra2=$rs['addExtra2'];
				}
			} 
			list($hcname)=mysqli_fetch_row (ect_query("select countryName from countries,admin where adminCountry=1"));
			$sql="select Lock_Store,Lock_Ship from customers where custID='".$_SESSION['custID']."'";
			list($lock_store,$lock_ship)=@mysqli_fetch_row (ect_query($sql));
			?>
		<form method="post" name="mainform" action="" onsubmit="return checkform(this)">
		<input type="hidden" name="clientID" id="clientID" value="<?php echo $clientID?>"/>
		<input type="hidden" name="action" value="<?php if(@$_POST['action']=='editaddress' || $_GET['act']=='ed') print "doeditaddress"; else print "donewaddress" ?>" />
		<?php 
			if(trim($_GET['act'])!="" && is_numeric($_GET['addid'])){?>
				<input type="hidden" name="pagerdir" id="pagerdir" value="cart.php" />
			<?php }
		?>
		<input type="hidden" name="theid" value="<?php print $addID?>" />
		<tr height="32"><td align="center" class="cobhl" bgcolor="#FFFFFF" colspan="2"><strong><?php print $xxEdAdd?></strong></td></tr>
		<?php	if(trim(@$extraorderfield1) != ''){ ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print (@$extraorderfield1required==TRUE ? '<font color="#FF0000">*</font>' : '') . $extraorderfield1 ?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><?php if(@$extraorderfield1html != '') print $extraorderfield1html; else print '<input type="text" name="ordextra1" id="ordextra1" size="20" value="' . $addExtra1 . '" />'?></td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><font color='#FF0000'>*</font>Store Name:</strong></td><td class="cobll" bgcolor="#FFFFFF">
		<?php
			if($lock_ship!="Y" && $lock_store=="Y"){?>
				<input type="hidden" name="name" id="name"  value="<?php print stripslashes($addName)?>"/>
			<?php } ?>
		<input type="text" name="name" id="name" size="20" value="<?php print stripslashes($addName)?>" <?php echo (($lock_store=="Y") ? "disabled":"") ?> />
		</td></tr>
		<tr><td align="center" class="cobhl" bgcolor="#FFFFFF" colspan="2"><strong>Billing Details</strong></td></tr>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><font color='#FF0000'>*</font><?php print $xxAddress?>:</strong></td><td class="cobll" bgcolor="#FFFFFF">
		<?php
			if($lock_ship!="Y"  && $lock_store=="Y"){?>
				<input type="hidden" name="address" id="address"  value="<?php print stripslashes($addAddress)?>" />
			<?php } ?>
		<input type="text" name="address" id="address" size="25" value="<?php print stripslashes($addAddress)?>" <?php echo (($lock_store=="Y") ? "disabled":"") ?>/>
		
		</td></tr>
		<?php	if(@$useaddressline2==TRUE){ ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxAddress2?>:</strong></td><td class="cobll" bgcolor="#FFFFFF">
		<?php
			if($lock_ship!="Y" && $lock_store=="Y"){?>
				<input type="hidden" name="address2" id="address2" value="<?php print stripslashes($addAddress2)?>" />
			<?php } ?>
		<input type="text" name="address2" id="address2" size="25" value="<?php print stripslashes($addAddress2)?>" <?php echo (($lock_store=="Y") ? "disabled":"") ?>/>
		</td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><font color='#FF0000'>*</font><?php print $xxCity?>:</strong></td><td class="cobll" bgcolor="#FFFFFF">
		<?php
			if($lock_ship!="Y"  && $lock_store=="Y"){?>
				<input type="hidden" name="city" id="city" value="<?php print stripslashes($addCity)?>" />
			<?php } ?>
		<input type="text" name="city" id="city" size="20" value="<?php print stripslashes($addCity)?>" <?php echo (($lock_store=="Y") ? "disabled":"") ?>/>
			
		</td></tr>
		<?php	if($numallstates>0){ ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><font color='#FF0000'><span id="outspandd" style="visibility:hidden">*</span></font><?php print $xxState?>:</strong></td><td class="cobll" bgcolor="#FFFFFF">
		<?php
			if($lock_ship!="Y" && $lock_store=="Y"){?>
				<input type="hidden" name="state" id="state" value="<?php print stripslashes($addState)?>" />
			<?php } ?>
		<select name="state" id="state" size="1" onchange="dosavestate('')" <?php echo (($lock_store=="Y") ? "disabled":"") ?>><?php $havestate = show_states($addState) ?></select>
		
		</td></tr>
		<?php	}
		
		if($nonhomecountries != 0){?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><font color='#FF0000'><span id="outspan" style="visibility:hidden">*</span></font><?php print $xxNonState?>:</strong></td><td class="cobll" bgcolor="#FFFFFF">
		<?php
			if($lock_ship!="Y" && $lock_store=="Y"){?>
				<input type="hidden" name="state2" id="state2"  value="<?php echo ($hcname!=$addCountry)? stripslashes($addState):""; ?>" />
			<?php } ?>
		<input type="text" name="state2" id="state2" size="20" value="<?php echo ($hcname!=$addCountry)? stripslashes($addState):""; ?>" <?php echo (($lock_store=="Y") ? "disabled":"") ?>/>
		
		</td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><font color='#FF0000'>*</font><?php print $xxCountry?>:</strong></td><td class="cobll" bgcolor="#FFFFFF">
		<?php
			if($lock_ship!="Y" && $lock_store=="Y"){?>
					<input type="hidden" name="country" id="country" value="<?php print stripslashes($addCountry)?>" />
			<?php } ?>
		<select name="country" id="country" size="1" onchange="checkoutspan('')" <?php echo (($lock_store=="Y") ? "disabled":"") ?>><?php show_countries($addCountry) ?></select>
		
		</td></tr>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><font color='#FF0000'><?php if(@$zipoptional != TRUE) print "*"?></font><?php print $xxZip?>:</strong></td><td class="cobll" bgcolor="#FFFFFF">
		<?php
			if($lock_ship!="Y" && $lock_store=="Y"){?>
				<input type="hidden" name="zip" id="zip" size="10" value="<?php print stripslashes($addZip)?>" />
			<?php } ?>
		<input type="text" name="zip" id="zip" size="10" value="<?php print stripslashes($addZip)?>" <?php echo (($lock_store=="Y") ? "disabled":"") ?>/>
		
		</td></tr>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxPhone?>:</strong></td><td class="cobll" bgcolor="#FFFFFF">
		<?php
			if($lock_ship!="Y"  && $lock_store=="Y"){?>
				<input type="hidden" name="phone" id="phone" value="<?php print stripslashes($addPhone)?>" />
			<?php  }?>
		<input type="text" name="phone" id="phone" size="20" value="<?php print stripslashes($addPhone)?>" <?php echo (($lock_store=="Y") ? "disabled":"") ?>/>
		
		</td></tr>
		
		<tr><td align="center" class="cobhl" bgcolor="#FFFFFF" colspan="2"><strong>Shipping Details</strong>
		<br />
<input type="button" value="Same As Billing Address" onclick="javascript:populateShipAdd();" <?php echo (($lock_ship=='Y'  && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))? "disabled":"")?>/></td></tr>
		
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxAddress?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><input type="text" name="saddress" id="saddress" size="25" value="<?php print stripslashes($saddAddress)?>" <?php echo (($lock_ship=='Y'  && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))? "disabled":"")?>/></td></tr>
		<?php	if(@$useaddressline2==TRUE){ ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxAddress2?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><input type="text" name="saddress2" id="saddress2" size="25" value="<?php print stripslashes($saddAddress2)?>" <?php echo (($lock_ship=='Y' && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))? "disabled":"")?>/></td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxCity?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><input type="text" name="scity" id="scity" size="20" value="<?php print stripslashes($saddCity)?>" <?php echo (($lock_ship=='Y'  && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))? "disabled":"")?>/></td></tr>
		<?php	if($numallstates>0){ ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxState?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><select name="sstate" id="sstate" size="1" onchange="dosavestate('')" <?php echo (($lock_ship=='Y'  && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))? "disabled":"")?>><?php $havestate = show_states(stripslashes($saddState)) ?></select></td></tr>
		<?php	}
			if($nonhomecountries != 0){ ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxNonState?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><input type="text" name="sstate2" id="sstate2" size="20" value="<?php echo ($hcname!=$saddCountry)?stripslashes($saddState):""; ?>" <?php echo (($lock_ship=='Y'  && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))? "disabled":"")?>/></td></tr>
		<?php	} ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxCountry?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><select name="scountry" id="scountry" size="1" onchange="checkoutspan('')" <?php echo (($lock_ship=='Y'  && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))? "disabled":"")?>><?php show_countries($saddCountry) ?></select></td></tr>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxZip?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><input type="text" name="szip" id="szip" size="10" value="<?php print stripslashes($saddZip)?>" <?php echo (($lock_ship=='Y'  && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))? "disabled":"")?>/></td></tr>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print $xxPhone?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><input type="text" name="sphone" id="sphone" size="20" value="<?php print stripslashes($saddPhone)?>" <?php echo (($lock_ship=='Y' && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))? "disabled":"")?>/></td></tr>
		
		<?php
			if($lock_ship=='Y' && (@$_POST['action']=='editaddress' || $_GET['act']=='ed'))
			{?>
				<input type="hidden" name="saddress" value="<?php print stripslashes($saddAddress)?>" />
				<input type="hidden" name="saddress2" value="<?php print stripslashes($saddAddress2)?>" />
				<input type="hidden" name="scity"  value="<?php print stripslashes($saddCity)?>" />
				<input  type="hidden" name="sstate" value="<?php echo stripslashes($saddState)?>">
				<input type="hidden" name="sstate2" value="<?php echo ($hcname!=$saddCountry)?stripslashes($saddState):""; ?>" />
				<input  type="hidden" name="scountry" value="<?php echo stripslashes($saddCountry)?>">
				<input type="hidden" name="szip" value="<?php print stripslashes($saddZip)?>"/>
				<input type="hidden" name="sphone" value="<?php print stripslashes($saddPhone)?>" />
				
			<?php
			}
		
		?>
		
		<?php	if(trim(@$extraorderfield2) != ''){ ?>
		<tr><td align="right" class="cobhl" bgcolor="#FFFFFF"><strong><?php print (@$extraorderfield2required==true ? '<font color="#FF0000">*</font>' : '') . $extraorderfield2 ?>:</strong></td><td class="cobll" bgcolor="#FFFFFF"><?php if(@$extraorderfield2html != '') print $extraorderfield2html; else print '<input type="text" name="ordextra2" id="ordextra2" size="20" value="' . stripslashes($addExtra2) . '" />'?></td></tr>
		<?php	} ?>
		<?php if($lock_ship!="Y" || $lock_store!="Y"){ ?>
		<tr><td align="center" colspan="2" class="cobll" bgcolor="#FFFFFF"><input type="submit" value="<?php print $xxSubmt?>"> <input type="button" value="Cancel" onclick="history.go(-1)"></td></tr>
		<?php } ?>
		</form>
<script language="javascript" type="text/javascript">
var checkedfullname=false;
var numhomecountries=0,nonhomecountries=0;
function checkform(frm)
{
<?php if(trim(@$extraorderfield1) != '' && @$extraorderfield1required==true){ ?>
if(frm.ordextra1.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $extraorderfield1?>\".");
	frm.ordextra1.focus();
	return (false);
}
<?php } ?>
if(frm.name.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print "Store Name"?>\".");
	frm.name.focus();
	return (false);
}
gotspace=false;
var checkStr = frm.name.value;
if(checkStr ){
for (i = 0; i < checkStr.length; i++){
	if(checkStr.charAt(i)==" ")
		gotspace=true;
}
}
/*if(!checkedfullname && !gotspace){
	alert("<?php print $xxFulNam?> \"<?php print $xxName?>\".");
	frm.name.focus();
	checkedfullname=true;
	return (false);
}*/
if(frm.address.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxAddress?>\".");
	frm.address.focus();
	return (false);
}
if(frm.city.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxCity?>\".");
	frm.city.focus();
	return (false);
}
if(frm.country.selectedIndex < numhomecountries){
<?php	if($numallstates>0 && $xxOutState != ''){ ?>
	if(frm.state.selectedIndex==0){
		alert("<?php print $xxPlsSlct . " " . $xxState?>.");
		frm.state.focus();
		return (false);
	}
<?php	} ?>
}else{
<?php	if($nonhomecountries>0){ ?>
	if(frm.state2.value==""){
		alert("<?php print $xxPlsEntr?> \"<?php print str_replace('<br />',' ',$xxNonState)?>\".");
		frm.state2.focus();
		return (false);
	}
<?php	} ?>}
if(frm.zip.value==""<?php if(@$zipoptional==TRUE) print ' && FALSE'?>){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxZip?>\".");
	frm.zip.focus();
	return (false);
}
/*if(frm.phone.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $xxPhone?>\".");
	frm.phone.focus();
	return (false);
}*/
<?php if(trim(@$extraorderfield2) != '' && @$extraorderfield2required==TRUE){ ?>
if(frm.ordextra2.value==""){
	alert("<?php print $xxPlsEntr?> \"<?php print $extraorderfield2?>\".");
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
	thestate = eval('document.forms.mainform.'+shp+'state');
	eval(shp+'savestate = thestate.selectedIndex');
}
function checkoutspan(shp){
if(shp=='s' && document.getElementById('saddress').value=="")visib='hidden';else visib='visible';<?php
if($nonhomecountries>0) print "thestyle = document.getElementById(shp+'outspan').style;\r\n";
if($numallstates>0){
	print "theddstyle = document.getElementById(shp+'outspandd').style;\r\n";
	print "thestate = eval('document.forms.mainform.'+shp+'state');\r\n";
} ?>
thecntry = eval('document.forms.mainform.'+shp+'country');
if(thecntry.selectedIndex < numhomecountries){<?php
if($nonhomecountries>0) print "thestyle.visibility='hidden';\r\n";
if($numallstates>0){
	print "theddstyle.visibility=visib;\r\n";
	if($lock_store!="Y")
	print "thestate.disabled=false;\r\n";
	print "eval('thestate.selectedIndex='+shp+'savestate');\r\n";
} ?>
}else{<?php
if($nonhomecountries>0) print "thestyle.visibility=visib;\r\n";
if($numallstates>0){ ?>
theddstyle.visibility="hidden";
if(thestate.disabled==false){
thestate.disabled=true;
eval(shp+'savestate = thestate.selectedIndex');
thestate.selectedIndex=0;}
<?php } ?>
}}
<?php
	if($numallstates>0) print "savestate = document.forms.mainform.state.selectedIndex;\r\n";
	print "numhomecountries=" . $numhomecountries . ";\r\n";
	print "checkoutspan('');\r\n";
?></script>
<?php	}elseif(@$_POST['action']=="deleteaddress" || @$_POST['action']=="doeditaddress" || @$_POST['action']=="donewaddress"){
			
			$addID = str_replace("'",'',@$_POST['theid']);
			$ordName=@$_POST['name'];
			$ordAddress=@$_POST['address'];
			$ordAddress2=@$_POST['address2'];
			
			$lenstat=strlen(trim($_POST['state']));
			
			if($lenstat > 0) 
				$ordState=@$_POST['state'];
			else
				$ordState=@$_POST['state2'];
			
			$ordCity=@$_POST['city'];
			$ordZip=@$_POST['zip'];
			$ordPhone=@$_POST['phone'];
			$ordCountry=@$_POST['country'];
			
			$sordAddress=@$_POST['saddress'];
			$sordAddress2=@$_POST['saddress2'];
			
			$slenstat=strlen(trim($_POST['sstate']));
			
			if($slenstat > 0) 
				$sordState=@$_POST['sstate'];
			else
				$sordState=@$_POST['sstate2'];
			
			$sordCity=@$_POST['scity'];
			$sordZip=@$_POST['szip'];
			$sordPhone=@$_POST['sphone'];
			$sordCountry=@$_POST['scountry'];
			
			$ordExtra1=@$_POST['ordextra1'];
			$ordExtra2=@$_POST['ordextra2'];
			if(@$_POST['action']=="deleteaddress"){
				$sSQL = "DELETE FROM address WHERE addID=" . $addID . " AND addCustID=" . $clientID;
				ect_query($sSQL) or ect_error();;
			}elseif(@$_POST['action']=="donewaddress"){
				$sSQL = "INSERT INTO address (addCustID,addIsDefault,addName,addAddress,addAddress2,addCity,addState,addZip,addCountry,addPhone,saddAddress,saddAddress2,saddCity,saddState,saddZip,saddCountry,saddPhone,addExtra1,addExtra2) VALUES (" . $clientID . ",0,'" . escape_string($ordName) . "','" . escape_string($ordAddress) . "','" . escape_string($ordAddress2) . "','" . escape_string($ordCity) . "','" . escape_string($ordState) . "','" . escape_string($ordZip) . "','" . escape_string($ordCountry) . "','" . escape_string($ordPhone) . "','" . escape_string($sordAddress) . "','" . escape_string($sordAddress2) . "','" . escape_string($sordCity) . "','" . escape_string($sordState) . "','" . escape_string($sordZip) . "','" . escape_string($sordCountry) . "','" . escape_string($sordPhone) . "','" . escape_string($ordExtra1) . "','" . escape_string($ordExtra2) . "')";
				ect_query($sSQL) or ect_error();;
			}elseif(@$_POST['action']=="doeditaddress"){
				$sSQL = "UPDATE address SET addName='" . escape_string($ordName) . "',addAddress='" . escape_string($ordAddress) . "',addAddress2='" . escape_string($ordAddress2) . "',addCity='" . escape_string($ordCity) . "',addState='" . escape_string($ordState) . "',addZip='" . escape_string($ordZip) . "',addCountry='" . escape_string($ordCountry) . "',addPhone='" . escape_string($ordPhone) . "',saddAddress='" . escape_string($sordAddress) . "',saddAddress2='" . escape_string($sordAddress2) . "',saddCity='" . escape_string($sordCity) . "',saddState='" . escape_string($sordState) . "',saddZip='" . escape_string($sordZip) . "',saddCountry='" . escape_string($sordCountry) . "',saddPhone='" . escape_string($sordPhone) . "',addExtra1='" . escape_string($ordExtra1) . "',addExtra2='" . escape_string($ordExtra2) . "' WHERE addCustID=" . $clientID . " AND addID=" . $addID;
				ect_query($sSQL) or ect_error();;
			}
			$tpath = $_SERVER['PHP_SELF'];
			if (trim(@$_SERVER['QUERY_STRING']) != "") $tpath .= "?".trim(@$_SERVER['QUERY_STRING']);
			if(!empty($_POST['pagerdir']))
				$tpath=$_POST['pagerdir']."?rd=1";
			print '<script language="javascript">location.replace("'.$tpath.'")</script>';
			
?>		<tr>
          <td class="cobll" bgcolor="#FFFFFF" width="100%" align="center">
			<br /><strong><?php print $xxUpdSuc?></strong><br /><br />
		  </td>
        </tr>
<?php	}else{ ?>
		  <form method="post" name="mainform" action="">
			<input type="hidden" name="posted" value="1" />
			<input type="hidden" name="action" value="none" />
			<input type="hidden" name="theid" value="" />
			 <tr> 
           <td class="cobhl" colspan="2" bgcolor="#FFFFFF" align="center" height="34"><strong>CHOOSE ADDRESS  </strong></td>
			  </tr>
       
			  <tr> 
          <td class="cobll" bgcolor="#FFFFFF" height="34" align="center">
				  <table class="cobtbl" width="100%" border="0" cellspacing="1" cellpadding="3" >
					<?php
					if (isset($clientID) && $clientID != ""){
						$sSQL = "SELECT addID,addIsDefault,addName,addAddress,addAddress2,addState,addCity,addZip,addPhone,addCountry FROM address WHERE addCustID=" . $clientID . " ORDER BY addIsDefault";
						
						$result = ect_query($sSQL) or ect_error();
						if(ect_num_rows($result)>0){
								print '<tr><td class="cobll" align="left"><strong>Store Name</strong></td>';
								print '<td class="cobll" align="left"><strong>City, State</strong></td>';
								print '<td class="cobll" align="left"><strong>Country</strong></td>';	
								//if(empty($_SESSION['loggedon']))
								print '<td class="cobll" align="left"><strong>Shop For Store</strong></td>';							
								print '<td class="cobhl" align="left"><strong>Edit</strong></td>';
								print '<td class="cobhl" align="left"><strong>Delete</strong></td></tr>';						
							while($rs=ect_fetch_assoc($result)){
								print '<tr><td class="cobll" align="left">' . stripslashes($rs['addName']) . '</td>';
								print '<td class="cobll" align="left">' . stripslashes($rs['addCity']) . ", " . stripslashes($rs['addState']) .'</td>';
								print '<td class="cobll" align="left">' . $rs['addCountry'] . '</td>';	
								//if(empty($_SESSION['loggedon']))						
								print '<td class="cobhl" align="left" style="padding-left:30px"><a href="myhomepage.php?addid='.$rs['addID'].'">' . "Select" . '</a></td>';
								print '<td class="cobhl" align="left"><a href="javascript:editaddress(' . $rs['addID'] . ')">' . "Edit" . '</a></td>';
								print '<td class="cobhl" align="left"><a href="javascript:deleteaddress(' . $rs['addID'] . ')">' . "Delete" . '</a></td></tr>';
							}
						}else{
								print '<tr><td class="cobll" align="center" colspan="5" height="34">' . $xxNoAdd . '</td></tr>';
						}
					}
					$sql="select Lock_Store from customers where custID='".$_SESSION['custID']."'";
			list($lock_store)=@mysqli_fetch_row (ect_query($sql));
					if (isset($clientID) && $clientID != "" && $lock_store<>'Y'){
					?>
					<tr><td class="cobhl" colspan="5" align="center"><br /><?php print $xxPCAdd?> <a href="javascript:newaddress()"><strong><?php print $xxClkHere?></strong></a>.
					<?php
						$flnm=$_SERVER['SCRIPT_NAME'];
						$flnm=explode("/",$flnm);
						$flnm=array_reverse($flnm);
						//if(!empty($_SESSION["loggedon"]) && empty($_SESSION["custID"]) )
						if($flnm[0]=="manage_store.php")
						{?>
							<input type="button" value="Back" onclick="javascript:window.location.href='user-editlist.php';" />
						<?php
						}
							
					?>
					</td></tr>
					<?php
					}
					?>
				  </table>
				</td>
			  </tr>
			  
		  </form>
<?php	}
	 ?>
      </table>

<?php } ?>
