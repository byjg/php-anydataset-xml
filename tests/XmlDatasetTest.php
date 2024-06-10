<?php

namespace Tests;

use ByJG\AnyDataset\Core\IteratorInterface;
use ByJG\AnyDataset\Core\Row;
use ByJG\AnyDataset\Xml\XmlDataset;
use ByJG\XmlUtil\Exception\XmlUtilException;
use PHPUnit\Framework\TestCase;

class XmlDatasetTest extends TestCase
{

    const XML_OK = '<?xml version="1.0" encoding="UTF-8"?>
        <bookstore>
        <book category="COOKING">
          <title lang="en">Everyday Italian</title>
          <author>Giada De Laurentiis</author>
          <year>2005</year>
          <price>30.00</price>
        </book>
        <book category="CHILDREN">
          <title lang="de">Harry Potter</title>
          <author>J K. Rowling</author>
          <year>2005</year>
          <price>29.99</price>
        </book>
        <book category="WEB">
          <title lang="pt">Learning XML</title>
          <author>Erik T. Ray</author>
          <year>2003</year>
          <price>39.95</price>
        </book>
        </bookstore>';
    const XML_NOTOK = '<book><nome>joao</book>';

    protected $rootNode = "book";
    protected $arrColumn = array("category" => "@category", "title" => "title", "lang" => "title/@lang");
    protected $arrTest = array();
    protected $arrTest2 = array();

    // Run before each test case
    public function setUp(): void
    {
        $this->arrTest = array();
        $this->arrTest[] = array("category" => "COOKING", "title" => "Everyday Italian", "lang" => "en");
        $this->arrTest[] = array("category" => "CHILDREN", "title" => "Harry Potter", "lang" => "de");
        $this->arrTest[] = array("category" => "WEB", "title" => "Learning XML", "lang" => "pt");

        $this->arrTest2 = array();
        $this->arrTest2[] = array("id" => "Open");
        $this->arrTest2[] = array("id" => "OpenNew", "label" => "Open New");
    }


    public function testcreateXMLDataset()
    {
        $xmlDataset = new XmlDataset(XmlDatasetTest::XML_OK, $this->rootNode, $this->arrColumn);
        $xmlIterator = $xmlDataset->getIterator();

        $this->assertTrue($xmlIterator instanceof IteratorInterface);
        $this->assertTrue($xmlIterator->hasNext());
        $this->assertEquals($xmlIterator->Count(), 3);
    }

    public function testnavigateXMLIterator()
    {
        $xmlDataset = new XmlDataset(XmlDatasetTest::XML_OK, $this->rootNode, $this->arrColumn);
        $xmlIterator = $xmlDataset->getIterator();

        $count = 0;
        while ($xmlIterator->hasNext()) {
            $this->assertSingleRow($xmlIterator->moveNext(), $count++);
        }

        $this->assertEquals($count, 3);
    }

    public function testnavigateXMLIterator2()
    {
        $xmlDataset = new XmlDataset(XmlDatasetTest::XML_OK, $this->rootNode, $this->arrColumn);
        $xmlIterator = $xmlDataset->getIterator();

        $count = 0;
        foreach ($xmlIterator as $sr) {
            $this->assertSingleRow($sr, $count++);
        }

        $this->assertEquals($count, 3);
    }

    public function testxmlNotWellFormatted()
    {
        $this->expectException(XmlUtilException::class);
        new XmlDataset(XmlDatasetTest::XML_NOTOK, $this->rootNode, $this->arrColumn);
    }

    public function testwrongNodeRoot()
    {
        $xmlDataset = new XmlDataset(XmlDatasetTest::XML_OK, "wrong", $this->arrColumn);
        $xmlIterator = $xmlDataset->getIterator();

        $this->assertEquals($xmlIterator->count(), 0);
    }

    public function testwrongColumn()
    {
        $xmlDataset = new XmlDataset(XmlDatasetTest::XML_OK, $this->rootNode, array("title" => "aaaa"));
        $xmlIterator = $xmlDataset->getIterator();

        $this->assertEquals($xmlIterator->count(), 3);
    }

    public function testrepeatedNodes()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <bookstore>
        <book category="COOKING">
          <title lang="en">Everyday Italian</title>
          <author>Giada De Laurentiis</author>
          <author>Another Author</author>
          <year>2005</year>
          <price>30.00</price>
        </book></bookstore>';

        $xmlDataset = new XmlDataset($xml, $this->rootNode, array("author" => "author"));
        $xmlIterator = $xmlDataset->getIterator();

        $this->assertTrue($xmlIterator instanceof IteratorInterface);
        $this->assertTrue($xmlIterator->hasNext());
        $this->assertEquals(1, $xmlIterator->Count());

        $sr = $xmlIterator->moveNext();
        $authors = $sr->getAsArray('author');

        $this->assertEquals(2, count($authors));
        $this->assertEquals('Giada De Laurentiis', $authors[0]);
        $this->assertEquals('Another Author', $authors[1]);
    }

    public function testatomXml()
    {
        $xml = '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:batch="http://schemas.google.com/gdata/batch" xmlns:gContact="http://schemas.google.com/contact/2008" xmlns:gd="http://schemas.google.com/g/2005" xmlns:openSearch="http://a9.com/-/spec/opensearchrss/1.0/">
            <id>myId</id>
            <updated>2014-09-15T19:35:55.795Z</updated>
            <category scheme="http://schemas.google.com/g/2005#kind" term="gContact#contact"/>
            <title type="text">Title</title>
            <link rel="alternate" type="text/html" href="https://www.google.com/"/>
            <link rel="http://schemas.google.com/g/2005#feed" type="application/atom+xml" href="https://www.google.com/m8/feeds/contacts/my%40gmail.com/full"/>
            <link rel="http://schemas.google.com/g/2005#post" type="application/atom+xml" href="https://www.google.com/m8/feeds/contacts/my%40gmail.com/full"/>
            <link rel="http://schemas.google.com/g/2005#batch" type="application/atom+xml" href="https://www.google.com/m8/feeds/contacts/my%40gmail.com/full/batch"/>
            <link rel="self" type="application/atom+xml" href="https://www.google.com/m8/feeds/contacts/my%40gmail.com/full?max-results=20"/>
            <link rel="next" type="application/atom+xml" href="https://www.google.com/m8/feeds/contacts/my%40gmail.com/full?max-results=20&amp;start-index=21"/>
            <author>
             <name>My Name</name>
             <email>My Email</email>
            </author>
            <generator version="1.0" uri="http://www.google.com/m8/feeds">Contacts</generator>
            <openSearch:totalResults>2107</openSearch:totalResults>
            <openSearch:startIndex>1</openSearch:startIndex>
            <openSearch:itemsPerPage>20</openSearch:itemsPerPage>
            <entry>
             <id>http://www.google.com/m8/feeds/contacts/my%40gmail.com/base/0</id>
             <updated>2013-10-05T22:16:03.564Z</updated>
             <category scheme="http://schemas.google.com/g/2005#kind" term="gContact#contact"/>
             <title type="text">Person 1</title>
             <link rel="http://schemas.google.com/contacts/2008/rel#edit-photo" type="image/*" href="https://www.google.com/m8/feeds/photos/media/my%40gmail.com/1/ABCDE"/>
             <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*" href="https://www.google.com/m8/feeds/photos/media/my%40gmail.com/1"/>
             <link rel="self" type="application/atom+xml" href="https://www.google.com/m8/feeds/contacts/my%40gmail.com/full/0"/>
             <link rel="edit" type="application/atom+xml" href="https://www.google.com/m8/feeds/contacts/my%40gmail.com/full/0/1234"/>
             <gd:email rel="http://schemas.google.com/g/2005#other" address="p1@gmail.com" primary="true"/>
            </entry>
            <entry>
             <id>http://www.google.com/m8/feeds/contacts/my%40gmail.com/base/1</id>
             <updated>2012-07-12T17:19:17.546Z</updated>
             <category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/contact/2008#contact"/>
             <title type="text">Person 2</title>
             <link rel="http://schemas.google.com/contacts/2008/rel#edit-photo" type="image/*" href="https://www.google.com/m8/feeds/photos/media/my%40gmail.com/1/EDFGH"/>
             <link rel="http://schemas.google.com/contacts/2008/rel#photo" type="image/*" href="https://www.google.com/m8/feeds/photos/media/my%40gmail.com/1"/>
             <link rel="self" type="application/atom+xml" href="https://www.google.com/m8/feeds/contacts/my%40gmail.com/full/1"/>
             <link rel="edit" type="application/atom+xml" href="https://www.google.com/m8/feeds/contacts/my%40gmail.com/full/1/5678"/>
             <gd:email rel="http://schemas.google.com/g/2005#other" address="p2@gmail.com" primary="true"/>
            </entry></feed>';

        $namespace = array(
            "fake" => "http://www.w3.org/2005/Atom",
            "gd" => "http://schemas.google.com/g/2005"
        );
        $rootNode = 'fake:entry';
        $colNode = array("id" => "fake:id", "updated" => "fake:updated", "name" => "fake:title", "email" => "gd:email/@address", "item" => function($row) { return $row->get("name") . " - " . $row->get("email"); });
        $xmlDataset = new XmlDataset($xml, $rootNode, $colNode, $namespace);
        $xmlIterator = $xmlDataset->getIterator();

        $this->assertTrue($xmlIterator instanceof IteratorInterface);
        $this->assertTrue($xmlIterator->hasNext());
        $this->assertEquals(2, $xmlIterator->Count());

        $row = $xmlIterator->moveNext();
        $this->assertEquals("http://www.google.com/m8/feeds/contacts/my%40gmail.com/base/0", $row->get("id"));
        $this->assertEquals("2013-10-05T22:16:03.564Z", $row->get("updated"));
        $this->assertEquals("Person 1", $row->get("name"));
        $this->assertEquals("p1@gmail.com", $row->get("email"));
        $this->assertEquals("Person 1 - p1@gmail.com", $row->get("item"));

        $row = $xmlIterator->moveNext();
        $this->assertEquals("http://www.google.com/m8/feeds/contacts/my%40gmail.com/base/1", $row->get("id"));
        $this->assertEquals("2012-07-12T17:19:17.546Z", $row->get("updated"));
        $this->assertEquals("Person 2", $row->get("name"));
        $this->assertEquals("p2@gmail.com", $row->get("email"));
        $this->assertEquals("Person 2 - p2@gmail.com", $row->get("item"));
    }

    /**

     * @param Row $sr
     */
    public function assertSingleRow($sr, $count)
    {
        $this->assertEquals($sr->get("category"), $this->arrTest[$count]["category"]);
        $this->assertEquals($sr->get("title"), $this->arrTest[$count]["title"]);
        $this->assertEquals($sr->get("lang"), $this->arrTest[$count]["lang"]);
    }

}
