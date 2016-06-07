<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\Module as Module;
use Doctrine\ORM\Mapping as ORM;

/**
 * ExpenseModule
 *
 * @ORM\Table(name="expense_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\ExpenseModuleRepository")
 */
class ExpenseModule
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
     * @ORM\ManyToOne(targetEntity="\AppBundle\Entity\Module", inversedBy="expenseModules")
     * @ORM\JoinColumn(name="module_id", referencedColumnName="id")
     *
     * @var Module
     */
    private $module;
    

   

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
     * Set module
     *
     * @param \AppBundle\Entity\Module $module
     *
     * @return ExpenseModule
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
