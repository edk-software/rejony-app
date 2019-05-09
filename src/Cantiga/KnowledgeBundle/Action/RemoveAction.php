<?php

namespace Cantiga\KnowledgeBundle\Action;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Entity\EntityInterface;
use Cantiga\KnowledgeBundle\Repository\BaseRepository as Repository;
use Symfony\Component\HttpFoundation\Request;

class RemoveAction extends AbstractAction
{
    const CONFIRM_NO = 'no';
    const CONFIRM_YES = 'yes';

    public function __construct(CRUDInfo $crudInfo)
    {
        $this->info = $crudInfo;
    }

    public function run(CantigaController $controller, Request $request, array $queryIds, array $routeIds,
        callable $beforeRemove = null)
    {
        $this->setUrlArgs($routeIds);
        /** @var Repository $repository */
        $repository = $this->info->getRepository();
        /** @var EntityInterface|null $item */
        $item = $repository->findOneBy($queryIds);
        if (empty($item)) {
            return $this->onError($controller, $controller->trans('admin.item_not_found'));
        }

        $answer = $request->query->get('answer');
        switch ($answer) {
            case self::CONFIRM_YES:
                if (is_callable($beforeRemove)) {
                    $beforeRemove($item);
                }
                $repository->delete($item, true);
                return $this->onSuccess($controller, $controller->trans($this->info->getItemRemovedMessage(), [
                    $this->getName($item),
                ]));

            case self::CONFIRM_NO:
                return $this->toIndexPage($controller);

            default:
                $controllerBreadcrumbs = $controller->breadcrumbs();
                $controllerBreadcrumbs->link($this->getName($item), $this->info->getInfoPage(), $this->slugify());
                $controllerBreadcrumbs->link($controller->trans('admin.remove'),
                    $this->info->getRemovePage(), $this->slugify());

                $this
                    ->addViewParams($routeIds)
                    ->importViewParamsFromCrudInfo()
                    ->setViewParam('questionTitle', $controller->trans($this->info->getRemoveQuestionTitle()))
                    ->setViewParam('question', $controller->trans($this->info->getRemoveQuestion(), [
                        $this->getName($item),
                    ]))
                    ->setViewParam('successPath', $controller->generateUrl($this->info->getRemovePage(),
                        $this->slugify([
                        'answer' => self::CONFIRM_YES,
                    ])))
                    ->setViewParam('failurePath', $controller->generateUrl($this->info->getRemovePage(),
                        $this->slugify([
                        'answer' => self::CONFIRM_NO,
                    ])))
                    ->setViewParam('successBtn', $controller->trans('admin.yes'))
                    ->setViewParam('failureBtn', $controller->trans('admin.cancel'))
                ;

                return $controller->render('CantigaCoreBundle:layout:question.html.twig', $this->getViewParams());
        }
    }
}
