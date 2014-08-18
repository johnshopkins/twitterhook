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
    $this->token = is_object($accessToken) ? $accessToken->token : $accessToken["token"];
    $this->secret = is_object($accessToken) ? $accessToken->secret : $accessToken["secret"];
  }

  public function __get($prop) {
    return $this->$prop;
  }
}
