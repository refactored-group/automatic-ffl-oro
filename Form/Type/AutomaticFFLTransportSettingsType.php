<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Form\Type;

use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Entity\AutomaticFFLSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form type for Automatic FFL integration settings
 */
class AutomaticFFLTransportSettingsType extends AbstractType
{
    private const BLOCK_PREFIX = 'rfg_automatic_ffl_settings';

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'labels',
            LocalizedFallbackValueCollectionType::class,
            [
                'label'    => 'refactored_group.automatic_ffl.settings.labels.label',
                'required' => true,
                'entry_options'  => ['constraints' => [new NotBlank()]],
            ]
        );
        $builder->add(
            'autofflStoreHash',
            TextType::class,
            [
                'label' => 'refactored_group.automatic_ffl.settings.store_hash.label',
                'required' => true,
                'attr' => ['autocomplete' => 'on'],
            ]
        );
        $builder->add(
            'autofflTestMode',
            ChoiceType::class,
            [
                'label' => 'refactored_group.automatic_ffl.settings.test_mode.label',
                'required' => false,
                'choices' => [
                    'refactored_group.automatic_ffl.settings.test_mode.choices.no.label' => 0,
                    'refactored_group.automatic_ffl.settings.test_mode.choices.yes.label' => 1,
                ]
            ]
        );
        $builder->add(
            'autofflMapsApiKey',
            TextType::class,
            [
                'label' => 'refactored_group.automatic_ffl.settings.maps_api_key.label',
                'required' => true,
                'attr' => ['autocomplete' => 'on'],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AutomaticFFLSettings::class
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }
}
