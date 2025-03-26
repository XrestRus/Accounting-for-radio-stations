<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Depot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Форма для создания и редактирования пользователей системы
 */
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'Имя пользователя',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Введите имя пользователя'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Имя пользователя не должно быть пустым']),
                    new Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Имя пользователя должно быть не менее {{ limit }} символов',
                        'maxMessage' => 'Имя пользователя не должно превышать {{ limit }} символов',
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Пароли должны совпадать',
                'required' => $options['require_password'],
                'first_options' => [
                    'label' => 'Пароль',
                    'attr' => [
                        'class' => 'form-control',
                        'autocomplete' => 'new-password'
                    ],
                    'constraints' => $options['require_password'] ? [
                        new NotBlank(['message' => 'Введите пароль']),
                        new Length([
                            'min' => 6,
                            'minMessage' => 'Пароль должен быть не менее {{ limit }} символов',
                        ]),
                    ] : [],
                ],
                'second_options' => [
                    'label' => 'Подтверждение пароля',
                    'attr' => [
                        'class' => 'form-control',
                        'autocomplete' => 'new-password'
                    ],
                ],
                'mapped' => $options['require_password'],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Роль',
                'choices' => [
                    'Администратор' => 'ROLE_ADMIN',
                    'Оператор' => 'ROLE_USER',
                ],
                'attr' => ['class' => 'form-select'],
                'constraints' => [
                    new NotBlank(['message' => 'Выберите роль пользователя']),
                ],
            ])
            ->add('depot', EntityType::class, [
                'class' => Depot::class,
                'choice_label' => 'name',
                'label' => 'Депо',
                'attr' => ['class' => 'form-select'],
                'required' => false,
                'placeholder' => 'Выберите депо',
            ]);

        // При редактировании делаем пароль опциональным
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $user = $event->getData();
            $form = $event->getForm();

            // Если это редактирование существующего пользователя
            if ($user && $user->getId() !== null && !$options['require_password']) {
                $form->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Пароли должны совпадать',
                    'required' => false,
                    'first_options' => [
                        'label' => 'Пароль (оставьте пустым, чтобы не менять)',
                        'attr' => [
                            'class' => 'form-control',
                            'autocomplete' => 'new-password'
                        ],
                    ],
                    'second_options' => [
                        'label' => 'Подтверждение пароля',
                        'attr' => [
                            'class' => 'form-control',
                            'autocomplete' => 'new-password'
                        ],
                    ],
                    'mapped' => false,
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'require_password' => true,
        ]);
    }
} 