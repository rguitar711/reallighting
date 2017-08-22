<?php 
$post			= file_get_contents( "php://input" );
//$post			= file_get_contents( "orderReceived2015-02-04.xml" );
include_once "./vsadmin/db_conn_open.php";
$timeStamp		= date("Y-m-d") . "T" . date( "H:i:sP" );
$xmlName		= "orderReceived" . $timeStamp . ".xml";
$FileHandle		= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $post ); 
fclose( $FileHandle );
$xmlName		= "log.txt";
$FileHandle		= fopen( "xml/" . $xmlName, 'a' ) or die( "can't open file" ); 
fwrite( $FileHandle, "\r\n" . $timestamp . ": XML For Order Confirmation Received returning Success " ); 
fclose( $FileHandle );
if( $post !== false ) { 
	$cXML			= simplexml_load_string( $post );
	$payloadIDArray		= explode( "@", $cXML[ 0 ][ 'payloadID' ] );
	$payloadID		= $payloadIDArray[ 0 ];
        $vendorID               = $payloadIDArray[ 1 ];
	$orderID		= $cXML->Request->OrderRequest->OrderRequestHeader[ 'orderID' ];
	$timeStamp		= date("Y-m-d") . "T" . date( "H:i:sP" );
        
        if($vendorID == 'payablesnexus.com')
        {
            $RLOrderID		= $cXML->Request->OrderRequest->ItemOut[0]->ItemID->SupplierPartAuxiliaryID;
        }
        else
        {
            $RLOrderID		= $cXML->Request->OrderRequest->ItemOut[0]->ItemID->SupplierPartID;
           
        }
						
		// Update PunchOutLog
		
	$logSql	= "UPDATE PunchOutLog SET OrderConfirmation_Timestamp = NOW(), PONumber = '$orderID' WHERE OrderID = '$RLOrderID';";
	mysql_query( $logSql );	
	
		// Update Order Notes, to set the PONumber 
	
	$poSql	= "UPDATE orders set ordAddInfo = 'Order Confirmed. PO Number = [$orderID]' WHERE ordID = '$RLOrderID';";
	mysql_query( $poSql );
	
        
         
	include_once "vsadmin/db_conn_open.php";
	$sql = "Select secret, identity, realLightingURL,eprocurementPayload,realLightingPayload,eprocurementURL from procurement where eprocurement ='$vendorID';";
       
        $result=mysql_query($sql) or die(error());
	while($rs = mysql_fetch_array($result)){
	
	
		 $realLightingURL = $rs['realLightingURL'];
                 
                  $eprocurementURL = $rs['emporcurementURL'];
		
		 $realLightingPayload = $rs['realLightingPayload'];
		
		 $eprocurementPayload = $rs['eprocurementPayload'];
                 
        }
	
	if( 1 ) {
		$response = '<?xml version="1.0"?><!DOCTYPE cXML SYSTEM ' . 
		'"http://xml.cxml.org/schemas/cXML/1.1.007/cXML.dtd"><cXML xml:lang="en" payloadID="' . 
		$payloadID . $realLightingPayload .'" timestamp="' . $timeStamp . 
		'"><Response><Status code="200" text="OK">'.$data[ 'OrderID' ].'</Status></Response></cXML>';
	} else {
		$response = '<?xml version="1.0"?><!DOCTYPE cXML SYSTEM ' . 
		'"http://xml.cxml.org/schemas/cXML/1.1.007/cXML.dtd"><cXML xml:lang="en" payloadID="' . 
			$payloadID . $realLightingPayload .'" timestamp="' . $timeStamp . 
			'"><Response><Status code="400" text="error"/><PunchOutSetupResponse></PunchOutSetupResponse></Response></cXML>';
	}
} else {
	$response = '<?xml version="1.0"?><!DOCTYPE cXML SYSTEM "http://xml.cxml.org/schemas/cXML/1.1.007/cXML.dtd">' . 
				'<cXML xml:lang="en" payloadID="' . $payloadID . $realLightingPayload .'" timestamp="' . $timeStamp . 
				'"><Response><Status code="400" text="error"/><PunchOutSetupResponse>' . 
				'</PunchOutSetupResponse></Response></cXML>';
}
$xmlName	= "editResponse" . $timeStamp . ".xml";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $response ); 
fclose( $FileHandle );
header('Content-type: text/xml');
echo $response;
?>