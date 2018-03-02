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
namespace WIO\EdkBundle\Controller;

use Cantiga\CoreBundle\CoreTexts;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use DateInterval;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use WIO\EdkBundle\EdkSettings;
use WIO\EdkBundle\EdkTexts;
use WIO\EdkBundle\Entity\EdkParticipant;
use WIO\EdkBundle\Form\EdkCheckRegistrationForm;
use WIO\EdkBundle\Form\PublicParticipantForm;
use WIO\EdkBundle\Repository\EdkParticipantRepository;

/**
 * @Route("/pub/edk/{slug}/zapisy")
 */
class PublicRegistrationFormController extends PublicEdkController
{
	const CURRENT_PAGE = 'public_edk_register';
	const REPOSITORY_NAME = 'wio.edk.repo.participant';

	/**
	 * @Route("/formularz", name="public_edk_register", defaults={"_localeFromQuery" = true})
	 */
	public function indexAction($slug, Request $request)
	{
		/** @var EdkParticipantRepository $repository */
		$repository = $this->get(self::REPOSITORY_NAME);
		$recaptcha = $this->get('cantiga.security.recaptcha');

		$formErrors = [];
		$registrationSettings = null;
		$routeId = $request->get('r');
		try {
			if (!empty($routeId)) {
				$activeStatus = $this
					->getProjectSettings()
					->get(EdkSettings::PUBLISHED_AREA_STATUS)
					->getValue()
				;
				$registrationSettings = $repository->getPublicRegistration($routeId, $activeStatus);
				if (!$registrationSettings->isRegistrationOpen()) {
					return $this->showErrorMessage('RegistrationOverErrorMsg');
				}
			}
		} catch (Exception $exception) {
			$formErrors[] = new FormError($this->trans($exception->getMessage(), [], 'public'));
		}

		$participant = new EdkParticipant();
		$participant->setPeopleNum(1);
		$actionParams = [
			'slug' => $slug,
		];
		if (isset($registrationSettings)) {
			$participant->setRegistrationSettings($registrationSettings);
			$actionParams['r'] = $registrationSettings
				->getRoute()
				->getId()
			;
		}
		$form = $this->createForm(PublicParticipantForm::class, $participant, [
			'action' => $this->generateUrl('public_edk_register', $actionParams),
			'registrationSettings' => $registrationSettings,
			'texts' => $this->buildTexts($request),
		]);
		$form->handleRequest($request);

		if ($form->isSubmitted()) {
			if (!$recaptcha->verifyRecaptcha($request)) {
				$formErrors[] = new FormError($this->trans('You did not solve the CAPTCHA correctly, sorry.', [],
                    'public'));
			}
			if (empty($routeId)) {
				$formErrors[] = new FormError($this->trans('You have to select proper route.', [], 'public'));
			}
			foreach ($formErrors as $formError) {
				$form->addError($formError);
			}
			if ($form->isValid()) {
				$participant->setIpAddress(ip2long($_SERVER['REMOTE_ADDR']));
				$repository->register($participant, $_SERVER['REMOTE_ADDR'], $slug);
				return $this->redirect($this->generateUrl('public_edk_registration_completed', [
					'accessKey' => $participant->getAccessKey(),
					'currentPage' => self::CURRENT_PAGE,
					'slug' => $slug,
				]));
			}
		}

		$textRepository = $this->getTextRepository();
		$text = $textRepository->getText(EdkTexts::REGISTRATION_FORM_TEXT, $request, $this->project);
		$personalDataInfo = $textRepository->getText(CoreTexts::PERSONAL_DATA_INFO, $request, $this->project);
		$response = $this->render('WioEdkBundle:Public:registration-form.html.twig', [
			'currentPage' => self::CURRENT_PAGE,
			'form' => $form->createView(),
			'personalDataInfo' => $personalDataInfo->getContent(),
			'recaptcha' => $recaptcha,
			'registrationSettings' => $registrationSettings,
			'route' => isset($registrationSettings) ? $registrationSettings->getRoute() : null,
			'slug' => $this->project->getSlug(),
			'text' => $text,
		]);
		return $response;
	}

	/**
	 * @Route("/sprawdz", name="public_edk_check", defaults={"_localeFromQuery" = true})
	 */
	public function checkAction(Request $request)
	{
		try {
			$recaptcha = $this->get('cantiga.security.recaptcha');
			$form = $this->createForm(EdkCheckRegistrationForm::class, [
				'k' => $request->query->get('k'),
				't' => $request->query->getInt('t'),
			]);
			$form->handleRequest($request);

			if ($form->isValid()) {
				if ($recaptcha->verifyRecaptcha($request)) {
					$k = $form->get('k')->getData();
					$t = $form->get('t')->getData();

					switch ($t) {
						case EdkCheckRegistrationForm::TYPE_CHECK:
							return $this->checkRequest($k);

						case EdkCheckRegistrationForm::TYPE_REMOVE:
							return $this->removeRequest($k);
					}
				} else {
					return $this->showErrorMessage('You did not solve the CAPTCHA correctly, sorry.');
				}
			}

			return $this->render('WioEdkBundle:Public:check-registration.html.twig', [
				'currentPage' => 'public_edk_check',
				'form' => $form->createView(),
				'recaptcha' => $recaptcha,
				'slug' => $this->project->getSlug(),
			]);
		} catch (ModelException $exception) {
			return $this->showErrorMessage($exception->getMessage());
		}
	}

	/**
	 * @Route("/potwierdzenie/{accessKey}", name="public_edk_registration_completed", defaults={"_localeFromQuery" = true})
	 */
	public function completedAction($accessKey, Request $request)
	{
		return $this->render('WioEdkBundle:Public:registration-completed.html.twig', [
			'accessKey' => $accessKey,
			'slug' => $this->project->getSlug(),
			'currentPage' => self::CURRENT_PAGE,
		]);
	}

	/**
	 * @Route("/api/data", name="public_edk_registration_data", defaults={"_localeFromQuery" = true})
	 */
	public function registrationsAction(Request $request)
	{
		try {
			$repository = $this->get('wio.edk.repo.published_data');
			$registrations = $repository->getOpenRegistrations($this->project, $this->getProjectSettings()->get(EdkSettings::PUBLISHED_AREA_STATUS)->getValue());
			$response = new JsonResponse($registrations);
			$response->setDate(new DateTime());
			$exp = new DateTime();
			$exp->add(new DateInterval('PT0H5M0S'));
			$response->setExpires($exp);
			return $response;
		} catch(ItemNotFoundException $exception) {
			return new JsonResponse(['success' => 0]);
		}
	}

	private function checkRequest($key)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			list($item, $notes) = $repository->getItemByKey($key, $this->getProjectSettings()->get(EdkSettings::PUBLISHED_AREA_STATUS)->getValue());
			$response = $this->render('WioEdkBundle:Public:check-result.html.twig', [
				'item' => $item,
				'beginningNote' => $notes->getEditableNote(1),
				'slug' => $this->project->getSlug(),
				'showGuide' => $item->getRegistrationSettings()->getRoute()->isDescriptionFilePublished(),
				'showMap' => $item->getRegistrationSettings()->getRoute()->isMapFilePublished(),
				'currentPage' => 'public_edk_check',
			]);
			return $response;
		} catch(ItemNotFoundException $exception) {
			return $this->showErrorMessage('ParticipantNotFoundErrMsg');
		}
	}

	private function removeRequest($key)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->removeItemByKey($key, $this->getProjectSettings()->get(EdkSettings::PUBLISHED_AREA_STATUS)->getValue());
			return $this->render('WioEdkBundle:Public:public-message.html.twig', [
				'message' => $this->trans('RequestRemovedMsg', [], 'public'),
				'slug' => $this->project->getSlug(),
				'currentPage' => 'public_edk_check',
			]);
		} catch(ItemNotFoundException $exception) {
			return $this->showErrorMessage('ParticipantNotFoundErrMsg');
		}
	}
	
	private function showErrorMessage($message)
	{
		return $this->render('WioEdkBundle:Public:public-error.html.twig', [
			'message' => $this->trans($message, [], 'public'),
			'slug' => $this->project->getSlug(),
			'currentPage' => self::CURRENT_PAGE,
		]);
	}
	
	private function buildTexts(Request $request): array
	{
		return [
			1 => $this->getTextRepository()->getText(EdkTexts::REGISTRATION_TERMS1_TEXT, $request, $this->project)->getContent(),
			2 => $this->getTextRepository()->getText(EdkTexts::REGISTRATION_TERMS2_TEXT, $request, $this->project)->getContent(),
			3 => $this->getTextRepository()->getText(EdkTexts::REGISTRATION_TERMS3_TEXT, $request, $this->project)->getContent()
		];
	}
}
