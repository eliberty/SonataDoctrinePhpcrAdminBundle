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

class StringFilter extends Filter
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

        $where                 = $this->getWhere($query);
        $isComparisonLowerCase = $this->getOption('compare_case_insensitive');
        $value                 = $isComparisonLowerCase ? strtolower($value) : $value;
        switch ($type) {
            case ChoiceType::TYPE_EQUAL:
                if ($isComparisonLowerCase) {
                    $where->eq()->lowerCase()->field('a.'.$field)->end()->literal($value);
                } else {
                    $where->eq()->field('a.'.$field)->literal($value);
                }

                break;
            case ChoiceType::TYPE_NOT_CONTAINS:
                $where->fullTextSearch('a.'.$field, '* -'.$value);

                break;
            case ChoiceType::TYPE_CONTAINS:
                if ($isComparisonLowerCase) {
                    $where->like()->lowerCase()->field('a.'.$field)->end()->literal('%'.$value.'%');
                } else {
                    $where->like()->field('a.'.$field)->literal('%'.$value.'%');
                }

                break;
            case ChoiceType::TYPE_CONTAINS_WORDS:
            default:
                $where->fullTextSearch('a.'.$field, $value);
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
            'format'             => '%%%s%%',
            'compare_lower_case' => false,
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
