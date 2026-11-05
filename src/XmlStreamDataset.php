<?php

namespace ByJG\AnyDataset\Xml;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\Serializer\Reader\XmlBlockReader;

/**
 * An XML file read one row element at a time (byjg/serializer XmlBlockReader): only the element being read is in
 * memory, not the whole document, as XmlDataset loads it. For a large file.
 *
 * The rows are the elements with a name, at any depth, not an XPath. The columns are the same as in XmlDataset:
 * an XPath relative to the row element, or a callable.
 */
class XmlStreamDataset
{
    /**
     * @param string $filename
     * @param string $rowElement The name of the row elements, at any depth. With a prefix of $registerNS
     *                           (`fake:entry`), the elements of that namespace, whatever prefix the file uses;
     *                           otherwise the name as written in the file. An element with that name inside a row
     *                           is part of the row, not another row
     * @param array<string, string|callable> $colNodes The XPath of each column, relative to the row element, or a
     *                                                 callable that receives the row
     * @param array<string, string> $registerNS The namespace URI of each prefix used in $rowElement and $colNodes
     */
    public function __construct(
        private string $filename,
        private string $rowElement,
        private array $colNodes,
        private array $registerNS = []
    ) {
    }

    /**
     * The file is read again on each call, and closed when the rows end or the iterator is dropped
     */
    public function getIterator(): GenericIterator
    {
        return new XmlIterator(
            new XmlBlockReader($this->filename, $this->rowElement, $this->registerNS),
            $this->colNodes,
            $this->registerNS
        );
    }
}
