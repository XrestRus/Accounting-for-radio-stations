<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class IssueDeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('employee', EntityType::class, [
                'class' => Employee::class,
                'choice_label' => 'fullName',
                'label' => 'Сотрудник',
                'placeholder' => 'Выберите сотрудника',
                'required' => true,
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, выберите сотрудника',
                    ]),
                ],
            ])
            ->add('deviceIdentifier', TextType::class, [
                'mapped' => false,
                'label' => 'Серийный номер или QR-код',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Сканируйте или введите серийный номер/QR-код',
                ],
            ])
            ->add('dueDate', DateTimeType::class, [
                'label' => 'Срок возврата',
                'widget' => 'single_text',
                'required' => true,
                'html5' => true,
                'input' => 'datetime',
                'input_format' => 'Y-m-d H:i',
                'with_seconds' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пожалуйста, укажите срок возврата',
                    ]),
                ],
                'model_timezone' => 'Europe/Moscow',
                'view_timezone' => 'Europe/Moscow',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
} 