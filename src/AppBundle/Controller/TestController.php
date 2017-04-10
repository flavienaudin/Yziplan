<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 22/11/2016
 * Time: 14:12
 */

namespace AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class TestController
 * @package AppBundle\Controller
 *
 * @Route("/{_locale}/test", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class TestController extends Controller
{

    /**
     * @Route("/action")
     */
    public function testAction()
    {
        if ($this->get('kernel')->getEnvironment() == "dev") {

            // Test here

            return $this->render("@App/Test/testResult.html.twig", []);
        }
        return $this->render("@App/Test/testResult.html.twig", ['error' => "MAUVAIS ENVIRONNEMENT"]);

    }

}