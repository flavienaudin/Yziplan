<?php

namespace AppBundle\Controller;

use AppBundle\Form\ActivityDirectory\NewActivityType;
use AppBundle\Form\ActivityDirectory\SearchActivityType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class DirectoryController
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
        $activities = null;
        $searchFormResult = $this->get('form.factory')->create(SearchActivityType::class);
        $searchFormResult->handleRequest($request);
        if ($searchFormResult->isSubmitted()) {
            if ($searchFormResult->isValid()) {
                /**
                 * Array d'id d'activityType
                 */
                $types = $searchFormResult->get('type')->getData();
                /**
                 * Début de nom de ville, departement ou region ou null
                 */
                $place = $searchFormResult->get('place')->getData();
                $activities = $this->get('at.manager.directory')->searchActivities($types, $place);
            }
        } else {
            $activities = $this->get('at.manager.directory')->getActivities();
        }
        /** @var FormInterface $searchForm */
        $searchForm = $this->get('form.factory')->create(SearchActivityType::class);

        return $this->render('AppBundle:Directory:directory_index.html.twig', array(
            "activities" => $activities,
            "searchForm" => $searchForm->createView()
        ));
    }
    /**
     * @Route("/admin/new", name="directoryAdminNew")
     */
    public function adminNew(Request $request)
    {
        $activities = null;
        $addActivityForm = $this->get('form.factory')->create(NewActivityType::class);
        $addActivityForm->handleRequest($request);
        if ($addActivityForm->isSubmitted()) {
            if ($addActivityForm->isValid()) {
                $eventId = $addActivityForm->get('event')->getData();
                $typeIds = $addActivityForm->get('activityTypes')->getData();
                $place = $addActivityForm->get('place')->getData();
                $this->get('at.manager.directory')->treatAddActivityForm($eventId, $typeIds, $place);
            }
        }
        /** @var FormInterface $newForm */
        $newForm = $this->get('form.factory')->create(NewActivityType::class);

        return $this->render('AppBundle:Directory:directory_admin_new.html.twig', array(
            "activities" => $activities,
            "newForm" => $newForm->createView()
        ));
    }

}
