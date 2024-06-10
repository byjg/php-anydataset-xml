<?php

namespace ByJG\AnyDataset\Xml;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Exception\IteratorException;
use ByJG\AnyDataset\Core\Row;
use ByJG\Util\XmlNode;
use DOMNodeList;
use InvalidArgumentException;

class XmlIterator extends GenericIterator
{

    /**
     * Enter description here...
     *
     * @var DOMNodeList
     */
    private $nodeList = null;

    /**
     * Enter description here...
     *
     * @var string[]
     */
    private $colNodes = null;

    /**
     * Enter description here...
     *
     * @var int
     */
    private $current = 0;
    protected $registerNS;

    public function __construct($nodeList, $colNodes, $registerNS)
    {
        if (!($nodeList instanceof DOMNodeList)) {
            throw new InvalidArgumentException("XmlIterator: Wrong node list type");
        }
        if (!is_array($colNodes)) {
            throw new InvalidArgumentException("XmlIterator: Wrong column node type");
        }


        $this->registerNS = $registerNS;
        $this->nodeList = $nodeList;
        $this->colNodes = $colNodes;

        $this->current = 0;
    }

    public function count()
    {
        return $this->nodeList->length;
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if ($this->current < $this->count()) {
            return true;
        }

        return false;
    }

    /**
     * @access public
     * @return Row
     * @throws IteratorException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \ByJG\Util\Exception\XmlUtilException
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
        }

        $node = $this->nodeList->item($this->current++);

        $row = new Row();
        $callables = [];

        foreach ($this->colNodes as $key => $colxpath) {
            if (is_callable($colxpath)) {
                $callables[$key] = $colxpath;
                continue;
            }

            $nodecol = XmlNode::instance($node)->selectNodes($colxpath, $this->registerNS);
            if (is_null($nodecol)) {
                $row->addField(strtolower($key), "");
            } else {
                foreach ($nodecol as $col) {
                    $row->addField(strtolower($key), $col->nodeValue);
                }
            }
        }

        foreach ($callables as $key => $callable) {
            $row->addField(strtolower($key), $callable($row));
        }

        return $row;
    }

    public function key()
    {
        return $this->current;
    }
}
