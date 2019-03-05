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
namespace Cantiga\UserBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\FormAction;
use Cantiga\CoreBundle\Api\Controller\UserPageController;
use Cantiga\CoreBundle\CoreTexts;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\UserBundle\Form\UserChangeEmailForm;
use Cantiga\UserBundle\Form\UserChangePasswordForm;
use Cantiga\UserBundle\Form\UserAgreementsForm;
use Cantiga\UserBundle\Form\UserPhotoUploadForm;
use Cantiga\UserBundle\Form\UserSettingsForm;
use Cantiga\UserBundle\Intent\EmailChangeIntent;
use Cantiga\UserBundle\Intent\PasswordChangeIntent;
use Cantiga\UserBundle\Intent\UserProfilePhotoIntent;
use Cantiga\UserBundle\Repository\ContactRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/user/profile")
 * @Security("has_role('ROLE_USER')")
 */
class ProfileController extends UserPageController
{
	const REPOSITORY = 'cantiga.user.repo.profile';
	const TEMPLATE_LOCATION = 'CantigaUserBundle:Profile';
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->breadcrumbs()->workgroup('profile');
	}

	/**
	 * @Route("/contact-data", name="user_profile_contact_data")
	 */
	public function contactDataAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Contact data', [], 'pages'), 'user_profile_contact_data');
		$user = $this->getUser();
        $marketingAgreementLabel = $this
            ->getTextRepository()
            ->getText(CoreTexts::MARKETING_AGREEMENT, $request)
            ->getContent()
        ;
        return $this->render(self::TEMPLATE_LOCATION . ':contact-data.html.twig', [
			'location' => $user->getLocation(),
			'hasMarketingAgreement' => $user->hasMarketingAgreement(),
			'marketingAgreementLabel' => strip_tags($marketingAgreementLabel),
		]);
	}

	/**
	 * @Route("/contact-data/projects", name="user_profile_contact_data_api_projects")
	 */
	public function apiLoadProjectContactsAction(Request $request)
	{
		return new JsonResponse(['success' => 1, 'projects' => 
			$this->getContactRepository()->findAllContactData($this->getUser())
		]);
	}

	/**
	 * @Route("/contact-data/project", name="user_profile_contact_data_api_project_update")
	 * @Method({"POST"})
	 */
	public function apiContactUpdateAction(Request $request)
	{
		try {
			$id = $request->get('id');
			$notes = $request->get('notes');
			$email = filter_var($request->get('email'), FILTER_VALIDATE_EMAIL);
			$telephone = filter_var($request->get('telephone'), FILTER_SANITIZE_STRING);

			if (false === $email || strlen($email) == 0 || strlen($email) > 100) {
				throw new \Exception('Please provide a valid e-mail addres no longer than 100 characters.');
			}
			if (false === $telephone || strlen($telephone) == 0 || strlen($telephone) > 30) {
				throw new \Exception('Please provide a valid telephone number no longer than 30 characters.');
			}

			$contactData = $this->getContactRepository()->findContactData($this->getContactRepository()->getPlaceProjectById($id), $this->getUser());
			$contactData->setEmail($email)
				->setTelephone($telephone)
				->setNotes($notes);

			$this->getContactRepository()->persistContactData($contactData);
		
			return new JsonResponse(['success' => 1, 'project' =>
				$contactData->asArray(),
			]);
		} catch (\Exception $exception) {
			return new JsonResponse(['success' => 0, 'error' => $this->trans($exception->getMessage(), [], 'validators')]);
		}
	}
	
	/**
	 * @Route("/contact-data/location", name="user_profile_contact_data_api_location_update")
	 * @Method({"POST"})
	 */
	public function apiLocationUpdateAction(Request $request)
	{
		try {
			$location = $request->get('location');
			$repo = $this->get(self::REPOSITORY);

			$this->getUser()->setLocation($location);
			$repo->updateProfile($this->getUser());
			return new JsonResponse(['success' => 1, 'location' => $location]);
		} catch (\Exception $exception) {
			return new JsonResponse(['success' => 0]);
		}
	}

	/**
	 * @Route("/contact-data/agreements", name="user_profile_contact_data_api_agreements_update")
	 * @Method({"POST"})
	 */
	public function apiAgreementsUpdateAction(Request $request)
	{
		try {
            $marketingAgreement = $request->request->getBoolean('marketing_agreement');

            $user = $this->getUser();
			$user->setMarketingAgreementAt($marketingAgreement ? time() : null);

            $repository = $this->get(self::REPOSITORY);
			$repository->update($user);

			return new JsonResponse([
                'marketing_agreement' => $user->hasMarketingAgreement(),
                'success' => 1,
            ]);
		} catch (\Exception $exception) {
			return new JsonResponse([
			    'success' => 0,
            ]);
		}
	}
	
	/**
	 * @Route("/settings", name="user_profile_settings")
	 */
	public function settingsAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Settings', [], 'pages'), 'user_profile_settings');
		$repo = $this->get(self::REPOSITORY);
		$action = new FormAction($this->getUser(), UserSettingsForm::class, ['languageRepository' => $this->get('cantiga.core.repo.language')]);
		return $action->action($this->generateUrl('user_profile_settings'))
			->template(self::TEMPLATE_LOCATION.':settings.html.twig')
			->redirect($this->generateUrl('user_profile_settings'))
			->formSubmittedMessage('UserSettingsUpdatedText')
			->onSubmit(function($user) use($repo) {
				$repo->updateSettings($user);

				$locale = $user->getSettingsLanguage()
					->getLocale();
				$session = $this->get('session');
				$session->set('timezone', $user->getSettingsTimezone());
				$session->set('_locale', $locale);
				$session->set('_user_locale', $locale);
			})
			->run($this, $request);
	}
	
	/**
	 * @Route("/change-mail", name="user_profile_change_mail")
	 */
	public function changeEmailAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Change e-mail', [], 'pages'), 'user_profile_change_mail');
		$repo = $this->get(self::REPOSITORY);
		$intent = new EmailChangeIntent($repo, $this->get('event_dispatcher'), $this->get('security.encoder_factory'), $this->getUser());
		$action = new FormAction($intent, UserChangeEmailForm::class);
		return $action->action($this->generateUrl('user_profile_change_mail'))
			->template(self::TEMPLATE_LOCATION.':change-email.html.twig')
			->redirect($this->generateUrl('user_profile_change_mail'))
			->formSubmittedMessage('ConfirmationLinkChangeEmailSentText')
			->onSubmit(function(EmailChangeIntent $intent) use($repo) {
				$intent->execute();
			})
			->run($this, $request);
	}
	
	/**
	 * @Route("/change-pass", name="user_profile_change_password")
	 */
	public function changePasswordAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Change password', [], 'pages'), 'user_profile_change_password');
		$repo = $this->get(self::REPOSITORY);
		$intent = new PasswordChangeIntent($repo, $this->get('event_dispatcher'), $this->get('security.encoder_factory'), $this->getUser());
		$action = new FormAction($intent, UserChangePasswordForm::class);
		return $action->action($this->generateUrl('user_profile_change_password'))
			->template(self::TEMPLATE_LOCATION.':change-password.html.twig')
			->redirect($this->generateUrl('user_profile_change_password'))
			->formSubmittedMessage('ConfirmationLinkChangePasswordSentText')
			->onSubmit(function(PasswordChangeIntent $intent) use($repo) {
				$intent->execute();
			})
			->run($this, $request);
	}
	
	/**
	 * @Route("/confirm-credential-change/{id}/{provisionKey}", name="user_profile_confirm_credential_change")
	 */
	public function confirmChangeAction($id, $provisionKey, Request $request)
	{
		try {
			$repo = $this->get(self::REPOSITORY);
			$changeRequest = $repo->getCredentialChangeRequest($id, $this->getUser());
			if ($changeRequest->verify($provisionKey, $_SERVER['REMOTE_ADDR'])) {
				$changeRequest->export();
			}
			$repo->completeCredentialChangeRequest($changeRequest);
			$this->get('session')->getFlashBag()->add('info', $this->trans('The credentials have been changed.', [], 'users'));
		} catch(ModelException $exception) {
			$this->get('session')->getFlashBag()->add('error', $this->trans('There is no such credentials set to update.', [], 'users'));
		}
		return $this->redirect($this->generateUrl('cantiga_home_page'));
	}
	
	/**
	 * @Route("/manage-photo", name="user_profile_photo")
	 */
	public function photoAction(Request $request)
	{
		$this->breadcrumbs()->entryLink($this->trans('Manage photo', [], 'pages'), 'user_profile_photo');
		$repo = $this->get(self::REPOSITORY);
		$intent = new UserProfilePhotoIntent($this->getUser(), $repo, $this->get('kernel')->getRootDir().'/../web/ph');
		$action = new FormAction($intent, UserPhotoUploadForm::class);
		return $action->action($this->generateUrl('user_profile_photo'))
			->template(self::TEMPLATE_LOCATION.':photo.html.twig')
			->redirect($this->generateUrl('user_profile_photo'))
			->formSubmittedMessage('UserPhotoUploadedText')
			->onSubmit(function(UserProfilePhotoIntent $intent) use($repo) {
				$intent->execute();
			})
			->run($this, $request);
	}
	
	private function getContactRepository(): ContactRepository
	{
		return $this->get('cantiga.user.repo.contact');
	}

    /**
     * @Route("/agreements", name="user_profile_agreements")
     */
    public function agreementsAction(Request $request)
    {
        /** @var User|null $user */
        $user = $this->getUser();
        if (!isset($user)) {
            return $this->redirectToRoute('cantiga_auth_login');
        }
        if ($user->isTermsOfUseAccepted() && $user->isPersonalDataAllowed()) {
            return $this->redirectToRoute('cantiga_home_page');
        }
        $textRepository = $this->getTextRepository();

        $termsOfUseLink = $textRepository
            ->getText(CoreTexts::TERMS_OF_USE_LINK, $request)
        ;
        $termsOfUseUrl = $termsOfUseLink->isEmpty() ? $this->generateUrl('cantiga_auth_terms') : $termsOfUseLink->getContent();
        $termsOfUse = $textRepository
            ->getText(CoreTexts::TERMS_OF_USE_LABEL, $request)
        ;
        $termsOfUseLabel = !$termsOfUse->isEmpty() ? $termsOfUse->getContent(): sprintf(
            $this->trans('I have read and accept <a href="%s" target="_blank">the terms of use</a>.'),
            $termsOfUseUrl
        );
        $marketingAgreementLabel = $textRepository
            ->getText(CoreTexts::MARKETING_AGREEMENT, $request)
            ->getContent()
        ;
        $termsOfEditionLabel = $textRepository
            ->getText(CoreTexts::PROCESSING_PERSONAL_DATA, $request)
            ->getContent()
        ;


        $personalDataInfo = $textRepository
            ->getText(CoreTexts::PERSONAL_DATA_INFO, $request)
            ->getContent()
        ;
        $form = $this->createForm(UserAgreementsForm::class, null, [
            'action' => $this->generateUrl('user_profile_agreements'),
            'marketingAgreementLabel' => strip_tags($marketingAgreementLabel),
            'personalDataLabel' => $termsOfEditionLabel,
            'termsOfUseLabel' => $termsOfUseLabel,
            'user' => $user,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $time = time();
            $user
                ->setTermsOfUseAcceptedAt($time)
                ->setPersonalDataAllowedAt($time)
                ->setMarketingAgreementAt($data['marketingAgreement'] ? $time : null)
            ;
            $this
                ->get('cantiga.user.repo.profile')
                ->update($user)
            ;
            return $this->redirectToRoute('cantiga_home_page');
        }

        return $this->render('CantigaUserBundle:Profile:agreements.html.twig', [
            'form' => $form->createView(),
            'personalDataInfo' => $personalDataInfo,
        ]);
    }
}
