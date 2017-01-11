<?php

namespace Registry\Whoops\Handler;

use Exception;

use Registry\Client\Store;
use Whoops\Handler\Handler;

use Registry\Whoops\Formatter\RequestExceptionFormatter;

/**
 * Whoops handler to store an exception via the Registry API.
 */
class RegistryHandler extends Handler
{
    /**
     * @var \Registry\Client\Store
     */
    private $registryStore;
    /**
     *
     * @var \Registry\Whoops\Formatter\RequestExceptionFormatter
     */
    private $formatter;

    public function __construct(
        RequestExceptionFormatter $formatter,
        Store $registryStore
    ) {
        $this->formatter = $formatter;
        $this->registryStore = $registryStore;
    }

    public function handle()
    {
        $properties = $this
            ->formatter
            ->getExceptionProperties($this->getInspector())
        ;

        $event = $this
            ->registryStore
            ->createEvent('exception', null, $properties)
        ;

        $response = null;
        try {
            $response = $event->save();
        } catch (Exception $e) {
            return Handler::DONE;
        }

        $result = json_decode((string) $response->getBody(), true);

        if (!$result || !$result['success']) {
            # NoOp
        }

        return Handler::DONE;
    }
}
