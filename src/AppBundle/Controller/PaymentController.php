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

class PaymentController extends Controller
{

    /**
     * @Route("/test", name="test-mangopay")
     */
    public function testMangoPayAction(Request $request)
    {
        $mangopay = $this->get("at.manager.mangopay");
        $user = $mangopay->testMango();

        return $this->render('AppBundle:Core:mangopay.html.twig', array('user' => $user));
    }
    
    /**
     * @Route("/payment/newmodule", name="payment-newmodule")
     */
    public function createPaymentModuleAction(Request $request)
    {
        $mangopay = $this->get("at.manager.mangopay");
        $user = $mangopay->getMangoUser();

        return $this->render('AppBundle:Core:mangopay.html.twig', array('user' => $user));
    }
    
    /**
     * @Route("/payment/wallet/{walletId}", name="payment-newmodule")
     */
    public function createMangoPayUserAction(Request $request)
    {
        $mangopay = $this->get("at.manager.mangopay");
        $user = $mangopay->getMangoUser();

        return $this->render('AppBundle:Core:mangopay.html.twig', array('user' => $user));
    }

    /**
     * @Route("/payment/newmodule", name="payment-newmodule")
     */
    public function createMangoPayWalletAction(Request $request)
    {
        $mangopay = $this->get("at.manager.mangopay");
        $user = $mangopay->getMangoUser();

        return $this->render('AppBundle:Core:mangopay.html.twig', array('user' => $user));
    }
}