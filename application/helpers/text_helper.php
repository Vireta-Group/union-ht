<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Text helpers
 *
 * PHP 8 turned several silent string operations into thrown Errors. The most
 * damaging one for this app was:
 *
 *     substr($text, 0, strpos($text, ' ', 1000))
 *
 * In PHP 7 strpos() returned FALSE with a warning when offset 1000 was past
 * the end of the string. In PHP 8 it throws
 *     ValueError: strpos(): Argument #3 ($offset) must be contained in
 *     argument #1 ($haystack)
 * which is an uncaught fatal. That meant ANY tenant whose text was shorter
 * than the offset — e.g. a new union parishad with a blank chairman's
 * message — got a 500 on the home page. Use excerpt() instead.
 */

if ( ! function_exists('excerpt'))
{
	/**
	 * First $limit characters of $text, trimmed back to a word boundary and
	 * ellipsised. Never throws, whatever $text is.
	 *
	 * @param  mixed  $text
	 * @param  int    $limit
	 * @param  string $ellipsis
	 * @return string
	 */
	function excerpt($text, $limit = 1000, $ellipsis = '...')
	{
		if (is_array($text) OR is_object($text) OR is_bool($text) OR $text === NULL)
		{
			$text = '';
		}
		$text = (string) $text;

		// collapse whitespace so truncation never lands mid-tag or mid-gap
		$text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)));
		if ($text === '')
		{
			return '';
		}

		$limit = (int) $limit;
		if ($limit < 1 OR mb_strlen($text, 'UTF-8') <= $limit)
		{
			return $text;
		}

		$cut = mb_substr($text, 0, $limit, 'UTF-8');

		// back off to the last space so we do not split a word
		$space = mb_strrpos($cut, ' ', 0, 'UTF-8');
		if ($space !== FALSE && $space > 0)
		{
			$cut = mb_substr($cut, 0, $space, 'UTF-8');
		}

		return rtrim($cut) . $ellipsis;
	}
}

if ( ! function_exists('excerpt_words'))
{
	/**
	 * First $words words of $text. Never throws.
	 *
	 * @param  mixed $text
	 * @param  int   $words
	 * @return string
	 */
	function excerpt_words($text, $words = 60)
	{
		if (is_array($text) OR is_object($text) OR is_bool($text) OR $text === NULL)
		{
			$text = '';
		}
		$parts = preg_split('/\s+/u', trim(strip_tags((string) $text)), -1, PREG_SPLIT_NO_EMPTY);
		if ($parts === FALSE OR empty($parts))
		{
			return '';
		}
		if (count($parts) <= $words)
		{
			return implode(' ', $parts);
		}
		return implode(' ', array_slice($parts, 0, $words)) . '...';
	}
}

if ( ! function_exists('en2bn'))
{
	/**
	 * Convert English numbers/digits to Bengali digits.
	 *
	 * @param  mixed $number
	 * @return string
	 */
	function en2bn($number)
	{
		$en = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$bn = array('০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯');
		return str_replace($en, $bn, (string)$number);
	}
}

