<?php

namespace TwitterHook\OAuth;

class Signature
{
	protected $base;
	protected $key;

	protected $signature;

	public function __construct($request, $consumer, $accessToken)
	{
		$base = $request->getSignatureBaseString();

		$keyParts = array(
			$consumer->secret,
			$accessToken->secret
		);

		$keyParts = Utility::urlencode_rfc3986($keyParts);
		$key = implode('&', $keyParts);

		$this->signature = base64_encode(hash_hmac("sha1", $base, $key, true));
	}

	public function get()
	{
		return $this->signature;
	}
}