<?php

namespace App\Form;

use App\Entity\Employee;
use App\Entity\Depot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Форма для добавления и редактирования сотрудников
 */
class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'ФИО',
                'attr' => [
                    'placeholder' => 'Фамилия Имя Отчество',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Пожалуйста, введите ФИО сотрудника']),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'ФИО не должно превышать {{ limit }} символов'
                    ])
                ]
            ])
            ->add('position', TextType::class, [
                'label' => 'Должность',
                'attr' => [
                    'placeholder' => 'Должность сотрудника',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Пожалуйста, введите должность сотрудника']),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Должность не должна превышать {{ limit }} символов'
                    ])
                ]
            ])
            ->add('department', TextType::class, [
                'label' => 'Подразделение',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Подразделение сотрудника',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Подразделение не должно превышать {{ limit }} символов'
                    ])
                ]
            ])
            ->add('phone', TextType::class, [
                'label' => 'Телефон',
                'required' => false,
                'attr' => [
                    'placeholder' => '+7 (___) ___-__-__',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Length([
                        'max' => 20,
                        'maxMessage' => 'Телефон не должен превышать {{ limit }} символов'
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Электронная почта',
                'required' => false,
                'attr' => [
                    'placeholder' => 'example@domain.com',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Email(['message' => 'Пожалуйста, введите корректный email']),
                    new Length([
                        'max' => 100,
                        'maxMessage' => 'Email не должен превышать {{ limit }} символов'
                    ])
                ]
            ])
            ->add('depot', EntityType::class, [
                'class' => Depot::class,
                'choice_label' => 'name',
                'label' => 'Депо',
                'required' => false,
                'placeholder' => 'Выберите депо',
                'attr' => [
                    'class' => 'form-select'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Employee::class,
        ]);
    }
} 