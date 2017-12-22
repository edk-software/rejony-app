<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\GroupPageController;
use Cantiga\KnowledgeBundle\Entity\MaterialsFile as File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/group/{slug}/materials")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class GroupMaterialsController extends GroupPageController
{
    use MaterialsControllerTrait;

    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->initializeParams(File::LEVEL_GROUP, 'group_materials_index', 'group_materials_info',
            'group_materials_file');
    }

    /**
     * @Route("/", name="group_materials_index")
     */
    public function indexAction() : Response
    {
        return $this->renderIndex();
    }

    /**
     * @Route("/{id}", name="group_materials_info", requirements={"id": "\d+"})
     */
    public function infoAction(int $id) : Response
    {
        return $this->renderInfo($id);
    }

    /**
     * @Route("/file/{id}", name="group_materials_file", requirements={"id": "\d+"})
     */
    public function fileAction(int $id) : Response
    {
        return $this->returnFile($id);
    }
}
