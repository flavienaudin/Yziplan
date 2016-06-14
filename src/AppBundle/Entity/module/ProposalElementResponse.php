<?php

namespace AppBundle\Entity\module;

use AppBundle\Entity\ModuleInvitation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * ProposalElementResponse
 *
 * @ORM\Table(name="proposal_element_response")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\ProposalElementResponseRepository")
 */
class ProposalElementResponse
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
     * @var String
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    private $value;

    /**
     * Contient les informations complémentaires, par exemple le placeId pour un lieu, le fuseau horaire pour une date, etc...
     *
     * @var String
     *
     * @ORM\Column(name="info1", type="string", length=255, nullable=true)
     */
    private $info1;


    /***********************************************************************
     *                      Jointures
     ***********************************************************************/

    /**
     * @var ProposalElement
     *
     * @ORM\ManyToOne(targetEntity="ProposalElement", inversedBy="proposalElementResponses")
     * @ORM\JoinColumn(name="proposal_element_id", referencedColumnName="id")
     */
    private $proposalElement;

    /**
     * @var ModuleInvitation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EventInvitation", inversedBy="proposalElementResponse")
     * @ORM\JoinColumn(name="module_invitation_id", referencedColumnName="id")
     */
    private $eventInvitation;

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
     * Set value
     *
     * @param string $value
     *
     * @return ProposalElementResponse
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get info1
     *
     * @return string
     */
    public function getInfo1()
    {
        return $this->info1;
    }

    /**
     * Set info1
     *
     * @param string $info1
     *
     * @return ProposalElementResponse
     */
    public function setInfo1($info1)
    {
        $this->info1 = $info1;

        return $this;
    }

    /**
     * Set proposalElement
     *
     * @param \AppBundle\Entity\module\ProposalElement $proposalElement
     *
     * @return ProposalElementResponse
     */
    public function setProposalElement(\AppBundle\Entity\module\ProposalElement $proposalElement = null)
    {
        $this->proposalElement = $proposalElement;

        return $this;
    }

    /**
     * Get proposalElement
     *
     * @return \AppBundle\Entity\module\ProposalElement
     */
    public function getProposalElement()
    {
        return $this->proposalElement;
    }

    /**
     * @return mixed
     */
    public function getModuleInvitation()
    {
        return $this->moduleInvitation;
    }

    /**
     * @param mixed $moduleInvitation
     * @return ProposalElementResponse
     */
    public function setModuleInvitation($moduleInvitation)
    {
        $this->moduleInvitation = $moduleInvitation;
        return $this;
    }

    /**
     * Set eventInvitation
     *
     * @param \AppBundle\Entity\EventInvitation $eventInvitation
     *
     * @return ProposalElementResponse
     */
    public function setEventInvitation(\AppBundle\Entity\EventInvitation $eventInvitation = null)
    {
        $this->eventInvitation = $eventInvitation;

        return $this;
    }

    /**
     * Get eventInvitation
     *
     * @return \AppBundle\Entity\EventInvitation
     */
    public function getEventInvitation()
    {
        return $this->eventInvitation;
    }
}
