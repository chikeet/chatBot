<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 20.08.2018
 * Time: 7:37
 */

namespace Chikeet\Utils;

/**
 * Class Url
 * @package Chikeet\Utils
 *
 * Utils for work with URL.
 */
class Url
{
	
	
	
	public static function isHttps(string $url): bool
	{
		return strpos($url, 'https://') === 0;
	}
	
	
	public static function constructUrl(string $url, array $queryParameters)
	{
		$query = Url::prepareQueryString($queryParameters);
		$queryDelimiter = Url::getQueryDelimiter($url, $query);
		
		return $url . $queryDelimiter . $query;
	}
	
	
	/**
	 * Returns a query string part of an URL.
	 * @param array $parameters
	 * @return string
	 */
	public static function prepareQueryString(array $parameters): string
	{
		$formattedParameters = [];
		foreach($parameters as $key => $value){
			$formattedParameters[] = $key . '=' . $value;
		}
		
		return implode('&', $formattedParameters);
	}
	
	
	/**
	 * Returns an delimiter for an URL and a query string.
	 * @param string $url
	 * @param string $query
	 * @return string
	 */
	public static function getQueryDelimiter(string $url, string $query): string
	{
		if(!strlen($url) || (!strlen($query))){// empty url or query
			return '';
		}
		
		$possibleDelimiters = ['?', '&'];
		$lastUrlCharacter = $url[-1]; // url already ending with delimiter
		$firstQueryCharacter = $query[0]; // query already starting with delimiter
		if(in_array($lastUrlCharacter, $possibleDelimiters) || in_array($firstQueryCharacter, $possibleDelimiters)){
			return '';
		}
		
		return strpos($url, '?') ? '&' : '?';
	}
	
}