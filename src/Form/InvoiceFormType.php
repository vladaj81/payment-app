<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class InvoiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class, [
                'data' => 1
            ])
            ->add('first_name', TextType::class, [
                'data' => 'Nikola'
            ])
            ->add('last_name', TextType::class, [
                'data' => 'Pavlovic'
            ])
            ->add('phone_number', TextType::class, [
                'data' => '+381 69 55 33'
            ])
            ->add('email', EmailType::class, [
                'data' => 'test@test.com'
            ])
            ->add('merchant_order_id', IntegerType::class)
            ->add('amount', MoneyType::class)
            ->add('country', TextType::class)
            ->add('currency', TextType::class)
            ->add('payment_method', TextType::class)
            ->add('submit', SubmitType::class, [
                'label' => 'Create invoice'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    
    }
}
