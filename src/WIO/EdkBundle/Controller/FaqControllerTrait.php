<?php

namespace WIO\EdkBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * FAQ controller trait
 */
trait FaqControllerTrait
{
    /** @var int */
    private $level;
    
    /** @var string */
    private $indexRouteName;
    
    /** @var string */
    private $infoRouteName;
    
    protected function initializeParams(string $workgroup, int $level, string $indexRouteName, string $infoRouteName)
    {
        $this->level = $level;
        $this->indexRouteName = $indexRouteName;
        $this->infoRouteName = $infoRouteName;

        $this
            ->breadcrumbs()
            ->workgroup($workgroup)
            ->entryLink($this->trans('Frequently asking questions', [], 'pages'), $this->indexRouteName, [
                'slug' => $this->getSlug(),
            ])
        ;
    }

    public function renderIndex() : Response
    {
        $repository = $this->get('wio.edk.repo.faq_category');
        $categories = $repository->findBy([]);

        return $this->render('WioEdkBundle:Faq:index.html.twig', [
            'categories' => $categories,
            'categoryRouteName' => $this->infoRouteName,
            'level' => $this->level,
            'slug' => $this->getSlug(),
        ]);
    }

    public function renderInfo(int $id) : Response
    {
        $repository = $this->get('wio.edk.repo.faq_category');
        $categories = $repository->findBy([
            'id' => $id,
        ]);
        if (count($categories) != 1) {
            throw new NotFoundHttpException();
        }

        return $this->render('WioEdkBundle:Faq:index.html.twig', [
            'categories' => $categories,
            'level' => $this->level,
        ]);
    }
}
