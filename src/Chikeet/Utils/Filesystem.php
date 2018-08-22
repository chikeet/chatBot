<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 19.08.2018
 * Time: 23:29
 */

namespace Chikeet\Utils;

/**
 * Class Filesystem
 * @package Chikeet\Utils
 *
 * Utils for common filesystem operations.
 */
class Filesystem
{
	
	public static function checkOrCreateFileDirectory(string $filePath): void
	{
		$directoryPath = dirname($filePath);
		self::checkOrCreateDirectory($directoryPath);
	}
	
	
	public static function checkOrCreateDirectory(string $directoryPath): void
	{
		if(!file_exists($directoryPath)){
			mkdir($directoryPath, 0755, true);
		}
	}
	
}