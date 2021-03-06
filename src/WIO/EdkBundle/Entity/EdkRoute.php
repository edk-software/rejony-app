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
namespace WIO\EdkBundle\Entity;

use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\LabelColor;
use Cantiga\CoreBundle\Entity\Message;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Capabilities\EditableEntityInterface;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\InsertableEntityInterface;
use Cantiga\Metamodel\Capabilities\RemovableEntityInterface;
use Cantiga\Metamodel\DataMappers;
use Cantiga\Metamodel\Exception\DiskAssetException;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\FileRepositoryInterface;
use Cantiga\Metamodel\TimeFormatterInterface;
use Doctrine\DBAL\Connection;
use LogicException;
use PDO;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use WIO\EdkBundle\EdkTables;
use WIO\EdkBundle\Model\Coordinates;
use WIO\EdkBundle\Model\IndexedCoordinates;

/**
 * Represents a single route of the Extreme Way of the Cross.
 */
class EdkRoute implements IdentifiableInterface, InsertableEntityInterface, EditableEntityInterface, RemovableEntityInterface
{
	const TYPE_FULL = 0;
	const TYPE_INSPIRED = 1;
	const TYPE_UNDEFINED = 2;

    const STATUS_NONE = 0;
    const STATUS_NEW = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REVOKED = 3;

    const ICO_MAP = 'fa-map';
    const ICO_DESCRIPTION = 'fa-file-pdf-o';
    const ICO_GPS = 'fa-map-marker';

	private $id;
	private $area;
	private $name;
	private $routePatron;
	private $routeColor;
	private $routeAuthor;
	private $routeType = self::TYPE_UNDEFINED;
	private $routeFrom;
	private $routeFromDetails;
	private $routeTo;
	private $routeToDetails;
	private $routeCourse;
	private $routeLength = 0;
	private $routeAscent = 0;
	private $routeObstacles;
	private $createdAt;
	private $createdBy;
	private $updatedAt;
	private $updatedBy;
	private $approved;
	private $approvedAt;
	private $approvedBy;
	private $descriptionFile;
    private $descriptionCreatedAt;
    private $descriptionCreatedBy;
    private $descriptionUpdatedAt;
    private $descriptionUpdatedBy;
    private $descriptionStatus;
    private $descriptionApprovedAt;
    private $descriptionApprovedBy;
	private $mapFile;
    private $mapCreatedAt;
    private $mapCreatedBy;
    private $mapUpdatedAt;
    private $mapUpdatedBy;
    private $mapStatus;
    private $mapApprovedAt;
    private $mapApprovedBy;
	private $gpsTrackFile;
	private $gpsCreatedAt;
	private $gpsCreatedBy;
	private $gpsUpdatedAt;
	private $gpsUpdatedBy;
	private $gpsStatus;
	private $gpsApprovedAt;
	private $gpsApprovedBy;
	/** @var UploadedFile */
	private $descriptionFileUpload;
	/** @var UploadedFile */
	private $mapFileUpload;
	/** @var UploadedFile */
	private $gpsTrackFileUpload;
	/** @var array|null */
	private $verificationStatus;
	/** @var array|null */
	private $elevationCharacteristic;
	/** @var Coordinates[] */
	private $pathCoordinates = [];
	/** @var IndexedCoordinates[] */
	private $stations = [];
	/** @var Coordinates|null */
	private $pathStart;
	/** @var Coordinates|null */
	private $pathEnd;
	/** @var Coordinates|null */
	private $pathAvg;
	private $publicAccessSlug;
	private $commentNum;
	private $importedFrom;

	/**
	 * Additional notes related to the route.
	 * @var array
	 */
	private $notes = [];
	
	private $postedMessage = null;
	
	public static function fetchByRoot(Connection $conn, $id, HierarchicalInterface $root)
	{
		if ($root instanceof Area) {
			$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::ROUTE_TBL.'` WHERE `areaId` = :areaId AND `id` = :id', [':id' => $id, ':areaId' => $root->getId()]);
		} elseif ($root instanceof Group) {
			$data = $conn->fetchAssoc('SELECT r.* '
				. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = r.`areaId` '
				. 'WHERE a.`groupId` = :groupId AND r.`id` = :id', [':id' => $id, ':groupId' => $root->getId()]);
		} elseif ($root instanceof Project) {
			$data = $conn->fetchAssoc('SELECT r.* '
				. 'FROM `'.EdkTables::ROUTE_TBL.'` r '
				. 'INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`id` = r.`areaId` '
				. 'WHERE a.`projectId` = :projectId AND r.`id` = :id', [':id' => $id, ':projectId' => $root->getId()]);
		}
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		if ($root instanceof Area) {
			$item->area = $root;
		} else {
			$item->area = Area::fetchByPlace($conn, $data['areaId'], $root);
		}

		self::bindUsers($item, $conn);

		$notes = $conn->fetchAll('SELECT * FROM `'.EdkTables::ROUTE_NOTE_TBL.'` WHERE `routeId` = :routeId', [':routeId' => $item->getId()]);
		foreach ($notes as $note) {
			$item->notes[$note['noteType']] = $note['content'];
		}
		
		return $item;
	}
	
	public static function fetchApproved(Connection $conn, $id)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::ROUTE_TBL.'` WHERE `id` = :id AND `approved` = 1', [':id' => $id]);
		if (false === $data) {
			return false;
		}
		$item = self::fromArray($data);
		$item->area = Area::fetchActive($conn, $data['areaId']);
		return $item;
	}
	
	public static function fetchBySlug(Connection $conn, $slug)
	{
		$data = $conn->fetchAssoc('SELECT * FROM `'.EdkTables::ROUTE_TBL.'` WHERE `publicAccessSlug` = :slug', [':slug' => $slug]);
		if (false === $data) {
			return false;
		}
		return self::fromArray($data);
	}
	
	public static function fromArray($array, $prefix = '')
	{
		$item = new EdkRoute;
		DataMappers::fromArray($item, $array, $prefix);
		if (isset($array['pathStartLat']) && isset($array['pathStartLng'])) {
			$item->setPathStart(new Coordinates((float) $array['pathStartLat'], (float) $array['pathStartLng']));
		}
		if (isset($array['pathEndLat']) && isset($array['pathEndLng'])) {
			$item->setPathEnd(new Coordinates((float) $array['pathEndLat'], (float) $array['pathEndLng']));
		}
		return $item;
	}

	public static function getMapMark(TranslatorInterface $translator, $status)
    {
        return [
            'ico' => self::ICO_MAP,
            'textColor' => self::getColorByState($status),
            'tooltip' => $translator->trans(self::getTooltip($status), [], 'edk')
        ];
    }

    public static function getDescriptionMark(TranslatorInterface $translator, $status)
    {
        return [
            'ico' => self::ICO_DESCRIPTION,
            'textColor' => self::getColorByState($status),
            'tooltip' => $translator->trans(self::getTooltip($status), [], 'edk')
        ];
    }

    public static function getGpsMark(TranslatorInterface $translator, $status)
    {
        return [
            'ico' => self::ICO_GPS,
            'textColor' => self::getColorByState($status),
            'tooltip' => $translator->trans(self::getTooltip($status), [], 'edk')
        ];
    }

    public static function getColorByState($status)
    {
        switch ($status) {
            case self::STATUS_NONE:
                return LabelColor::STATUS_NONE;
            case self::STATUS_NEW:
                return LabelColor::STATUS_NEW;
            case self::STATUS_APPROVED:
                return LabelColor::STATUS_APPROVED;
            case self::STATUS_REVOKED:
                return LabelColor::STATUS_REVOKED;
        }
    }

    public static function getTooltip($status)
    {
        switch ($status) {
            case self::STATUS_NEW:
                return 'New';
            case self::STATUS_NONE:
                return 'Blank';
            case self::STATUS_APPROVED:
                return 'Approved';
            case self::STATUS_REVOKED:
                return 'Declined';
        }
    }

	public static function getRelationships()
	{
		return ['area'];
	}
	
	public static function loadValidatorMetadata(ClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('name', new NotBlank());
		$metadata->addPropertyConstraint('name', new Length(array('min' => 2, 'max' => 50)));
        $metadata->addPropertyConstraint('routePatron', new Length(array('min' => 2, 'max' => 50)));
        $metadata->addPropertyConstraint('routeColor', new Length(array('min' => 2, 'max' => 50)));
        $metadata->addPropertyConstraint('routeAuthor', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('routeFrom', new NotBlank());
		$metadata->addPropertyConstraint('routeFrom', new Length(array('min' => 2, 'max' => 50)));
        $metadata->addPropertyConstraint('routeFromDetails', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('routeTo', new NotBlank());
		$metadata->addPropertyConstraint('routeTo', new Length(array('min' => 2, 'max' => 50)));
        $metadata->addPropertyConstraint('routeToDetails', new Length(array('min' => 2, 'max' => 50)));
		$metadata->addPropertyConstraint('routeCourse', new NotBlank());
		$metadata->addPropertyConstraint('routeCourse', new Length(array('min' => 2, 'max' => 500)));
		$metadata->addPropertyConstraint('routeObstacles', new Length(array('min' => 0, 'max' => 100)));
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getArea()
	{
		return $this->area;
	}

	public function getName()
	{
		return $this->name;
	}
	
	public function getRouteType()
	{
		return $this->routeType;
	}

	public function getRouteColor()
	{
		return $this->routeColor;
	}

	public function getRoutePatron()
	{
		return $this->routePatron;
	}

	public function getRouteAuthor()
	{
		return $this->routeAuthor;
	}

	public function getRouteFrom()
	{
		return $this->routeFrom;
	}

	public function getRouteTo()
	{
		return $this->routeTo;
	}

    public function getRouteFromDetails()
    {
        return $this->routeFromDetails;
    }

    public function getRouteToDetails()
    {
        return $this->routeToDetails;
    }

	public function getRouteCourse()
	{
		return $this->routeCourse;
	}

	public function getRouteLength()
	{
		return $this->routeLength;
	}

	public function getRouteAscent()
	{
		return $this->routeAscent;
	}

	public function getRouteObstacles()
	{
		return $this->routeObstacles;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function getUpdatedAt()
	{
		return $this->updatedAt;
	}

    public function getApprovedAt()
    {
        return $this->approvedAt;
    }

	public function getApproved()
	{
		return $this->approved;
	}

	public function getDescriptionFile()
	{
		return $this->descriptionFile;
	}

	public function getMapFile()
	{
		return $this->mapFile;
	}

	public function getGpsTrackFile()
	{
		return $this->gpsTrackFile;
	}

	public function getDescriptionFileUpload()
	{
		return $this->descriptionFileUpload;
	}

	public function getMapFileUpload()
	{
		return $this->mapFileUpload;
	}

	public function getGpsTrackFileUpload()
	{
		return $this->gpsTrackFileUpload;
	}

	/** @return array|null */
	public function getVerificationStatus()
	{
		return $this->verificationStatus;
	}

	/** @return array|null */
	public function getElevationCharacteristic()
	{
		return $this->elevationCharacteristic;
	}

	/** @return Coordinates[] */
	public function getPathCoordinates()
	{
		return $this->pathCoordinates;
	}

	/** @return IndexedCoordinates[] */
	public function getStations()
	{
		return $this->stations;
	}

	/** @return Coordinates|null */
	public function getPathStart()
	{
		return $this->pathStart;
	}

	/** @return Coordinates|null */
	public function getPathEnd()
	{
		return $this->pathEnd;
	}

	/** @return Coordinates|null */
	public function getPathAvg()
	{
		return $this->pathAvg;
	}

	public function getPublicAccessSlug()
	{
		return $this->publicAccessSlug;
	}
	
	public function getCommentNum()
	{
		return $this->commentNum;
	}

	public function getGpsStatus()
	{
		return $this->gpsStatus;
	}

	public function isGpsApproved()
	{
		return (int) $this->gpsStatus === self::STATUS_APPROVED;
	}

	public function isGpsRevoked()
	{
		return (int) $this->gpsStatus === self::STATUS_REVOKED;
	}

    public function getGpsCreatedAt()
    {
        return $this->gpsCreatedAt;
    }

    public function getGpsCreatedBy()
    {
        return $this->gpsCreatedBy;
    }

    public function getGpsUpdatedAt()
    {
        return $this->gpsUpdatedAt;
    }

    public function getGpsUpdatedBy()
    {
        return $this->gpsUpdatedBy;
    }

	public function getGpsApprovedAt()
	{
		return $this->gpsApprovedAt;
	}

	public function getGpsApprovedBy()
	{
		return $this->gpsApprovedBy;
	}

    public function getMapStatus()
    {
        return $this->mapStatus;
    }

	public function isMapApproved()
	{
		return (int) $this->mapStatus === self::STATUS_APPROVED;
	}

	public function isMapRevoked()
	{
		return (int) $this->mapStatus === self::STATUS_REVOKED;
	}

    public function getMapCreatedAt()
    {
        return $this->mapCreatedAt;
    }

    public function getMapCreatedBy()
    {
        return $this->mapCreatedBy;
    }

    public function getMapUpdatedAt()
    {
        return $this->mapUpdatedAt;
    }

    public function getMapUpdatedBy()
    {
        return $this->mapUpdatedBy;
    }

	public function getMapApprovedAt()
	{
		return $this->mapApprovedAt;
	}

	public function getMapApprovedBy()
	{
		return $this->mapApprovedBy;
	}

    public function getDescriptionStatus()
    {
        return $this->descriptionStatus;
    }

	public function isDescriptionApproved()
	{
		return (int) $this->descriptionStatus === self::STATUS_APPROVED;
	}

	public function isDescriptionRevoked()
	{
		return (int) $this->descriptionStatus === self::STATUS_REVOKED;
	}

    public function getDescriptionCreatedAt()
    {
        return $this->descriptionCreatedAt;
    }

    public function getDescriptionCreatedBy()
    {
        return $this->descriptionCreatedBy;
    }

    public function getDescriptionUpdatedAt()
    {
        return $this->descriptionUpdatedAt;
    }

    public function getDescriptionUpdatedBy()
    {
        return $this->descriptionUpdatedBy;
    }

	public function getDescriptionApprovedAt()
	{
		return $this->descriptionApprovedAt;
	}

	public function getDescriptionApprovedBy()
	{
		return $this->descriptionApprovedBy;
	}

    public function setId($id)
	{
		DataMappers::noOverwritingId($this->id);
		$this->id = $id;
		return $this;
	}

	public function setArea(Area $area)
	{
		$this->area = $area;
		return $this;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}
	
	public function setRoutePatron($value)
	{
		$this->routePatron = $value;
		return $this;
	}

    public function setRouteColor($value)
    {
        $this->routeColor = $value;
        return $this;
    }

    public function setRouteType($value)
    {
        $this->routeType = $value;
        return $this;
    }

    public function setRouteAuthor($value)
    {
        $this->routeAuthor = $value;
        return $this;
    }

	public function setRouteFrom($routeFrom)
	{
		$this->routeFrom = $routeFrom;
		return $this;
	}

	public function setRouteTo($routeTo)
	{
		$this->routeTo = $routeTo;
		return $this;
	}

    public function setRouteFromDetails($value)
    {
        $this->routeFromDetails = $value;
        return $this;
    }

    public function setRouteToDetails($value)
    {
        $this->routeToDetails = $value;
        return $this;
    }

	public function setRouteCourse($routeCourse)
	{
		$this->routeCourse = $routeCourse;
		return $this;
	}

	public function setRouteLength($routeLength)
	{
		$this->routeLength = $routeLength;
		return $this;
	}

	public function setRouteAscent($routeAscent)
	{
		$this->routeAscent = $routeAscent;
		return $this;
	}

	public function setRouteObstacles($routeObstacles)
	{
		if (empty($routeObstacles)) {
			$this->routeObstacles = null;
		} else {
			$this->routeObstacles = $routeObstacles;
		}
		return $this;
	}

	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;
		return $this;
	}

	public function setUpdatedAt($updatedAt)
	{
		$this->updatedAt = $updatedAt;
		return $this;
	}

    public function setApprovedAt($approvedAt)
    {
        $this->approvedAt = $approvedAt;
        return $this;
    }

	public function setApproved($approved)
	{
		$this->approved = $approved;
		return $this;
	}

	public function setDescriptionFile($descriptionFile)
	{
		$this->descriptionFile = $descriptionFile;
		return $this;
	}

	public function setMapFile($mapFile)
	{
		$this->mapFile = $mapFile;
		return $this;
	}

	public function setGpsTrackFile($gpsTrackFile)
	{
		$this->gpsTrackFile = $gpsTrackFile;
		return $this;
	}

	public function setDescriptionFileUpload(UploadedFile $descriptionFileUpload)
	{
		$this->descriptionFileUpload = $descriptionFileUpload;
		return $this;
	}

	public function setMapFileUpload(UploadedFile $mapFileUpload)
	{
		$this->mapFileUpload = $mapFileUpload;
		return $this;
	}

	public function setGpsTrackFileUpload(UploadedFile $gpsTrackFileUpload)
	{
		$this->gpsTrackFileUpload = $gpsTrackFileUpload;
		return $this;
	}

	public function setVerificationStatus($verificationStatus)
	{
		$this->verificationStatus = is_string($verificationStatus) ? json_decode($verificationStatus) :
			$verificationStatus;
		return $this;
	}

	public function setElevationCharacteristic($elevationCharacteristic)
	{
		$this->elevationCharacteristic = is_string($elevationCharacteristic) ? json_decode($elevationCharacteristic) :
			$elevationCharacteristic;
		return $this;
	}

	public function setPathCoordinates($pathCoordinates)
	{
		if (is_string($pathCoordinates)) {
			$pathCoordinates = json_decode($pathCoordinates, true);
		}
		$this->pathCoordinates = is_array($pathCoordinates) ? array_map(function ($data) {
			return $data instanceof Coordinates ? $data : Coordinates::createFromDb($data);
		}, $pathCoordinates) : [];
		return $this;
	}

	public function setStations($stations)
	{
		if (is_string($stations)) {
			$stations = json_decode($stations, true);
		}
		$this->stations = is_array($stations) ? array_map(function ($data) {
			return $data instanceof IndexedCoordinates ? $data : IndexedCoordinates::createFromDb($data);
		}, $stations) : [];
		return $this;
	}

	public function setPathStart($pathStart)
	{
		$this->pathStart = $pathStart;
		if (isset($this->pathStart) && isset($this->pathEnd)) {
			$this->pathAvg = Coordinates::createAvg($this->pathStart, $this->pathEnd);
		}
		return $this;
	}

	public function setPathEnd($pathEnd)
	{
		$this->pathEnd = $pathEnd;
		if (isset($this->pathStart) && isset($this->pathEnd)) {
			$this->pathAvg = Coordinates::createAvg($this->pathStart, $this->pathEnd);
		}
		return $this;
	}

	public function setPublicAccessSlug($publicAccessSlug)
	{
		$this->publicAccessSlug = $publicAccessSlug;
		return $this;
	}
	
	public function setCommentNum($commentNum)
	{
		DataMappers::noOverwritingField($this->commentNum);
		$this->commentNum = $commentNum;
	}

    public function setDescriptionStatus($descriptionStatus)
    {
        $this->descriptionStatus = $descriptionStatus;
        return $this;
    }

    public function setDescriptionCreatedAt($descriptionCreatedAt)
    {
        $this->descriptionCreatedAt = $descriptionCreatedAt;
        return $this;
    }

    public function setDescriptionCreatedBy($descriptionCreatedBy)
    {
        $this->descriptionCreatedBy = $descriptionCreatedBy;
        return $this;
    }

    public function setDescriptionUpdatedAt($descriptionUpdatedAt)
    {
        $this->descriptionUpdatedAt = $descriptionUpdatedAt;
        return $this;
    }

    public function setDescriptionUpdatedBy($descriptionUpdatedBy)
    {
        $this->descriptionUpdatedBy = $descriptionUpdatedBy;
        return $this;
    }

	public function setDescriptionApprovedAt($descriptionApprovedAt)
	{
		$this->descriptionApprovedAt = $descriptionApprovedAt;
		return $this;
	}

	public function setDescriptionApprovedBy($descriptionApprovedBy)
	{
		$this->descriptionApprovedBy = $descriptionApprovedBy;
		return $this;
	}

    public function setGpsStatus($gpsStatus)
    {
        $this->gpsStatus = $gpsStatus;
        return $this;
    }

    public function setGpsCreatedAt($gpsCreatedAt)
    {
        $this->gpsCreatedAt = $gpsCreatedAt;
        return $this;
    }

    public function setGpsCreatedBy($gpsCreatedBy)
    {
        $this->gpsCreatedBy = $gpsCreatedBy;
        return $this;
    }

    public function setGpsUpdatedAt($gpsUpdatedAt)
    {
        $this->gpsUpdatedAt = $gpsUpdatedAt;
        return $this;
    }

    public function setGpsUpdatedBy($gpsUpdatedBy)
    {
        $this->gpsUpdatedBy = $gpsUpdatedBy;
        return $this;
    }

	public function setGpsApprovedAt($gpsApprovedAt)
	{
		$this->gpsApprovedAt = $gpsApprovedAt;
		return $this;
	}

	public function setGpsApprovedBy($gpsApprovedBy)
	{
		$this->gpsApprovedBy = $gpsApprovedBy;
		return $this;
	}

    public function setMapStatus($mapStatus)
    {
        $this->mapStatus = $mapStatus;
        return $this;
    }

    public function setMapCreatedAt($mapCreatedAt)
    {
        $this->mapCreatedAt = $mapCreatedAt;
        return $this;
    }

    public function setMapCreatedBy($mapCreatedBy)
    {
        $this->mapCreatedBy = $mapCreatedBy;
        return $this;
    }

    public function setMapUpdatedAt($mapUpdatedAt)
    {
        $this->mapUpdatedAt = $mapUpdatedAt;
        return $this;
    }

    public function setMapUpdatedBy($mapUpdatedBy)
    {
        $this->mapUpdatedBy = $mapUpdatedBy;
        return $this;
    }

	public function setMapApprovedAt($mapApprovedAt)
	{
		$this->mapApprovedAt = $mapApprovedAt;
		return $this;
	}

	public function setMapApprovedBy($mapApprovedBy)
	{
		$this->mapApprovedBy = $mapApprovedBy;
		return $this;
	}

	function getImportedFrom()
	{
		return $this->importedFrom;
	}

	function setImportedFrom($importedFrom)
	{
		$this->importedFrom = $importedFrom;
	}
		
	public function getEditableNote($type)
	{
		if (!isset($this->notes[$type])) {
			return '';
		}
		return $this->notes[$type];
	}
	
	public function post(Message $message)
	{
		$this->postedMessage = $message;
	}
	
	public function getFullEditableNote(TranslatorInterface $translator, $type)
	{
		foreach (self::getNoteTypes() as $id => $name) {
			if ($id == $type) {
				$content = $this->getEditableNote($id);
				return ['id' => $id, 'name' => $translator->trans($name, [], 'edk'), 'content' => $content, 'editable' => $content];
			}
		}
		return ['id' => 0, 'name' => '', 'content' => ''];
	}
	
	public function getFullNoteInformation(TranslatorInterface $translator)
	{
		$result = [];
		foreach (self::getNoteTypes() as $id => $name) {
			$content = $this->getEditableNote($id);
			$result[] = ['id' => $id, 'name' => $translator->trans($name, [], 'edk'), 'content' => $content, 'editable' => $content];
		}
		return $result;
	}
	
	public function isDescriptionFilePublished()
	{
		return !empty($this->descriptionFile);
	}
	
	public function isMapFilePublished()
	{
		return !empty($this->mapFile);
	}
	
	/**
	 * @param Connection $conn Database connection
	 * @param int $type Note type (numbers from 1 to 4)
	 * @param string $content New content
	 * @throws ModelException
	 */
	public function saveEditableNote(Connection $conn, $type, $content)
	{
		if ($type < 1 || $type > 4) {
			throw new ModelException('Invalid note type.');
		}

		if (empty($content)) {
			$html = '';
			$conn->delete(EdkTables::ROUTE_NOTE_TBL, array('routeId' => $this->getId(), 'noteType' => $type));
			unset($this->notes[$type]);
		} else {
			$stmt = $conn->prepare('INSERT INTO `' . EdkTables::ROUTE_NOTE_TBL . '` (`routeId`, `noteType`, `content`, `lastUpdatedAt`) VALUES(:routeId, :noteType, :content, :lastUpdatedAt)'
				. ' ON DUPLICATE KEY UPDATE `content` = VALUES(`content`), `lastUpdatedAt` = VALUES(`lastUpdatedAt`)');
			$stmt->bindValue(':routeId', $this->getId());
			$stmt->bindValue(':noteType', $type);
			$stmt->bindValue(':content', $content);
			$stmt->bindValue(':lastUpdatedAt', time());
			$stmt->execute();
			$this->notes[$type] = $content;
		}
	}

	public function downloadDescription(FileRepositoryInterface $repository, Response $response)
	{
		if(null === $this->descriptionFile) {
			throw new ItemNotFoundException('There is no file with the route description available.', $this->id);
		}
		$repository->downloadFile($this->descriptionFile, 'edk-desc-route-' . $this->id . '.pdf', 'application/pdf', $response);
	}

	public function downloadMap(FileRepositoryInterface $repository, Response $response)
	{
		if(null === $this->mapFile) {
			throw new ItemNotFoundException('There is no file with the route map available.', $this->id);
		}
		
		if(strpos($this->mapFile, '.jpg') !== false) {
			$repository->downloadFile($this->mapFile, 'edk-map-route-' . $this->id . '.jpg', 'image/jpeg', $response);
		} else {
			$repository->downloadFile($this->mapFile, 'edk-map-route-' . $this->id . '.pdf', 'application/pdf', $response);
		}
	}

	public function downloadGpsTrack(FileRepositoryInterface $repository, Response $response)
	{
		$repository->downloadFile($this->gpsTrackFile, 'edk-gps-route-' . $this->id . '.kml', 'application/vnd.google-earth.kml+xml', $response);
	}

	public function getGpsTrackContent(FileRepositoryInterface $repository)
	{
		return $repository->getFileContent($this->gpsTrackFile);
	}

	private function insertDescription(FileRepositoryInterface $fileRepository)
    {
        $this->descriptionStatus = self::STATUS_NEW;
        $this->descriptionCreatedAt = time();
        $this->descriptionUpdatedAt = time();
        $this->descriptionFile = $fileRepository->storeFile($this->getDescriptionFileUpload());
    }

    private function insertMap(FileRepositoryInterface $fileRepository)
    {
        $this->mapStatus = self::STATUS_NEW;
        $this->mapCreatedAt = time();
        $this->mapUpdatedAt = time();
        $this->mapFile = $fileRepository->storeFile($this->getMapFileUpload());
    }

    private function insertGps(FileRepositoryInterface $fileRepository)
    {
        $this->gpsStatus = self::STATUS_NEW;
        $this->gpsCreatedAt = time();
        $this->gpsUpdatedAt = time();
        $this->gpsTrackFile = $fileRepository->storeFile($this->getGpsTrackFileUpload());
    }

	public function storeFiles(FileRepositoryInterface $fileRepository)
	{
		if (!$this->getGpsTrackFileUpload() instanceof UploadedFile) {
			throw new ModelException('Files not uploaded!');
		}
		if($this->getDescriptionFileUpload() instanceof UploadedFile) {
			$this->verifyMIMEOfDescription();
		}
		if($this->getMapFileUpload() instanceof UploadedFile) {
			$this->verifyMIMEOfMap();
		}
		$this->verifyMIMEOfKML();

		if($this->getDescriptionFileUpload() instanceof UploadedFile) {
		    $this->insertDescription($fileRepository);
		}
		if($this->getMapFileUpload() instanceof UploadedFile) {
			$this->insertMap($fileRepository);
		}
		$this->insertGps($fileRepository);
	}

	public function updateFiles(FileRepositoryInterface $fileRepository)
	{
		if($this->getDescriptionFileUpload() instanceof UploadedFile) {
			$this->verifyMIMEOfDescription();
		}
		if($this->getMapFileUpload() instanceof UploadedFile) {
			$this->verifyMIMEOfMap();
		}
		$this->verifyMIMEOfKML();

		if (null !== $this->getDescriptionFileUpload()) {
			if(null === $this->descriptionFile) {
			    $this->insertDescription($fileRepository);
			} else {
                $this->descriptionStatus = self::STATUS_NEW;
                $this->descriptionUpdatedAt = time();
                $this->descriptionFile = $fileRepository->replaceFile($this->descriptionFile, $this->getDescriptionFileUpload());
			}
		}
		if (null !== $this->getMapFileUpload()) {
			if(null === $this->mapFile) {
                $this->insertMap($fileRepository);
			} else {
                $this->mapStatus = self::STATUS_NEW;
                $this->mapUpdatedAt = time();
				$this->mapFile = $fileRepository->replaceFile($this->mapFile, $this->getMapFileUpload());
			}
		}
		if (null !== $this->getGpsTrackFileUpload()) {
            $this->gpsStatus = self::STATUS_NEW;
            $this->approved = 0;
            $this->gpsUpdatedAt = time();
			$this->gpsTrackFile = $fileRepository->replaceFile($this->gpsTrackFile, $this->getGpsTrackFileUpload());
		}
	}
	
	public function insert(Connection $conn)
	{
		if (null === $this->area) {
			throw new LogicException('EdkRoute requires ::area to be specified.');
		}

		$this->approved = 0;
		
		$this->publicAccessSlug = sha1(time() . $this->routeAscent . $this->routeFrom . rand(-20000, 20000));
		$this->createdAt = time();
		$this->updatedAt = time();
		$conn->insert(
			EdkTables::ROUTE_TBL,
			DataMappers::pick($this,
                [
                    'name',
                    'area',
                    'routePatron',
                    'routeColor',
                    'routeAuthor',
                    'routeType',
                    'routeFrom',
                    'routeFromDetails',
                    'routeTo',
                    'routeToDetails',
                    'routeCourse',
                    'routeLength',
                    'routeAscent',
                    'routeObstacles',
                    'createdAt',
                    'updatedAt',
                    'approved',
                    'descriptionFile',
                    'mapFile',
                    'gpsTrackFile',
                    'publicAccessSlug',
                    'importedFrom',
                    'gpsStatus',
                    'gpsCreatedAt',
                    'gpsUpdatedAt',
                    'mapStatus',
                    'mapCreatedAt',
                    'mapUpdatedAt',
                    'descriptionStatus',
                    'descriptionCreatedAt',
                    'descriptionUpdatedAt'
                ])
		);
		$this->id = $conn->lastInsertId();
		return $this->id;
	}
	
	public function update(Connection $conn)
	{
		//$this->publicAccessSlug = sha1(time() . $this->routeAscent . $this->routeFrom . rand(-20000, 20000));
		$this->updatedAt = time();
		
		if (null !== $this->postedMessage) {
			$this->commentNum = $conn->fetchColumn('SELECT `commentNum` FROM `'.EdkTables::ROUTE_TBL.'` WHERE `id` = :id', [':id' => $this->id]);
			$conn->insert(EdkTables::ROUTE_COMMENT_TBL, [
				'routeId' => $this->id,
				'userId' => $this->postedMessage->getUser()->getId(),
				'createdAt' => $this->postedMessage->getCreatedAt(),
				'message' => $this->postedMessage->getMessage()
			]);
			$this->commentNum++;
		}
		
		$conn->update(
			EdkTables::ROUTE_TBL,
			DataMappers::pick($this, [
			    'name',
                'routePatron',
                'routeColor',
                'routeAuthor',
                'routeType',
                'routeFrom',
                'routeFromDetails',
                'routeToDetails',
                'routeTo',
                'routeCourse',
                'routeLength',
                'routeAscent',
                'routeObstacles',
                'updatedAt',
                'approved',
                'descriptionFile',
                'mapFile',
                'gpsTrackFile',
                'publicAccessSlug',
                'commentNum',
                'gpsStatus',
                'gpsCreatedAt',
                'gpsUpdatedAt',
                'mapStatus',
                'mapCreatedAt',
                'mapUpdatedAt',
                'descriptionStatus',
                'descriptionCreatedAt',
                'descriptionUpdatedAt'
            ]),
			DataMappers::pick($this, ['id'])
		);
	}
	
	public function canRemove()
	{
		return !$this->approved;
	}
	
	public function cleanupFiles(FileRepositoryInterface $fileRepository)
	{
		if (null !== $this->descriptionFile && !empty($this->descriptionFile)) {
			$fileRepository->removeFile($this->descriptionFile);
		}
		if (null !== $this->mapFile && !empty($this->descriptionFile)) {
			$fileRepository->removeFile($this->mapFile);
		}
		if (null !== $this->gpsTrackFile && !empty($this->descriptionFile)) {
			$fileRepository->removeFile($this->gpsTrackFile);
		}
	}

	public function remove(Connection $conn)
	{
		$conn->delete(EdkTables::ROUTE_TBL, DataMappers::pick($this, ['id']));
	}

	public function approve(Connection $conn, User $user, $force = false): bool
	{
		if ($force || !$this->isApproved($conn)) {
			$approvedAt = time();
			$this->approved = 1;
			$this->approvedAt = $approvedAt;
			$this->approvedBy = $user->getId();
			$this->gpsStatus = self::STATUS_APPROVED;
			$this->gpsApprovedAt = $approvedAt;
			$this->gpsApprovedBy = $user->getId();

			$pathAvg = isset($this->pathStart) && isset($this->pathEnd) ?
				Coordinates::createAvg($this->pathStart, $this->pathEnd) : null;
			$conn->update(EdkTables::ROUTE_TBL,
				[
					'approved' => $this->approved,
					'approvedAt' => $this->approvedAt,
					'approvedBy' => $this->approvedBy,
					'elevationCharacteristic' => isset($this->elevationCharacteristic) ?
						json_encode($this->elevationCharacteristic) : null,
					'pathCoordinates' => isset($this->pathCoordinates) ? json_encode($this->pathCoordinates) : null,
					'stations' => isset($this->stations) ? json_encode($this->stations) : null,
					'pathStartLat' => isset($this->pathStart) ? $this->pathStart->getLatitude() : null,
					'pathStartLng' => isset($this->pathStart) ? $this->pathStart->getLongitude() : null,
					'pathEndLat' => isset($this->pathEnd) ? $this->pathEnd->getLatitude() : null,
					'pathEndLng' => isset($this->pathEnd) ? $this->pathEnd->getLongitude() : null,
					'pathAvgLat' => isset($pathAvg) ? $pathAvg->getLatitude() : null,
					'pathAvgLng' => isset($pathAvg) ? $pathAvg->getLongitude() : null,
					'gpsStatus' => $this->gpsStatus,
					'gpsApprovedAt' => $this->gpsApprovedAt,
					'gpsApprovedBy' => $this->gpsApprovedBy,
                    'routeAscent' => $this->routeAscent,
                    'routeLength' => $this->routeLength,
                    'routeType' => $this->routeType,
					'verificationStatus' => isset($this->verificationStatus) ? json_encode($this->verificationStatus) :
                        null,
				],
				[
					'id' => $this->getId(),
				]
			);
			return true;
		}
		return false;
	}

	public function revoke(Connection $conn, User $user, $force = false): bool
	{
		if ($force || $this->isApproved($conn)) {
			$this->approved = 0;
			$this->approvedAt = time();
			$this->approvedBy = $user->getId();

			$pathAvg = isset($this->pathStart) && isset($this->pathEnd) ?
				Coordinates::createAvg($this->pathStart, $this->pathEnd) : null;
			$conn->update(EdkTables::ROUTE_TBL,
				[
					'approved' => $this->approved,
					'approvedAt' => $this->approvedAt,
					'approvedBy' => $this->approvedBy,
					'elevationCharacteristic' => isset($this->elevationCharacteristic) ?
						json_encode($this->elevationCharacteristic) : null,
					'pathCoordinates' => isset($this->pathCoordinates) ? json_encode($this->pathCoordinates) : null,
					'stations' => isset($this->stations) ? json_encode($this->stations) : null,
					'pathStartLat' => isset($this->pathStart) ? $this->pathStart->getLatitude() : null,
					'pathStartLng' => isset($this->pathStart) ? $this->pathStart->getLongitude() : null,
					'pathEndLat' => isset($this->pathEnd) ? $this->pathEnd->getLatitude() : null,
					'pathEndLng' => isset($this->pathEnd) ? $this->pathEnd->getLongitude() : null,
					'pathAvgLat' => isset($pathAvg) ? $pathAvg->getLatitude() : null,
					'pathAvgLng' => isset($pathAvg) ? $pathAvg->getLongitude() : null,
					'routeAscent' => $this->routeAscent,
					'routeLength' => $this->routeLength,
					'routeType' => $this->routeType,
					'verificationStatus' => isset($this->verificationStatus) ? json_encode($this->verificationStatus) :
						null,
				],
				[
					'id' => $this->getId(),
				]
			);
			return true;
		}
		return false;
	}

	public function approveGps(Connection $conn, User $user): bool
	{
		if (!$this->isGpsApproved()) {
            $approvedAt = time();
			$this->gpsStatus = self::STATUS_APPROVED;
			$this->gpsApprovedAt = $approvedAt;
			$this->gpsApprovedBy = $user->getId();
            $this->approved = 1;
            $this->approvedAt = $approvedAt;
            $this->approvedBy = $user->getId();

			$conn->update(EdkTables::ROUTE_TBL, [
				'gpsStatus' => $this->gpsStatus,
				'gpsApprovedAt' => $this->gpsApprovedAt,
				'gpsApprovedBy' => $this->gpsApprovedBy,
                'approved' => $this->approved,
                'approvedAt' => $this->approvedAt,
                'approvedBy' => $this->approvedBy,
			], [
				'id' => $this->getId(),
			]);
			return true;
		}
		return false;
	}

	public function revokeGps(Connection $conn, User $user): bool
	{
		if ($this->isGpsApproved()) {
            $approvedAt = time();
			$this->gpsStatus = self::STATUS_REVOKED;
			$this->gpsApprovedAt = $approvedAt;
			$this->gpsApprovedBy = $user->getId();
            $this->approved = 0;
            $this->approvedAt = $approvedAt;
            $this->approvedBy = $user->getId();

			$conn->update(EdkTables::ROUTE_TBL, [
				'gpsStatus' => $this->gpsStatus,
				'gpsApprovedAt' => $this->gpsApprovedAt,
				'gpsApprovedBy' => $this->gpsApprovedBy,
                'approved' => $this->approved,
                'approvedAt' => $this->approvedAt,
                'approvedBy' => $this->approvedBy,
			], [
				'id' => $this->getId(),
			]);
			$this->revoke($conn, $user);
			return true;
		}
		return false;
	}

	public function approveDescription(Connection $conn, User $user): bool
	{
		if (!$this->isDescriptionApproved()) {
			$this->descriptionStatus = self::STATUS_APPROVED;
			$this->descriptionApprovedAt = time();
			$this->descriptionApprovedBy = $user->getId();

			$conn->update(EdkTables::ROUTE_TBL, [
				'descriptionStatus' => $this->descriptionStatus,
				'descriptionApprovedAt' => $this->descriptionApprovedAt,
				'descriptionApprovedBy' => $this->descriptionApprovedBy,
			], [
				'id' => $this->getId(),
			]);
			return true;
		}
		return false;
	}

	public function revokeDescription(Connection $conn, User $user): bool
	{
		if ($this->isDescriptionApproved()) {
			$this->descriptionStatus = self::STATUS_REVOKED;
			$this->descriptionApprovedAt = time();
			$this->descriptionApprovedBy = $user->getId();

			$conn->update(EdkTables::ROUTE_TBL, [
				'descriptionStatus' => $this->descriptionStatus,
				'descriptionApprovedAt' => $this->descriptionApprovedAt,
				'descriptionApprovedBy' => $this->descriptionApprovedBy,
			], [
				'id' => $this->getId(),
			]);
			return true;
		}
		return false;
	}

	public function approveMap(Connection $conn, User $user): bool
	{
		if (!$this->isMapApproved()) {
			$this->mapStatus = self::STATUS_APPROVED;
			$this->mapApprovedAt = time();
			$this->mapApprovedBy = $user->getId();

			$conn->update(EdkTables::ROUTE_TBL, [
				'mapStatus' => $this->mapStatus,
				'mapApprovedAt' => $this->mapApprovedAt,
				'mapApprovedBy' => $this->mapApprovedBy,
			], [
				'id' => $this->getId(),
			]);
			return true;
		}
		return false;
	}

	public function revokeMap(Connection $conn, User $user): bool
	{
		if ($this->isMapApproved()) {
			$this->mapStatus = self::STATUS_REVOKED;
			$this->mapApprovedAt = time();
			$this->mapApprovedBy = $user->getId();

			$conn->update(EdkTables::ROUTE_TBL, [
				'mapStatus' => $this->mapStatus,
				'mapApprovedAt' => $this->mapApprovedAt,
				'mapApprovedBy' => $this->mapApprovedBy,
			], [
				'id' => $this->getId(),
			]);
			return true;
		}
		return false;
	}

	private function isApproved(Connection $conn): bool
	{
		return (bool) $conn->fetchColumn('
			SELECT `approved`
			FROM `' . EdkTables::ROUTE_TBL . '`
			WHERE `id` = :id
		', [
			':id' => $this->id,
		]);
	}

	public function getComments(Connection $conn, TimeFormatterInterface $timeFormatter)
	{
		$stmt = $conn->prepare('SELECT m.`createdAt`, m.`message`, u.`id` AS `user_id`, u.`name`, u.`avatar` FROM `'.EdkTables::ROUTE_COMMENT_TBL.'` m '
			. 'INNER JOIN `'.CoreTables::USER_TBL.'` u ON u.`id` = m.`userId` '
			. 'WHERE m.`routeId` = :id ORDER BY m.`id`');
		$stmt->bindValue(':id', $this->id);
		$stmt->execute();
		$result = [];
		$direction = 1;
		$previousUser = null;
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($previousUser != $row['user_id']) {
				$direction = ($direction == 1 ? 0 : 1);
			}
			$result[] = [
				'message' => $row['message'],
				'time' => $timeFormatter->ago($row['createdAt']),
				'author' => $row['name'],
				'avatar' => $row['avatar'],
				'dir' => $direction
			];
			$previousUser = $row['user_id'];
		}
		$stmt->closeCursor();
		return $result;
	}
	
	public static function getNoteTypes()
	{
		return [
			1 => 'RouteDescriptionNote',
			2 => 'RouteRecommendationNote',
			3 => 'RouteArrivalNote',
			4 => 'RouteSecurityNote',
		];
	}

	private function verifyMIMEOfDescription()
	{
		if (null !== $this->getDescriptionFileUpload()) {
			if ($this->getDescriptionFileUpload()->getMimeType() != 'application/pdf') {
				throw new DiskAssetException('The MIME type of the uploaded description is invalid.');
			}
			if ($this->getDescriptionFileUpload()->getClientOriginalExtension() != 'pdf') {
				throw new DiskAssetException('The extension of the uploaded description is invalid.');
			}
		}
	}

	private function verifyMIMEOfMap()
	{
		if (null !== $this->getMapFileUpload()) {
			if ($this->getMapFileUpload()->getMimeType() != 'application/pdf' && $this->getMapFileUpload()->getMimeType() != 'image/jpeg') {
				throw new DiskAssetException('The MIME type of the uploaded map is invalid.');
			}
			$ext = $this->getMapFileUpload()->getClientOriginalExtension();
			if ($ext != 'jpg' && $ext != 'pdf') {
				throw new DiskAssetException('The extension of the uploaded map is invalid.');
			}
		}
	}

	private function verifyMIMEOfKML()
	{
		if (null !== $this->getGpsTrackFileUpload()) {
			if ($this->getGpsTrackFileUpload()->getMimeType() != 'application/vnd.google-earth.kml+xml' && $this->getGpsTrackFileUpload()->getMimeType() != 'application/xml') {
				throw new DiskAssetException('The MIME type of the uploaded GPS track is invalid.');
			}
			if ($this->getGpsTrackFileUpload()->getClientOriginalExtension() != 'kml') {
				throw new DiskAssetException('The extension of the uploaded GPS track is invalid.');
			}
		}
	}

	private static function bindUsers(self $route, Connection $conn)
	{
		$userIds = [
			$route->getGpsCreatedBy(),
			$route->getGpsUpdatedBy(),
			$route->getGpsApprovedBy(),
			$route->getDescriptionCreatedBy(),
			$route->getDescriptionUpdatedBy(),
			$route->getDescriptionApprovedBy(),
			$route->getMapCreatedBy(),
			$route->getMapUpdatedBy(),
			$route->getMapApprovedBy(),
		];
		$uniqueIds = [];
		foreach ($userIds as $id) {
			if (is_numeric($id) && !in_array((int) $id, $uniqueIds)) {
				$uniqueIds[] = (int) $id;
			}
		}
		/** @var User[] $users */
		$users = User::fetchByIds($conn, $uniqueIds);
		$usersByIds = [];
		foreach ($users as $user) {
			$usersByIds[$user->getId()] = $user;
		}
		$route
			->setGpsCreatedBy(self::getUser($usersByIds, $route->getGpsCreatedBy()))
			->setGpsUpdatedBy(self::getUser($usersByIds, $route->getGpsUpdatedBy()))
			->setGpsApprovedBy(self::getUser($usersByIds, $route->getGpsApprovedBy()))
			->setDescriptionCreatedBy(self::getUser($usersByIds, $route->getDescriptionCreatedBy()))
			->setDescriptionUpdatedBy(self::getUser($usersByIds, $route->getDescriptionUpdatedBy()))
			->setDescriptionApprovedBy(self::getUser($usersByIds, $route->getDescriptionApprovedBy()))
			->setMapCreatedBy(self::getUser($usersByIds, $route->getMapCreatedBy()))
			->setMapUpdatedBy(self::getUser($usersByIds, $route->getMapUpdatedBy()))
			->setMapApprovedBy(self::getUser($usersByIds, $route->getMapApprovedBy()))
		;
	}

	private static function getUser(array $users, $id)
	{
		if (!is_numeric($id) || !array_key_exists($id, $users)) {
			return null;
		}
		return $users[$id];
	}
}
