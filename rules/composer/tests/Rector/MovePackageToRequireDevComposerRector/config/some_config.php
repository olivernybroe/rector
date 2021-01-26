<?php

declare(strict_types=1);

use Rector\Composer\Rector\MovePackageToRequireDevComposerRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(MovePackageToRequireDevComposerRector::class)
        ->call('configure', [[
            MovePackageToRequireDevComposerRector::PACKAGE_NAMES => ['vendor1/package3'],
        ]]);
};