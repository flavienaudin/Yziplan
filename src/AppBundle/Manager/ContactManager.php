<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 14/09/2016
 * Time: 16:13
 */

namespace AppBundle\Manager;

use AppBundle\Entity\User\ApplicationUser;
use AppBundle\Entity\User\Contact;
use AppBundle\Entity\User\ContactEmail;
use AppBundle\Entity\User\ContactGroup;
use AppBundle\Utils\enum\ContactStatus;
use ATUserBundle\Entity\AccountUser;
use ATUserBundle\Manager\UserManager;
use Doctrine\ORM\EntityManager;

class ContactManager
{
    /** @var EntityManager */
    private $entityManager;

    /** @var UserManager */
    private $userManager;

    /** @var Contact $contact */
    private $contact;

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
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Contact $contact
     * @return ContactManager
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * Retrieve a Contact by its ID
     * @param $contactId int Id of the contact to retrieve
     * @return Contact|null
     */
    public function retrieveContact($contactId)
    {
        $this->contact = $this->entityManager->getRepository(Contact::class)->find($contactId);
        return $this->contact;

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

        $contactRepo = $this->entityManager->getRepository(Contact::class);
        $contactsList = $contactRepo->findUserContacts($user, $search, $nbResult, $pageIdx, $sorts);
        $result['rows'] = array();
        /** @var Contact $contact */
        foreach ($contactsList as $contact) {
            //$linked = $contact->getLinked();
            $contactsAsArray = array();
            $contactsAsArray['id'] = $contact->getId();
            $contactsAsArray['name'] = $contact->getFirstName() . ' ' . $contact->getLastName();
            $contactsAsArray['avatar'] = null; // TODO
            /** @var ContactEmail $contactEmail */
            foreach ($contact->getContactEmails() as $contactEmail) {
                $contactsAsArray['emails'][] = $contactEmail->getEmail();
            }
            /** @var ContactGroup $group */
            foreach ($contact->getGroups() as $group) {
                $contactsAsArray['groups'][] = $group->getName();
            }
            $result['rows'][] = $contactsAsArray;
        }
        $result['total'] = (int)$contactRepo->countUserContacts($user, $search);
        return $result;
    }

    /**
     * Ajoute aux contacts d'un utilisateur un autre utilisateur
     * @param AccountUser $owner L'utilisateur a qui ajouter le contact
     * @param int|string $userLinkedId L'identifiant ou EMAIL de l'utilisateur à ajouter
     * @return array|null [$id => Contact]|Contact
     * @deprecated
     */
    public function addContact(AccountUser $owner, $userLinkedId)
    {
        return null;
        if (is_array($userLinkedId)) {
            $result = array();
            foreach ($userLinkedId as $id) {
                array_merge($result, $this->addContact($owner, $id));
            }
            return $result;
        } else {
            // TODO Revoir la gestion des contacts => ApplicationUser + AppUserEmail
            if (is_numeric($userLinkedId)) {
                $userLinked = $this->userManager->findUserBy(array('id' => $userLinkedId));
            } else {
                $userLinked = $this->userManager->findUserByEmail($userLinkedId);
                if (false && $userLinked == null) {
                    $userLinked = $this->userManager->createUserFromEmail($userLinkedId);
                }
            }

            if ($userLinked != null && $owner != $userLinked) {
                $contactRepository = $this->entityManager->getRepository("ATUserBundle:Contact");
                /** @var Contact $existingContact */
                $existingContact = $contactRepository->findOneBy(array('owner' => $owner, 'linked' => $userLinked));
                if ($existingContact != null) {
                    if ($existingContact->getStatus() != ContactStatus::VALID) {
                        $existingContact->setStatus(ContactStatus::VALID);
                        $this->entityManager->persist($existingContact);
                    } else {
                        return [$userLinkedId => null];
                    }
                } else {
                    $existingContact = new Contact();
                    $existingContact->addLinked($userLinked->getApplicationUser());
                    $existingContact->setStatus(ContactStatus::VALID);
                    $owner->getApplicationUser()->addContact($existingContact);
                    $this->entityManager->persist($owner);

                    /* TODO Envoi d'email ?
                    if (!$userLinked->getContactsListAsUser(false)->contains($owner)) {
                        // Si le contact a déjà l'utilisateur dans ses contacts, pas d'envoi d'email
                        //$this->emailManager->envoiNotificationAjoutAuxContacts($owner, $userLinked);
                    }*/
                }
                $this->entityManager->flush();
                return [$userLinkedId => $existingContact];
            }
            return [$userLinkedId => null];
        }
    }

    /**
     * Supprime un Contact à l'utilisateur connecté : passe à l'état ContactStatus::DELETED
     * @param AccountUser $userConnected L'utilisateur connecté à qui supprimer le contact
     * @param int $contactId Id du contact à supprimer
     * @return bool true si l'opréation a réussi, false sinon
     */
    public function removeContact(AccountUser $userConnected, $contactId)
    {
        $this->retrieveContact($contactId);
        if ($this->contact != null && $this->contact->getOwner() == $userConnected->getApplicationUser()) {
            if ($this->contact->getStatus() != ContactStatus::DELETED) {
                $this->contact->setStatus(ContactStatus::DELETED);
                foreach($this->contact->getGroups() as $group){
                    $this->contact->removeGroup($group);
                }
                $this->entityManager->persist($this->contact);
                $this->entityManager->flush();
                return true;
            }
        }
        return false;
    }
}