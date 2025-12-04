<?php
// THIS FILE IS AUTO-GENERATED.
// Do not modify it, your changes will be lost after the next "composer install/updates" execution.
// This file contains a list of composer packages that are shipped with this plugin and their autoloader information.
// This file is explicitly a PHP file (and not JSON or YAML) to make it cacheable for the OpCache.
return array (
  0 => 
  array (
    'name' => 'doctrine/annotations',
    'version_normalized' => '2.0.1.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Doctrine\\Common\\Annotations\\' => 'lib/Doctrine/Common/Annotations',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
    ),
  ),
  1 => 
  array (
    'name' => 'pickware/api-error-handling-bundle',
    'version_normalized' => '3.34.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\ApiErrorHandlingBundle\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  2 => 
  array (
    'name' => 'pickware/api-versioning-bundle',
    'version_normalized' => '3.8.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\ApiVersioningBundle\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  3 => 
  array (
    'name' => 'pickware/bundle-installer',
    'version_normalized' => '3.7.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\BundleInstaller\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  4 => 
  array (
    'name' => 'pickware/dal-bundle',
    'version_normalized' => '5.22.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\DalBundle\\' => 'src',
      ),
      'files' => 
      array (
        0 => 'src/BulkInsert/functions.php',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  5 => 
  array (
    'name' => 'pickware/debug-bundle',
    'version_normalized' => '4.26.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\DebugBundle\\' => 'src',
      ),
      'files' => 
      array (
        0 => 'src/PickwareDebugging.php',
        1 => 'src/Profiling/Profiling.php',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  6 => 
  array (
    'name' => 'pickware/dependency-loader',
    'version_normalized' => '5.5.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\DependencyLoader\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'composer-plugin',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
      'class' => 'Pickware\\DependencyLoader\\DependencyLoaderComposerPlugin',
    ),
  ),
  7 => 
  array (
    'name' => 'pickware/document-bundle',
    'version_normalized' => '4.51.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\DocumentBundle\\' => 'src',
        'Pickware\\DocumentBundle\\Test\\TestEntityCreation\\' => 'test/TestEntityCreation/',
        'Pickware\\ShopwarePlugins\\DocumentBundle\\Migration\\' => 'src/MigrationOldNamespace',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  8 => 
  array (
    'name' => 'pickware/document-utils',
    'version_normalized' => '1.1.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\DocumentUtils\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  9 => 
  array (
    'name' => 'pickware/feature-flag-bundle',
    'version_normalized' => '3.31.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\FeatureFlagBundle\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  10 => 
  array (
    'name' => 'pickware/http-utils',
    'version_normalized' => '4.14.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\HttpUtils\\' => 'src',
        'Pickware\\HttpUtils\\Test\\' => 'test',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  11 => 
  array (
    'name' => 'pickware/incompatibility-bundle',
    'version_normalized' => '2.24.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\IncompatibilityBundle\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  12 => 
  array (
    'name' => 'pickware/installation-library',
    'version_normalized' => '4.27.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\InstallationLibrary\\' => 'src',
      ),
      'files' => 
      array (
        0 => 'src/Migration/collation_functions.php',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  13 => 
  array (
    'name' => 'pickware/money-bundle',
    'version_normalized' => '4.24.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\MoneyBundle\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  14 => 
  array (
    'name' => 'pickware/php-standard-library',
    'version_normalized' => '2.6.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\PhpStandardLibrary\\' => 'src',
      ),
      'files' => 
      array (
        0 => 'src/Language/functions.php',
        1 => 'src/Exception/functions.php',
        2 => 'src/Optional/functions.php',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  15 => 
  array (
    'name' => 'pickware/shipping-bundle',
    'version_normalized' => '3.65.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\ShippingBundle\\' => 'src',
        'Pickware\\ShippingBundle\\Test\\TestEntityCreation\\' => 'test/TestEntityCreation/',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  16 => 
  array (
    'name' => 'pickware/shopware-extensions-bundle',
    'version_normalized' => '3.27.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\ShopwareExtensionsBundle\\' => 'src',
      ),
      'files' => 
      array (
        0 => 'src/VersionCheck/functions.php',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  17 => 
  array (
    'name' => 'pickware/units-of-measurement',
    'version_normalized' => '3.5.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\UnitsOfMeasurement\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  18 => 
  array (
    'name' => 'pickware/validation-bundle',
    'version_normalized' => '3.34.0.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Pickware\\ValidationBundle\\' => 'src',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
      'ci-min-supported-shopware-version' => '6.6.2.0',
    ),
  ),
  19 => 
  array (
    'name' => 'setasign/fpdf',
    'version_normalized' => '1.8.6.0',
    'autoload' => 
    array (
      'classmap' => 
      array (
        'FPDF' => 'fpdf.php',
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
    ),
  ),
  20 => 
  array (
    'name' => 'symfony/runtime',
    'version_normalized' => '7.0.3.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'Symfony\\Component\\Runtime\\' => '',
        'Symfony\\Runtime\\Symfony\\Component\\' => 'Internal/',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'composer-plugin',
    'extra' => 
    array (
      'class' => 'Symfony\\Component\\Runtime\\Internal\\ComposerPlugin',
    ),
  ),
  21 => 
  array (
    'name' => 'viison/address-splitter',
    'version_normalized' => '0.3.4.0',
    'autoload' => 
    array (
      'psr-4' => 
      array (
        'VIISON\\AddressSplitter\\' => 'src/',
      ),
      'classmap' => 
      array (
      ),
    ),
    'type' => 'library',
    'extra' => 
    array (
    ),
  ),
);