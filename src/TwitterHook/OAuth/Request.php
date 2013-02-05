<?php

namespace TwitterHook\OAuth;

class Request
{
	protected $httpMethod = "GET";
	protected $httpUrl;

	protected $oAuthRequestHeader = array();
	protected $requestParams = array();



	public function __construct($clientCred, $tokenCred, $method, $url, $params = array())
	{
		$this->oAuthRequestHeader = array(
			"oauth_version" => "1.0",
			"oauth_nonce" => $this->createNonce(),
			"oauth_timestamp" => time(),
			"oauth_consumer_key" => $clientCred->key,
			"oauth_token" => $tokenCred->key
		);

		$params = array_merge(Utility::parse_params(parse_url($url, PHP_URL_QUERY)), $params);
		
		$this->requestParams = $params;
		$this->httpMethod = $method;
		$this->httpUrl = $url;

	}

	protected function createNonce()
	{
		$mt = microtime();
		$rand = mt_rand();

		return md5($mt . $rand);

	    return hash("sha512", $this->makeRandomString() . time());
	}

	/**
	 * Create a random string to append to the nonce
	 * From http://stackoverflow.com/questions/4145531/how-to-create-and-use-nonces
	 * 
	 * @param  integer $bits
	 * @return random string
	 */
	protected function makeRandomString($bits = 256) {
	    $bytes = ceil($bits / 8);
	    $return = "";
	    for ($i = 0; $i < $bytes; $i++) {
	        $return .= chr(mt_rand(0, 255));
	    }

	    return $return;
	}

	public function sign($consumer, $token)
	{
		$this->setOAuthHeader("oauth_signature_method", "HMAC-SHA1");
		$signature = new \TwitterHook\OAuth\Signature($this, $consumer, $token);
		$this->setOAuthHeader("oauth_signature", $signature->get(), false);
	}

	public function setParam($key, $value) {

		if (!isset($this->requestParams[$key])) {
			$this->requestParams[$key] = $value;
		} else if (!isset($this->requestParams[$key]) && is_array($this->requestParams[$key])) {
			$this->requestParams[$key][] = $value;
		}
	}

	public function setOAuthHeader($key, $value) {

		if (!isset($this->oAuthRequestHeader[$key])) {
			$this->oAuthRequestHeader[$key] = $value;
		} else if (!isset($this->oAuthRequestHeader[$key]) && is_array($this->oAuthRequestHeader[$key])) {
			$this->oAuthRequestHeader[$key][] = $value;
		}
	}

	public function getSignatureBaseString()
	{
		$parts = array(
			strtoupper($this->httpMethod),
			$this->httpUrl,
			$this->getSignableParams()
		);

		$parts = Utility::urlencode_rfc3986($parts);
		return implode('&', $parts);
	}

	public function getSignableParams()
	{
		// Grab all params
		$params = $this->oAuthRequestHeader + $this->requestParams;

		// Remove oauth_signature if present
		// Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
		if (isset($params['oauth_signature'])) {
			unset($params['oauth_signature']);
		}

		return Utility::build_http_query($params);
	}

	public function compileOAuthHeader()
	{
		uksort($this->oAuthRequestHeader, "strcmp");

		$compiled = array();
		foreach ($this->oAuthRequestHeader as $k => $v) {
			$k = Utility::urlencode_rfc3986($k);
			$v = Utility::urlencode_rfc3986($v);
			$compiled[] = "{$k}=\"{$v}\"";
		}
		return "OAuth " . implode(", ", $compiled);
	}

	public function compileUrlWithParams()
	{
		$params = Utility::build_http_query($this->requestParams);
		return "{$this->httpUrl}?{$params}";
	}

	public function to_url()
	{
		$post_data = $this->to_postdata();
		$out = $this->httpUrl;
		if ($post_data) {
			$out .= '?'.$post_data;
		}
		return $out;
	}

	/**
	* builds the data one would send in a POST request
	*/
	public function to_postdata()
	{
		return Utility::build_http_query($this->requestParams);
	}

	public function __get($prop) {
		return $this->$prop;
	}
}