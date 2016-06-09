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
use ATUserBundle\Form\UserAboutBasicInformationType;
use ATUserBundle\Form\UserAboutBiographyType;
use FOS\UserBundle\Controller\ProfileController as BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\User\UserInterface;


class ProfileController extends BaseController
{

    /**
     * Show the user
     */
    public function showAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof User) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }
        $userAbout = $this->get("at.manager.user_about_manager")->getUserAbout($user);

        $biographyForm = $this->createForm(UserAboutBiographyType::class, $userAbout);
        $basicInformationForm = $this->createForm(UserAboutBasicInformationType::class, $userAbout);

        return $this->render('FOSUserBundle:Profile:show.html.twig', array(
            'user' => $user,
            'form_biography' => $biographyForm->createView(),
            'form_basic_information' => $basicInformationForm->createView()
        ));
    }

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
                $userAboutManager = $this->get("at.manager.user_about_manager");
                $userAbout = $userAboutManager->getUserAbout($user);
                $biographyForm = $this->createForm(UserAboutBiographyType::class, $userAbout);
                $biographyForm->handleRequest($request);
                if ($biographyForm->isValid()) {
                    $userAboutManager->updateUserAbout($userAbout);

                    $reponseStatus = 200;
                    $data["error"] = false;
                    $data["message"] = $this->get("translator")->trans("profile.message.update.success");
                    $data["data"]["biography"] = nl2br($userAbout->getBiography());
                }else {
                    $data["error"] = array();
                    foreach ($biographyForm->getErrors(true) as $error) {
                        $data["error"][$error->getOrigin()->getName()] = $error->getMessage();
                    }
                }
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

    /**
     * @Route("/update-user-basic-information", name="updateUserBasicInformationAjax")
     */
    public function updateUserBasicInformationAjaxAction(Request $request)
    {
        if ($request->getMethod() == 'POST' && $request->isXmlHttpRequest()) {
            $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);

            $data = array();
            $reponseStatus = 400;

            $user = $this->getUser();
            if ($user instanceof User) {
                $userAboutManager = $this->get("at.manager.user_about_manager");
                $userAbout = $userAboutManager->getUserAbout($user);
                $basicInformationForm = $this->createForm(UserAboutBasicInformationType::class, $userAbout);
                $basicInformationForm->handleRequest($request);
                if ($basicInformationForm->isValid()) {
                    $userAboutManager->updateUserAbout($userAbout);

                    $reponseStatus = 200;
                    $data["message"] = $this->get("translator")->trans("profile.message.update.success");
                    // TODO : Gérer le format de la date en fonction de la locale
                    $data["data"] = array(
                        'fullname' => $userAbout->getFullname(),
                        'gender' => $this->get('translator')->trans("global.gender." . $userAbout->getGender()),
                        'birthday' => $userAbout->getBirthday()->format("d/m/Y")
                    );
                } else {
                    $data["error"] = array();
                    foreach ($basicInformationForm->getErrors(true) as $error) {
                        $data["error"][$error->getOrigin()->getName()] = $error->getMessage();
                    }
                }
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