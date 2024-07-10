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

namespace Sonata\DoctrinePHPCRAdminBundle\Filter;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\Filter\DefaultType;
use Sonata\AdminBundle\Form\Type\Operator\EqualOperatorType;
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;

class ChoiceFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): bool
    {
        if (!$data->hasValue()) {
            return false;
        }

        $values = (array) $data->getValue();
        $type   = $data->getType();

        // clean values
        foreach ($values as $key => $value) {
            $value = trim((string) $value);
            if (!$value) {
                unset($values[$key]);
            } else {
                $values[$key] = $value;
            }
        }

        // if values not set, do not do this filter
        if (!$values) {
            return false;
        }

        $andX = $this->getWhere($query)->andX();

        foreach ($values as $value) {
            if (ChoiceType::TYPE_NOT_CONTAINS === $type) {
                $andX->not()->like()->field('a.'.$field)->literal('%'.$value.'%');
            } elseif (ChoiceType::TYPE_CONTAINS === $type) {
                $andX->like()->field('a.'.$field)->literal('%'.$value.'%');
            } elseif (ChoiceType::TYPE_EQUAL === $type) {
                $andX->like()->field('a.'.$field)->literal($value);
            }
        }

        // filter is active as we have now modified the query
        $this->setActive(true);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return [];
    }

    public function getParent(): string
    {
        return DefaultType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions(): array
    {
        return [
            'operator_type' => EqualOperatorType::class,
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel(),
        ];
    }
}
