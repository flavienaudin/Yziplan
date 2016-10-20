<?php
/**
 * Created by PhpStorm.
 * User: Patman
 * Date: 25/05/2016
 * Time: 09:52
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PaymentController
 * @package AppBundle\Controller
 * @Route("/{_locale}/payment", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"})
 */
class PaymentController extends Controller
{

    /**
     * @ Route("/test", name="testMangopay")
     */
    public function testMangoPayAction(Request $request)
    {
        $mangopay = $this->get("at.manager.mangopay");
        $user = $mangopay->testMango();

        return $this->render('', array('user' => $user));
    }
}