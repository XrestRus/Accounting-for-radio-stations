<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\DeviceRepository')]
#[ORM\Table(name: 'devices', options: ['comment' => 'Устройства (радиостанции, носители информации и др.)'])]
#[ORM\HasLifecycleCallbacks]
class Device
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_ISSUED = 'issued';
    public const STATUS_FAULTY = 'faulty';
    public const STATUS_IN_REPAIR = 'in_repair';
    public const STATUS_WRITTEN_OFF = 'written_off';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['comment' => 'Уникальный идентификатор устройства'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, options: ['comment' => 'Название устройства'])]
    private string $name;

    #[ORM\Column(type: 'string', length: 50, options: ['comment' => 'Тип устройства (например, радиостанция, носитель информации)'])]
    private string $type;

    #[ORM\Column(type: 'string', length: 50, unique: true, options: ['comment' => 'Серийный номер устройства'])]
    private string $serialNumber;

    #[ORM\Column(type: 'string', length: 100, nullable: true, options: ['comment' => 'QR-код или штрих-код устройства'])]
    private ?string $qrCode = null;

    #[ORM\Column(type: 'string', length: 20, options: ['comment' => 'Текущий статус устройства (доступно, выдано, неисправно, в ремонте, списано)'])]
    private string $status = self::STATUS_AVAILABLE;

    #[ORM\Column(type: 'text', nullable: true, options: ['comment' => 'Комментарий о причине списания устройства'])]
    private ?string $writeOffComment = null;

    #[ORM\Column(type: 'datetime', nullable: true, options: ['comment' => 'Дата списания устройства'])]
    private ?\DateTimeInterface $writeOffDate = null;

    #[ORM\Column(type: 'text', nullable: true, options: ['comment' => 'Комментарий о ремонте устройства'])]
    private ?string $repairComment = null;

    #[ORM\ManyToOne(targetEntity: Depot::class, inversedBy: 'devices')]
    #[ORM\JoinColumn(nullable: true, options: ['comment' => 'Идентификатор депо, к которому привязано устройство'])]
    private ?Depot $depot = null;

    #[ORM\Column(type: 'datetime', options: ['comment' => 'Дата и время создания записи'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true, options: ['comment' => 'Дата и время последнего обновления записи'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;
        return $this;
    }

    public function getQrCode(): ?string
    {
        return $this->qrCode;
    }

    public function setQrCode(?string $qrCode): self
    {
        $this->qrCode = $qrCode;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getWriteOffComment(): ?string
    {
        return $this->writeOffComment;
    }

    public function setWriteOffComment(?string $writeOffComment): self
    {
        $this->writeOffComment = $writeOffComment;
        return $this;
    }

    public function getWriteOffDate(): ?\DateTimeInterface
    {
        return $this->writeOffDate;
    }

    public function setWriteOffDate(?\DateTimeInterface $writeOffDate): self
    {
        $this->writeOffDate = $writeOffDate;
        return $this;
    }

    public function getRepairComment(): ?string
    {
        return $this->repairComment;
    }

    public function setRepairComment(?string $repairComment): self
    {
        $this->repairComment = $repairComment;
        return $this;
    }

    public function getDepot(): ?Depot
    {
        return $this->depot;
    }

    public function setDepot(?Depot $depot): self
    {
        $this->depot = $depot;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
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

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isIssued(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function isFaulty(): bool
    {
        return $this->status === self::STATUS_FAULTY;
    }

    public function isInRepair(): bool
    {
        return $this->status === self::STATUS_IN_REPAIR;
    }

    public function isWrittenOff(): bool
    {
        return $this->status === self::STATUS_WRITTEN_OFF;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
} 