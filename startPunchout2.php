<?php 
session_start();
include_once "./vsadmin/db_conn_open.php";
include_once "vsadmin/inc/incfunctions.php";
	$success=true;
		
		$strEMail = trim((@$_GET["email"]));
		$strPassword = trim((@$_GET["password"]));

 		$strSQL = "SELECT * FROM customerlogin WHERE Active = 1 AND (Approved=1 or Approved is NULL) And clEmail = '".$strEMail."'";
		$rs=ect_query($strSQL) or ect_error();
		
		$theuser = trim((@$_GET["email"]));
	
		$strSQL = "SELECT clID,clUserName,clLoginLevel,clActions,clPercentDiscount from customerlogin WHERE (clUserName='" . $theuser . "' OR clEmail='" . $theuser . "')";
		$rsA=ect_query($strSQL) or ect_error();
	
 	 
if (!$rs || ect_num_rows($rs)==0 ){
	$_SESSION["Email"] = $_GET["email"];
	echo '<p align="center"><FONT color=#ff0000><b>Email address not found or your account has not been activated or not approved by admin.</B></FONT></P>'; 	  
}else{
	$num_res=ect_num_rows($rs);	
	for ($i=0; $i<$num_res; $i++)
	{
		$row=ect_fetch_assoc($rs);
		//$tCustID = $row["custID"];
		$tCustID = $row["clID"];
       	if (strtoupper(htmlspecialchars(stripslashes($row["clPW"]))) == strtoupper($strPassword)) 
       	{
			$num_f=mysqli_num_fields($rs);
			for ($ii=0; $ii<$num_f; $ii++) {
				$strName =  mysqli_fetch_field_direct($rs,$ii); 
        		$strValue = htmlspecialchars(stripslashes($row[$strName->name]));
				
				
        		//$_SESSION[$strName] = $strValue[$ii];
			}
							
			$_SESSION["ValidUser"] = true;
			if (ect_num_rows($rsA)>0){
			$rowA=ect_fetch_assoc($rsA);
			
			$_SESSION['clientID']=$rowA['clID'];
			$_SESSION["clientUser"]=$rowA['clUserName'];
			$_SESSION["clientEmail"]=$theuser;
					$_SESSION["clientActions"]=$rowA["clActions"];
					$_SESSION["clientLoginLevel"]=$rowA["clLoginLevel"];
					$_SESSION["clientPercentDiscount"]=(100.0-(double)$rowA["clPercentDiscount"])/100.0;
			}
			
			$sql = "UPDATE customerlogin SET custSRnd = ".$tCustID." WHERE Active = 1 AND (Approved=1 or Approved is NULL) And clEmail = '".$_GET["email"]."'";
			ect_query($sql) or ect_error();
	
			$strRequest = $_SERVER['QUERY_STRING'];									

		
			$sSQLt="select * from address where addCustID='".$_SESSION['clientID']."'";
			$address_rs=ect_query($sSQLt) or ect_error();
			if(ect_num_rows($address_rs) > 0){

				$rs = ect_fetch_assoc($address_rs);


				list($_SESSION['addID'])=$rs['addID'];

			}
			mysqli_free_result($address_rs);
	
									
						 
		}
	}
	
	$_SESSION["Email"] = $_GET["email"];
	
}
header("Location: myhomepage.php")?>