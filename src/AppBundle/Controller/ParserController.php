<?php

namespace AppBundle\Controller;

use AppBundle\Entity\NamespaceSymfony;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class ParserController
 * @Route("parsing")
 */
class ParserController extends Controller
{
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

    public function retrieveAction()
    {
        $repository = $this->getDoctrine()->getRepository(NamespaceSymfony::class);
        $options = array('representationField' => 'slug', 'html' => true);
        $htmlTree = $repository->childrenHierarchy(
            null, /* starting from root nodes */
            false, /* false: load all children, true: only direct */
            $options
        );
        return $this->json($htmlTree);
    }

}