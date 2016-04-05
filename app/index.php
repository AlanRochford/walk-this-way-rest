<?php
require 'db.php';
require_once "../Slim/Slim.php";
include_once('../geoPHP/geoPHP.inc');
require_once("../geoPHP/lib/geometry/Geometry.class.php");

Slim\Slim::registerAutoloader ();

$app = new \Slim\Slim (); // slim run-time object

$app->map ( "/linestring/(:id)", function ($elementID = null) use ($app)
{
	$body = $app->request->getBody(); // get the body of the HTTP request (from client)
	$parts = explode('!',$body); //Parse linestring after '!' to create the three values 
	$pathName = $parts[0];
	$pathGeom = $parts[1];
	$routeTime = $parts[2];
	$visibility = $parts[3];
	$userID = $parts[4];
	$geom = geoPHP::load("LINESTRING('$pathGeom')");
	$insert_string = pg_escape_bytea($geom->out('ewkb'));
	$sql = "INSERT INTO routes (route_name, route_time, facebook_id, visibility, geom) values ('$pathName', $routeTime, $userID, '$visibility', ST_GeomFromWKB('$insert_string'))";
	try {
		$db = getDB();
		$stmt = pg_query($db, $sql);
		$db = null;
	} 
	
	catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
} )->via( "POST");

$app->map ( "/myroutes/", function ($elementID = null) use ($app)
{
	$paramValue = $app->request()->get('id');
	$sql = "SELECT route_name, route_time, visibility, ST_AsEWKT(geom) as geom FROM routes WHERE facebook_id = '$paramValue'";

	try {
		$db = getDB();
		$stmt = pg_query($db, $sql);
		$arrayOfResults = fetchResults ( $stmt );
		
		$db = null;
		echoRespnse(200, $arrayOfResults);

	} 
	
	catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}

} )->via( "GET");

$app->map ( "/pubroutes/", function ($elementID = null) use ($app)
{
	$paramValue = $app->request()->get('loc');
	echo $paramValue;
	$userID = $app->request()->get('userid');
	//$geometry = geoPHP::load("POINT('$paramValue')", 'wkt');
	//$get_string = pg_escape_bytea($geometry->out('ewkb'));
	
	$geom = geoPHP::load("POINT('$paramValue')");
	$insert_string = pg_escape_bytea($geom->out('ewkb'));
	$toReplace = "%20";
	$locFormat = str_replace($toReplace, " ", $paramValue);
	$sql = "SELECT route_name,route_time, visibility, ST_AsEWKT(geom) as geom,  
			ST_Distance(ST_PointN(ST_GeomFromText(ST_AsEWKT(geom)),2)," . "'POINT(" . $locFormat . ")'" .") as distance 
			FROM routes WHERE visibility = 'public' AND facebook_id != '$userID' 
			ORDER BY distance LIMIT 10";
	


	try {
		$db = getDB();
		$stmt = pg_query($db, $sql);
		$arrayOfResults = fetchResults ( $stmt );
		
		$db = null;
		echoRespnse(200, $arrayOfResults);

	} 
	
	catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}

} )->via( "GET");

$app->map ( "/video/", function ($elementID = null) use ($app)
{
	$routeTime = $app->request()->get('time');
	$currTime = $app->request()->get('currtime');
	$currDay = $app->request()->get('currday');
	$currMonth = $app->request()->get('currmonth');
	$currDate = $app->request()->get('currdate');
	$timeInt = intval($routeTime);
	
	if($currDate == 17 && $currMonth == '03') // If St. Patricks Day--Play Irish Ballads
	{
		$sql = "SELECT video_key FROM video WHERE (genre = 'Irish')";	
	}
	
	else if($currDate == 25 && $currMonth == '12')
	{
		$sql = "SELECT video_key FROM video WHERE (genre = 'Christmas')";
	}
	
	else{
			
		switch ($currTime){
			
			case '00': case '01': case '02':
				$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Soul') 
						AND (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500))
						ORDER BY RANDOM() LIMIT 1";			
				break;
				
			case '03': case '04': case '05':
				$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Dance') 
						AND (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500))
						ORDER BY RANDOM() LIMIT 1";
				break;
				
			case '06': case '07': case '08':
				$sql = "SELECT video_key FROM video WHERE (genre = 'Reggae' OR genre = 'Light Rock') 
						AND (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500))
						ORDER BY RANDOM() LIMIT 1";
				break;
				
			case '09': case '10': case '11':
				$sql = "SELECT video_key FROM video WHERE (genre = 'Classical' OR genre = 'Light Rock') 
						AND (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500)) 
						ORDER BY RANDOM() LIMIT 1";
				break;
				
			case '12': case '13': case '14':
				$sql = "SELECT video_key FROM video WHERE (genre = 'Country' OR genre = 'Classical') 
						AND (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500)) 
						ORDER BY RANDOM() LIMIT 1";
				break;
				
			case '15': case '16': case '17':
				$sql = "SELECT video_key FROM video WHERE (genre = 'Indie' OR genre = 'Dance') 
						AND (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500 ))
						ORDER BY RANDOM() LIMIT 1";
				break;
				
			case '18': case '19': case '20':
				$sql = "SELECT video_key FROM video WHERE (genre = 'Rock' OR genre = 'HipHop' OR genre ='Techno') 
						AND (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500)) 
						ORDER BY RANDOM() LIMIT 1";
				break;
				
			case '21': case '22': case '23':
				if($currDay == 'Fri')//If its Friday night play some upbeat party Music
				{
					$sql = "SELECT video_key FROM video WHERE (genre = 'Techno' OR genre = 'Rock' OR genre = 'HipHop') 
						AND (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500)) 
						ORDER BY RANDOM() LIMIT 1";
				}
				else//If any other day play music chilled music
				{
				$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Light Rock' OR genre = 'Soul') 
						AND (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500)) 
						ORDER BY RANDOM() LIMIT 1";
				}
				break;
				
			
		}
	}

	try {
		$db = getDB();
		$stmt = pg_query($db, $sql);
		$arrayOfResults = fetchResults ( $stmt );
		
		if($arrayOfResults == null)//If no videos of that genre match route time, choose from all genres
		{
			$sql1 = "SELECT video_key FROM video WHERE (video_time BETWEEN ($routeTime - 500) AND ($routeTime + 500)) 
					ORDER BY RANDOM() LIMIT 1";
			$stmt = pg_query($db, $sql1);
			$arrayOfResults = fetchResults ( $stmt );
			
		}
		
		$db = null;
		echoRespnse(200, $arrayOfResults);

	} 
	
	catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}

} )->via( "GET");

function fetchResults($resultSet) {
	$rows = array (); // will contain all the records
	while ( $row = pg_fetch_assoc($resultSet) ) {
		$rows [] = $row;
	}
	return $rows;
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);
 
    // setting response content type to json
    $app->contentType('application/json');
 
    echo json_encode($response);
}

$app->run ();
?>