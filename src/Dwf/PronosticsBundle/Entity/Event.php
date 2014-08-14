<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Event
 *
 * @ORM\Table("events")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\EventRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Event
{
    /**
     * @var integer
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
     * @var boolean
     *
     * @ORM\Column(name="nationalTeams", type="boolean")
     */
    private $nationalTeams;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="finishDate", type="datetime")
     */
    private $finishDate;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active;

    /**
     * @var boolean
     *
     * @ORM\Column(name="simpleBet", type="boolean")
     */
    private $simpleBet;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="nbPointsForLoss", type="integer", nullable=true)
     */
    private $nbPointsForLoss;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbPointsForDraw", type="integer", nullable=true)
     */
    private $nbPointsForDraw;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="nbPointsForWin", type="integer", nullable=true)
     */
    private $nbPointsForWin;
    
    
    /**
     * @var file
     *
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     * @Assert\File(maxSize="6000000")
     */
    private $file;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;
    
    /**
     * @ORM\ManyToMany(targetEntity="GameType", mappedBy="events")
     */
    private $gameTypes;
    
    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sport")
     * @ORM\JoinColumn(name="sport", referencedColumnName="id")
     */
    private $sport;
    
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }
    
    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }
    
    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }
    
    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return 'uploads/documents';
    }

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
     * Set name
     *
     * @param string $name
     * @return Event
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Event
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set finishDate
     *
     * @param \DateTime $finishDate
     * @return Event
     */
    public function setFinishDate($finishDate)
    {
        $this->finishDate = $finishDate;

        return $this;
    }

    /**
     * Get finishDate
     *
     * @return \DateTime 
     */
    public function getFinishDate()
    {
        return $this->finishDate;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Event
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
    
    public function __toString()
    {
    	return $this->getName();
    }
    /**
     * @return the $img
     */
    public function getFile() {
    	return $this->file;
    }
    
    /**
     * @param \Dwf\PronosticsBundle\Entity\file $img
     */
    public function setFile($file) {
    	$this->file = $file;
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
    	if (null !== $this->file) {
    		// faites ce que vous voulez pour générer un nom unique
    		$this->path = sha1(uniqid(mt_rand(), true)).'.'.$this->file->guessExtension();
    	}
    }
    
    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
    	if (null === $this->file) {
    		return;
    	}
    
    	// s'il y a une erreur lors du déplacement du fichier, une exception
    	// va automatiquement être lancée par la méthode move(). Cela va empêcher
    	// proprement l'entité d'être persistée dans la base de données si
    	// erreur il y a
    	$this->file->move($this->getUploadRootDir(), $this->path);
    
    	unset($this->file);
    }
    
    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
    	if ($file = $this->getAbsolutePath()) {
    		unlink($file);
    	}
    }
    
    public function hasBegan()
    {
        return ($this->getStartDate()->format('U') < time());
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->gameTypes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set nbPointsForLoss
     *
     * @param integer $nbPointsForLoss
     * @return Event
     */
    public function setNbPointsForLoss($nbPointsForLoss)
    {
        $this->nbPointsForLoss = $nbPointsForLoss;

        return $this;
    }

    /**
     * Get nbPointsForLoss
     *
     * @return integer 
     */
    public function getNbPointsForLoss()
    {
        return $this->nbPointsForLoss;
    }

    /**
     * Set nbPointsForDraw
     *
     * @param integer $nbPointsForDraw
     * @return Event
     */
    public function setNbPointsForDraw($nbPointsForDraw)
    {
        $this->nbPointsForDraw = $nbPointsForDraw;

        return $this;
    }

    /**
     * Get nbPointsForDraw
     *
     * @return integer 
     */
    public function getNbPointsForDraw()
    {
        return $this->nbPointsForDraw;
    }

    /**
     * Set nbPointsForWin
     *
     * @param integer $nbPointsForWin
     * @return Event
     */
    public function setNbPointsForWin($nbPointsForWin)
    {
        $this->nbPointsForWin = $nbPointsForWin;

        return $this;
    }

    /**
     * Get nbPointsForWin
     *
     * @return integer 
     */
    public function getNbPointsForWin()
    {
        return $this->nbPointsForWin;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Event
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Add gameTypes
     *
     * @param \Dwf\PronosticsBundle\Entity\GameType $gameTypes
     * @return Event
     */
    public function addGameType(\Dwf\PronosticsBundle\Entity\GameType $gameTypes)
    {
        $this->gameTypes[] = $gameTypes;

        return $this;
    }

    /**
     * Remove gameTypes
     *
     * @param \Dwf\PronosticsBundle\Entity\GameType $gameTypes
     */
    public function removeGameType(\Dwf\PronosticsBundle\Entity\GameType $gameTypes)
    {
        $this->gameTypes->removeElement($gameTypes);
    }

    /**
     * Get gameTypes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGameTypes()
    {
        return $this->gameTypes;
    }

    /**
     * Set sport
     *
     * @param \Dwf\PronosticsBundle\Entity\Sport $sport
     * @return Event
     */
    public function setSport(\Dwf\PronosticsBundle\Entity\Sport $sport = null)
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * Get sport
     *
     * @return \Dwf\PronosticsBundle\Entity\Sport 
     */
    public function getSport()
    {
        return $this->sport;
    }

    /**
     * Set nationalTeams
     *
     * @param boolean $nationalTeams
     * @return Event
     */
    public function setNationalTeams($nationalTeams)
    {
        $this->nationalTeams = $nationalTeams;

        return $this;
    }

    /**
     * Get nationalTeams
     *
     * @return boolean 
     */
    public function getNationalTeams()
    {
        return $this->nationalTeams;
    }

    /**
     * Set simpleBet
     *
     * @param boolean $simpleBet
     * @return Event
     */
    public function setSimpleBet($simpleBet)
    {
        $this->simpleBet = $simpleBet;

        return $this;
    }

    /**
     * Get simpleBet
     *
     * @return boolean 
     */
    public function getSimpleBet()
    {
        return $this->simpleBet;
    }
}
