<?php
require('./inc.php');
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	header('HTTP/1.1 501 Not Implemented');
	header('Content-Type: text/html; charset=UTF-8');
	die('');
}
header('HTTP/1.1 200 OK');
header('Content-Type: text/html; charset=UTF-8');

$ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
$decrypted = base64_decode(file_get_contents('php://input'));
if (false === $decrypted) {
	die('');
}

if (strlen($decrypted) <= $ivSize) {
	die('');
}
$ivDec = substr($decrypted, 0, $ivSize);
$message = substr($decrypted, $ivSize);

$decrypted = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, base64_decode(ENC_KEY), $message, MCRYPT_MODE_CBC, $ivDec));
if (false === $decrypted) {
	die('');
}
$data = json_decode($decrypted);
if (null === $data) {
	die('');
}

// Import territories
if (sizeof($data->territory->ids) > 0) {
	$pdo->query('DELETE FROM `edk_territories` WHERE `id` NOT IN('.implode(',', $data->territory->ids).')');
	foreach ($data->territory->update as $item) {
		insertOrUpdate($pdo, 'edk_territories', array(
			'id' => $item->id,
			'name' => $item->name,
		));
	}
} else {
	$pdo->query('DELETE FROM `edk_territories`');
}

// Import areas
if (sizeof($data->area->ids) > 0) {
	$pdo->query('DELETE FROM `edk_areas` WHERE `id` NOT IN('.implode(',', $data->area->ids).')');
	foreach ($data->area->update as $area) {
		if (empty($area->customData->positionLat) || empty($area->customData->positionLng)) {
			continue;
		}
		
		insertOrUpdate($pdo, 'edk_areas', array(
			'id' => $area->id,
			'name' => $area->name,
			'territoryId' => $area->territoryId,
			'lastUpdatedAt' => $area->lastUpdatedAt,
			'positionLat' => $area->customData->positionLat,
			'positionLng' => $area->customData->positionLng,
			'edkDate' => array2date($area->customData->ewcDate),
			'parishName' => $area->customData->parishName,
			'parishAddress' => $area->customData->parishAddress,
			'parishPostal' => $area->customData->parishPostal,
			'parishCity' => $area->customData->parishCity,
			'parishWebsite' => $area->customData->parishWebsite,
			'responsiblePriest' => $area->customData->responsiblePriest,
			'responsibleCoordinator' => $area->customData->responsibleCoordinator,
			'contactPhone' => $area->customData->contactPhone,
			'areaWebsite' => $area->customData->areaWebsite,
			'facebookProfile' => $area->customData->facebookProfile,
		));
	}
} else {
	$pdo->query('DELETE FROM `edk_areas`');
}

// Import routes
if (sizeof($data->route->ids) > 0) {
	$pdo->query('DELETE FROM `edk_routes` WHERE `id` NOT IN('.implode(',', $data->route->ids).')');
	foreach ($data->route->update as $item) {
		insertOrUpdate($pdo, 'edk_routes', array(
			'id' => $item->id,
			'name' => $item->name,
			'areaId' => $item->areaId,
			'territoryId' => $item->territoryId,
			'routeType' => $item->routeType,
			'routeFrom' => $item->routeFrom,
			'routeTo' => $item->routeTo,
			'routeCourse' => $item->routeCourse,
			'routeLength' => $item->routeLength,
			'routeAscent' => $item->routeAscent,
			'routeObstacles' => $item->routeObstacles,
			'updatedAt' => $item->updatedAt,
			'publicAccessSlug' => $item->publicAccessSlug,
			'hasAllFiles' => (!empty($item->descriptionFile) && !empty($item->mapFile)),
			'hasMapFile' => (!empty($item->mapFile)),
			'hasGuideFile' => (!empty($item->descriptionFile)),
			'registrationType' => (empty($item->registrationType) ? 0 : $item->registrationType),
			'registrationStartTime' => $item->startTime,
			'registrationEndTime' => $item->endTime,
			'externalRegistrationUrl' => $item->externalRegistrationUrl,
			'currentYear' => $item->currentYear ? 1 : 0,
		));
	}
} else {
	$pdo->query('DELETE FROM `edk_routes`');
}

if (sizeof($data->participants->update) > 0) {
	$stmt = $pdo->prepare('UPDATE `edk_routes` SET `participantNum` = :participantNum WHERE `id` = :id');
	foreach ($data->participants->update as $item) {
		$stmt->bindValue(':participantNum', $item->n);
		$stmt->bindValue(':id', $item->id);
		$stmt->execute();
	}
}

// Import area descriptions
if (sizeof($data->areaDesc->ids) > 0) {
	foreach ($data->areaDesc->update as $item) {
		insertOrUpdate($pdo, 'edk_area_notes', array(
			'areaId' => $item->areaId,
			'noteType' => $item->noteType,
			'content' => $item->content,
		));
	}
}

// Import route descriptions
if (sizeof($data->routeDesc->ids) > 0) {
	foreach ($data->routeDesc->update as $item) {
		insertOrUpdate($pdo, 'edk_route_notes', array(
			'routeId' => $item->routeId,
			'noteType' => $item->noteType,
			'content' => $item->content,
		));
	}
}