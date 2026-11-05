---
sidebar_position: 3
title: XmlStreamDataset
---

# XmlStreamDataset

`XmlStreamDataset` reads an XML **file** one row element at a time: only the element being read is in memory, not
the whole document, as [XmlDataset](xml-dataset.md) loads it. Use it for a large file.

It uses `XmlBlockReader` of byjg/serializer (XMLReader): entities are never expanded and nothing is fetched from
the network.

## Basic Usage

```php
<?php
$dataset = new \ByJG\AnyDataset\Xml\XmlStreamDataset(
    "/data/books.xml",
    "book",
    [
        "category" => "@category",
        "title" => "title",
        "lang" => "title/@lang",
    ]
);

foreach ($dataset->getIterator() as $row) {
    echo $row->get("title");
}
```

## Constructor Parameters

```php
public function __construct(
    string $filename,
    string $rowElement,
    array $colNodes,
    array $registerNS = []
)
```

- `$filename` - the XML file
- `$rowElement` - the **name** of the row elements, at any depth (not an XPath: no path, no predicate)
- `$colNodes` - the columns, as in `XmlDataset`: an XPath relative to the row element, or a callable that receives
  the row
- `$registerNS` - the namespace URI of each prefix used in `$rowElement` and `$colNodes`

## Namespaces

With a prefix of `$registerNS`, `$rowElement` is the elements of that namespace, whatever prefix the file uses
(none, for a default namespace):

```php
<?php
// <feed xmlns="http://www.w3.org/2005/Atom" xmlns:gd="http://schemas.google.com/g/2005"><entry>...
$dataset = new \ByJG\AnyDataset\Xml\XmlStreamDataset(
    "/data/contacts.xml",
    "atom:entry",
    [
        "id" => "atom:id",
        "name" => "atom:title",
        "email" => "gd:email/@address",
    ],
    [
        "atom" => "http://www.w3.org/2005/Atom",
        "gd" => "http://schemas.google.com/g/2005",
    ]
);
```

Without a prefix of `$registerNS`, the name is compared as written in the file (`entry`, or `atom:entry` when the
file uses that prefix).

## Compared with XmlDataset

| | XmlDataset | XmlStreamDataset |
|---|---|---|
| Input | string, `DOMDocument`, `XmlNode` or `File` | a file |
| Rows | any XPath | an element name, at any depth |
| A row element inside a row | another row | part of the row |
| Columns | XPath or callable | the same |
| Memory | the whole document | one row element |

## Iterating

`getIterator()` reads the file again on each call. The iterator moves forward only, and the file is closed when the
rows end or the iterator is dropped (`first()`, a `foreach` with `break`). An invalid XML throws
`ByJG\Serializer\Exception\InvalidArgumentException` while the rows are read: the rows before the error may already
have been processed.
