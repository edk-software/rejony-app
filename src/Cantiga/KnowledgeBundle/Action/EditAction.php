<?php

namespace Cantiga\KnowledgeBundle\Action;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Entity\EntityInterface;
use Cantiga\KnowledgeBundle\Repository\BaseRepository as Repository;
use Symfony\Component\HttpFoundation\Request;

class EditAction extends AbstractAction
{
    private $formType;
    private $options;

    public function __construct(CRUDInfo $crudInfo, string $formType = null, array $options = [])
    {
        $this->info = $crudInfo;
        $this->formType = $formType;
        $this->options = $options;
    }

    public function run(CantigaController $controller, Request $request, array $queryIds, array $routeIds,
        callable $beforeUpdate = null, callable $paramsFilter = null)
    {
        $this->setUrlArgs($routeIds);
        /** @var Repository $repository */
        $repository = $this->info->getRepository();
        /** @var EntityInterface|null $item */
        $item = $repository->findOneBy($queryIds);
        if (empty($item)) {
            return $this->onError($controller, $controller->trans('admin.item_not_found'));
        }

        $form = $controller->createForm($this->formType, $item, array_merge($this->options, [
            'action' => $controller->generateUrl($this->info->getEditPage(), $this->slugify()),
        ]));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (is_callable($beforeUpdate)) {
                $beforeUpdate($item);
            }
            $repository->update($item, true);
            $controller
                ->get('session')
                ->getFlashBag()
                ->add('info', $controller->trans($this->info->getItemUpdatedMessage(), [
                    $this->getName($item),
                ]))
            ;
            $parameters = is_callable($paramsFilter) ? $paramsFilter($item, $this->slugify()) : $this->slugify();
            $url = $controller->generateUrl($this->info->getInfoPage(), $parameters);

            return $controller->redirect($url);
        }

        $controllerBreadcrumbs = $controller->breadcrumbs();
        $controllerBreadcrumbs->link($this->getName($item), $this->info->getInfoPage(), $this->slugify());
        if ($this->hasBreadcrumbs()) {
            $controllerBreadcrumbs->item($this->breadcrumbs);
        } else {
            $controllerBreadcrumbs->link($controller->trans('admin.edit'), $this->info->getEditPage(),
                $this->slugify());
        }

        $this
            ->addViewParams($routeIds)
            ->importViewParamsFromCrudInfo()
            ->setViewParam('item', $item)
            ->setViewParam('form', $form->createView())
            ->setViewParam('user', $controller->getUser())
        ;

        return $controller->render($this->info->getTemplateLocation() . 'form.html.twig', $this->getViewParams());
    }
}
