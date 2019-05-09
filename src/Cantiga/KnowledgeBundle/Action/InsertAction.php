<?php

namespace Cantiga\KnowledgeBundle\Action;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Entity\EntityInterface;
use Cantiga\KnowledgeBundle\Repository\BaseRepository as Repository;
use Symfony\Component\HttpFoundation\Request;

class InsertAction extends AbstractAction
{
    private $entity;
    private $formType;
    private $options;

    public function __construct(CRUDInfo $crudInfo, EntityInterface $entity, $formType = null,
        array $options = [])
    {
        $this->info = $crudInfo;
        $this->entity = $entity;
        $this->formType = $formType;
        $this->options = $options;
    }

    public function run(CantigaController $controller, Request $request, array $routeIds = [],
        callable $beforeInsert = null, callable $paramsFilter = null)
    {
        $this->setUrlArgs($routeIds);
        /** @var Repository $repository */
        $repository = $this->info->getRepository();
        $item = $this->entity;

        $form = $controller->createForm($this->formType, $item, array_merge($this->options, [
            'action' => $controller->generateUrl($this->info->getInsertPage(), $this->slugify()),
        ]));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (is_callable($beforeInsert)) {
                $beforeInsert($item, $form);
            }
            if ($form->isValid()) {
                $repository->insert($item, true);
                $controller
                    ->get('session')
                    ->getFlashBag()
                    ->add('info', $controller->trans($this->info->getItemCreatedMessage(), [
                        $this->getName($item),
                    ]))
                ;
                $routeIds['id'] = $item->getId();
                $this->setUrlArgs($routeIds);
                $parameters = is_callable($paramsFilter) ? $paramsFilter($item, $this->slugify()) : $this->slugify();
                $url = $controller->generateUrl($this->info->getInfoPage(), $parameters);
                return $controller->redirect($url);
            }
        }

        $controllerBreadcrumbs = $controller->breadcrumbs();
        if ($this->hasBreadcrumbs()) {
            $controllerBreadcrumbs->item($this->breadcrumbs);
        } else {
            $controllerBreadcrumbs->link($controller->trans('admin.insert'), $this->info->getInsertPage(),
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
