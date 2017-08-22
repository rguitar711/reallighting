<?php 
$post		= file_get_contents( "php://input" );
$timeStamp	= date("Y-m-d") . "T" . date( "H:i:sP" );
$xmlName	= "setupRequest" . $timeStamp . ".xml";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $post ); 
fclose( $FileHandle );
if( $post !== false ) { 
	$cXML			= simplexml_load_string( $post );
	$sharedSecret	= $cXML->Header->Sender->Credential->SharedSecret;
	$url			= $cXML->Request->PunchOutSetupRequest->BrowserFormPost->URL;
	$senderIdentity	= $cXML->Header->Sender->Credential->Identity;
	$payloadID		= explode( "@", $cXML[ 0 ][ 'payloadID' ]);
	$payloadID		= $payloadID[ 0 ];
	if( $sharedSecret == 'realLightning' && $senderIdentity == 'testRealLightning' ) {
		$response	= '<?xml version="1.0"?>
<!DOCTYPE cXML SYSTEM "http://xml.cxml.org/schemas/cXML/1.1.009/cXML.dtd">
<cXML xml:lang="en-US" payloadID="' . str_replace( ".", "", $payloadID ) . '@reallighting.psynapsis.net" timestamp="' . $timeStamp . '"><Response><Status code="200" text="success" ></Status><PunchOutSetupResponse><StartPage><URL>http://reallighting.psynapsis.net/startPunchout.php?payloadID=' . str_replace( ".", "", $payloadID ) . '</URL></StartPage></PunchOutSetupResponse></Response></cXML>';
	} else {
		$response	= '<?xml version="1.0"?>
<!DOCTYPE cXML SYSTEM "http://xml.cxml.org/schemas/cXML/1.1.007/cXML.dtd">
<cXML xml:lang="en" payloadID="' . $payloadID . '@birchstreet.com" timestamp="' . $timeStamp . '">
	<Response>
		<Status code="400" text="error"/>
		<PunchOutSetupResponse>
		</PunchOutSetupResponse>
	</Response>
</cXML>';
	}
} else {
	$response	= '<?xml version="1.0"?>
<!DOCTYPE cXML SYSTEM "http://xml.cxml.org/schemas/cXML/1.1.007/cXML.dtd">
<cXML xml:lang="en" payloadID="' . $payloadID . '@birchstreet.com" timestamp="' . $timeStamp . '">
	<Response>
		<Status code="400" text="error"/>
		<PunchOutSetupResponse>
		</PunchOutSetupResponse>
	</Response>
</cXML>';
}
$xmlName	= "setupResponse" . $timeStamp . ".xml";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $response ); 
fclose( $FileHandle );
$xmlName	= "url" . str_replace( ".", "", $payloadID ) . ".txt";
$FileHandle	= fopen( "xml/" . $xmlName, 'w' ) or die( "can't open file" ); 
fwrite( $FileHandle, $url ); 
fclose( $FileHandle );
// header('Content-type: text/xml');
echo $response;
?>