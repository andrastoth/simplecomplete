<?php
require_once('mysql_db.php');
$db = Database::getInstance();
$db->connect();
$order = $_REQUEST['order'];
$param = $_REQUEST['param'];
if($order == 'getkey'){
	$sql = "SELECT country_name AS elem FROM counter WHERE country_name <> '' AND LOWER(country_name) like '%$param%' group by country_name;";
	$query = $db->query($sql); 
	$json = $db->jsonEncode($query);
	echo $json;
}else if($order == 'getdata'){
	$sql = "SELECT * FROM counter WHERE LOWER(country_name) = '$param' LIMIT 10";
	$table = $db-> getHTMLtable($sql, 'data');
	echo $table;
}else if($order == 'getcity'){
	$sql = "SELECT city AS elem FROM counter WHERE city <> '' AND LOWER(city) like '%$param%' group by city;";
	$query = $db->query($sql); 
	$json = $db->jsonEncode($query);
	echo $json;
}else if($order == 'getcordinates'){
	$sql = "SELECT latitude, longitude FROM counter WHERE city <> '' AND LOWER(city) = '$param' group by latitude, longitude;";
	$query = $db->query($sql); 
	$json = $db->jsonEncode($query);
	echo $json;
}
$db->disconnect();
?>
