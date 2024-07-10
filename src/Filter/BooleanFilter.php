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
use Sonata\DoctrinePHPCRAdminBundle\Filter\Filter as BaseFilter;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class BooleanFilter extends BaseFilter
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

        if (\is_array($value) || !\in_array($value, [BooleanType::TYPE_NO, BooleanType::TYPE_YES], true)) {
            return false;
        }

        $where = $this->getWhere($query);
        $where->eq()->field('a.'.$field)->literal(BooleanType::TYPE_YES === $value ? true : false);

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
            'field_type'       => $this->getFieldType(),
            'field_options'    => $this->getFieldOptions(),
            'operator_type'    => HiddenType::class,
            'operator_options' => [],
            'label'            => $this->getLabel(),
        ];
    }
}
