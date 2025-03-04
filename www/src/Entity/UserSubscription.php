<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\Get;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\UserSubscriptionRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserSubscriptionRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get()
    ],
    normalizationContext: ['groups' => ['subscriptionPlan:read']],
)]

#[ApiFilter(
    SearchFilter::class,
    properties: [
        'user.email' => 'exact'
    ]
)]
class UserSubscription
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['subscription:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userSubscriptions')]
    #[Groups(['subscription:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userSubscriptions')]
    #[Groups(['subscription:read'])]
    private ?SubscriptionPlan $plan = null;

    #[ORM\Column(length: 255)]
    #[Groups(['subscription:read'])]
    private ?string $stripeSubsciptionId = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['subscription:read'])]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['subscription:read'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPlan(): ?SubscriptionPlan
    {
        return $this->plan;
    }

    public function setPlan(?SubscriptionPlan $plan): static
    {
        $this->plan = $plan;

        return $this;
    }

    public function getStripeSubsciptionId(): ?string
    {
        return $this->stripeSubsciptionId;
    }

    public function setStripeSubsciptionId(string $stripeSubsciptionId): static
    {
        $this->stripeSubsciptionId = $stripeSubsciptionId;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}