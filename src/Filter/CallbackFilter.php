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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CallbackFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException if the filter is not configured with a
     *                                   callable in the 'callback' option field
     */
    public function filter(ProxyQueryInterface $query, string $alias, string $field, FilterData $data): bool
    {
        if (!\is_callable($this->getOption('callback'))) {
            throw new \RuntimeException(sprintf('Please provide a valid callback for option "callback" and field "%s"', $this->getName()));
        }

        $callbackresult = \call_user_func($this->getOption('callback'), $query, $alias, $field, $data);

        $this->setActive(true === $callbackresult);

        return true === $callbackresult;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): array
    {
        return [
            'callback'         => null,
            'field_type'       => TextType::class,
            'operator_type'    => HiddenType::class,
            'operator_options' => [],
        ];
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
            'operator_type'    => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label'            => $this->getLabel(),
        ];
    }
}
