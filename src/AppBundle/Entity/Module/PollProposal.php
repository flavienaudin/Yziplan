<?php

namespace AppBundle\Entity\Module;

use AppBundle\Entity\Event\ModuleInvitation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Proposal
 *
 * @ORM\Table(name="module_poll_proposal")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Module\PollProposalRepository")
 * @Vich\Uploadable
 */
class PollProposal
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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    private $deleted = false;

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
     * ModuleInvitation de l'invité ayant ajouté la proposition
     *
     * @var ModuleInvitation
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event\ModuleInvitation", inversedBy="pollProposalsCreated")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    private $creator;

    /**
     * @var PollModule
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Module\PollModule", inversedBy="pollProposals")
     * @ORM\JoinColumn(name="poll_module_id", referencedColumnName="id")
     */
    private $pollModule;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\PollProposalElement", mappedBy="pollProposal", cascade={"persist"})
     */
    private $pollProposalElements;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Module\PollProposalResponse", mappedBy="pollProposal", cascade={"persist"})
     */
    private $pollProposalResponses;


    /***********************************************************************
     *                      Constructor
     ***********************************************************************/
    public function __construct()
    {
        $this->pollProposalElements = new ArrayCollection();
        $this->pollProposalResponses = new ArrayCollection();
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return PollProposal
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param boolean $deleted
     * @return PollProposal
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
        return $this;
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
     * @return PollProposal
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
     * @return PollProposal
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
     * @return PollProposal
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
     * @return PollProposal
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
     * @return PollProposal
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
     * @return PollProposal
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
     * @return ModuleInvitation
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * @param ModuleInvitation $creator
     * @return PollProposal
     */
    public function setCreator(ModuleInvitation $creator)
    {
        $this->creator = $creator;
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
     * @return PollProposal
     */
    public function setPollModule(PollModule $pollModule)
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
     * Add proposalElement
     * @param PollProposalElement $pollProposalElement
     * @return PollProposal
     */
    public function addPollProposalElement(PollProposalElement $pollProposalElement)
    {
        $this->pollProposalElements[] = $pollProposalElement;
        $pollProposalElement->setPollProposal($this);

        return $this;
    }

    /**
     * Remove pollProposalElement
     * @param PollProposalElement $pollProposalElement
     */
    public function removePollProposalElement(PollProposalElement $pollProposalElement)
    {
        $this->pollProposalElements->removeElement($pollProposalElement);
    }

    /**
     * Get pollProposalResponses
     * @return ArrayCollection
     */
    public function getPollProposalResponses()
    {
        return $this->pollProposalResponses;
    }

    /**
     * @param PollProposalResponse $pollProposalResponse
     * @return PollProposal
     */
    public function addPollProposalResponse(PollProposalResponse $pollProposalResponse)
    {
        $this->pollProposalResponses[] = $pollProposalResponse;
        $pollProposalResponse->setPollProposal($this);
        return $this;
    }

    /**
     * Remove pollProposalResponse
     * @param PollProposalResponse $pollProposalResponse
     */
    public function removePollProposalResponse(PollProposalResponse $pollProposalResponse)
    {
        $this->pollProposalResponses->removeElement($pollProposalResponse);
    }


    /***********************************************************************
     *                      Helpers
     ***********************************************************************/

    /**
     * Return PollProposalReponses concerning the PollProposal, of the ModuleInvitation
     * @param $moduleInvitation
     */
    public function getPollProposalResponsesOfModuleInvitation(ModuleInvitation $moduleInvitation)
    {
        $miPollProposalResponse = $moduleInvitation->getPollProposalResponses();
        $criteria = Criteria::create()->where(Criteria::expr()->eq("pollProposal", $this));
        $criteria->setMaxResults(1);
        $response = $miPollProposalResponse->matching($criteria);
        return $response[0];
    }

    /**
     * Return PollProposalReponses concerning the PollProposal, of the ModuleInvitation
     * @param $moduleInvitations
     */
    public function getPollProposalResponsesOfModuleInvitations($moduleInvitations)
    {
        $pollProposalResponses = array();
        /** @var ModuleInvitation $moduleInvitation */
        foreach ($moduleInvitations as $moduleInvitation) {
            $pollProposalResponses[] = $this->getPollProposalResponsesOfModuleInvitation($moduleInvitation);
        }
        return $pollProposalResponses;
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
