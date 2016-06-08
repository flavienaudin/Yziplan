<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 08/06/2016
 * Time: 11:23
 */

namespace ATUserBundle\Controller;

use AppBundle\Utils\FlashBagTypes;
use ATUserBundle\Entity\User;
use FOS\UserBundle\Controller\ProfileController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;


class ProfileController extends BaseController
{

    /**
     * @Route("/update-user-biography", name="updateUserBiographyAjax")
     */
    public function updateUserBiographyAjaxAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

            $data = array();
            $reponseStatus = 400;

            $user = $this->getUser();
            if ($user instanceof User) {
                $this->get("at.manager.user_about_manager")->updateBiography($user, $request->get('biography'));

                $reponseStatus = 200;
                $data["error"] = false;
                $data["message"] = $this->get("translator")->trans("profile.message.update.success");
                $data["data"]["biography"] = $request->get('biography');
            } else {
                $data["error"] = true;
                $data["message"] = $this->get("translator")->trans("profile.message.update.error");
            }
            return new JsonResponse($data, $reponseStatus);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans("global.error.not_ajax_request"));
            return $this->redirectToRoute('accueil');
        }

    }


}