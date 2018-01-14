<?php

namespace Cantiga\KnowledgeBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\KnowledgeBundle\Entity\MaterialsFile as File;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/materials")
 * @Security("is_granted('PLACE_MEMBER')")
 */
class ProjectMaterialsController extends ProjectPageController
{
    use MaterialsControllerTrait;
    
    public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
    {
        $this->initializeParams(File::LEVEL_PROJECT, 'project_materials_index', 'project_materials_info',
            'project_materials_file');
    }

    /**
     * @Route("/", name="project_materials_index")
     */
    public function indexAction() : Response
    {
        return $this->renderIndex();
    }

    /**
     * @Route("/{id}", name="project_materials_info", requirements={"id": "\d+"})
     */
    public function infoAction(int $id) : Response
    {
        return $this->renderInfo($id);
    }

    /**
     * @Route("/file/{id}", name="project_materials_file", requirements={"id": "\d+"})
     */
    public function fileAction(int $id) : Response
    {
        return $this->returnFile($id, null, $this->level);
    }
}
