<?php

namespace ByJG\AnyDataset\Xml;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\XmlUtil\Exception\XmlUtilException;
use ByJG\XmlUtil\File;
use ByJG\XmlUtil\XmlDocument;
use ByJG\XmlUtil\XmlNode;
use DOMDocument;
use InvalidArgumentException;

class XmlDataset
{

    /**
     * String
     *
     * @var string
     */
    private string $rowNode;

    /**
     * Enter description here...
     *
     * @var string[]
     */
    private ?array $colNodes;

    /**
     * @var XmlDocument
     */
    private XmlDocument $domDocument;

    /**
     *
     * @var array
     */
    protected array $registerNS;

    /**
     * @param XmlNode|DOMDocument|string|File $xml
     * @param string $rowNode
     * @param string[] $colNode
     * @param array $registerNS
     * @throws XmlUtilException
     */
    public function __construct(XmlNode|DOMDocument|string|File $xml, string $rowNode, array $colNode, array $registerNS = [])
    {
        $this->domDocument = new XmlDocument($xml);

        $this->registerNS = $registerNS;
        $this->rowNode = $rowNode;
        $this->colNodes = $colNode;
    }

    /**
     * @access public
     * @return GenericIterator
     * @throws XmlUtilException
     */
    public function getIterator(): GenericIterator
    {
        return new XmlIterator(
            $this->domDocument->selectNodes(
                $this->rowNode,
                $this->registerNS
            ),
            $this->colNodes,
            $this->registerNS
        );
    }
}
