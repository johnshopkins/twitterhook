# TwitterHook

A PHP library to interact with the Twitter's REST API using single user sign-on OAuth authentication.

## Example
HttpExchange and Resty components autoloaded with Composer.

```php
<?php

include "path/to/vendor/autoload.php";

$consumer = array(
	"key" =>"consumerkey",
	"secret" => "consumersecret"
);

$token = array(
	"key" =>"accesstoken",
	"secret" => "accesstokensecret"
);

$httpEngine = new \HttpExchange\Adapters\Resty(new \Resty());

// If an HTTP engine is not passed to Client, cURL is used
$twitterHook = new \TwitterHook\Client($consumer, $token, $httpEngine);

$content = $twitterHook->get("statuses/home_timeline");

$tweet = $twitterHook->post("statuses/update", array(
  "status" => "You would not believe how I posted this tweet.",
));

```