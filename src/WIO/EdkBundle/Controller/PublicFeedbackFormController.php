<?php

namespace WIO\EdkBundle\Controller;

use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use WIO\EdkBundle\Entity\EdkFeedback;
use WIO\EdkBundle\Form\PublicFeedbackForm;
use WIO\EdkBundle\Repository\EdkFeedbackRepository;
use WIO\EdkBundle\Repository\EdkRouteRepository;

/**
 * @Route("/pub/edk/{slug}/uwagi")
 */
class PublicFeedbackFormController extends PublicEdkController
{
	const CURRENT_PAGE = 'public_edk_feedback';
	const REPOSITORY_NAME = 'wio.edk.repo.feedback';

	/**
	 * @Route("/formularz", name="public_edk_feedback", defaults={"_localeFromQuery" = true})
	 */
	public function indexAction(Request $request)
	{
		/** @var EdkFeedbackRepository $repository */
		$repository = $this->get(self::REPOSITORY_NAME);
		$recaptcha = $this->get('cantiga.security.recaptcha');

		$feedback = new EdkFeedback();
		$actionParams = [
			'slug' => $this->project->getSlug(),
		];
		$route = null;
		$routeId = $request->get('r');
		try {
			if (!empty($routeId)) {
				/** @var EdkRouteRepository $routeRepository */
				$routeRepository = $this->get('wio.edk.repo.route');
				$routeRepository->setRootEntity($this->project);
				$route = $routeRepository->getItem($routeId);
				$feedback->setRoute($route);
				$actionParams['r'] = $routeId;
			}

			$form = $this->createForm(PublicFeedbackForm::class, $feedback, [
				'action' => $this->generateUrl('public_edk_feedback', $actionParams),
			]);
			$form->handleRequest($request);

			if ($form->isSubmitted()) {
				if (!$recaptcha->verifyRecaptcha($request)) {
					$form->addError(new FormError($this->trans('You did not solve the CAPTCHA correctly, sorry.', [],
						'public')));
				}
				if (empty($route)) {
					$form->addError(new FormError($this->trans('You have to select proper route.', [], 'public')));
				}
				if ($form->isValid()) {
					$repository->insert($feedback);
					return $this->redirect($this->generateUrl('public_edk_feedback_completed', [
						'currentPage' => self::CURRENT_PAGE,
						'slug' => $this->project->getSlug(),
					]));
				}
			}
		} catch (Exception $e) {
			return $this->showErrorMessage($e->getMessage());
		}

		return $this->render('WioEdkBundle:Public:feedback-form.html.twig', [
			'currentPage' => self::CURRENT_PAGE,
			'form' => $form->createView(),
			'recaptcha' => $recaptcha,
			'route' => $route,
			'slug' => $this->project->getSlug(),
		]);
	}

	/**
	 * @Route("/potwierdzenie", name="public_edk_feedback_completed", defaults={"_localeFromQuery" = true})
	 */
	public function completedAction()
	{
		return $this->render('WioEdkBundle:Public:feedback-completed.html.twig', [
			'slug' => $this->project->getSlug(),
			'currentPage' => self::CURRENT_PAGE,
		]);
	}

	private function showErrorMessage($message)
	{
		return $this->render('WioEdkBundle:Public:public-error.html.twig', [
			'message' => $this->trans($message, [], 'public'),
			'slug' => $this->project->getSlug(),
			'currentPage' => self::CURRENT_PAGE,
		]);
	}
}
