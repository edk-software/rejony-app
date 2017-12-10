<?php

namespace WIO\EdkBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\Entity\EdkFaqQuestion;

/**
 * @Route("/project/{slug}/faq")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class ProjectFaqController extends ProjectPageController
{
    use FaqControllerTrait;
    
    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->initializeParams('knowledge', EdkFaqQuestion::LEVEL_PROJECT, 'project_faq_index', 'project_faq_info');
    }

    /**
     * @Route("/", name="project_faq_index")
     */
    public function indexAction()
    {
        return $this->renderIndex();
    }

    /**
     * @Route("/{id}", name="project_faq_info", requirements={"id": "\d+"})
     */
    public function infoAction(int $id)
    {
        return $this->renderInfo($id);
    }
}
