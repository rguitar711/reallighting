<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protect under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
@ini_set('session.gc_maxlifetime', 1440);
session_cache_limiter('none');
session_start();
ob_start();
$isvsadmindir=TRUE;
include 'db_conn_open.php';
function lofect_query($ectsql){
	return(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->query($ectsql):mysql_query($ectsql));
}
function lofect_fetch_assoc($ectres){
	return(@$GLOBALS['ectdatabase']?$ectres->fetch_assoc():mysql_fetch_assoc($ectres));
}
function lofect_free_result($ectres){
	@$GLOBALS['ectdatabase']?$ectres->free_result():mysql_free_result($ectres);
}
function lofect_error(){
	print(@$GLOBALS['ectdatabase']?$GLOBALS['ectdatabase']->error:mysql_error());
}
$result=lofect_query("SELECT adminlang FROM admin WHERE adminid=1") or lofect_error();
if($rs=lofect_fetch_assoc($result)){
	$adminlang=$rs['adminlang'];
	if($rs['adminlang']=='de'){
		include './inc/languageadmin_de.php';
		include './inc/languagefile_de.php';
	}elseif($rs['adminlang']=='es'){
		include './inc/languageadmin_es.php';
		include './inc/languagefile_es.php';
	}elseif($rs['adminlang']=='fr'){
		include './inc/languageadmin_fr.php';
		include './inc/languagefile_fr.php';
	}elseif($rs['adminlang']=='it'){
		include './inc/languageadmin_it.php';
		include './inc/languagefile_it.php';
	}elseif($rs['adminlang']=='nl'){
		include './inc/languageadmin_nl.php';
		include './inc/languagefile_nl.php';
	}else{
		$adminlang='en';
		include './inc/languageadmin.php';
		include './inc/languagefile_en.php';
	}
}
lofect_free_result($result);
include 'includes.php';
include 'inc/incfunctions.php';
include 'inc/incloginfunctions.php';
$isprinter=(@$_GET['printer']=='true' || @$_GET['invoice']=='true');
?>
<!doctype html>
<head>

<title>Admin Orders</title>
<?php adminassets() ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php print $adminencoding ?>"/>
</head>
<body <?php if($isprinter) print 'class="printbody"'?>>
<?php if(! $isprinter){ ?>

<!-- Header section -->
<?php adminheader() ?>

<!-- Left menus -->
<?php adminnavigation(); ?>

<?php } ?>
<!-- main content -->
<a href="marketbasketreport.php">Punchout Report</a>
<?php
	if(! $isprinter) print '<div id="main">'; else print '<div id="mainprint"' . (@$righttoleft==TRUE ? ' style="direction:rtl;"' : '') . '>';
	if(substr(@$_SESSION['loggedonpermissions'],1,1)!='X')
		print '<table width="100%" border="0" bgcolor=""><tr><td width="100%" colspan="4" align="center"><p>&nbsp;</p><p>&nbsp;</p><p><strong>'.$yyOpFai.'</strong></p><p>&nbsp;</p><p>'.$yyNoPer.' <br />&nbsp;<br />&nbsp;<br />&nbsp;<br />&nbsp;<br /><a href="admin.php"><strong>'.$yyAdmHom.'</strong></a>.</p><p>&nbsp;</p></td></tr></table>';
	else
		include 'inc/incorders.php';
	print "</div>"; ?>



<!-- Footer -->
<?php if(! $isprinter) adminfooter() ?>

</body>
</html>
