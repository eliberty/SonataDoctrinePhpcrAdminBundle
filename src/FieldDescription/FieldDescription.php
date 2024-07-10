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

namespace Sonata\DoctrinePHPCRAdminBundle\FieldDescription;

use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Sonata\AdminBundle\FieldDescription\BaseFieldDescription;

final class FieldDescription extends BaseFieldDescription
{
    public function getTargetModel(): ?string
    {
        if (isset($this->associationMapping['targetDocument'])) {
            return $this->associationMapping['targetDocument'];
        }

        if (isset($this->associationMapping['referringDocument'])) {
            return $this->associationMapping['referringDocument'];
        }

        return null;
    }

    public function isIdentifier(): bool
    {
        return $this->fieldMapping['id'] ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($object)
    {
        foreach ($this->parentAssociationMappings as $parentAssociationMapping) {
            $object = $this->getFieldValue($object, $parentAssociationMapping['fieldName']);
        }

        return $this->getFieldValue($object, $this->getFieldName());
    }

    public function describesSingleValuedAssociation(): bool
    {
        return \is_int($this->getMappingType()) && $this->getMappingType() === ($this->getMappingType() & ClassMetadata::MANY_TO_ONE);
    }

    public function describesCollectionValuedAssociation(): bool
    {
        return \is_int($this->getMappingType()) && $this->getMappingType() === ($this->getMappingType() & ClassMetadata::MANY_TO_MANY);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if the mapping information is not an array
     */
    public function setFieldMapping(array $fieldMapping): void
    {
        if (!\is_array($fieldMapping)) {
            throw new \InvalidArgumentException('The field mapping must be an array');
        }

        $this->fieldMapping = $fieldMapping;

        $this->type        = $this->type ?: $fieldMapping['type'];
        $this->mappingType = $this->mappingType ?: $fieldMapping['type'];
        $this->fieldName   = $this->fieldName ?: $fieldMapping['fieldName'];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if the mapping information is not an array
     */
    public function setAssociationMapping($associationMapping): void
    {
        if (!\is_array($associationMapping)) {
            throw new \InvalidArgumentException('The association mapping must be an array');
        }

        $this->associationMapping = $associationMapping;

        if (isset($associationMapping['type'])) {
            $this->type        = $this->type ?: $associationMapping['type'];
            $this->mappingType = $this->mappingType ?: $associationMapping['type'];
        } else {
            throw new \InvalidArgumentException('Unknown association mapping type');
        }
        $this->fieldName = $associationMapping['fieldName'];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if the mapping information is not an array
     */
    public function setParentAssociationMappings(array $parentAssociationMappings): void
    {
        foreach ($parentAssociationMappings as $parentAssociationMapping) {
            if (!\is_array($parentAssociationMapping)) {
                throw new \InvalidArgumentException('An association mapping must be an array');
            }
        }

        $this->parentAssociationMappings = $parentAssociationMappings;
    }
}
