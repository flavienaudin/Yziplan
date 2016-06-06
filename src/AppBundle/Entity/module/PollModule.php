<?php

namespace AppBundle\Entity\module;

use Doctrine\ORM\Mapping as ORM;

/**
 * PollModule
 *
 * @ORM\Table(name="poll_module")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\module\PollModuleRepository")
 */
class PollModule
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
