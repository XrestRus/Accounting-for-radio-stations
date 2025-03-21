<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\TransactionRepository')]
#[ORM\Table(name: 'transactions', options: ['comment' => 'Операции выдачи/возврата устройств'])]
#[ORM\HasLifecycleCallbacks]
class Transaction
{
    public const RETURN_STATUS_RETURNED_OK = 'returned_ok';
    public const RETURN_STATUS_RETURNED_FAULTY = 'returned_faulty';
    public const RETURN_STATUS_UNRETURNABLE = 'unreturnable';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['comment' => 'Уникальный идентификатор операции'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Device::class)]
    #[ORM\JoinColumn(nullable: false, options: ['comment' => 'Идентификатор устройства'])]
    private Device $device;

    #[ORM\ManyToOne(targetEntity: Employee::class)]
    #[ORM\JoinColumn(nullable: false, options: ['comment' => 'Идентификатор сотрудника, которому выдано устройство'])]
    private Employee $employee;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, options: ['comment' => 'Идентификатор пользователя, который выдал устройство'])]
    private User $issuedBy;

    #[ORM\Column(type: 'datetime', options: ['comment' => 'Дата и время выдачи устройства'])]
    private \DateTimeInterface $issuedAt;

    #[ORM\Column(type: 'datetime', nullable: true, options: ['comment' => 'Дата и время возврата устройства'])]
    private ?\DateTimeInterface $returnedAt = null;

    #[ORM\Column(type: 'datetime', options: ['comment' => 'Срок возврата устройства'])]
    private \DateTimeInterface $dueDate;

    #[ORM\Column(type: 'string', length: 20, nullable: true, options: ['comment' => 'Статус возврата (исправно, неисправно, невозвращено)'])]
    private ?string $returnStatus = null;

    #[ORM\Column(type: 'datetime', options: ['comment' => 'Дата и время создания записи'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true, options: ['comment' => 'Дата и время последнего обновления записи'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function setDevice(Device $device): self
    {
        $this->device = $device;
        return $this;
    }

    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    public function setEmployee(Employee $employee): self
    {
        $this->employee = $employee;
        return $this;
    }

    public function getIssuedBy(): User
    {
        return $this->issuedBy;
    }

    public function setIssuedBy(User $issuedBy): self
    {
        $this->issuedBy = $issuedBy;
        return $this;
    }

    public function getIssuedAt(): \DateTimeInterface
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(\DateTimeInterface $issuedAt): self
    {
        $this->issuedAt = $issuedAt;
        return $this;
    }

    public function getReturnedAt(): ?\DateTimeInterface
    {
        return $this->returnedAt;
    }

    public function setReturnedAt(?\DateTimeInterface $returnedAt): self
    {
        $this->returnedAt = $returnedAt;
        return $this;
    }

    public function getDueDate(): \DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeInterface $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getReturnStatus(): ?string
    {
        return $this->returnStatus;
    }

    public function setReturnStatus(?string $returnStatus): self
    {
        $this->returnStatus = $returnStatus;
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

    public function isReturned(): bool
    {
        return $this->returnedAt !== null;
    }

    public function isOverdue(): bool
    {
        if ($this->isReturned()) {
            return false;
        }
        
        return $this->dueDate < new \DateTime();
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