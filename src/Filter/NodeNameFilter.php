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
use Sonata\DoctrinePHPCRAdminBundle\Form\Type\Filter\ChoiceType;

class NodeNameFilter extends Filter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): bool
    {
        if (!$data->hasValue()) {
            return false;
        }

        $value = trim((string) $data->getValue());
        $type  = $data->getType() ?? ChoiceType::TYPE_CONTAINS;

        if ('' === $value) {
            return false;
        }

        $where = $this->getWhere($query);

        switch ($type) {
            case ChoiceType::TYPE_EQUAL:
                $where->eq()->localName($alias)->literal($value);

                break;
            case ChoiceType::TYPE_CONTAINS:
            default:
                $where->like()->localName($alias)->literal('%'.$value.'%');
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
        return [
            'format' => '%%%s%%',
        ];
    }

    public function getParent(): string
    {
        return ChoiceType::class;
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
