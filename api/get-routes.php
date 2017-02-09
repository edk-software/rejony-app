<?php
require('./inc.php');
header('Content-type: application/json;charset=utf-8');
try {
	$area = takeParamInt('area');
	$territory = takeParamInt('territory');
	$route = takeParamInt('route');
	$details = takeParamStr('details');
	
	$result = array();
	
	if (!empty($area)) {
		$stmt = $pdo->prepare('SELECT `id`, `name`, `areaId`, `territoryId`, `currentYear` FROM `'.AgentTables::EDK_ROUTES.'` WHERE `areaId` = :areaId ORDER BY `name`');
		$stmt->bindValue(':areaId', $area);
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['currentYear'] = $row['currentYear'] || false;
			$result[] = $row;
		}
		$stmt->closeCursor();
	} elseif(!empty($territory)) {
		$stmt = $pdo->prepare('SELECT `id`, `name`, `areaId`, `territoryId`, `currentYear` FROM `'.AgentTables::EDK_ROUTES.'` WHERE `territoryId` = :territoryId ORDER BY `name`');
		$stmt->bindValue(':territoryId', $territory);
		$stmt->execute();
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['currentYear'] = $row['currentYear'] || false;
			$result[] = $row;
		}
		$stmt->closeCursor();
	} elseif(!empty($route) && empty($details)) {
		$stmt = $pdo->prepare('SELECT `id`, `name`, `areaId`, `territoryId`, `currentYear`, `publicAccessSlug` AS `kmlData` FROM `'.AgentTables::EDK_ROUTES.'` WHERE `id` = :id');
		$stmt->bindValue(':id', $route);
		$stmt->execute();
		
		$random = rand(0, 100);
		
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $fullUrl = 'http://m2.edk.org.pl/mirror/'.$row['kmlData'].'/edk-gps-route-'.$row['id'].'.kml';
			
			$result = $row;
            $result['currentYear'] = $row['currentYear'] || false;
			$result['kmlDataPath'] = $fullUrl;
			$result['stations'] = array();
		}
		$stmt->closeCursor();
	} elseif(!empty($route) && $details == 'full') {
		$stmt = $pdo->prepare('SELECT r.`id`, r.`name`, r.`areaId`, r.`territoryId`, r.`currentYear`, r.`publicAccessSlug` AS `kmlData`, n.content AS `content` '
			. 'FROM `'.AgentTables::EDK_ROUTES.'` r '
			. 'LEFT JOIN `'.AgentTables::EDK_ROUTE_NOTES.'` n ON (n.routeId = r.id AND n.noteType = 1) WHERE r.`id` = :id');
		$stmt->bindValue(':id', $route);
		$stmt->execute();
		
		$random = rand(0, 100);
		
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$fullUrl = 'http://m2.edk.org.pl/mirror/'.$row['kmlData'].'/edk-gps-route-'.$row['id'].'.kml';
			$result = array(
				'routeId' => $row['id'],
				'description' => $row['content'],
				'routeData' => array(
					'id' => $row['id'],
					'areaId' => $row['areaId'],
					'name' => $row['name'],
					'kmlDataPath' => $fullUrl,
                    'currentYear' => $row['currentYear'] || false,
				),
				'stationDescs' => array(),
			);
		}
		
		$stmt->closeCursor();
	}
	echo json_encode($result);
} catch (Exception $ex) {
	echo json_encode(array('success' => 0, 'message' => $ex->getMessage()));
}