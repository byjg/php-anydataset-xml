<?php

namespace ByJG\AnyDataset\Xml;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Core\RowArray;
use ByJG\AnyDataset\Core\RowInterface;
use ByJG\XmlUtil\Exception\XmlUtilException;
use ByJG\XmlUtil\XmlNode;
use DOMNodeList;
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

    /**
     * Enter description here...
     *
     * @var array
     */
    private array $current;

    protected array $registerNS;

    public function __construct(DOMNodeList $nodeList, array $colNodes, ?array $registerNS = null)
    {
        $this->registerNS = $registerNS;
        $this->nodeList = $nodeList;
        $this->colNodes = $colNodes;

        $this->current = [
            'row' => null,
            'i' => 0,
        ];
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext(): bool
    {
        return ($this->current["i"] < count($this->nodeList));
    }

    /**
     * @throws XmlUtilException
     */
    protected function parseXmlNode(bool $next): ?RowInterface
    {
        if ($this->current["row"] !== null && !$next) {
            return $this->current["row"];
        }

        if (!$this->hasNext()) {
            return null;
        }

        $rowNumber = $this->current["i"];
        $node = $this->nodeList->item($rowNumber);

        $row = new RowArray();
        $callables = [];

        foreach ($this->colNodes as $key => $colXpath) {
            if (is_callable($colXpath)) {
                $callables[$key] = $colXpath;
                continue;
            }

            $nodeCol = XmlNode::instance($node)->selectNodes($colXpath, $this->registerNS);
            if ($nodeCol->count() == 0) {
                $row->set(strtolower($key), "");
            } else {
                foreach ($nodeCol as $col) {
                    $row->set(strtolower($key), $col->nodeValue, append: true);
                }
            }
        }

        foreach ($callables as $key => $callable) {
            $row->set(strtolower($key), $callable($row), append: true);
        }

        $this->current = [
            'row' => $next ? null : $row,
            'i' => $rowNumber + ($next ? 1 : 0),
        ];

        return $row;
    }

    /**
     * @access public
     * @return Row|null
     * @throws XmlUtilException
     */
    public function moveNext(): ?RowInterface
    {
        return $this->parseXmlNode(next: true);
    }

    public function key(): int
    {
        return $this->current["i"];
    }

    /**
     * @throws XmlUtilException
     */
    #[ReturnTypeWillChange]
    public function current(): ?RowInterface
    {
        return $this->parseXmlNode(next: false);
    }
}
