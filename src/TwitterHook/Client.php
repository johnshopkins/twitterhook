<?php

namespace TwitterHook;

class Client
{
	protected $httpEngine = null;

	/**
	 * Consumer object for client
	 * @var object
	 */
	protected $clientCred;

	/**
	 * Consumer object for token
	 * @var object
	 */
	protected $tokenCred;

	/**
	 * Base URL of Twitter API
	 * @var string
	 */
	protected $apiBase = "https://api.twitter.com";

	/**
	 * Version of Twitter API to targets
	 * @var string
	 */
	protected $apiVersion = "1.1";



	public $format = 'json';
	public $decode_json = false;

	function __construct($client, $token, \HttpExchange\Interfaces\ClientInterface $httpEngine = null)
	{
		$this->clientCred = new OAuth\Consumer($client);
		$this->tokenCred = new OAuth\Consumer($token);
		$this->httpEngine = $httpEngine;
	}

	function get($url, $params = array())
	{
		$response = $this->oAuthRequest($url, "GET", $params);
		if ($this->format === "json" && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}

	function oAuthRequest($url, $method, $params)
	{
		$url = $this->buildRequestUrl($url);

		$request = new OAuth\Request($this->clientCred, $this->tokenCred, $method, $url, $params);

		$request->sign($this->clientCred, $this->tokenCred);

		if ($this->httpEngine) {
			return $this->httpCall($request);
		} else {
			return $this->curlCall($request->to_url());
		}
		
	}

	protected function getRequestEndpoint($url)
	{
		$urlParts = parse_url($url);
		return trim($urlParts["path"], "/");
	}

	protected function buildRequestUrl($url)
	{
		$endpoint = $this->getRequestEndpoint($url);
		return "{$this->apiBase}/{$this->apiVersion}/{$endpoint}.{$this->format}";	
	}



	/**
	 * Make the request using HTTP
	 * @param  string $url Request URL
	 * @return array
	 */
	protected function httpCall($request)
	{
		$url = $request->httpUrl;
		$params = $request->requestParams;
		$oAuthHeader = $request->compileOAuthHeader();

		return $this->httpEngine->get($url, $params, array("Authorization" => $oAuthHeader))->getBody();
	}

	/**
	 * Make the request using cURL
	 * @param  string $url Request URL
	 * @return array
	 */
	protected function curlCall($url)
	{
		$this->decode_json = true;

		$ci = curl_init();

		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_HEADER, false);
		curl_setopt($ci, CURLOPT_URL, $url);

		$response = curl_exec($ci);
		curl_close ($ci);

		return $response;
	}

}