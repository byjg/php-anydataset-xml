<?php

namespace ByJG\AnyDataset\Xml;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\Row;
use ByJG\XmlUtil\Exception\XmlUtilException;
use ByJG\XmlUtil\XmlNode;
use DOMNodeList;
use InvalidArgumentException;

class XmlIterator extends GenericIterator
{

    /**
     * Enter description here...
     *
     * @var DOMNodeList|null
     */
    private ?DOMNodeList $nodeList = null;

    /**
     * Enter description here...
     *
     * @var string[]
     */
    private ?array $colNodes = null;

    /**
     * Enter description here...
     *
     * @var int
     */
    private int $current = 0;
    protected array $registerNS;

    public function __construct(DOMNodeList $nodeList, array $colNodes, array $registerNS = null)
    {
        $this->registerNS = $registerNS;
        $this->nodeList = $nodeList;
        $this->colNodes = $colNodes;

        $this->current = 0;
    }

    public function count(): int
    {
        return $this->nodeList->length;
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext(): bool
    {
        if ($this->current < $this->count()) {
            return true;
        }

        return false;
    }

    /**
     * @access public
     * @return Row|null
     * @throws IteratorException
     * @throws XmlUtilException
     */
    public function moveNext(): ?Row
    {
        if (!$this->hasNext()) {
            return null;
        }

        $node = $this->nodeList->item($this->current++);

        $row = new Row();
        $callables = [];

        foreach ($this->colNodes as $key => $colxpath) {
            if (is_callable($colxpath)) {
                $callables[$key] = $colxpath;
                continue;
            }

            $nodeCol = XmlNode::instance($node)->selectNodes($colxpath, $this->registerNS);
            if ($nodeCol->count() == 0) {
                $row->addField(strtolower($key), "");
            } else {
                foreach ($nodeCol as $col) {
                    $row->addField(strtolower($key), $col->nodeValue);
                }
            }
        }

        foreach ($callables as $key => $callable) {
            $row->addField(strtolower($key), $callable($row));
        }

        return $row;
    }

    public function key(): int
    {
        return $this->current;
    }
}
