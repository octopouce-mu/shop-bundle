<?php

namespace Octopouce\ShopBundle\Entity;

use App\Entity\Account\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Payment\CoreBundle\Entity\PaymentInstruction;

/**
 * @ORM\Entity(repositoryClass="Octopouce\ShopBundle\Repository\OrderRepository")
 * @ORM\Table(name="shop_order")
 * @ORM\HasLifecycleCallbacks()
 */
class Order
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

	/**
	 * @ORM\Column(type="integer", nullable=true, unique=true)
	 */
	private $number;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="integer")
     */
    private $itemsTotal;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $itemsPriceTotal;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $priceTotal;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="Octopouce\ShopBundle\Entity\OrderItem", mappedBy="order", cascade={"all"}, orphanRemoval=true)
	 */
	private $items;

	/**
	 * @var ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="Octopouce\ShopBundle\Entity\OrderState", mappedBy="order", cascade={"all"}, orphanRemoval=true)
	 * @ORM\OrderBy({"createdAt" = "DESC"})
	 */
	private $states;

	/**
	 * @var Billing
	 *
	 * @ORM\ManyToOne(targetEntity="Octopouce\ShopBundle\Entity\Billing", cascade={"all"})
	 * @ORM\JoinColumn(name="billing_id", referencedColumnName="id")
	 */
	private $billing;

	/**
	 * @var Shipment
	 *
	 * @ORM\ManyToOne(targetEntity="Octopouce\ShopBundle\Entity\Shipment", cascade={"all"})
	 * @ORM\JoinColumn(name="shipment_id", referencedColumnName="id")
	 */
	private $shipment;

	/**
	 * @var PaymentInstruction
	 *
	 * @ORM\OneToOne(targetEntity="JMS\Payment\CoreBundle\Entity\PaymentInstruction")
	 */
	private $paymentInstruction;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $payIt;

	/**
	 * @var Discount
	 *
	 * @ORM\ManyToOne(targetEntity="Octopouce\ShopBundle\Entity\Discount", cascade={"all"})
	 * @ORM\JoinColumn(name="discount_id", referencedColumnName="id")
	 */
	private $discount;

	/**
	 * @var User
	 *
	 * @ORM\ManyToOne(targetEntity="App\Entity\Account\User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	private $user;

    /**
     * @ORM\OneToMany(targetEntity="Octopouce\ShopBundle\Entity\Invoice", mappedBy="order", cascade={"all"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $invoices;

	public function __construct()
                  	{
                  		$this->createdAt = new \DateTime();
                  		$this->updatedAt = new \DateTime();
                    $this->invoices = new ArrayCollection();
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

	/**
	 * @return mixed
	 */
	public function getNumber() {
                  		return $this->number;
                  	}

	/**
	 * @param mixed $number
	 *
	 * @return Order
	 */
	public function setNumber( $number ) {
                  		$this->number = $number;

                  		return $this;
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

    public function getItemsTotal(): ?int
    {
        return $this->itemsTotal;
    }

    public function setItemsTotal(int $itemsTotal): self
    {
        $this->itemsTotal = $itemsTotal;

        return $this;
    }

    public function getItemsPriceTotal()
    {
        return $this->itemsPriceTotal;
    }

    public function setItemsPriceTotal( $itemsPriceTotal): self
    {
        $this->itemsPriceTotal = $itemsPriceTotal;

        return $this;
    }

    public function getPriceTotal()
    {
        return $this->priceTotal;
    }

    public function setPriceTotal( $priceTotal): self
    {
        $this->priceTotal = $priceTotal;

        return $this;
    }

	public function addItem(OrderItem $item)
                  	{
                  		$this->items[] = $item;

                  		return $this;
                  	}

	public function removeItem(OrderItem $item)
                  	{
                  		return $this->items->removeElement($item);
                  	}

	public function getItems()
                  	{
                  		return $this->items;
                  	}

	public function addState(OrderState $state)
                  	{
                  		$this->states[] = $state;

                  		return $this;
                  	}

	public function removeState(OrderState $state)
                  	{
                  		return $this->states->removeElement($state);
                  	}

	public function getStates()
                  	{
                  		return $this->states;
                  	}

	/**
	 * @return Billing
	 */
	public function getBilling(): ?Billing {
                  		return $this->billing;
                  	}

	/**
	 * @param Billing $billing
	 *
	 * @return Order
	 */
	public function setBilling( ?Billing $billing ): self {
                  		$this->billing = $billing;
                  		return $this;
                  	}

	/**
	 * @return Shipment
	 */
	public function getShipment(): ?Shipment {
                  		return $this->shipment;
                  	}

	/**
	 * @param Shipment $shipment
	 *
	 * @return Order
	 */
	public function setShipment( ?Shipment $shipment ): self {
                  		$this->shipment = $shipment;
                  		return $this;
                  	}

	/**
	 * @return Discount
	 */
	public function getDiscount(): ?Discount {
                  		return $this->discount;
                  	}

	/**
	 * @param Discount $discount
	 *
	 * @return Order
	 */
	public function setDiscount( ?Discount $discount ): self {
                  		$this->discount = $discount;
                  		return $this;
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
	 * @return Order
	 */
	public function setUser( ?User $user ): self {
                  		$this->user = $user;
                  		return $this;
                  	}

	public function getPaymentInstruction()
                  	{
                  		return $this->paymentInstruction;
                  	}

	public function setPaymentInstruction(PaymentInstruction $instruction)
                  	{
                  		$this->paymentInstruction = $instruction;

                  		return $this;
                  	}

	public function getPayIt()
                  	{
                  		return $this->payIt;
                  	}

	public function setPayIt($payIt)
                  	{
                  		$this->payIt = $payIt;

                  		return $this;
                  	}

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setOrder($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getOrder() === $this) {
                $invoice->setOrder(null);
            }
        }

        return $this;
    }
}
