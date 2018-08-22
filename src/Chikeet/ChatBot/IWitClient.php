<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 20.08.2018
 * Time: 13:20
 */

namespace Chikeet\ChatBot;

/**
 * Interface IWitClient
 * @package Chikeet\ChatBot
 *
 * Wit AI communication client (https://wit.ai/).
 */
interface IWitClient
{
	
	/**
	 * Should return a JSON string Wit response.
	 * @param string $message
	 * @return string
	 */
	public function processRequest(string $message): string;
	
}