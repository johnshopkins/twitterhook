<?php

namespace TwitterHook;

class Cleaner
{
  /**
   * Transform text URLs, mentions,
   * and hastags into links.
   * @param string $text Tweet text
   */
  public function cleanText($text)
	{
		$text = $this->link($text);
		$text = $this->hashTag($text);
		$text = $this->mention($text);

		return $text;
	}

	protected function link($text)
	{
		return preg_replace_callback("/[a-z]+:\/\/([a-z0-9-_]+\.[a-z0-9-_:~\+#%&\?\/.=]+[^:\.,\)\s*$])/i", function($matches) {
			$displayURL = strlen($matches[0]) > 36 ? substr($matches[0], 0, 35) . "&hellip;" : $matches[0];
			return "<a target='_newtab' href='$matches[0]'>$displayURL</a>";
		}, $text);
	}

	protected function mention($text)
	{
		return preg_replace("/(^|[^\w]+)\@([a-zA-Z0-9_]{1,15}(\/[a-zA-Z0-9-_]+)*)/", "$1<a target='_newtab' href='http://twitter.com/$2'>@$2</a>", $text);
	}

	protected function hashTag($text)
	{
		return preg_replace("/(^|[^&\w'\"]+)\#([a-zA-Z0-9_^\"^<]+)/", "$1<a target='_newtab' href='http://search.twitter.com/search?q=%23$2'>#$2</a>", $text);
	}

}
