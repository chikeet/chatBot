<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 19.08.2018
 * Time: 19:57
 */

namespace Chikeet\Utils;


class JsonDecoder
{
	protected const JSON_ERROR_NAMES = [
		JSON_ERROR_NONE	=> 'No error has occurred.',
		JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded.',
		JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON.',
		JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded.',
		JSON_ERROR_SYNTAX => 'Syntax error.',
		JSON_ERROR_UTF8	=> 'Malformed UTF-8 characters, possibly incorrectly encoded.',
		JSON_ERROR_RECURSION => 'One or more recursive references in the value to be encoded.',
		JSON_ERROR_INF_OR_NAN => 'One or more NAN or INF values in the value to be encoded.',
		JSON_ERROR_UNSUPPORTED_TYPE	 => 'A value of a type that cannot be encoded was given.',
		JSON_ERROR_INVALID_PROPERTY_NAME => 'A property name that cannot be encoded was given.',
		JSON_ERROR_UTF16 => 'Malformed UTF-16 characters, possibly incorrectly encoded.',
	];
	
	/**
	 * @var int
	 */
	protected $lastError = JSON_ERROR_NONE;
	
	
	/**
	 * @param string $rawJson
	 * @return mixed
	 */
	public function decode(string $rawJson)
	{
		$jsonData = json_decode($rawJson);
		
		$jsonError = json_last_error();
		$this->lastError = $jsonError;
		
		return $jsonData;
	}
	
	
	public function hasError(): bool
	{
		return $this->lastError !== JSON_ERROR_NONE;
	}
	
	
	public function getLastError(): int
	{
		return $this->lastError;
	}
	
	
	public function getLastErrorDescription(): string
	{
		return self::JSON_ERROR_NAMES[$this->lastError];
	}
	
}