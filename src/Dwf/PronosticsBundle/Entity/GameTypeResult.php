<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dwf\PronosticsBundle\Entity\User as User;

/**
 * Pronostic
 *
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\GameTypeResultRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class GameTypeResult
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
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\GameType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $gameType;
    
    /**
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\Game")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;
    
    /**
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\Team")
     * @ORM\JoinColumn(nullable=false)
     */
    private $team;

    /**
     * @var integer
     *
     * @ORM\Column(name="result", type="integer", nullable=true)
     */
    private $result;

    /**
     * @var integer
     *
     * @ORM\Column(name="goalaverage", type="integer", nullable=true)
     */
    private $goalaverage;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;
    
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
     * Set result
     *
     * @param integer $result
     * @return Pronostic
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }

    /**
     * Get result
     *
     * @return integer 
     */
    public function getResult()
    {
        return $this->result;
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
     * Set gameType
     *
     * @param \Dwf\PronosticsBundle\Entity\GameType $gameType
     * @return GameTypeResult
     */
    public function setGameType(\Dwf\PronosticsBundle\Entity\GameType $gameType)
    {
        $this->gameType = $gameType;

        return $this;
    }

    /**
     * Get gameType
     *
     * @return \Dwf\PronosticsBundle\Entity\GameType 
     */
    public function getGameType()
    {
        return $this->gameType;
    }

    /**
     * Set team
     *
     * @param \Dwf\PronosticsBundle\Entity\Team $team
     * @return GameTypeResult
     */
    public function setTeam(\Dwf\PronosticsBundle\Entity\Team $team)
    {
        $this->team = $team;

        return $this;
    }

    /**
     * Get team
     *
     * @return \Dwf\PronosticsBundle\Entity\Team 
     */
    public function getTeam()
    {
        return $this->team;
    }

    /**
     * Set goalaverage
     *
     * @param integer $goalaverage
     * @return GameTypeResult
     */
    public function setGoalaverage($goalaverage)
    {
        $this->goalaverage = $goalaverage;

        return $this;
    }

    /**
     * Get goalaverage
     *
     * @return integer 
     */
    public function getGoalaverage()
    {
        return $this->goalaverage;
    }
    
    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Pronostic
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }
    
    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
    
    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if(!$this->getCreatedAt())
        {
            $this->createdAt = new \DateTime();
        }
    }
    

    /**
     * Set game
     *
     * @param \Dwf\PronosticsBundle\Entity\Game $game
     * @return GameTypeResult
     */
    public function setGame(\Dwf\PronosticsBundle\Entity\Game $game)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * Get game
     *
     * @return \Dwf\PronosticsBundle\Entity\Game 
     */
    public function getGame()
    {
        return $this->game;
    }
}
