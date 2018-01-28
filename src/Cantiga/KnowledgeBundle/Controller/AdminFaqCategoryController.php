<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\KnowledgeBundle\Action\EditAction;
use Cantiga\KnowledgeBundle\Action\InfoAction;
use Cantiga\KnowledgeBundle\Action\InsertAction;
use Cantiga\KnowledgeBundle\Action\RemoveAction;
use Cantiga\KnowledgeBundle\Entity\FaqCategory;
use Cantiga\KnowledgeBundle\Form\AdminFaqCategoryForm;
use Cantiga\KnowledgeBundle\Repository\FaqCategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/admin/faq")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminFaqCategoryController extends AdminPageController
{
    const REPOSITORY_NAME = 'cantiga.knowledge.repo.faq_category';

    /** @var CRUDInfo */
    private $crudInfo;
    
    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->crudInfo = $this
            ->newCrudInfo(self::REPOSITORY_NAME)
            ->setTemplateLocation('CantigaKnowledgeBundle:AdminFaq/Category:')
            ->setItemNameProperty('name')
            ->setPageTitle('admin.faq_categories.title')
            ->setPageSubtitle('admin.faq_categories.subtitle')
            ->setIndexPage('admin_faq_category_index')
            ->setInfoPage('admin_faq_category_info')
            ->setInsertPage('admin_faq_category_insert')
            ->setEditPage('admin_faq_category_edit')
            ->setRemovePage('admin_faq_category_remove')
            ->setRemoveQuestion('admin.remove_question')
        ;
        
        $this
            ->breadcrumbs()
            ->workgroup('knowledge')
            ->entryLink($this->trans('admin.faq_categories.title'), $this->crudInfo->getIndexPage())
        ;
    }
        
    /**
     * @Route("/", name="admin_faq_category_index")
     */
    public function indexAction(Request $request) : Response
    {
        /** @var FaqCategoryRepository $repository */
        $repository = $this->get(self::REPOSITORY_NAME);
        $dataTable = $repository->createDataTable();

        return $this->render('CantigaKnowledgeBundle:AdminFaq/Category:index.html.twig', [
            'dataTable' => $dataTable,
            'locale' => $request->getLocale(),
            'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
            'pageTitle' => $this->crudInfo->getPageTitle(),
        ]);
    }
    
    /**
     * @Route("/ajax-list", name="admin_faq_category_ajax_list")
     */
    public function ajaxListAction(Request $request) : JsonResponse
    {
        $routes = $this
            ->dataRoutes()
            ->link('info_link', $this->crudInfo->getInfoPage(), [
                'id' => '::id',
            ])
            ->link('edit_link', $this->crudInfo->getEditPage(), [
                'id' => '::id',
            ])
            ->link('remove_link', $this->crudInfo->getRemovePage(), [
                'id' => '::id',
            ])
            ->link('questions_link', 'admin_faq_question_index', [
                'categoryId' => '::id',
            ])
        ;
        /** @var FaqCategoryRepository $repository */
        $repository = $this->get(self::REPOSITORY_NAME);
        $dataTable = $repository->createDataTable();
        $dataTable->process($request);

        return new JsonResponse($routes->process($repository->listData($dataTable)));
    }
    
    /**
     * @Route("/{id}/info", name="admin_faq_category_info")
     */
    public function infoAction(int $id) : Response
    {
        $action = new InfoAction($this->crudInfo);

        return $action->run($this, [
            'id' => $id,
        ], [
            'id' => $id,
        ]);
    }
     
    /**
     * @Route("/insert", name="admin_faq_category_insert")
     */
    public function insertAction(Request $request) : Response
    {
        $action = new InsertAction($this->crudInfo, new FaqCategory(), AdminFaqCategoryForm::class);
        $action->set('form_title', $this->trans('admin.faq_categories.insert'));

        return $action->run($this, $request);
    }
    
    /**
     * @Route("/{id}/edit", name="admin_faq_category_edit")
     */
    public function editAction(Request $request, int $id) : Response
    {
        $action = new EditAction($this->crudInfo, AdminFaqCategoryForm::class);
        $action->set('form_title', $this->trans('admin.faq_categories.edit'));

        return $action->run($this, $request, [
            'id' => $id,
        ], [
            'id' => $id,
        ]);
    }
    
    /**
     * @Route("/{id}/remove", name="admin_faq_category_remove")
     */
    public function removeAction(Request $request, int $id) : Response
    {
        $action = new RemoveAction($this->crudInfo);

        return $action->run($this, $request, [
            'id' => $id,
        ], [
            'id' => $id,
        ]);
    }
}
