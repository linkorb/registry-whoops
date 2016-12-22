# Registry Whoops

Whoops Formatter and Handler for Registry.

## Installation

```sh
$ composer require linkorb/registry-client-php linkorb/registry-whoops
```

## Integration

Create an instance of the Registry Store client:-

```php
use Registry\Client\ClientBuilder;
use Registry\Client\Store;

$config = array(
    'api_host' => 'registry.example.com',
    'auth' => array('myusername', 'mypassword'),
    'secure' => true,
);
$store = new Store(new ClientBuilder($config), 'myaccount', 'mystore');
```

and an instance of the Whoops handler:-

```php
use Registry\Whoops\Formatter\RequestExceptionFormatter;
use Registry\Whoops\Handler\RegistryHandler;

$handler = new RegistryHandler(new RequestExceptionFormatter, $store);
```

and register the Whoops handler with Whoops:-

```php
$whoops->pushHandler($handler);
```
