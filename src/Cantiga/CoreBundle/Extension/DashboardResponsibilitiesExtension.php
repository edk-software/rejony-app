<?php

namespace Cantiga\CoreBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Api\Workspace;
use Cantiga\CoreBundle\CoreSettings;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Repository\ProjectAreaRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DashboardResponsibilitiesExtension implements DashboardExtensionInterface
{
	/** @var ProjectAreaRequestRepository */
	private $repository;
	/** @var EngineInterface */
	private $templating;
	/** @var TranslatorInterface */
    private $translator;
	
	public function __construct(ProjectAreaRequestRepository $repository, EngineInterface $templating,
        TranslatorInterface $translator)
	{
		$this->repository = $repository;
		$this->templating = $templating;
		$this->translator = $translator;
	}
	
	public function getPriority()
	{
		return self::PRIORITY_MEDIUM - 10;
	}

	public function render(CantigaController $controller, Request $request, Workspace $workspace,
        Project $project = null)
	{
	    $showChat = $controller
            ->getProjectSettings()
            ->get(CoreSettings::DASHBOARD_SHOW_CHAT)
            ->getValue()
        ;
		if ($showChat) {
		    $user = $controller->getUser();
		    if (isset($user)) {
                $this->repository->setActiveProject($project);
                $comments = $this->repository->getLastCommentsFromAreasOfResponsibility($user);
                if (count($comments) > 0) {
                    return $this->templating->render('CantigaCoreBundle:Project:recent-comments.html.twig', [
                        'comments' => $comments,
                        'title' => $this->translator->trans('Messages in linked area requests'),
                    ]);
                }
            }
		}

		return '';
	}
}
