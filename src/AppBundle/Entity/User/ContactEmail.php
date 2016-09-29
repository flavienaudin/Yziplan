<?php

namespace AppBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContactEmail
 *
 * @ORM\Table(name="user_contact_email")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\User\ContactEmailRepository")
 */
class ContactEmail
{
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
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     * @ORM\Column(name="type", type="enum_contactinfo_type")
     */
    private $type;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Contact Contact auquel est associé l'email
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User\Contact", inversedBy="contactEmails" )
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
     */
    private $contact;

    /***********************************************************************
     *                      Constructor
     ***********************************************************************/

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
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return ContactEmail
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ContactEmail
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
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
     * @return ContactEmail
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
        return $this;
    }
}

