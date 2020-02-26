<?php

namespace WIO\EdkBundle\Model;

use Cantiga\CoreBundle\Validator\Constraints as LocalAssert;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;
use WIO\EdkBundle\Entity\EdkRoute;
use WIO\EdkBundle\Exception\RouteVerifierResultException;

class RouteVerifierResult
{
    /** @var array */
    private $verificationStatus;

    /** @var string[] */
    private $verificationLogs;

    /** @var array */
    private $elevationCharacteristic;

    /** @var Coordinates[] */
    private $pathCoordinates;

    /** @var IndexedCoordinates[] */
    private $stations;

    /** @var Coordinates */
    private $pathStart;

    /** @var Coordinates */
    private $pathEnd;

    public function __construct(array $body)
    {
        $statusItemsByValues = [
            'elevationGain' => $this->getNumberValidator(),
            'elevationLoss' => $this->getNumberValidator(),
            'elevationTotalChange' => $this->getNumberValidator(),
            'numberOfStations' => null,
            'pathLength' => $this->getNumberValidator(),
            'routeType' => $this->getRouteTypeValidator(),
            'singlePath' => null,
            'stationsOnPath' => null,
            'stationsOrder' => null,
        ];
        $constraint = new Assert\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'routeCharacteristics' => new Assert\Collection([
                    'allowExtraFields' => true,
                    'fields' => [
                        'elevationCharacteristics' => [
                            new Assert\Type('array'),
                            new Assert\All([
                                new Assert\Collection([
                                    'distance' => new LocalAssert\Type('number'),
                                    'elevation' => new LocalAssert\Type('number'),
                                ]),
                            ]),
                        ],
                        'pathCoordinates' => [
                            new Assert\Type('array'),
                            new Assert\All([
                                new Assert\Collection([
                                    'latitude' => new LocalAssert\Type('number'),
                                    'longitude' => new LocalAssert\Type('number'),
                                ]),
                            ]),
                        ],
                        'pathEnd' => new Assert\Collection([
                            'latitude' => new LocalAssert\Type('number'),
                            'longitude' => new LocalAssert\Type('number'),
                        ]),
                        'pathStart' => new Assert\Collection([
                            'latitude' => new LocalAssert\Type('number'),
                            'longitude' => new LocalAssert\Type('number'),
                        ]),
                        'stations' => [
                            new Assert\Type('array'),
                            new Assert\All([
                                new Assert\Collection([
                                    'index' => new LocalAssert\Type('number'),
                                    'latitude' => new LocalAssert\Type('number'),
                                    'longitude' => new LocalAssert\Type('number'),
                                ]),
                            ]),
                        ],
                    ],
                ]),
                'verificationStatus' => new Assert\Collection([
                    'allowExtraFields' => true,
                    'fields' => array_merge(array_combine(
                        array_keys($statusItemsByValues),
                        array_map(function ($valueValidator) {
                            $params = [
                                'valid' => new Assert\Type('bool'),
                            ];
                            if (isset($valueValidator)) {
                                $params['value'] = $valueValidator;
                            }
                            return new Assert\Collection([
                                'allowExtraFields' => true,
                                'fields' => $params,
                            ]);
                        }, $statusItemsByValues)
                    ), [
                        'logs' => [
                            new Assert\Type('array'),
                            new Assert\All([
                                new Assert\Type('string'),
                            ]),
                        ],
                    ]),
                ]),
            ],
        ]);

        $validator = Validation::createValidator();
        $violations = $validator->validate($body, $constraint);
        if ($violations->count() > 0) {
            throw new RouteVerifierResultException($violations);
        }

        $statusKeys = array_keys($statusItemsByValues);
        $this->verificationStatus = array_filter($body['verificationStatus'], function ($key) use ($statusKeys) {
            return in_array($key, $statusKeys);
        }, ARRAY_FILTER_USE_KEY);
        $this->verificationLogs = $body['verificationStatus']['logs'];
        $this->elevationCharacteristic = $body['routeCharacteristics']['elevationCharacteristics'];
        $this->pathCoordinates = array_map(function ($data) {
            return new Coordinates($data['latitude'], $data['longitude']);
        }, $body['routeCharacteristics']['pathCoordinates']);
        $this->stations = array_map(function ($data) {
            return new IndexedCoordinates($data['index'], $data['latitude'], $data['longitude']);
        }, $body['routeCharacteristics']['stations']);
        $pathStart = $body['routeCharacteristics']['pathStart'];
        $this->pathStart = new Coordinates($pathStart['latitude'], $pathStart['longitude']);
        $pathEnd = $body['routeCharacteristics']['pathEnd'];
        $this->pathEnd = new Coordinates($pathEnd['latitude'], $pathEnd['longitude']);
    }

    public function isValid(): bool
    {
        foreach ($this->verificationStatus as $itemStatus) {
            if ($itemStatus['valid'] !== true) {
                return false;
            }
        }

        return true;
    }

    public function getVerificationStatus(): array
    {
        return $this->verificationStatus;
    }

    public function getVerificationLogs(): array
    {
        return $this->verificationLogs;
    }

    public function getElevationCharacteristic(): array
    {
        return $this->elevationCharacteristic;
    }

    /** @return Coordinates[] */
    public function getPathCoordinates(): array
    {
        return $this->pathCoordinates;
    }

    /** @return IndexedCoordinates[] */
    public function getStations(): array
    {
        return $this->stations;
    }

    public function getPathStart(): Coordinates
    {
        return $this->pathStart;
    }

    public function getPathEnd(): Coordinates
    {
        return $this->pathEnd;
    }

    public function getRouteAscent(): float
    {
        return (float) $this->verificationStatus['elevationGain']['value'];
    }

    public function getRouteLength(): float
    {
        return (float) $this->verificationStatus['pathLength']['value'];
    }

    public function getRouteType(): int
    {
        return $this->verificationStatus['routeType']['value'];
    }

    private function getNumberValidator(): Constraint
    {
        return new LocalAssert\Type('number');
    }

    private function getRouteTypeValidator(): array
    {
        return [
            new Assert\Type('int'),
            new Assert\Choice([EdkRoute::TYPE_FULL, EdkRoute::TYPE_INSPIRED, EdkRoute::TYPE_UNDEFINED]),
        ];
    }
}
