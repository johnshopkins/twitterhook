<?php

namespace TwitterHook\OAuth;

class Utility
{
	public static function urlencode_rfc3986($input)
	{
		if (is_array($input)) {
			return array_map(array("TwitterHook\\OAuth\\Utility", 'urlencode_rfc3986'), $input);
		} else if (is_scalar($input)) {
			return str_replace('+', ' ', str_replace('%7E', '~', rawurlencode($input)));
		} else {
			return '';
		}
	}

	public static function build_http_query($params = array())
	{
		if (empty($params)) {
			return "";
		} 

		// Urlencode both keys and values
		$keys = Utility::urlencode_rfc3986(array_keys($params));
		$values = Utility::urlencode_rfc3986(array_values($params));
		$params = array_combine($keys, $values);

		// params are sorted by name, using lexicographical byte value ordering.
		// Ref: Spec: 9.1.1 (1)
		uksort($params, 'strcmp');

		$pairs = array();
		foreach ($params as $parameter => $value) {
			if (is_array($value)) {
				// If two or more params share the same name, they are sorted by their value
				// Ref: Spec: 9.1.1 (1)
				natsort($value);
				foreach ($value as $duplicate_value) {
					$pairs[] = $parameter . '=' . $duplicate_value;
				}
			} else {
				$pairs[] = $parameter . '=' . $value;
			}
		}
		// For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
		// Each name-value pair is separated by an '&' character (ASCII code 38)
		return implode('&', $pairs);
	}

	public static function parse_params($input)
	{
		if (!isset($input) || !$input) return array();

			$pairs = explode('&', $input);

			$parsed_params = array();
			foreach ($pairs as $pair) {
			$split = explode('=', $pair, 2);
			$parameter = Utility::urldecode_rfc3986($split[0]);
			$value = isset($split[1]) ? Utility::urldecode_rfc3986($split[1]) : '';

			if (isset($parsed_params[$parameter])) {
			// We have already recieved parameter(s) with this name, so add to the list
			// of params with this name

				if (is_scalar($parsed_params[$parameter])) {
					// This is the first duplicate, so transform scalar (string) into an array
					// so we can add the duplicates
					$parsed_params[$parameter] = array($parsed_params[$parameter]);
				}

				$parsed_params[$parameter][] = $value;
			} else {
				$parsed_params[$parameter] = $value;
			}
		}
		return $parsed_params;
	}
}