<?php

namespace Octopouce\ShopBundle\Entity;

use App\Entity\Account\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Octopouce\ShopBundle\Repository\BillingRepository")
 * @ORM\Table(name="shop_billing")
 * @ORM\HasLifecycleCallbacks()
 */
class Billing
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $firstname;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $lastname;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $address;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $complementAddress;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $postalCode;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $city;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $country;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $phone;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $company;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private $intraVAT;

	/**
	 * @var User
	 *
	 * @ORM\ManyToOne(targetEntity="App\Entity\Account\User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	private $user;


	public function __construct()
	{
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
	}

	/**
	 * @ORM\PreUpdate
	 */
	public function setUpdatedAtValue()
	{
		$this->updatedAt = new \DateTime();
	}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

	/**
	 * Set firstname.
	 *
	 * @param string $firstname
	 *
	 * @return Billing
	 */
	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;

		return $this;
	}

	/**
	 * Get firstname.
	 *
	 * @return string
	 */
	public function getFirstname()
	{
		return $this->firstname;
	}

	/**
	 * Set lastname.
	 *
	 * @param string $lastname
	 *
	 * @return Billing
	 */
	public function setLastname($lastname)
	{
		$this->lastname = $lastname;

		return $this;
	}

	/**
	 * Get lastname.
	 *
	 * @return string
	 */
	public function getLastname()
	{
		return $this->lastname;
	}

	/**
	 * Set address.
	 *
	 * @param string|null $address
	 *
	 * @return Billing
	 */
	public function setAddress($address = null)
	{
		$this->address = $address;

		return $this;
	}

	/**
	 * Get address.
	 *
	 * @return string|null
	 */
	public function getAddress()
	{
		return $this->address;
	}

	/**
	 * Set complementAddress.
	 *
	 * @param string|null $complementAddress
	 *
	 * @return Billing
	 */
	public function setComplementAddress($complementAddress = null)
	{
		$this->complementAddress = $complementAddress;

		return $this;
	}

	/**
	 * Get complementAddress.
	 *
	 * @return string|null
	 */
	public function getComplementAddress()
	{
		return $this->complementAddress;
	}

	/**
	 * Set postalCode.
	 *
	 * @param string|null $postalCode
	 *
	 * @return Billing
	 */
	public function setPostalCode($postalCode = null)
	{
		$this->postalCode = $postalCode;

		return $this;
	}

	/**
	 * Get postalCode.
	 *
	 * @return string|null
	 */
	public function getPostalCode()
	{
		return $this->postalCode;
	}

	/**
	 * Set city.
	 *
	 * @param string|null $city
	 *
	 * @return Billing
	 */
	public function setCity($city = null)
	{
		$this->city = $city;

		return $this;
	}

	/**
	 * Get city.
	 *
	 * @return string|null
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * Set country.
	 *
	 * @param string|null $country
	 *
	 * @return Billing
	 */
	public function setCountry($country = null)
	{
		$this->country = $country;

		return $this;
	}

	/**
	 * Get country.
	 *
	 * @return string|null
	 */
	public function getCountry()
	{
		return $this->country;
	}

	/**
	 * Set phone.
	 *
	 * @param string|null $phone
	 *
	 * @return Billing
	 */
	public function setPhone($phone = null)
	{
		$this->phone = $phone;

		return $this;
	}

	/**
	 * Get phone.
	 *
	 * @return string|null
	 */
	public function getPhone()
	{
		return $this->phone;
	}

	/**
	 * @return User
	 */
	public function getUser(): ?User {
		return $this->user;
	}

	/**
	 * @param User $user
	 *
	 * @return Billing
	 */
	public function setUser( ?User $user ): self {
		$this->user = $user;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCompany() {
		return $this->company;
	}

	/**
	 * @param mixed $company
	 *
	 * @return Billing
	 */
	public function setCompany( $company ) {
		$this->company = $company;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getIntraVAT() {
		return $this->intraVAT;
	}

	/**
	 * @param mixed $intraVAT
	 *
	 * @return Billing
	 */
	public function setIntraVAT( $intraVAT ) {
		$this->intraVAT = $intraVAT;

		return $this;
	}


}
