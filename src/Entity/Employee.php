<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\EmployeeRepository')]
#[ORM\Table(name: 'employees', options: ['comment' => 'Сотрудники, получающие устройства'])]
#[ORM\HasLifecycleCallbacks]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['comment' => 'Уникальный идентификатор сотрудника'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100, options: ['comment' => 'ФИО сотрудника'])]
    private string $fullName;

    #[ORM\Column(type: 'string', length: 100, options: ['comment' => 'Должность сотрудника'])]
    private string $position;

    #[ORM\Column(type: 'string', length: 100, nullable: true, options: ['comment' => 'Подразделение или отдел сотрудника'])]
    private ?string $department = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true, options: ['comment' => 'Контактный телефон сотрудника'])]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true, options: ['comment' => 'Электронная почта сотрудника'])]
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity: Depot::class, inversedBy: 'employees')]
    #[ORM\JoinColumn(nullable: true, options: ['comment' => 'Идентификатор депо, к которому привязан сотрудник'])]
    private ?Depot $depot = null;

    #[ORM\Column(type: 'datetime', options: ['comment' => 'Дата и время создания записи'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true, options: ['comment' => 'Дата и время последнего обновления записи'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'datetime', nullable: true, options: ['comment' => 'Дата и время удаления записи (если запись удалена)'])]
    private ?\DateTimeInterface $deletedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;
        return $this;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
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

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
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