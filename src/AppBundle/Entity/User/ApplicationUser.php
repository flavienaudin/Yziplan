<?php

namespace AppBundle\Entity\User;

use AppBundle\Entity\Event\EventInvitation;
use AppBundle\Utils\enum\AppUserStatus;
use ATUserBundle\Entity\AccountUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ApplicationUser
 *
 * Classe représentant un utilisateur dans l'application. Celui-ci n'est pas forcément connecté
 *
 * @ORM\Table(name="user_application_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User\ApplicationUserRepository")
 */
class ApplicationUser
{
    /** Active les timestamps automatiques pour la creation et la mise a jour */
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="enum_appuser_status")
     */
    private $status = AppUserStatus::MAIN;

    /**
     * @var string
     *
     * @ORM\Column(name="mangoPayId", type="string", length=255, nullable=true, unique=true)
     */
    private $mangoPayId;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var AccountUser
     * @ORM\OneToOne(targetEntity="ATUserBundle\Entity\AccountUser", inversedBy="applicationUser")
     */
    private $accountUser;

    /**
     * @var AppUserInformation     *
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\User\AppUserInformation", inversedBy="applicationUser", cascade={"persist"})
     * @ORM\JoinColumn(name="app_user_information_id", referencedColumnName="id")
     */
    private $appUserInformation;

    /**
     * @var ArrayCollection of EventInvitation
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Event\EventInvitation", mappedBy="applicationUser")
     */
    private $eventInvitations;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User\AppUserEmail", mappedBy="applicationUser", cascade={"persist"})
     */
    private $appUserEmails;

    /**
     * @var ArrayCollection of Contact
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User\Contact", mappedBy="owner")
     */
    private $contacts;

    /**
     * @var ArrayCollection of ContactGroup
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\User\ContactGroup", mappedBy="owner")
     */
    private $contactGroups;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->appUserInformation = new AppUserInformation();
        $this->appUserInformation->setApplicationUser($this);

        $this->eventInvitations = new ArrayCollection();
        $this->appUserEmails = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->contactGroups = new ArrayCollection();
    }


    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get mangoPayId
     *
     * @return string
     */
    public function getMangoPayId()
    {
        return $this->mangoPayId;
    }

    /**
     * Set mangoPayId
     *
     * @param string $mangoPayId
     *
     * @return ApplicationUser
     */
    public function setMangoPayId($mangoPayId)
    {
        $this->mangoPayId = $mangoPayId;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ApplicationUser
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return AccountUser
     */
    public function getAccountUser()
    {
        return $this->accountUser;
    }

    /**
     * @param AccountUser $accountUser
     * @return ApplicationUser
     */
    public function setAccountUser($accountUser)
    {
        $this->accountUser = $accountUser;
        return $this;
    }

    /**
     * @return AppUserInformation
     */
    public function getAppUserInformation()
    {
        return $this->appUserInformation;
    }

    /**
     * @param AppUserInformation $appUserInformation
     * @return ApplicationUser
     */
    public function setAppUserInformation(AppUserInformation $appUserInformation)
    {
        $this->appUserInformation = $appUserInformation;
        $this->appUserInformation->setApplicationUser($this);
        return $this;
    }

    /**
     * Get eventInvitations
     *
     * @return Collection
     */
    public function getEventInvitations()
    {
        return $this->eventInvitations;
    }

    /**
     * Add eventInvitation
     *
     * @param EventInvitation $eventInvitation
     *
     * @return ApplicationUser
     */
    public function addEventInvitation(EventInvitation $eventInvitation)
    {
        $this->eventInvitations[] = $eventInvitation;
        $eventInvitation->setApplicationUser($this);
        return $this;
    }

    /**
     * Remove eventInvitation.
     * WARNING : The best is to use instead EventInvitation.setStatus(EventInviationsStatus::CANCELLED)
     *
     * @param EventInvitation $eventInvitation
     */
    public function removeEventInvitation(EventInvitation $eventInvitation)
    {
        $this->eventInvitations->removeElement($eventInvitation);
    }

    /**
     * @return ArrayCollection
     */
    public function getAppUserEmails()
    {
        return $this->appUserEmails;
    }

    /**
     * Add appUserEmail
     *
     * @param AppUserEmail $appUserEmail
     * @return ApplicationUser
     */
    public function addAppUserEmail($appUserEmail)
    {
        if (!$this->appUserEmails->contains($appUserEmail)) {
            $this->appUserEmails[] = $appUserEmail;
            $appUserEmail->setApplicationUser($this);
        }
        return $this;
    }

    /**
     * Remove appUserEmail
     *
     * @param AppUserEmail $appUserEmail
     */
    public function removeAppUserEmail(AppUserEmail $appUserEmail)
    {
        $this->appUserEmails->removeElement($appUserEmail);
    }

    /**
     * @return ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @param Contact $contact
     * @return ApplicationUser
     */
    public function addContact(Contact $contact)
    {
        $this->contacts[] = $contact;
        $contact->setOwner($this);
        return $this;
    }

    /**
     * @param Contact $contact
     */
    public function removeContact(Contact $contact)
    {
        $this->contacts->removeElement($contact);
    }


    /**
     * @return ArrayCollection
     */
    public function getContactGroups()
    {
        return $this->contactGroups;
    }

    /**
     * @param ContactGroup $contactGroup
     * @return ApplicationUser
     */
    public function addContactGroup(ContactGroup $contactGroup)
    {
        $this->contactGroups[] = $contactGroup;
        $contactGroup->setOwner($this);
        return $this;
    }

    /**
     * @param ContactGroup $contactGroup
     */
    public function removeContactGroup(ContactGroup $contactGroup)
    {
        $this->contactGroups->removeElement($contactGroup);
    }

    /***********************************************************************
     *                      Helpers
     ***********************************************************************/
    public function getDisplayableName()
    {
        return $this->appUserInformation->getDisplayableName();
    }
}

