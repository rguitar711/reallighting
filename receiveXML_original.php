<?php

$post		= file_get_contents( "php://input" );
//$post		= file_get_contents( "TESTNEXUS.xml" );

$timeStamp	= date("Y-m-d") . "T" . date( "H:i:sP" );
$xmlName	= "setupRequest" . $timeStamp . ".xml";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $post ); 
fclose( $FileHandle );
$xmlName		= "log.txt";
$FileHandle		= fopen( "xml/" . $xmlName, 'a' ) or die( "can't open file" ); 
fwrite( $FileHandle, "\r\n" . $timeStamp . ": XML Logging in Received" ); 
fclose( $FileHandle );
if( $post !== false ) { 
	$cXML			= simplexml_load_string( $post );
	$sharedSecret	= $cXML->Header->Sender->Credential->SharedSecret;
	$url			= $cXML->Request->PunchOutSetupRequest->BrowserFormPost->URL;
	$buyerCookie	= $cXML->Request->PunchOutSetupRequest->BuyerCookie;
	$senderIdentity	= $cXML->Header->Sender->Credential->Identity;
	$intCoCode		= $cXML->Header->To->Credential->Identity;
	$payloadIDArray		= explode( "@", $cXML[ 0 ][ 'payloadID' ]);
	$payloadID		= $payloadIDArray[ 0 ];
        $vendorID               = $payloadIDArray[ 1 ];
	$realLightingURL = "";
	$secret = "";
	$identity = "";
	$eprocurementURL = "";
	$realLightingPayload = "";
	$eprocurementPayload= "";
	
	
        
	include_once "vsadmin/db_conn_open.php";
	$sql = "Select secret, identity, realLightingURL,eprocurementPayload,realLightingPayload,eprocurementURL from procurement where eprocurement ='$vendorID';";
       
      
        
        
        
	$result=mysql_query($sql) or die(error());
	while($rs = mysql_fetch_array($result)){
	
	
		 $realLightingURL = $rs['realLightingURL'];
		
		 $secret = $rs['secret'];
		 
		 $identity = $rs['identity'];
		
		 $eprocurementURL = $rs['emporcurementURL'];
		
		 $realLightingPayload = $rs['realLightingPayload'];
		
		 $eprocurementPayload = $rs['eprocurementPayload'];
	}
		if( $sharedSecret == $secret && $senderIdentity == $identity ) {
		$xmlName		= "log.txt";
		$FileHandle		= fopen( "xml/" . $xmlName, 'a' ) or die( "can't open file" ); 
		fwrite( $FileHandle, "\r\n" . $timeStamp . ": Login Succeeded" ); 
		fclose( $FileHandle );
		
		$sql			= "INSERT INTO `PunchOutLog` (`id`, `PayloadID`, `SetupRequest_Timestamp`, `SessionStarted_Timestamp`, `OrderSent_Timestamp`, `OrderID`, `OrderConfirmation_Timestamp`, `PONumber`) VALUES ( NULL, '$payloadID', NOW(), NULL, NULL, NULL, NULL, NULL );";
		mysql_query( $sql );
		$response	= '<?xml version="1.0"?><!DOCTYPE cXML SYSTEM "http://xml.cxml.org' . 
						'/schemas/cXML/1.1.009/cXML.dtd"><cXML xml:lang="en-US" payloadID="' . $payloadID . $realLightingPayload .'" timestamp="' . $timeStamp . 
						'"><Response><Status code="200" text="success" ></Status><PunchOutSetupResponse><StartPage>' . 
						'<URL>'. $realLightingURL .'/startPunchout.php?payloadID=' . $payloadID . '&vendorID='.$vendorID .
						'</URL></StartPage></PunchOutSetupResponse></Response></cXML>';
						
						
	} else {
		$response	= '<?xml version="1.0"?>
<!DOCTYPE cXML SYSTEM "http://xml.cxml.org/schemas/cXML/1.1.007/cXML.dtd">
<cXML xml:lang="en" payloadID="' . $payloadID . $eprocurementPayload .'" timestamp="' . $timeStamp . '">
	<Response>
		<Status code="400" text="error - credential error"/>
		<PunchOutSetupResponse>
		</PunchOutSetupResponse>
	</Response>
</cXML>'; 


	}
} else {
	$response	= '<?xml version="1.0"?>
<!DOCTYPE cXML SYSTEM "http://xml.cxml.org/schemas/cXML/1.1.007/cXML.dtd">
<cXML xml:lang="en" payloadID="' . $payloadID . $eprocurementPayload .'" timestamp="' . $timeStamp . '">
	<Response>
		<Status code="400" text="error - post failed"/>
		<PunchOutSetupResponse>
		</PunchOutSetupResponse>
	</Response>
</cXML>';

}
$xmlName	= "setupResponse" . $timeStamp . ".xml";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $response ); 
fclose( $FileHandle );
$xmlName	= "url" . $payloadID . ".txt";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $url ); 
fclose( $FileHandle );
$xmlName	= "buyerCookie" . $payloadID . ".txt";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $buyerCookie ); 
fclose( $FileHandle );
$xmlName	= "payloadID" . $payloadID . ".txt";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $payloadID ); 
fclose( $FileHandle );
$xmlName	= "senderIdentity" . $payloadID . ".txt";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $intCoCode ); 
fclose( $FileHandle );
// header('Content-type: text/xml');
echo $response;
?>