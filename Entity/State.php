<?php

namespace Octopouce\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Octopouce\ShopBundle\Repository\StateRepository")
 * @ORM\Table(name="shop_state")
 */
class State
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $paid;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $failed;

	/**
	 * @ORM\Column(type="boolean", nullable=true)
	 */
	private $waiting;


	public function __construct() {
		$this->paid = false;
		$this->failed = false;
		$this->waiting = false;
	}


	public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }

	public function isFailed(): ?bool
	{
		return $this->failed;
	}

	public function setFailed(bool $failed): self
	{
		$this->failed = $failed;

		return $this;
	}

	public function isWaiting(): ?bool
	{
		return $this->waiting;
	}

	public function setWaiting(bool $waiting): self
	{
		$this->waiting = $waiting;

		return $this;
	}
}
