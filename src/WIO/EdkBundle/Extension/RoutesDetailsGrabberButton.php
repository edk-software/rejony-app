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
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Extension\MagicButtonExtension;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use PDO;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use WIO\EdkBundle\EdkTables;

class RoutesDetailsGrabberButton implements MagicButtonExtension
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
        $stmt = $this->conn->prepare('SELECT '
            . 'p.`name` as projectName, '
            . 'a.`name` as areaName, '
            . 's.`name` as areaStatus, '
            . 't.`name` as territoryName, '
            . 'r.`name`, '
            . 'routeType, '
            . 'routePatron, '
            . 'routeColor, '
            . 'routeFrom, '
            . 'routeFromDetails, '
            . 'routeTo, '
            . 'routeToDetails, '
            . 'routeCourse, '
            . 'routeLength, '
            . 'routeAscent, '
            . 'routeObstacles, '
            . 'routeAuthor, '
            . 'FROM_UNIXTIME(r.`createdAt`) as createdAt, '
            . 'uc.`name` as createdBy, '
            . 'FROM_UNIXTIME(r.`updatedAt`) as updatedAt, '
            . 'uu.`name` as updatedBy,'
            . 'approved, '
            . 'FROM_UNIXTIME(approvedAt) as approvedAt, '
            . 'ua.`name` as approvedBy, '
            . 'descriptionStatus, '
            . 'FROM_UNIXTIME(descriptionCreatedAt) as descriptionCreatedAt, '
            . 'ucd.`name` as descriptionCreatedBy, '
            . 'FROM_UNIXTIME(descriptionUpdatedAt) as descriptionUpdatedAt, '
            . 'uud.`name` as descriptionUpdatedBy, '
            . 'FROM_UNIXTIME(descriptionApprovedAt) as descriptionApprovedAt, '
            . 'uad.`name` as descriptionApprovedBy, '
            . 'gpsStatus, '
            . 'FROM_UNIXTIME(gpsCreatedAt) as gpsCreatedAt, '
            . 'ucg.`name` as gpsCreatedBy, '
            . 'FROM_UNIXTIME(gpsUpdatedAt) as gpsUpdatedAt, '
            . 'uug.`name` as gpsUpdatedBy, '
            . 'FROM_UNIXTIME(gpsApprovedAt) gpsApprovedAt, '
            . 'uag.`name` as gpsApprovedBy, '
            . 'mapStatus, '
            . 'FROM_UNIXTIME(mapCreatedAt) as mapCreatedAt, '
            . 'ucm.`name` as mapCreatedBy, '
            . 'FROM_UNIXTIME(mapUpdatedAt) as mapUpdatedAt, '
            . 'uum.`name` as mapUpdatedBy, '
            . 'FROM_UNIXTIME(mapApprovedAt) as mapApprovedAt, '
            . 'uam.`name` as mapApprovedBy '
            . 'FROM `' . EdkTables::ROUTE_TBL . '` r '
            . 'JOIN `' . CoreTables::AREA_TBL . '` a '
            . 'ON r.`areaId` = a.`id` '
            . 'JOIN `' . CoreTables::AREA_STATUS_TBL . '` s '
            . 'ON s.`id` = a.`statusId` '
            . 'JOIN `' . CoreTables::TERRITORY_TBL . '` t '
            . 'ON t.`id` = a.`territoryId` '
            . 'JOIN `' . CoreTables::PROJECT_TBL . '` p '
            . 'ON p.`id` = a.`projectId` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` uc '
            . 'ON r.`createdBy` = uc.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` uu '
            . 'ON r.`updatedBy` = uu.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` ua '
            . 'ON r.`approvedBy` = ua.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` ucd '
            . 'ON r.`descriptionCreatedBy` = ucd.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` uud '
            . 'ON r.`descriptionUpdatedBy` = uud.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` uad '
            . 'ON r.`descriptionApprovedBy` = uad.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` ucg '
            . 'ON r.`gpsCreatedBy` = ucg.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` uug '
            . 'ON r.`gpsUpdatedBy` = uug.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` uag '
            . 'ON r.`gpsApprovedBy` = uag.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` ucm '
            . 'ON r.`mapCreatedBy` = ucm.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` uum '
            . 'ON r.`mapUpdatedBy` = uum.`id` '
            . 'LEFT JOIN `' . CoreTables::USER_TBL . '` uam '
            . ' ON r.`mapApprovedBy` = uam.`id`'
            . 'WHERE a.`projectId` = :projectId ');
        $stmt->bindValue(':projectId', $project->getId());
        $stmt->execute();

        $response = new StreamedResponse(function () use ($stmt) {
            $out = fopen('php://output', 'w');
            $i = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                fputcsv($out, $row, chr(9));
                if (($i++) % 5 == 0) {
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

        $contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'routes-' . $currentDate . '.csv');
        $response->headers->set('Content-Disposition', $contentDisposition);
        return $response;
    }
}
