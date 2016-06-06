<?php

namespace AppBundle\Entity\module;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProposalElement
 *
 * @ORM\Table(name="proposal_element")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\ProposalElementRepository")
 */
class ProposalElement
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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
