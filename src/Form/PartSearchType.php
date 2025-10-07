<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('query', null, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Search...',
                    'class' => 'w-full p-3 pl-10 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'method' => 'GET',
            'csrf_protection' => false,
            'attr' => [
                'data-turbo' => 'false',
            ],
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }

}
