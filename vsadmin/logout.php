<?php
session_cache_limiter('none');
session_start();
ob_start();
//This code is copyright (c) Internet Business Solutions SL, all rights reserved.
//The contents of this file are protect under law as the intellectual property
//of Internet Business Solutions SL. Any use, reproduction, disclosure or copying
//of any kind without the express and written permission of Internet Business 
//Solutions SL is forbidden.
//Author: Vince Reid, vince@virtualred.net
include 'db_conn_open.php';
include 'includes.php';
include 'inc/languageadmin.php';
include 'inc/incfunctions.php';
$donotlogin=TRUE;
include 'inc/incloginfunctions.php';
?>
<!doctype html>
<head>

<title>Admin Logout</title>
<?php adminassets() ?>
<meta http-equiv="Content-Type" content="text/html; charset=<?php print $adminencoding ?>"/>
</head>
<body>

<!-- main content -->
<div class="login">

<?php include "inc/incdologout.php";?>
		
      </div>

</body>
</html>
