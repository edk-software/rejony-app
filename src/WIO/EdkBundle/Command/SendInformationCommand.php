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

use Cantiga\CoreBundle\Mail\MailSenderInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use WIO\EdkBundle\EdkTexts;

/**
 * @author Tomasz Jędrzejewski
 */
class SendInformationCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('cantiga:edk:send-information')
			->setDescription('Sends the e-mail information from discussion chanel.')
            ->addOption('projectId', 'p', InputOption::VALUE_REQUIRED, 'Project id')
		;
	}
	
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$repository = $this->getContainer()->get('wio.edk.repo.export');
		$mailer = $this->getContainer()->get('cantiga.mail.sender');
        $projectId = (int) $input->getOption('projectId');
		$summary = $repository->getUsersByProject($projectId);
		
		foreach ($summary as $user) {
			try {
				$this->distributeNotification($mailer, $user);
				$output->writeln('<info>OK</info> '.$user.': notification sent');
			} catch(Exception $exception) {
				$output->writeln('<error>ERROR</error> '.$user.': '.$exception->getMessage());
			}
		}
	}
	
	private function distributeNotification(MailSenderInterface $mailer, $user)
	{
		if (sizeof($user) > 0) {
			$mailer->send(EdkTexts::PROJECT_MESSAGE, $user, $user, ['mail'=>$user]);
		}
	}
}