<?php

namespace Test\Registry\Whoops\Formatter;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use Registry\Whoops\Formatter\RequestExceptionFormatter;
use stdClass;

class RequestExceptionFormatterFormatArgTest extends PHPUnit_Framework_TestCase
{
    public function testFormatArgRendersArrayAsPlaceholder()
    {
        $this->assertSame('Array(...)', $this->formatArg(array(1, 'two')));
    }

    public function testFormatArgRendersTrueAsBoolTrue()
    {
        $this->assertSame('bool(true)', $this->formatArg(true));
    }

    public function testFormatArgRendersFalseAsBoolFalse()
    {
        $this->assertSame('bool(false)', $this->formatArg(false));
    }

    public function testFormatArgRendersObjectAsClassName()
    {
        $this->assertSame('stdClass', $this->formatArg(new stdClass()));
    }

    public function testFormatArgReturnsOtherValuesUnchanged()
    {
        $this->assertSame(42, $this->formatArg(42));
    }

    private function formatArg($originalArg)
    {
        $method = new ReflectionMethod(RequestExceptionFormatter::class, 'formatArg');
        $method->setAccessible(true);

        return $method->invoke(new RequestExceptionFormatter(), $originalArg);
    }
}
