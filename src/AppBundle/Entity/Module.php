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

    /*******************************************************************************************************
     *                                Type de module
     ********************************************************************************************************/

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\module\PollModule", mappedBy="module")
     */
    private $pollModules;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\module\ExpenseModule", mappedBy="module")
     */
    private $expenseModules;

    /*******************************************************************************************************
     *                                Getter and Setter
     ********************************************************************************************************/
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->moduleInvitations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pollModules = new \Doctrine\Common\Collections\ArrayCollection();
        $this->expenseModules = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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

    /**
     * Add pollModule
     *
     * @param \AppBundle\Entity\module\PollModule $pollModule
     *
     * @return Module
     */
    public function addPollModule(\AppBundle\Entity\module\PollModule $pollModule)
    {
        $this->pollModules[] = $pollModule;

        return $this;
    }

    /**
     * Remove pollModule
     *
     * @param \AppBundle\Entity\module\PollModule $pollModule
     */
    public function removePollModule(\AppBundle\Entity\module\PollModule $pollModule)
    {
        $this->pollModules->removeElement($pollModule);
    }

    /**
     * Get pollModules
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPollModules()
    {
        return $this->pollModules;
    }

    /**
     * Add expenseModule
     *
     * @param \AppBundle\Entity\module\ExpenseModule $expenseModule
     *
     * @return Module
     */
    public function addExpenseModule(\AppBundle\Entity\module\ExpenseModule $expenseModule)
    {
        $this->expenseModules[] = $expenseModule;

        return $this;
    }

    /**
     * Remove expenseModule
     *
     * @param \AppBundle\Entity\module\ExpenseModule $expenseModule
     */
    public function removeExpenseModule(\AppBundle\Entity\module\ExpenseModule $expenseModule)
    {
        $this->expenseModules->removeElement($expenseModule);
    }

    /**
     * Get expenseModules
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExpenseModules()
    {
        return $this->expenseModules;
    }
}
