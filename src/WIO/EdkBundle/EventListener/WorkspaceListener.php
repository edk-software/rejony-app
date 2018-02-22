<?php
/*
 * This file is part of Cantiga Project. Copyright 2016 Cantiga contributors.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace WIO\EdkBundle\EventListener;

use Cantiga\CoreBundle\Api\Workgroup;
use Cantiga\CoreBundle\Api\WorkItem;
use Cantiga\CoreBundle\Event\ContextMenuEvent;
use Cantiga\CoreBundle\Event\ShowHelpEvent;
use Cantiga\CoreBundle\Event\WorkspaceEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class WorkspaceListener
{
	/**
	 * @var AuthorizationCheckerInterface 
	 */
	private $authChecker;
	private $request;
	private $router;
    private $translator;
	
	public function __construct(AuthorizationCheckerInterface $authChecker, Router $router,
        TranslatorInterface $translator)
	{
		$this->authChecker = $authChecker;
        $this->router = $router;
        $this->translator = $translator;
	}

    public function setRequest(Request $request) : self
    {
        $this->request = $request;
        
        return $this;
    }

	public function onProjectWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('edk')) {
			$workspace->addWorkgroup(new Workgroup('participants', 'Participants', 'male', 6));
			$workspace->addWorkgroup(new Workgroup('routes', 'Routes', 'map-signs', 4));

            $workspace->addWorkItem('knowledge', new WorkItem('project_course_summary_index', 'Course results'));
			$workspace->addWorkItem('knowledge', new WorkItem('project_materials_index', 'Materials database'));
			$workspace->addWorkItem('knowledge', new WorkItem('project_faq_index', 'Faq'));
            $workspace->addWorkItem('routes', new WorkItem('edk_route_index', 'Routes list'));
			$workspace->addWorkItem('routes', new WorkItem('project_stats_route_index', 'Route statistics'));
			$workspace->addWorkItem('participants', new WorkItem('edk_reg_settings_index', 'Registration settings'));
            $workspace->addWorkItem('participants', new WorkItem('project_participant_summary', 'Participants d/d'));
            $workspace->addWorkItem('participants', new WorkItem('project_stats_participant_index', 'Participant statistics'));
            $workspace->addWorkItem('area', new WorkItem('area_summary_index', 'Areas summary list'));
		}
	}

	public function onGroupWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		if ($workspace->getProject()->supportsModule('edk')) {
			$workspace->addWorkgroup(new Workgroup('participants', 'Participants', 'male', 5));
            $workspace->addWorkgroup(new Workgroup('routes', 'Routes', 'map-signs', 4));


            $workspace->addWorkItem('knowledge', new WorkItem('group_course_summary_index', 'Course results'));
            $workspace->addWorkItem('knowledge', new WorkItem('group_materials_index', 'Materials database'));
            $workspace->addWorkItem('knowledge', new WorkItem('group_faq_index', 'Faq'));
			$workspace->addWorkItem('routes', new WorkItem('edk_route_index', 'Routes'));
			$workspace->addWorkItem('participants', new WorkItem('edk_reg_settings_index', 'Registration settings'));
            $workspace->addWorkItem('area', new WorkItem('area_summary_index', 'Areas summary list'));
		}
	}
	
	public function onAreaWorkspace(WorkspaceEvent $event)
	{
		$workspace = $event->getWorkspace();
		
		if ($workspace->getProject()->supportsModule('edk')) {
            $workspace->addWorkgroup(new Workgroup('routes', 'Routes', 'map-signs', 4));
			$workspace->addWorkgroup(new Workgroup('participants', 'Participants', 'male', 5));

			$workspace->addWorkItem('knowledge', new WorkItem('area_course_index', 'On-line courses'));
			$workspace->addWorkItem('knowledge', new WorkItem('area_materials_index', 'Materials database'));
			$workspace->addWorkItem('knowledge', new WorkItem('area_faq_index', 'Faq'));
			$workspace->addWorkItem('area', new WorkItem('area_note_index', 'WWW: area information'));
			$workspace->addWorkItem('routes', new WorkItem('edk_route_index', 'Routes'));
			$workspace->addWorkItem('participants', new WorkItem('edk_reg_settings_index', 'Registration settings'));
			$workspace->addWorkItem('participants', new WorkItem('area_edk_message_index', 'Messages'));
            if ($this->authChecker->isGranted('PLACE_PD_ADMIN')) {
                $workspace->addWorkItem('participants', new WorkItem('area_edk_participant_index', 'Participants'));
            }
			$workspace->addWorkItem('participants', new WorkItem('area_stats_participant_index', 'Participant statistics'));
		}
	}
	
	public function onProjectAreaInfo(ContextMenuEvent $event)
	{
		$event->addLink('Participant statistics', 'project_area_stats', ['id' => $event->getEntity()->getId()]);
		$event->addLink('Import routes', 'edk_area_route_import', ['areaId' => $event->getEntity()->getId()]);
	}

    public function onUiHelp(ShowHelpEvent $event)
    {
        $pages = $event->getPages();
        $lastPage = end($pages);
        if (is_array($lastPage)) {
            // @HACK: FAQ link adding dependent on other links existed on list
            switch ($lastPage['route']) {
                case 'area_members':
                    $pages[] = $this->getFaqPage('area_faq_index');
                    break;

                case 'group_members':
                    $pages[] = $this->getFaqPage('group_faq_index');
                    break;

                case 'project_members':
                    $pages[] = $this->getFaqPage('project_faq_index');
                    break;
            }
            $event->setPages($pages);
        }
    }

    private function getFaqPage(string $route)
    {
        $slug = $this->request->attributes->get('slug');
        $faqPage = [
            'route' => $this->router->generate($route, [
                'slug' => $slug,
            ]),
            'title' => $this->translator->trans('Frequently asked questions', [], 'pages'),
        ];
        
        return $faqPage;
    }
}
