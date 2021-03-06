<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
// use Application\Sonata\UserBundle\Model\Group as BaseSonataGroup;
use FOS\UserBundle\Model\Group as BaseGroup;

/**
 * Contest
 *
 * @ORM\Table("contests")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\ContestRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Contest extends BaseGroup
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(name="event", referencedColumnName="id")
     */
    private $event;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime", nullable=true)
     */
    private $updatedAt;
    
    /**
     * @var string
     *
     * @ORM\Column(name="contestName", type="string", length=255)
     */
    private $contestName;
    

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
     * @return Contest
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
     * @return Contest
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
     * Set owner
     *
     * @param \Dwf\PronosticsBundle\Entity\User $owner
     * @return Contest
     */
    public function setOwner(\Dwf\PronosticsBundle\Entity\User $owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return \Dwf\PronosticsBundle\Entity\User 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set event
     *
     * @param \Dwf\PronosticsBundle\Entity\Event $event
     * @return Contest
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
    
    public function __toString()
    {
        return $this->name;
    }


    /**
     * Set contestName
     *
     * @param string $contestName
     * @return Contest
     */
    public function setContestName($contestName)
    {
        $this->contestName = $contestName;

        return $this;
    }

    /**
     * Get contestName
     *
     * @return string 
     */
    public function getContestName()
    {
        return $this->contestName;
    }

    public function getSlugName()
    {
        return str_replace(' ', '-', $this->getName());
    }
}
