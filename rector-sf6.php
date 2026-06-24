<?php

declare(strict_types=1);

// Scoped "SF2.8 -> SF6.4 stage" config — run with redpill8's Rector ^2 binary.
// These eliberty/* forks are old 2.x bundles migrated BY HAND, palier by palier,
// so each step may have left incomplete cruft (e.g. setDefaultOptions /
// OptionsResolverInterface, a SF2.6->3.0 removal never cleaned). Replaying the
// FULL set progression catches every missed migration rule, not just the SF6 ones.
// Stop at 64: target is SF6, not SF7 (we intentionally keep symfony/templating).
// The sets carry baked-in API knowledge, so they work even while SF5.4 is installed.

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src'])
    ->withPhpVersion(\Rector\ValueObject\PhpVersion::PHP_84)
    ->withSets([
        SymfonySetList::SYMFONY_28,
        SymfonySetList::SYMFONY_30,
        SymfonySetList::SYMFONY_31,
        SymfonySetList::SYMFONY_32,
        SymfonySetList::SYMFONY_33,
        SymfonySetList::SYMFONY_34,
        SymfonySetList::SYMFONY_40,
        SymfonySetList::SYMFONY_41,
        SymfonySetList::SYMFONY_42,
        SymfonySetList::SYMFONY_43,
        SymfonySetList::SYMFONY_44,
        SymfonySetList::SYMFONY_50,
        SymfonySetList::SYMFONY_50_TYPES,
        SymfonySetList::SYMFONY_51,
        SymfonySetList::SYMFONY_52,
        SymfonySetList::SYMFONY_53,
        SymfonySetList::SYMFONY_54,
        SymfonySetList::SYMFONY_60,
        SymfonySetList::SYMFONY_61,
        SymfonySetList::SYMFONY_62,
        SymfonySetList::SYMFONY_63,
        SymfonySetList::SYMFONY_64,
    ]);
