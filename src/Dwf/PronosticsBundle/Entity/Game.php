<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection as ArrayCollection;
/**
 * Game
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("games")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\GameRepository")
 */
class Game
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
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event", referencedColumnName="id")
     */
    private $event;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Team")
     * @ORM\JoinColumn(name="team1", referencedColumnName="id")
     */
    private $team1;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Team")
     * @ORM\JoinColumn(name="team2", referencedColumnName="id")
     */
    private $team2;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="GameType")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    private $type;

    /**
     * @var integer
     *
     * @ORM\Column(name="scoreTeam1", type="integer", nullable=true)
     */
    private $scoreTeam1 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="scoreTeam2", type="integer", nullable=true)
     */
    private $scoreTeam2 = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="scoreTeam1Overtime", type="integer", nullable=true)
     */
    private $scoreTeam1Overtime = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="scoreTeam2Overtime", type="integer", nullable=true)
     */
    private $scoreTeam2Overtime = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="overtime", type="boolean", nullable=true)
     */
    private $overtime = false;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Team")
     * @ORM\JoinColumn(name="winner", referencedColumnName="id", nullable=true)
     */
    private $winner;

    /**
     * @var bool
     *
     * @ORM\Column(name="played", type="boolean", nullable=true)
     */
    private $played = false;

    /**
     * @var text
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Scorer", mappedBy="game", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ ORM\OrderBy({"position"="ASC"})
     * @ Assert\Valid
     */
    protected $scorers;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumn(name="city", referencedColumnName="id", nullable=true)
     */
    private $city;

    /**
     * Constructor
     */
    public function __construct()
    {
    	$this->scorers = new ArrayCollection();
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
     * Set date
     *
     * @param \DateTime $date
     * @return Game
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set scoreTeam1
     *
     * @param integer $scoreTeam1
     * @return Game
     */
    public function setScoreTeam1($scoreTeam1)
    {
        $this->scoreTeam1 = $scoreTeam1;

        return $this;
    }

    /**
     * Get scoreTeam1
     *
     * @return integer
     */
    public function getScoreTeam1()
    {
        return $this->scoreTeam1;
    }

    /**
     * Set scoreTeam2
     *
     * @param integer $scoreTeam2
     * @return Game
     */
    public function setScoreTeam2($scoreTeam2)
    {
        $this->scoreTeam2 = $scoreTeam2;

        return $this;
    }

    /**
     * Get scoreTeam2
     *
     * @return integer
     */
    public function getScoreTeam2()
    {
        return $this->scoreTeam2;
    }

    /**
     * Set team1
     *
     * @param \Dwf\PronosticsBundle\Entity\Team $team1
     * @return Game
     */
    public function setTeam1(\Dwf\PronosticsBundle\Entity\Team $team1 = null)
    {
        $this->team1 = $team1;

        return $this;
    }

    /**
     * Get team1
     *
     * @return \Dwf\PronosticsBundle\Entity\Team
     */
    public function getTeam1()
    {
        return $this->team1;
    }

    /**
     * Set team2
     *
     * @param \Dwf\PronosticsBundle\Entity\Team $team2
     * @return Game
     */
    public function setTeam2(\Dwf\PronosticsBundle\Entity\Team $team2 = null)
    {
        $this->team2 = $team2;

        return $this;
    }

    /**
     * Get team2
     *
     * @return \Dwf\PronosticsBundle\Entity\Team
     */
    public function getTeam2()
    {
        return $this->team2;
    }

    public function __toString()
    {
    	return $this->getName().($this->getEvent() ? " (".$this->getEvent()->getName().")":"");
    }

    public function getName()
    {
    	return ($this->getTeam1() ? $this->getTeam1()->getName()." - ".$this->getTeam2()->getName():"");
    }

    /**
     * Set played
     *
     * @param integer $played
     * @return Game
     */
    public function setPlayed($played)
    {
        $this->played = $played;

        return $this;
    }

    /**
     * Get played
     *
     * @return integer
     */
    public function getPlayed()
    {
        return $this->played;
    }

    public function getScore()
    {
    	return $this->getScoreTeam1()." - ".$this->getScoreTeam2();
    }

    public function getScoreAfterOvertime()
    {
        return $this->getScoreTeam1Overtime()." - ".$this->getScoreTeam2Overtime();
    }

    /**
     * Set type
     *
     * @param \Dwf\PronosticsBundle\Entity\GameType $type
     * @return Game
     */
    public function setType(\Dwf\PronosticsBundle\Entity\GameType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Dwf\PronosticsBundle\Entity\GameType
     */
    public function getType()
    {
        return $this->type;
    }

    public function getWhoWin()
    {
    	if($this->getScoreTeam1() > $this->getScoreTeam2())
    		return 1;
    	elseif($this->getScoreTeam2() > $this->getScoreTeam1())
    		return 2;
    	else return 0;
    }

    public function getWhoWinAfterOvertime()
    {
    	if($this->getScoreTeam1Overtime() > $this->getScoreTeam2Overtime())
    		return 1;
    	elseif($this->getScoreTeam2Overtime() > $this->getScoreTeam1Overtime())
    		return 2;
    	else return 0;
    }

    public function getWhoLose()
    {
        if($this->getWhoWin() == 1)
            return 2;
        elseif($this->getWhoWin() == 2)
            return 1;
        elseif($this->hasOvertime()) {
            if($this->getWhoWinAfterOvertime() == 1)
                return 2;
            elseif($this->getWhoWinAfterOvertime() == 2)
                return 1;
            elseif($winner = $this->getWinner()) {
                if($winner->getId() == $this->getTeam1()->getId())
                    return 2;
                elseif($winner->getId() == $this->getTeam2()->getId())
                    return 1;
                return 0;
            }
            return 0;
        }
        return 0;
    }

    public function hasBegan()
    {
        return (intval($this->getDate()->format('U')) < time());
    }

    public function hasOvertime()
    {
    	return $this->overtime;
    }

    /**
     * Set scoreTeam1Overtime
     *
     * @param integer $scoreTeam1Overtime
     * @return Game
     */
    public function setScoreTeam1Overtime($scoreTeam1Overtime)
    {
        $this->scoreTeam1Overtime = $scoreTeam1Overtime;

        return $this;
    }

    /**
     * Get scoreTeam1Overtime
     *
     * @return integer
     */
    public function getScoreTeam1Overtime()
    {
        return $this->scoreTeam1Overtime;
    }

    /**
     * Set scoreTeam2Overtime
     *
     * @param integer $scoreTeam2Overtime
     * @return Game
     */
    public function setScoreTeam2Overtime($scoreTeam2Overtime)
    {
        $this->scoreTeam2Overtime = $scoreTeam2Overtime;

        return $this;
    }

    /**
     * Get scoreTeam2Overtime
     *
     * @return integer
     */
    public function getScoreTeam2Overtime()
    {
        return $this->scoreTeam2Overtime;
    }

    /**
     * Set overtime
     *
     * @param boolean $overtime
     * @return Game
     */
    public function setOvertime($overtime)
    {
        $this->overtime = $overtime;

        return $this;
    }

    /**
     * Get overtime
     *
     * @return boolean
     */
    public function getOvertime()
    {
        return $this->overtime;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Game
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set winner
     *
     * @param \Dwf\PronosticsBundle\Entity\Team $winner
     * @return Game
     */
    public function setWinner(\Dwf\PronosticsBundle\Entity\Team $winner = null)
    {
        $this->winner = $winner;

        return $this;
    }

    /**
     * Get winner
     *
     * @return \Dwf\PronosticsBundle\Entity\Team
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * Set event
     *
     * @param \Dwf\PronosticsBundle\Entity\Event $event
     * @return Game
     */
    public function setEvent(\Dwf\PronosticsBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \Dwf\PronosticsBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Add scorers
     *
     * @param \Dwf\PronosticsBundle\Entity\Scorer $scorers
     * @return Game
     */
    public function addScorer(\Dwf\PronosticsBundle\Entity\Scorer $scorers)
    {
        $scorers->setGame($this);
        $scorers->setEvent($this->getEvent());
        $this->scorers->add($scorers);

        return $this;
    }

    /**
     * Remove scorers
     *
     * @param \Dwf\PronosticsBundle\Entity\Scorer $scorers
     */
    public function removeScorer(\Dwf\PronosticsBundle\Entity\Scorer $scorers)
    {
        $this->scorers->removeElement($scorers);
    }

    /**
     * Get scorers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getScorers()
    {
        return $this->scorers;
    }

    public function getScoreDifference()
    {
        return abs($this->getScoreTeam1() - $this->getScoreTeam2());
    }

    /**
     * Set city
     *
     * @param \Dwf\PronosticsBundle\Entity\City $city
     * @return Game
     */
    public function setCity(\Dwf\PronosticsBundle\Entity\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \Dwf\PronosticsBundle\Entity\City 
     */
    public function getCity()
    {
        return $this->city;
    }
}
