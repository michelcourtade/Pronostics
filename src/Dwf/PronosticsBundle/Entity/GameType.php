<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GameType
 *
 * @ORM\Table("games_types")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\GameTypeRepository")
 */
class GameType
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
     * 
     * @var boolean
     * @ORM\Column(name="canHaveOvertime", type="boolean")
     */
    private $canHaveOvertime;
    
    /**
     * @ORM\ManyToMany(targetEntity="Event", inversedBy="gameTypes")
     * @ORM\JoinTable(name="event_gametypes")
     */
    public $events;


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
     * @return GameType
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
    
    public function __toString()
    {
    	return $this->getName();
    }

    /**
     * Set canHaveOvertime
     *
     * @param boolean $canHaveOvertime
     * @return GameType
     */
    public function setCanHaveOvertime($canHaveOvertime)
    {
        $this->canHaveOvertime = $canHaveOvertime;

        return $this;
    }

    /**
     * Get canHaveOvertime
     *
     * @return boolean 
     */
    public function getCanHaveOvertime()
    {
        return $this->canHaveOvertime;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->events = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add events
     *
     * @param \Dwf\PronosticsBundle\Entity\Event $events
     * @return GameType
     */
    public function addEvent(\Dwf\PronosticsBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    /**
     * Remove events
     *
     * @param \Dwf\PronosticsBundle\Entity\Event $events
     */
    public function removeEvent(\Dwf\PronosticsBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    /**
     * Get events
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getEvents()
    {
        return $this->events;
    }
}
