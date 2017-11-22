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
            ->entryLink($this->trans('Help', [], 'pages'), $this->indexRouteName, [
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
            'level' => $this->level,
            'questionRouteName' => $this->infoRouteName,
            'slug' => $this->getSlug(),
        ]);
    }

    public function renderInfo(int $id) : Response
    {
        $repository = $this->get('wio.edk.repo.faq_question');
        $question = $repository->findOneBy([
            'id' => $id,
        ]);
        if (!isset($question) || $question->getLevel() > $this->level) {
            throw new NotFoundHttpException();
        }

        return $this->render('WioEdkBundle:Faq:info.html.twig', [
            'question' => $question,
        ]);
    }
}
