<?php
//HCS WTD 08 Feb 2009 CODE FOR ACL 5.7.0
$sql= "select paraName,paraValue from hcsparameters";
$result = $ectdatabase->query($sql) or die(mysqli_error());

while($row=$result->fetch_assoc())
{
	$$row['paraName']=$row['paraValue'];
	
	if(strtolower($$row['paraName'])=="true"){
		$$row['paraName']=TRUE;
		if($row['paraName'] == "multiship") $enableclientlogin=TRUE;
	}elseif(strtolower($$row['paraName'])=="false"){
		$$row['paraName']=FALSE;
		if($row['paraName'] == "multiship") $enableclientlogin=FALSE;
	}
}	

?>
