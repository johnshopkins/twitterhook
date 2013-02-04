<?php

namespace TwitterHook\OAuth;

class Consumer
{
	/**
	 * Public key
	 * @var string
	 */
	protected $key;

	/**
	 * Secret key
	 * @var string
	 */
	protected $secret;

	public function __construct($consumer)
	{
		$this->key = $consumer["key"];
		$this->secret = $consumer["secret"];
	}

	public function __get($prop) {
		return $this->$prop;
	}
}