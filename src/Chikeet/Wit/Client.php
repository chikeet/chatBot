<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 20.08.2018
 * Time: 4:44
 */

namespace Chikeet\Wit;

use Chikeet\ChatBot\IWitClient;
use Chikeet\Utils\Url;

/**
 * Class Client
 * @package Chikeet\Wit
 *
 * Client for communication with Wit AI (https://wit.ai/).
 */
class Client implements IWitClient
{
	
	protected const WIT_URL = 'https://api.wit.ai/message';
	
	protected const WIT_VERSION = '20180820';
	
	
	/**
	 * @var string
	 */
	protected $witToken;
	
	/**
	 * @var string
	 */
	protected $witUrl;
	
	/**
	 * @var string
	 */
	protected $witVersion;
	
	
	/**
	 * @param string $witToken
	 * @param string $witVersion Another values than default should be used for testing only.
	 * @param string $witUrl Another values than default should be used for testing only.
	 */
	public function __construct(string $witToken, string $witVersion = self::WIT_VERSION, string $witUrl = self::WIT_URL)
	{
		$this->witToken = $witToken;
		$this->witUrl = $witUrl;
		$this->witVersion = $witVersion;
	}
	
	
	public function processRequest(string $message): string
	{
		/* init curl */
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->prepareUrl($message));
		
		$isHttps = Url::isHttps($this->witUrl);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $isHttps);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($curl, CURLOPT_HTTPHEADER, $this->prepareHeaders()); // no-auth error when missing
		curl_setopt($curl, CURLOPT_HEADER, $this->prepareHeaders()); // empty JSON returned when missing
		
		/* get curl response */
		$response = curl_exec($curl);
		$curlInfo = curl_getinfo($curl);
		
		curl_close($curl);
		
		/* parse curl response */
		$headerSize = $curlInfo['header_size'];
		$responseBody = substr($response, $headerSize);
		
		return $responseBody;
	}
	
	
	protected function prepareHeaders(): array
	{
		return [
			'Authorization: Bearer ' . $this->witToken,
			'Content-type: application/json',
			'Accept: application/json',
		];
	}
	
	
	protected function prepareUrl(string $message): string
	{
		$queryParameters = $this->prepareQueryParameters($message);
		return Url::constructUrl($this->witUrl, $queryParameters);
	}
	
	
	protected function prepareQueryParameters(string $message): array
	{
		return [
			'v' => $this->witVersion,
			'q' => urlencode($message),
		];
	}
	
}