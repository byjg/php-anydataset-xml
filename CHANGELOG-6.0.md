# Changelog - Version 6.0

## Overview

Version 6.0 represents a major update to the AnyDataset-XML library, focusing on modernizing the codebase with stricter type hints, updated dependencies, improved documentation, and alignment with the AnyDataset 6.0 core library.

## New Features

### Enhanced Type Safety
- Added comprehensive type hints throughout the codebase (PHP 8.3+ features)
- Refactored to use strict typing for better IDE support and error detection
- Improved Psalm static analysis support with adjusted error levels

### PHP 8.4 Support
- Added support for PHP 8.4 (requirement now >=8.3 <8.6)
- Updated development dependencies to support latest PHP versions

### Improved Documentation
- Added comprehensive [XmlDataset documentation](docs/XmlDataset.md)
- Added detailed [XPath Expressions guide](docs/XPath-Expressions.md)
- Restructured README with clearer feature descriptions and quick examples
- Added sponsor badge to README

### Testing and CI/CD Improvements
- Updated GitHub Actions workflow with improved checkout action and container options
- Added PHPUnit 10.5+ and 11.5+ support
- Added Psalm (5.9+/6.13+) for static analysis
- Added composer scripts for testing (`composer test`) and static analysis (`composer psalm`)
- Added IDE configuration files (.run/, .vscode/)

## Breaking Changes

| Before (5.x) | After (6.0) | Description |
|--------------|-------------|-------------|
| `php: ">=8.1 <8.4"` | `php: ">=8.3 <8.6"` | Minimum PHP version increased from 8.1 to 8.3 |
| `byjg/anydataset: "^5.0"` | `byjg/anydataset: "^6.0"` | Updated to AnyDataset 6.0 core library |
| `phpunit/phpunit: "^9.6"` | `phpunit/phpunit: "^10.5\|^11.5"` | Updated PHPUnit to version 10.5+ or 11.5+ |
| `Row` class | `RowArray` class | Iterator now uses `RowArray` instead of `Row` |
| `$row->addField()` | `$row->set()` | Row API changed from `addField()` to `set()` method |
| `hasNext()/moveNext()` pattern | Standard Iterator pattern | XmlIterator now uses standard PHP Iterator methods (`current()`, `valid()`, `key()`, `next()`) |
| Property `?array $colNodes` | Property `array $colNodes` | Type changed from nullable to non-nullable array |
| Property `?DOMNodeList $nodeList` | Property `DOMNodeList $nodeList` | Type changed from nullable to non-nullable |
| No namespace parameter default | `?array $registerNS = null` | Constructor now has proper default value for namespace parameter |
| Exception imports | Updated exception namespaces | Changed from `ByJG\AnyDataset\Core\Exception\*` to `ByJG\XmlUtil\Exception\*` |

## Migration Guide: Upgrading from 5.x to 6.0

### Step 1: Update System Requirements

Ensure your environment meets the new requirements:
- PHP 8.3 or higher (up to PHP 8.5)
- Update composer dependencies

```bash
composer require "byjg/anydataset-xml:^6.0"
```

### Step 2: Update PHP Version

If you're running PHP 8.1 or 8.2, upgrade to PHP 8.3 or higher before migrating to version 6.0.

### Step 3: Update Row API Calls

If you were using the Row API directly (not common in typical usage), update your code:

**Before (5.x):**
```php
$row = new \ByJG\AnyDataset\Core\Row();
$row->addField('fieldname', 'value');
```

**After (6.0):**
```php
$row = new \ByJG\AnyDataset\Core\RowArray();
$row->set('fieldname', 'value');
```

**Note:** If you're only using the `$row->get()` method to retrieve values (the most common case), no changes are needed.

### Step 4: Update Iterator Usage (if using directly)

If you were manually iterating with `hasNext()/moveNext()`:

**Before (5.x):**
```php
$iterator = $dataset->getIterator();
while ($iterator->hasNext()) {
    $row = $iterator->moveNext();
    // process row
}
```

**After (6.0):**
```php
$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    // process row
}
```

**Note:** The `foreach` pattern was already the recommended approach in 5.x, so most code should already be compatible.

### Step 5: Update Exception Handling (if used)

If you were catching specific exceptions:

**Before (5.x):**
```php
use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\AnyDataset\Core\Exception\IteratorException;
```

**After (6.0):**
```php
use ByJG\XmlUtil\Exception\FileException;
use ByJG\XmlUtil\Exception\XmlUtilException;
```

### Step 6: Update Development Dependencies

If you're developing or maintaining a library that uses anydataset-xml:

```bash
composer require --dev "phpunit/phpunit:^10.5|^11.5"
composer require --dev "vimeo/psalm:^5.9|^6.13"
```

### Step 7: Test Thoroughly

Run your test suite to ensure everything works correctly:

```bash
composer test
composer psalm
```

## Bug Fixes

- Fixed type hints throughout the codebase for better static analysis
- Improved null safety by removing unnecessary nullable types
- Fixed GitHub Actions workflow configuration
- Documentation fixes and improvements

## Notes

- The core functionality and API for typical usage (creating XmlDataset and iterating with foreach) remains unchanged
- Most users will only need to update their PHP version and composer dependencies
- The library now follows stricter coding standards and type safety practices
