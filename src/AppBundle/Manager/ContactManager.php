<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 14/09/2016
 * Time: 16:13
 */

namespace AppBundle\Manager;

use AppBundle\Entity\User\ApplicationUser;
use ATUserBundle\Entity\AccountUser;
use ATUserBundle\Manager\UserManager;
use Doctrine\ORM\EntityManager;

class ContactManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var UserManager */
    private $userManager;

    /**
     * ContactManager constructor.
     *
     * @param EntityManager $doctrine
     * @param UserManager $userManager
     */
    public function __construct(EntityManager $doctrine, UserManager $userManager)
    {
        $this->entityManager = $doctrine;
        $this->userManager = $userManager;
    }


    /**
     * Recherche la liste d'emails des utilisateurs ayant déjà été invité par l'utilisateur connecté.
     * @param AccountUser $appUser l'utilisateur connecté
     * @return array|null
     */
    public function getUserContactEmailsForAutocomplete(AccountUser $appUser)
    {
        $emails = array();
        /** @var ApplicationUser $appUser */
        foreach ($appUser->getApplicationUser()->getContactsListAsUser() as $appUser) {
            $emails[] = $appUser->getAppUserEmails();
        }
        return $emails;
    }

    /**
     * @param AccountUser $user
     * @param null $search
     * @param int $nbResult
     * @param int $pageIdx
     * @param array $sorts
     * @return mixed
     */
    public function getFilteredContactsOfUser(AccountUser $user, $search = null, $nbResult = 10, $pageIdx = 1, $sorts = array())
    {
        $result['current'] = (int)$pageIdx;
        $result['rowCount'] = (int)$nbResult;

        $contactRepo = $this->entityManager->getRepository("ATUserBundle:Contact");
        $contactsList = $contactRepo->findUserContacts($user, $search, $nbResult, $pageIdx, $sorts);
        $result['rows'] = array();
        /** @var Contact $contact */
        foreach ($contactsList as $contact) {
            $linked = $contact->getLinked();
            $linkedAsArray = array();
            $linkedAsArray['name'] = $linked->getDisplayableName();
            $linkedAsArray['avatar'] = null; // TODO
            $linkedAsArray['email'] = $linked->getEmail();
            /** @var ContactGroup $group */
            foreach ($contact->getGroups() as $group) {
                $linkedAsArray['groups'][] = $group->getName();
            }
            $result['rows'][] = $linkedAsArray;
        }
        $result['total'] = (int)$contactRepo->countUserContacts($user, $search);
        return $result;
    }

    /**
     * Ajoute aux contacts d'un utilisateur un autre utilisateur
     * @param AccountUser $owner L'utilisateur a qui ajouter le contact
     * @param int|string $userLinkedId L'identifiant ou EMAIL de l'utilisateur à ajouter
     * @return array|bool true si l'opréation réussit, false sinon
     */
    public function addContact(AccountUser $owner, $userLinkedId)
    {
        if (is_array($userLinkedId)) {
            $result = true;
            foreach ($userLinkedId as $id) {
                $result &= $this->addContact($owner, $id);
            }
            return $result;
        } else {
            // TODO Revoir la gestion des contacts => ApplicationUser + AppUserEmail
            if (is_numeric($userLinkedId)) {
                $userLinked = $this->userManager->findUserBy(array('id' => $userLinkedId));
            } else {
                $userLinked = $this->userManager->findUserByEmail($userLinkedId);
                if ($userLinked == null) {
                    $userLinked = $this->userManager->createUserFromEmail($userLinkedId);
                }
            }

            if ($userLinked != null && $owner != $userLinked) {
                $contactRepository = $this->entityManager->getRepository("ATUserBundle:Contact");
                /** @var Contact $existingContact */
                $existingContact = $contactRepository->findOneBy(array('owner' => $owner, 'linked' => $userLinked));
                if ($existingContact != null) {
                    if ($existingContact->getStatus() != Contact::STATUS_VALID) {
                        $existingContact->setStatus(Contact::STATUS_VALID);
                        $this->entityManager->persist($existingContact);
                    } else {
                        return false;
                    }
                } else {
                    $existingContact = new Contact();
                    $existingContact->setOwner($owner);
                    $existingContact->setLinked($userLinked);
                    $existingContact->setStatus(Contact::STATUS_VALID);
                    $owner->addContact($existingContact);
                    $this->entityManager->persist($owner);

                    /* TODO Envoi d'email ?
                    if (!$userLinked->getContactsListAsUser(false)->contains($owner)) {
                        // Si le contact a déjà l'utilisateur dans ses contacts, pas d'envoi d'email
                        //$this->emailManager->envoiNotificationAjoutAuxContacts($owner, $userLinked);
                    }*/
                }
                $this->entityManager->flush();
                return true;
            }
            return false;
        }
    }

    /**
     * Supprime un utilisateur des contacts d'un autre utilisateur : passe à l'état ETAT_SUPPRIME
     * @param AccountUser $owner L'utilisateur à qui supprimer le contact
     * @param string $email Email de l'User à supprimer des contacts
     * @return bool true si l'opréation a réussi, false sinon
     */
    public function removeContact(AccountUser $owner, $email)
    {
        $userLinked = $this->userManager->findUserByEmail($email);
        if ($userLinked != null) {
            $contactRepository = $this->entityManager->getRepository("ATUserBundle:Contact");
            /** @var Contact $existingContact */
            $existingContact = $contactRepository->findOneBy(array('owner' => $owner, 'linked' => $userLinked));
            if ($existingContact != null && $existingContact->getStatus() != Contact::STATUS_DELETED) {
                $existingContact->setStatus(Contact::STATUS_DELETED);
                $this->entityManager->persist($existingContact);
                $this->entityManager->flush();
                return true;
            }
        }
        return false;
    }

}