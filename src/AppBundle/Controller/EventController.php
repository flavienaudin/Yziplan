<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 01/06/2016
 * Time: 11:13
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends Controller
{

    /**
     * @Route("/{_locale}/evenement", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="event")
     */
    public function eventAction(Request $request)
    {
        return $this->render('AppBundle:Event:evenement.html.twig');
    }
}