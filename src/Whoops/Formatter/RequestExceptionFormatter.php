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
        $properties = array(
            'exception' => Formatter::formatExceptionAsDataArray(
                $inspector,
                $withStackTrace
            ),
        );

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

        if (!empty($_SESSION)) {
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
}
