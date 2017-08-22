<!--HCS WTD 08 Feb 2009 CODE FOR ACL 5.7.0-->
<?php 
	$action=@$_GET['action_nm'];
	if(trim($action)!="")
		$file_name="vsadmin/inc/inc".$action.".php";
	else
		$file_name="vsadmin/inc/inccustlogin.php";
	
	if(!file_exists($file_name))
		$file_name="vsadmin/inc/inccustlogin.php";
		
	include $file_name;

?>