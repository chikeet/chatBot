<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 20.08.2018
 * Time: 13:20
 */

namespace Chikeet\ChatBot;


use Chikeet\Utils\JsonDecoder;
use Chikeet\Wit\Client;

class ChatBot
{
	/**
	 * @desc Wit AI data.
	 */
	private const WIT_ERROR_CODE_UNKNOWN = 'unknown';
	private const WIT_MESSAGE_MAX_LENGTH = 280;
	
	/**
	 * @desc Error messages
	 */
	public const ERROR_MESSAGE_MISSING_ADEQUATE_PROCESSOR = 'No adequate message processor was added to ChatBot.';
	public const ERROR_MESSAGE_NO_JSON_RETURNED_FROM_WIT = 'Wit AI returned no JSON. Check if your web domain is registered correctly in the Wit app.';
	
	/**
	 * @desc Error types
	 */
	public const ERROR_TYPE_NO_ERROR = 'No error';
	public const ERROR_TYPE_JSON_ERROR = 'JSON error';
	public const ERROR_TYPE_WIT_ERROR = 'Wit AI error';
	public const ERROR_TYPE_MISSING_ADEQUATE_PROCESSOR = 'Missing adequate processor';
	public const ERROR_TYPE_PROCESSING_ERROR = 'Processing error';
	
	/**
	 * @desc Default text of response for user.
	 */
	public const DEFAULT_USER_ERROR_RESPONSE = 'Sorry, this was too complicated for me. Please try to say it differently.';
	public const DEFAULT_USER_TEMPORARY_ERROR_RESPONSE = 'Sorry, I\'m not feeling well at the moment. My robot doctors are working on it. Please try again later.';
	public const DEFAULT_USER_NOT_UNDERSTAND_RESPONSE = 'Sorry, I can\'t understand. Please try to say it differently.';
	public const DEFAULT_USER_MESSAGE_TOO_LONG_RESPONSE = 'Sorry, this was too long for me. Please try to send a shorter message.';
	
	
	/**
	 * @var Client
	 */
	protected $witClient;
	
	/**
	 * @var string|null
	 */
	protected $lastError;
	
	/**
	 * @var string
	 */
	protected $lastErrorType = self::ERROR_TYPE_NO_ERROR;
	
	/**
	 * @var IWitMessageProcessor[]
	 */
	protected $messageProcessors;
	
	/**
	 * @var string|null
	 */
	protected $messengerMessage;
	
	/**
	 * @var string
	 */
	protected $userErrorResponse = self::DEFAULT_USER_ERROR_RESPONSE;
	
	/**
	 * @var string
	 */
	protected $userTemporaryErrorResponse = self::DEFAULT_USER_TEMPORARY_ERROR_RESPONSE;
	
	/**
	 * @var string
	 */
	protected $userMessageTooLongResponse = self::DEFAULT_USER_MESSAGE_TOO_LONG_RESPONSE;
	
	/**
	 * @var string
	 */
	protected $userNotUnderstandResponse = self::DEFAULT_USER_NOT_UNDERSTAND_RESPONSE;
	
	/**
	 * @var bool
	 */
	protected $isDebugMode = false;
	
	/**
	 * @var string
	 */
	protected $rawWitResponseJson;
	
	
	public function __construct(Client $witClient)
	{
		$this->witClient = $witClient;
	}
	
	
	/* Setup ******************************************************************/
	
	public function addMessageProcessor(IWitMessageProcessor $processor): void
	{
		$this->messageProcessors[] = $processor;
	}
	
	
	/**
	 * @param string $message A human-readable message that is returned to Messenger if an error occurs.
	 */
	public function setUserErrorResponse(string $message): void
	{
		$this->userErrorResponse = $message;
	}
	
	
	/**
	 * @param string $message A human-readable message that is returned to Messenger if the message is too long for Wit AI.
	 */
	public function setUserMessageTooLongResponse(string $message): void
	{
		$this->userMessageTooLongResponse = $message;
	}
	
	
	/**
	 * @param string $message A human-readable message that is returned to Messenger if a temporary error occurs.
	 */
	public function setUserTemporaryErrorResponse(string $message): void
	{
		$this->userTemporaryErrorResponse = $message;
	}
	
	
	/**
	 * @param string $message A human-readable message that is returned to Messenger if there is no adequate message processor.
	 */
	public function setUserNotUnderstandResponse(string $message): void
	{
		$this->userNotUnderstandResponse = $message;
	}
	
	
	/* Request processing *****************************************************/
	
	/**
	 * @desc Processes a message from Messenger and returns a human-readable text response.
	 * @param string $message
	 * @return string
	 */
	public function processMessage(string $message): string
	{
		$this->resetState();
		$this->messengerMessage = $message;
		$witResponse = $this->witClient->processRequest($message);
		return $this->processWitResponse($witResponse);
	}
	
	
	/* Errors *****************************************************************/
	
	public function hasError(): bool
	{
		return $this->lastErrorType !== self::ERROR_TYPE_NO_ERROR;
	}
	
	
	/**
	 * Returns human-readable error if any.
	 * @return string
	 */
	public function getLastError(): string
	{
		return (string) $this->lastError;
	}
	
	
	/**
	 * Returns human-readable error type.
	 * @return string
	 */
	public function getLastErrorType(): string
	{
		return $this->lastErrorType;
	}
	
	
	/**
	 * @desc Returns more detailed human-readable error description.
	 * @return string
	 */
	public function getLastErrorDescription(): string
	{
		return $this->lastErrorType
			. ($this->hasError() && strlen($this->lastError) ? ': ' . $this->lastError : '');
	}
	
	
	/* Debugging **************************************************************/
	
	/**
	 * Debug mode enables direct inspection of Wit response and ChatBot error messages.
	 * @param bool $value
	 */
	public function setDebugMode(bool $value): void
	{
		$this->isDebugMode = $value;
	}
	
	
	/**
	 * @return string
	 * @throws ChatBotNotInDebugModeException
	 */
	public function getWitResponseJson(): string
	{
		if(!$this->isDebugMode){
			throw new ChatBotNotInDebugModeException('Dumping raw Wit response JSON is allowed only in debug mode.');
		}
		return (string) $this->rawWitResponseJson;
	}
	
	
	/* Confidence index *******************************************************/
	
	/**
	 * Returns a confidence as a string padded by zeros from right side.
	 * Used to easily index and sort responses by confidence alphabetically.
	 * @param float $confidence
	 * @return string
	 */
	public static function getConfidenceIndex(float $confidence)
	{
		$rawIndex = (string) $confidence;
		$index = substr($rawIndex, 2); // remove '0.'
		$paddedIndex = str_pad($index, 20, '0', STR_PAD_RIGHT);
		return $paddedIndex;
	}
	
	/* Internal methods *******************************************************/
	
	/**
	 * @param string $witResponse
	 * @return string
	 */
	protected function processWitResponse(string $witResponse): string
	{
		/* check Wit AI response */
		if(!strlen($witResponse)){ // no JSON returned
			$this->lastError = self::ERROR_MESSAGE_NO_JSON_RETURNED_FROM_WIT;
			$this->lastErrorType = self::ERROR_TYPE_WIT_ERROR;
			
			return $this->userErrorResponse;
		}
		
		if($this->isDebugMode){
			$this->rawWitResponseJson = $witResponse;
		}
		
		/* check and decode Wit JSON */
		$jsonDecoder = new JsonDecoder;
		$jsonData = $jsonDecoder->decode($witResponse);
		
		if($jsonDecoder->hasError()){ // invalid JSON
			$this->lastError = $jsonDecoder->getLastErrorDescription() . " (JSON: '$witResponse')";
			$this->lastErrorType = self::ERROR_TYPE_JSON_ERROR;
			
			return $this->userErrorResponse;
		} elseif(property_exists($jsonData, 'error')) { // a Wit error returned in JSON
			$errorCode = (property_exists($jsonData, 'code') ? " (code: '" . $jsonData->code . "')" : '');
			$this->lastError = $jsonData->error . $errorCode;
			$this->lastErrorType = self::ERROR_TYPE_WIT_ERROR;
			
			return $this->getWitErrorResponse($jsonData);
		}
		
		/* valid JSON - try to process */
		$responses = [];
		foreach($this->messageProcessors as $messageProcessor){
			try {
				if($messageProcessor->canUnderstand($jsonData)){
					$responses = array_merge($responses, $messageProcessor->getResponses($jsonData));
				}
			} catch(WitMessageProcessorException $e) {
				$this->lastError = $e->getMessage();
				$this->lastErrorType = self::ERROR_TYPE_PROCESSING_ERROR;
			}
		}
		/* if there are any responses from processors, return a response with best confidence */
		if($responses){
			ksort($responses);
			return end($responses);
		}
		
		return $this->getErrorResponse();
	}
	
	
	protected function getErrorResponse(): string
	{
		/* unable to process - manage errors */
		if($this->lastErrorType === self::ERROR_TYPE_PROCESSING_ERROR){
			return $this->userErrorResponse;
		}
		
		$this->lastErrorType = self::ERROR_TYPE_MISSING_ADEQUATE_PROCESSOR;
		$this->lastError = self::ERROR_MESSAGE_MISSING_ADEQUATE_PROCESSOR;
		
		return $this->userNotUnderstandResponse;
	}
	
	
	protected function getWitErrorResponse(\stdClass $jsonData): string
	{
		if(strlen($this->messengerMessage) > self::WIT_MESSAGE_MAX_LENGTH){
			return $this->userMessageTooLongResponse; // user message is too long for Wit to process
		}
		
		if(property_exists($jsonData, 'code') && $jsonData->code === self::WIT_ERROR_CODE_UNKNOWN){
			return $this->userTemporaryErrorResponse; // unknown Wit error - a temporary problem on Wit side
		}
		
		return $this->userErrorResponse; // common Wit error
	}
	
	
	protected function resetState(): void
	{
		$this->lastError = null;
		$this->lastErrorType = null;
	}
	
}