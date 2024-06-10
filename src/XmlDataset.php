<?php

namespace ByJG\AnyDataset\Xml;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Exception\DatasetException;
use ByJG\XmlUtil\Exception\XmlUtilException;
use ByJG\XmlUtil\File;
use ByJG\XmlUtil\XmlDocument;
use DOMDocument;
use InvalidArgumentException;

class XmlDataset
{

    /**
     * String
     *
     * @var string
     */
    private $rowNode = null;

    /**
     * Enter description here...
     *
     * @var string[]
     */
    private $colNodes = null;

    /**
     * @var DOMDocument
     */
    private $domDocument;

    /**
     *
     * @var string
     */
    protected $registerNS;

    /**
     * @param DOMDocument|string|File $xml
     * @param string $rowNode
     * @param string[] $colNode
     * @param null $registerNS
     * @throws DatasetException
     * @throws XmlUtilException
     */
    public function __construct(DOMDocument|string|File $xml, string $rowNode, array $colNode, array $registerNS = [])
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
    public function getIterator()
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
