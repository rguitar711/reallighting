<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protected under law as the intellectual property of Internet
//Business Solutions SL. Any use, reproduction, disclosure or copying of any kind 
//without the express and written permission of Internet Business Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
if(@$forceloginonhttps && (@$_SERVER['HTTPS']!='on' && @$_SERVER['SERVER_PORT']!='443') && strpos(@$pathtossl,'https')!==FALSE){ header('Location: '.$pathtossl.'vsadmin/'.basename($_SERVER['PHP_SELF'])); exit; }
$success=TRUE;
$dorefresh=FALSE;
$repeatedattempts=FALSE;
if($success){
	if(getpost('posted')=="1"){
		$alreadygotadmin = getadminsettings();
		$thashedpw=dohashpw(getpost('pass'));
		$adminuser="";
		$adminpassword="";
		$adminuserlock=0;
		$sSQL = "SELECT adminEmail,adminUser,adminPassword,adminUserLock,adminPWLastChange FROM admin WHERE adminID=1";
		$result=ect_query($sSQL) or ect_error();
		$rs=ect_fetch_assoc($result);
		$datelastchanged=$rs['adminPWLastChange'];
		$adminuser = $rs['adminUser'];
		$adminpassword = $rs['adminPassword'];
		$adminuserlock=$rs['adminUserLock'];
		ect_free_result($result);
		if(@$storesessionvalue=='') $storesessionvalue='virtualstore';
		if(@$disallowlogin==TRUE){
			$success=FALSE;
			$errmsg = $yyLogSor;
		}elseif($adminuserlock>=6 && @$nopadsscompliance!=TRUE){
			$success=FALSE;
			$disallowlogin=TRUE;
			$repeatedattempts=TRUE;
		}elseif(! (getpost('user')==$adminuser && $thashedpw==$adminpassword)){
			$sSQL="SELECT adminloginid,adminloginname,adminloginpassword,adminloginpermissions,adminLoginLock FROM adminlogin WHERE adminloginname='" . escape_string(getpost('user')) . "'";
			$result=ect_query($sSQL) or ect_error();
			if($rs=ect_fetch_assoc($result)){
				if($rs['adminLoginLock']>=6 && @$nopadsscompliance!=TRUE){
					$success=FALSE;
					$disallowlogin=TRUE;
					$repeatedattempts=TRUE;
				}elseif($rs['adminloginpassword']==$thashedpw){
					$_SESSION['loggedon'] = $storesessionvalue;
					$_SESSION['loggedonpermissions'] = $rs['adminloginpermissions'];
					$_SESSION['loginid']=$rs['adminloginid'];
					$_SESSION['loginuser']=$rs['adminloginname'];
					$dorefresh=TRUE;
				}else{
					$success=FALSE;
					$errmsg = $yyLogSor;
				}
			}else{
				$success=FALSE;
				$errmsg = $yyLogSor;
			}
			ect_free_result($result);
		}else{
			$_SESSION['loggedon'] = $storesessionvalue;
			$_SESSION['loggedonpermissions'] = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
			$_SESSION['loginid']=0;
			$_SESSION['loginuser']=$adminuser;
			if($thashedpw=='50481f28d0f9c62842ad64b8985ab91c') $_SESSION['mustchangepw']='A';
			if(time()-strtotime($datelastchanged)>(90*60*60*24) && @$padssfeatures==TRUE) $_SESSION['mustchangepw']='B';
			$dorefresh=TRUE;
		}
		if(! $success){
			ect_query("UPDATE admin SET adminUserLock=adminUserLock+1 WHERE adminUser='".escape_string(getpost('user'))."'") or ect_error();
			ect_query("UPDATE adminlogin SET adminLoginLock=adminLoginLock+1 WHERE adminLoginName='".escape_string(getpost('user'))."'") or ect_error();
		}else{
			ect_query("UPDATE admin SET adminUserLock=0 WHERE adminUser='".escape_string(getpost('user'))."'") or ect_error();
			ect_query("UPDATE adminlogin SET adminLoginLock=0 WHERE adminLoginName='".escape_string(getpost('user'))."'") or ect_error();
		}
		logevent(getpost('user'),"LOGIN",$success,"LOGIN","");
		if(@$notifyloginattempt==TRUE&&@$disallowlogin!=TRUE){
			if(@$htmlemails==TRUE) $emlNl = "<br />"; else $emlNl="\n";
			$sMessage = "This is notification of a login attempt at your store."  . $emlNl;
			$sMessage.=$storeurl . $emlNl;
			if($success || (getpost('user')==$adminuser && getpost('pass')==$adminpassword))
				$sMessage.="A correct login / password was used." . $emlNl;
			else{
				$sMessage.="An incorrect login was used." . $emlNl .
					"Username: " . getpost('user') . $emlNl .
					"Password: " . getpost('pass') . $emlNl;
			}
			$sMessage.="User Agent: " . @$_SERVER["HTTP_USER_AGENT"] . $emlNl .
				"IP: " . @$_SERVER["REMOTE_ADDR"] . $emlNl;
			dosendemail($emailAddr, $emailAddr, '', 'Login attempt at your store', $sMessage);
		}
		if($success && getpost('cook')=='ON'){
			setcookie("WRITECKL",getpost('user'),time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
			setcookie("WRITECKP",$thashedpw,time()+(60*60*24*365), '/', '', @$_SERVER['HTTPS']=='on');
		}
		if($dorefresh){
			print '<meta http-equiv="refresh" content="1; url=admin.php">';
		}
	}
}
	if(getpost('posted')=="1" && $success){ ?>
	<div class="row centerit">
      <div class="login_message">
            <h2 class="centerit"><?php print $yyLogCor?></h2>
            <p><?php print $yyNowFrd?></p>
            <p><?php print $yyNoAuto?><a href="admin.php"><strong><?php print $yyClkHer?></strong></a>.</p>
      </div>
    </div>
<?php
	}else{
		if(@$disallowlogin){ $success=FALSE; $errmsg='<div class="login_message">' . 'Login Disabled' . ($repeatedattempts?' (Repeated login attempts)':'') . '</div>'; } ?>
			<form method="post" name="mainform" action="login.php">
			<input type="hidden" name="posted" value="1">
	<div class="row centerit">
        <div class="login_form round_all">
            <div class="login_header round_all" onclick="document.location='admin.php'"></div>
<?php	if(! $success){ ?>
			  <p class="ectred"><?php print $errmsg?></p>
<?php	}
		if(@$disallowlogin!=TRUE){ ?>
			<table>
              <tr>
                <td width="30%" align="right"><strong><?php print $yyUname?>: </strong></td>
				<td align="left"><input type="text" name="user" id="user" size="20" /></td>
			  </tr>
			  <tr>
                <td align="right"><strong><?php print $yyPass?>: </strong></td>
				<td align="left"><input type="password" name="pass" size="20" autocomplete="off" /></td>
			  </tr>
			  <tr>
                <td align="right"><input type="checkbox" name="cook" value="ON" /></td>
				<td align="left" class="small"><strong><?php print $yyWrCoo?></strong><br /><span style="font-size:10px"><?php print $yyNoRec?></span></td>
			  </tr>
			</table>
			<p><input type="submit" value="<?php print $yySubmit?>"></p>
<?php	} ?>
			  <p class="credit"><a href="http://www.ecommercetemplates.com/">Shopping Cart Software</a> by Ecommerce Templates</p>
			</form>
        </div>
    </div>
<script type="text/javascript">
<!--
document.getElementById('user').focus();
// -->
</script>
<?php
	} ?>
     