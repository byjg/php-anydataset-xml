---
sidebar_position: 2
title: XPath Expressions
---

# XPath Expressions in AnyDataset-Xml

This library uses XPath expressions to select nodes and attributes from XML documents. Understanding XPath is essential for effectively using the AnyDataset-Xml library.

## Basic XPath Syntax

Here are some common XPath expressions used in the library:

| Expression           | Description                                                      | Example                                                                                |
|----------------------|------------------------------------------------------------------|----------------------------------------------------------------------------------------|
| `element`            | Selects all elements with the given name                         | `"title"` selects all `<title>` elements                                               |
| `@attribute`         | Selects the attribute with the given name                        | `"@category"` selects the category attribute                                           |
| `element/@attribute` | Selects an attribute of an element                               | `"title/@lang"` selects the lang attribute of title elements                           |
| `parent/child`       | Selects all child elements of the parent                         | `"book/author"` selects all author elements that are children of book elements         |
| `//element`          | Selects all elements with the given name, regardless of position | `"//title"` selects all title elements anywhere in the document                        |
| `*`                  | Selects all elements                                             | `"book/*"` selects all child elements of book                                          |
| `element[n]`         | Selects the nth element                                          | `"author[1]"` selects the first author element                                         |
| `element[condition]` | Selects elements that satisfy the condition                      | `"book[@category='WEB']"` selects book elements with category attribute equal to 'WEB' |

## Implementation Details

:::warning Key Points
- All field names in the resulting data are converted to lowercase
- If an XPath expression doesn't match any nodes, an empty string is returned for that field
- If an XPath expression matches multiple nodes, all values are collected automatically in an array
:::

## Examples in AnyDataset-Xml

### Basic Element Selection

```php
$colNodes = [
    "title" => "title",       // Selects the <title> element
    "author" => "author",     // Selects the <author> element
    "year" => "year",         // Selects the <year> element
    "price" => "price"        // Selects the <price> element
];
```

### Attribute Selection

```php
$colNodes = [
    "category" => "@category",  // Selects the category attribute of the current node
    "lang" => "title/@lang"     // Selects the lang attribute of the title element
];
```

### Using Namespaces

When working with namespaced XML, you need to register the namespaces and use them in your XPath expressions:

```php
$namespace = [
    "atom" => "http://www.w3.org/2005/Atom",
    "gd" => "http://schemas.google.com/g/2005"
];

$colNodes = [
    "id" => "atom:id",             // Selects the id element in the atom namespace
    "email" => "gd:email/@address" // Selects the address attribute of the email element in the gd namespace
];

$dataset = new \ByJG\AnyDataset\Xml\XmlDataset(
    $xml,
    "atom:entry",  // Selects entry elements in the atom namespace
    $colNodes,
    $namespace
);
```

## Advanced Usage

### Custom Field Processing

You can use callback functions to process field values:

```php
$colNodes = [
    "title" => "title",
    "lang" => "title/@lang",
    "shortLang" => function ($row) {
        return substr($row->get('lang'), 0, 2);
    }
];
```

### Handling Repeated Nodes

When an XPath expression matches multiple nodes, the values are automatically collected in an array:

```php
// For XML like:
// <book>
//   <author>Author 1</author>
//   <author>Author 2</author>
// </book>

$colNodes = [
    "authors" => "author"  // Will automatically return an array of all author values: ["Author 1", "Author 2"]
];

// Access as:
$authorArray = $row->get('authors');
```

### Accessing Field Values

:::caution Case Sensitivity
All field names are converted to lowercase when accessed through the iterator.
:::

```php
$colNodes = [
    "Title" => "title",
    "AUTHOR" => "author"
];

// Access using lowercase:
$title = $row->get('title');  // Not $row->get('Title')
$author = $row->get('author'); // Not $row->get('AUTHOR')
``` 