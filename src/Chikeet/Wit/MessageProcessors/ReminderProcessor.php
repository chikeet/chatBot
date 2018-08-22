<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 19.08.2018
 * Time: 22:02
 */

namespace Chikeet\Wit\MessageProcessors;

use Chikeet\ChatBot\ChatBot;
use Chikeet\ChatBot\IWitMessageProcessor;

class ReminderProcessor implements IWitMessageProcessor
{
	public const WIT_ENTITY_TYPE = 'reminder';
	
	/**
	 * Possible Messenger responses.
	 */
	private const MESSENGER_RESPONSE_SUCCESS = 'I am setting a reminder for "%s" to %s';
	
	
	/**
	 * @param \stdClass $witJsonObject
	 * @return bool
	 */
	public function canUnderstand(\stdClass $witJsonObject): bool
	{
		$dateTimeProcessor = new DateTimeProcessor;
		if(!$dateTimeProcessor->canUnderstand($witJsonObject)){
			return false;
		}
		$dateTime = $dateTimeProcessor->getBestResponse($witJsonObject);
		
		return property_exists($witJsonObject, 'entities')
			&& array_key_exists(self::WIT_ENTITY_TYPE, $witJsonObject->entities)
			&& is_string($dateTime);
	}
	
	
	/**
	 * @param \stdClass $witJsonObject
	 * @return string[]
	 */
	public function getResponses(\stdClass $witJsonObject): array
	{
		$dateTimeProcessor = new DateTimeProcessor;
		$dateTime = $dateTimeProcessor->getBestResponse($witJsonObject); // one datetime is used for all reminder values
		
		$items = $witJsonObject->entities->{self::WIT_ENTITY_TYPE};
		
		$responses = [];
		foreach($items as $item){
			$confidence = (float) $item->confidence;
			$confidenceIndex = ChatBot::getConfidenceIndex($confidence);
			$responses[$confidenceIndex] = sprintf(self::MESSENGER_RESPONSE_SUCCESS, $item->value, $dateTime);
		}
		return $responses;
	}
	
}