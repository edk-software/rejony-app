<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\KnowledgeBundle\Entity\MaterialsFile as File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait MaterialsControllerTrait
{
    use FileReturnTrait;

    /** @var int */
    private $level;

    /** @var string */
    private $indexRouteName;

    /** @var string */
    private $infoRouteName;

    /** @var string */
    private $fileRouteName;

    protected function initializeParams(int $level, string $indexRouteName, string $infoRouteName,
        string $fileRouteName)
    {
        $this->level = $level;
        $this->indexRouteName = $indexRouteName;
        $this->infoRouteName = $infoRouteName;
        $this->fileRouteName = $fileRouteName;

        $this
            ->breadcrumbs()
            ->workgroup('knowledge')
            ->entryLink($this->trans('materials.title'), $this->indexRouteName, [
                'slug' => $this->getSlug(),
            ])
        ;
    }

    protected function renderIndex() : Response
    {
        $repository = $this->get('cantiga.knowledge.repo.materials_category');
        $categories = $repository->findBy([]);

        return $this->render('CantigaKnowledgeBundle:Materials:index.html.twig', [
            'categories' => $categories,
            'categoryRouteName' => $this->infoRouteName,
            'fileRouteName' => $this->fileRouteName,
            'level' => $this->level,
            'slug' => $this->getSlug(),
        ]);
    }

    protected function renderInfo(int $id) : Response
    {
        $repository = $this->get('cantiga.knowledge.repo.materials_category');
        $categories = $repository->findBy([
            'id' => $id,
        ]);
        if (count($categories) != 1) {
            throw new NotFoundHttpException();
        }

        return $this->render('CantigaKnowledgeBundle:Materials:index.html.twig', [
            'categories' => $categories,
            'fileRouteName' => $this->fileRouteName,
            'level' => $this->level,
            'slug' => $this->getSlug(),
        ]);
    }
}
