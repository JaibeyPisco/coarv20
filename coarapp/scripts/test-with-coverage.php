<?php

declare(strict_types=1);

/**
 * Script para ejecutar tests con cobertura si hay un driver disponible
 */

$hasPcov = extension_loaded('pcov');
$hasXdebug = extension_loaded('xdebug');

$pestCommand = file_exists(__DIR__.'/../vendor/bin/pest')
    ? __DIR__.'/../vendor/bin/pest'
    : 'vendor/bin/pest';

// Ejecutar solo tests Unit y Feature (excluir Browser que requiere Playwright)
$testSuites = '--testsuite=Unit --testsuite=Feature';

if (! $hasPcov && ! $hasXdebug) {
    echo "No code coverage driver available (PCOV or Xdebug). Running tests without coverage.\n";
    passthru("php {$pestCommand} --parallel {$testSuites}", $exitCode);
    exit($exitCode);
}

echo "Code coverage driver available. Running tests with coverage.\n";
passthru("php {$pestCommand} --parallel --coverage --exactly=100.0 {$testSuites}", $exitCode);
exit($exitCode);

