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
 * @Route("/{_locale}/directory", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class DirectoryController extends Controller
{
    /**
     * @Route("/", name="directoryIndex")
     */
    public function indexAction(Request $request)
    {
        $activities = $this->get('at.manager.directory')->getActivities();
        return $this->render('AppBundle:Directory:directory_index.html.twig', array(
            "activities" => $activities
        ));
    }
}
