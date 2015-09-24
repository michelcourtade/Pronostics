<?php
namespace Dwf\PronosticsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity
 * @ORM\Table("invitations")
 * @ORM\HasLifecycleCallbacks
 * */
class Invitation
{
	/** @ORM\Id @ORM\Column(type="string", length=6) */
	protected $code;

	/** @ORM\Column(type="string", length=256) */
	protected $email;

	/**
	 * When sending invitation be sure to set this value to `true`
	 *
	 * It can prevent invitations from being sent twice
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $sent = false;

	/** @ORM\OneToMany(targetEntity="User", mappedBy="invitation", cascade={"persist", "merge"}) */
	protected $user;

	/**
	 * @ORM\ManyToOne(targetEntity="Dwf\PronosticsBundle\Entity\Contest")
	 * @ORM\JoinColumn(nullable=true)
	 */
	private $contest;

	public function __construct()
	{
		// generate identifier only once, here a 6 characters length code
// 		$this->code = substr(md5(uniqid(rand(), true)), 0, 6);
	}

	public function getCode()
	{
		return $this->code;
	}

	public function getEmail()
	{
		return $this->email;
	}

	public function setEmail($email)
	{
		$this->email = $email;
	}

	public function isSent()
	{
		return $this->sent;
	}

	public function send()
	{
		$this->sent = true;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	public function setInvitationCode()
	{
		$this->code = substr(md5(uniqid(rand(), true)), 0, 6);
	}

	public function getId()
	{
		return $this->code;
	}

    /**
     * Set code
     *
     * @param string $code
     * @return Invitation
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set sent
     *
     * @param boolean $sent
     * @return Invitation
     */
    public function setSent($sent)
    {
        $this->sent = $sent;

        return $this;
    }

    /**
     * Get sent
     *
     * @return boolean
     */
    public function getSent()
    {
        return $this->sent;
    }

    /**
     * Set contest
     *
     * @param \Dwf\PronosticsBundle\Entity\Contest $contest
     * @return Invitation
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

    /**
     * Add user
     *
     * @param \Dwf\PronosticsBundle\Entity\User $user
     *
     * @return Invitation
     */
    public function addUser(\Dwf\PronosticsBundle\Entity\User $user)
    {
        $this->user[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \Dwf\PronosticsBundle\Entity\User $user
     */
    public function removeUser(\Dwf\PronosticsBundle\Entity\User $user)
    {
        $this->user->removeElement($user);
    }
}
