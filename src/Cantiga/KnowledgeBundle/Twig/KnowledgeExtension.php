<?php

namespace Cantiga\KnowledgeBundle\Twig;

use Twig_Extension;
use Twig_SimpleFilter;

class KnowledgeExtension extends Twig_Extension
{
    public function getFilters() : array
    {
        $filters = [
            new Twig_SimpleFilter('file_name', [
                $this,
                'fileNameFilter',
            ]),
        ];

        return $filters;
    }

    public function fileNameFilter(string $filePath) : string
    {
        $pathParts = explode('/', str_replace('\\', '/', $filePath));
        $fileName = array_pop($pathParts);

        return $fileName;
    }

    public function getName() : string
    {
        return 'knowledge_extension';
    }
}
