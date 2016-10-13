<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 13/10/2016
 * Time: 18:07
 */

namespace ATUserBundle\Controller;


use ATUserBundle\Entity\AccountUser;
use FOS\UserBundle\Controller\ResettingController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;


class ResettingController extends BaseController
{
    /**
     * Initialise le mot de passe d'un utilisateur ne le connaissant pas car l'utilisateur a été créé via hwi-oAuth (ou pas invitation)
     * @Route("/{_locale}/init-password/request", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="init_password_request")
     */
    public function initializePasswordAction(Request $request)
    {
        $utilisateur = $this->getUser();
        if (!is_object($utilisateur) || !$utilisateur instanceof AccountUser) {
            throw $this->createAccessDeniedException($this->get("translator")->trans("global.error.unauthorized_access"));
        }

        if ($utilisateur->isPasswordKnown()) {
            $this->addFlash('warning', $this->get("translator")->trans('profile.message.init_password.already_init'));
            return new RedirectResponse($this->generateUrl('fos_user_profile_show'));
        }
        $request->request->set('username', $utilisateur->getUsername());
        return $this->sendEmailAction($request);
    }
}