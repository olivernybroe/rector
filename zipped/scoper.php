<?php

declare(strict_types=1);

use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Rector\Compiler\PhpScoper\StaticEasyPrefixer;
use Rector\Compiler\Unprefixer;
use Rector\Compiler\ValueObject\ScoperOption;

require_once __DIR__ . '/vendor/autoload.php';

// [BEWARE] this path is relative to the root and location of this file
$filePathsToRemoveNamespace = [
    // @see https://github.com/rectorphp/rector/issues/2852#issuecomment-586315588
    'vendor/symfony/deprecation-contracts/function.php',
    // it would make polyfill function work only with namespace = brokes
    'vendor/symfony/polyfill-ctype/bootstrap.php',
    'vendor/symfony/polyfill-intl-normalizer/bootstrap.php',
    'vendor/symfony/polyfill-intl-grapheme/bootstrap.php',
    'vendor/symfony/polyfill-mbstring/bootstrap.php',
    'vendor/symfony/polyfill-php80/bootstrap.php',
    'vendor/symfony/polyfill-php74/bootstrap.php',
    'vendor/symfony/polyfill-php73/bootstrap.php',
    'vendor/symfony/polyfill-php72/bootstrap.php',
    'vendor/symfony/polyfill-uuid/bootstrap.php',
];

// remove phpstan, because it is already prefixed in its own scope

$dateTime = DateTime::from('now');
$timestamp = $dateTime->format('Ymd');

// see https://github.com/humbug/php-scoper
return [
    ScoperOption::PREFIX => 'RectorPrefix' . $timestamp,
    ScoperOption::WHITELIST => StaticEasyPrefixer::getExcludedNamespacesAndClasses(),
    ScoperOption::PATCHERS => [
        // [BEWARE] $filePath is absolute!

        // fixes https://github.com/rectorphp/rector-prefixed/runs/2143717534
        function (string $filePath, string $prefix, string $content) use ($filePathsToRemoveNamespace): string {
            // @see https://regex101.com/r/0jaVB1/1
            $prefixedNamespacePattern = '#^namespace (.*?);$#m';

            foreach ($filePathsToRemoveNamespace as $filePathToRemoveNamespace) {
                if (Strings::endsWith($filePath, $filePathToRemoveNamespace)) {
                    return Strings::replace($content, $prefixedNamespacePattern, '');
                }
            }

            return $content;
        },

        // fixes https://github.com/rectorphp/rector-prefixed/runs/2103759172
        // and https://github.com/rectorphp/rector-prefixed/blob/0cc433e746b645df5f905fa038573c3a1a9634f0/vendor/jean85/pretty-package-versions/src/PrettyVersions.php#L6
        function (string $filePath, string $prefix, string $content): string {
            if (! Strings::endsWith($filePath, 'vendor/jean85/pretty-package-versions/src/PrettyVersions.php')) {
                return $content;
            }

            // see https://regex101.com/r/v8zRMm/1
            return Strings::replace($content, '#' . $prefix . '\\\\Composer\\\\InstalledVersions#', 'Composer\InstalledVersions');
        },

        // unprefix string classes, as they're string on purpose - they have to be checked in original form, not prefixed
        function (string $filePath, string $prefix, string $content): string {
            // skip vendor, expect rector packages
            if (Strings::contains($filePath, 'vendor/') && ! Strings::contains($filePath, 'vendor/rector')) {
                return $content;
            }

            // skip bin/rector.php for composer autoload class
            if (Strings::endsWith($filePath, 'bin/rector.php')) {
                return $content;
            }

            // skip scoper-autoload
            if (Strings::endsWith($filePath, 'vendor/scoper-autoload.php')) {
                return $content;
            }

            return Unprefixer::unprefixQuoted($content, $prefix);
        },

        // scoper missed PSR-4 autodiscovery in Symfony
        function (string $filePath, string $prefix, string $content): string {
            // scoper missed PSR-4 autodiscovery in Symfony
            if (! Strings::endsWith($filePath, 'config.php') && ! Strings::endsWith($filePath, 'services.php')) {
                return $content;
            }

            // skip "Rector\\" namespace
            if (Strings::contains($content, '$services->load(\'Rector')) {
                return $content;
            }

            return Strings::replace($content, '#services\->load\(\'#', 'services->load(\'' . $prefix . '\\');
        },
    ],
];
