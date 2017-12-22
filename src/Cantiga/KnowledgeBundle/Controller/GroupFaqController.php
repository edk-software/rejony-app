<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\GroupPageController;
use Cantiga\KnowledgeBundle\Entity\FaqQuestion as Question;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/group/{slug}/faq")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class GroupFaqController extends GroupPageController
{
    use FaqControllerTrait;

    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->initializeParams(Question::LEVEL_GROUP, 'group_faq_index', 'group_faq_info');
    }

    /**
     * @Route("/", name="group_faq_index")
     */
    public function indexAction() : Response
    {
        return $this->renderIndex();
    }

    /**
     * @Route("/{id}", name="group_faq_info", requirements={"id": "\d+"})
     */
    public function infoAction(int $id) : Response
    {
        return $this->renderInfo($id);
    }
}
