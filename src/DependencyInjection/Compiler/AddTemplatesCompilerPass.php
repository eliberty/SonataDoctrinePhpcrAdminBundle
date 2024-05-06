<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DoctrinePHPCRAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 * @author Nacho Martín <nitram.ohcan@gmail.com>
 */
class AddTemplatesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $settings = $this->fixSettings($container);
        foreach ($container->findTaggedServiceIds('sonata.admin') as $id => $attributes) {
            if (!isset($attributes[0]['manager_type']) || 'doctrine_phpcr' !== $attributes[0]['manager_type']) {
                continue;
            }

            $definition = $container->getDefinition($id);

            // Like DoctrineORMAdminBundle same file improvement
            $this->mergeMethodCall($definition, 'setFormTheme', $settings['templates']['form']);
            $this->mergeMethodCall($definition, 'setFilterTheme', $settings['templates']['filter']);

            $definition->addMethodCall('setTemplate', ['pager_results', $settings['templates']['pager_results']]);
        }
    }

    /**
     * @param string       $name
     * @param array<mixed> $value
     *
     * @return void
     */
    public function mergeMethodCall(Definition $definition, $name, $value)
    {
        if (!$definition->hasMethodCall($name)) {
            $definition->addMethodCall($name, [$value]);

            return;
        }

        $methodCalls = $definition->getMethodCalls();

        foreach ($methodCalls as &$calls) {
            foreach ($calls as &$call) {
                if (\is_string($call)) {
                    if ($call !== $name) {
                        continue 2;
                    }

                    continue;
                }

                $call = [array_merge($call[0], $value)];
            }
        }

        $definition->setMethodCalls($methodCalls);
    }

    /**
     * @return bool
     */
    protected function fixSettings(ContainerBuilder $container)
    {
        $pool = $container->getDefinition('sonata.admin.manager.doctrine_phpcr');

        // @todo not very clean but don't know how to do that for now
        $settings = false;
        $methods = $pool->getMethodCalls();
        foreach ($methods as $pos => $calls) {
            if ('__hack_doctrine_phpcr__' === $calls[0]) {
                $settings = $calls[1];

                break;
            }
        }

        if ($settings) {
            unset($methods[$pos]);
        }

        $pool->setMethodCalls($methods);

        return $settings;
    }
}
