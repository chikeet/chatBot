<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 20.08.2018
 * Time: 13:20
 */

namespace Chikeet\ChatBot;

/**
 * Interface IWitMessageProcessor
 * @package Chikeet\ChatBot
 *
 * @desc Message processor to process a single type of response from Wit AI (https://wit.ai/).
 */
interface IWitMessageProcessor
{
	
	/**
	 * @desc Should return true if message processor is able to understand and process the wit response in $witJsonContent.
	 * @param \stdClass $witJsonObject
	 * @return bool
	 */
	public function canUnderstand(\stdClass $witJsonObject): bool;
	
	
	/**
	 * @desc Should return an array of text responses for a Facebook user.
	 * @desc The array should be indexed by a confidenceIndex retrieved from \Chikeet\ChatBot\ChatBot::getConfidenceIndex method.
	 * @param \stdClass $witJsonObject
	 * @return string[]
	 */
	public function getResponses(\stdClass $witJsonObject): array;
	
}