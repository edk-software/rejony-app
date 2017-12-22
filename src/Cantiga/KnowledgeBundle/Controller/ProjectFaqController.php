<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\KnowledgeBundle\Entity\FaqQuestion as Question;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/faq")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class ProjectFaqController extends ProjectPageController
{
    use FaqControllerTrait;
    
    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->initializeParams(Question::LEVEL_PROJECT, 'project_faq_index', 'project_faq_info');
    }

    /**
     * @Route("/", name="project_faq_index")
     */
    public function indexAction() : Response
    {
        return $this->renderIndex();
    }

    /**
     * @Route("/{id}", name="project_faq_info", requirements={"id": "\d+"})
     */
    public function infoAction(int $id) : Response
    {
        return $this->renderInfo($id);
    }
}
