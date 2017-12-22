<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Cantiga\KnowledgeBundle\Entity\FaqQuestion as Question;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/area/{slug}/faq")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaFaqController extends AreaPageController
{
    use FaqControllerTrait;

    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->initializeParams(Question::LEVEL_AREA, 'area_faq_index', 'area_faq_info');
    }

    /**
     * @Route("/", name="area_faq_index")
     */
    public function indexAction() : Response
    {
        return $this->renderIndex();
    }

    /**
     * @Route("/{id}", name="area_faq_info", requirements={"id": "\d+"})
     */
    public function infoAction(int $id) : Response
    {
        return $this->renderInfo($id);
    }
}
