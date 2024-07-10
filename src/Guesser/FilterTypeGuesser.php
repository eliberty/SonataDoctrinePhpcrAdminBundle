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

namespace Sonata\DoctrinePHPCRAdminBundle\Guesser;

use Doctrine\Bundle\PHPCRBundle\Form\Type\DocumentType;
use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\DoctrinePHPCRAdminBundle\Filter\BooleanFilter;
use Sonata\DoctrinePHPCRAdminBundle\Filter\DateFilter;
use Sonata\DoctrinePHPCRAdminBundle\Filter\NumberFilter;
use Sonata\DoctrinePHPCRAdminBundle\Filter\StringFilter;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class FilterTypeGuesser implements TypeGuesserInterface
{
    protected ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        $options = [
            'field_name'                  => $fieldDescription->getFieldName(),
            'parent_association_mappings' => $fieldDescription->getParentAssociationMappings(),
            'field_type'                  => TextType::class,
            'field_options'               => [],
            'options'                     => [],
        ];

        switch ($fieldDescription->getMappingType()) {
            case 'boolean':
                $options['field_type']    = BooleanType::class;
                $options['field_options'] = [];

                return new TypeGuess(BooleanFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case 'date':
                return new TypeGuess(DateFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case 'decimal':
            case 'float':
                return new TypeGuess(NumberFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case 'integer':
                $options['field_type']    = NumberType::class;
                $options['field_options'] = [
                    'csrf_protection' => false,
                ];

                return new TypeGuess(NumberFilter::class, $options, Guess::HIGH_CONFIDENCE);
            case 'text':
            case 'string':
                $options['field_type'] = TextType::class;

                return new TypeGuess(StringFilter::class, $options, Guess::HIGH_CONFIDENCE);

            case ClassMetadata::MANY_TO_ONE:
            case ClassMetadata::MANY_TO_MANY:
                $mapping = $fieldDescription->getFieldMapping();        // TODO CHECK

                $options['operator_type']    = BooleanType::class;
                $options['operator_options'] = [];

                $options['field_type'] = DocumentType::class;
                if (!empty($mapping['targetDocument'])) {
                    $options['field_options'] = [
                        'class' => $mapping['targetDocument'],
                    ];
                }
                $options['field_name']   = $mapping['fieldName'];
                $options['mapping_type'] = $mapping['type'];

                // no break
            case ClassMetadata::MANY_TO_MANY:
                return new TypeGuess('doctrine_phpcr_many_to_many', $options, Guess::HIGH_CONFIDENCE);

            case ClassMetadata::MANY_TO_ONE:
                return new TypeGuess('doctrine_phpcr_many_to_one', $options, Guess::HIGH_CONFIDENCE);
        }

        return new TypeGuess(StringFilter::class, $options, Guess::LOW_CONFIDENCE);
    }
}
