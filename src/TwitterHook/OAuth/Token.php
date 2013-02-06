<?php

namespace TwitterHook\OAuth;

class Token
{
	/**
	 * Token
	 * @var string
	 */
	protected $token;

	/**
	 * Token Secret
	 * @var string
	 */
	protected $secret;

	public function __construct($accessToken)
	{
		$this->token = $accessToken["token"];
		$this->secret = $accessToken["secret"];
	}

	public function __get($prop) {
		return $this->$prop;
	}
}