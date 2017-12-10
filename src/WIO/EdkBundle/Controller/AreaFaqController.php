<?php

namespace WIO\EdkBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use WIO\EdkBundle\Entity\EdkFaqQuestion;

/**
 * @Route("/area/{slug}/faq")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class AreaFaqController extends AreaPageController
{
    use FaqControllerTrait;

    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->initializeParams('knowledge', EdkFaqQuestion::LEVEL_AREA, 'area_faq_index', 'area_faq_info');
    }

    /**
     * @Route("/", name="area_faq_index")
     */
    public function indexAction()
    {
        return $this->renderIndex();
    }

    /**
     * @Route("/{id}", name="area_faq_info", requirements={"id": "\d+"})
     */
    public function infoAction(int $id)
    {
        return $this->renderInfo($id);
    }
}
