<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dwf\PronosticsBundle\Entity\User as User;

/**
 * Pronostic
 *
 * @ORM\Table("pronostics")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\PronosticRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Pronostic
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
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\Game")
     * @ORM\JoinColumn(nullable=false)
     */
    private $game;

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
     * @ORM\Column(name="scoreTeam1", type="integer", nullable=true)
     */
    private $scoreTeam1;

    /**
     * @var integer
     *
     * @ORM\Column(name="scoreTeam2", type="integer", nullable=true)
     */
    private $scoreTeam2;

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
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="SliceScore")
     * @ORM\JoinColumn(name="slicescore", referencedColumnName="id", nullable=true)
     */
    private $sliceScore;
    
    /**
     * @var string
     *
     * possible values : 1 / N / 2
     * 
     * @ORM\Column(name="simpleBet", type="string", length=1)
     */
    private $simpleBet;
    
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * 
     * @var \DateTime
     * 
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     */
    private $updatedAt;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiresAt", type="datetime")
     */
    private $expiresAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="result", type="integer", nullable=true)
     */
    private $result;


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
     * Set scoreTeam1
     *
     * @param integer $scoreTeam1
     * @return Pronostic
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
     * @return Pronostic
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Pronostic
     */
    public function setUpdatedAt($updatedAt)
    {
    	$this->updatedAt = $updatedAt;
    
    	return $this;
    }
    
    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
    	return $this->updatedAt;
    }
    
    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
    	$this->updatedAt = new \DateTime();
    }

    /**
     * Set expiresAt
     *
     * @param \DateTime $expiresAt
     * @return Pronostic
     */
    public function setExpiresAt($expiresAt)
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * Get expiresAt
     *
     * @return \DateTime 
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function setExpiresAtValue()
    {
    	if(!$this->getExpiresAt())
    	{
    		$now = $this->getCreatedAt() ? $this->getCreatedAt()->format('U') : time();
    		$this->expiresAt = new \DateTime(date('Y-m-d H:i:s', $now + 86400 * 10));
    	}
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
     * Set game
     *
     * @param \Dwf\PronosticsBundle\Entity\Game $game
     * @return Pronostic
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
    
//     static public function setResultsForGame(\Dwf\PronosticsBundle\Entity\Game $game)
//     {
//     	$em = $this->getDoctrine()->getEntityManager();
    
//     	$pronostics = $this->findAllByGame($game);
//     	foreach ($pronostics as $pronostic)
//     	{
//     		if(($game->getScoreTeam1() == $pronostic->getScoreTeam1()) && ($game->getScoreTeam2() == $pronostic->getScoreTeam2())) {
//     			$result = 3;
//     		}
//     		elseif($game->getWhoWin() == $pronostic->getWhoWin()) {
//     			$result = 2;
//     		}
//     		else $result = 1;
    			
//     		$pronostic->setResult($result);
//     		$em->persist($pronostic);
//     		$em->flush();
    			
//     	}
//     }
    
//     public function getEntityManager() {
//     	return $this->container->get('doctrine')->getEntityManager();
//     }
    
    public function getScore()
    {
        return $this->getScoreTeam1()." - ".$this->getScoreTeam2();
    }
    
    public function getScoreAfterOvertime()
    {
        return $this->getScoreTeam1Overtime()." - ".$this->getScoreTeam2Overtime();
    }

    /**
     * Set scoreTeam1Overtime
     *
     * @param integer $scoreTeam1Overtime
     * @return Pronostic
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
     * @return Pronostic
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
     * @return Pronostic
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
     * Set winner
     *
     * @param \Dwf\PronosticsBundle\Entity\Team $winner
     * @return Pronostic
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
     * Set simpleBet
     *
     * @param string $simpleBet
     * @return Pronostic
     */
    public function setSimpleBet($simpleBet)
    {
        $this->simpleBet = $simpleBet;

        return $this;
    }

    /**
     * Get simpleBet
     *
     * @return string 
     */
    public function getSimpleBet()
    {
        return $this->simpleBet;
    }

    /**
     * Set sliceScore
     *
     * @param \Dwf\PronosticsBundle\Entity\SliceScore $sliceScore
     *
     * @return Pronostic
     */
    public function setSliceScore(\Dwf\PronosticsBundle\Entity\SliceScore $sliceScore = null)
    {
        $this->sliceScore = $sliceScore;

        return $this;
    }

    /**
     * Get sliceScore
     *
     * @return \Dwf\PronosticsBundle\Entity\SliceScore
     */
    public function getSliceScore()
    {
        return $this->sliceScore;
    }
}
