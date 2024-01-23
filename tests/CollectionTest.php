<?php
namespace Tests;

use lovely\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testMerge()
    {
        $c = new Collection(['name' => 'Hello']);
        $this->assertSame(['name' => 'Hello', 'id' => 1], $c->merge(['id' => 1])->toArray());
    }

    public function testFirst()
    {
        $c = new Collection(['name' => 'Hello', 'age' => 25]);

        $this->assertSame('Hello', $c->first());
    }

    public function testLast()
    {
        $c = new Collection(['name' => 'Hello', 'age' => 25]);

        $this->assertSame(25, $c->last());
    }

    public function testToArray()
    {
        $c = new Collection(['name' => 'Hello', 'age' => 25]);

        $this->assertSame(['name' => 'Hello', 'age' => 25], $c->toArray());
    }

    public function testToJson()
    {
        $c = new Collection(['name' => 'Hello', 'age' => 25]);

        $this->assertSame(json_encode(['name' => 'Hello', 'age' => 25]), $c->toJson());
        $this->assertSame(json_encode(['name' => 'Hello', 'age' => 25]), (string) $c);
        $this->assertSame(json_encode(['name' => 'Hello', 'age' => 25]), json_encode($c));
    }

    public function testSerialize()
    {
        $c = new Collection(['name' => 'Hello', 'age' => 25]);

        $sc = serialize($c);
        $c = unserialize($sc);

        $this->assertSame(['name' => 'Hello', 'age' => 25], $c->toArray());
    }

    public function testGetIterator()
    {
        $c = new Collection(['name' => 'Hello', 'age' => 25]);

        $arrayIteratorClass = version_compare(PHP_VERSION, '5.6.0') >= 0 ? \ArrayIterator::class : 'ArrayIterator';

        $this->assertInstanceOf($arrayIteratorClass, $c->getIterator());

        $this->assertSame(['name' => 'Hello', 'age' => 25], $c->getIterator()->getArrayCopy());
    }

    public function testCount()
    {
        $c = new Collection(['name' => 'Hello', 'age' => 25]);

        $this->assertCount(2, $c);
    }
}