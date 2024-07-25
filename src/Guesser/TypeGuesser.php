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

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

/**
 * Guesser for displaying fields.
 *
 * Form guesses happen in the FormContractor.
 */
class TypeGuesser implements TypeGuesserInterface
{
    public function guess(FieldDescriptionInterface $fieldDescription): TypeGuess
    {
        switch ($fieldDescription->getMappingType()) {
            case 'boolean':
                return new TypeGuess(FieldDescriptionInterface::TYPE_BOOLEAN, [], Guess::HIGH_CONFIDENCE);
            case 'date':
                return new TypeGuess(FieldDescriptionInterface::TYPE_DATE, [], Guess::HIGH_CONFIDENCE);
            case 'decimal':
            case 'double':
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
            case 'integer':
            case 'long':
                return new TypeGuess(FieldDescriptionInterface::TYPE_INTEGER, [], Guess::MEDIUM_CONFIDENCE);
            case 'string':
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::HIGH_CONFIDENCE);
            case 'binary':
            case 'uri':
                return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::MEDIUM_CONFIDENCE);
            case ClassMetadata::MANY_TO_MANY:
            case 'referrers':
                return new TypeGuess(FieldDescriptionInterface::TYPE_MANY_TO_MANY, [], Guess::HIGH_CONFIDENCE);

            case ClassMetadata::MANY_TO_ONE:
            case 'parent':
                return new TypeGuess(FieldDescriptionInterface::TYPE_MANY_TO_ONE, [], Guess::HIGH_CONFIDENCE);

            case 'children':
                return new TypeGuess(FieldDescriptionInterface::TYPE_ONE_TO_MANY, [], Guess::HIGH_CONFIDENCE);

            case 'child':
                return new TypeGuess(FieldDescriptionInterface::TYPE_ONE_TO_ONE, [], Guess::HIGH_CONFIDENCE);
        }

        return new TypeGuess(FieldDescriptionInterface::TYPE_STRING, [], Guess::LOW_CONFIDENCE);
    }
}
