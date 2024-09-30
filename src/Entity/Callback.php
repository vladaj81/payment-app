<?php

namespace App\Entity;

use App\Repository\CallbackRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CallbackRepository::class)]
class Callback
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $merchant_order_id = null;

    #[ORM\Column]
    private array $callbackData = [];

    #[ORM\Column]
    private \DateTimeImmutable $created_at;

    #[ORM\Column]
    private \DateTimeImmutable $updated_at;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->updated_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMerchantOrderId(): ?int
    {
        return $this->merchant_order_id;
    }

    public function setMerchantOrderId(int $merchant_order_id): static
    {
        $this->merchant_order_id = $merchant_order_id;

        return $this;
    }

    public function getCallbackData(): array
    {
        return $this->callbackData;
    }

    public function setCallbackData(array $callbackData): static
    {
        $this->callbackData = $callbackData;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
