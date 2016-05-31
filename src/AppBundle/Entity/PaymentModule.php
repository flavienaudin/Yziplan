<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentModule
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
     * @OneToOne(targetEntity="Module", inversedBy="paymentModule")
     * @JoinColumn(name="module_id", referencedColumnName="id")
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

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}

