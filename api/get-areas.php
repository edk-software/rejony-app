<?php
require('./inc.php');
header('Content-type: application/json;charset=utf-8');
try {
	
	$stmt = $pdo->query('SELECT `id`, `name` AS `displayName`, `territoryId` FROM `'.AgentTables::EDK_AREAS.'` ORDER BY `name`');
	$result = array();
	while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$result[] = $row;
	}
	$stmt->closeCursor();
	echo json_encode($result);
} catch (Exception $ex) {
	echo json_encode(array('success' => 0, 'message' => $ex->getMessage()));
}