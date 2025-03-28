<?php

namespace App\Tests\Entity;

use App\Entity\Device;
use App\Entity\StatusEnum;
use App\Entity\Depot;
use PHPUnit\Framework\TestCase;

/**
 * Набор тестов для класса Device
 */
class DeviceTest extends TestCase
{
    private Device $device;

    protected function setUp(): void
    {
        $this->device = new Device();
        $this->device->setName('Тестовое устройство');
        $this->device->setType('radio');
        $this->device->setSerialNumber('TEST123456');
        $this->device->setStatus(StatusEnum::AVAILABLE);
    }

    public function testInitialState(): void
    {
        // Проверка начального состояния объекта
        $this->assertEquals('Тестовое устройство', $this->device->getName());
        $this->assertEquals('radio', $this->device->getType());
        $this->assertEquals('TEST123456', $this->device->getSerialNumber());
        $this->assertEquals(StatusEnum::AVAILABLE, $this->device->getStatus());
        $this->assertNull($this->device->getId());
        $this->assertNull($this->device->getQrCode());
        $this->assertNull($this->device->getDepot());
        $this->assertNull($this->device->getWriteOffComment());
        $this->assertNull($this->device->getWriteOffDate());
        $this->assertNull($this->device->getRepairComment());
        $this->assertNull($this->device->getUpdatedAt());
    }

    public function testStatusEnumMethods(): void
    {
        // Проверка методов статуса с использованием значений enum
        $this->assertEquals(StatusEnum::AVAILABLE, $this->device->getStatus());
        $this->assertEquals(StatusEnum::AVAILABLE->value, 'available');

        // Изменяем статус на "выдано"
        $this->device->setStatus(StatusEnum::ISSUED);
        $this->assertEquals(StatusEnum::ISSUED, $this->device->getStatus());
        $this->assertEquals(StatusEnum::ISSUED->value, 'issued');

        // Изменяем статус на "неисправно"
        $this->device->setStatus(StatusEnum::FAULTY);
        $this->assertEquals(StatusEnum::FAULTY, $this->device->getStatus());
        $this->assertEquals(StatusEnum::FAULTY->value, 'faulty');

        // Изменяем статус на "в ремонте"
        $this->device->setStatus(StatusEnum::IN_REPAIR);
        $this->assertEquals(StatusEnum::IN_REPAIR, $this->device->getStatus());
        $this->assertEquals(StatusEnum::IN_REPAIR->value, 'in_repair');

        // Изменяем статус на "списано"
        $this->device->setStatus(StatusEnum::WRITTEN_OFF);
        $this->assertEquals(StatusEnum::WRITTEN_OFF, $this->device->getStatus());
        $this->assertEquals(StatusEnum::WRITTEN_OFF->value, 'written_off');
    }

    public function testQrCodeSetting(): void
    {
        // Проверка установки QR-кода
        $this->device->setQrCode('QR123456');
        $this->assertEquals('QR123456', $this->device->getQrCode());

        // Проверка установки пустого QR-кода
        $this->device->setQrCode(null);
        $this->assertNull($this->device->getQrCode());
    }

    public function testDepotAssignment(): void
    {
        // Создаем тестовый объект депо
        $depot = new Depot();
        $depot->setName('Тестовое депо');

        // Проверка присвоения депо устройству
        $this->device->setDepot($depot);
        $this->assertSame($depot, $this->device->getDepot());
        $this->assertEquals('Тестовое депо', $this->device->getDepot()->getName());

        // Проверка удаления связи с депо
        $this->device->setDepot(null);
        $this->assertNull($this->device->getDepot());
    }

    public function testWriteOffProcess(): void
    {
        // Проверка процесса списания устройства
        $writeOffDate = new \DateTime();
        $this->device->setStatus(StatusEnum::WRITTEN_OFF);
        $this->device->setWriteOffDate($writeOffDate);
        $this->device->setWriteOffComment('Техническая неисправность');

        $this->assertEquals(StatusEnum::WRITTEN_OFF, $this->device->getStatus());
        $this->assertEquals($writeOffDate, $this->device->getWriteOffDate());
        $this->assertEquals('Техническая неисправность', $this->device->getWriteOffComment());
    }

    public function testRepairProcess(): void
    {
        // Проверка процесса ремонта устройства
        $this->device->setStatus(StatusEnum::IN_REPAIR);
        $this->device->setRepairComment('Требуется замена экрана');

        $this->assertEquals(StatusEnum::IN_REPAIR, $this->device->getStatus());
        $this->assertEquals('Требуется замена экрана', $this->device->getRepairComment());
    }

    public function testLifecycleCallbacks(): void
    {
        // Проверка колбэков жизненного цикла
        $this->device->prePersist();
        $this->assertInstanceOf(\DateTimeInterface::class, $this->device->getCreatedAt());
        
        $initialCreatedAt = $this->device->getCreatedAt();
        $this->assertNull($this->device->getUpdatedAt());
        
        // Тестируем обновление
        sleep(1); // Ждем секунду для гарантии различия в timestamp
        $this->device->preUpdate();
        $this->assertInstanceOf(\DateTimeInterface::class, $this->device->getUpdatedAt());
        
        // Проверяем, что createdAt не изменился, а updatedAt установлен
        $this->assertEquals($initialCreatedAt, $this->device->getCreatedAt());
        $this->assertNotNull($this->device->getUpdatedAt());
    }
} 