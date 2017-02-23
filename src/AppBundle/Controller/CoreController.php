<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        if ($this->get('kernel')->getEnvironment() == "dev") {
            return $this->render('AppBundle:Core:testIndex.html.twig');
        } else {
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/testindex2", name="testhome2")
     */
    public function testIndex2Action(Request $request)
    {
        if ($this->get('kernel')->getEnvironment() == "dev") {
            return $this->render('AppBundle:Core:testIndex2.html.twig');
        } else {
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/add_suggestion", name="addSuggestion")
     */
    public function addSuggestionAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $datas = $request->request->all();
            $this->get("at.manager.retour_utilisateur")->posterSuggestion($datas);
            return new JsonResponse(array(
                "type" => "success",
                "titre" => $this->get('translator')->trans("suggestion.success.titre"),
                "message" => $this->get('translator')->trans("suggestion.success.message")
            ));
        } else {
            $this->addFlash("error", $this->get("translator")->trans("flashMessage.erreur_requete"));
            return $this->redirectToRoute("home");
        }
    }
}
