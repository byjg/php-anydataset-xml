# Changelog - Version 7.0

> **Status: in development.** This document tracks changes landing on the `7.0` branch.
> Nothing here is released yet, and the contents may still change.

## Breaking Changes

- A field definition given as a string is now always treated as an XPath. Before, a string
  naming a PHP function (e.g. `"strtoupper"`) was called as a callback; use a closure instead.
- `XmlIterator` is generator based: it follows byjg/anydataset 7.0 `GenericIterator`
  (`IteratorAggregate`, forward only, a static `rows()`; see its changelog). Its constructor accepts any
  `iterable` of nodes, not only a `DOMNodeList`, and `parseXmlNode()` is now
  `protected static parseXmlNode(DOMNode $node, array $colNodes, array $registerNS)`. The column names
  are lower-cased once, in the constructor.

## New Features

### Large XML files: `XmlStreamDataset`

`XmlStreamDataset` reads an XML file one row element at a time with byjg/serializer `XmlBlockReader`:
only the element being read is in memory, not the whole document as `XmlDataset` loads it.

```php
$dataset = new XmlStreamDataset(
    '/data/contacts.xml',
    'atom:entry',                                        // the row elements, by name, at any depth
    ['id' => 'atom:id', 'email' => 'gd:email/@address'], // the columns, as in XmlDataset
    ['atom' => 'http://www.w3.org/2005/Atom', 'gd' => 'http://schemas.google.com/g/2005']
);
```

- The rows are the elements with a **name**, not an XPath (no path, no predicate). With a prefix of
  `$registerNS`, the elements of that namespace, whatever prefix the file uses; otherwise the name as
  written in the file. An element with that name inside a row is part of the row.
- The columns are the same as in `XmlDataset`: an XPath relative to the row element, or a callable.
- The file is read again on each `getIterator()`, and closed when the rows end or the iterator is dropped.

**Documentation:** [XmlStreamDataset](docs/xml-stream-dataset.md)

## Bug Fixes

- `XmlIterator` called a column XPath as a PHP function when it matched a function name, so
  columns like `"date"` or `"key"` threw a `TypeError` or returned garbage. Only non-string
  callables (closures, invokable objects, array callables) are called now.

## Requirements

- PHP 8.3, 8.4, 8.5 and 8.6 are now supported: `"php": ">=8.3 <8.7"`.
  The previous `<8.6` upper bound excluded PHP 8.6, since `<8.6` is exclusive.

### ByJG dependencies

- `byjg/anydataset` is now `^7.0`.
- `byjg/serializer` `^7.0` is now required directly (it came only through `byjg/anydataset`).

While 7.0 is unreleased these resolve to `7.0.x-dev` from each component's
`7.0` branch, via `minimum-stability: dev` with `prefer-stable: true`.

## Toolchain

- PHPUnit updated to `^12.5`.
- Psalm is installed as `psalm/phar` instead of `vimeo/psalm`.

  `vimeo/psalm` lists the PHP versions it supports and no published release includes
  8.6, so as a dev dependency it made `composer install` fail on the 8.6 build job
  before any test ran. `psalm/phar` requires only `php ^8.2` and bundles its own
  dependencies, so it installs on every PHP version in the matrix and cannot conflict
  with the project's. Psalm itself still refuses to *run* on 8.6, which is why the
  Psalm job uses 8.5. `composer psalm` runs it.

- PHPUnit 13 is deliberately **not** used. It requires PHP `>=8.4.1`, breaking the
  8.3 floor.

## Continuous Integration

- The build matrix now includes PHP 8.6.
- The Psalm job runs on PHP 8.5.

## Housekeeping

- `phpunit.xml.dist` renamed to `phpunit.xml`.
- The documentation pages were renamed to kebab-case (`xml-dataset.md`, `xpath-expressions.md`), so their
  addresses on the documentation site change as well.
