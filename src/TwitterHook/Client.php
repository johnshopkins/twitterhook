<?php

namespace TwitterHook;

class Client
{
	/**
	 * A compatible HTTP object that can make GET requests
	 * by way of $http->get() and returns the results in a
	 * response object
	 * @var mixed: object or null
	 */
	protected $httpEngine = null;

	/**
	 * Consumer object for consumer
	 * @var object
	 */
	protected $consumerCred;

	/**
	 * Consumer object for accessToken
	 * @var object
	 */
	protected $accessToken;

	/**
	 * Base URL of Twitter API
	 * @var string
	 */
	protected $apiBase = "https://api.twitter.com";

	/**
	 * Version of Twitter API to target
	 * @var string
	 */
	protected $apiVersion = "1.1";

	/**
	 * Constructor
	 * @param array $consumer    	Consumer authentication. Array keys consist of:
	 *                            	"key"    => consumer key
	 *                            	"secret" => consumer secret
	 * @param array $accessToken    Access token authentication. Array keys consist of:
	 *                              "key"    => access token
	 *                              "secret" => access token secret
	 * @param [type] $httpEngine [description]
	 */
	public function __construct($consumer, $accessToken, \HttpExchange\Interfaces\ClientInterface $httpEngine = null)
	{
		$this->consumerCred = new OAuth\Consumer($consumer);
		$this->accessToken = new OAuth\Token($accessToken);
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
		return $this->oAuthRequest($url, "GET", $params);
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

		$request = new OAuth\Request($this->consumerCred, $this->accessToken, $method, $url, $params);

		$request->sign($this->consumerCred, $this->accessToken);

		if ($this->httpEngine) {
			return $this->httpCall($request);
		} else {
			return $this->curlCall($request);
		}
		
	}

	/**
	 * Trim the URL to be only the endpoint.
	 * 
	 * @param  string $url API URL
	 * @return string Endpoint
	 */
	protected function getRequestEndpoint($url)
	{
		$urlParts = parse_url($url);
		$path = trim($urlParts["path"], "/");
		return preg_replace("/\.[A-Za-z0-9]{3,4}$/", "", $path);
	}

	protected function buildRequestUrl($url)
	{
		$endpoint = $this->getRequestEndpoint($url);
		return "{$this->apiBase}/{$this->apiVersion}/{$endpoint}.json";	
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

		return json_decode($response);
	}

}