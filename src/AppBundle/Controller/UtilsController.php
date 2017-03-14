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
 * Class UtilsController
 * @package AppBundle\Controller
 * @Route("/{_locale}/utils", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class UtilsController extends Controller
{
    /**
     * @Route("/autocomplete/place", name="placeAutocompletion")
     */
    public function indexAction(Request $request)
    {
        $pattern = $request->query->get('query');
        $activities = $this->get('at.manager.utils')->getPlaceAutocompleteResult($pattern);
        $result = array("suggestions" => $activities);
        return $this->json($result);
    }
}
