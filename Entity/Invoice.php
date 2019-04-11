<?php

namespace Octopouce\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Octopouce\ShopBundle\Repository\InvoiceRepository")
 * @ORM\Table(name="shop_invoice")
 * @ORM\HasLifecycleCallbacks()
 */
class Invoice
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Octopouce\ShopBundle\Entity\Order", inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $order;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="integer")
     */
    private $itemsTotal;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $itemsPriceTotal;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=2)
     */
    private $priceTotal;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sendAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $payIt;

    /**
     * @ORM\Column(type="text")
     */
    private $address;

    /**
     * @ORM\Column(type="text")
     */
    private $addressClient;

    /**
     * @ORM\Column(type="json_array")
     */
    private $items;

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

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getItemsTotal(): ?int
    {
        return $this->itemsTotal;
    }

    public function setItemsTotal(?int $itemsTotal): self
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

    public function setPriceTotal($priceTotal): self
    {
        $this->priceTotal = $priceTotal;

        return $this;
    }

    public function getSendAt(): ?\DateTimeInterface
    {
        return $this->sendAt;
    }

    public function setSendAt(?\DateTimeInterface $sendAt): self
    {
        $this->sendAt = $sendAt;

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

    public function getPayIt(): ?\DateTimeInterface
    {
        return $this->payIt;
    }

    public function setPayIt(?\DateTimeInterface $payIt): self
    {
        $this->payIt = $payIt;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getAddressClient(): ?string
    {
        return $this->addressClient;
    }

    public function setAddressClient(?string $addressClient): self
    {
        $this->addressClient = $addressClient;

        return $this;
    }

    public function getItems()
    {
        return json_decode($this->items, true);
    }

    public function setItems($items): self
    {
        $this->items = $items;

        return $this;
    }
}
