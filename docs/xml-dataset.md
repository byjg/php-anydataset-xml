---
sidebar_position: 1
title: XmlDataset
---

# XmlDataset

The `XmlDataset` class is the main entry point for working with XML data in the AnyDataset library.

## Basic Usage

```php
$dataset = new \ByJG\AnyDataset\Xml\XmlDataset(
    $xml,        // The XML string, can also be an XmlNode, DOMDocument, or File object
    "book",      // The node that represents a row
    [
        "category" => "@category",
        "title" => "title",
        "lang" => "title/@lang",
    ] // Mapping columns
);

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    echo $row->get('category'); 
    echo $row->get('title');    
    echo $row->get('lang');     
}
```

## Constructor Parameters

```php
public function __construct(
    XmlNode|DOMDocument|string|File $xml, 
    string $rowNode, 
    array $colNode, 
    array $registerNS = []
)
```

- **$xml**: The XML source. Can be:
  - A string containing XML
  - An XmlNode object
  - A DOMDocument object
  - A File object

- **$rowNode**: XPath expression that identifies the nodes to be treated as rows

- **$colNode**: Associative array mapping field names to XPath expressions
  - Keys: The field names that will be accessible in the iterator (will be converted to lowercase)
  - Values: XPath expressions relative to the row node, or callback functions

- **$registerNS**: Optional array of namespace prefixes and URIs

## Methods

### getIterator()

```php
public function getIterator(): GenericIterator
```

Returns an `XmlIterator` instance that can be used to iterate through the XML data.

## Field Values Handling

:::info Important Behavior
- When an XPath expression matches multiple nodes, the values are automatically collected in an array
- All field names are converted to lowercase when accessed through the iterator
- If no nodes match an XPath expression, an empty string is returned for that field
:::

## Examples

### Basic Example

```php
$xml = file_get_contents('example.xml');

$dataset = new \ByJG\AnyDataset\Xml\XmlDataset(
    $xml,
    "book",
    [
        "category" => "@category",
        "title" => "title",
        "lang" => "title/@lang"
    ]
);

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    echo $row->get('category'); // Print attribute values
    echo $row->get('title');    // Print element values
    echo $row->get('lang');     // Print attribute of an element
}
```

### With Custom Field Processing

```php
$dataset = new \ByJG\AnyDataset\Xml\XmlDataset(
    $xml,
    "book",
    [
        "category" => "@category",
        "title" => "title",
        "lang" => "title/@lang",
        "lang2" => function ($row) {
            return substr($row->get('lang'), 0, 2);
        }
    ]
);
```

### With Namespaces

```php
$namespace = [
    "atom" => "http://www.w3.org/2005/Atom",
    "gd" => "http://schemas.google.com/g/2005"
];

$dataset = new \ByJG\AnyDataset\Xml\XmlDataset(
    $xml,
    "atom:entry",
    [
        "id" => "atom:id", 
        "updated" => "atom:updated", 
        "name" => "atom:title", 
        "email" => "gd:email/@address"
    ],
    $namespace
);
``` 