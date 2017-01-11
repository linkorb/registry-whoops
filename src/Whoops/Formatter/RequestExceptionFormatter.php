<?php

namespace Registry\Whoops\Formatter;

use Whoops\Exception\Formatter;
use Whoops\Exception\Inspector;

class RequestExceptionFormatter
{
    /**
     * Format an exception and PHP request environment as an array of properties.
     *
     * @param \Whoops\Exception\Inspector $inspector
     * @param string $withStackTrace
     *
     * @return array
     */
    public function getExceptionProperties(Inspector $inspector, $withStackTrace = true)
    {
        $exception = Formatter::formatExceptionAsDataArray($inspector, $withStackTrace);

        if ($withStackTrace) {
            $exception['trace'] = $this->formatTrace($exception['trace']);
        }

        $properties = ['exception' => $exception];

        if (!empty($_GET)) {
            $properties['request']['get'] = $_GET;
        }

        if (!empty($_POST)) {
            $properties['request']['post'] = $_POST;
        }

        if (!empty($_FILES)) {
            $properties['request']['files'] = $_FILES;
        }

        if (!empty($_COOKIE)) {
            $properties['request']['cookie'] = $_COOKIE;
        }

        if (isset($_SESSION) && !empty($_SESSION)) {
            $properties['request']['session'] = $_SESSION;
        }

        if (!empty($_SERVER)) {
            $properties['request']['server'] = $_SERVER;
        }

        if (!empty($_ENV)) {
            $properties['request']['env'] = $_ENV;
        }

        return $properties;
    }

    /*
     * Further format the stack trace.
     */
    private function formatTrace($originalFrames)
    {
        $frames = [];
        foreach ($originalFrames as $originalFrame) {
            $formattedFrame = [
                'file' => $originalFrame['file'],
                'line' => $originalFrame['line'],
                'function' => $originalFrame['function'],
                'class' => $originalFrame['class'],
            ];
            $formattedArgs = [];
            foreach ($originalFrame['args'] as $originalArg) {
                $formattedArgs[] = $this->formatArg($originalArg);
            }
            $formattedFrame['args'] = $formattedArgs;
            $frames[] = $formattedFrame;
        }
        return $frames;
    }

    /*
     * Convert non-string args to some kind of string representation.
     */
    private function formatArg($originalArg)
    {
        if (is_array($originalArg)) {
            return 'Array(...)';
        } elseif (is_bool($originalArg)) {
            return sprintf('bool(%s)', $originalArg ? 'true' : 'false');
        } elseif (is_object($originalArg)) {
            return get_class($originalArg);
        }
        return $originalArg;
    }
}
