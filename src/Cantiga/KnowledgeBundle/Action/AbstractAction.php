<?php

namespace Cantiga\KnowledgeBundle\Action;

use Cantiga\CoreBundle\Api\Actions\AbstractAction as BaseAction;
use Cantiga\KnowledgeBundle\Entity\EntityInterface;

class AbstractAction extends BaseAction
{
    private $urlArgs = [];

    protected function setUrlArgs(array $args)
    {
        $this->urlArgs = $args;

        return $this;
    }

    protected function slugify($args = [])
    {
        $args = array_merge($this->urlArgs, $args);
        if (!empty($this->slug)) {
            $args['slug'] = $this->slug;
        }

        return $args;
    }

    protected function getName(EntityInterface $item) : string
    {
        $nameProperty = 'get' . ucfirst($this->info->getItemNameProperty());
        $name = (string) $item->$nameProperty();

        return $name;
    }

    protected function setViewParam($name, $value) : self
    {
        $this->set($name, $value);

        return $this;
    }

    protected function addViewParams(array $params) : self
    {
        foreach ($params as $name => $value) {
            $this->setViewParam($name, $value);
        }

        return $this;
    }

    protected function importViewParamsFromCrudInfo() : self
    {
        $this
            ->setViewParam('pageTitle', $this->info->getPageTitle())
            ->setViewParam('pageSubtitle', $this->info->getPageSubtitle())
            ->setViewParam('indexPage', $this->info->getIndexPage())
            ->setViewParam('infoPage', $this->info->getInfoPage())
            ->setViewParam('insertPage', $this->info->getInsertPage())
            ->setViewParam('editPage', $this->info->getEditPage())
            ->setViewParam('removePage', $this->info->getRemovePage())
        ;

        return $this;
    }

    protected function getViewParams() : array
    {
        $viewParams = $this->getVars();

        return $viewParams;
    }
}
