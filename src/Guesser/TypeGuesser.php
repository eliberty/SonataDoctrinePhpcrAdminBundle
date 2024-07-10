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

use Doctrine\Bundle\PHPCRBundle\ManagerRegistry;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\Form\Type\BooleanType;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * Guesser for displaying fields.
 *
 * Form guesses happen in the FormContractor.
 */
class TypeGuesser implements TypeGuesserInterface
{
    protected ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * TODO: test new implementation.
     */
    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        switch ($fieldDescription->getMappingType()) {
            case 'boolean':
                return new TypeGuess(BooleanType::class, [], Guess::HIGH_CONFIDENCE);
            case 'date':
                return new TypeGuess(DatePickerType::class, [], Guess::HIGH_CONFIDENCE);

            case 'decimal':
            case 'double':
                return new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE);
            case 'integer':
            case 'long':
                return new TypeGuess(NumberType::class, [], Guess::MEDIUM_CONFIDENCE);
            case 'string':
                return new TypeGuess(TextType::class, [], Guess::HIGH_CONFIDENCE);
            case 'binary':
            case 'uri':
                return new TypeGuess(TextType::class, [], Guess::MEDIUM_CONFIDENCE);
            case ClassMetadata::MANY_TO_MANY:
            case 'referrers':
                return new TypeGuess('doctrine_phpcr_many_to_many', [], Guess::HIGH_CONFIDENCE);

            case ClassMetadata::MANY_TO_ONE:
            case 'parent':
                return new TypeGuess('doctrine_phpcr_many_to_one', [], Guess::HIGH_CONFIDENCE);

            case 'children':
                return new TypeGuess('doctrine_phpcr_one_to_many', [], Guess::HIGH_CONFIDENCE);

            case 'child':
                return new TypeGuess('doctrine_phpcr_one_to_one', [], Guess::HIGH_CONFIDENCE);
        }

        return new TypeGuess(TextType::class, [], Guess::LOW_CONFIDENCE);
    }
}
