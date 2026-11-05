<?php

namespace ByJG\AnyDataset\Xml;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowArray;
use ByJG\AnyDataset\Core\RowInterface;
use ByJG\XmlUtil\Exception\XmlUtilException;
use ByJG\XmlUtil\XmlNode;
use DOMNode;
use Generator;
use Override;

class XmlIterator extends GenericIterator
{

    /**
     * The row nodes
     *
     * @var iterable<DOMNode>
     */
    private iterable $nodes;

    /**
     * The XPath (or callable) of each column
     *
     * @var array<string, string|callable>
     */
    private array $colNodes;

    protected array $registerNS;

    /**
     * @param iterable<DOMNode> $nodes The row nodes: a DOMNodeList, or the elements read from a file one at a time
     * @param array $colNodes
     * @param array|null $registerNS
     */
    public function __construct(iterable $nodes, array $colNodes, ?array $registerNS = null)
    {
        $this->registerNS = $registerNS ?? [];
        $this->nodes = $nodes;
        $this->colNodes = array_change_key_case($colNodes);
    }

    /**
     * @throws XmlUtilException
     */
    #[Override]
    protected function rows(): Generator
    {
        return self::walk($this->nodes, $this->colNodes, $this->registerNS);
    }

    /**
     * @param iterable<DOMNode> $nodes
     * @throws XmlUtilException
     */
    private static function walk(iterable $nodes, array $colNodes, array $registerNS): Generator
    {
        foreach ($nodes as $node) {
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
