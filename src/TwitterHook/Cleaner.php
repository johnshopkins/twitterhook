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
    $tweet = $this->entities($tweet, $maxUrlLength);
		return $tweet;
	}

  /**
   * Links user mentions, hashtags and links
   * in a tweet and shortend URLs if requested.
   *
   * Adapted from: http://stackoverflow.com/a/15306910
   *
   * @param  object  $tweet        Tweet object
   * @param  integer $maxUrlLength [description]
   * @return [type]               [description]
   */
  function entities($tweet, $maxUrlLength) {

    // convert tweet text to array of one-character strings
    $chars = preg_split("//u", $tweet->text, null, PREG_SPLIT_NO_EMPTY);

    $chars = $this->convertEntityToLink("hashtags", $tweet->entities, $chars);
    $chars = $this->convertEntityToLink("user_mentions", $tweet->entities, $chars);
    $chars = $this->convertEntityToLink("urls", $tweet->entities, $chars, $maxUrlLength);

    // convert array back to string
    $tweet->text = implode("", $chars);

    return $tweet;

  }

  protected function convertEntityToLink($type, $entities, $chars, $max = null)
  {
    if (!isset($entities->$type) || !is_array($entities->$type)) return;
    
    foreach ($entities->$type as $entity) {

      $link = $this->getLink($type, $entity, $max);
      $displayText = $chars[$entity->indices[0]];

      $firstIndex = $entity->indices[0];
      $lastIndex = $entity->indices[1];

      if ($type == "urls" && $max) {

        // created shortened URL
        $displayText = $this->shortenUrl($entity->url, $max);

        // add full link to first index
        $chars[$firstIndex] = "<a href=\"$link\">{$displayText}</a>";

        // remove characters from the rest of the indecies

        $from = $firstIndex + 1;
        $to = $lastIndex - 1;

        for ($i = $from; $i <= $to; $i++) {
          $chars[$i] = "";
        }

      } else {

        // add opening <a> to the first index. will look something like this: <a href="http://...">t
        $chars[$firstIndex] = "<a href=\"$link\">" . $displayText;

        // the rest of the link display text is here...

        // add closing <a> to the last index. will look something like this: t</a>
        $chars[$lastIndex - 1] .= "</a>";

      }

    }

    return $chars;
  }

  protected function getLink($type, $entity, $max = null)
  {
    if ($type == "hashtags") {
      return "https://twitter.com/search?q=%23" . $entity->text;
    } else if ($type == "user_mentions") {
      return "https://twitter.com/" . $entity->screen_name;
    } else if ($type == "urls") {
      return $entity->expanded_url;
    }
  }

  /**
   * Shorten a URL to given character length
   * @param  string  $url URL
   * @param  integer $max Maximum character length
   * @return string       Shortened URL
   */
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

}
