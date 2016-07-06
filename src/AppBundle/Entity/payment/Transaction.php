<?php

namespace AppBundle\Entity\payment;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;

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
 * @ORM\Table(name="transaction")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\payment\TransactionRepository")
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
     * 
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * Reference l'ID d'un transfer mangopay, peut être null si le transfer n'a pas eu lieu et est "à faire"
     * 
     * @var string
     *
     * @ORM\Column(name="mangopay_transfer_id", type="string", length=255, nullable=true)
     */
    private $mangoPayTransferId;

    /**
     * Status du transfer :
     *  - SUCCESS : transfer effectué et réussi
     *  - TODO : transfer à faire ultérieurement (Moment du transfer conditionné par l'existance d'un e-wallet cible et par la configuration du paiement module)
     *  - FAILED : essai de transfer qui a échoué.
     *
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

    /**************************************************************************************************************
     *                                      Jointures
     **************************************************************************************************************/

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\payment\Wallet", inversedBy="debitedTransfers")
     * @ORM\JoinColumn(name="debited_wallet_id", referencedColumnName="id")
     *
     * @var Wallet
     */
    private $debited;

    /**
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\payment\Wallet", inversedBy="creditedTransfers")
     * @ORM\JoinColumn(name="credited_wallet_id", referencedColumnName="id")
     *
     * @var Wallet
     */
    private $credited;

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
     * Set amount
     *
     * @param integer $amount
     *
     * @return Transaction
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
     * Set mangoPayTransferId
     *
     * @param string $mangoPayTransferId
     *
     * @return Transaction
     */
    public function setMangoPayTransferId($mangoPayTransferId)
    {
        $this->mangoPayTransferId = $mangoPayTransferId;

        return $this;
    }

    /**
     * Get mangoPayTransferId
     *
     * @return string
     */
    public function getMangoPayTransferId()
    {
        return $this->mangoPayTransferId;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Transaction
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set debited
     *
     * @param Wallet $debited
     *
     * @return Transaction
     */
    public function setDebited(Wallet $debited = null)
    {
        $this->debited = $debited;

        return $this;
    }

    /**
     * Get debited
     *
     * @return Wallet
     */
    public function getDebited()
    {
        return $this->debited;
    }

    /**
     * Set credited
     *
     * @param \AppBundle\Entity\payment\Wallet $credited
     *
     * @return Transaction
     */
    public function setCredited(Wallet $credited = null)
    {
        $this->credited = $credited;

        return $this;
    }

    /**
     * Get credited
     *
     * @return Wallet
     */
    public function getCredited()
    {
        return $this->credited;
    }
}
