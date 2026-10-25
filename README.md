---
sidebar_key: anydataset-xml
sidebar_category: data-access
tags: [php, anydataset, text-manipulation]
---

# AnyDataset-Xml

Read an XML document as a table: choose the node that is a row, map each field with XPath, iterate the rows.

[![Sponsor](https://img.shields.io/badge/Sponsor-%23ea4aaa?logo=githubsponsors&logoColor=white&labelColor=0d1117)](https://github.com/sponsors/byjg)
[![Build Status](https://github.com/byjg/php-anydataset-xml/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-anydataset-xml/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](https://opensource.byjg.com)
[![Install MCP Server](https://img.shields.io/badge/Install-MCP_Server-8A2BE2?logo=modelcontextprotocol&logoColor=white)](https://opensource.byjg.com/docs/ai/mcpserver-byjg-docs/)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-anydataset-xml/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-anydataset-xml.svg)](https://opensource.byjg.com/license/)
[![GitHub release](https://img.shields.io/github/release/byjg/php-anydataset-xml.svg)](https://github.com/byjg/php-anydataset-xml/releases/)

## Why

Reading XML with DOM or SimpleXML means walking nodes, handling namespaces and repeated
elements, and converting every value by hand, each time with different code.

XmlDataset turns it into rows. Say which node is a row and map each field with an XPath
expression (or a closure), namespaces included:

```php
$dataset = new XmlDataset($xml, 'book', [
    'category' => '@category',
    'title'    => 'title',
    'lang'     => 'title/@lang',
]);
```

The result is the same iterator of rows as every other
[AnyDataset](https://opensource.byjg.com/anydataset) source, so the code that reads it does
not care that the data was XML.

## Features

- **Simple XML Processing**: Process XML data with a clean, consistent API
- **XPath Support**: Use XPath expressions to select nodes and attributes
- **Namespace Support**: Full support for XML namespaces
- **Repeated Node Handling**: Automatically handles repeated nodes by returning arrays
- **Custom Field Processing**: Define custom field mappings with closures/callbacks
- **Flexible Input**: Accept XML as string, DOMDocument, XmlNode, or File object
- **AnyDataset Integration**: Compatible with the AnyDataset abstraction layer

## Documentation

- [XmlDataset](docs/xml-dataset.md) - Main class for working with XML data
- [XPath Expressions](docs/xpath-expressions.md) - Guide to using XPath in the library

## Quick Example

```php
<?php
$xml = file_get_contents('example.xml');

$dataset = new \ByJG\AnyDataset\Xml\XmlDataset(
    $xml,        // The XML string, can also be an XmlNode, DOMDocument, or File object
    "book",      // The node that represents a row
    [
        "category" => "@category",
        "title" => "title",
        "lang" => "title/@lang",
        "lang2" => function ($row) {
            return substr($row->get('lang'), 0, 2);
        }
    ] // Mapping columns
);

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    echo $row->get('category'); // Print COOKING, CHILDREN, WEB
    echo $row->get('title');    // Print Everyday Italian, Harry Potter, Learning XML
    echo $row->get('lang');     // Print en-US, de-DE, pt-BR
    echo $row->get('lang2');    // Print en, de, pt
}
```

## Installation

```bash
composer require "byjg/anydataset-xml"
```

## Running Tests

```bash
vendor/bin/phpunit
```

----
[Open source ByJG](https://opensource.byjg.com)
