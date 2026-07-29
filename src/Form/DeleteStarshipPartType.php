<?php

namespace App\Form;

use App\Entity\StarshipPart;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;

class DeleteStarshipPartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var StarshipPart $part */
        $part = $options['data'];

        $builder
            ->add('confirmName', TextType::class, [
                'mapped' => false,
                'label' => false,
                'help' => sprintf('Type "%s" to confirm deletion', $part->getName()),
                'constraints' => [
                    new EqualTo(
                        value: $part->getName(),
                        message: 'The name does not match this part',
                    ),
                ],
            ])
            ->add('delete', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StarshipPart::class,
        ]);
    }
}
