<?php

namespace AppBundle\Entity\Module;

use AppBundle\Utils\enum\PollModuleType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * PollElement
 *
 * @ORM\Table(name="module_poll_element")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\PollElementRepository")
 */
class PollElement
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="enum_pollproposal_elementtype")
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="orderIndex", type="integer")
     */
    private $orderIndex;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var PollModule
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Module\PollModule", inversedBy="pollElements")
     * @ORM\JoinColumn(name="poll_module_id", referencedColumnName="id")
     */
    private $pollModule;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\PollProposalElement", mappedBy="pollElement", cascade={"persist"})
     */
    private $pollProposalElements;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->pollProposalElements = new ArrayCollection();
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return PollElement
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return PollElement
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderIndex()
    {
        return $this->orderIndex;
    }

    /**
     * @param int $orderIndex
     * @return PollElement
     */
    public function setOrderIndex($orderIndex)
    {
        $this->orderIndex = $orderIndex;
        return $this;
    }

    /**
     * @return PollModule
     */
    public function getPollModule()
    {
        return $this->pollModule;
    }

    /**
     * @param PollModule $pollModule
     * @return PollElement
     */
    public function setPollModule($pollModule)
    {
        $this->pollModule = $pollModule;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPollProposalElements()
    {
        return $this->pollProposalElements;
    }

    /**
     * @param PollProposalElement $pollProposalElement
     * @return PollElement
     */
    public function addPollProposalElement(PollProposalElement $pollProposalElement)
    {
        $this->pollProposalElements[] = $pollProposalElement;
        $pollProposalElement->setPollElement($this);
        return $this;
    }

    /**
     * @param PollProposalElement $pollProposalElement
     */
    public function removePollProposalElement(PollProposalElement $pollProposalElement)
    {
        $this->pollProposalElements->removeElement($pollProposalElement);
    }

}

