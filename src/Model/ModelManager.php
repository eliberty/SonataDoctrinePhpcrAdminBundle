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

namespace Sonata\DoctrinePHPCRAdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Mapping\ClassMetadata;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use PHPCR\Util\PathHelper;
use PHPCR\Util\UUIDHelper;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\DoctrinePHPCRAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrinePHPCRAdminBundle\FieldDescription\FieldDescriptionFactory;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

class ModelManager implements ModelManagerInterface
{
    protected DocumentManager $dm;
    protected FieldDescriptionFactory $fieldDescriptionFactory;

    public function __construct(DocumentManager $dm, FieldDescriptionFactory $fieldDescriptionFactory)
    {
        $this->dm                      = $dm;
        $this->fieldDescriptionFactory = $fieldDescriptionFactory;
    }

    /**
     * Returns the related model's metadata.
     */
    public function getMetadata(string $class): ClassMetadata
    {
        return $this->dm->getMetadataFactory()->getMetadataFor($class);
    }

    /**
     * Returns true is the model has some metadata.
     */
    public function hasMetadata(string $class): bool
    {
        return $this->dm->getMetadataFactory()->hasMetadataFor($class);
    }

    /**
     * {@inheritdoc}
     *
     * @throws ModelManagerException if the document manager throws any exception
     */
    public function create($object): void
    {
        try {
            $this->dm->persist($object);
            $this->dm->flush();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws ModelManagerException if the document manager throws any exception
     */
    public function update($object): void
    {
        try {
            $this->dm->persist($object);
            $this->dm->flush();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws ModelManagerException if the document manager throws any exception
     */
    public function delete($object): void
    {
        try {
            $this->dm->remove($object);
            $this->dm->flush();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * Find one object from the given class repository.
     *
     * {@inheritdoc}
     */
    public function find(string $class, $id): ?object
    {
        if (!isset($id)) {
            return null;
        }

        if (!$class) {
            return $this->dm->find(null, $id);
        }

        return $this->dm->getRepository($class)->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy($class, array $criteria = []): array
    {
        return $this->dm->getRepository($class)->findBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy($class, array $criteria = []): ?object
    {
        return $this->dm->getRepository($class)->findOneBy($criteria);
    }

    /**
     * @return DocumentManager The PHPCR-ODM document manager responsible for
     *                         this model
     */
    public function getDocumentManager(): DocumentManager
    {
        return $this->dm;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentFieldDescription($parentAssociationMapping, $class): FieldDescriptionInterface
    {
        $fieldName = $parentAssociationMapping['fieldName'];

        return $this->fieldDescriptionFactory->create($class, $fieldName);
    }

    /**
     * @param string $class the fully qualified class name to search for
     * @param string $alias alias to use for this class when accessing fields,
     *                      defaults to 'a'
     *
     * @throws \InvalidArgumentException if alias is not a string or an empty string
     */
    public function createQuery(string $class): ProxyQueryInterface
    {
        $alias = 'a';       // todo better to manage alias
        $qb    = $this->getDocumentManager()->createQueryBuilder();
        $qb->from()->document($class, $alias);

        return new ProxyQuery($qb, $alias);
    }

    /**
     * @param ProxyQuery $query
     *
     * @return mixed
     */
    public function executeQuery(object $query)
    {
        return $query->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getModelIdentifier($classname): ?string
    {
        return $this->getMetadata($classname)->identifier;
    }

    /**
     * Transforms the document into the PHPCR path.
     *
     * Note: This is returning an array because Doctrine ORM for example can
     * have multiple identifiers, e.g. if the primary key is composed of
     * several columns. We only ever have one, but return that wrapped into an
     * array to adhere to the interface.
     *
     * {@inheritdoc}
     */
    public function getIdentifierValues(object $model): array
    {
        $class = $this->getMetadata(ClassUtils::getClass($model));
        $path  = $class->reflFields[$class->identifier]->getValue($model);

        return [$path];
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierFieldNames(string $class): array
    {
        return [$this->getModelIdentifier($class)];
    }

    /**
     * This is just taking the id out of the array again.
     *
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if $model is not an object or null
     */
    public function getNormalizedIdentifier(object $model): ?string
    {
        if (is_scalar($model)) {
            throw new \InvalidArgumentException('Invalid argument, object or null required');
        }

        // the document is not managed
        if (!$model || !$this->getDocumentManager()->contains($model)) {
            return null;
        }

        $values = $this->getIdentifierValues($model);

        return $values[0];
    }

    /**
     * Currently only the leading slash is removed.
     *
     *
     */
    public function getUrlsafeIdentifier(object $model): ?string
    {
        $id = $this->getNormalizedIdentifier($model);
        if (null !== $id) {
            return substr($id, 1);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function addIdentifiersToQuery(string $class, ProxyQueryInterface $query, array $idx): void
    {
        /** @var ProxyQuery $query */
        $qb = $query->getQueryBuilder();

        $orX = $qb->andWhere()->orX();

        foreach ($idx as $id) {
            $path = $this->getBackendId($id);
            $orX->same($path, $query->getAlias());
        }
    }

    /**
     * Add leading slash to construct valid phpcr document id.
     *
     * The phpcr-odm QueryBuilder uses absolute paths and expects id´s to start with a forward slash
     * because SonataAdmin uses object id´s for constructing URL´s it has to use id´s without the
     * leading slash.
     *
     * @param string $id
     *
     * @return string
     */
    public function getBackendId($id)
    {
        return '/' === substr($id, 0, 1) ? $id : '/'.$id;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ModelManagerException if anything goes wrong during query execution
     */
    public function batchDelete($class, ProxyQueryInterface $queryProxy): void
    {
        try {
            $i   = 0;
            $res = $queryProxy->execute();
            foreach ($res as $object) {
                $this->dm->remove($object);

                if (0 === (++$i % 20)) {
                    $this->dm->flush();
                    $this->dm->clear();
                }
            }

            $this->dm->flush();
            $this->dm->clear();
        } catch (\Exception $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return object
     */
    public function getModelInstance($class)
    {
        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function getSortParameters(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid): array
    {
        $values = $datagrid->getValues();

        if ($this->isFieldAlreadySorted($fieldDescription, $datagrid)) {
            if ('ASC' === $values['_sort_order']) {
                $values['_sort_order'] = 'DESC';
            } else {
                $values['_sort_order'] = 'ASC';
            }

            $values['_sort_by'] = $fieldDescription->getName();
        } else {
            $values['_sort_order'] = 'ASC';
            $values['_sort_by']    = $fieldDescription->getName();
        }

        return ['filter' => $values];
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginationParameters(DatagridInterface $datagrid, $page): array
    {
        $values = $datagrid->getValues();

        if (isset($values['_sort_by']) && $values['_sort_by'] instanceof FieldDescriptionInterface) {
            $values['_sort_by'] = $values['_sort_by']->getName();
        }
        $values['_page'] = $page;

        return ['filter' => $values];
    }

    /**
     * Returns a list of default sort values.
     *
     * @phpstan-return array{
     *     _page?: int,
     *     _per_page?: int,
     *     _sort_by?: string,
     *     _sort_order?: string
     * }
     */
    public function getDefaultSortValues($class): array
    {
        $defaultSortValues = [];

        $this->configureDefaultSortValues($defaultSortValues);

        return $defaultSortValues;
    }

    public function getPerPageOptions(): array
    {
        return [10, 25, 50, 100, 250];
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PAGE]       = 1;
        $sortValues[DatagridInterface::PER_PAGE]   = 25;
        $sortValues[DatagridInterface::SORT_BY]    = 'id';
        $sortValues[DatagridInterface::SORT_ORDER] = 'ASC';
    }

    /**
     * {@inheritdoc}
     *
     * @return object
     */
    public function modelTransform($class, $instance)
    {
        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NoSuchPropertyException if the class has no magic setter and
     *                                 public property for a field in array
     */
    public function reverseTransform(object $object, array $array = []): void
    {
        $metadata  = $this->getMetadata(\get_class($object));
        $reflClass = $metadata->reflClass;
        foreach ($array as $name => $value) {
            $reflection_property = false;
            // property or association ?
            if (\array_key_exists($name, $metadata->fieldMappings)) {
                $property            = $metadata->fieldMappings[$name]['fieldName'];
                $reflection_property = $metadata->reflFields[$name];
            } elseif (\array_key_exists($name, $metadata->associationMappings)) {
                $property = $metadata->associationMappings[$name]['fieldName'];
            } else {
                $property = $name;
            }

            // TODO: use PropertyAccess https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/issues/187
            $setter = 'set'.$this->camelize($name);

            if ($reflClass->hasMethod($setter)) {
                if (!$reflClass->getMethod($setter)->isPublic()) {
                    throw new NoSuchPropertyException(sprintf('Method "%s()" is not public in class "%s"', $setter, $reflClass->getName()));
                }

                $object->$setter($value);
            } elseif ($reflClass->hasMethod('__set')) {
                // needed to support magic method __set
                $object->$property = $value;
            } elseif ($reflClass->hasProperty($property)) {
                if (!$reflClass->getProperty($property)->isPublic()) {
                    throw new NoSuchPropertyException(sprintf('Property "%s" is not public in class "%s". Maybe you should create the method "set%s()"?', $property, $reflClass->getName(), ucfirst($property)));
                }

                $object->$property = $value;
            } elseif ($reflection_property) {
                $reflection_property->setValue($object, $value);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getModelCollectionInstance($class): ArrayCollection
    {
        return new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function collectionClear(&$collection)
    {
        return $collection->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function collectionHasElement(&$collection, &$element)
    {
        return $collection->contains($element);
    }

    /**
     * {@inheritdoc}
     */
    public function collectionAddElement(&$collection, &$element)
    {
        return $collection->add($element);
    }

    /**
     * {@inheritdoc}
     */
    public function collectionRemoveElement(&$collection, &$element)
    {
        return $collection->removeElement($element);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataSourceIterator(DatagridInterface $datagrid, array $fields, $firstResult = null, $maxResult = null): void
    {
        throw new \RuntimeException('Datasourceiterator not implemented.');
    }

    /**
     * {@inheritdoc}
     *
     * Not really implemented.
     */
    public function getExportFields(string $class): array
    {
        return [];
    }

    /**
     * Method taken from PropertyPath.
     *
     * NEXT_MAJOR: remove when doing https://github.com/sonata-project/SonataDoctrinePhpcrAdminBundle/issues/187
     *
     * @param string $property
     *
     * @return string
     *
     * @deprecated
     */
    protected function camelize($property): ?string
    {
        return preg_replace(['/(^|_)+(.)/e', '/\.(.)/e'], ["strtoupper('\\2')", "'_'.strtoupper('\\1')"], $property);
    }

    private function isFieldAlreadySorted(FieldDescriptionInterface $fieldDescription, DatagridInterface $datagrid): bool
    {
        $values = $datagrid->getValues();

        if (!isset($values['_sort_by']) || !$values['_sort_by'] instanceof FieldDescriptionInterface) {
            return false;
        }

        return $values['_sort_by']->getName() === $fieldDescription->getName()
            || $values['_sort_by']->getName() === $fieldDescription->getOption('sortable');
    }

    public function supportsQuery(object $query): bool
    {
        return $query instanceof ProxyQuery || $query instanceof QueryBuilder;
    }
}
