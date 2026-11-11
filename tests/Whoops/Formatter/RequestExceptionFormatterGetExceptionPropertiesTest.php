<?php

namespace Test\Registry\Whoops\Formatter;

use Exception;
use PHPUnit_Framework_TestCase;
use Registry\Whoops\Formatter\RequestExceptionFormatter;
use Whoops\Exception\Inspector;

class RequestExceptionFormatterGetExceptionPropertiesTest extends PHPUnit_Framework_TestCase
{
    private $savedGet;
    private $savedPost;
    private $savedFiles;
    private $savedCookie;
    private $savedServer;
    private $savedEnv;
    private $sessionWasSet;
    private $savedSession;

    protected function setUp()
    {
        $this->savedGet = $_GET;
        $this->savedPost = $_POST;
        $this->savedFiles = $_FILES;
        $this->savedCookie = $_COOKIE;
        $this->savedServer = $_SERVER;
        $this->savedEnv = $_ENV;
        $this->sessionWasSet = isset($_SESSION);
        $this->savedSession = $this->sessionWasSet ? $_SESSION : null;
    }

    protected function tearDown()
    {
        $_GET = $this->savedGet;
        $_POST = $this->savedPost;
        $_FILES = $this->savedFiles;
        $_COOKIE = $this->savedCookie;
        $_SERVER = $this->savedServer;
        $_ENV = $this->savedEnv;
        if ($this->sessionWasSet) {
            $_SESSION = $this->savedSession;
        } else {
            unset($_SESSION);
        }
    }

    public function testGetExceptionPropertiesCopiesPopulatedRequestAndSkipsTrace()
    {
        $_GET = array('q' => 'search');
        $_POST = array('name' => 'ada');
        $_FILES = array('upload' => array('name' => 'a.txt'));
        $_COOKIE = array('sid' => 'abc');
        $_SESSION = array('user' => 'ada');
        $_SERVER = array('REQUEST_METHOD' => 'POST');
        $_ENV = array('APP_ENV' => 'test');

        $exception = $this->captureException();
        $formatter = new RequestExceptionFormatter();
        $properties = $formatter->getExceptionProperties(new Inspector($exception), false);

        $this->assertSame(
            array(
                'exception' => array(
                    'type' => 'Exception',
                    'message' => 'characterized',
                    'code' => 42,
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ),
                'request' => array(
                    'get' => array('q' => 'search'),
                    'post' => array('name' => 'ada'),
                    'files' => array('upload' => array('name' => 'a.txt')),
                    'cookie' => array('sid' => 'abc'),
                    'session' => array('user' => 'ada'),
                    'server' => array('REQUEST_METHOD' => 'POST'),
                    'env' => array('APP_ENV' => 'test'),
                ),
            ),
            $properties,
            'Populated request data is copied and the trace is omitted when disabled.'
        );
    }

    public function testGetExceptionPropertiesOmitsEmptyRequestAndFormatsTrace()
    {
        $_GET = array();
        $_POST = array();
        $_FILES = array();
        $_COOKIE = array();
        unset($_SESSION);
        $_SERVER = array();
        $_ENV = array();

        $exception = $this->captureException();
        $formatter = new RequestExceptionFormatter();
        $properties = $formatter->getExceptionProperties(new Inspector($exception));

        $this->assertSame(array('exception'), array_keys($properties));
        $this->assertSame('Exception', $properties['exception']['type']);
        $this->assertSame('characterized', $properties['exception']['message']);
        $this->assertSame(42, $properties['exception']['code']);
        $this->assertSame($exception->getFile(), $properties['exception']['file']);
        $this->assertSame($exception->getLine(), $properties['exception']['line']);

        $trace = $properties['exception']['trace'];
        $this->assertGreaterThan(1, count($trace));
        $this->assertSame(
            array(
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'function' => null,
                'class' => 'Exception',
                'args' => array('characterized'),
            ),
            $trace[0],
            'The synthetic top frame is stored with a null function.'
        );

        $caller = $exception->getTrace();
        $this->assertSame($caller[0]['file'], $trace[1]['file']);
        $this->assertSame($caller[0]['line'], $trace[1]['line']);
        $this->assertSame($caller[0]['function'], $trace[1]['function']);
        $this->assertSame($caller[0]['class'], $trace[1]['class']);
        $this->assertSame(array(), $trace[1]['args']);

        foreach ($trace as $frame) {
            $this->assertSame(
                array('file', 'line', 'function', 'class', 'args'),
                array_keys($frame)
            );
        }
    }

    public function testGetExceptionPropertiesOmitsSessionWhenItIsEmpty()
    {
        $_GET = array('q' => 'search');
        $_POST = array();
        $_FILES = array();
        $_COOKIE = array();
        $_SESSION = array();
        $_SERVER = array();
        $_ENV = array();

        $exception = $this->captureException();
        $formatter = new RequestExceptionFormatter();
        $properties = $formatter->getExceptionProperties(new Inspector($exception), false);

        $this->assertSame(
            array(
                'exception' => array(
                    'type' => 'Exception',
                    'message' => 'characterized',
                    'code' => 42,
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                ),
                'request' => array(
                    'get' => array('q' => 'search'),
                ),
            ),
            $properties,
            'An empty session is omitted while other populated request data is kept.'
        );
    }

    private function captureException()
    {
        try {
            $this->throwCharacterizedException();
        } catch (Exception $exception) {
            return $exception;
        }

        $this->fail('Expected the characterized exception to be thrown.');
    }

    private function throwCharacterizedException()
    {
        throw new Exception('characterized', 42);
    }
}
