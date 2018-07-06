<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dwf\PronosticsBundle\Entity\User as User;

/**
 * Pronostic
 *
 * @ORM\Table("standings")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\StandingRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Standing
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
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;
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
     * @ORM\ManyToOne(targetEntity="Contest")
     * @ORM\JoinColumn(name="contest", referencedColumnName="id")
     */
    private $contest;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="points", type="integer", nullable=true)
     */
    private $points;

    /**
     * @var integer
     *
     * @ORM\Column(name="pronostics", type="integer", nullable=true)
     */
    private $pronostics;
    
    /**
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\Game")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;


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
     * Set user
     *
     * @param \Dwf\PronosticsBundle\Entity\User $user
     * @return Pronostic
     */
    public function setUser(\Dwf\PronosticsBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Dwf\PronosticsBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
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
     * Set points
     *
     * @param integer $points
     * @return Standing
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Get points
     *
     * @return integer 
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set pronostics
     *
     * @param integer $pronostics
     * @return Standing
     */
    public function setPronostics($pronostics)
    {
        $this->pronostics = $pronostics;

        return $this;
    }

    /**
     * Get pronostics
     *
     * @return integer 
     */
    public function getPronostics()
    {
        return $this->pronostics;
    }

    /**
     * Set game
     *
     * @param \Dwf\PronosticsBundle\Entity\Game $game
     * @return Standing
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


    /**
     * Set contest
     *
     * @param \Dwf\PronosticsBundle\Entity\Contest $contest
     *
     * @return Standing
     */
    public function setContest(\Dwf\PronosticsBundle\Entity\Contest $contest = null)
    {
        $this->contest = $contest;

        return $this;
    }

    /**
     * Get contest
     *
     * @return \Dwf\PronosticsBundle\Entity\Contest
     */
    public function getContest()
    {
        return $this->contest;
    }
}
