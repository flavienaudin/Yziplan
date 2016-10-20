<?php
/**
 * Created by PhpStorm.
 * User: Flavien
 * Date: 14/09/2016
 * Time: 16:13
 */

namespace AppBundle\Manager;

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
     * Créer un contact à l'utilisateur
     * @param Contact $contact
     * @param AccountUser $user
     * @return Contact
     */
    public function addContact(Contact $contact, AccountUser $user)
    {
        $this->contact = $contact;
        $this->contact->setOwner($user->getApplicationUser());
        $this->contact->setStatus(ContactStatus::VALID);

        $this->entityManager->persist($this->contact);
        $this->entityManager->flush();
        return $this->contact;
    }

    /**
     * Update un contact à l'utilisateur
     * @param Contact $contact
     * @param AccountUser $user
     * @return Contact
     */
    public function updateContact(Contact $contact, AccountUser $user)
    {
        $this->contact = $contact;
        if ($this->contact->getOwner() == null) {
            $this->contact->setOwner($user->getApplicationUser());
        }
        $this->contact->setStatus(ContactStatus::VALID);
        $this->entityManager->persist($contact);
        $this->entityManager->flush();
        return $this->contact;
    }

    /**
     * Supprime un Contact à l'utilisateur connecté : passe à l'état ContactStatus::DELETED
     *
     * @param Contact $contact Optional the contact to remove from its owner
     * @return bool true si l'opréation a réussi, false sinon
     */
    public function removeContact(Contact $contact = null)
    {
        if ($contact != null) {
            $this->contact = $contact;
        }
        if ($this->contact != null && $this->contact->getStatus() != ContactStatus::DELETED) {
            $this->contact->setStatus(ContactStatus::DELETED);
            foreach ($this->contact->getGroups() as $group) {
                $this->contact->removeGroup($group);
            }
            $this->entityManager->persist($this->contact);
            $this->entityManager->flush();
            return true;
        }
        return false;
    }
}