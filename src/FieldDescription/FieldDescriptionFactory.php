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

use Doctrine\ODM\PHPCR\DocumentManagerInterface;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionFactoryInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;

final class FieldDescriptionFactory implements FieldDescriptionFactoryInterface
{
    private DocumentManagerInterface $dm;

    public function __construct(DocumentManagerInterface $dm)
    {
        $this->dm = $dm;
    }

    public function create(string $class, string $name, array $options = []): FieldDescriptionInterface
    {
        $metadata = $this->getMetadata($class);

        return new FieldDescription(
            $name,
            $options,
            $metadata->fieldMappings[$name] ?? [],
            $metadata->associationMappings[$name] ?? []
        );
    }

    /**
     * Returns the related model's metadata.
     */
    public function getMetadata(string $class): ClassMetadata
    {
        return $this->dm->getMetadataFactory()->getMetadataFor($class);
    }
}
