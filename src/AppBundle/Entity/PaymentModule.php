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
     * Actions sur lesquelles sont appliquées les frais :
     *  - PAYIN : frais appliqués au moment du credit duu e-wallet
     *  - PAYOUT : frais appliqués au moment du retrait de l'argent
     *  - TRANSFER : frais appliqué au moment du transfert de l'argent d'un e-wallet à un autre
     *
     * @var string
     *
     * @ORM\Column(name="fees_application", type="string", length=255, nullable=true)
     */
    private $feesApplication;

    /**
     * Moment ou sont declanchées les transactions pour ce module
     *  - IMMEDIATE : la transaction est effectuée dès qu'elle est créée (sauf si le wallet a crediter n'a pas de e-wallet mangopay)
     *  - ONDEMAND : les transactions sont effectuées à la demande
     *
     * @var string
     *
     * @ORM\Column(name="transaction_trigger", type="string", length=255, nullable=true)
     */
    private $transactionTrigger;


    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * PaymentModule constructor.
     *
     */
    public function __construct()
    {
        $this->feesApplication = "TRANSFER";
        $this->transactionTrigger = "IMMEDIATE";
    }

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
     * Set feesApplication
     *
     * @param string $feesApplication
     *
     * @return PaymentModule
     */
    public function setFeesApplication($feesApplication)
    {
        $this->feesApplication = $feesApplication;

        return $this;
    }

    /**
     * Get feesApplication
     *
     * @return string
     */
    public function getFeesApplication()
    {
        return $this->feesApplication;
    }

    /**
     * Set transactionTrigger
     *
     * @param string $transactionTrigger
     *
     * @return PaymentModule
     */
    public function setTransactionTrigger($transactionTrigger)
    {
        $this->transactionTrigger = $transactionTrigger;

        return $this;
    }

    /**
     * Get transactionTrigger
     *
     * @return string
     */
    public function getTransactionTrigger()
    {
        return $this->transactionTrigger;
    }
}
