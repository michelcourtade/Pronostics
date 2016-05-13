<?php

namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Cocur\Slugify\Slugify;

/**
 * Team
 *
 * @ORM\Table("teams")
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\TeamRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Team
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
     * @var boolean
     *
     * @ORM\Column(name="national", type="boolean")
     */
    private $national;

    /**
     * @var string
     *
     * @ORM\Column(name="iso", type="string", length=3)
     */
    private $iso;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="Sport")
     * @ORM\JoinColumn(name="sport", referencedColumnName="id")
     */
    private $sport;

    /**
     * @var file
     *
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     * @Assert\File(maxSize="6000000")
     */
    private $file;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $path;

    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return 'uploads/documents';
    }

    public function getFixturesPath()
    {
        return $this->getAbsolutePath() . 'web/uploads/documents/fixtures/';
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
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
     * Set name
     *
     * @param string $name
     * @return Team
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
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null !== $this->file && !$this->path) {
            $slugify = new Slugify();
            $sport = $slugify->slugify($this->getSport());
            $this->path = $sport.'-'.($this->getNational() ? 'NAT-' : 'CLU-').$this->getIso().'.'.$this->file->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {
        if (null === $this->file) {
            return;
        }

        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        $this->file->move($this->getUploadRootDir(), $this->path);

        unset($this->file);
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unlink($file);
        }
    }
	/**
     * @return the $iso
     */
    public function getIso() {
        return $this->iso;
    }

	/**
     * @param string $iso
     */
    public function setIso($iso) {
        $this->iso = $iso;
    }



    /**
     * Set national
     *
     * @param boolean $national
     * @return Team
     */
    public function setNational($national)
    {
        $this->national = $national;

        return $this;
    }

    /**
     * Get national
     *
     * @return boolean
     */
    public function getNational()
    {
        return $this->national;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Team
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set sport
     *
     * @param \Dwf\PronosticsBundle\Entity\Sport $sport
     * @return Team
     */
    public function setSport(\Dwf\PronosticsBundle\Entity\Sport $sport = null)
    {
        $this->sport = $sport;

        return $this;
    }

    /**
     * Get sport
     *
     * @return \Dwf\PronosticsBundle\Entity\Sport
     */
    public function getSport()
    {
        return $this->sport;
    }
}
