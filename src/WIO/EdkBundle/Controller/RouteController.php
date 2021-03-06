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

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\Components\Hierarchy\Importer\ImporterInterface;
use Cantiga\Components\Hierarchy\HierarchicalInterface;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\QuestionHelper;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Message;
use Cantiga\CoreBundle\Entity\User;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\Client\RouteVerifierClient;
use WIO\EdkBundle\Entity\EdkRoute;
use WIO\EdkBundle\Exception\RouteVerifierResultException;
use WIO\EdkBundle\Form\EdkRouteForm;
use WIO\EdkBundle\Form\AreaRoutesImportForm;
use WIO\EdkBundle\EdkTexts;
use WIO\EdkBundle\Repository\EdkRouteRepository;

/**
 * @Route("/s/{slug}/routes")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class RouteController extends WorkspaceController
{
	const REPOSITORY_NAME = 'wio.edk.repo.route';
	const API_LIST_PAGE = 'edk_route_api_list';
	const API_RELOAD_PAGE = 'edk_route_api_reload';
	const API_UPDATE_PAGE = 'edk_route_api_update';
	const API_FEED_PAGE = 'edk_route_api_feed';
	const API_POST_PAGE = 'edk_route_api_post';
	const APPROVE_PAGE = 'edk_route_approve';
	const REVOKE_PAGE = 'edk_route_revoke';
	const APPROVE_GPS_PAGE = 'edk_route_approve_gps';
	const REVOKE_GPS_PAGE = 'edk_route_revoke_gps';
	const APPROVE_DESCRIPTION_PAGE = 'edk_route_approve_description';
	const REVOKE_DESCRIPTION_PAGE = 'edk_route_revoke_description';
	const APPROVE_MAP_PAGE = 'edk_route_approve_map';
	const REVOKE_MAP_PAGE = 'edk_route_revoke_map';
	const AREA_INFO_PAGE = 'area_mgmt_info';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$place = $this->get('cantiga.user.membership.storage')->getMembership()->getPlace();
		
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setRootEntity($place);
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('WioEdkBundle:EdkRoute:')
			->setItemNameProperty('name')
			->setPageTitle('Routes')
			->setPageSubtitle('Manage the routes of Extreme Way of the Cross')
			->setIndexPage('edk_route_index')
			->setInfoPage('edk_route_info')
			->setInsertPage('edk_route_insert')
			->setEditPage('edk_route_edit')
			->setRemovePage('edk_route_remove')
			->setItemCreatedMessage('The route \'0\' has been created.')
			->setItemUpdatedMessage('The route \'0\' has been updated.')
			->setItemRemovedMessage('The route \'0\' has been removed.')
			->setRemoveQuestion('Do you really want to remove the route \'0\'?');

		$this->breadcrumbs()
			->workgroup('routes')
			->entryLink($this->trans('Routes list', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="edk_route_index")
	 */
	public function indexAction(Request $request, Membership $membership)
	{
		$dataTable = $this->crudInfo->getRepository()->createDataTable();
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'insertPage' => $this->crudInfo->getInsertPage(),
			'ajaxListPage' => self::API_LIST_PAGE,
			'isArea' => $this->isArea($membership),
			'importer' => $this->getImportService()
		));
	}
	
	/**
	 * @Route("/ajax-list", name="edk_route_api_list")
	 */
	public function apiListAction(Request $request)
	{
		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('edit_link', $this->crudInfo->getEditPage(), ['id' => '::id', 'slug' => $this->getSlug()])
			->link('remove_link', $this->crudInfo->getRemovePage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->crudInfo->getRepository();
		$dataTable = $repository->createDataTable();
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable)));
	}
	
	/**
	 * @Route("/{id}/info", name="edk_route_info")
	 */
	public function infoAction($id, Membership $membership, Request $request)
	{
        $text = $this->getTextRepository()->getTextOrFalse(EdkTexts::ROUTE_INFO_TEXT, $request, $this->getActiveProject());
		$action = new InfoAction($this->crudInfo);
		$action->slug($this->getSlug())
			->set('ajaxReloadPage', self::API_RELOAD_PAGE)
			->set('ajaxUpdatePage', self::API_UPDATE_PAGE)
			->set('ajaxChatFeedPage', self::API_FEED_PAGE)
			->set('ajaxChatPostPage', self::API_POST_PAGE)
			->set('areaInfoPage', self::AREA_INFO_PAGE)
            ->set('user', $this->getUser())
            ->set('isGroup', $this->isGroup($membership))
            ->set('infoText', $text)
			->set('isArea', $this->isArea($membership));
		if (!$this->isArea($membership)) {
			$action->set('approvePage', self::APPROVE_PAGE)->set('revokePage', self::REVOKE_PAGE);
		}
		$action->setMap($this->get('cantiga.security.map'));
		return $action->run($this, $id);
	}
	 
	/**
	 * @Route("/insert", name="edk_route_insert")
	 */
	public function insertAction(Request $request, Membership $membership)
	{
		$entity = new EdkRoute();
		$this->crudInfo->setInfoPage('edk_route_verify');
		$action = new InsertAction($this->crudInfo, $entity, EdkRouteForm::class, [
			'areaRepository' => $this->findAreaRepository($membership),
            'mode' => EdkRouteForm::ADD,
		]);
		$action->slug($this->getSlug());
		$action->set('isArea', $this->isArea($membership));
		return $action->run($this, $request);
	}
	
	/**
	 * @Route("/{id}/edit", name="edk_route_edit")
	 */
	public function editAction($id, Request $request, Membership $membership)
	{
        $this->crudInfo->setInfoPage('edk_route_verify');
		$action = new EditAction($this->crudInfo, EdkRouteForm::class, [
			'areaRepository' => $this->findAreaRepository($membership),
            'mode' => EdkRouteForm::EDIT,
		]);
		$action->slug($this->getSlug());
		$action->set('isArea', $this->isArea($membership));
		return $action->run($this, $id, $request);
	}
	
	/**
	 * @Route("/{id}/remove", name="edk_route_remove")
	 */
	public function removeAction($id, Request $request)
	{
		$action = new RemoveAction($this->crudInfo);
		$action->slug($this->getSlug());
		return $action->run($this, $id, $request);
	}

    /**
     * @Route("/{id}/verify", name="edk_route_verify")
     */
    public function verifyAction($id, Membership $membership, Request $request)
    {
        $user = $this->getUser();

        try {
            /** @var EdkRouteRepository $routeRepository */
            $routeRepository = $this->crudInfo->getRepository();
            $route = $routeRepository->getItem($id);
        } catch (ItemNotFoundException $exception) {
            throw new NotFoundHttpException('Route not found.');
        }

        /** @var SessionInterface $session */
        $session = $this->get('session');
        /** @var FlashBagInterface $flashBag */
        $flashBag = $session->getFlashBag();

        try {
            /** @var RouteVerifierClient $routeVerifier */
            $routeVerifier = $this->get('wio.edk.route_verifier');
            $result = $routeVerifier->verify($route);
            $route
                ->setElevationCharacteristic($result->getElevationCharacteristic())
                ->setPathCoordinates($result->getPathCoordinates())
                ->setStations($result->getStations())
                ->setPathStart($result->getPathStart())
                ->setPathEnd($result->getPathEnd())
                ->setVerificationStatus($result->getVerificationStatus())
                ->setRouteAscent($result->getRouteAscent())
                ->setRouteLength($result->getRouteLength())
                ->setRouteType($result->getRouteType())
            ;
            if ($result->isValid()) {
                $message = $this->trans('VerificationSuccessful', [], 'edk');
                $routeRepository->approve($route, $user, true);
                $flashBag->add('info', implode(' ', array_merge([$message], $result->getVerificationLogs())));
            } else {
                $message = $this->trans('VerificationFailed', [], 'edk');
                $routeRepository->revoke($route, $user, true);
                $flashBag->add('alert', implode(' ', array_merge([
                    $this->trans('VerificationFailed', [], 'edk'),
                ], $result->getVerificationLogs())));
            }
        } catch (Exception $exception) {
            $messages = [$this->trans('VerificationError', [], 'edk'), $exception->getMessage()];
            $previousException = $exception->getPrevious();
            if ($previousException instanceof RouteVerifierResultException) {
                foreach ($previousException->getViolations() as $violation) {
                    $messages[] = $violation;
                }
            } elseif (isset($previousException)) {
                $messages[] = $previousException->getMessage();
            }
            $flashBag->add('error', implode(' ', $messages));
        }

        return $this->redirectToRoute('edk_route_info', [
            'id' => $id,
            'slug' => $this->getSlug(),
        ]);
    }
	
	/**
	 * @Route("/{id}/api-reload", name="edk_route_api_reload")
	 */
	public function apiReloadAction($id, Request $request)
	{
		try {
			$route = $this->crudInfo->getRepository()->getItem($id);
			return new JsonResponse(['success' => 1, 'notes' => $route->getFullNoteInformation($this->getTranslator())]);
		} catch (Exception $ex) {
			return new JsonResponse(['success' => 0]);
		}
	}
	
	/**
	 * @Route("/{id}/api-update", name="edk_route_api_update")
	 */
	public function apiUpdateAction($id, Request $request)
	{
		try {
			$i = $request->get('i');
			$c = $request->get('c');
			if (empty($c)) {
				$c = null;
			}

			$route = $this->crudInfo->getRepository()->getItem($id);
			$route->saveEditableNote($this->get('database_connection'), $i, $c);
			return new JsonResponse(['success' => 1, 'note' => $route->getFullEditableNote($this->getTranslator(), $i)]);
		} catch (Exception $ex) {
			return new JsonResponse(['success' => 0]);
		}
	}
	
	/**
	 * @Route("/{id}/api-feed", name="edk_route_api_feed")
	 */
	public function ajaxFeedAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			return new JsonResponse($repository->getComments($item));
		} catch (Exception $ex) {
			return new JsonResponse(['status' => 0]);
		}
	}
	
	/**
	 * @Route("/{id}/api-post", name="edk_route_api_post")
	 */
	public function ajaxPostAction($id, Request $request)
	{
		try {
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);
			$message = $request->get('message');
			if (!empty($message)) {
				$item->post(new Message($this->getUser(), $message));
				$repository->update($item);
			}
			return new JsonResponse($repository->getComments($item));
		} catch (Exception $ex) {
			return new JsonResponse(['status' => 0]);
		}
	}
	
	/**
	 * @Route("/{id}/approve", name="edk_route_approve")
	 */
	public function approveAction($id, Request $request)
	{
		return $this->modifyRouteAfterApproval(
			$request, (int) $id, 'Do you want to approve the route "0"?', self::APPROVE_PAGE,
			function (EdkRouteRepository $repository, EdkRoute $item, User $user) {
				$repository->approve($item, $user);
			}
		);
	}

	/**
	 * @Route("/{id}/revoke", name="edk_route_revoke")
	 */
	public function revokeAction($id, Request $request)
	{
		return $this->modifyRouteAfterApproval(
			$request, (int) $id, 'Do you want to revoke the route "0"?', self::REVOKE_PAGE,
			function (EdkRouteRepository $repository, EdkRoute $item, User $user) {
				$repository->revoke($item, $user);
			}
		);
	}

	/**
	 * @Route("/{id}/approve/gps", name="edk_route_approve_gps")
	 */
	public function approveGpsAction($id, Request $request)
	{
		return $this->modifyRouteAfterApproval(
			$request, (int) $id, 'Do you want to approve GPS from route "0"?', self::APPROVE_GPS_PAGE,
			function (EdkRouteRepository $repository, EdkRoute $item, User $user) {
				$repository->approveGps($item, $user);
			}
		);
	}

	/**
	 * @Route("/{id}/revoke/gps", name="edk_route_revoke_gps")
	 */
	public function revokeGpsAction($id, Request $request)
	{
		return $this->modifyRouteAfterApproval(
			$request, (int) $id, 'Do you want to revoke GPS from route "0"?', self::REVOKE_GPS_PAGE,
			function (EdkRouteRepository $repository, EdkRoute $item, User $user) {
				$repository->revokeGps($item, $user);
			}
		);
	}

	/**
	 * @Route("/{id}/approve/description", name="edk_route_approve_description")
	 */
	public function approveDescriptionAction($id, Request $request)
	{
		return $this->modifyRouteAfterApproval(
			$request, (int) $id, 'Do you want to approve description from route "0"?', self::APPROVE_DESCRIPTION_PAGE,
			function (EdkRouteRepository $repository, EdkRoute $item, User $user) {
				$repository->approveDescription($item, $user);
			}
		);
	}

	/**
	 * @Route("/{id}/revoke/description", name="edk_route_revoke_description")
	 */
	public function revokeDescriptionAction($id, Request $request)
	{
		return $this->modifyRouteAfterApproval(
			$request, (int) $id, 'Do you want to revoke description from route "0"?', self::REVOKE_DESCRIPTION_PAGE,
			function (EdkRouteRepository $repository, EdkRoute $item, User $user) {
				$repository->revokeDescription($item, $user);
			}
		);
	}

	/**
	 * @Route("/{id}/approve/map", name="edk_route_approve_map")
	 */
	public function approveMapAction($id, Request $request)
	{
		return $this->modifyRouteAfterApproval(
			$request, (int) $id, 'Do you want to approve map from route "0"?', self::APPROVE_MAP_PAGE,
			function (EdkRouteRepository $repository, EdkRoute $item, User $user) {
				$repository->approveMap($item, $user);
			}
		);
	}

	/**
	 * @Route("/{id}/revoke/map", name="edk_route_revoke_map")
	 */
	public function revokeMapAction($id, Request $request)
	{
		return $this->modifyRouteAfterApproval(
			$request, (int) $id, 'Do you want to revoke map from route "0"?', self::REVOKE_MAP_PAGE,
			function (EdkRouteRepository $repository, EdkRoute $item, User $user) {
				$repository->revokeMap($item, $user);
			}
		);
	}

	private function modifyRouteAfterApproval(
		Request $request, int $id, string $question, string $routeName, callable $onSuccessCallback
	) : Response
	{
		try {
			/** @var EdkRouteRepository $repository */
			$repository = $this->get(self::REPOSITORY_NAME);
			$item = $repository->getItem($id);

			$user = $this->getUser();
			$question = new QuestionHelper($this->trans($question, [
				$item->getName(),
			], 'edk'));
			$question->onSuccess(function () use ($repository, $item, $user, $onSuccessCallback) {
				$onSuccessCallback($repository, $item, $user);
			});
			$arguments = [
				'id' => $item->getId(),
				'slug' => $this->getSlug(),
			];
			$question->respond($routeName, $arguments);
			$question->path($this->crudInfo->getInfoPage(), $arguments);
			$question->title($this->trans('EdkRoute: 0', [
				$item->getName(),
			]), $this->crudInfo->getPageSubtitle());
			$this
				->breadcrumbs()
				->link($item->getName(), $this->crudInfo->getInfoPage(), $arguments)
			;
			return $question->handleRequest($this, $request);
		} catch (Exception $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), [
				'slug' => $this->getSlug(),
			]);
		}
	}

	private function importRoutes(Area $source, Area $destination)
    {
        $this
            ->get(self::REPOSITORY_NAME)
           ->importFrom($source, $destination);

        return $this->showPageWithMessage('Import completed',
            $this->crudInfo->getIndexPage(), [
                'slug' => $this->getSlug(),
            ]);
    }
    /**
     * @Route("/{areaId}/import_area_routes", name="edk_area_route_import")
     * @Security("is_granted('MEMBEROF_PROJECT') and is_granted('PLACE_MANAGER')")
     */
    public function importRouteToArea($areaId, Request $request, Membership $membership)
    {
        try {
            $repository = $this->get(self::REPOSITORY_NAME);
            $sourceProject = $this->get('cantiga.core.repo.archived_project')
                ->getItem($membership->getPlace()->getParentProject()->getId());
            $areaRoutes = $repository->getRotesByProject($sourceProject);

            $form = $this->createForm(AreaRoutesImportForm::class, null, [
                'areasRoutes' => $areaRoutes,
            ]);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $fromAreaId = (int) $form
                    ->get('areaId')
                    ->getData();

                $destinationPlace = $this->getAreaRepository( $membership->getPlace())->getItem($areaId);
                $sourcePlace = $this->getAreaRepository($sourceProject)->getItem($fromAreaId);

                return $this->importRoutes($sourcePlace, $destinationPlace);
            }

            return $this->render('WioEdkBundle:EdkRoute:import.html.twig', [
                'form' => $form->createView(),
            ]);
        } catch (ModelException $exception) {
            return $this->showPageWithError(
                $exception->getMessage(),
                $this->crudInfo->getIndexPage(),
                ['slug' => $this->getSlug()]
            );
        }
    }

	/**
	 * @Route("/import", name="edk_route_import")
	 * @Security("is_granted('MEMBEROF_AREA') and is_granted('PLACE_MANAGER')")
	 */
	public function importAction(Request $request, Membership $membership)
	{
		try {
			$importer = $this->getImportService();
			$repository = $this->get(self::REPOSITORY_NAME);
			if (!$importer->isImportAvailable()) {
				return $this->showPageWithError($this->trans('ImportNotPossibleText'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
			}
			$question = $importer->getImportQuestion($this->crudInfo->getPageTitle(), 'ImportRoutesQuestionText');
			$question->path($this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
			$question->respond('edk_route_import', ['slug' => $this->getSlug()]);
			$question->onSuccess(function() use ($repository, $importer) {
				$repository->importFrom($importer->getImportSource(), $importer->getImportDestination());
			});
			$this->breadcrumbs()->link($this->trans('Import', [], 'general'), 'edk_route_import', ['slug' => $this->getSlug()]);
			return $question->handleRequest($this, $request);
		} catch(ModelException $exception) {
			return $this->showPageWithError($exception->getMessage(), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		}
	}
	
	public function getImportService(): ImporterInterface
	{
		return $this->get('cantiga.importer');
	}
	
	private function isArea(Membership $membership)
	{
		return ($membership->getPlace() instanceof Area);
	}
	private function isGroup(Membership $membership)
	{
		return ($membership->getPlace() instanceof Group);
	}

	private function findAreaRepository(Membership $membership)
	{
		$item = $membership->getPlace();
		if (!($item instanceof Area)) {
			return $this->getAreaRepository($item);
		}
		return null;
	}

	private function getAreaRepository(HierarchicalInterface $place)
    {
        $repository = $this->get('cantiga.core.repo.area_mgmt');
        $repository->setParentPlace($place);
        return $repository;
    }
}
