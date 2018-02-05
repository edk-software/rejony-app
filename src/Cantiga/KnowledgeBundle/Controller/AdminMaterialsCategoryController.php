<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\KnowledgeBundle\Action\EditAction;
use Cantiga\KnowledgeBundle\Action\InfoAction;
use Cantiga\KnowledgeBundle\Action\InsertAction;
use Cantiga\KnowledgeBundle\Action\RemoveAction;
use Cantiga\KnowledgeBundle\Entity\MaterialsCategory;
use Cantiga\KnowledgeBundle\Form\AdminMaterialsCategoryForm;
use Cantiga\KnowledgeBundle\Repository\MaterialsCategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/admin/materials")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminMaterialsCategoryController extends AdminPageController
{
    use FileReturnTrait;

    const REPOSITORY_NAME = 'cantiga.knowledge.repo.materials_category';

    /** @var CRUDInfo */
    private $crudInfo;
    
    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->crudInfo = $this
            ->newCrudInfo(self::REPOSITORY_NAME)
            ->setTemplateLocation('CantigaKnowledgeBundle:AdminMaterials/Category:')
            ->setItemNameProperty('name')
            ->setPageTitle('admin.materials_categories.title')
            ->setPageSubtitle('admin.materials_categories.subtitle')
            ->setIndexPage('admin_materials_category_index')
            ->setInfoPage('admin_materials_category_info')
            ->setInsertPage('admin_materials_category_insert')
            ->setEditPage('admin_materials_category_edit')
            ->setRemovePage('admin_materials_category_remove')
            ->setRemoveQuestion('admin.remove_question')
        ;
        
        $this
            ->breadcrumbs()
            ->workgroup('knowledge')
            ->entryLink($this->trans('admin.materials_categories.title'), $this->crudInfo->getIndexPage())
        ;
    }
        
    /**
     * @Route("/", name="admin_materials_category_index")
     */
    public function indexAction(Request $request) : Response
    {
        /** @var MaterialsCategoryRepository $repository */
        $repository = $this->get(self::REPOSITORY_NAME);
        $dataTable = $repository->createDataTable();

        return $this->render('CantigaKnowledgeBundle:AdminMaterials/Category:index.html.twig', [
            'dataTable' => $dataTable,
            'locale' => $request->getLocale(),
            'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
            'pageTitle' => $this->crudInfo->getPageTitle(),
        ]);
    }
    
    /**
     * @Route("/ajax-list", name="admin_materials_category_ajax_list")
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
            ->link('files_link', 'admin_materials_file_index', [
                'categoryId' => '::id',
            ])
        ;
        /** @var MaterialsCategoryRepository $repository */
        $repository = $this->get(self::REPOSITORY_NAME);
        $dataTable = $repository->createDataTable();
        $dataTable->process($request);

        return new JsonResponse($routes->process($repository->listData($dataTable)));
    }
    
    /**
     * @Route("/{id}/info", name="admin_materials_category_info")
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
     * @Route("/insert", name="admin_materials_category_insert")
     */
    public function insertAction(Request $request) : Response
    {
        $action = new InsertAction($this->crudInfo, new MaterialsCategory(), AdminMaterialsCategoryForm::class);
        $action->set('form_title', $this->trans('admin.materials_categories.insert'));

        return $action->run($this, $request);
    }
    
    /**
     * @Route("/{id}/edit", name="admin_materials_category_edit")
     */
    public function editAction(Request $request, int $id) : Response
    {
        $action = new EditAction($this->crudInfo, AdminMaterialsCategoryForm::class);
        $action->set('form_title', $this->trans('admin.materials_categories.edit'));

        return $action->run($this, $request, [
            'id' => $id,
        ], [
            'id' => $id,
        ]);
    }
    
    /**
     * @Route("/{id}/remove", name="admin_materials_category_remove")
     */
    public function removeAction(Request $request, int $id) : Response
    {
        $action = new RemoveAction($this->crudInfo);

        return $action->run($this, $request, [
            'id' => $id,
        ], [
            'id' => $id,
        ], function (MaterialsCategory $category) {
            foreach ($category->getFiles() as $file) {
                $fs = new Filesystem();
                $fs->remove([
                    $this->returnFilePath($file->getPath()),
                ]);
                // @TODO: Remove this method when Doctrine ORM mapping will be ready
                $this
                    ->get('cantiga.milestone.repo.status')
                    ->removeMilestoneMaterialsByMaterial($file->getId())
                ;
            }
        });
    }
}
