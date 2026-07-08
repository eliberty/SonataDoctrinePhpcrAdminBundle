<?php

declare(strict_types=1);

use Symfony\Cmf\Bundle\ResourceBundle\CmfResourceBundle;
use Symfony\Cmf\Bundle\ResourceRestBundle\CmfResourceRestBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Knp\Bundle\MenuBundle\KnpMenuBundle;
use Sonata\AdminBundle\SonataAdminBundle;
use Sonata\CoreBundle\SonataCoreBundle;
use Sonata\Doctrine\Bridge\Symfony\Bundle\SonataDoctrineBundle;
use Sonata\BlockBundle\SonataBlockBundle;
use Sonata\DoctrinePHPCRAdminBundle\SonataDoctrinePHPCRAdminBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Cmf\Bundle\TreeBrowserBundle\CmfTreeBrowserBundle;
use Doctrine\Bundle\PHPCRBundle\DoctrinePHPCRBundle;

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    CmfResourceBundle::class => ['all' => true],
    CmfResourceRestBundle::class => ['all' => true],
    JMSSerializerBundle::class => ['all' => true],
    DoctrineBundle::class => ['all' => true],
    KnpMenuBundle::class => ['all' => true],
    SonataAdminBundle::class => ['all' => true],
    SonataCoreBundle::class => ['all' => true],
    SonataDoctrineBundle::class => ['all' => true],
    SonataBlockBundle::class => ['all' => true],
    SonataDoctrinePHPCRAdminBundle::class => ['all' => true],
    TwigBundle::class => ['all' => true],
    CmfTreeBrowserBundle::class => ['all' => true],
    DoctrinePHPCRBundle::class => ['all' => true],
];
