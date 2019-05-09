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
namespace Cantiga\CoreBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\CoreBundle\Controller\Traits\DashboardTrait;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\UserBundle\Entity\AgreementSignature;
use Cantiga\UserBundle\Form\ProjectAgreementsForm;
use Cantiga\UserBundle\Repository\AgreementSignatureRepository;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/s/{slug}")
 */
class PlaceDashboardController extends WorkspaceController
{
	use DashboardTrait;

	/**
	 * @Route("/dashboard", name="place_dashboard")
	 */
	public function indexAction($slug, Membership $membership, Request $request)
	{
		switch ($membership->getPlace()->getTypeName()) {
			case 'Project':
				$topExtensions = CoreExtensions::PROJECT_DASHBOARD_TOP;
				$rightExtensions = CoreExtensions::PROJECT_DASHBOARD_RIGHT;
				$centralExtensions = CoreExtensions::PROJECT_DASHBOARD_CENTRAL;
				$view = 'CantigaCoreBundle:Place:dashboard.html.twig';
				break;
			case 'Group':
				$topExtensions = CoreExtensions::GROUP_DASHBOARD_TOP;
				$rightExtensions = CoreExtensions::GROUP_DASHBOARD_RIGHT;
				$centralExtensions = CoreExtensions::GROUP_DASHBOARD_CENTRAL;
				$view = 'CantigaCoreBundle:Place:dashboard.html.twig';
				break;
			case 'Area':
				$topExtensions = CoreExtensions::AREA_DASHBOARD_TOP;
				$rightExtensions = CoreExtensions::AREA_DASHBOARD_RIGHT;
				$centralExtensions = CoreExtensions::AREA_DASHBOARD_CENTRAL;
				$view = 'CantigaCoreBundle:Area:dashboard.html.twig';
				break;
			default:
				throw new NotFoundHttpException('Place not found.');
		}

		/** @var User $user */
		$user = $this->getUser();
		$place = $membership->getPlace();
		$projectId = $place->getRootElement()->getId();
		/** @var AgreementSignatureRepository $repository */
		$repository = $this->get('cantiga.user.repo.agreement_signature');
		$signatures = $repository->getUnsignedByUserInProject($user->getId(), $projectId);
		if (count($signatures) > 0) {
			return $this->agreementsSigning($request, $place, $signatures, $slug);
		}
		
		return $this->render($view, [
			'user' => $user,
			'place' => $place,
			'topExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions($topExtensions)),
			'rightExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions($rightExtensions)),
			'centralExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions($centralExtensions)),
		]);
	}

	/**
	 * @Route("/help/{page}", name="place_help_page")
	 */
	public function helpAction($page, Request $request)
	{
		return $this->renderHelpPage($request, 'place_help_page', $page);
	}

	/**
	 * Agreements signing
	 *
	 * @param Request               $request    request
	 * @param HierarchicalInterface $place      place
	 * @param AgreementSignature[]  $signatures signatures
	 * @param string                $slug       slug
	 *
	 * @return Response
	 *
	 * @throws Exception
	 */
	private function agreementsSigning(Request $request, HierarchicalInterface $place, array $signatures, $slug)
	{
		/** @var User $user */
		$user = $this->getUser();
		$textRepository = $this->getTextRepository();
		$agreementConfirmation = $textRepository->getTextOrFalse(CoreTexts::AGREEMENT_CONFIRMATION, $request);
		/** @var AgreementSignatureRepository $repository */
		$repository = $this->get('cantiga.user.repo.agreement_signature');
		$lastSigned = $repository->getLastSigned($user->getId());
		$form = $this->createForm(ProjectAgreementsForm::class, null, [
			'action' => $this->generateUrl('place_dashboard', [
				'slug' => $slug,
			]),
			'confirmation' => empty($agreementConfirmation) ? null : $agreementConfirmation->getContent(),
			'lastSigned' => $lastSigned,
			'signatures' => $signatures,
		]);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();
			if ($data['confirmation'] === false) {
				return $this->redirectToRoute('place_dashboard', [
					'slug' => $slug,
				]);
			}

			$time = time();
			$lowestAge = (new DateTime())->modify('-18 years');
			$signaturesToUpdate = [];
			foreach ($signatures as $signature) {
				$signatureKey = 'signature_' . $signature->getId();
				if (!array_key_exists($signatureKey, $data) || $data[$signatureKey] !== true) {
					continue;
				}
				$signature
					->setFirstName($data['firstName'])
					->setLastName($data['lastName'])
					->setTown($data['town'])
					->setZipCode($data['zipCode'])
					->setStreet($data['street'])
					->setHouseNo($data['houseNo'])
					->setFlatNo($data['flatNo'])
					->setPesel($data['pesel'])
					->setSignedAt($time)
                    ->setSentAt(null)
					->setUpdatedBy($user->getId())
				;
				if (count($signaturesToUpdate) === 0 && $signature->getDateOfBirth() > $lowestAge) {
					$error = new FormError($this->trans('Your are too young to sign this agreement.', [], 'validators'));
					$form->get('pesel')->addError($error);
					break;
				}
				$signaturesToUpdate[] = $signature;
			}

			if ($form->isValid()) {
				foreach ($signaturesToUpdate as $signature) {
					$repository->update($signature);
				}
				$confirmed = $textRepository->getTextOrFalse(CoreTexts::AGREEMENT_CONFIRMED, $request);
				if (!empty($confirmed)) {
					$this
						->get('session')
						->getFlashBag()
						->add('info', $confirmed->getContent())
					;
				}

				return $this->redirectToRoute('place_dashboard', [
					'slug' => $slug,
				]);
			}
		}

		return $this->render('CantigaCoreBundle:Agreements:form.html.twig', [
			'form' => $form->createView(),
			'place' => $place,
			'signatures' => $signatures,
			'user' => $user,
		]);
	}
}
