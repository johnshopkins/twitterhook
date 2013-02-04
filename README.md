# TwitterHook

A PHP library to interact with the GET methods of the Twitter API using single user sign-on OAuth authentication.

## Example with HttpExchange and Resty components autoloaded with Composer.

```php
<?php

include "path/to/vendor/autoload.php";

$client = array(
	"key" =>"consumerkey",
	"secret" => "consumersecret"
);

$token = array(
	"key" =>"accesstoken",
	"secret" => "accesstokensecret"
);

$httpEngine = new \HttpExchange\Adapters\Resty(new \Resty());

// If an HTTP engine is not passed to Client, cURL is used
$twitterHook = new \TwitterHook\Client($client, $token, $httpEngine);

$content = $twitterHook->get("statuses/home_timeline");

```