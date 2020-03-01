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

namespace WIO\EdkBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\AreaStatus;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\UserBundle\UserTables;
use Doctrine\DBAL\Connection;
use PDO;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Entity\AreaRoutesStatus;
use WIO\EdkBundle\Entity\AggrementsStatus;

class EdkValidationRepository
{
    /**
     * @var Connection
     */
    private $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function getAgreementsStatus($projectId)
    {
        $stmt = $this->conn->prepare('SELECT a.`id` as areaId, a.`name` as areaName, a.`contract` as contract, COUNT(s.`createdBy`) as assignAgreementsCount, COUNT(s.`updatedBy`) as signedAgreementsCount'
            . ' FROM `' . CoreTables::AREA_TBL . '` a'
            . ' JOIN `' . CoreTables::PLACE_TBL . '` pl'
            . ' ON pl.`id` = a.`placeId`'
            . ' JOIN `' . UserTables::PLACE_MEMBERS_TBL . '` m'
            . ' ON m.`placeId` = pl.`id`'
            . ' LEFT JOIN `' . UserTables::AGREEMENTS_SIGNATURES_TBL . '` s'
            . ' ON s.`signerId`=m.`userId` AND s.`projectId`= :projectId'
            . ' WHERE a.`projectId`= :projectId'
            . ' GROUP BY a.`id`, a.`name`, a.`contract`');
        $stmt->bindValue(':projectId', $projectId);
        $stmt->execute();
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = AggrementsStatus::fromArray($row);
        }
        $stmt->closeCursor();
        return $result;
    }

    public function addUsersAgreement(Project $project, int $agreementId, int $createdBy)
    {
        $this->conn->executeQuery('INSERT INTO `' . UserTables::AGREEMENTS_SIGNATURES_TBL . '` (`agreementId`, `signerId`, `projectId`, `createdAt`, `createdBy`)'
            . 'select DISTINCT :agreementId, u.userId, :projectId, :createdAt, :createdBy '
            . 'FROM `' . UserTables::PLACE_MEMBERS_TBL . '` u '
            . 'JOIN `' . CoreTables::PLACE_TBL . '` p '
            . 'ON p.id = u.placeId '
            . 'WHERE p.rootPlaceId = :projectPlaceId AND p.type = "Area" '
            . 'AND u.userId NOT IN '
            . '(SELECT signerId FROM `' . UserTables::AGREEMENTS_SIGNATURES_TBL . '` WHERE projectId=:projectId)', [
            ':projectId' => 12,
            ':agreementId' => 2,
            ':createdAt' => 1582497363,
            ':createdBy' => 2,
            ':projectPlaceId' => 1607
        ]);
    }

    public function getAreasRouteStatus($projectId)
    {
        $stmt = $this->conn->prepare('SELECT a.`id` as areaId, a.`name` as areaName, a.`percentCompleteness` as profilePercent, s.`name` as statusName, s.`id` as statusId, SUM(r.`routeType`) as activeRoutesTypes, COUNT(r.`id`) as activeRoutesCount'
            . ' FROM `' . CoreTables::AREA_TBL . '` a'
            . ' JOIN `' . CoreTables::AREA_STATUS_TBL . '` s'
            . ' ON s.`id` = a.`statusId`'
            . ' LEFT JOIN (SELECT `id`, `routeType`, `areaId` FROM `' . EdkTables::ROUTE_TBL . '` WHERE `approved` = 1) r'
            . ' ON r.`areaId` = a.`id`'
            . ' WHERE a.`projectId`= :projectId'
            . ' GROUP BY a.`id`, a.`name`, a.`percentCompleteness`, s.`name`, s.`id`');
        $stmt->bindValue(':projectId', $projectId);
        $stmt->execute();
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = AreaRoutesStatus::fromArray($row);
        }
        $stmt->closeCursor();
        return $result;
    }

    public function updateAreaStatus($areaId, AreaStatus $newStatus)
    {
        $item = Area::fetchActive($this->conn, $areaId);
        $item->setStatus($newStatus);
        $item->update($this->conn);
    }

    public function getAreaStatus($project, $id)
    {
        return AreaStatus::fetchByProject($this->conn, $id, $project);
    }

    public function getProject($projectId)
    {
        return Project::fetch($this->conn, $projectId);
    }

    public function updateContract($areaId, $isSigned)
    {
        $this->conn->update(CoreTables::AREA_TBL, ['contract' => $isSigned], ['id' => $areaId]);
    }
}
