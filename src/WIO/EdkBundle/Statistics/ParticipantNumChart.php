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

namespace WIO\EdkBundle\Statistics;

use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Repository\CoreStatisticsRepository;
use Cantiga\Metamodel\Capabilities\IdentifiableInterface;
use Cantiga\Metamodel\Capabilities\StatsInterface;
use Cantiga\Metamodel\Statistics\ChartJSDateDatasetRenderer;
use Cantiga\Metamodel\Statistics\StatDateDataset;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Translation\TranslatorInterface;
use WIO\EdkBundle\Repository\EdkParticipantRepository;
use WIO\EdkBundle\Repository\EdkRegistrationSettingsRepository;

/**
 * Displays the chart, how many participants have registered over time.
 *
 * @author Tomasz Jędrzejewski
 */
class ParticipantNumChart implements StatsInterface
{
    /**
     * @var Connection
     */
    private $conn;
    /**
     * @var CoreStatisticsRepository
     */
    private $repo;
    /**
     * @var EdkRegistrationSettingsRepository
     */
    private $registrationSettingsRepository;
    /**
     * @var StatDateDataset
     */
    private $data;

    private $allParticipants;
    private $registerParticipants;
    private $externalParticipants;

    public function __construct(
        Connection $conn,
        EdkParticipantRepository $repository,
        EdkRegistrationSettingsRepository $registrationSettingsRepository,
        TranslatorInterface $translator
    ) {
        $this->conn = $conn;
        $this->repo = $repository;
        $this->registrationSettingsRepository = $registrationSettingsRepository;
        $this->translator = $translator;
    }

    public function collectData(IdentifiableInterface $root)
    {
        if ($root instanceof Project) {
            $this->data = $this->repo->fetchParticipantsOverTime($root);
        } elseif ($root instanceof Area) {
            $this->data = $this->repo->fetchAreaParticipantsOverTime($root);
        }
        $this->registrationSettingsRepository->setRootEntity($root);
        $this->allParticipants = $this->registrationSettingsRepository->countParticipants();
        $this->registerParticipants = $this->registrationSettingsRepository->countParticipantsRegister();
        $this->externalParticipants = $this->registrationSettingsRepository->countParticipantsExternal();

        return true;
    }

    public function getTitle()
    {
        return $this->translator->trans('Number of participants', [], 'edk');
    }

    public function renderPlaceholder(TwigEngine $tpl)
    {
        return $tpl->render(
            'WioEdkBundle:Stats:participant-num-chart.html.twig',
            array(
                'allParticipants' => $this->allParticipants,
                'registerParticipants' => $this->registerParticipants,
                'externalParticipants' => $this->externalParticipants,
            )
        );
    }

    public function renderStatistics(TwigEngine $tpl)
    {
        $renderer = new ChartJSDateDatasetRenderer();
        $renderer->data($this->translator->trans('Participants', [], 'edk'), '60,141,188');

        return $tpl->render(
            'WioEdkBundle:Stats:participant-num-chart.js.twig',
            array(
                'data' => $renderer->generateData($this->data),
            )
        );
    }

    public function getCssBoxClass()
    {
        return 'col-xs-12';
    }
}
