<?php

namespace ByJG\AnyDataset\Xml;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use ByJG\AnyDataset\Core\RowInterface;
use ByJG\XmlUtil\Exception\XmlUtilException;
use ByJG\XmlUtil\XmlNode;
use DOMNode;
use DOMNodeList;
use Generator;
use Override;

class XmlIterator extends GenericIterator
{

    /**
     * Enter description here...
     *
     * @var DOMNodeList
     */
    private DOMNodeList $nodeList;

    /**
     * Enter description here...
     *
     * @var array<string, string|callable>
     */
    private array $colNodes;

    protected array $registerNS;

    public function __construct(DOMNodeList $nodeList, array $colNodes, ?array $registerNS = null)
    {
        $this->registerNS = $registerNS ?? [];
        $this->nodeList = $nodeList;
        $this->colNodes = array_change_key_case($colNodes);
    }

    /**
     * @throws XmlUtilException
     */
    #[Override]
    protected function rows(): Generator
    {
        return self::walk($this->nodeList, $this->colNodes, $this->registerNS);
    }

    /**
     * @throws XmlUtilException
     */
    private static function walk(DOMNodeList $nodeList, array $colNodes, array $registerNS): Generator
    {
        foreach ($nodeList as $node) {
            yield self::parseXmlNode($node, $colNodes, $registerNS);
        }
    }

    /**
     * @throws XmlUtilException
     */
    protected static function parseXmlNode(DOMNode $node, array $colNodes, array $registerNS): RowInterface
    {
        $row = new RowArray();
        $callables = [];

        $xmlNode = XmlNode::instance($node);
        foreach ($colNodes as $key => $colXpath) {
            if (!is_string($colXpath) && is_callable($colXpath)) {
                $callables[$key] = $colXpath;
                continue;
            }

            $nodeCol = $xmlNode->selectNodes($colXpath, $registerNS);
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

        return $row;
    }
}
