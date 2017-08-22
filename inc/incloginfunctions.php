<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
global $incfunctionsdefined;
if(@$incfunctionsdefined!=TRUE && @$isadmincsv!=TRUE){
	print 'Illegal Call';
	flush();
	exit;
}
function updaterchecker(){
	global $yyNoNew,$yyLasChk,$yyChkMan,$yyClkHer,$yyNewRec,$yyRUSec,$yyChkNew,$disableupdatechecker,$padssfeatures,$yyWilLog,$yyCliCon,$yyFinMor,$yyClkHer,$yyContin,$yyCancel;
	$sSQL='SELECT adminVersion,updLastCheck,updRecommended,updSecurity,updShouldUpd,adminStoreURL FROM admin WHERE adminID=1';
	$result=ect_query($sSQL) or ect_error();
	$rs=ect_fetch_assoc($result);
	$storeVersion=$rs['adminVersion'];
	$updLastCheck=$rs['updLastCheck'];
	$recommendedversion=$rs['updRecommended'];
	$securityrelease=$rs['updSecurity'];
	$shouldupdate=$rs['updShouldUpd'];
	$storeURL=$rs['adminStoreURL'];
	ect_free_result($result);
	if(@$disableupdatechecker){
		$checkupdates=FALSE;
	}else{
		$checkupdates=(time()-strtotime($updLastCheck))>=(3*60*60*24);

		$admindatestr='Y-m-d';
		if(@$admindateformat=='') $admindateformat=0;
		if($admindateformat==1)
			$admindatestr='m/d/Y';
		elseif($admindateformat==2)
			$admindatestr='d/m/Y';
?>
<script type="text/javascript">
/* <![CDATA[ */
function ajaxcallback(){
	if(ajaxobj.readyState==4){
		var newtxt='';
		var xmlDoc=ajaxobj.responseXML.documentElement;
		var recver=xmlDoc.getElementsByTagName("recommendedversion")[0].childNodes[0].nodeValue;
		var shouldupdate=(xmlDoc.getElementsByTagName("shouldupdate")[0].childNodes[0].nodeValue=='true');
		var securityupdate=(xmlDoc.getElementsByTagName("securityupdate")[0].childNodes[0].nodeValue=='true');
		var haserror=(xmlDoc.getElementsByTagName("haserror")[0].childNodes[0].nodeValue=='true');
		if(haserror){
			newtxt='<span style="color:#FF0000;font-weight:bold">' + recver + '!</span><br /><?php print str_replace("'","\'",$yyChkMan)?> <a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank"><?php print str_replace("'","\'",$yyClkHer)?></a><br />';
			newtxt+='To disable this function please <a href="http://www.ecommercetemplates.com/phphelp/ecommplus/parameters.asp#dissupcheck" target="_blank"><?php print str_replace("'","\'",$yyClkHer)?></a><br />';
		}else{
			if(shouldupdate) newtxt='<a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank"><?php print str_replace("'","\'",$yyNewRec)?>: v' + recver + '</a><br />';
			if(securityupdate) newtxt+='<span style="color:#FF0000;font-weight:bold"><?php print str_replace("'","\'",$yyRUSec)?></span><br />';
		}
		document.getElementById("checkupdates").innerHTML=(shouldupdate?'<div class="should_update">'+newtxt+'</div>':'<div class="updates_okay"><?php print str_replace("'","\'",$yyNoNew)?></div>');
	}
}
function checkforupdates(){
	if(window.XMLHttpRequest)
		ajaxobj=new XMLHttpRequest();
	else
		ajaxobj=new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.onreadystatechange=ajaxcallback;
	ajaxobj.open("GET", "ajaxservice.php?action=checkupdates&storever=<?php print urlencode($storeVersion)?>", true);
	ajaxobj.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxobj.send(null);
}
<?php if($checkupdates) print "checkforupdates();\r\n";
?>/* ]]> */
</script>
<?php
	} ?><div class="updatecheck">
<div class="current_version<?php if($shouldupdate)print ' old_version'?>"><?php print $storeVersion?></div>
<span id="checkupdates"><?php
	if(@$disableupdatechecker)
		print '<div class="updates_okay">Auto update feature disabled! ' . $yyChkMan . ' <a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank">' . $yyClkHer . '</a></div>';
	elseif($checkupdates)
		print '<div class="updates_okay">' . $yyChkNew . '...</div>';
	else{
		if($shouldupdate){
			print '<div class="should_update'.($securityrelease?' security_update':'').'">';
			print '<a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank">' . $yyNewRec . ': v' . $recommendedversion . '</a>';
			if($securityrelease) print '<br><span>' . $yyRUSec.'</span>';
			print '</div>';
		}else
			print '<div class="updates_okay">'.$yyNoNew . '<div class="last_update">'.$yyLasChk.': <span class="update_date"><a href="javascript:checkforupdates()">' . date($admindatestr, strtotime($updLastCheck)) . '</a></span></div></div>';
	} ?></span>
</div>
<?php
	if(@$padssfeatures==TRUE){ ?>
<script type="text/javascript">
/* <![CDATA[ */
var ecttimo=0;
function dokeepalive(){
	clearTimeout(ecttimo);
	if(window.XMLHttpRequest)
		ajaxobj=new XMLHttpRequest();
	else
		ajaxobj=new ActiveXObject("MSXML2.XMLHTTP");
	ajaxobj.open("GET", "ajaxservice.php", true);
	ajaxobj.send('');
	document.getElementById('logindiv').style.display='none';
	setlotimos();
	return(false);
}
function setlotimos(){
	setTimeout("document.getElementById('logindiv').style.display='block';document.getElementById('contbutton').focus();",870000);
	ecttimo=setTimeout("document.location='logout.php';",900000);
}
if(ecttimo==0)setlotimos();
/* ]]> */
</script>
<div id="logindiv" style="display:none;position:absolute;width:100%;height:2000px;background-image:url(adminimages/opaquepixel.png);top:0px;left:0px;text-align:center;z-index:10000;">
<br /><br /><br /><br /><br /><br /><br /><br />
<table width="100%"><tr><td align="center">
<form method="post" action="admin.asp" onsubmit="return dokeepalive()">
<table width="350" cellspacing="2" cellpadding="2" bgcolor="#FFFFFF"><tr><td align="center"><br /><br /><?php print $yyWilLog?><br /><br /><?php print $yyCliCon?><br /><br />
<?php print $yyFinMor?> <a href="http://www.ecommercetemplates.com/pa-dss-compliance.asp#padss5" target="_blank"><strong><?php print $yyClkHer?></strong></a>.<br /><br />
</td></tr>
<tr><td align="center"><br /><input type="submit" id="contbutton" value="<?php print $yyContin?>" /> &nbsp; <input type="button" value="<?php print $yyCancel?>" onclick="document.getElementById('logindiv').style.display='none'" /><br /><br /></td></tr></table>
</form>
</td></tr></table>
</div>
<?php
	}
}
function adminassets(){?>
<meta http-equiv="X-UA-Compatible" content="IE=9" />
<!-- Mobile Specific Meta
================================================== -->
<meta name="robots" content="noindex,nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<link rel="stylesheet" type="text/css" href="adminstyle.css" />
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="assets/ectadmin.js"></script>
<?php }
function adminheader(){
	global $yyLmVwSt,$storeurl;?>
<div id="header1">
<div class="inner_half"><p class="viewstore"><a class="topbar" href="../" target="_blank"><?php print $yyLmVwSt?></a></p></div>
<div class="inner_half aright last">
<p class="log-out"><a class="topbar" href="logout.php"><?php print $GLOBALS['yyLLLogO']?></a></p>
</div>
</div>
<div id="header">
<div class="logotop">&nbsp;<a href="admin.php"><img src="adminimages/logo.png" alt="Ecommerce Templates"/></a></div>
<div class="toplinks">
<a class="topbar" href="http://www.ecommercetemplates.com/help.asp" target="_blank"><img src="adminimages/icon-help.png" title="<?php print $GLOBALS['yyLLHelp']?>" alt="<?php print $GLOBALS['yyLLHelp']?>"></a> 
<a href="http://www.ecommercetemplates.com/support/default.asp" target="_blank" class="topbar"><img src="adminimages/icon-forum.png" title="<?php print $GLOBALS['yyLLForu']?>" alt="<?php print $GLOBALS['yyLLForu']?>"></a>
<a href="http://www.ecommercetemplates.com/support/search.asp" target="_blank" class="topbar"><img src="adminimages/icon-search.png" title="<?php print $GLOBALS['yyLLForS']?>" alt="<?php print $GLOBALS['yyLLForS']?>" ></a>
<a href="http://www.ecommercetemplates.com/updaters.asp" target="_blank" class="topbar"><img src="adminimages/icon-update.png" alt="<?php print $GLOBALS['yyLLUpda']?>" title="<?php print $GLOBALS['yyLLUpda']?>"></a>
</div>
</div>
<?php }
function adminnavigation(){?>
<div id="admin_menu">
<div class='menu-button'>Menu</div>
<nav>
<ul id="nav" role="navigation">
 <li class="top-level"><a href="admin.php"><?php print $GLOBALS['yyLMStAd']?></a>
<ul class="sub-menu">
	<li><a href="admin.php"><?php print strtolower($GLOBALS['yyDashbd'])?></a></li>
    <li><a href="adminmain.php"><?php print $GLOBALS['yyLLMain']?></a></li>
    <li><a href="adminlogin.php"><?php print $GLOBALS['yyLLPass']?></a></li>
    <li><a href="adminaffil.php"><?php print $GLOBALS['yyLLAffl']?></a></li>
	<li><a href="adminemailmsgs.php"><?php print $GLOBALS['yyLMEmla']?></a></li>
    <li><a href="adminmailinglist.php"><?php print $GLOBALS['yyLMMaLi']?></a></li>
	<li><a href="adminerrormsgs.php"><?php print 'login error messages'?></a></li>
    <li><a href="admincontent.php"><?php print strtolower($GLOBALS['yyContReg'])?></a></li>
	<li><a href="adminipblock.php"><?php print strtolower($GLOBALS['yyIPBlock'])?></a></li>
</ul>
 <li class="top-level"><a href="adminorders.php"><?php print $GLOBALS['yyOrdAdm']?></a>
<ul class="sub-menu">
    <li><a href="adminorders.php"><?php print $GLOBALS['yyLLOrds']?></a></li>
    <li><a href="adminpayprov.php"><?php print $GLOBALS['yyLLPayP']?></a></li>
    <li><a href="adminclientlog.php"><?php print $GLOBALS['yyLLClLo']?></a></li>
    <li><a href="adminordstatus.php"><?php print $GLOBALS['yyLLOrSt']?></a></li>
    <li><a href="admingiftcert.php"><?php print $GLOBALS['yyLLGftC']?></a></li>
	<li><a href="punchoutreport.php"><?php print 'punchout report'?></a></li>
</ul>
 <li class="top-level"><a href="adminprods.php"><?php print $GLOBALS['yyLMPrAd']?></a>
<ul class="sub-menu">
	<li><a href="adminprods.php"><?php print $GLOBALS['yyLLProA']?></a></li>
    <li><a href="adminprodopts.php"><?php print $GLOBALS['yyLLProO']?></a></li>
    <li><a href="admincats.php"><?php print $GLOBALS['yyLLCats']?></a></li>
    <li><a href="admindiscounts.php"><?php print $GLOBALS['yyLLDisc']?></a></li>
	<li><a href="adminsearchcriteria.php"><?php print strtolower($GLOBALS['yySeaCri'])?></a></li>
    <li><a href="adminpricebreak.php"><?php print $GLOBALS['yyLLQuan']?></a></li>
    <li><a href="adminratings.php"><?php print $GLOBALS['xxLMRaRv']?></a></li>
	<li><a href="admincsv.php"><?php print strtolower($GLOBALS['yyCSVUpl'])?></a></li>
</ul>
 <li class="top-level"><a href="adminuspsmeths.php"><?php print $GLOBALS['yyLMShAd']?></a>
<ul class="sub-menu">
	<li><a href="adminstate.php"><?php print $GLOBALS['yyLLStat']?></a></li>
    <li><a href="admincountry.php"><?php print $GLOBALS['yyLLCoun']?></a></li>
    <li><a href="adminzones.php"><?php print $GLOBALS['yyLLZone']?></a></li>
    <li><a href="adminuspsmeths.php"><?php print $GLOBALS['yyLLShpM']?></a></li>
    <li><a href="admindropship.php"><?php print $GLOBALS['yyDrShpr']?></a></li>
</ul>
</ul>
</nav>
</div>
<?php }
function adminfooter(){?>
<div id="adminfooter">
<div class="row footer_block">
<div class="one_third footer_half">
<h4>Store Help</h4>
<ul>
<li><a href="http://www.ecommercetemplates.com/phphelp/ecommplus/about.asp">PHP Help Files</a></li>
<li><a href="http://www.ecommercetemplates.com/help/admin-help.asp">Admin Help Files</a></li>
<li><a href="http://www.ecommercetemplates.com/free_downloads.asp#usermanual">User manual</a></li>
<li><a href="http://www.ecommercetemplates.com/phphelp/ecommplus/parameters.asp">Store settings</a></li>
<li><a href="http://www.ecommercetemplates.com/tutorials/">Tutorials</a></li>
<li><a href="http://www.ecommercetemplates.com/support/">Support Forum</a></li>
</ul>
</div>
<div class="one_third footer_half">
<h4>Resources</h4>
<ul>
<li><a href="http://www.ecommercetemplates.com/affiliateinfo.asp" target="_blank"><?php print $GLOBALS['yyLLAffP']?></a></li>
<li><a href="http://www.ecommercetemplates.com/addsite.asp" target="_blank"><?php print $GLOBALS['yyLLSubm']?></a></li>
<li><a href="http://www.ecommercetemplates.com/payment_processors.asp">Payment providers</a></li>
<li><a href="http://www.ecommercetemplates.com/free_downloads.asp">Store downloads</a></li>
<li><a href="http://www.ecommercetemplates.com/ecommercetools.asp">Store tools &amp; add-ons</a></li>
<li><a href="http://www.ecommercetemplates.com/newsletter/default.asp">Ecommerce Templates News</a></li>
</ul>
</div>
<div class="one_third last">
<h4>Social media</h4>
<p>
<a href="http://www.facebook.com/EcommerceTemplates" target="_blank"><img src="adminimages/fb.gif" alt="Facebook" width="32" height="32" /></a>
<a href="http://twitter.com/etemplates/" target="_blank"><img src="adminimages/tw.gif" alt="Twitter" width="32" height="32" /></a>
<a href="http://www.linkedin.com/in/ecommercetemplates" target="_blank"><img src="adminimages/li.gif" alt="Linkedin" width="32" height="32" /></a>
<a href="http://www.youtube.com/user/EcommerceTemplates" target="_blank"><img src="adminimages/yt.gif" alt="YouTube" width="32" height="32" border="0" /></a>
<a href="https://plus.google.com/116093646501490888409/" target="_blank"><img src="adminimages/gl.gif" alt="Google Plus" width="32" height="32"/></a>
</p>
</div>
</div>
<?php updaterchecker(); ?>
</div>
<script> 
$("[role='navigation']").mainnav(); 
$('#responsive-table').stacktable({myClass:'admin-table-a-small'});
</script>
<?php }
if(@$adminlang==''){
	$result=ect_query("SELECT adminlang FROM admin WHERE adminid=1") or ect_error();
	if($rs=ect_fetch_assoc($result)){
		$adminlang=$rs['adminlang'];
		if($rs['adminlang']=='de'){
			include './inc/languageadmin_de.php';
		}elseif($rs['adminlang']=='es'){
			include './inc/languageadmin_es.php';
		}elseif($rs['adminlang']=='fr'){
			include './inc/languageadmin_fr.php';
		}elseif($rs['adminlang']=='it'){
			include './inc/languageadmin_it.php';
		}elseif($rs['adminlang']=='nl'){
			include './inc/languageadmin_nl.php';
		}
	}
	ect_free_result($result);
}
if(@$storesessionvalue=='') $storesessionvalue='virtualstore';
if(!@$donotlogin){
	if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.(substr($pathtossl,-1)=='/'?substr($pathtossl,0,-1):$pathtossl).$_SERVER['PHP_SELF']); exit; }
	$mustchangefordate=FALSE;
	if(@$padssfeatures==TRUE){
		header('Cache-Control: no-store,no-cache');
		header('Pragma: no-cache');
	}
	if(@$_SESSION['loggedon'] != $storesessionvalue && trim(@$_COOKIE['WRITECKL'])!='' && @$disallowlogin!=TRUE){
		$sSQL="SELECT adminID,adminUser,adminPWLastChange FROM admin WHERE adminPassword='" . escape_string(trim(@$_COOKIE['WRITECKP'])) . "' AND adminID=1";
		$result=ect_query($sSQL) or ect_error();
		if($rs=ect_fetch_assoc($result)){
			if($rs['adminUser']==trim(@$_COOKIE['WRITECKL'])){
				$_SESSION['loggedon'] = $storesessionvalue;
				$_SESSION['loggedonpermissions'] = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
				$_SESSION['loginid']=0;
				$_SESSION['loginuser']=$rs['adminUser'];
				if(time()-strtotime($rs['adminPWLastChange'])>(90*60*60*24) && @$padssfeatures==TRUE){ $_SESSION['mustchangepw']='B'; $mustchangefordate=TRUE; }
			}
		}
		ect_free_result($result);
		if(@$_SESSION['loggedon']!=$storesessionvalue){
			$sSQL="SELECT adminloginid,adminloginname,adminloginpermissions,adminLoginLastChange FROM adminlogin WHERE adminloginname='" . escape_string(trim(@$_COOKIE['WRITECKL'])) . "' AND adminloginpassword='" . escape_string(trim(@$_COOKIE['WRITECKP'])) . "'";
			$result=ect_query($sSQL) or ect_error();
			while($rs=ect_fetch_assoc($result)){
				if($rs['adminloginname']==trim(@$_COOKIE['WRITECKL'])){
					$_SESSION['loggedon'] = $storesessionvalue;
					$_SESSION['loggedonpermissions'] = $rs['adminloginpermissions'];
					$_SESSION['loginid']=$rs['adminloginid'];
					$_SESSION['loginuser']=$rs['adminloginname'];
					if(time()-strtotime($rs['adminLoginLastChange'])>(90*60*60*24) && @$padssfeatures==TRUE){ $_SESSION['mustchangepw']='B'; $mustchangefordate=TRUE; }
				}
			}
			ect_free_result($result);
		}
		logevent(@$_COOKIE['WRITECKL'],'LOGIN',@$_SESSION['loggedon']==$storesessionvalue,'LOGIN','');
	}
	if(@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']=='443')$prot='https://';else $prot='http://';
	if(@$_SESSION['loggedon'] != $storesessionvalue || @$disallowlogin==TRUE){
		header('Location: '.$prot.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/login.php');
		exit;
	}
	if((@$_SESSION['mustchangepw']!='' || $mustchangefordate) && ! (@$thispagename=='adminlogin')){
		header('Location: '.$prot.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/adminlogin.php');
		exit;
	}
}
$isprinter=FALSE;
$alreadygotadmin = getadminsettings();
?>