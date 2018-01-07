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
        $workgroup = new Workgroup('knowledge', $this->translator->trans('admin.menu.knowledge'), 'book', 4);
        $workspace->addWorkgroup($workgroup);

        $workItem = new WorkItem('admin_faq_category_index', $this->translator->trans('admin.menu.faq_categories'));
        $workspace->addWorkItem('knowledge', $workItem);
    }
}
