<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
}
