<?php

namespace WIO\EdkBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class KernelListener
{
    private $workspaceListener;

    public function __construct(WorkspaceListener $workspaceListener)
    {
        $this->workspaceListener = $workspaceListener;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->workspaceListener->setRequest($event->getRequest());
    }
}
