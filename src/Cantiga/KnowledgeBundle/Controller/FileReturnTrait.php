<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\KnowledgeBundle\Entity\MaterialsFile;
use Cantiga\KnowledgeBundle\Repository\MaterialsFileRepository;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;

trait FileReturnTrait
{
    protected function returnFile(int $id, int $categoryId = null, int $level = null) : Response
    {
        /** @var MaterialsFileRepository $repository */
        $repository = $this->get('cantiga.knowledge.repo.materials_file');
        /** @var MaterialsFile $file */
        $file = $repository->findOneBy([
            'id' => $id,
        ]);
        if (!isset($file)) {
            throw new NotFoundHttpException(sprintf('There is no file with ID %d.', $id));
        }
        if (isset($level) && $level < $file->getLevel()) {
            throw new NotFoundHttpException('You don\'t have permissions to download this file.');
        }
        if (isset($categoryId)) {
            $category = $file->getCategory();
            if (!isset($category) || $categoryId != $category->getId()) {
                throw new NotFoundHttpException('There is no such file in selected materials category.');
            }
        }

        try {
            $filePath = $this->returnFilePath($file->getPath());
            $fileParts = explode('/', $file->getPath());
            $fileName = array_pop($fileParts);
            $response = new BinaryFileResponse($filePath);
            $mimeTypeGuesser = new FileinfoMimeTypeGuesser();
            $contentType = $mimeTypeGuesser->isSupported() ? $mimeTypeGuesser->guess($filePath) : 'text/plain';
            $response->headers->set('Content-Type', $contentType);
            $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);
        } catch (FileNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return $response;
    }

    protected function returnFilePath(string $relativePath = null) : string
    {
        /** @var Kernel $kernel */
        $kernel = $this->get('kernel');
        $rootDir = $kernel->getRootDir();
        $pathParts = [
            $rootDir,
            '..',
            trim($this->getParameter('cantiga_knowledge.materials_path'), '/'),
        ];
        if (!empty($relativePath)) {
            $pathParts[] = trim($relativePath, '/');
        }
        $filePath = implode('/', $pathParts);

        return $filePath;
    }

    abstract function get($id);

    abstract function getParameter($name);
}
