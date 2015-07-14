<?php

namespace TwitterHook;

class Cleaner
{
  /**
   * Transform text URLs, mentions, and hastags into links.
   * Converts tweet text to UTF-8.
   * @param string  $text Tweet text
   * @param integer $maxUrlLength Maximum number of characters a link can be
   */
  public function cleanText($tweet, $maxUrlLength = null)
	{
		$tweet = $this->utf8Text($tweet);
		$tweet = $this->link($tweet, $maxUrlLength);
		$tweet = $this->hashTag($tweet);
		$tweet = $this->mention($tweet);

		return $tweet;
	}

  protected function utf8Text($tweet)
  {
    $tweet->text = htmlentities($tweet->text, ENT_QUOTES, "utf-8", FALSE);

    return $tweet;
  }

  protected function shortenUrl($url, $max)
  {
    // remove http:// or https://
    $url = preg_replace("/(http|https):\/\//", "", $url);

    // shorten, if necessary
    if ($max) {
      $max = $max - 3; // account for ...
      $url = substr($url, 0, $max) . "...";
    }

    return $url;

  }

	protected function link($tweet, $maxLength)
	{
    foreach ($tweet->entities->urls as $url) {

      $short_url = $this->shortenUrl($url->url, $maxLength);
      $replacement = "<a href='{$url->expanded_url}' title='{$url->expanded_url}'>{$short_url}</a>";

      $tweet->text = substr_replace($tweet->text, $replacement, $url->indices[0], $url->indices[1]);

    }

    return $tweet;
	}

	protected function mention($tweet)
	{
    $tweet->text = preg_replace("/(^|[^\w]+)\@([a-zA-Z0-9_]{1,15}(\/[a-zA-Z0-9-_]+)*)/", "$1<a target='_newtab' href='http://twitter.com/$2'>@$2</a>", $tweet->text);
    return $tweet;
  }

	protected function hashTag($tweet)
	{
    $tweet->text = preg_replace("/(^|[^&\w'\"]+)\#([a-zA-Z0-9_^\"^<]+)/", "$1<a target='_newtab' href='http://twitter.com/search?q=%23$2'>#$2</a>", $tweet->text);
    return $tweet;
  }

}
