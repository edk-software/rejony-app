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

namespace WIO\EdkBundle\Command;

use Cantiga\CoreBundle\Entity\AreaStatus;
use Cantiga\CoreBundle\Mail\MailSenderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WIO\EdkBundle\EdkSettings;
use WIO\EdkBundle\EdkTexts;


class UpdateAgreementsCommand extends ContainerAwareCommand
{
    const toSigned = 'areasToBeSignedContract';
    const toAdded = 'areasToAddContract';
    const toUpdated = 'areasToUpdateContract';
    const toDowngrade = 'areasToDowngradeContract';

    const toPublication = 'areasToPublication';
    const toHide = 'areasToHide';

    const validationRepository = 'wio.edk.repo.validation';

    protected function configure()
    {
        $this
            ->setName('cantiga:edk:update-agreements')
            ->setDescription('Update Agreements')
            ->addOption('projectId', 'p', InputOption::VALUE_REQUIRED, 'Project id');
    }

    private function validateAggrements($repository, $projectId)
    {
        $result = [
            self::toSigned => [],
            self::toAdded => [],
            self::toUpdated => [],
            self::toDowngrade => [],
        ];
        $summary = $repository->getAgreementsStatus($projectId);

        foreach ($summary as $row) {
            if ($row->isToBeSigned()) {
                $result[self::toSigned][] = $row;
            }
            if ($row->isToAddAgreements()) {
                $result[self::toAdded][] = $row;
            }
            if ($row->isContractToBeUpdated()) {
                $repository->updateContract($row->getAreaId(), 1);
                $result[self::toUpdated][] = $row;
            }
            if ($row->isContractToBeDowngrade()) {
                $repository->updateContract($row->getAreaId(), 0);
                $result[self::toDowngrade][] = $row;
            }
        }
        return $result;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->getRepository();
        $projectId = (int)$input->getOption('projectId');
        $project = $repository->getProject($projectId);

        $resultAggrements = $this->validateAggrements($repository, $projectId);

        $repository->addUsersAgreement($project, 2, 2);

        $emails = $this->getMails($projectId);
        $mailer = $this->getContainer()->get('cantiga.mail.sender');
        try {
            $this->distributeNotification($mailer, $emails, $projectId, $resultAggrements);
            $output->writeln('<info>OK</info> areas status send');
        } catch (Exception $exception) {
            $output->writeln('<error>ERROR</error> areas status send: ' . $exception->getMessage());
        }
    }

    private function distributeNotification(MailSenderInterface $mailer, $emails, $projectId, $resultAggrements)
    {
        if (sizeof($emails) > 0) {
            $message = [
                'toSigned' => $resultAggrements[self::toSigned],
                'toAdded' => $resultAggrements[self::toAdded],
                'updated' => $resultAggrements[self::toUpdated],
                'downgraded' => $resultAggrements[self::toDowngrade]
            ];
            $mailer->send(EdkTexts::AGREEMENTS_STATUS_MAIL, $emails, $projectId, $message);
        }
    }

    private function getMails($projectId)
    {
        return explode(";", $this->getSettings($projectId, EdkSettings::NOTIFICATION_EMAILS));
    }

    private function getPublishedStatus($project, $repository)
    {
        $id = $this->getSettings($project->getId(), EdkSettings::PUBLISHED_AREA_STATUS);
        return $repository->getAreaStatus($project, $id);
    }

    private function getPublishedLikeStatus($project, $repository)
    {
        $id = $this->getSettings($project->getId(), EdkSettings::PUBLISHED_LIKE_AREA_STATUS);
        return $repository->getAreaStatus($project, $id);
    }

    private function getAreaPercentLimit($projectId)
    {
        return $this->getSettings($projectId, EdkSettings::PUBLISHED_PERCENT);
    }

    private function getRepository()
    {
        return $this->getContainer()->get(self::validationRepository);
    }
    private function getSettings($projectId, $setting)
    {
        $repository = $this->getContainer()->get('wio.edk.repo.route');
        return $repository->getSettingValue($projectId, $setting)['setting'];
    }
}