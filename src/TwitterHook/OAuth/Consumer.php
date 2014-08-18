<?php

namespace TwitterHook\OAuth;

class Consumer
{
  /**
   * Consumer key
   * @var string
   */
  protected $key;

  /**
   * Consumer Secret
   * @var string
   */
  protected $secret;

  public function __construct($consumer)
  {
    $this->key = is_object($consumer) ? $consumer->key : $consumer["key"];
    $this->secret = is_object($consumer) ? $consumer->secret : $consumer["secret"];
  }

  public function __get($prop) {
    return $this->$prop;
  }
}
