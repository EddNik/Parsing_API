<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ClassSymfony;
use AppBundle\Entity\InterfaceSymfony;
use AppBundle\Entity\NamespaceSymfony;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ParserController
 * @Route("parsing")
 */
class ParserController extends Controller
{
    /**
     * @Route("/admin")
     */
    public function adminAction()
    {
        return new Response('<html><body>Admin page!</body></html>');
    }

    /**
     * @Route("/", name="parse_index")
     * @Method("GET")
     */

    public function indexAction()
    {
        return $this->render('parsing/pars.html.twig');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/rowData", name="retrieve")
     *
     */

    public function retrieveNameSpaceAction()
    {
        $repository = $this->getDoctrine()->getRepository(NamespaceSymfony::class);
        $options = array('representationField' => 'slug', 'url' => true, 'childSort' => 'desc');
        $repository->setChildrenIndex('children');
        $htmlTree = $repository->childrenHierarchy(
            null, /* starting from root nodes */
            false, /* false: load all children, true: only direct */
            $options
        );
        return $this->json($htmlTree);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/rowData/interfaces", name="retrieveInterfaces")
     *
     */

    public function retrieveInterfacesAction()
    {
        $repository = $this->getDoctrine()->getRepository(InterfaceSymfony::class);
        $options = array('representationField' => 'slug', 'url' => true, 'childSort' => 'desc');
        $repository->setChildrenIndex('children');
        $htmlTree = $repository->childrenHierarchy(
            null, /* starting from root nodes */
            false, /* false: load all children, true: only direct */
            $options
        );
        return $this->json($htmlTree);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route("/rowData/classes", name="retrieveClasses")
     *
     */

    public function retrieveClassesAction()
    {
        $repository = $this->getDoctrine()->getRepository(ClassSymfony::class);
        $options = array('representationField' => 'slug', 'url' => true, 'childSort' => 'desc');
        $repository->setChildrenIndex('children');
        $htmlTree = $repository->childrenHierarchy(
            null, /* starting from root nodes */
            false, /* false: load all children, true: only direct */
            $options
        );
        return $this->json($htmlTree);
    }
}
