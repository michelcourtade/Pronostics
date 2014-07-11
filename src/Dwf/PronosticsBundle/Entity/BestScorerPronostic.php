<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BestScorerPronostic
 *
 * @ORM\Table("bestscorers_pronostics")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\BestScorerPronosticRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class BestScorerPronostic
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
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\Player")
     * @ORM\JoinColumn(nullable=false)
     */
    private $player;

    /**
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\Event")
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="goals", type="integer")
     */
    private $goals;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime")
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return BestScorerPronostic
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
     * @return BestScorerPronostic
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
     * @ORM\PrePersist
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }
    
    /**
     * Set expiresAt
     *
     * @param \DateTime $expiresAt
     * @return BestScorerPronostic
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
     * @return BestScorerPronostic
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
     * @return BestScorerPronostic
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
     * Set player
     *
     * @param \Dwf\PronosticsBundle\Entity\Player $player
     * @return BestScorerPronostic
     */
    public function setPlayer(\Dwf\PronosticsBundle\Entity\Player $player)
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
     * Set event
     *
     * @param \Dwf\PronosticsBundle\Entity\Event $event
     * @return BestScorerPronostic
     */
    public function setEvent(\Dwf\PronosticsBundle\Entity\Event $event)
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
     * Set goals
     *
     * @param integer $goals
     * @return BestScorerPronostic
     */
    public function setGoals($goals)
    {
        $this->goals = $goals;

        return $this;
    }

    /**
     * Get goals
     *
     * @return integer 
     */
    public function getGoals()
    {
        return $this->goals;
    }
}
