<?php

namespace Octopouce\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Octopouce\ShopBundle\Repository\OrderItemRepository")
 * @ORM\Table(name="shop_order_item")
 * @ORM\HasLifecycleCallbacks()
 */
class OrderItem {
	/**
	 * @ORM\Id()
	 * @ORM\GeneratedValue()
	 * @ORM\Column(type="integer")
	 */
	private $id;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;

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
	private $quantity;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2)
	 */
	private $price;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=2)
	 */
	private $priceTotal;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $digital;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $classLinked;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $idLinked;

	private $articleLinked;

	/**
	 * @var Order
	 *
	 * @ORM\ManyToOne(targetEntity="Octopouce\ShopBundle\Entity\Order", inversedBy="items")
	 * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $order;

	public function __construct() {
		$this->createdAt = new \DateTime();
		$this->updatedAt = new \DateTime();
		$this->digital = false;
	}

	/**
	 * @ORM\PreUpdate
	 */
	public function setUpdatedAtValue() {
		$this->updatedAt = new \DateTime();
	}

	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param mixed $name
	 *
	 * @return OrderItem
	 */
	public function setName( $name ) {
		$this->name = $name;

		return $this;
	}

	public function getCreatedAt(): ?\DateTimeInterface {
		return $this->createdAt;
	}

	public function setCreatedAt( \DateTimeInterface $createdAt ): self {
		$this->createdAt = $createdAt;

		return $this;
	}

	public function getUpdatedAt(): ?\DateTimeInterface {
		return $this->updatedAt;
	}

	public function setUpdatedAt( \DateTimeInterface $updatedAt ): self {
		$this->updatedAt = $updatedAt;

		return $this;
	}

	public function getQuantity(): ?int {
		return $this->quantity;
	}

	public function setQuantity( int $quantity ): self {
		$this->quantity = $quantity;

		return $this;
	}

	public function getPriceTotal() {
		return $this->priceTotal;
	}

	public function setPriceTotal( $priceTotal ): self {
		$this->priceTotal = $priceTotal;

		return $this;
	}

	public function getPrice() {
		return $this->price;
	}

	public function setPrice( $price ): self {
		$this->price = $price;

		return $this;
	}

	public function getOrder(): Order {
		return $this->order;
	}

	public function setOrder( Order $order ): self {
		$this->order = $order;

		return $this;
	}

	public function getDigital() {
		return $this->digital;
	}

	public function setDigital( $digital ) {
		$this->digital = $digital;

		return $this;
	}

	public function getClassLinked() {
		return $this->classLinked;
	}

	public function setClassLinked( $classLinked ) {
		$this->classLinked = $classLinked;

		return $this;
	}

	public function getIdLinked() {
		return $this->idLinked;
	}

	public function setIdLinked( $idLinked ) {
		$this->idLinked = $idLinked;

		return $this;
	}

	public function getArticleLinked() {
		return $this->articleLinked;
	}

	public function setArticleLinked( $articleLinked ) {
		$this->articleLinked = $articleLinked;

		return $this;
	}




}
