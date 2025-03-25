<?php

namespace App\DataFixtures;

use App\Entity\Depot;
use App\Entity\Device;
use App\Entity\Employee;
use App\Entity\StatusEnum;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Создаем депо
        $depot1 = new Depot();
        $depot1->setName('Депо 1');
        $manager->persist($depot1);

        $depot2 = new Depot();
        $depot2->setName('Депо 2');
        $manager->persist($depot2);

        // Создаем администраторов
        $admin1 = new User();
        $admin1->setUsername('admin');
        $admin1->setRole('admin');
        $admin1->setDepot($depot1);
        $admin1->setPassword($this->passwordHasher->hashPassword($admin1, 'admin'));
        $manager->persist($admin1);

        // Создаем операторов
        $operator1 = new User();
        $operator1->setUsername('operator1');
        $operator1->setRole('operator');
        $operator1->setDepot($depot1);
        $operator1->setPassword($this->passwordHasher->hashPassword($operator1, 'operator1'));
        $manager->persist($operator1);

        $operator2 = new User();
        $operator2->setUsername('operator2');
        $operator2->setRole('operator');
        $operator2->setDepot($depot2);
        $operator2->setPassword($this->passwordHasher->hashPassword($operator2, 'operator2'));
        $manager->persist($operator2);

        // Создаем сотрудников
        $employee1 = new Employee();
        $employee1->setFullName('Иванов Иван Иванович');
        $employee1->setPosition('Машинист');
        $employee1->setDepartment('Локомотивное депо');
        $employee1->setPhone('+7 999 123 45 67');
        $employee1->setEmail('ivanov@example.com');
        $employee1->setDepot($depot1);
        $manager->persist($employee1);

        $employee2 = new Employee();
        $employee2->setFullName('Петров Петр Петрович');
        $employee2->setPosition('Дежурный по депо');
        $employee2->setDepartment('Ремонтный цех');
        $employee2->setPhone('+7 999 987 65 43');
        $employee2->setEmail('petrov@example.com');
        $employee2->setDepot($depot1);
        $manager->persist($employee2);

        $employee3 = new Employee();
        $employee3->setFullName('Сидоров Сидор Сидорович');
        $employee3->setPosition('Помощник машиниста');
        $employee3->setDepartment('Локомотивное депо');
        $employee3->setPhone('+7 999 111 22 33');
        $employee3->setEmail('sidorov@example.com');
        $employee3->setDepot($depot2);
        $manager->persist($employee3);

        // Создаем устройства
        $device1 = new Device();
        $device1->setName('Радиостанция АК-9');
        $device1->setType('Радиостанция');
        $device1->setSerialNumber('SN123456');
        $device1->setQrCode('QR123');
        $device1->setStatus(StatusEnum::AVAILABLE);
        $device1->setDepot($depot1);
        $manager->persist($device1);

        $device2 = new Device();
        $device2->setName('Носитель КР-М');
        $device2->setType('Носитель');
        $device2->setSerialNumber('SN654321');
        $device2->setQrCode('QR456');
        $device2->setStatus(StatusEnum::ISSUED);
        $device2->setDepot($depot1);
        $manager->persist($device2);

        $device3 = new Device();
        $device3->setName('БЛОК');
        $device3->setType('Носитель');
        $device3->setSerialNumber('SN789012');
        $device3->setQrCode('QR789');
        $device3->setStatus(StatusEnum::AVAILABLE);
        $device3->setDepot($depot2);
        $manager->persist($device3);

        $device4 = new Device();
        $device4->setName('МПМЭ-128');
        $device4->setType('Носитель');
        $device4->setSerialNumber('SN345678');
        $device4->setQrCode('QR345');
        $device4->setStatus(StatusEnum::FAULTY);
        $device4->setDepot($depot1);
        $manager->persist($device4);

        $device5 = new Device();
        $device5->setName('ТСКБМ-Н');
        $device5->setType('Носитель');
        $device5->setSerialNumber('SN901234');
        $device5->setQrCode('QR901');
        $device5->setStatus(StatusEnum::IN_REPAIR);
        $device5->setRepairComment('На ремонте: неисправная батарея');
        $device5->setDepot($depot2);
        $manager->persist($device5);

        // Создаем операции
        $transaction1 = new Transaction();
        $transaction1->setDevice($device2);
        $transaction1->setEmployee($employee1);
        $transaction1->setIssuedBy($operator1);
        $transaction1->setIssuedAt(new \DateTime('2023-03-15 12:00:00'));
        $transaction1->setDueDate(new \DateTime('2023-03-18 12:00:00'));
        $manager->persist($transaction1);

        $transaction2 = new Transaction();
        $transaction2->setDevice($device1);
        $transaction2->setEmployee($employee2);
        $transaction2->setIssuedBy($operator1);
        $transaction2->setIssuedAt(new \DateTime('2023-03-01 10:00:00'));
        $transaction2->setDueDate(new \DateTime('2023-03-05 18:00:00'));
        $transaction2->setReturnedAt(new \DateTime('2023-03-05 17:30:00'));
        $transaction2->setReturnStatus(Transaction::RETURN_STATUS_RETURNED_OK);
        $manager->persist($transaction2);

        $manager->flush();
    }
} 