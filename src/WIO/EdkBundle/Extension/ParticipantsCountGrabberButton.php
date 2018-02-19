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
use WIO\EdkBundle\EdkTables;

class ParticipantsCountGrabberButton implements MagicButtonExtension
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
		$stmt = $this->conn->prepare('SELECT a.name, s.datePoint, s.participantNum FROM `'
            .EdkTables::STAT_AREA_PARTICIPANT_TIME_TBL.'` s '
            . 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = s.`areaId` '
            . 'WHERE s.`projectId` = :projectId '
            . 'ORDER BY a.name, s.datePoint');
		$stmt->bindValue(':projectId', $project->getId());
		$stmt->execute();
		
		$response = new StreamedResponse(function() use ($stmt) {
			$out = fopen('php://output', 'w');
            fputcsv($out, array('AreaName', 'Date', 'ParticipantNum'),chr(9));
			$i = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				fputcsv($out, $row, chr(9));
				if(($i++) % 5 == 0) {
					fflush($out);
				}
			}
			fclose($out);
			$stmt->closeCursor();
		});
		$response->headers->set('Content-Type', 'text/csv');
		$response->headers->set('Cache-Control', '');
		$response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s'));
		
		$currentDate = date('YmdHis');
		
		$contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'participants-'.$currentDate.'.csv');
		$response->headers->set('Content-Disposition', $contentDisposition);
		return $response;
	}
}
