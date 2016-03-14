<?php
session_start ();

require_once "../Slim/Slim.php";
Slim\Slim::registerAutoloader ();
$app = new \Slim\Slim (); // slim run-time object
require_once "../conf/config.inc.php"; // include configuration file

if (empty ( $_SESSION ["artistsList"] ))
	$_SESSION ["artistsList"] = array (); // initialitation of users container

$app->map ( "/artists(/:id)", function ($elementID = null) use($app) {
	$body = $app->request->getBody (); // get the body of the HTTP request (from client)
	$decBody = json_decode ( $body, true ); // this transform the string into an associative array
	$httpMethod = $app->request->getMethod ();
	
	// initialisations
	$responseBody = null;
	$responseCode = null;
	
	switch ($httpMethod) {
		case "GET" :
			if (empty ( $_SESSION )) {
				$respondeCode = HTTPSTATUS_NOCONTENT;
			} else {
				if ($elementID != null) {
					if(($artist = $_SESSION["artistsList"][$elementID]) != null)
					{
						$responseBody=json_encode($artist);
						$respondeCode=HTTPSTATUS_OK;
					}
					
					else {
						$respondeCode=HTTPSTATUS_NOCONTENT;
					}
					
					// TODO:
				} else {
					$respondeCode=HTTPSTATUS_NOCONTENT;
					// TODO:
				}
			}
			break;
		case "POST" :
			if ($body != null)
			{
				$newAlhanumericalID = "i" . rand(0,100);	
				$_SESSION ["artistsList"]["$newAlhanumericalID"]=array($decBody["name"], $decBody["surname"]);
				
				$respondeCode = HTTPSTATUS_CREATED;
			}
			
			else {
				$respondeCode=HTTPSTATUS_BADREQUEST;
			}
			
			break;
		case "PUT" :
			if ($elementID != null) 
			{
				$_SESSION ["artistsList"]["$elementID"]=array($decBody["name"], $decBody["surname"]);
				$respondeCode = HTTPSTATUS_CREATED;
			}
			
			else {
				$respondeCode=HTTPSTATUS_BADREQUEST;
			}
			break;
		case "DELETE" :
	if ($elementID != null) 
			{
				UNSET($_SESSION ["artistsList"]["$elementID"]);
				$respondeCode = HTTPSTATUS_OK;
			}
			
			else {
				$respondeCode=HTTPSTATUS_BADREQUEST;
			}
			break;
	}
	
	// return response to client
	
	if ($responseBody != null)
		$app->response->write ( json_encode ( $responseBody ) ); // this is the body of the response
			                                                         
	// we need to write also the response codes to send back to the client
	$app->response->status ( $respondeCode );
} )->via ( "GET", "POST", "PUT", "DELETE" );

$app->run ();
?>