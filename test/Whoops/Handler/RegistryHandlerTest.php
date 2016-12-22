<?php

namespace Test\Registry\Whoops;

use Exception;

use PHPUnit_Framework_TestCase;
use Psr\Http\Message\ResponseInterface;
use Registry\Client\Model\Event;
use Registry\Client\Store;
use Whoops\Exception\Inspector;
use Whoops\Handler\Handler;

use Registry\Whoops\Formatter\RequestExceptionFormatter;
use Registry\Whoops\Handler\RegistryHandler;

class RegistryHandlerTest extends PHPUnit_Framework_TestCase
{
    private $event;
    private $fomatter;
    private $inspector;
    private $request;
    private $store;

    protected function setUp()
    {
        $this->event = $this
            ->getMockBuilder(Event::class)
            ->setMethods(array('save'))
            ->getMock()
        ;
        $this->formatter = $this
            ->getMockBuilder(RequestExceptionFormatter::class)
            ->getMock()
        ;
        $this->inspector = $this
            ->getMockBuilder(Inspector::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->request = $this
            ->getMockBuilder(ResponseInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;
        $this->store = $this
            ->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(array('createEvent'))
            ->getMock()
        ;
    }

    public function testHandlerWhenClientCommsFailWillExitNormally()
    {
        $handler = new RegistryHandler($this->formatter, $this->store);
        $handler->setInspector($this->inspector);

        $this
            ->formatter
            ->expects($this->once())
            ->method('getExceptionProperties')
            ->with($this->isInstanceOf(Inspector::class))
            ->willReturn(array())
        ;
        $this
            ->store
            ->expects($this->once())
            ->method('createEvent')
            ->willReturn($this->event)
        ;
        $this
            ->event
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new Exception('badness'))
        ;
        $this
            ->request
            ->expects($this->never())
            ->method('getBody')
        ;
        $this->assertSame(
            Handler::DONE,
            $handler->handle(),
            'The handler will exit normally.'
        );
    }

    public function testHandlerWhenRegistryReturnsWeirdnessWillExitNormally()
    {
        $handler = new RegistryHandler($this->formatter, $this->store);
        $handler->setInspector($this->inspector);

        $this
            ->formatter
            ->expects($this->once())
            ->method('getExceptionProperties')
            ->with($this->isInstanceOf(Inspector::class))
            ->willReturn(array())
        ;
        $this
            ->store
            ->expects($this->once())
            ->method('createEvent')
            ->willReturn($this->event)
        ;
        $this
            ->event
            ->expects($this->once())
            ->method('save')
            ->willReturn($this->request)
        ;
        $this
            ->request
            ->expects($this->once())
            ->method('getBody')
            ->willReturn(null)
        ;
        $this->assertSame(
            Handler::DONE,
            $handler->handle(),
            'The handler will exit normally.'
        );
    }

    public function testHandlerWhenRegistryReturnsFailureWillExitNormally()
    {
        $handler = new RegistryHandler($this->formatter, $this->store);
        $handler->setInspector($this->inspector);

        $this
            ->formatter
            ->expects($this->once())
            ->method('getExceptionProperties')
            ->with($this->isInstanceOf(Inspector::class))
            ->willReturn(array())
        ;
        $this
            ->store
            ->expects($this->once())
            ->method('createEvent')
            ->willReturn($this->event)
        ;
        $this
            ->event
            ->expects($this->once())
            ->method('save')
            ->willReturn($this->request)
        ;
        $this
            ->request
            ->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode(array('success' => false)))
        ;
        $this->assertSame(
            Handler::DONE,
            $handler->handle(),
            'The handler will exit normally.'
        );
    }

    public function testHandlerWhenRegistryReturnsSucessWillExitNormally()
    {
        $handler = new RegistryHandler($this->formatter, $this->store);
        $handler->setInspector($this->inspector);

        $this
            ->formatter
            ->expects($this->once())
            ->method('getExceptionProperties')
            ->with($this->isInstanceOf(Inspector::class))
            ->willReturn(array())
        ;
        $this
            ->store
            ->expects($this->once())
            ->method('createEvent')
            ->willReturn($this->event)
        ;
        $this
            ->event
            ->expects($this->once())
            ->method('save')
            ->willReturn($this->request)
        ;
        $this
            ->request
            ->expects($this->once())
            ->method('getBody')
            ->willReturn(json_encode(array('success' => true)))
        ;
        $this->assertSame(
            Handler::DONE,
            $handler->handle(),
            'The handler will exit normally.'
        );
    }
}
