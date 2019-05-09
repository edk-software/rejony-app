<?php

namespace Cantiga\UserBundle\Command;

use Cantiga\CoreBundle\Mail\MailSenderInterface;
use Cantiga\UserBundle\Repository\AgreementSignatureRepository;
use Cantiga\UserBundle\Repository\UserRepository;
use Cantiga\UserBundle\UserTexts;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cantiga\Metamodel\Transaction;
use Exception;

class SendAgreementsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('cantiga:agreements:send')
			->setDescription('Sends agreements to users.')
			->addArgument('period', InputArgument::REQUIRED, 'Period in seconds in which command execution should ends.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
	    $period = (int) $input->getArgument('period');
	    $finishTime = time() + $period;

        $avgActionTime = 0;
        $maxActionTime = 0;
        $actionsNumber = 0;

        do {
            $startTime = microtime(true);
            $sent = $this->notifyOneUser($output);
            $endTime = microtime(true);
            $actionTime = $endTime - $startTime;
            $avgActionTime = ($avgActionTime * $actionsNumber + $actionTime) / ($actionsNumber + 1);
            $maxActionTime = max($maxActionTime, $actionTime);
            $actionsNumber++;
        } while ($sent && $endTime + 2 * $avgActionTime <= $finishTime);
	}

	private function notifyOneUser(OutputInterface $output) : bool
    {
        $container = $this->getContainer();

        try {
            /** @var AgreementSignatureRepository $signatureRepository */
            $signatureRepository = $container->get('cantiga.user.repo.agreement_signature');
            $signatures = $signatureRepository->getNotSentForOneSigner();
            if (count($signatures) === 0) {
                return false;
            }

            /** @var UserRepository $userRepository */
            $userRepository = $container->get('cantiga.user.repo.user');
            $signer = $userRepository->getItem($signatures[0]->getSignerId());
            if (!isset($signer)) {
                $output->writeln(sprintf(
                    '<error>Signer not found for signature ID %d.</error>',
                    $signatures[0]->getId()
                ));
                return false;
            }
        } catch (Exception $exception) {
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            return false;
        }

        /** @var Transaction $transaction */
        $transaction = $container->get('cantiga.transaction');
        $transaction->requestTransaction();

        try {
            $time = time();
            foreach ($signatures as $signature) {
                $signature->setSentAt($time);
                $signatureRepository->update($signature);
            }

            /** @var MailSenderInterface $mailer */
            $mailer = $container->get('cantiga.mail.sender');
            $mailer->send(UserTexts::AGREEMENTS_MAIL, $signer->getEmail(), $signer->getEmail(), [
                'signatures' => $signatures,
                'signer' => $signer,
            ]);

            $output->writeln(sprintf(
                '<info>E-mail with %d agreement confirmations has been sent to %s.</info>',
                count($signatures),
                $signer->getEmail()
            ));
        } catch (Exception $exception) {
            $transaction->requestRollback();
            $output->writeln(sprintf('<error>%s</error>', $exception->getMessage()));
            return false;
        }
        $transaction->closeTransaction();

        return true;
    }
}
