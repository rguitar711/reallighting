<pre><?php 
if( isset( $_POST[ 'SharedSecret' ])) { 	
	$url	= "http://reallighting.psynapsis.net/receiveXML2.php";
	$ch		= curl_init( $url ); 
	$cXML	= '<?xml version="1.0"?><cXML><Header payloadID="18390412402.259879434589.0316781099499912314200898231566.4394231566512.2309994231566536475@birchstreet.com" xml:lang="en-US" timestamp="2012-12-13T18:02:37-06:00"><From><Credential><Identity domain="NetworkID">24591839200</Identity></Credential></From><To><Credential domain="birchstreet.com"><Identity>a3aSD24131S</Identity></Credential></To><Sender><Credential domain="birchstreet.com"><Identity>testRealLightning</Identity><SharedSecret>realLightning</SharedSecret></Credential><UserAgent>V7</UserAgent></Sender></Header><Request deploymentMode="tester"><PunchOutSetupRequest operation="create"><BuyerCookie>11-3-2003-9-37-6-484-USERLOGIN</BuyerCookie><BrowserFormPost><URL>http://216.154.238.116:80/j4/ReceiveItemsPunchout.jsp</URL></BrowserFormPost></PunchOutSetupRequest></Request></cXML>';
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $cXML); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_REFERER, 'http://reallightning.psynapsis.net');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_POST, 1);  
	$punchoutResponse	= curl_exec ($ch); 
	curl_close ($ch); 		
	echo htmlentities( $punchoutResponse );
}
?></pre><br />
<br />
<form method="post">
Shared Secret:
<input name="SharedSecret" />
</form>
<p id="sendData"></p>
<p id="returnData"></p>