<?php

namespace Dwf\PronosticsBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Sonata\UserBundle\Entity\User as BaseSonataUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Dwf\PronosticsBundle\Entity\UserRepository")
 */
class User extends BaseUser
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
     * @ORM\OneToOne(targetEntity="Invitation", mappedBy="user")
     * @ORM\JoinColumn(referencedColumnName="code")
     * @Assert\NotNull(message="Code invitation invalide")
     */
    protected $invitation;
    
    /**
     * @ ORM\ManyToMany(targetEntity="Application\Sonata\UserBundle\Entity\Group")
     * @ORM\ManyToMany(targetEntity="Dwf\PronosticsBundle\Entity\Contest")
     * @ORM\JoinTable(name="fos_user_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;
    
    public function setInvitation(Invitation $invitation)
    {
    	$this->invitation = $invitation;
    }
    
    public function getInvitation()
    {
    	return $this->invitation;
    }


    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
