<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SliceScore
 *
 * @ORM\Table("slicescores")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\SliceScoreRepository")
 */
class SliceScore
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
     * @ORM\ManyToMany(targetEntity="Sport", inversedBy="sliceScores")
     */
    public $sports;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="min", type="integer")
     */
    private $min;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="max", type="integer")
     */
    private $max;
    
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
     *
     * @return SliceScore
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
     * Constructor
     */
    public function __construct()
    {
        $this->sports = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add sport
     *
     * @param \Dwf\PronosticsBundle\Entity\Sport $sport
     *
     * @return SliceScore
     */
    public function addSport(\Dwf\PronosticsBundle\Entity\Sport $sport)
    {
        $this->sports[] = $sport;

        return $this;
    }

    /**
     * Remove sport
     *
     * @param \Dwf\PronosticsBundle\Entity\Sport $sport
     */
    public function removeSport(\Dwf\PronosticsBundle\Entity\Sport $sport)
    {
        $this->sports->removeElement($sport);
    }

    /**
     * Get sports
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSports()
    {
        return $this->sports;
    }
    
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set min
     *
     * @param integer $min
     *
     * @return SliceScore
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * Get min
     *
     * @return integer
     */
    public function getMin()
    {
        return $this->min;
    }

    /**
     * Set max
     *
     * @param integer $max
     *
     * @return SliceScore
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * Get max
     *
     * @return integer
     */
    public function getMax()
    {
        return $this->max;
    }
}
