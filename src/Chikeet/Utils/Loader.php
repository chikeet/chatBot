<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 19.08.2018
 * Time: 19:57
 */

namespace Chikeet\Utils;

/**
 * Class Loader
 * @package Chikeet\Utils
 *
 * Very simple PHP class loading utility.
 */
class Loader
{
	
	/**
	 * Returns array of file paths to include or require. All paths from the specified directory recursively
	 * @param string $directoryPath
	 * @param string $extension Defaults to 'php'. Returns paths to all files if set to null.
	 * @return array
	 */
	public static function getDirectoryContents(string $directoryPath, string $extension = 'php'): array
	{
		if(!is_dir($directoryPath)){
			throw new \InvalidArgumentException("No directory found at path '$directoryPath'.");
		}
		
		$contents = scandir($directoryPath);
		$results = [];
		
		foreach($contents as $name) {
			$contentPath = realpath($directoryPath . DIRECTORY_SEPARATOR . $name);
			
			if(is_file($contentPath)) {
				if(is_null($extension)){
					$results[] = $contentPath;
				} else {
					$info = new \SplFileInfo($contentPath);
					if($info->getExtension() === $extension){
						$results[] = $contentPath;
					}
				}
			} elseif(!in_array($name, ['.', '..'])) {
				$results = array_merge($results, self::getDirectoryContents($contentPath));
			}
		}
		return $results;
	}
}