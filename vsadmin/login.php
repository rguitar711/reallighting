<?php
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protect under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
session_cache_limiter('none');
session_start();
ob_start();
header('cache-Control: no-cache, no-store');
header('Pragma: no-cache');
include 'db_conn_open.php';
include 'includes.php';
include 'inc/languageadmin.php';
include 'inc/incfunctions.php';
$donotlogin=TRUE;
include 'inc/incloginfunctions.php';
?>
<!doctype html>
<html>
<head>
<title>Control panel login</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<?php adminassets() ?>
</head>

<body>
<div class="login">

<?php include 'inc/incdologin.php';?>
		
</div>
</body>
</html>
