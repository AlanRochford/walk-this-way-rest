<?php
function getDB() {
$dbhost="fyp-walkthiswau.cloudapp.net";
$dbuser="AlanRochford";
$dbpass="Rochie12";
$dbname="firstspatial";


$dbConnection = pg_connect("host=$dbhost dbname=$dbname user=$dbuser password=$dbpass")
or die("Can't connect to database".pg_last_error());

return $dbConnection;
}
?>