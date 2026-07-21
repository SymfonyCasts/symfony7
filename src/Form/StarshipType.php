<?php

namespace App\Form;

use App\Entity\Starship;
use App\Entity\StarshipStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StarshipType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        dd($options);
        /** @var Starship $starship */
        $starship = $options['data'];
        $isEdit = $starship && $starship->getId();
        if ($isEdit) {
            $builder->add('status', EnumType::class, [
                'class' => StarshipStatusEnum::class,
            ]);
        }

        $builder
            ->add('slug', null, [
                'attr' => [
                    //'readonly' => $isEdit,
                ],
                'disabled' => $isEdit,
            ])
            ->add('name')
            ->add('class')
            ->add('captain')
            ->add('arrivedAt', null, [
                'widget' => 'single_text',
            ])
            ->add('createdAt')
            ->add('updatedAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Starship::class,
        ]);
    }
}
