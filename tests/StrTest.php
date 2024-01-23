<?php
namespace Tests;

use lovely\helper\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    public function testCamel()
    {
        $this->assertSame('fooBar', Str::camel('FooBar'));
        $this->assertSame('fooBar', Str::camel('FooBar'));
        $this->assertSame('fooBar', Str::camel('foo_bar'));
        $this->assertSame('fooBar', Str::camel('_foo_bar'));
        $this->assertSame('fooBar', Str::camel('_foo_bar_'));
    }

    public function testStudly()
    {
        $this->assertSame('FooBar', Str::studly('fooBar'));
        $this->assertSame('FooBar', Str::studly('_foo_bar'));
        $this->assertSame('FooBar', Str::studly('_foo_bar_'));
        $this->assertSame('FooBar', Str::studly('_foo_bar_'));
    }

    public function testSnake()
    {
        $this->assertSame('lovely_p_h_p_framework', Str::snake('LovelyPHPFramework'));
        $this->assertSame('lovely_php_framework', Str::snake('LovelyPhpFramework'));
        $this->assertSame('lovely php framework', Str::snake('LovelyPhpFramework', ' '));
        $this->assertSame('lovely_php_framework', Str::snake('Lovely Php Framework'));
        $this->assertSame('lovely_php_framework', Str::snake('Lovely    Php      Framework   '));
        $this->assertSame('lovely__php__framework', Str::snake('LovelyPhpFramework', '__'));
        $this->assertSame('lovely_php_framework_', Str::snake('LovelyPhpFramework_', '_'));
        $this->assertSame('lovely_php_framework', Str::snake('Lovely php Framework'));
        $this->assertSame('lovely_php_frame_work', Str::snake('Lovely php FrameWork'));
        $this->assertSame('foo-bar', Str::snake('foo-bar'));
        $this->assertSame('foo-_bar', Str::snake('Foo-Bar'));
        $this->assertSame('foo__bar', Str::snake('Foo_Bar'));
        $this->assertSame('żółtałódka', Str::snake('ŻółtaŁódka'));
    }

    public function testTitle()
    {
        $this->assertSame('Welcome Back', Str::title('welcome back'));
    }

    public function testRandom()
    {
        // Check PHPUnit version for assertIsString
        if (method_exists($this, 'assertIsString')) {
            $this->assertIsString(Str::random(10));
        } else {
            // PHPUnit 8 and below
            $this->assertInternalType('string', Str::random(10));
        }
    }

    public function testUpper()
    {
        $this->assertSame('USERNAME', Str::upper('username'));
        $this->assertSame('USERNAME', Str::upper('userNaMe'));
    }
}