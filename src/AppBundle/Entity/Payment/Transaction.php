<?php

namespace AppBundle\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Transaction
 *
 * Une transaction référence un "transfer" mangoPay, c'est le passage d'un Wallet à un autre.
 * On stockera ici tous les transfers, qu'ils soient fait, ou à faire.
 * Par exemple :
 * - Dans le cas ou le destinataire à un Wallet MangoPay de créer on transferera directement les fonds dessus
 * - Si le Wallet n'est pas crée (cas d'une cagnotte par exemple) chaque transfer sera enregistré chez nous, tagué "à faire"
 * et sera effectué au moment de la création du wallet du destinataire.
 *
 * @ORM\Table(name="payment_transaction")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Payment\TransactionRepository")
 */
class Transaction
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
     * Montant de la transaction en CENTIMES
     * @var int
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * Reference l'ID d'un transfer mangopay, peut être null si le transfer n'a pas eu lieu et est "à faire"
     *
     * @var string
     * @ORM\Column(name="mangopay_transfer_id", type="string", length=255, nullable=true)
     */
    private $mangoPayTransferId;

    /**
     * Status du transfer :
     *  - DONE : transfer effectué et réussi
     *  - TO_DO : transfer à faire ultérieurement (Moment du transfer conditionné par l'existance d'un e-wallet cible et par la configuration du paiement module)
     *  - FAILED : essai de transfer qui a échoué.
     *  - CANCELLED :transfert annulé
     *
     * @var string
     * @ORM\Column(name="status", type="enum_transaction_status")
     */
    private $status;

    /**
     * @var DateTime
     * @ORM\Column(name="payment_date", type="datetime", nullable=true)
     */
    private $paymentDate;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var PaymentModule
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Payment\PaymentModule", inversedBy="transactions")
     * @ORM\JoinColumn(name="payment_module_id", referencedColumnName="id")
     */
    private $paymentModule;

    /**
     * @var Wallet
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Payment\Wallet", inversedBy="debitedTransactions")
     * @ORM\JoinColumn(name="debited_wallet_id", referencedColumnName="id")
     */
    private $debited;

    /**
     * @var Wallet
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Payment\Wallet", inversedBy="creditedTransactions")
     * @ORM\JoinColumn(name="credited_wallet_id", referencedColumnName="id")
     */
    private $credited;


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
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Transaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getMangoPayTransferId()
    {
        return $this->mangoPayTransferId;
    }

    /**
     * @param string $mangoPayTransferId
     * @return Transaction
     */
    public function setMangoPayTransferId($mangoPayTransferId)
    {
        $this->mangoPayTransferId = $mangoPayTransferId;
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
     * @return Transaction
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * @param DateTime $paymentDate
     * @return Transaction
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }


    /**
     * @return PaymentModule
     */
    public function getPaymentModule()
    {
        return $this->paymentModule;
    }

    /**
     * @param PaymentModule $paymentModule
     * @return Transaction
     */
    public function setPaymentModule($paymentModule)
    {
        $this->paymentModule = $paymentModule;
        return $this;
    }

    /**
     * @return Wallet
     */
    public function getDebited()
    {
        return $this->debited;
    }

    /**
     * @param Wallet $debited
     * @return Transaction
     */
    public function setDebited($debited)
    {
        $this->debited = $debited;
        return $this;
    }

    /**
     * @return Wallet
     */
    public function getCredited()
    {
        return $this->credited;
    }

    /**
     * @param Wallet $credited
     * @return Transaction
     */
    public function setCredited($credited)
    {
        $this->credited = $credited;
        return $this;
    }
}
