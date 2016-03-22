<?php
require 'db.php';
require_once "../Slim/Slim.php";
include_once('../geoPHP/geoPHP.inc');
require_once("../geoPHP/lib/geometry/Geometry.class.php");
// require_once "/DB/DAO/UsersDAO.php";
// require_once "/DB/DBManager.php";

// $db = new DBManager();
// $uDAO = new UsersDAO($db);

// $db->openConnection();

Slim\Slim::registerAutoloader ();

$app = new \Slim\Slim (); // slim run-time object
$app->get('/data','getData');
//$app->post('/linestring(/:id)','postLinestring');

function getData()
{
	
	//$sql = "SELECT * FROM paths";
	//$body = $app->request->getBody (); // get the body of the HTTP request (from client)
	//$geom = geoPHP::load("LINESTRING($body)");
	$geom = geoPHP::load('LINESTRING(1 1,5 1,5 5,1 5,1 1)');

	$insert_string = pg_escape_bytea($geom->out('ewkb'));
	$sql = "INSERT INTO PATHS (geom) values (ST_GeomFromWKB('$insert_string'))";
	//$sql = "INSERT INTO PATHS(geom) VALUES ($geom)";

	
	
	try {
		$db = getDB();
		$stmt = pg_query($db, $sql);
		$res = pg_fetch_all($stmt);
		$db = null;
		echo '{"res": ' . json_encode($res) . '}';
	} 
	
	catch(PDOException $e) {
		//error_log($e->getMessage(), 3, '/var/tmp/phperror.log'); //Write error log
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}


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
	$sql = "SELECT route_name, route_time, visibility, ST_AsEWKT(geom) FROM routes WHERE facebook_id = '$paramValue'";

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
	$timeInt = intval($routeTime);
	
	switch ($currTime){
		
		case '00': case '01': case '02':
			$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Light Rock') 
					AND (video_time BETWEEN ($routeTime - 100) AND ($routeTime + 100))";			
			break;
			
		case '03': case '04': case '05':
			$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Light Rock') 
					AND (video_time BETWEEN ($routeTime - 100) AND ($routeTime + 100))";
			break;
			
		case '06': case '07': case '08':
			$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Light Rock') 
					AND (video_time BETWEEN ($routeTime - 100) AND ($routeTime + 100))";
			break;
			
		case '09': case '10': case '11':
			$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Light Rock') 
					AND (video_time BETWEEN ($routeTime - 100) AND ($routeTime + 100))";
			break;
			
		case '12': case '13': case '14':
			$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Light Rock') 
					AND (video_time BETWEEN ($routeTime - 100) AND ($routeTime + 100))";
			break;
			
		case '15': case '16': case '17':
			$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Light Rock') 
					AND (video_time BETWEEN ($routeTime - 100) AND ($routeTime + 100))";
			break;
			
		case '18': case '19': case '20':
			$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Light Rock') 
					AND (video_time BETWEEN ($routeTime - 100) AND ($routeTime + 100))";
			break;
			
		case '21': case '22': case '23':
			$sql = "SELECT video_key FROM video WHERE (genre = 'Jazz' OR genre = 'Light Rock') 
					AND (video_time BETWEEN ($routeTime - 100) AND ($routeTime + 100))";
			break;
			
		
	}

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