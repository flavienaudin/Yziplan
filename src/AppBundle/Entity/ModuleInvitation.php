<?php

namespace AppBundle\Entity;

use AppBundle\Entity\module\ProposalElementResponse;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ModuleInvitation
 *
 * @ORM\Table(name="module_invitation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModuleInvitationRepository")
 */
class ModuleInvitation
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Module
     *
     * @ORM\ManyToOne(targetEntity="Module", inversedBy="moduleInvitations")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     */
    private $module;

    /**
     * @var EventInvitation
     *
     * @ORM\ManyToOne(targetEntity="EventInvitation", inversedBy="modulesInvitations")
     * @ORM\JoinColumn(name="event_invitation_id", referencedColumnName="id")
     */
    private $eventInvitation;


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
     * Set name
     *
     * @param string $name
     *
     * @return ModuleInvitation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set module
     *
     * @param \AppBundle\Entity\Module $module
     *
     * @return ModuleInvitation
     */
    public function setModule(\AppBundle\Entity\Module $module = null)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get module
     *
     * @return \AppBundle\Entity\Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set eventInvitation
     *
     * @param \AppBundle\Entity\EventInvitation $eventInvitation
     *
     * @return ModuleInvitation
     */
    public function setEventInvitation(\AppBundle\Entity\EventInvitation $eventInvitation = null)
    {
        $this->eventInvitation = $eventInvitation;

        return $this;
    }

    /**
     * Get eventInvitation
     *
     * @return \AppBundle\Entity\EventInvitation
     */
    public function getEventInvitation()
    {
        return $this->eventInvitation;
    }
}
