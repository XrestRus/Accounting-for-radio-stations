<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\LogRepository')]
#[ORM\Table(name: 'logs', options: ['comment' => 'Логи операций в системе'])]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', options: ['comment' => 'Уникальный идентификатор записи лога'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, options: ['comment' => 'Идентификатор пользователя, выполнившего операцию'])]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Device::class)]
    #[ORM\JoinColumn(nullable: true, options: ['comment' => 'Идентификатор устройства (необязательно)'])]
    private ?Device $device = null;

    #[ORM\Column(type: 'string', length: 100, options: ['comment' => 'Описание действия (например, "Выдача устройства", "Возврат устройства")'])]
    private string $action;

    #[ORM\Column(type: 'text', nullable: true, options: ['comment' => 'Дополнительные детали операции (например, серийный номер устройства, ФИО сотрудника)'])]
    private ?string $details = null;

    #[ORM\Column(type: 'json', nullable: true, options: ['comment' => 'Мета-информация об операции в формате JSON (например, дополнительные параметры операции)'])]
    private ?array $detailsMeta = null;

    #[ORM\Column(type: 'datetime', options: ['comment' => 'Дата и время выполнения операции'])]
    private \DateTimeInterface $timestamp;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): self
    {
        $this->device = $device;
        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): self
    {
        $this->details = $details;
        return $this;
    }

    public function getDetailsMeta(): ?array
    {
        return $this->detailsMeta;
    }

    public function setDetailsMeta(?array $detailsMeta): self
    {
        $this->detailsMeta = $detailsMeta;
        return $this;
    }

    public function getTimestamp(): \DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;
        return $this;
    }
} 