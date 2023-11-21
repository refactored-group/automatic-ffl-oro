<?php

namespace RefactoredGroup\Bundle\AutomaticFFLBundle\Form\Type;

use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use RefactoredGroup\Bundle\AutomaticFFLBundle\Entity\AutomaticFFLSettings;
use Symfony\Component\Form\AbstractType;
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
