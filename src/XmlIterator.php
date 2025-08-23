<?php

namespace ByJG\AnyDataset\Xml;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Core\RowArray;
use ByJG\AnyDataset\Core\RowInterface;
use ByJG\XmlUtil\Exception\XmlUtilException;
use ByJG\XmlUtil\XmlNode;
use DOMNodeList;
use Override;
use ReturnTypeWillChange;

class XmlIterator extends GenericIterator
{

    /**
     * Enter description here...
     *
     * @var DOMNodeList|null
     */
    private ?DOMNodeList $nodeList;

    /**
     * Enter description here...
     *
     * @var string[]
     */
    private ?array $colNodes;

    private ?RowInterface $currentRow = null;
    private int $currentIndex = 0;

    protected array $registerNS;

    public function __construct(DOMNodeList $nodeList, array $colNodes, ?array $registerNS = null)
    {
        $this->registerNS = $registerNS;
        $this->nodeList = $nodeList;
        $this->colNodes = $colNodes;
    }

    /**
     * @throws XmlUtilException
     */
    protected function parseXmlNode(): ?RowInterface
    {
        if (!$this->valid()) {
            return null;
        }

        $rowNumber = $this->currentIndex;
        $node = $this->nodeList->item($rowNumber);

        $row = new RowArray();
        $callables = [];

        $xmlNode = XmlNode::instance($node);
        $lowercaseKeys = array_map('strtolower', array_keys($this->colNodes));
        $this->colNodes = array_combine($lowercaseKeys, array_values($this->colNodes));
        foreach ($this->colNodes as $key => $colXpath) {
            if (is_callable($colXpath)) {
                $callables[$key] = $colXpath;
                continue;
            }

            $nodeCol = $xmlNode->selectNodes($colXpath, $this->registerNS);
            if ($nodeCol->count() == 0) {
                $row->set($key, "");
            } else {
                foreach ($nodeCol as $col) {
                    $row->set($key, $col->nodeValue, append: true);
                }
            }
        }

        foreach ($callables as $key => $callable) {
            $row->set($key, $callable($row), append: true);
        }

        $this->currentRow = $row;

        return $row;
    }

    #[ReturnTypeWillChange]
    #[Override]
    public function key(): int
    {
        return $this->currentIndex;
    }

    /**
     * @return RowInterface|null
     */
    #[ReturnTypeWillChange]
    #[Override]
    public function current(): ?RowInterface
    {
        if ($this->currentRow === null) {
            $this->parseXmlNode();
        }
        return $this->currentRow;
    }

    #[ReturnTypeWillChange]
    #[Override]
    public function next(): void
    {
        $this->currentIndex++;
        $this->currentRow = null;
    }

    #[ReturnTypeWillChange]
    #[Override]
    public function valid(): bool
    {
        return ($this->currentIndex < count($this->nodeList));
    }
}
