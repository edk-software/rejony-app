<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait FaqControllerTrait
{
    /** @var int */
    private $level;
    
    /** @var string */
    private $indexRouteName;
    
    /** @var string */
    private $infoRouteName;
    
    protected function initializeParams(int $level, string $indexRouteName, string $infoRouteName)
    {
        $this->level = $level;
        $this->indexRouteName = $indexRouteName;
        $this->infoRouteName = $infoRouteName;

        $this
            ->breadcrumbs()
            ->workgroup('knowledge')
            ->entryLink($this->trans('faq.title'), $this->indexRouteName, [
                'slug' => $this->getSlug(),
            ])
        ;
    }

    protected function renderIndex() : Response
    {
        $repository = $this->get('cantiga.knowledge.repo.faq_category');
        $categories = $repository->findBy([]);

        return $this->render('CantigaKnowledgeBundle:Faq:index.html.twig', [
            'categories' => $categories,
            'categoryRouteName' => $this->infoRouteName,
            'level' => $this->level,
            'slug' => $this->getSlug(),
        ]);
    }

    protected function renderInfo(int $id) : Response
    {
        $repository = $this->get('cantiga.knowledge.repo.faq_category');
        $categories = $repository->findBy([
            'id' => $id,
        ]);
        if (count($categories) != 1) {
            throw new NotFoundHttpException();
        }

        return $this->render('CantigaKnowledgeBundle:Faq:index.html.twig', [
            'categories' => $categories,
            'level' => $this->level,
        ]);
    }
}
