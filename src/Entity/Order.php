<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $reference = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Address $delivery_address = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Carrier $carrier = null;

    #[ORM\OneToMany(mappedBy: 'order_id', targetEntity: OrderDetails::class, orphanRemoval: true, cascade: ['persist'])]
    private Collection $orderDetails;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?OrderState $orderState = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $billingAddress = null;

    #[ORM\Column(length: 255)]
    private ?string $method = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSession = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paypal = null;
    
    #[ORM\Column(length: 20)]
    private ?string $firstName = null;
    
    #[ORM\Column(length: 20)]
    private ?string $lastName = null;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getDeliveryAddress(): ?Address
    {
        return $this->delivery_address;
    }

    public function setDeliveryAddress(?Address $delivery_address): self
    {
        $this->delivery_address = $delivery_address;

        return $this;
    }

    public function getCarrier(): ?Carrier
    {
        return $this->carrier;
    }

    public function setCarrier(?Carrier $carrier): self
    {
        $this->carrier = $carrier;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): self
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setOrderId($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): self
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getOrderId() === $this) {
                $orderDetail->setOrderId(null);
            }
        }

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

    public function getOrderState(): ?OrderState
    {
        return $this->orderState;
    }

    public function setOrderState(?OrderState $orderState): self
    {
        $this->orderState = $orderState;

        return $this;
    }

    public function getBillingAddress(): ?Address
    {
        return $this->billingAddress;
    }

    public function setBillingAddress(?Address $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getStripeSession(): ?string
    {
        return $this->stripeSession;
    }

    public function setStripeSession(?string $stripeSession): self
    {
        $this->stripeSession = $stripeSession;

        return $this;
    }

    public function getPaypal(): ?string
    {
        return $this->paypal;
    }

    public function setPaypal(?string $paypal): self
    {
        $this->paypal = $paypal;

        return $this;
    }
}
