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

use Cantiga\Components\Hierarchy\Entity\Member;
use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\CoreBundle\Controller\Traits\InformationTrait;
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Event\CantigaEvents;
use Cantiga\CoreBundle\Event\ContextMenuEvent;
use Cantiga\CoreBundle\Form\AreaForm;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Cantiga\CoreBundle\Entity\Message;

/**
 * @Route("/s/{slug}/areaSummary")
 * @Security("is_granted('PLACE_MEMBER') and (is_granted('MEMBEROF_PROJECT') or is_granted('MEMBEROF_GROUP'))")
 */
class AreaSummaryController extends WorkspaceController
{
	use InformationTrait;

	const REPOSITORY_NAME = 'wio.edk.repo.area_summary';
	const FILTER_NAME = 'cantiga.core.filter.area';
    const API_LIST_LINK = 'area_summary_api_list';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;
	private $customizableGroup;
	private $showCreateLink;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->crudInfo = $this->newCrudInfo(self::REPOSITORY_NAME)
			->setTemplateLocation('WioEdkBundle:AreaSummary:')
			->setItemNameProperty('name')
			->setPageTitle('Areas summary list')
			->setPageSubtitle('Summary')
			->setIndexPage('area_summary_index')
            ->setInfoPage('area_mgmt_info');

		$this->breadcrumbs()
			->workgroup('area')
			->entryLink($this->trans('Areas summary list', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
		$this->get(self::REPOSITORY_NAME)->setParentPlace($this->get('cantiga.user.membership.storage')->getMembership()->getPlace());
		$this->checkCapabilities();
	}

	/**
	 * @Route("/index", name="area_summary_index")
	 */
	public function indexAction(Request $request)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$filter = $this->get(self::FILTER_NAME);
		$filter->setTargetProject($this->getActiveProject());
		$filterForm = $filter->createForm($this->createFormBuilder($filter));
		$filterForm->handleRequest($request);

		$dataTable = $repository->createDataTable();
		$dataTable->filter($filter);
		return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', array(
			'pageTitle' => $this->crudInfo->getPageTitle(),
			'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
			'dataTable' => $dataTable,
			'locale' => $request->getLocale(),
			'ajaxListPage' => self::API_LIST_LINK,
			'filterForm' => $filterForm->createView(),
			'filter' => $filter,
			'showCreateLink' => $this->showCreateLink,
			'customizableGroup' => $this->customizableGroup
		));
	}

	/**
	 * @Route("/list", name="area_summary_api_list")
	 */
	public function apiListAction(Request $request)
	{
		$filter = $this->get(self::FILTER_NAME);
		$filter->setTargetProject($this->getActiveProject());
		$filterForm = $filter->createForm($this->createFormBuilder($filter));
		$filterForm->handleRequest($request);

		$routes = $this->dataRoutes()
			->link('info_link', $this->crudInfo->getInfoPage(), ['id' => '::id', 'slug' => $this->getSlug()]);

		$repository = $this->get(self::REPOSITORY_NAME);
		$dataTable = $repository->createDataTable();
		$dataTable->filter($filter);
		$dataTable->process($request);
		return new JsonResponse($routes->process($repository->listData($dataTable, $this->getTranslator())));
	}

	private function checkCapabilities()
	{
		if ($this->isGranted('MEMBEROF_PROJECT')) {
			$this->showCreateLink = true;
			$this->customizableGroup = true;
		} else {
			$this->showCreateLink = false;
			$this->customizableGroup = false;
		}
	}
}
