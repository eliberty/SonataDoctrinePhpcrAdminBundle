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
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Form\Type\Operator\NumberOperatorType;

class NumberFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): bool
    {
        if (!$data->hasValue()) {
            return false;
        }

        $value = $data->getValue();

        if (!is_numeric($value)) {
            return false;
        }

        $type  = $data->getType() ?? false;
        $where = $this->getWhere($query);

        switch ($type) {
            case NumberOperatorType::TYPE_GREATER_EQUAL:
                $where->gte()->field('a.'.$field)->literal($value);

                break;
            case NumberOperatorType::TYPE_GREATER_THAN:
                $where->gt()->field('a.'.$field)->literal($value);

                break;
            case NumberOperatorType::TYPE_LESS_EQUAL:
                $where->lte()->field('a.'.$field)->literal($value);

                break;
            case NumberOperatorType::TYPE_LESS_THAN:
                $where->lt()->field('a.'.$field)->literal($value);

                break;
            case NumberOperatorType::TYPE_EQUAL:
            default:
                $where->eq()->field('a.'.$field)->literal($value);
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
        return NumberType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions(): array
    {
        return [
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel(),
        ];
    }
}
