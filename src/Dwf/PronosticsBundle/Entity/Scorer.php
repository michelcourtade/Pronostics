<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Scorer
 *
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table("scorers")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\ScorerRepository")
 */
class Scorer
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
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event", referencedColumnName="id")
     */
    private $event;
    
    /**
     * @var Player
     *
     * @ORM\ManyToOne(targetEntity="Player")
     * @ORM\JoinColumn(name="player", referencedColumnName="id")
     */
    private $player;

    /**
     * @var Game
     *
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\Game", inversedBy="scorers")
     * @ORM\JoinColumn(nullable=false, name="game", referencedColumnName="id")
     */
    private $game;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="score", type="integer")
     */
    private $score;

    /**
     * @var boolean
     *
     * @ORM\Column(name="owngoal", type="boolean", nullable=true)
     */
    private $owngoal = false;
    
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
     * Set team
     *
     * @param \Dwf\PronosticsBundle\Entity\Team $team
     * @return Player
     */
    public function setTeam(\Dwf\PronosticsBundle\Entity\Team $team = null)
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
     * Set score
     *
     * @param integer $score
     * @return Scorer
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return integer 
     */
    public function getScore()
    {
        return $this->score;
    }
    
    /**
     * Set owngoal
     *
     * @param boolean $owngoal
     * @return Scorer
     */
    public function setOwngoal($owngoal)
    {
    	$this->owngoal = $owngoal;
    
    	return $this;
    }
    
    /**
     * Get owngoal
     *
     * @return boolean
     */
    public function getOwngoal()
    {
    	return $this->owngoal;
    }

    /**
     * Set event
     *
     * @param \Dwf\PronosticsBundle\Entity\Event $event
     * @return Scorer
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
     * Set player
     *
     * @param \Dwf\PronosticsBundle\Entity\Player $player
     * @return Scorer
     */
    public function setPlayer(\Dwf\PronosticsBundle\Entity\Player $player = null)
    {
        $this->player = $player;

        return $this;
    }

    /**
     * Get player
     *
     * @return \Dwf\PronosticsBundle\Entity\Player 
     */
    public function getPlayer()
    {
        return $this->player;
    }

    /**
     * Set game
     *
     * @param \Dwf\PronosticsBundle\Entity\Game $game
     * @return Scorer
     */
    public function setGame(\Dwf\PronosticsBundle\Entity\Game $game = null)
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
