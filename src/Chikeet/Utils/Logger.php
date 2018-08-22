<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 19.08.2018
 * Time: 23:29
 */

namespace Chikeet\Utils;

/**
 * Class Logger
 * @package Chikeet\Utils
 *
 * Utils for error logging.
 */
class Logger
{
	
	/**
	 * @desc Logs an error message with datetime and URI to the specified file. Creates the file directory if not exists.
	 * @param string $message
	 * @param string $filePath
	 */
	public static function logError(string $message, string $filePath): void
	{
		Filesystem::checkOrCreateFileDirectory($filePath);
		
		$datetime = date('Y-m-d H:i:s');
		$context = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$logLine = '[' . $datetime . '] ' . $message . ' @ ' . $context . "\n";
		
		error_log($logLine, 3, $filePath);
	}
	
}