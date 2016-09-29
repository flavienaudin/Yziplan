<?php

namespace AppBundle\Entity\Payment;

use AppBundle\Entity\Event\Module;
use AppBundle\Utils\enum\FeeApplication;
use AppBundle\Utils\enum\TransactionTrigger;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * PaymentModule
 *
 * Contient tous les element relatif aux paiement dans un module.
 *
 * @ORM\Table(name="payment_payment_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Payment\PaymentModuleRepository")
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
     * Actions sur lesquelles sont appliquées les frais :
     *  - PAYIN : frais appliqués au moment du credit duu e-wallet
     *  - PAYOUT : frais appliqués au moment du retrait de l'argent
     *  - TRANSFER : frais appliqué au moment du transfert de l'argent d'un e-wallet à un autre
     *
     * @var string     *
     * @ORM\Column(name="fees_application", type="enum_payment_feeapplication", nullable=true)
     */
    private $feesApplication;

    /**
     * Moment ou sont declanchées les transactions pour ce module
     *  - IMMEDIATE : la transaction est effectuée dès qu'elle est créée (sauf si le wallet a crediter n'a pas de e-wallet mangopay)
     *  - AT_THE-END : les transactions sont effectuées à la fin
     *  - ON_DEMAND : les transactions sont effectuées à la demande
     *
     * @var string
     * @ORM\Column(name="transaction_trigger", type="enum_payment_transactiontrigger", nullable=true)
     */
    private $transactionTrigger;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Module
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event\Module", inversedBy="paymentModule")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     */
    private $module;

    /**
     * @var Transaction
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Payment\Transaction", mappedBy="paymentModule")
     */
    private $transactions;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->feesApplication = FeeApplication::TRANSACTION;
        $this->transactionTrigger = TransactionTrigger::IMMEDIATE;
    }


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
    public function getFeesApplication()
    {
        return $this->feesApplication;
    }

    /**
     * @param string $feesApplication
     * @return PaymentModule
     */
    public function setFeesApplication($feesApplication)
    {
        $this->feesApplication = $feesApplication;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionTrigger()
    {
        return $this->transactionTrigger;
    }

    /**
     * @param string $transactionTrigger
     * @return PaymentModule
     */
    public function setTransactionTrigger($transactionTrigger)
    {
        $this->transactionTrigger = $transactionTrigger;
        return $this;
    }

    /**
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param Module $module
     * @return PaymentModule
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * @return Transaction
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    /**
     * @param Transaction $transaction
     * @return PaymentModule
     */
    public function addTransaction(Transaction $transaction)
    {
        $this->transactions[] = $transaction;
        $transaction->setPaymentModule($this);
        return $this;
    }

    /**
     * @param Transaction $transaction
     */
    public function removeTransaction(Transaction $transaction)
    {
        $this->transactions->removeElement($transaction);
    }

}
