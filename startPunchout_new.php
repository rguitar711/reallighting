<?php 
session_start();
include_once "./vsadmin/db_conn_open.php";
//$sql		= "select * from customers where INT_CO_CODE=''";




$payload	= $_GET[ 'payloadID' ];
$vendorID       =$_GET['vendorID'];
$timeStamp	= date("Y-m-d") . "T" . date( "H:i:sP" );
$xmlName		= "log.txt";
$FileHandle		= fopen( "xml/" . $xmlName, 'a' ) or die( "can't open log file" ); 
fwrite( $FileHandle, "\r\n" . date("Y-m-d") . "T" . date( "H:i:sP" ) . ": User is in RealLighting Shopping" ); 
fclose( $FileHandle );
	
	// Get URL Punchout
	
$xmlName	= "url" . $payload . ".txt";
$FileHandle	= fopen( "xml/" . $xmlName, 'r' ) or die( "can't open payload file" ); 
$url		= fgets( $FileHandle ); 
fclose( $FileHandle );
	
	// Get Sender Identity
	
$xmlName			= "senderIdentity" . $payload . ".txt";
$FileHandle			= fopen( "xml/" . $xmlName, 'r' ) or die( "can't open senderIdentity file" ); 
$senderIdentity		= fgets( $FileHandle ); 
fclose( $FileHandle );
	
	// Get Buyer Cookie
	
$xmlName		= "buyerCookie" . $payload . ".txt";
$FileHandle		= fopen( "xml/" . $xmlName, 'r' ) or die( "can't open buyerCookie file" ); 
$BuyerCookie	= fgets( $FileHandle ); 
fclose( $FileHandle );
	
	// Set up variables in the session

$_SESSION[ 'punchoutUrl' ]	= $url;	
$_SESSION[ 'BuyerCookie' ]	= $BuyerCookie;
$_SESSION[ 'payloadID' ]	= $payload;
$_SESSION[ 'vendorID' ]   = $vendorID;

	// Update PunhOutLog
	
$logSql	= "UPDATE PunchOutLog SET SessionStarted_Timestamp = NOW() WHERE PayloadID = '$payload';";
mysql_query( $logSql );
	
	// Start Session
	
$sqlVendor = "Select propertyCodeType from procurement where eprocurement =  '$vendorID'";
$result=mysql_query($sqlVendor) or die(error());
	while($rs = mysql_fetch_array($result)){
          $column = $rs['propertyCodeType']; 
        }
        
$sql						= "select * from customerlogin where $column='$senderIdentity'";




$FileHandle		= fopen( "xml/" . $xmlName, 'a' ) or die( "can't open ".$xmlName .  " file" ); 
fwrite( $FileHandle, "\r\n\tExecuting sql: [$sql]" );
$res						= mysql_query( $sql );
$data						= mysql_fetch_array( $res );
fwrite( $FileHandle, "\r\n\tRedirecting to: Location: startPunchout2.php?email=" . $data[ 'Email' ] . "&password=" . $data[ 'custPassword' ]  );
fclose( $FileHandle );
header( "Location: startPunchout2.php?email=" . $data[ 'Email' ] . "&password=" . $data[ 'custPassword' ] );
?>