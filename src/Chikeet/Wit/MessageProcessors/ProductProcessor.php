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

class ProductProcessor implements IWitMessageProcessor
{
	public const WIT_PRICE_ENTITY_TYPE = 'product_price';
	public const WIT_ENTITY_TYPE = 'product';
	
	/**
	 * Possible Messenger responses.
	 */
	private const MESSENGER_RESPONSE_SUCCESS = '%s costs %d USD.';
	private const MESSENGER_RESPONSE_ERROR_UNKNOWN_PRODUCT = 'Sorry, we have no %s in stock. Try some vegetables instead.';
	
	/**
	 * Products demo data.
	 */
	private const PRODUCTS_DATA = [
		'cauliflower' => 5,
		'onion' => 3,
		'carrot' => 7,
		'cucumber' => 4,
		'tomato' => 8,
		'garlic' => 6,
		'salad' => 9,
	];
	
	
	/**
	 * @param \stdClass $witJsonObject
	 * @return bool
	 */
	public function canUnderstand(\stdClass $witJsonObject): bool
	{
		return property_exists($witJsonObject, 'entities')
			&& array_key_exists(self::WIT_ENTITY_TYPE, $witJsonObject->entities)
			&& array_key_exists(self::WIT_PRICE_ENTITY_TYPE, $witJsonObject->entities);
	}
	
	
	/**
	 * @param \stdClass $witJsonObject
	 * @return string[]
	 */
	public function getResponses(\stdClass $witJsonObject): array
	{
		$productPriceProcessor = new ProductPriceProcessor;
		$bestPriceConfidence =  $productPriceProcessor->getBestConfidence($witJsonObject);
		
		$items = $witJsonObject->entities->{self::WIT_ENTITY_TYPE};
		
		$responses = [];
		foreach($items as $item){
			$confidence = (float) ($item->confidence + $bestPriceConfidence) / 2;
			$confidenceIndex = ChatBot::getConfidenceIndex($confidence);
			$productName = $item->value;
			if(array_key_exists($productName, self::PRODUCTS_DATA)){
				$responses[$confidenceIndex] = sprintf(self::MESSENGER_RESPONSE_SUCCESS, $productName, self::PRODUCTS_DATA[$productName]);
			} else {
				$responses[$confidenceIndex] = sprintf(self::MESSENGER_RESPONSE_ERROR_UNKNOWN_PRODUCT, $productName);
			}
		}
		return $responses;
	}
}