<?php
if (!file_exists('./config.php')) {
	die('config.php file is required!');
}
require_once('./config.php');

$pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
$pdo->query('SET NAMES `utf8`');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

class AgentTables {
	const EDK_TERRITORIES = 'edk_territories';
	const EDK_AREAS = 'edk_areas';
	const EDK_ROUTES = 'edk_routes';
	const EDK_AREA_NOTES = 'edk_area_notes';
	const EDK_ROUTE_NOTES = 'edk_route_notes';
}

function takeParamInt($field)
{
	if (!isset($_GET[$field])) {
		return null;
	}
	return (int) $_GET[$field];
}

function takeParamStr($field)
{
	if (!isset($_GET[$field])) {
		return null;
	}
	return (string) $_GET[$field];
}

function array2date($array)
{
	if (is_object($array)) {
		return $array->year.'-'.$array->month.'-'.$array->day;
	}
	return null;
}

function insertOrUpdate(PDO $pdo, $table, array $fields)
{
	$seq1 = '';
	$seq2 = '';
	$seq3 = '';
	$bindings = array();
	$first = true;
	foreach ($fields as $name => $value) {
		$bindings[':'.$name.'1'] = $value;
		$bindings[':'.$name.'2'] = $value;
		if ($first) {
			$seq1 .= $name;
			$seq2 .= ':'.$name.'1';
			$seq3 .= $name.' = :'.$name.'2';
			$first = false;
		} else {
			$seq1 .= ', '.$name;
			$seq2 .= ', :'.$name.'1';
			$seq3 .= ', '.$name.' = :'.$name.'2';
		}
	}
	$stmt = $pdo->prepare('INSERT INTO '.$table.' ('.$seq1.') VALUES('.$seq2.') ON DUPLICATE KEY UPDATE '.$seq3);
	foreach ($bindings as $name => $value) {
		$stmt->bindValue($name, $value);
	}
	$stmt->execute();
}
