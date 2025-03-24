<?php

namespace App\Form;

use App\Entity\Transaction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Форма для возврата устройства
 */
class ReturnDeviceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('deviceIdentifier', HiddenType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'Необходимо указать устройство для возврата']),
                ],
            ])
            ->add('returnStatus', ChoiceType::class, [
                'label' => 'Состояние устройства',
                'required' => true,
                'choices' => [
                    'Исправно' => Transaction::RETURN_STATUS_RETURNED_OK,
                    'Неисправно' => Transaction::RETURN_STATUS_RETURNED_FAULTY,
                ],
                'placeholder' => 'Выберите состояние',
                'constraints' => [
                    new NotBlank(['message' => 'Необходимо указать состояние устройства']),
                ],
            ]);
            
        // Закомментировано поле для ввода причины неисправности
        /*
            ->add('comment', TextareaType::class, [
                'label' => 'Комментарий',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Дополнительная информация о возврате устройства',
                ],
                'mapped' => false,
            ]);
        */
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Transaction::class,
        ]);
    }
} 