<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Webtoolshealth\CodingStandard\Set;

return static function (ContainerConfigurator $container): void {
    $parameters = $container->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $container->import(Set::WEBTOOLS_CODING_STANDARD);
};