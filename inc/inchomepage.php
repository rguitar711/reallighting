<?php


if(!@$GLOBALS['incfunctionsdefined']){
    print 'No incfunctions.php file';
    exit;}




	
	if(isset($_POST['addtohomepage'])){


			 


$fullid = $_GET['prodid'];


$prodid=str_replace(@$detlinkspacechar,' ',$_GET['prodid']);


			$sql ="INSERT INTO productandlocation (clientID, prodID, addID) VALUES('" .  $_SESSION['clientID'] . "','" .  $prodid . "','" . $_SESSION['addId'] . "') ";
				ect_query($sql) or ect_error();	
				$message = "Saved to HomePage";
			
		
	
		
			header("Location: proddetail.php?prod=" .$fullid . "&message=".$message);

	}



	





	




