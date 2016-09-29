<?php

namespace AppBundle\Entity\Payment;


use AppBundle\Entity\Event\EventInvitation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Wallet
 *
 * Le Wallet reference un e-wallet MangoPay, il contient toute les infos de PayIn (ajout d'argent sur la plateforme),
 * PayOut (retrait d'argent de la plateforme), Transaction (transfert d'argent d'un utilisateur à un autre), etc...
 *
 * @ORM\Table(name="payment_wallet")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Payment\WalletRepository")
 */
class Wallet
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
     * Reference l'ID d'un e-wallet mangopay
     * Peut-être null si le e-wallet mangopay n'est pas encore créé, dans ce cas les transfer d'argent seront en attente.
     *
     * @var string
     *
     * @ORM\Column(name="mangopay_ewallet_id", type="string", length=255, nullable=true)
     */
    private $mangoPayEWalletId;

    /**
     * Montant total disponible sur le e-wallet en CENTIMES
     * Comprend l'argent en attente de transfer et l'argent recu destiné au payout.
     *
     * @var float
     * @ORM\Column(name="amount", type="decimal")
     */
    private $eWalletAmount = 0;

    /**
     * Montant en CENTIMES disponible pour le payout
     * Comprend uniquement le somme recue, destinées à être retirée.
     *
     * @var float
     * @ORM\Column(name="payout_amount", type="decimal")
     */
    private $eWalletPayOutAmount = 0;



    /* TODO  Stockage  ??
    private $payInId;
    private $payOutId;
    private $payRefundId;
    */

    /**************************************************************************************************************
     *                                      Jointures
     **************************************************************************************************************/

    /**
     * @var EventInvitation
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event\EventInvitation", inversedBy="wallet")
     * @ORM\JoinColumn(name="wallet_id", referencedColumnName="id")
     */
    private $eventInvitation;
    
    /**
     * Wallet sur lequel l'argent a été/sera retiré
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Payment\Transaction", mappedBy="debited", cascade={"persist"})
     */
    private $debitedTransactions;

    /**
     * Wallet sur lequel l'argent a été/sera ajouté
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Payment\Transaction", mappedBy="credited", cascade={"persist"})
     */
    private $creditedTransactions;

    /*
     * Stocker un tableau en bdd ((pour les listes d'id) ou pas, faut peut etre avoir un status dessus:
     *
     * http://fr.php.net/manual/fr/function.implode.php
     *http://fr.php.net/manual/fr/function.explode.php
     *
     */


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->debitedTransactions = new ArrayCollection();
        $this->creditedTransactions = new ArrayCollection();
    }


    /**************************************************************************************************************
     *                                      Getter and Setter
     **************************************************************************************************************/

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
    public function getMangoPayEWalletId()
    {
        return $this->mangoPayEWalletId;
    }

    /**
     * @param string $mangoPayEWalletId
     * @return Wallet
     */
    public function setMangoPayEWalletId($mangoPayEWalletId)
    {
        $this->mangoPayEWalletId = $mangoPayEWalletId;
        return $this;
    }

    /**
     * @return int
     */
    public function getEWalletAmount()
    {
        return $this->eWalletAmount;
    }

    /**
     * @param int $eWalletAmount
     * @return Wallet
     */
    public function setEWalletAmount($eWalletAmount)
    {
        $this->eWalletAmount = $eWalletAmount;
        return $this;
    }

    /**
     * @return int
     */
    public function getEWalletPayOutAmount()
    {
        return $this->eWalletPayOutAmount;
    }

    /**
     * @param int $eWalletPayOutAmount
     * @return Wallet
     */
    public function setEWalletPayOutAmount($eWalletPayOutAmount)
    {
        $this->eWalletPayOutAmount = $eWalletPayOutAmount;
        return $this;
    }

    /**
     * @return EventInvitation
     */
    public function getEventInvitation()
    {
        return $this->eventInvitation;
    }

    /**
     * @param EventInvitation $eventInvitation
     * @return Wallet
     */
    public function setEventInvitation($eventInvitation)
    {
        $this->eventInvitation = $eventInvitation;
        return $this;
    }

    /**
     * Get debitedTransaction
     *
     * @return ArrayCollection
     */
    public function getDebitedTransactions()
    {
        return $this->debitedTransactions;
    }

    /**
     * Add debitedTransaction
     *
     * @param Transaction $debitedTransaction
     *
     * @return Wallet
     */
    public function addDebitedTransaction(Transaction $debitedTransaction)
    {
        $this->debitedTransactions[] = $debitedTransaction;
        $debitedTransaction->setDebited($this);
        return $this;
    }

    /**
     * Remove debitedTransaction
     *
     * @param Transaction $debitedTransaction
     */
    public function removeDebitedTransaction(Transaction $debitedTransaction)
    {
        $this->debitedTransactions->removeElement($debitedTransaction);
    }

    /**
     * Get creditedTransactions
     *
     * @return ArrayCollection
     */
    public function getCreditedTransactions()
    {
        return $this->creditedTransactions;
    }

    /**
     * Add creditedTransaction
     *
     * @param Transaction $creditedTransaction
     * @return Wallet
     */
    public function addCreditedTransaction(Transaction $creditedTransaction)
    {
        $this->creditedTransactions[] = $creditedTransaction;
        $creditedTransaction->setCredited($this);
        return $this;
    }

    /**
     * Remove creditedTransaction
     *
     * @param Transaction $creditedTransaction
     */
    public function removeCreditedTransaction(Transaction $creditedTransaction)
    {
        $this->creditedTransactions->removeElement($creditedTransaction);
    }
}
