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

class GreetingsProcessor implements IWitMessageProcessor
{
	public const WIT_ENTITY_TYPE = 'greetings';
	
	/**
	 * Possible Messenger responses.
	 */
	private const MESSENGER_RESPONSE_SUCCESS = 'Hello!';
	
	
	/**
	 * @param \stdClass $witJsonObject
	 * @return bool
	 */
	public function canUnderstand(\stdClass $witJsonObject): bool
	{
		return property_exists($witJsonObject, 'entities')
			&& array_key_exists(self::WIT_ENTITY_TYPE, $witJsonObject->entities);
	}
	
	
	/**
	 * @param \stdClass $witJsonObject
	 * @return string[]
	 */
	public function getResponses(\stdClass $witJsonObject): array
	{
		$items = $witJsonObject->entities->{self::WIT_ENTITY_TYPE};
		
		$responses = [];
		foreach($items as $item){
			$confidence = (float) $item->confidence;
			$confidenceIndex = ChatBot::getConfidenceIndex($confidence);
			$responses[$confidenceIndex] = self::MESSENGER_RESPONSE_SUCCESS;
		}
		return $responses;
	}
}