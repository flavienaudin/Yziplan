<?php

namespace ATUserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use FOS\UserBundle\Controller\ResettingController as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;


class ResettingController extends BaseController
{
    /**
     * Initialise le mot de passe d'un utilisateur ne connaissant pas son mot de passe car créé via hwi-oAuth
     * @Route("/{_locale}/mot-de-passe", defaults={"_locale": "fr"}, requirements={"_locale": "en|fr"}, name="initialiserMotDePasse")
     */
    public function initialiserMotDePasseAction(Request $request)
    {
        $utilisateur = $this->getUser();
        if (!is_object($utilisateur) || !$utilisateur instanceof UserInterface) {
            throw $this->createAccessDeniedException($this->get("translator.default")->trans("erreur.acces_non_autorise"));
        }

        if ($utilisateur->isPasswordKnown()) {
            $this->addFlash('warning', $this->get("translator.default")->trans('init_mot_de_passe.erreur.deja_initialise'));
            return new RedirectResponse($this->generateUrl('fos_user_profile_show'));
        }
        $request->request->set('username', $utilisateur->getUsername());
        return $this->sendEmailAction($request);
    }
}