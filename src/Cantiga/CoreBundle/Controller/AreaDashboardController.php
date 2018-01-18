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
namespace Cantiga\CoreBundle\Controller;

use Cantiga\Components\Hierarchy\Entity\Membership;
use Cantiga\CoreBundle\Api\Controller\WorkspaceController;
use Cantiga\CoreBundle\Controller\Traits\DashboardTrait;
use Cantiga\CoreBundle\CoreExtensions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Cantiga\CoreBundle\Entity\Message;

/**
 * @Route("/s/{slug}")
 */
class AreaDashboardController extends WorkspaceController
{
	use DashboardTrait;

    const REPOSITORY_NAME = 'cantiga.core.repo.area_comment';

    /**
     * @Route("/{id}/ajax-feed", name="area_chat_dashboard_ajax_feed")
     */
    public function ajaxFeedAction($id, Request $request)
    {
        try {
            $repository = $this->get(self::REPOSITORY_NAME);

            return new JsonResponse($repository->getFeedback($id));
        } catch (Exception $ex) {
            return new JsonResponse(['status' => 0]);
        }
    }

    /**
     * @Route("/{id}/ajax-post", name="area_chat_dashboard_ajax_post")
     */
    public function ajaxPostAction($id,  Request $request)
    {
        try {
            $repository = $this->get(self::REPOSITORY_NAME);
            $message = $request->get('message');
            if (!empty($message)) {
                $repository->addMessage($id, new Message($this->getUser(), $message));
            }

            return new JsonResponse($repository->getFeedback($id));
        } catch (Exception $ex) {
            return new JsonResponse(['status' => 0]);
        }
    }
}
