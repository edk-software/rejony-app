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

namespace WIO\EdkBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectAwareControllerInterface;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\CoreBundle\CoreExtensions;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/fastActions")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class FastActionController extends ProjectPageController
{
    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->breadcrumbs()
            ->workgroup('data')
            ->entryLink($this->trans('Fast Actions', [], 'pages'), 'project_fastAction', ['slug' => $this->getSlug()]);
    }
    const REPOSITORY_NAME = 'wio.edk.repo.validation';
	/**
	 * @Route("/index", name="project_fastAction")
	 */
	public function indexAction(Request $request)
	{
        $repository = $this->get(self::REPOSITORY_NAME);
        $data = $repository->listRegistrationsData($project = $this->getActiveProject()->getId());
        return $this->render('WioEdkBundle:AreaSummary:routes.html.twig', array('regSettings' => $data));
	}
}
