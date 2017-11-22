<?php

namespace WIO\EdkBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\GroupPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\Entity\EdkFaqQuestion;

/**
 * @Route("/group/{slug}/faq")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class GroupFaqController extends GroupPageController
{
    use FaqControllerTrait;

    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->initializeParams('data', EdkFaqQuestion::LEVEL_GROUP, 'group_faq_index', 'group_faq_info');
    }

    /**
     * @Route("/", name="group_faq_index")
     */
    public function indexAction()
    {
        return $this->renderIndex();
    }

    /**
     * @Route("/{id}", name="group_faq_info", requirements={"id": "\d+"})
     */
    public function infoAction(int $id)
    {
        return $this->renderInfo($id);
    }
}
