<?php 
session_start();
include_once "./vsadmin/db_conn_open.php";
include_once "vsadmin/inc/incfunctions.php";
//$sql		= "select * from customerlogin where INT_CO_CODE=''";
$payloadIDArray= explode( "|",$_GET[ 'payloadID' ]);
$payload = $payloadIDArray[0];
$vendorID = $payloadIDArray[1];



//$payload	= $_GET[ 'payloadID' ];
//$vendorID       =$_GET['vendorID'];
$timeStamp	= date("Y-m-d") . "T" . date( "H:i:sP" );
$xmlName		= "log.txt";
$FileHandle		= fopen( "xml/" . $xmlName, 'a' ) or die( "can't open log.txt file" ); 
fwrite( $FileHandle, "\r\n" . date("Y-m-d") . "T" . date( "H:i:sP" ) . ": User is in RealLighting Shopping" ); 
fclose( $FileHandle );
	
	// Get URL Punchout
	
$xmlName	= "url" . $payload . ".txt";
$FileHandle	= fopen( "xml/" . $xmlName, 'r' ) or die( "can't open url-Payload-.txt file" ); 
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
	
$sqlLog	= "UPDATE PunchOutLog SET SessionStarted_Timestamp = NOW() WHERE PayloadID = '$payload';";
$result=ect_query($sqlLog) or ect_error();
	
	// Start Session
	
$sqlVendor = "Select propertyCodeType from procurement where eprocurement =  '$vendorID'";
$result=ect_query($sqlVendor) or ect_error();
	while($rs =  ect_fetch_assoc($result)){
          $column = $rs['propertyCodeType']; 
        }
        
$sql						= "select * from customerlogin where $column='$senderIdentity'";


$FileHandle		= fopen( "xml/" . $xmlName, 'a' ) or die( "can't open last buyercookie file" ); 
fwrite( $FileHandle, "\r\n\tExecuting sql: [$sql]" );
$result						= ect_query($sql) or ect_error();
$rs						= ect_fetch_assoc( $result );
fwrite( $FileHandle, "\r\n\tRedirecting to: Location: startPunchout2.php?email=" . $rs[ 'clEmail' ] . "&password=" . $rs[ 'clPW' ]  );
fclose( $FileHandle );
header( "Location: startPunchout2.php?email=" . $rs[ 'clEmail' ] . "&password=" . $rs[ 'clPW' ] );
?>