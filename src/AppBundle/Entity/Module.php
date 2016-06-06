<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Module
 *
 * @ORM\Table(name="module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ModuleRepository")
 */
class Module
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
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="modules")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     */
    private $event;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ModuleInvitation", mappedBy="module")
     */
    private $moduleInvitations;

    /**
     * @ORM\OneToOne(targetEntity="PaymentModule", mappedBy="module")
     * 
     * @var PaymentModule
     */
    private $paymentModule;

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
     * Constructor
     */
    public function __construct()
    {
        $this->moduleInvitations = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set event
     *
     * @param \AppBundle\Entity\Event $event
     *
     * @return Module
     */
    public function setEvent(\AppBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \AppBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Add moduleInvitation
     *
     * @param \AppBundle\Entity\ModuleInvitation $moduleInvitation
     *
     * @return Module
     */
    public function addModuleInvitation(\AppBundle\Entity\ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations[] = $moduleInvitation;

        return $this;
    }

    /**
     * Remove moduleInvitation
     *
     * @param \AppBundle\Entity\ModuleInvitation $moduleInvitation
     */
    public function removeModuleInvitation(\AppBundle\Entity\ModuleInvitation $moduleInvitation)
    {
        $this->moduleInvitations->removeElement($moduleInvitation);
    }

    /**
     * Get moduleInvitations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModuleInvitations()
    {
        return $this->moduleInvitations;
    }

    /**
     * Set paymentModule
     *
     * @param \AppBundle\Entity\PaymentModule $paymentModule
     *
     * @return Module
     */
    public function setPaymentModule(\AppBundle\Entity\PaymentModule $paymentModule = null)
    {
        $this->paymentModule = $paymentModule;

        return $this;
    }

    /**
     * Get paymentModule
     *
     * @return \AppBundle\Entity\PaymentModule
     */
    public function getPaymentModule()
    {
        return $this->paymentModule;
    }
}
