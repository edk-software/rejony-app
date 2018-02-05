<?php

namespace Cantiga\KnowledgeBundle\EventListener;

use Symfony\Component\Translation\TranslatorInterface;
use Cantiga\CoreBundle\Api\Workgroup;
use Cantiga\CoreBundle\Api\WorkItem;
use Cantiga\CoreBundle\Event\WorkspaceEvent;

class WorkspaceListener
{
    private $translator;

    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;

        return $this;
    }

    public function onAdminWorkspace(WorkspaceEvent $event)
    {
        $workspace = $event->getWorkspace();
        $workspace->addWorkgroup(new Workgroup('knowledge', $this->translator->trans('admin.menu.knowledge'), 'book',
            4));

        $workspace->addWorkItem('knowledge', new WorkItem('admin_faq_category_index',
            $this->translator->trans('admin.menu.faq')));
        $workspace->addWorkItem('knowledge', new WorkItem('admin_materials_category_index',
            $this->translator->trans('admin.menu.materials')));
    }
}
