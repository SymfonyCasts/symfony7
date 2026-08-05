<?php

namespace App\Form;

use App\Entity\Starship;
use App\Entity\StarshipStatusEnum;
use App\Form\DataTransformer\TagsToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

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
                'disabled' => $isEdit && !$options['is_admin'],
            ])
            ->add('name')
            ->add('class')
            ->add('commander', null, [
//                'getter' => function (Starship $starship): ?string {
//                    return $starship->getCaptain();
//                },
//                'setter' => function (Starship $starship, ?string $value): void {
//                    $starship->setCaptain($value ?? '');
//                },
                'property_path' => 'captain',
                'required' => true,
//                'help' => sprintf('The captain will command %s droids on the starship', $starship->getStarshipDroids()->count()),
//                'help' => 'form.starship.captain_droids',
//                'help_translation_parameters' => [
//                    'count' => $starship->getStarshipDroids()->count(),
//                ],
                'help' => new TranslatableMessage('form.starship.captain_droids', [
                    'count' => $starship->getStarshipDroids()->count(),
                ]),
            ])
            ->add('arrivedAt', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
//            ->add('createdAt')
//            ->add('updatedAt')
            ->add('tags', null, [
                'required' => false,
                'invalid_message' => sprintf('An unknown tag is used. Known tags: %s.', implode(', ', TagsToStringTransformer::KNOWN_TAGS)),
            ])
            ->add('parts', CollectionType::class, [
                'entry_type' => EmbeddedStarshipPartType::class,
                'label' => false,
            ])
        ;

        $builder->get('tags')
            ->addViewTransformer(new TagsToStringTransformer());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Starship::class,
            'is_admin' => false,
            'attr' => [
                'novalidate' => true,
            ],
            'validation_groups' => function (FormInterface $form) {
                /** @var Starship $starship */
                $starship = $form->getData();
                $isEdit = $starship && $starship->getId();

                return $isEdit ? ['Default', 'edit'] : ['Default'];
            },
        ]);
        $resolver->setAllowedTypes('is_admin', 'bool');
    }
}
