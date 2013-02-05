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

	/**
	 * Requested format of response
	 * @var string
	 */
	public $format = "json";

	/**
	 * [$decodeJson description]
	 * @var boolean
	 */
	public $decodeJson = false;

	public function __construct($client, $token, \HttpExchange\Interfaces\ClientInterface $httpEngine = null)
	{
		$this->clientCred = new OAuth\Consumer($client);
		$this->tokenCred = new OAuth\Consumer($token);
		$this->httpEngine = $httpEngine;
	}

	/**
	 * Makes a GET call to the passed URL
	 * @param  string $url    API endpoint
	 * @param  array  $params Request parameters (key => value)
	 * @return response data
	 */
	public function get($url, $params = array())
	{
		$params = $this->cleanParams($params);

		$response = $this->oAuthRequest($url, "GET", $params);

		if ($this->format === "json" && $this->decodeJson) {
			return json_decode($response);
		}
		return $response;
	}

	/**
	 * Converts false values to 0. The false value causes the
	 * signature not to be correct because it is an empty value,
	 * but when it comes back from Twitter, it's a zero, so the
	 * signatures do not match.
	 * 
	 * @param  array $params Request parameters
	 * @return array Cleaned request parameters
	 */
	protected function cleanParams($params)
	{
		foreach ($params as $k => $v) {
			if ($v === false) {
				$params[$k] = 0;
			}
		}

		return $params;
	}

	protected function oAuthRequest($url, $method, $params)
	{
		$url = $this->buildRequestUrl($url);

		$request = new OAuth\Request($this->clientCred, $this->tokenCred, $method, $url, $params);

		$request->sign($this->clientCred, $this->tokenCred);

		if ($this->httpEngine) {
			return $this->httpCall($request);
		} else {
			return $this->curlCall($request);
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
	protected function curlCall($request)
	{
		$url = $request->compileUrlWithParams();
		$oAuthHeader = $request->compileOAuthHeader();

		$this->decodeJson = true;

		$ci = curl_init();

		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ci, CURLOPT_HEADER, false);
		curl_setopt($ci, CURLOPT_URL, $url);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array("Authorization: {$oAuthHeader}"));

		$response = curl_exec($ci);
		curl_close ($ci);

		return $response;
	}

}