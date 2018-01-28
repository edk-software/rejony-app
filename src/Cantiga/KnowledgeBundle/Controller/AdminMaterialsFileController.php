<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Controller\Traits\SlugifyTrait;
use Cantiga\KnowledgeBundle\Action\EditAction;
use Cantiga\KnowledgeBundle\Action\InfoAction;
use Cantiga\KnowledgeBundle\Action\InsertAction;
use Cantiga\KnowledgeBundle\Action\RemoveAction;
use Cantiga\KnowledgeBundle\Entity\MaterialsCategory;
use Cantiga\KnowledgeBundle\Entity\MaterialsFile;
use Cantiga\KnowledgeBundle\Form\AdminMaterialsFileForm;
use Cantiga\KnowledgeBundle\Repository\MaterialsCategoryRepository;
use Cantiga\KnowledgeBundle\Repository\MaterialsFileRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/admin/materials/{categoryId}/files")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminMaterialsFileController extends AdminPageController
{
    use FileReturnTrait;
    use SlugifyTrait;

    const REPOSITORY_NAME = 'cantiga.knowledge.repo.materials_file';

    /** @var CRUDInfo */
    private $crudInfo;
    
    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->crudInfo = $this
            ->newCrudInfo(self::REPOSITORY_NAME)
            ->setTemplateLocation('CantigaKnowledgeBundle:AdminMaterials/File:')
            ->setItemNameProperty('name')
            ->setPageTitle('admin.materials_files.title')
            ->setPageSubtitle('admin.materials_files.subtitle')
            ->setIndexPage('admin_materials_file_index')
            ->setInfoPage('admin_materials_file_info')
            ->setInsertPage('admin_materials_file_insert')
            ->setEditPage('admin_materials_file_edit')
            ->setRemovePage('admin_materials_file_remove')
            ->setRemoveQuestion('admin.remove_question')
        ;

        $this
            ->breadcrumbs()
            ->workgroup('knowledge')
            ->entryLink($this->trans('admin.materials_files.title'), $this->crudInfo->getIndexPage(), [
                'categoryId' => $request->attributes->getInt('categoryId'),
            ])
        ;
    }
        
    /**
     * @Route("/", name="admin_materials_file_index")
     */
    public function indexAction(Request $request, int $categoryId) : Response
    {
        $category = $this->getCategoryIfExists($categoryId);
        /** @var MaterialsFileRepository $repository */
        $repository = $this->get(self::REPOSITORY_NAME);
        $dataTable = $repository->createDataTable();

        return $this->render('CantigaKnowledgeBundle:AdminMaterials/File:index.html.twig', [
            'category' => $category,
            'dataTable' => $dataTable,
            'locale' => $request->getLocale(),
            'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
            'pageTitle' => $this->crudInfo->getPageTitle(),
        ]);
    }
    
    /**
     * @Route("/ajax-list", name="admin_materials_file_ajax_list")
     */
    public function ajaxListAction(Request $request, int $categoryId) : JsonResponse
    {
        $this->getCategoryIfExists($categoryId);
        $routes = $this
            ->dataRoutes()
            ->link('info_link', $this->crudInfo->getInfoPage(), [
                'categoryId' => '::categoryId',
                'id' => '::id',
            ])
            ->link('edit_link', $this->crudInfo->getEditPage(), [
                'categoryId' => '::categoryId',
                'id' => '::id',
            ])
            ->link('remove_link', $this->crudInfo->getRemovePage(), [
                'categoryId' => '::categoryId',
                'id' => '::id',
            ])
        ;
        /** @var MaterialsFileRepository $repository */
        $repository = $this->get(self::REPOSITORY_NAME);
        $dataTable = $repository->createDataTable();
        $dataTable->process($request);

        return new JsonResponse($routes->process($repository->listData($dataTable, [
            'categoryId' => $categoryId,
        ])));
    }
    
    /**
     * @Route("/{id}/info", name="admin_materials_file_info")
     */
    public function infoAction(int $categoryId, int $id) : Response
    {
        $category = $this->getCategoryIfExists($categoryId);
        $action = new InfoAction($this->crudInfo);
        $action->set('levels', AdminMaterialsFileForm::getLevels());

        return $action->run($this, [
            'category' => $category,
            'id' => $id,
        ], [
            'categoryId' => $categoryId,
            'id' => $id,
        ]);
    }
     
    /**
     * @Route("/insert", name="admin_materials_file_insert")
     */
    public function insertAction(Request $request, int $categoryId) : Response
    {
        $category = $this->getCategoryIfExists($categoryId);
        $file = new MaterialsFile();
        $file->setCategory($category);
        $action = new InsertAction($this->crudInfo, $file, AdminMaterialsFileForm::class, [
            'categories' => $this->getCategories(),
            'isNew' => true,
            'validation_groups' => [
                'add',
            ],
        ]);
        $action->set('form_title', $this->trans('admin.materials_files.insert'));

        return $action->run($this, $request, [
            'categoryId' => $categoryId,
        ], function (MaterialsFile $file) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $file->getPath();
            $fileName = $this->createFileName($uploadedFile, $file->getName());
            $uploadedFile->move(
                $this->returnFilePath(),
                $fileName
            );
            $file->setPath($fileName);
        }, function (MaterialsFile $file, array $params) : array {
            $params['categoryId'] = $file
                ->getCategory()
                ->getId()
            ;

            return $params;
        });
    }
    
    /**
     * @Route("/{id}/edit", name="admin_materials_file_edit")
     */
    public function editAction(Request $request, int $categoryId, int $id) : Response
    {
        $category = $this->getCategoryIfExists($categoryId);
        $action = new EditAction($this->crudInfo, AdminMaterialsFileForm::class, [
            'categories' => $this->getCategories(),
            'isNew' => false,
            'validation_groups' => [
                'edit',
            ],
        ]);
        $action->set('form_title', $this->trans('admin.materials_files.edit'));

        return $action->run($this, $request, [
            'category' => $category,
            'id' => $id,
        ], [
            'categoryId' => $categoryId,
            'id' => $id,
        ], null, function (MaterialsFile $file, array $params) : array {
            $params['categoryId'] = $file
                ->getCategory()
                ->getId()
            ;

            return $params;
        });
    }
    
    /**
     * @Route("/{id}/remove", name="admin_materials_file_remove")
     */
    public function removeAction(Request $request, int $categoryId, int $id) : Response
    {
        $category = $this->getCategoryIfExists($categoryId);
        $action = new RemoveAction($this->crudInfo);

        return $action->run($this, $request, [
            'category' => $category,
            'id' => $id,
        ], [
            'categoryId' => $categoryId,
            'id' => $id,
        ], function (MaterialsFile $file) {
            $fs = new Filesystem();
            $fs->remove([
                $this->returnFilePath($file->getPath()),
            ]);
        });
    }

    /**
     * @Route("/{id}/get", name="admin_materials_file_get")
     */
    public function getAction(int $categoryId, int $id) : Response
    {
        return $this->returnFile($id, $categoryId);
    }

    private function getCategories() : array
    {
        /** @var MaterialsCategoryRepository $categoryRepository */
        $categoryRepository = $this->get(AdminMaterialsCategoryController::REPOSITORY_NAME);
        /** @var MaterialsCategory[] $categories */
        $categories = $categoryRepository->findBy([], [
            'name' => 'asc',
        ]);

        return $categories;
    }

    private function getCategoryIfExists(int $id) : MaterialsCategory
    {
        /** @var MaterialsCategoryRepository $categoryRepository */
        $categoryRepository = $this->get(AdminMaterialsCategoryController::REPOSITORY_NAME);
        /** @var MaterialsCategory|null $category */
        $category = $categoryRepository->findOneBy([
            'id' => $id,
        ]);
        if (empty($category)) {
            throw new NotFoundHttpException();
        }

        return $category;
    }

    private function createFileName(UploadedFile $uploadedFile, string $name) : string {
        $counter = 0;
        $nameSlug = $this->slugify($name);
        $extension = $uploadedFile->guessExtension();
        do {
            $counter++;
            $fileName = $nameSlug . ($counter > 1 ? '-' . $counter : '') . '.' . $extension;
        } while (file_exists($this->returnFilePath($fileName)));

        return $fileName;
    }
}