<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 22.08.2018
 * Time: 4:54
 */

namespace Messenger;


use Chikeet\ChatBot\ChatBot;
use Chikeet\Messenger\WebHookStatusCodeNotSetException;
use Chikeet\Utils\JsonDecoder;

class WebHook
{
	/**
	 * @desc Possible actions.
	 */
	public const ACTION_PROCESS_MESSAGE = 'process message';
	public const ACTION_VERIFY_TOKEN = 'verify token';
	
	/**
	 * @desc Possible HTTP status codes.
	 */
	const HTTP_STATUS_CODE_200_OK = 200;
	const HTTP_STATUS_CODE_403_FORBIDDEN = 403;
	const HTTP_STATUS_CODE_404_NOT_FOUND = 403;
	
	/**
	 * @var string
	 */
	protected $accessToken;
	
	/**
	 * @var ChatBot
	 */
	protected $chatBot;
	
	/**
	 * @var string|null
	 */
	protected $errorLogPath;
	
	/**
	 * @var int|null
	 */
	protected $statusCode;
	
	/**
	 * @var string|null
	 */
	protected $response;
	
	/**
	 * @var string|null
	 */
	protected $action;
	
	
	public function __construct(string $accessToken, ChatBot $chatBot, string $errorLogPath)
	{
		$this->accessToken = $accessToken;
		$this->chatBot = $chatBot;
		$this->errorLogPath = $errorLogPath;
	}
	
	
	/**
	 * @desc Processes the HTTP request. Returns resulting HTTP status code.
	 * @param array $getParameters
	 * @param string $postBody
	 * @return int
	 */
	public function processRequest(array $getParameters, string $postBody): int
	{
		if(isset($getParameters['hub_verify_token'])){ // is a token verification request
			$this->action = self::ACTION_VERIFY_TOKEN;
			$this->statusCode = $this->processTokenVerification($getParameters);
		} elseif(strlen($postBody) > 0) { // is a POST request
			$jsonDecoder = new JsonDecoder;
			$jsonData = $jsonDecoder->decode($postBody);
			
			if($jsonDecoder->hasError()){
				$this->statusCode = self::HTTP_STATUS_CODE_403_FORBIDDEN;
				$this->logOwnError('JSON error: ' . $jsonDecoder->getLastErrorDescription() . " (JSON: '$postBody')");
			} elseif(property_exists($jsonData, 'object') && $jsonData->object === 'page') { // is a page request
				$this->action = self::ACTION_PROCESS_MESSAGE;
				$this->statusCode = $this->processMessage($jsonData);
			} else {
				$this->statusCode = self::HTTP_STATUS_CODE_404_NOT_FOUND; // returns a '404 Not Found' if event is not from a page subscription
				$this->logOwnError("POST request with unsupported parameters. (POST body: '$postBody')");
			}
		} else {
			$this->statusCode = self::HTTP_STATUS_CODE_403_FORBIDDEN; // returns a '403 Forbidden' for not implemented requests
			$this->logOwnError("Trying to run a not implemented request. " .
				"(GET parameters: '" . json_encode($getParameters) . "', POST body: '$postBody')");
		}
		
		return $this->statusCode;
	}
	
	
	/**
	 * @desc A valid int must be returned, otherwise an exception is thrown.
	 * @return int
	 * @throws WebHookStatusCodeNotSetException
	 */
	public function getStatusCode(): int
	{
		if(!isset($this->statusCode)){
			throw new WebHookStatusCodeNotSetException('WebHook status code is not yet set. Call '
				. __CLASS__ . '::processRequest method first.');
		}
		return $this->statusCode;
	}
	
	
	/**
	 * @desc Response may be an empty string.
	 * @return string
	 */
	public function getResponse(): string
	{
		return (string) $this->response;
	}
	
	
	/**
	 * @desc Action may be an empty string.
	 * @return string
	 */
	public function getAction(): string
	{
		return (string) $this->action;
	}
	
	
	/**
	 * Returns true if current statusCode is HTTP status 200 OK.
	 * @return bool
	 * @throws WebHookStatusCodeNotSetException
	 */
	public function isOk(): bool
	{
		return $this->getStatusCode() !== self::HTTP_STATUS_CODE_200_OK;
	}
	
	
	/**
	 * @param array $getParameters
	 * @return int
	 */
	protected function processTokenVerification(array $getParameters): int
	{
		$token = $getParameters['hub_verify_token']; // hub.verify_token in URL
		$mode = isset($getParameters['hub_mode']) ? $getParameters['hub_mode'] : NULL; // hub.mode in URL
		$challenge = isset($getParameters['hub_challenge']) ? $getParameters['hub_challenge'] : NULL; // hub.challenge in URL
		
		if($challenge && $mode === 'subscribe' && $token === $this->accessToken){
			$this->response = $getParameters['hub_challenge']; // verified - responds with the challenge token from the request
			return self::HTTP_STATUS_CODE_200_OK;
		} else {
			$this->logOwnError('Invalid or missing challenge, token or mode.');
			return self::HTTP_STATUS_CODE_403_FORBIDDEN; // invalid - responds with '403 Forbidden'
		}
	}
	
	
	protected function processMessage(\stdClass $jsonData): int
	{
		if(!property_exists($jsonData, 'entry') || !is_array($jsonData->entry)){
			return self::HTTP_STATUS_CODE_200_OK;
		}
		foreach($jsonData->entry as $item){
			if(!property_exists($item, 'messaging') || !is_array($item->messaging)){
				continue;
			}
			$messages = $item->messaging;
			$message = reset($messages); // there is always only one message in item->messaging
			if(!$message || !property_exists($message, 'message')){
				continue;
			};
			$this->response .= $this->chatBot->processMessage($message->message);
			
			if($this->chatBot->hasError()){
				$this->logError('ChatBot - ' . $this->chatBot->getLastErrorDescription());
			}
		}
		return self::HTTP_STATUS_CODE_200_OK;
	}
	
	
	protected function logError(string $message): void
	{
		\Chikeet\Utils\Logger::logError($message, $this->errorLogPath);
	}
	
	
	protected function logOwnError(string $message): void
	{
		$action = $this->action ? '/' . $this->action : '';
		\Chikeet\Utils\Logger::logError('WebHook' . $action . ' - ' . $message, $this->errorLogPath);
	}
}