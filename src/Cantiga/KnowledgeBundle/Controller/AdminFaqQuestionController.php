<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AdminPageController;
use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\KnowledgeBundle\Action\EditAction;
use Cantiga\KnowledgeBundle\Action\InfoAction;
use Cantiga\KnowledgeBundle\Action\InsertAction;
use Cantiga\KnowledgeBundle\Action\RemoveAction;
use Cantiga\KnowledgeBundle\Entity\FaqCategory;
use Cantiga\KnowledgeBundle\Entity\FaqQuestion;
use Cantiga\KnowledgeBundle\Form\AdminFaqQuestionForm;
use Cantiga\KnowledgeBundle\Repository\FaqCategoryRepository;
use Cantiga\KnowledgeBundle\Repository\FaqQuestionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/admin/faq/{categoryId}/questions")
 * @Security("has_role('ROLE_ADMIN')")
 */
class AdminFaqQuestionController extends AdminPageController
{
    const REPOSITORY_NAME = 'cantiga.knowledge.repo.faq_question';

    /** @var CRUDInfo */
    private $crudInfo;
    
    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->crudInfo = $this
            ->newCrudInfo(self::REPOSITORY_NAME)
            ->setTemplateLocation('CantigaKnowledgeBundle:AdminFaq/Question:')
            ->setItemNameProperty('topic')
            ->setPageTitle('admin.faq_questions.title')
            ->setPageSubtitle('admin.faq_questions.subtitle')
            ->setIndexPage('admin_faq_question_index')
            ->setInfoPage('admin_faq_question_info')
            ->setInsertPage('admin_faq_question_insert')
            ->setEditPage('admin_faq_question_edit')
            ->setRemovePage('admin_faq_question_remove')
            ->setRemoveQuestion('admin.remove_question')
        ;

        $this
            ->breadcrumbs()
            ->workgroup('knowledge')
            ->entryLink($this->trans('admin.faq_questions.title'), $this->crudInfo->getIndexPage(), [
                'categoryId' => $request->attributes->getInt('categoryId'),
            ])
        ;
    }
        
    /**
     * @Route("/", name="admin_faq_question_index")
     */
    public function indexAction(Request $request, int $categoryId) : Response
    {
        $category = $this->getCategoryIfExists($categoryId);
        /** @var FaqQuestionRepository $repository */
        $repository = $this->get(self::REPOSITORY_NAME);
        $dataTable = $repository->createDataTable();

        return $this->render('CantigaKnowledgeBundle:AdminFaq/Question:index.html.twig', [
            'category' => $category,
            'dataTable' => $dataTable,
            'locale' => $request->getLocale(),
            'pageSubtitle' => $this->crudInfo->getPageSubtitle(),
            'pageTitle' => $this->crudInfo->getPageTitle(),
        ]);
    }
    
    /**
     * @Route("/ajax-list", name="admin_faq_question_ajax_list")
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
        /** @var FaqQuestionRepository $repository */
        $repository = $this->get(self::REPOSITORY_NAME);
        $dataTable = $repository->createDataTable();
        $dataTable->process($request);

        return new JsonResponse($routes->process($repository->listData($dataTable, [
            'categoryId' => $categoryId,
        ])));
    }
    
    /**
     * @Route("/{id}/info", name="admin_faq_question_info")
     */
    public function infoAction(int $categoryId, int $id) : Response
    {
        $category = $this->getCategoryIfExists($categoryId);
        $action = new InfoAction($this->crudInfo);
        $action->set('levels', AdminFaqQuestionForm::getLevels());

        return $action->run($this, [
            'category' => $category,
            'id' => $id,
        ], [
            'categoryId' => $categoryId,
            'id' => $id,
        ]);
    }
     
    /**
     * @Route("/insert", name="admin_faq_question_insert")
     */
    public function insertAction(Request $request, int $categoryId) : Response
    {
        $category = $this->getCategoryIfExists($categoryId);
        $question = new FaqQuestion();
        $question->setCategory($category);
        $action = new InsertAction($this->crudInfo, $question, AdminFaqQuestionForm::class, [
            'categories' => $this->getCategories(),
        ]);
        $action->set('form_title', $this->trans('admin.faq_questions.insert'));

        return $action->run($this, $request, [
            'categoryId' => $categoryId,
        ], null, function (FaqQuestion $question, array $params) : array {
            $params['categoryId'] = $question
                ->getCategory()
                ->getId()
            ;

            return $params;
        });
    }
    
    /**
     * @Route("/{id}/edit", name="admin_faq_question_edit")
     */
    public function editAction(Request $request, int $categoryId, int $id) : Response
    {
        $category = $this->getCategoryIfExists($categoryId);
        $action = new EditAction($this->crudInfo, AdminFaqQuestionForm::class, [
            'categories' => $this->getCategories(),
        ]);
        $action->set('form_title', $this->trans('admin.faq_questions.edit'));

        return $action->run($this, $request, [
            'category' => $category,
            'id' => $id,
        ], [
            'categoryId' => $categoryId,
            'id' => $id,
        ], null, function (FaqQuestion $question, array $params) : array {
            $params['categoryId'] = $question
                ->getCategory()
                ->getId()
            ;

            return $params;
        });
    }
    
    /**
     * @Route("/{id}/remove", name="admin_faq_question_remove")
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
        ]);
    }

    private function getCategories() : array
    {
        /** @var FaqCategoryRepository $categoryRepository */
        $categoryRepository = $this->get(AdminFaqCategoryController::REPOSITORY_NAME);
        /** @var FaqCategory[] $categories */
        $categories = $categoryRepository->findBy([], [
            'name' => 'asc',
        ]);

        return $categories;
    }

    private function getCategoryIfExists(int $id) : FaqCategory
    {
        /** @var FaqCategoryRepository $categoryRepository */
        $categoryRepository = $this->get(AdminFaqCategoryController::REPOSITORY_NAME);
        /** @var FaqCategory|null $category */
        $category = $categoryRepository->findOneBy([
            'id' => $id,
        ]);
        if (empty($category)) {
            throw new NotFoundHttpException();
        }

        return $category;
    }
}
