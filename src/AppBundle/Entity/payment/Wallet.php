<?php

namespace AppBundle\Entity\payment;

use AppBundle\Entity\ModuleInvitation;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Wallet
 *
 * Le Wallet reference un e-wallet MangoPay, il contient toute les infos de PayIn (ajout d'argent sur la plateforme),
 * PayOut (retrait d'argent de la plateforme), Transfer (transfer d'argent d'un utilisateur à un autre), etc...
 *
 * @ORM\Table(name="wallet")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\payment\WalletRepository")
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
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * Montant en CENTIMES disponible pour le payout
     * Comprend uniquement le somme recue, destinées à être retirée.
     *
     * @var int
     *
     * @ORM\Column(name="payout_amount", type="integer")
     */
    private $payOutAmount;

    /**************************************************************************************************************
     *                                      Jointures
     **************************************************************************************************************/

    /**
     * @ORM\OneToOne(targetEntity="\AppBundle\Entity\ModuleInvitation", inversedBy="wallet")
     * @ORM\JoinColumn(name="wallet_id", referencedColumnName="id")
     *
     * @var ModuleInvitation
     */
    private $moduleInvitation;
    
    /**
     * Wallet sur lequel l'argent a été/sera retiré
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\payment\Transaction", mappedBy="debited", cascade={"persist"})
     */
    private $debitedTransfers;

    /**
     * Wallet sur lequel l'argent a été/sera ajouté
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="\AppBundle\Entity\payment\Transaction", mappedBy="credited", cascade={"persist"})
     */
    private $creditedTransfers;

    /*
     * Stocker un tableau en bdd ((pour les listes d'id) ou pas, faut peut etre avoir un status dessus:
     *
     * http://fr.php.net/manual/fr/function.implode.php
     *http://fr.php.net/manual/fr/function.explode.php
     *
     */

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
     * Constructor
     */
    public function __construct()
    {
        $this->debitedTransfers = new ArrayCollection();
        $this->creditedTransfers = new ArrayCollection();
    }

    /**
     * Set mangoPayEWalletId
     *
     * @param string $mangoPayEWalletId
     *
     * @return Wallet
     */
    public function setMangoPayEWalletId($mangoPayEWalletId)
    {
        $this->mangoPayEWalletId = $mangoPayEWalletId;

        return $this;
    }

    /**
     * Get mangoPayEWalletId
     *
     * @return string
     */
    public function getMangoPayEWalletId()
    {
        return $this->mangoPayEWalletId;
    }

    /**
     * Set amount
     *
     * @param integer $amount
     *
     * @return Wallet
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set payOutAmount
     *
     * @param integer $payOutAmount
     *
     * @return Wallet
     */
    public function setPayOutAmount($payOutAmount)
    {
        $this->payOutAmount = $payOutAmount;

        return $this;
    }

    /**
     * Get payOutAmount
     *
     * @return integer
     */
    public function getPayOutAmount()
    {
        return $this->payOutAmount;
    }

    /**
     * Set moduleInvitation
     *
     * @param ModuleInvitation $moduleInvitation
     *
     * @return Wallet
     */
    public function setModuleInvitation(ModuleInvitation $moduleInvitation = null)
    {
        $this->moduleInvitation = $moduleInvitation;

        return $this;
    }

    /**
     * Get moduleInvitation
     *
     * @return \AppBundle\Entity\ModuleInvitation
     */
    public function getModuleInvitation()
    {
        return $this->moduleInvitation;
    }

    /**
     * Add debitedTransfer
     *
     * @param Transaction $debitedTransfer
     *
     * @return Wallet
     */
    public function addDebitedTransfer(Transaction $debitedTransfer)
    {
        $this->debitedTransfers[] = $debitedTransfer;

        return $this;
    }

    /**
     * Remove debitedTransfer
     *
     * @param Transaction $debitedTransfer
     */
    public function removeDebitedTransfer(Transaction $debitedTransfer)
    {
        $this->debitedTransfers->removeElement($debitedTransfer);
    }

    /**
     * Get debitedTransfers
     *
     * @return ArrayCollection
     */
    public function getDebitedTransfers()
    {
        return $this->debitedTransfers;
    }

    /**
     * Add creditedTransfer
     *
     * @param Transaction $creditedTransfer
     *
     * @return Wallet
     */
    public function addCreditedTransfer(Transaction $creditedTransfer)
    {
        $this->creditedTransfers[] = $creditedTransfer;

        return $this;
    }

    /**
     * Remove creditedTransfer
     *
     * @param Transaction $creditedTransfer
     */
    public function removeCreditedTransfer(Transaction $creditedTransfer)
    {
        $this->creditedTransfers->removeElement($creditedTransfer);
    }

    /**
     * Get creditedTransfers
     *
     * @return ArrayCollection
     */
    public function getCreditedTransfers()
    {
        return $this->creditedTransfers;
    }
}
