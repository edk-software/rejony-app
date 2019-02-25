<?php

namespace Cantiga\UserBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Actions\EditAction;
use Cantiga\CoreBundle\Api\Actions\InfoAction;
use Cantiga\CoreBundle\Api\Actions\InsertAction;
use Cantiga\CoreBundle\Api\Actions\RemoveAction;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\UserBundle\Entity\Agreement;
use Cantiga\UserBundle\Form\AgreementForm;
use Cantiga\UserBundle\Repository\AgreementRepository;
use Cantiga\UserBundle\Repository\AgreementSignatureRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/agreements")
 * @Security("is_granted('PLACE_MANAGER') and is_granted('MEMBEROF_PROJECT')")
 */
class ProjectAgreementsController extends ProjectPageController
{
    const REPOSITORY_NAME = 'cantiga.user.repo.agreement';

    /** @var CRUDInfo */
    private $crudInfo;

    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $repository = $this->get(self::REPOSITORY_NAME);
        $this->crudInfo = $this->newCrudInfo($repository)
            ->setTemplateLocation('CantigaUserBundle:Agreement:')
            ->setItemNameProperty('title')
            ->setPageTitle('Agreements')
            ->setPageSubtitle('Manage project\'s agreements')
            ->setIndexPage('project_agreements_index')
            ->setInfoPage('project_agreements_info')
            ->setInsertPage('project_agreements_insert')
            ->setEditPage('project_agreements_edit')
            ->setRemovePage('project_agreements_remove')
            ->setItemCreatedMessage('The agreement \'0\' has been created.')
            ->setItemUpdatedMessage('The agreement \'0\' has been updated.')
            ->setItemRemovedMessage('The agreement \'0\' has been removed.')
            ->setRemoveQuestion('Do you really want to remove the agreement \'0\'?');

        $this->breadcrumbs()
            ->workgroup('manage')
            ->entryLink(
                $this->trans('Agreements', [], 'pages'),
                $this->crudInfo->getIndexPage(),
                [ 'slug' => $this->getSlug() ]
            );
    }

	/**
	 * @Route("/index", name="project_agreements_index")
	 */
	public function indexAction(Request $request)
	{
	    /** @var AgreementRepository $repository */
        $repository = $this->crudInfo->getRepository();
        $dataTable = $repository->createDataTable();

        return $this->render($this->crudInfo->getTemplateLocation() . 'index.html.twig', [
            'pageTitle' => $this->crudInfo->getPageTitle(),
            'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
            'dataTable' => $dataTable,
            'locale' => $request->getLocale(),
            'ajaxListPage' => 'project_agreements_ajax_list',
            'insertPage' => $this->crudInfo->getInsertPage()
        ]);
	}

    /**
     * @Route("/ajax-list", name="project_agreements_ajax_list")
     */
    public function ajaxListAction(Request $request)
    {
        $routes = $this->dataRoutes()
            ->link('info_link', $this->crudInfo->getInfoPage(), [
                'id' => '::id', 'slug' => $this->getSlug(),
            ])
            ->link('edit_link', $this->crudInfo->getEditPage(), [
                'id' => '::id', 'slug' => $this->getSlug(),
            ])
            ->link('remove_link', $this->crudInfo->getRemovePage(), [
                'id' => '::id', 'slug' => $this->getSlug(),
            ])
        ;
        /** @var AgreementRepository $repository */
        $repository = $this->crudInfo->getRepository();
        $dataTable = $repository->createDataTable();
        $dataTable->process($request);

        return new JsonResponse($routes->process($repository->listData($dataTable, $this->getActiveProject())));
    }

    /**
     * @Route("/{id}/info", name="project_agreements_info")
     */
    public function infoAction($id)
    {
        $action = new InfoAction($this->crudInfo);
        $action->slug($this->getSlug());

        return $action->run($this, $id);
    }

    /**
     * @Route("/insert", name="project_agreements_insert")
     */
    public function insertAction(Request $request)
    {
        $entity = new Agreement();
        $entity
            ->setProjectId($this->getActiveProject()->getId())
            ->setCreatedBy($this->getUser()->getId())
        ;

        $action = new InsertAction($this->crudInfo, $entity, AgreementForm::class);
        $action->slug($this->getSlug());

        return $action->run($this, $request);
    }

    /**
     * @Route("/{id}/edit", name="project_agreements_edit")
     */
    public function editAction($id, Request $request)
    {
        $action = new EditAction($this->crudInfo, AgreementForm::class);
        $action->slug($this->getSlug());
        $action->beforeEdit(function (Agreement $entity) {
            $entity->setUpdatedBy($this->getUser()->getId());
        });

        return $action->run($this, $id, $request);
    }

    /**
     * @Route("/{id}/remove", name="project_agreements_remove")
     */
    public function removeAction($id, Request $request)
    {
        $action = new RemoveAction($this->crudInfo);
        $action->slug($this->getSlug());
        $action->afterRemove(function () use ($id) {
            /** @var AgreementSignatureRepository $repository */
            $repository = $this->get('cantiga.user.repo.agreement_signature');
            $repository->deleteByAgreementId($id);
        });

        return $action->run($this, $id, $request);
    }
}
