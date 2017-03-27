<?php

namespace AppBundle\Entity\Module;

use AppBundle\Utils\enum\PollElementType;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * PollProposalElement
 *
 * @ORM\Table(name="module_poll_proposal_element")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\PollProposalElementRepository")
 * @Vich\Uploadable
 */
class PollProposalElement
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
     * @var PollProposal
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Module\PollProposal", inversedBy="pollProposalElements")
     * @ORM\JoinColumn(name="poll_proposal_id", referencedColumnName="id")
     */
    private $pollProposal;

    /**
     * @var PollElement
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Module\PollElement", inversedBy="pollProposalElements")
     * @ORM\JoinColumn(name="poll_element_id", referencedColumnName="id")
     */
    private $pollElement;


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
     * @return PollProposal
     */
    public function getPollProposal()
    {
        return $this->pollProposal;
    }

    /**
     * @param PollProposal $pollProposal
     * @return PollProposalElement
     */
    public function setPollProposal($pollProposal)
    {
        $this->pollProposal = $pollProposal;
        return $this;
    }

    /**
     * @return PollElement
     */
    public function getPollElement()
    {
        return $this->pollElement;
    }

    /**
     * @param PollElement $pollElement
     * @return PollProposalElement
     */
    public function setPollElement($pollElement)
    {
        $this->pollElement = $pollElement;
        return $this;
    }


    /***********************************************************************
     *                      Helpers
     ***********************************************************************/

    /**
     */
    public function getValue()
    {
        $type = $this->getPollElement()->getType();
        $val = $this->getValString();
        switch ($this->getPollElement()->getType()) {

            case PollElementType::DATETIME:
                $val = $this->getValDatetime();
                break;
            case PollElementType::INTEGER :
                $val = $this->getValInteger();
                break;
            case PollElementType::RICHTEXT :
                $val = $this->getValText();
                break;
            case PollElementType::PICTURE :
                $val = array(
                    'filename' => $this->getPictureFilename(),
                    'file' => $this->getPictureFile()
                );
                break;
            case PollElementType::GOOGLE_PLACE_ID:
            case PollElementType::STRING :
                $val = $this->getValString();
                break;
        }
        return $val;
    }

    public function getArrayFromDate()
    {
        if (!empty($this->valDatetime)) {
            $date['year'] = $this->valDatetime->format('Y');
            $date['month'] = $this->valDatetime->format('m');
            $date['day'] = $this->valDatetime->format('d');
            return $date;
        }
        return null;
    }

    public function getArrayFromTime()
    {
        if (!empty($this->valDatetime) && $this->time) {
            $date['hour'] = $this->valDatetime->format('H');
            $date['minute'] = $this->valDatetime->format('i');
            return $date;
        }
        return null;
    }

    public function getArrayFromEndDate()
    {
        if (!empty($this->valEndDatetime) && $this->endDate) {
            $date['year'] = $this->valEndDatetime->format('Y');
            $date['month'] = $this->valEndDatetime->format('m');
            $date['day'] = $this->valEndDatetime->format('d');
            return $date;
        }
        return null;
    }

    public function getArrayFromEndTime()
    {
        if (!empty($this->valEndDatetime) && $this->endTime) {
            $date['hour'] = $this->valEndDatetime->format('H');
            $date['minute'] = $this->valEndDatetime->format('i');
            return $date;
        }
        return null;
    }
}
