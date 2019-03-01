<?php

namespace Cantiga\UserBundle\Extension;

use Cantiga\Components\Hierarchy\Entity\AbstractProfileView;
use Cantiga\Components\Hierarchy\Entity\Member;
use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\UserBundle\Entity\Agreement;
use Cantiga\UserBundle\Entity\AgreementSignature;
use Cantiga\UserBundle\Form\MemberToAgreementsForm;
use Cantiga\UserBundle\Repository\AgreementRepository;
use Cantiga\UserBundle\Repository\AgreementSignatureRepository;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This built-in profile extension shows other places in the project the user is
 * member of.
 */
class AgreementsProfileExtension implements ExecutableExtensionInterface, ProfileExtensionInterface,
	SecuredExtensionInterface
{
	use SecuredExtensionTrait;

	/** @var EngineInterface */
	private $templating;

	/** @var TranslatorInterface */
	private $translator;

	/** @var AgreementRepository */
	private $agreementRepository;

	/** @var AgreementSignatureRepository */
	private $signatureRepository;

	/** @var Agreement[]|null */
	private $agreements;

	/** @var Form|null */
	private $form;

	public function __construct(EngineInterface $templating, TranslatorInterface $translator,
		AuthorizationCheckerInterface $authChecker, AgreementRepository $agreementRepository,
		AgreementSignatureRepository $signatureRepository)
	{
		$this->templating = $templating;
		$this->translator = $translator;
		$this->agreementRepository = $agreementRepository;
		$this->signatureRepository = $signatureRepository;

		$this->setAuthChecker($authChecker);
	}

	public function execute(AbstractProfileView $member, Membership $membership, User $user, callable $getForm,
		callable $reload)
	{
		$project = $membership->getPlace()
			->getRootElement();
		$this->agreements = $this->agreementRepository->getAllByUserInProject($member->getId(), $project->getId());

		$this->form = $getForm(MemberToAgreementsForm::class, null, [
			'agreements' => $this->agreements,
		]);
		if ($this->form->isSubmitted() && $this->form->isValid()) {
			$data = $this->form->getData();
			foreach ($this->agreements as $agreement) {
				/** @var AgreementSignature|null $signature */
				$signature = $agreement->getSignatures()->get(0);
				$required = $data['agreement_' . $agreement->getId()];
				if (!is_bool($required)) {
					continue;
				}
				if ($required && !isset($signature)) {
					$newSignature = new AgreementSignature();
					$newSignature
						->setAgreementId($agreement->getId())
						->setAgreement($agreement)
						->setSignerId($member->getId())
						->setProjectId($project->getId())
						->setCreatedBy($user->getId())
					;
					$this->signatureRepository->insert($newSignature);
				} elseif (!$required && isset($signature) && $signature->getSignerId() == $member->getId()) {
					$this->signatureRepository->delete($signature);
				}
			}
			return $reload();
		}
	}
	
	public function getTabTitle(): string
	{
		return $this->translator->trans('AgreementsText', [], 'users');
	}
	
	public function getTabHashtag(): string
	{
		return 'agreements';
	}

	public function getTabContent(Member $member)
	{
		return $this->templating->render('CantigaUserBundle:Memberlist:agreements-profile-extension.html.twig', [
			'agreements' => $this->agreements,
			'form' => $this->form->createView(),
		]);
	}
}
