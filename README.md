# TwitterHook

A PHP library to interact with the Twitter's REST API using single user sign-on OAuth authentication.

## Example

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

$twitterHook = new \TwitterHook\Client($consumer, $token);

$content = $twitterHook->get("statuses/home_timeline");

$tweet = $twitterHook->post("statuses/update", array(
  "status" => "You would not believe how I posted this tweet.",
));

```
