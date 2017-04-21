<?php

namespace AppBundle\Entity\Module;

use AppBundle\Entity\Event\ModuleInvitation;
use Doctrine\ORM\Mapping as ORM;

/**
 * KittyParticipation
 *
 * @ORM\Table(name="module_kitty_participation")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\KittyParticipationRepository")
 */
class KittyParticipation
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
     * @var float
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var string
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var string
     * @ORM\Column(name="status", type="enum_kittymodule_objectivestatus")
     */
    private $status;

    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var KittyModule
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Module\KittyModule", inversedBy="participations")
     * @ORM\JoinColumn(name="poll_module_id", referencedColumnName="id")
     */
    private $kittyModule;

    /**
     * @var ModuleInvitation
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\ModuleInvitation", inversedBy="kittyParticipations")
     * @ORM\JoinColumn(name="payer_id", referencedColumnName="id")
     */
    private $payer;


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
     * Set amount
     *
     * @param float $amount
     *
     * @return KittyParticipation
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return KittyParticipation
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return KittyParticipation
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return KittyModule
     */
    public function getKittyModule()
    {
        return $this->kittyModule;
    }

    /**
     * @param KittyModule $kittyModule
     * @return KittyParticipation
     */
    public function setKittyModule($kittyModule)
    {
        $this->kittyModule = $kittyModule;
        return $this;
    }

    /**
     * @return ModuleInvitation
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * @param ModuleInvitation $payer
     * @return KittyParticipation
     */
    public function setPayer($payer)
    {
        $this->payer = $payer;
        return $this;
    }
}

