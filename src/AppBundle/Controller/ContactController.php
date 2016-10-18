<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 14/09/2016
 * Time: 12:39
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User\Contact;
use AppBundle\Entity\User\ContactEmail;
use AppBundle\Form\User\ContactType;
use AppBundle\Utils\enum\ContactStatus;
use AppBundle\Utils\enum\FlashBagTypes;
use AppBundle\Utils\Response\AppJsonResponse;
use ATUserBundle\Entity\AccountUser;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Class ContactController
 * @package AppBundle\Controller
 * @Route("/{_locale}/contacts")
 */
class ContactController extends Controller
{
    /**
     * Get the contact list of the User to populate the BootGrid table
     * Post Request :
     * - current : n => n° "page" affichée
     * - rowCount : n => nb de résultat par "page", -1 : tous les résultats
     * - sort[fieldname] = [asc|desc] => colonne ayant un tri activé
     * - searchPhrase : [''|string] Entrée du formulaire de recherche dans la table
     * @Route("/get", name="getUserContacts", methods={"POST"})
     */
    public function getUserContactsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();
            if ($user instanceof AccountUser) {
                $contactManager = $this->get("at.manager.contact");
                $data = $contactManager->getFilteredContactsOfUser($user, $request->request->get("searchPhrase", ""), $request->request->get("rowCount", 10),
                    $request->request->get("current", 1), $request->request->get("sort", array()));
                return new JsonResponse($data, Response::HTTP_OK);
            } else {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("global.error.unauthorized_access");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            }
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.not_ajax_request"));
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/manage/{id}", name="addContact", methods={"POST"})
     * @ParamConverter("contact", class="AppBundle:User\Contact", options={"strip_null" : true})
     */
    public function addEditContactAction(Request $request, Contact $contact = null)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        if ($user instanceof AccountUser) {
            $responseStatus = Response::HTTP_OK;
            $actionKey = 'edit';
            if ($contact == null) {
                $contact = new Contact();
                $actionKey = 'add';
            }
            $contactForm = $this->createForm(ContactType::class, $contact);
            $contactForm->handleRequest($request);
            if ($contactForm->isSubmitted()) {
                if ($contactForm->isValid()) {
                    // TODO transfert to manager.contact
                    $contact->setOwner($user->getApplicationUser());
                    $contact->setStatus(ContactStatus::VALID);
                    $this->get('doctrine.orm.entity_manager')->persist($contact);
                    $this->get('doctrine.orm.entity_manager')->flush();

                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans('contacts.message.' . $actionKey . '_contact.success');
                    // Initialize new contactForm
                    $contactForm = $this->createForm(ContactType::class, new Contact());
                    $responseStatus = Response::HTTP_OK;
                } else {
                    $responseStatus = Response::HTTP_BAD_REQUEST;
                }
            }
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#' . $actionKey . 'Contact_formcontainer'] =
                    $this->renderView("@App/Contact/partials/contact_form.html.twig", [
                        'modalIdPrefix' => $actionKey . 'Contact', 'contact' => (empty($contact->getId()) ? null : $contact), 'form_contact' => $contactForm->createView()
                    ]);
                return new AppJsonResponse($data, $responseStatus);
            } else {
                // Affichage du formulaire avec ou sans erreur dans une page spécifique
                // TODO : Not Ajax request not supported yet
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
                return $this->redirect($this->generateUrl("fos_user_profile_show") . '#profile-contacts');
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("global.error.unauthorized_access");
            return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.unauthorized_access"));
            return $this->redirect($this->generateUrl("fos_user_profile_show") . '#profile-contacts');
        }
    }


    /**
     * @Route("/add", name="addContact", methods={"POST"})
     * @deprecated
     */
    public function addContactAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        if ($user instanceof AccountUser) {
            $responseStatus = Response::HTTP_OK;
            $contact = new Contact();
            $addContactForm = $this->createForm(ContactType::class, $contact);
            $addContactForm->handleRequest($request);
            if ($addContactForm->isSubmitted()) {
                if ($addContactForm->isValid()) {
                    // TODO transfert to manager.contact
                    $contact->setOwner($user->getApplicationUser());
                    $contact->setStatus(ContactStatus::VALID);
                    $this->get('doctrine.orm.entity_manager')->persist($contact);
                    $this->get('doctrine.orm.entity_manager')->flush();

                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans('contacts.message.add_contact.success');
                    // Initialize new addContact form
                    $addContactForm = $this->createForm(ContactType::class, new Contact());
                    $responseStatus = Response::HTTP_OK;
                } else {
                    $responseStatus = Response::HTTP_BAD_REQUEST;
                }
            }
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#addContact_formcontainer'] =
                    $this->renderView("@App/Contact/partials/contact_form.html.twig", [
                        'modalIdPrefix' => 'addContact', 'contact' => null, 'form_contact' => $addContactForm->createView()
                    ]);
                return new AppJsonResponse($data, $responseStatus);
            } else {
                // Affichage du formulaire avec ou sans erreur dans une page spécifique
                // TODO : Not Ajax request not supported yet
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
                return $this->redirect($this->generateUrl("fos_user_profile_show") . '#profile-contacts');
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("global.error.unauthorized_access");
            return new AppJsonResponse($data, Response::HTTP_UNAUTHORIZED);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.unauthorized_access"));
            return $this->redirect($this->generateUrl("fos_user_profile_show") . '#profile-contacts');
        }
    }

    /**
     * @Route("/edit/{id}", name="editContact", methods={"POST"})
     * @ParamConverter("contact", class="AppBundle:User\Contact")
     * @deprecated
     */
    public function editContactAction(Contact $contact, Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        $user = $this->getUser();
        $data = array();

        if ($contact != null && $user instanceof AccountUser) {
            $addContactForm = $this->createForm(ContactType::class, $contact);
            $addContactForm->handleRequest($request);
            if ($request->isXmlHttpRequest()) {
                if ($addContactForm->isSubmitted()) {
                    if ($addContactForm->isValid()) {
                        // TODO transfert to manager
                        $contact->setOwner($user->getApplicationUser());
                        $contact->setStatus(ContactStatus::VALID);
                        $this->get('doctrine.orm.entity_manager')->persist($contact);
                        $this->get('doctrine.orm.entity_manager')->flush();
                        $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get('translator')->trans('contacts.message.add_contact.success');
                        return new AppJsonResponse($data, Response::HTTP_OK);
                    } else {
                        $data[AppJsonResponse::HTML_CONTENTS][AppJsonResponse::HTML_CONTENT_ACTION_REPLACE]['#editContact_modal'] =
                            $this->renderView("@App/Contact/partials/modal_contact_form.html.twig", [
                                'modalIdPrefix' => 'editContact', 'contact' => $contact, 'form_contact' => $addContactForm->createView()
                            ]);
                        return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                    }
                }

            } else {
                // TODO : Not Ajax request not supported yet
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get('translator')->trans('global.error.not_ajax_request'));
                return $this->redirect($this->generateUrl("fos_user_profile_show") . '#profile-contacts');
            }
        }
        if ($request->isXmlHttpRequest()) {
            $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("global.error.unauthorized_access");
            return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
        } else {
            $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator")->trans("global.error.unauthorized_access"));
            return $this->redirect($this->generateUrl("fos_user_profile_show") . '#profile-contacts');
        }
    }


    /**
     * @Route("/delete", name="deleteContact", methods={"POST"})
     */
    public function deleteContactAction(Request $request)
    {
        $this->denyAccessUnlessGranted(AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED);
        if ($request->request->has("contact-id")) {
            $contactManager = $this->get("at.manager.contact");
            if ($contactManager->removeContact($this->getUser(), $request->request->get('contact-id'))) {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::SUCCESS_TYPE][] = $this->get("translator")->trans("contacts.message.remove_contact.success");
                    return new AppJsonResponse($data, Response::HTTP_OK);
                } else {
                    $this->addFlash(FlashBagTypes::SUCCESS_TYPE, $this->get("translator.default")->trans("contacts.message.remove_contact.success"));
                    return $this->redirectToRoute('fos_user_profile_show');
                }
            } else {
                if ($request->isXmlHttpRequest()) {
                    $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator")->trans("contacts.message.remove_contact.error");
                    return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
                } else {
                    $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator.default")->trans("contacts.message.remove_contact.error"));
                    return $this->redirectToRoute('fos_user_profile_show');
                }
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $data[AppJsonResponse::MESSAGES][FlashBagTypes::ERROR_TYPE][] = $this->get("translator.default")->trans("global.error.invalid_form");
                return new AppJsonResponse($data, Response::HTTP_BAD_REQUEST);
            } else {
                $this->addFlash(FlashBagTypes::ERROR_TYPE, $this->get("translator.default")->trans("global.error.invalid_form"));
                return $this->redirectToRoute('home');
            }
        }
    }
}