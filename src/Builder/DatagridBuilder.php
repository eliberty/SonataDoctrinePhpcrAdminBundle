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

namespace Sonata\DoctrinePHPCRAdminBundle\Builder;

use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Builder\DatagridBuilderInterface;
use Sonata\AdminBundle\Datagrid\Datagrid;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\PagerInterface;
use Sonata\AdminBundle\Datagrid\SimplePager;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\FieldDescription\TypeGuesserInterface;
use Sonata\AdminBundle\Filter\FilterFactoryInterface;
use Symfony\Component\Form\FormFactory;

class DatagridBuilder implements DatagridBuilderInterface
{
    private FilterFactoryInterface $filterFactory;

    private FormFactory $formFactory;

    private TypeGuesserInterface $guesser;

    /**
     * Indicates that csrf protection enabled.
     */
    private bool $csrfTokenEnabled;

    private ?PagerInterface $pager = null;

    public function __construct(FormFactory $formFactory, FilterFactoryInterface $filterFactory, TypeGuesserInterface $guesser, bool $csrfTokenEnabled = true)
    {
        $this->formFactory      = $formFactory;
        $this->filterFactory    = $filterFactory;
        $this->guesser          = $guesser;
        $this->csrfTokenEnabled = $csrfTokenEnabled;
    }

    public function setPager(PagerInterface $pager): void
    {
        $this->pager = $pager;
    }

    public function getPager(): PagerInterface
    {
        if (null === $this->pager) {
            $this->pager = new SimplePager();
        }

        return $this->pager;
    }

    /**
     * {@inheritdoc}
     */
    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        $admin = $fieldDescription->getAdmin();

        if ($admin->getModelManager()->hasMetadata($admin->getClass())) {
            $metadata = $admin->getModelManager()->getMetadata($admin->getClass());

            // set the default field mapping
            if (isset($metadata->mappings[$fieldDescription->getName()])) {
                $fieldDescription->setFieldMapping($metadata->mappings[$fieldDescription->getName()]);

                if ('string' === $metadata->mappings[$fieldDescription->getName()]['type']) {
                    $fieldDescription->setOption('global_search', $fieldDescription->getOption('global_search', true)); // always search on string field only
                }
            }

            // set the default association mapping
            if (isset($metadata->associationMappings[$fieldDescription->getName()])) {
                $fieldDescription->setAssociationMapping($metadata->associationMappings[$fieldDescription->getName()]);
            }
        }

        $fieldDescription->setOption('field_name', $fieldDescription->getOption('field_name', $fieldDescription->getFieldName()));
        $fieldDescription->setOption('code', $fieldDescription->getOption('code', $fieldDescription->getName()));
        $fieldDescription->setOption('name', $fieldDescription->getOption('name', $fieldDescription->getName()));
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(DatagridInterface $datagrid, $type, FieldDescriptionInterface $fieldDescription): void
    {
        $admin = $fieldDescription->getAdmin();

        if (null === $type) {
            $guessType = $this->guesser->guess($fieldDescription);
            if (null === $guessType) {
                throw new \InvalidArgumentException(sprintf('Cannot guess a type for the field description "%s", You MUST provide a type.', $fieldDescription->getName()));
            }

            $type = $guessType->getType();
            $fieldDescription->setType($type);
            $options = $guessType->getOptions();

            foreach ($options as $name => $value) {
                if (\is_array($value)) {
                    $fieldDescription->setOption($name, array_merge($value, $fieldDescription->getOption($name, [])));
                } else {
                    $fieldDescription->setOption($name, $fieldDescription->getOption($name, $value));
                }
            }
        } else {
            $fieldDescription->setType($type);
        }

        $this->fixFieldDescription($fieldDescription);
        $admin->addFilterFieldDescription($fieldDescription->getName(), $fieldDescription);

        $fieldDescription->mergeOption('field_options', ['required' => false]);
        $filter = $this->filterFactory->create($fieldDescription->getName(), $type, $fieldDescription->getOptions());

        if (false !== $filter->getLabel() && !$filter->getLabel()) {
            $filter->setLabel($admin->getLabelTranslatorStrategy()->getLabel($fieldDescription->getName(), 'filter', 'label'));
        }

        $datagrid->addFilter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseDatagrid(AdminInterface $admin, array $values = []): DatagridInterface
    {
        $defaultOptions = [];
        if ($this->csrfTokenEnabled) {
            $defaultOptions['csrf_protection'] = false;
        }

        $formBuilder = $this->formFactory->createNamedBuilder(
            'filter',
            'Symfony\Component\Form\Extension\Core\Type\FormType',
            [],
            $defaultOptions
        );

        return new Datagrid($admin->createQuery(), $admin->getList(), $this->getPager(), $formBuilder, $values);
    }
}
