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

    /**
     * Contient la valeur si le PollElement correspondant est de type
     *
     * @var string
     * @ORM\Column(name="val_string", type="string", length=511, nullable=true)
     */
    private $valString;

    /**
     * @var string
     *
     * @ORM\Column(name="val_text", type="text", nullable=true)
     */
    private $valText;

    /**
     * Contient la valeur si le type == PollElementType::INTEGER
     *
     * @var integer
     *
     * @ORM\Column(name="val_integer", type="integer", nullable=true)
     */
    private $valInteger;

    /**
     * Contient la valeur si le type == PollElementType::DATETIME
     *
     * @var \DateTime
     *
     * @ORM\Column(name="val_datetime", type="datetime", nullable=true)
     */
    private $valDatetime;

    /**
     * true si on doit prendre en compte l'heure de valDateTime
     *
     * @var boolean
     *
     * @ORM\Column(name="has_time", type="boolean", nullable=false)
     */
    private $time = false;

    /**
     * Contient la valeur si le type == PollElementType::DATETIME
     *
     * @var \DateTime
     *
     * @ORM\Column(name="val_enddatetime", type="datetime", nullable=true)
     */
    private $valEndDatetime;

    /**
     * true si on doit prendre en compte la date de valEndDateTime
     *
     * @var boolean
     *
     * @ORM\Column(name="has_end_date", type="boolean", nullable=false)
     */
    private $endDate = false;

    /**
     * true si on doit prendre en compte l'heure de valEndDateTime
     *
     * @var boolean
     *
     * @ORM\Column(name="has_end_time", type="boolean", nullable=false)
     */
    private $endTime = false;

    /**
     * Contient la valeur du Google Place Id si le PollElement est de Type PollElementType::GOOGLE_PLACE_ID
     *
     * @var string
     * @ORM\Column(name="val_google_place_id", type="string", length=511, nullable=true)
     */
    private $valGooglePlaceId;

    /**
     * This attributes stores the filename of the file for the database
     * @var string
     * @ORM\Column(name="picture_filename", type="string", length=255, nullable=true)
     */
    private $pictureFilename;

    /**
     * This is not a mapped field of entity metadata, just a simple property.
     * @Vich\UploadableField(mapping="pollproposalelement_picture", fileNameProperty="pictureFilename")
     * @var File
     */
    private $pictureFile;

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
     * @return string
     */
    public function getValString()
    {
        return $this->valString;
    }

    /**
     * @param string $valString
     * @return PollProposalElement
     */
    public function setValString($valString)
    {
        $this->valString = $valString;
        return $this;
    }

    /**
     * @return string
     */
    public function getValText()
    {
        return $this->valText;
    }

    /**
     * @param string $valText
     */
    public function setValText($valText)
    {
        $this->valText = $valText;
    }


    /**
     * @return int
     */
    public function getValInteger()
    {
        return $this->valInteger;
    }

    /**
     * @param int $valInteger
     * @return PollProposalElement
     */
    public function setValInteger($valInteger)
    {
        $this->valInteger = $valInteger;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getValDatetime()
    {
        return $this->valDatetime;
    }

    /**
     * @param \DateTime $valDatetime
     * @return PollProposalElement
     */
    public function setValDatetime($valDatetime)
    {
        $this->valDatetime = $valDatetime;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTime()
    {
        return $this->time;
    }

    /**
     * @param bool $hasTime
     */
    public function setTime($hasTime)
    {
        $this->time = $hasTime;
    }

    /**
     * @return \DateTime
     */
    public function getValEndDatetime()
    {
        return $this->valEndDatetime;
    }

    /**
     * @param \DateTime $valEndDatetime
     */
    public function setValEndDatetime($valEndDatetime)
    {
        $this->valEndDatetime = $valEndDatetime;
    }

    /**
     * @return bool
     */
    public function hasEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param bool $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }


    /**
     * @return bool
     */
    public function hasEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param bool $hasEndTime
     */
    public function setEndTime($hasEndTime)
    {
        $this->endTime = $hasEndTime;
    }


    /**
     * @return string
     */
    public function getValGooglePlaceId()
    {
        return $this->valGooglePlaceId;
    }

    /**
     * @param string $valGooglePlaceId
     * @return PollProposalElement
     */
    public function setValGooglePlaceId($valGooglePlaceId)
    {
        $this->valGooglePlaceId = $valGooglePlaceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPictureFilename()
    {
        return $this->pictureFilename;
    }

    /**
     * @param string $pictureFilename
     * @return PollProposalElement
     */
    public function setPictureFilename($pictureFilename)
    {
        $this->pictureFilename = $pictureFilename;
        return $this;
    }

    /**
     * @return File
     */
    public function getPictureFile()
    {
        return $this->pictureFile;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $pictureFile
     * @return PollProposalElement
     */
    public function setPictureFile($pictureFile)
    {
        $this->pictureFile = $pictureFile;
        if ($pictureFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
        return $this;
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
            case PollElementType::END_DATETIME:
                $val = $this->getValEndDatetime();
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
            case $type == PollElementType::GOOGLE_PLACE_ID:
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
