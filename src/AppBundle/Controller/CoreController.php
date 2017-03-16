<?php

namespace AppBundle\Controller;

use AppBundle\Form\Core\SuggestionType;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @Route("/pro", name="indexPro")
     */
    public function indexProAction(Request $request)
    {
        return $this->render('AppBundle:Core:footer/index_pro.html.twig');
    }

    /**
     * @Route("/help", name="helpPageCreation")
     */
    public function helpPageCreationAction(Request $request)
    {
        return $this->render('AppBundle:Core:footer/help_page.html.twig');
    }

    /**
     * @Route("/privacy-policy", name="privacyPolicy")
     */
    public function privacyPolicyAction(Request $request)
    {
        return $this->render('@App/Core/privacy_policy.html.twig');
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
        $suggestionForm = $this->get('form.factory')->create(SuggestionType::class);
        $suggestionForm->handleRequest($request);
        if ($suggestionForm->isSubmitted()) {
            if ($request->isXmlHttpRequest()) {
                if ($suggestionForm->isValid()) {
                    $this->get("at.manager.retour_utilisateur")->posterSuggestion($suggestionForm);

                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans("suggestion.success.message");

                    // Reset form
                    $suggestionForm = $this->get('form.factory')->create(SuggestionType::class);
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#suggestion_formContainer'] =
                        $this->renderView("partials/suggestion_form.html.twig", array(
                            'suggestionForm' => $suggestionForm->createView()
                        ));
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#suggestion_formContainer'] =
                        $this->renderView("partials/suggestion_form.html.twig", array(
                            'suggestionForm' => $suggestionForm->createView()
                        ));
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                }
            } else {
                $this->addFlash("error", $this->get("translator")->trans("global.error.not_ajax_request"));
                return $this->redirectToRoute("home");
            }
        }

        return $this->render('partials/suggestion_modal.html.twig', array(
            'suggestionForm' => $suggestionForm->createView()
        ));
    }
}
