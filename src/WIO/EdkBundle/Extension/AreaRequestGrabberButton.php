<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace WIO\EdkBundle\Extension;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Extension\MagicButtonExtension;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WIO\EdkBundle\CustomForm\AreaRequestModel2018;

class AreaRequestGrabberButton implements MagicButtonExtension
{
	/**
	 * @var Connection
	 */
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function execute(Project $project)
	{	
		$stmt = $this->conn->prepare('SELECT r.`name`, r.`customData`, u.`name` AS `username`, c.`email`, c.`telephone` '
			. 'FROM `'.CoreTables::AREA_REQUEST_TBL.'` r '
            . 'LEFT JOIN `'.CoreTables::CONTACT_TBL.'` c ON c.`userId` = r.`requestorId` '
            . 'LEFT JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = r.`requestorId` '
			. 'WHERE r.`projectId` = :projectId '
			. 'AND c.`placeId` = :placeId '
			. 'ORDER BY r.`name`, u.`name`');
		$stmt->bindValue(':projectId', $project->getId());
		$stmt->bindValue(':placeId', $project->getPlace()->getId());
		$stmt->execute();
		
		$response = new StreamedResponse(function() use ($stmt) {
			$out = fopen('php://output', 'w');
            fputcsv($out, array('AreaName', 'Username', 'Email', 'Phone', 'IsParticipant', 'IsAreaCreated', 'StationaryCourse', 'StationaryCoursePerson', 'StationaryCourseDiet', 'StationaryCourseDetails'),chr(9));
			$i = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			    $customExport = AreaRequestModel2018::GetExportArray($row['customData']);
			    $data = array($row['name'], $row['username'], $row['email'],$row['telephone']);
				fputcsv($out,array_merge($data,$customExport) , chr(9));
				if(($i++) % 5 == 0) {
					fflush($out);
				}
			}
			fclose($out);
			$stmt->closeCursor();
		});
		$response->headers->set('Content-Type', 'text/csv; charset=utf-8');
		$response->headers->set('Cache-Control', '');
		$response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s'));
		
		$currentDate = date('YmdHis');
		
		$contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'requests-'.$currentDate.'.csv');
		$response->headers->set('Content-Disposition', $contentDisposition);
		return $response;
	}
}
