<?php

namespace Cantiga\KnowledgeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cantiga_knowledge');
        $rootNode
            ->children()
                ->scalarNode('materials_path')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
