<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CoreController
 * @package AppBundle\Controller
 * @Route("/{_locale}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class CoreController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:Core:index.html.twig');
    }

    /**
     * @Route("/testindex", name="testhome")
     */
    public function testIndexAction(Request $request)
    {
        return $this->render('AppBundle:Core:testIndex.html.twig');
    }

    /**
     * @Route("/testindex2", name="testhome2")
     */
    public function testIndex2Action(Request $request)
    {
        return $this->render('AppBundle:Core:testIndex2.html.twig');
    }

}
