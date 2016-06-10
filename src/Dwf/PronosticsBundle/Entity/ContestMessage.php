<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * ContestMessage
 *
 * @ORM\Table("contests_messages")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\ContestMessageRepository")
 * @ORM\HasLifecycleCallbacks
 */
class ContestMessage
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
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @ORM\OneToOne(targetEntity="Dwf\PronosticsBundle\Entity\Contest")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contest;

    /** @ORM\ManyToOne(targetEntity="User", inversedBy="invitation", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     * @var unknown*/
    private $user;


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
     * Set message
     *
     * @param string $message
     *
     * @return ContestMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ContestMessage
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
     * Set contest
     *
     * @param \Dwf\PronosticsBundle\Entity\Contest $contest
     *
     * @return ContestMessage
     */
    public function setContest(\Dwf\PronosticsBundle\Entity\Contest $contest)
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

    /**
     * Set user
     *
     * @param \Dwf\PronosticsBundle\Entity\User $user
     *
     * @return ContestMessage
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
     * @ORM\PrePersist
     */
    public function onPrePersist() {

        $this->date = new \DateTime("now");
    }
}
