<?php

namespace AppBundle\Entity\Module;

use AppBundle\Entity\Event\ModuleInvitation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ExpenseElement
 *
 * @ORM\Table(name="module_expense_element")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\ExpenseElementRepository")
 */
class ExpenseElement
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
     * @var float
     * @ORM\Column(name="amount", type="decimal")
     */
    private $amount = 0;

    /**
     * @var \DateTime
     * @ORM\Column(name="when", type="datetime", nullable=true)
     */
    private $when;

    /**
     * @var string
     * @ORM\Column(name="where", type="string", length=255, nullable=true)
     */
    private $where;

    /**
     * @var string
     * @ORM\Column(name="where_google_place_id", type="string", length=255, nullable=true)
     */
    private $whereGooglePlaceId;

    /**
     * @var string
     * @ORM\Column(name="comment", type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @var string
     * @ORM\Column(name="type", type="enum_expenseelement_type")
     */
    private $type;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * Personne ayant ajouté la depense
     *
     * @var ModuleInvitation
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\ModuleInvitation", inversedBy="expenseElementsCreated")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    private $creator;

    /**
     * Personne ayant payé
     *
     * @var ModuleInvitation
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\ModuleInvitation", inversedBy="expenseElementsPayed")
     * @ORM\JoinColumn(name="payer_id", referencedColumnName="id")
     */
    private $payer;

    /**
     * Personnes concernées par la déponse
     *
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Event\ModuleInvitation", inversedBy="expenseElementsDue")
     * @ORM\JoinTable(name="module_expenseElements_beneficaries")
     */
    private $beneficiaries;

    /**
     * @var ExpenseModule
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Module\ExpenseModule", inversedBy="expenseElements")
     * @ORM\JoinColumn(name="expense_module_id", referencedColumnName="id")
     */
    private $expenseModule;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->beneficiaries = new ArrayCollection();
    }


    /***********************************************************************
     *                      Getters and Setters
     ***********************************************************************/

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     * @return ExpenseElement
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getWhen()
    {
        return $this->when;
    }

    /**
     * @param \DateTime $when
     * @return ExpenseElement
     */
    public function setWhen($when)
    {
        $this->when = $when;
        return $this;
    }

    /**
     * @return string
     */
    public function getWhere()
    {
        return $this->where;
    }

    /**
     * @param string $where
     * @return ExpenseElement
     */
    public function setWhere($where)
    {
        $this->where = $where;
        return $this;
    }

    /**
     * @return string
     */
    public function getWhereGooglePlaceId()
    {
        return $this->whereGooglePlaceId;
    }

    /**
     * @param string $whereGooglePlaceId
     * @return ExpenseElement
     */
    public function setWhereGooglePlaceId($whereGooglePlaceId)
    {
        $this->whereGooglePlaceId = $whereGooglePlaceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return ExpenseElement
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return ExpenseElement
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return ModuleInvitation
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param ModuleInvitation $creator
     * @return ExpenseElement
     */
    public function setCreator(ModuleInvitation $creator)
    {
        $this->creator = $creator;
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
     * @return ExpenseElement
     */
    public function setPayer(ModuleInvitation $payer)
    {
        $this->payer = $payer;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getBeneficiaries()
    {
        return $this->beneficiaries;
    }

    /**
     * @param ModuleInvitation $beneficiary
     * @return ExpenseElement
     */
    public function addBeneficiarie(ModuleInvitation $beneficiary)
    {
        $this->beneficiaries[] = $beneficiary;
        $beneficiary->addExpenseElementDue($this);
        return $this;
    }

    /**
     * @return ExpenseModule
     */
    public function getExpenseModule()
    {
        return $this->expenseModule;
    }

    /**
     * @param ExpenseModule $expenseModule
     * @return ExpenseElement
     */
    public function setExpenseModule($expenseModule)
    {
        $this->expenseModule = $expenseModule;
        return $this;
    }
}
