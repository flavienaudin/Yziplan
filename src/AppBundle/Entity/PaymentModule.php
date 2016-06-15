<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * PaymentModule
 *
 * Contient tous les element relatif aux paiement dans un module.
 *
 * @ORM\Table(name="payment_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PaymentModuleRepository")
 */
class PaymentModule
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
     * @ORM\OneToOne(targetEntity="Module", inversedBy="paymentModule")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     *
     * @var Module
     */
    private $module;

    /**
     * Emetteurs des paiements
     *
     * @var
     */
    private $emitter;

    /**
     * Destinataires des paiements
     *
     * @var
     */
    private $recipient;

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
     * Set module
     *
     * @param \AppBundle\Entity\Module $module
     *
     * @return PaymentModule
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
     * @return mixed
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    /**
     * @param mixed $emitter
     * @return PaymentModule
     */
    public function setEmitter($emitter)
    {
        $this->emitter = $emitter;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param mixed $recipient
     * @return PaymentModule
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

}
