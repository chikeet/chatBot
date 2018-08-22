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

class DateTimeProcessor implements IWitMessageProcessor
{
	public const WIT_ENTITY_TYPE = 'datetime';
	
	/**
	 * @desc Datetime accuracy levels.
	 */
	private const GRAIN_DAY = 'day';
	private const GRAIN_HOUR = 'hour';
	private const GRAIN_MINUTE = 'minute';
	
	/**
	 * @desc Datetime value types.
	 */
	private const VALUE_TYPE_VALUE = 'value';
	private const VALUE_TYPE_INTERVAL = 'interval';
	
	/**
	 * @var string
	 */
	protected $preferredDatetimeValueType = self::VALUE_TYPE_VALUE;
	
	
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
		if(!is_array($items)){
			return [];
		}
		
		$responses = [];
		foreach($items as $item){
			$confidence = (float) $item->confidence;
			$confidenceIndex = ChatBot::getConfidenceIndex($confidence);
			$dateTimeValue = $this->getDateTimeValueByType($item->values, $this->preferredDatetimeValueType);
			if($dateTimeValue instanceof \stdClass && property_exists($dateTimeValue, 'grain')){
				$responses[$confidenceIndex] = $this->parseDateTime($dateTimeValue->value, $item->grain ?: self::GRAIN_MINUTE);
			}
		}
		return $responses;
	}
	
	
	/**
	 * @param \stdClass $witJsonObject
	 * @return string|bool
	 */
	public function getBestResponse(\stdClass $witJsonObject)
	{
		$responses = $this->getResponses($witJsonObject);
		ksort($responses);
		return end($responses);
	}
	
	
	/**
	 * @param string $type
	 */
	public function setPreferredDatetimeValueType(string $type)
	{
		$this->preferredDatetimeValueType = $type;
	}
	
	
	/**
	 * Parses datetime from format e.g. 2018-08-23T12:01:00.000+02:00;
	 * @param string $rawDateTime
	 * @param string $grain
	 * @return string
	 */
	private function parseDateTime(string $rawDateTime, string $grain): string
	{
		list($date, $rawTime) = explode('T', $rawDateTime);
		
		$timeZoneSign = strpos($rawTime, '-') !== FALSE ? '-' : '+';
		list($decimalTime, $timeZone) = explode($timeZoneSign, $rawTime);
		list($time, ) = explode('.', $decimalTime);
		
		$dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', "$date $time");
		
		return $this->formatDateTime($dateTime, $grain);
	}
	
	
	/**
	 * Returns dateTime formatted according to english human-readable time format and accuracy.
	 * @param \DateTime $dateTime
	 * @param string $grain
	 * @return string
	 */
	protected function formatDateTime(\DateTime $dateTime, string $grain): string
	{
		$dayFormat = 'l, F jS';
		
		if($grain === self::GRAIN_DAY){
			return $dateTime->format($dayFormat);
		} elseif($grain === self::GRAIN_HOUR){
			return $dateTime->format($dayFormat . ', g A');
		} else {
			return $dateTime->format($dayFormat . ', g:i A');
		}
	}
	
	
	/**
	 * Returns a Datetime value that is not an interval.
	 * @param array $values
	 * @param string $type
	 * @return \stdClass|null
	 */
	protected function getDateTimeValueByType(array $values, string $type)
	{
		foreach($values as $value){
			if($value->type === $type){
				return $value;
			}
		}
		return NULL;
	}
	
}