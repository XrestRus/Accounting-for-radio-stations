<?php

namespace App\Form;

use App\Entity\Device;
use App\Entity\Depot;
use App\Entity\StatusEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class DeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Название устройства',
                'constraints' => [
                    new NotBlank(['message' => 'Пожалуйста, введите название устройства']),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Название устройства не может быть длиннее {{ limit }} символов'
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Тип устройства',
                'choices' => [
                    'Радиостанция' => 'radio',
                    'Носитель информации' => 'carrier',
                    'Устройство безопасности' => 'security'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Пожалуйста, выберите тип устройства'])
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('serialNumber', TextType::class, [
                'label' => 'Серийный номер',
                'constraints' => [
                    new NotBlank(['message' => 'Пожалуйста, введите серийный номер']),
                    new Length([
                        'max' => 50,
                        'maxMessage' => 'Серийный номер не может быть длиннее {{ limit }} символов'
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('qrCode', TextType::class, [
                'label' => 'QR/Штрих-код',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Статус',
                'choices' => [
                    'Доступно' => StatusEnum::AVAILABLE,
                    'Выдано' => StatusEnum::ISSUED,
                    'Неисправно' => StatusEnum::FAULTY,
                    'В ремонте' => StatusEnum::IN_REPAIR,
                    'Списано' => StatusEnum::WRITTEN_OFF
                ],
                'choice_value' => function (?StatusEnum $status) {
                    return $status ? $status->value : '';
                },
                'attr' => ['class' => 'form-select']
            ])
            ->add('writeOffComment', TextareaType::class, [
                'label' => 'Комментарий о причине списания',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])
            ->add('repairComment', TextareaType::class, [
                'label' => 'Комментарий о ремонте',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ]
            ])
            ->add('depot', EntityType::class, [
                'class' => Depot::class,
                'label' => 'Депо',
                'required' => false,
                'choice_label' => 'name',
                'placeholder' => 'Выберите депо',
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Device::class,
        ]);
    }
} 