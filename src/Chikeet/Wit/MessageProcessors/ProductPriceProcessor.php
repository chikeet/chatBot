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

class ProductPriceProcessor implements IWitMessageProcessor
{
	public const WIT_ENTITY_TYPE = 'product_price';
	
	private const MESSENGER_RESPONSE_ERROR_UNKNOWN_PRODUCT = 'We do not sell this kind of products. But you can try some vegetables instead.';
	
	
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
		$productProcessor = new ProductProcessor;
		$isUnknownProduct = !$productProcessor->canUnderstand($witJsonObject);
		
		$items = $witJsonObject->entities->{self::WIT_ENTITY_TYPE};
		
		$responses = [];
		foreach($items as $item){
			$confidence = (float) $item->confidence;
			$confidenceIndex = ChatBot::getConfidenceIndex($confidence);
			$responses[$confidenceIndex] = $isUnknownProduct ? self::MESSENGER_RESPONSE_ERROR_UNKNOWN_PRODUCT : $item->value;
		}
		return $responses;
	}
	
	
	/**
	 * @param \stdClass $witJsonObject
	 * @return string|bool
	 */
	public function getBestConfidence(\stdClass $witJsonObject)
	{
		$responses = $this->getResponses($witJsonObject);
		$confidences = array_keys($responses);
		sort($confidences);
		return end($confidences);
	}
}