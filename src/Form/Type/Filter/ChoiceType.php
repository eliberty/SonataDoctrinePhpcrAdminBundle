<?php

declare(strict_types = 1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter;

use Sonata\AdminBundle\Form\Type\Filter\FilterDataType;
use Sonata\AdminBundle\Form\Type\Operator\ContainsOperatorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as FormChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends AbstractType
{
    public const TYPE_CONTAINS       = ContainsOperatorType::TYPE_CONTAINS;
    public const TYPE_NOT_CONTAINS   = ContainsOperatorType::TYPE_NOT_CONTAINS;
    public const TYPE_EQUAL          = ContainsOperatorType::TYPE_EQUAL;
    public const TYPE_CONTAINS_WORDS = 4;

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'field_type'                => FormChoiceType::class,
            'operator_type'             => ContainsOperatorType::class,
            'choice_translation_domain' => 'SonataDoctrinePHPCRAdmin',
            'choices'                   => [
                'label_type_contains'       => self::TYPE_CONTAINS,
                'label_type_not_contains'   => self::TYPE_NOT_CONTAINS,
                'label_type_equals'         => self::TYPE_EQUAL,
                'label_type_contains_words' => self::TYPE_CONTAINS_WORDS,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', FormChoiceType::class, [
                'required' => false,
            ])
            ->add('value', $options['field_type'], array_merge(['required' => false], $options['field_options']));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'doctrine_phpcr_type_filter_choice';
    }

    public function getParent(): string
    {
        return FilterDataType::class;
    }

    /**
     * NEXT_MAJOR: remove this method.
     */
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }
}
