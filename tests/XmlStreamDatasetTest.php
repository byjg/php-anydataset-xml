<?php

namespace Tests;

use ByJG\AnyDataset\Xml\XmlDataset;
use ByJG\AnyDataset\Xml\XmlStreamDataset;
use ByJG\Serializer\Exception\InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

class XmlStreamDatasetTest extends TestCase
{
    private const ATOM = '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:gd="http://schemas.google.com/g/2005">
        <id>feedId</id>
        <title type="text">Contacts</title>
        <entry>
          <id>1</id>
          <title type="text">Person 1</title>
          <gd:email address="p1@gmail.com"/>
        </entry>
        <entry>
          <id>2</id>
          <title type="text">Person 2</title>
          <gd:email address="p2@gmail.com"/>
        </entry>
        </feed>';

    private const NAMESPACES = [
        "fake" => "http://www.w3.org/2005/Atom",
        "gd" => "http://schemas.google.com/g/2005",
    ];

    private string $filename;

    #[Override]
    protected function setUp(): void
    {
        $this->filename = sys_get_temp_dir() . '/xml-stream-dataset-' . uniqid() . '.xml';
    }

    #[Override]
    protected function tearDown(): void
    {
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    private function file(string $xml): string
    {
        file_put_contents($this->filename, $xml);
        return $this->filename;
    }

    public function testSameRowsAsXmlDataset(): void
    {
        $columns = ["category" => "@category", "title" => "title", "lang" => "title/@lang"];

        $stream = new XmlStreamDataset($this->file(XmlDatasetTest::XML_OK), "book", $columns);

        $this->assertEquals(
            (new XmlDataset(XmlDatasetTest::XML_OK, "book", $columns))->getIterator()->toArray(),
            $stream->getIterator()->toArray()
        );
        $this->assertCount(3, $stream->getIterator()->toArray());
    }

    public function testNamespacesAndCallableColumns(): void
    {
        $columns = [
            "id" => "fake:id",
            "name" => "fake:title",
            "email" => "gd:email/@address",
            "item" => fn($row) => $row->get("name") . " - " . $row->get("email"),
        ];

        // fake:entry is the Atom namespace of $registerNS: the file has it as the default namespace, no prefix
        $stream = new XmlStreamDataset($this->file(self::ATOM), "fake:entry", $columns, self::NAMESPACES);

        $expected = [
            ["id" => "1", "name" => "Person 1", "email" => "p1@gmail.com", "item" => "Person 1 - p1@gmail.com"],
            ["id" => "2", "name" => "Person 2", "email" => "p2@gmail.com", "item" => "Person 2 - p2@gmail.com"],
        ];
        $this->assertEquals($expected, $stream->getIterator()->toArray());
        $this->assertEquals(
            (new XmlDataset(self::ATOM, "fake:entry", $columns, self::NAMESPACES))->getIterator()->toArray(),
            $stream->getIterator()->toArray()
        );
    }

    public function testRowElementAsWrittenInTheFile(): void
    {
        $stream = new XmlStreamDataset($this->file(self::ATOM), "entry", ["id" => "fake:id"], self::NAMESPACES);

        $this->assertEquals([["id" => "1"], ["id" => "2"]], $stream->getIterator()->toArray());
    }

    public function testRowInsideARowIsPartOfIt(): void
    {
        $xml = '<tree><node><name>a</name><node><name>b</name></node></node><node><name>c</name></node></tree>';

        $stream = new XmlStreamDataset($this->file($xml), "node", ["name" => "name"]);

        $this->assertEquals([["name" => "a"], ["name" => "c"]], $stream->getIterator()->toArray());
    }

    public function testFileIsClosedWhenAPartlyReadIteratorIsDestroyed(): void
    {
        $stream = new XmlStreamDataset($this->file(XmlDatasetTest::XML_OK), "book", ["title" => "title"]);
        $openFiles = fn() => count(scandir('/proc/self/fd'));
        $before = $openFiles();

        $iterator = $stream->getIterator();
        $this->assertEquals(["title" => "Everyday Italian"], $iterator->first());
        $this->assertEquals($before + 1, $openFiles());

        unset($iterator);
        $this->assertEquals($before, $openFiles());
    }

    public function testInvalidXml(): void
    {
        $stream = new XmlStreamDataset($this->file(XmlDatasetTest::XML_NOTOK), "book", ["name" => "nome"]);

        $this->expectException(InvalidArgumentException::class);
        $stream->getIterator()->toArray();
    }
}
