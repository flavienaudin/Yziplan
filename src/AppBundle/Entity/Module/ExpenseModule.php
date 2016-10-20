<?php

namespace AppBundle\Entity\Module;

use AppBundle\Entity\Event\Module;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ExpenseModule
 *
 * @ORM\Table(name="module_expense_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\ExpenseModuleRepository")
 */
class ExpenseModule
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


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var Module
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Event\Module", inversedBy="expenseModule")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     */
    private $module;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\ExpenseElement", mappedBy="expenseModule", cascade={"persist"})
     */
    private $expenseElements;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->expenseElements = new ArrayCollection();
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
     * @return Module
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param mixed $module
     * @return ExpenseModule
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * Get expenseElements
     *
     * @return ArrayCollection
     */
    public function getExpenseElements()
    {
        return $this->expenseElements;
    }

    /**
     * Add expenseElement
     *
     * @param ExpenseElement $expenseElement
     *
     * @return ExpenseModule
     */
    public function addExpenseElement(ExpenseElement $expenseElement)
    {
        $this->expenseElements[] = $expenseElement;
        $expenseElement->setExpenseModule($this);
        return $this;
    }

    /**
     * Remove expenseElement
     *
     * @param ExpenseElement $expenseElement
     */
    public function removeExpenseElement(ExpenseElement $expenseElement)
    {
        $this->expenseElements->removeElement($expenseElement);
    }
}
