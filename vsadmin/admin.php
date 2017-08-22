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
include 'db_conn_open.php';
include 'includes.php';
include 'inc/languageadmin.php';
include 'inc/incfunctions.php';
include 'inc/incloginfunctions.php';
?>
<!doctype html>
<head>

<title>Admin Home</title>

<!-- Header assets -->

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

<div id="main">

<?php
	if(@$_SESSION['loggedon'] != $storesessionvalue)
		print '<table width="100%" border="0" bgcolor=""><tr><td width="100%" colspan="4" align="center"><p>&nbsp;</p><p>&nbsp;</p><p><strong>'.$yyOpFai.'</strong></p><p>&nbsp;</p><p>'.$yyCorCoo.' '.$yyCorLI.' <a href="login.php">'.$yyClkHer.'</a>.</p></td></tr></table>';
	else{
		updaterchecker();
		include 'inc/incadmin.php';
	} ?>
	</div>


<!-- Footer -->
<?php adminfooter() ?>

</body>
</html>
