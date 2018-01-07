<?php

namespace Cantiga\KnowledgeBundle\Action;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\KnowledgeBundle\Entity\EntityInterface;
use Cantiga\KnowledgeBundle\Repository\BaseRepository as Repository;

class InfoAction extends AbstractAction
{
    public function __construct(CRUDInfo $crudInfo)
    {
        $this->info = $crudInfo;
    }

    public function run(CantigaController $controller, array $queryIds, array $routeIds, $customDataGenerator = null)
    {
        $this->setUrlArgs($routeIds);
        /** @var Repository $repository */
        $repository = $this->info->getRepository();
        /** @var EntityInterface|null $item */
        $item = $repository->findOneBy($queryIds);
        if (empty($item)) {
            return $this->onError($controller, $controller->trans('admin.item_not_found'));
        }

        $this
            ->addViewParams($routeIds)
            ->importViewParamsFromCrudInfo()
            ->setViewParam('item', $item)
            ->setViewParam('name', $this->getName($item))
            ->setViewParam('custom', is_callable($customDataGenerator) ? $customDataGenerator($item) : null)
        ;

        $controllerBreadcrumbs = $controller->breadcrumbs();
        $controllerBreadcrumbs->link($this->getName($item), $this->info->getInfoPage(), $this->slugify());

        return $controller->render($this->info->getTemplateLocation() . $this->info->getInfoTemplate(),
            $this->getViewParams());
    }
}
