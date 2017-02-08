<?php

namespace AppBundle\Entity\Event;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventCoordinates
 *
 * @ORM\Table(name="event_coordinates")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Event\EventCoordinatesRepository")
 */
class EventCoordinates
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
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phoneNumber", type="string", length=31, nullable=true)
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="mobileNumber", type="string", length=31, nullable=true)
     */
    private $mobileNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="faxNumber", type="string", length=31, nullable=true)
     */
    private $faxNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="facebookURL", type="string", length=255, nullable=true)
     */
    private $facebookURL;

    /**
     * @var string
     *
     * @ORM\Column(name="googlePlusURL", type="string", length=255, nullable=true)
     */
    private $googlePlusURL;

    /**
     * @var string
     *
     * @ORM\Column(name="twitterURL", type="string", length=255, nullable=true)
     */
    private $twitterURL;

    /**
     * @var string
     *
     * @ORM\Column(name="instagramURL", type="string", length=255, nullable=true)
     */
    private $instagramURL;


    /**************************************************************************************************************
     *                                      Jointures
     **************************************************************************************************************/

    /**
     * @var Event
     *
s     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event\Event", mappedBy="coordinates")
     */
    private $event;

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
     * Set website
     *
     * @param string $website
     *
     * @return EventCoordinates
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return EventCoordinates
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     *
     * @return EventCoordinates
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set mobileNumber
     *
     * @param string $mobileNumber
     *
     * @return EventCoordinates
     */
    public function setMobileNumber($mobileNumber)
    {
        $this->mobileNumber = $mobileNumber;

        return $this;
    }

    /**
     * Get mobileNumber
     *
     * @return string
     */
    public function getMobileNumber()
    {
        return $this->mobileNumber;
    }

    /**
     * Set faxNumber
     *
     * @param string $faxNumber
     *
     * @return EventCoordinates
     */
    public function setFaxNumber($faxNumber)
    {
        $this->faxNumber = $faxNumber;

        return $this;
    }

    /**
     * Get faxNumber
     *
     * @return string
     */
    public function getFaxNumber()
    {
        return $this->faxNumber;
    }

    /**
     * Set facebookURL
     *
     * @param string $facebookURL
     *
     * @return EventCoordinates
     */
    public function setFacebookURL($facebookURL)
    {
        $this->facebookURL = $facebookURL;

        return $this;
    }

    /**
     * Get facebookURL
     *
     * @return string
     */
    public function getFacebookURL()
    {
        return $this->facebookURL;
    }

    /**
     * Set googlePlusURL
     *
     * @param string $googlePlusURL
     *
     * @return EventCoordinates
     */
    public function setGooglePlusURL($googlePlusURL)
    {
        $this->googlePlusURL = $googlePlusURL;

        return $this;
    }

    /**
     * Get googlePlusURL
     *
     * @return string
     */
    public function getGooglePlusURL()
    {
        return $this->googlePlusURL;
    }

    /**
     * Set twitterURL
     *
     * @param string $twitterURL
     *
     * @return EventCoordinates
     */
    public function setTwitterURL($twitterURL)
    {
        $this->twitterURL = $twitterURL;

        return $this;
    }

    /**
     * Get twitterURL
     *
     * @return string
     */
    public function getTwitterURL()
    {
        return $this->twitterURL;
    }

    /**
     * Set instagramURL
     *
     * @param string $instagramURL
     *
     * @return EventCoordinates
     */
    public function setInstagramURL($instagramURL)
    {
        $this->instagramURL = $instagramURL;

        return $this;
    }

    /**
     * Get instagramURL
     *
     * @return string
     */
    public function getInstagramURL()
    {
        return $this->instagramURL;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return EventCoordinates
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }
}

