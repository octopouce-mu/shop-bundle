<?php

namespace Octopouce\ShopBundle\Entity;

use App\Entity\Account\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Octopouce\ShopBundle\Repository\OrderStateRepository")
 * @ORM\Table(name="shop_order_state")
 */
class OrderState
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Account\User")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Octopouce\ShopBundle\Entity\State")
     */
    private $state;

	/**
	 * @var Order
	 *
	 * @ORM\ManyToOne(targetEntity="Octopouce\ShopBundle\Entity\Order", inversedBy="states")
	 */
	private $order;

	public function __construct()
	{
		$this->createdAt = new \DateTime();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }

	public function getOrder(): Order {
		return $this->order;
	}

	public function setOrder( ?Order $order ): OrderState {
		$this->order = $order;

		return $this;
	}


}
