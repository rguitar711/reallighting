<?php

// You host should be able to provide you with these settings if you do not know them already

$db_username = "dbo626601275";  // Your database login username
$db_password = "LightBulb";  // Your database login password
$db_name = "db626601275";      // The name of the database you wish to use
$db_host = "db626601275.db.1and1.com";   // The address of the database. Often this is localhost, but may be for example db.yoursite.com




//////////////////////////////////////////////////
// Please do not edit anything below this line. //
//////////////////////////////////////////////////

$GLOBALS['ectdatabase'] = new mysqli($db_host, $db_username, $db_password, $db_name);
if(mysqli_connect_error()){
	ob_clean();
	print '<html><head><title>Database connect error</title></head><body><div style="margin:20px;clear:both">Database Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error() . '</div>';
	print('<div style="margin:20px;clear:both">You need to set your database connection in vsadmin/db_conn_open.php</div>');
	print '<div style="margin:20px;clear:both">For help setting your database connection please see...<br />';
	print '<a href="http://www.ecommercetemplates.com/phphelp/ecommplus/instructions.asp#dbconn" target="_blank">http://www.ecommercetemplates.com/phphelp/ecommplus/instructions.asp#dbconn</a></div>';
	print '<div style="margin:20px;clear:both">We also have a support forum here...<br />';
	print '<a href="http://www.ecommercetemplates.com/support/" target="_blank">http://www.ecommercetemplates.com/support/</a></div>';
	die('</body></html>');
}
?>