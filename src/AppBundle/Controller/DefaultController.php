<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/{_locale}", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="accueil")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:Core:index.html.twig');
    }


    /**
     * @Route("/{_locale}/evenement", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="event")
     */
    public function eventAction(Request $request)
    {
        return $this->render('AppBundle:Core:evenement.html.twig');
    }
}
