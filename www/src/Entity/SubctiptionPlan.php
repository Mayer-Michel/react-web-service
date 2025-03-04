<?php

namespace App\Entity;

use ApiPlatform\Metadata\Get;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\SubscriptionPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SubscriptionPlanRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get()
    ],
    normalizationContext: ['groups' => ['subscriptionPlan:read']],
)]
class SubscriptionPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['subscriptionPlan:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['subscriptionPlan:read'])]
    private ?string $name = null;

    #[ORM\Column]
    #[Groups(['subscriptionPlan:read'])]
    private ?int $price = null;

    #[ORM\Column(length: 50)]
    #[Groups(['subscriptionPlan:read'])]
    private ?string $period = null;

    #[ORM\Column(length: 255)]
    #[Groups(['subscriptionPlan:read'])]
    private ?string $stripeProductId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['subscriptionPlan:read'])]
    private ?string $stripePriceId = null;

    #[ORM\Column]
    #[Groups(['subscriptionPlan:read'])]
    private ?bool $isFeatured = null;

    /**
     * @var Collection<int, UserSubscription>
     */
    #[ORM\OneToMany(mappedBy: 'plan', targetEntity: UserSubscription::class)]
    #[Groups(['subscriptionPlan:read'])]
    private Collection $userSubscriptions;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['subscriptionPlan:read'])]
    private ?string $discount = null;

    public function __construct()
    {
        $this->userSubscriptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getPeriod(): ?string
    {
        return $this->period;
    }

    public function setPeriod(string $period): static
    {
        $this->period = $period;

        return $this;
    }

    public function getStripeProductId(): ?string
    {
        return $this->stripeProductId;
    }

    public function setStripeProductId(string $stripeProductId): static
    {
        $this->stripeProductId = $stripeProductId;

        return $this;
    }

    public function getStripePriceId(): ?string
    {
        return $this->stripePriceId;
    }

    public function setStripePriceId(?string $stripePriceId): static
    {
        $this->stripePriceId = $stripePriceId;

        return $this;
    }

    public function isFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function setFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    /**
     * @return Collection<int, UserSubscription>
     */
    public function getUserSubscriptions(): Collection
    {
        return $this->userSubscriptions;
    }

    public function addUserSubscription(UserSubscription $userSubscription): static
    {
        if (!$this->userSubscriptions->contains($userSubscription)) {
            $this->userSubscriptions->add($userSubscription);
            $userSubscription->setPlan($this);
        }

        return $this;
    }

    public function removeUserSubscription(UserSubscription $userSubscription): static
    {
        if ($this->userSubscriptions->removeElement($userSubscription)) {
            // set the owning side to null (unless already changed)
            if ($userSubscription->getPlan() === $this) {
                $userSubscription->setPlan(null);
            }
        }

        return $this;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function setDiscount(?string $discount): static
    {
        $this->discount = $discount;

        return $this;
    }
}