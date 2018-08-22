<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 19.08.2018
 * Time: 20:27
 */

/* load PHP stack */
require_once '../src/Chikeet/Utils/Loader.php';
use Chikeet\Utils\Loader;

$loadedPaths = Loader::getDirectoryContents(__DIR__ . '/../src/Chikeet');
foreach($loadedPaths as $path){
	require_once $path;
}

/* process Messenger request */
$witToken = 'JVCXL3W3F2H5YXYVSYCN5NGXXRQE2T5U';
$witClient = new \Chikeet\Wit\Client($witToken);

$chatBot = new \Chikeet\ChatBot\ChatBot($witClient);
$chatBot->addMessageProcessor(new \Chikeet\Wit\MessageProcessors\GreetingsProcessor);
$chatBot->addMessageProcessor(new \Chikeet\Wit\MessageProcessors\ReminderProcessor);
$chatBot->addMessageProcessor(new \Chikeet\Wit\MessageProcessors\ProductProcessor);

$errorLogFilePath = __DIR__ . '/../log/error.log';

$chatBot->setDebugMode(TRUE);
$message = $_GET['message'];
$botResponse = $chatBot->processMessage($message);

echo 'Bot says: <b>' . $botResponse . '</b>';
echo "\n<br><br>\n";
echo 'Bot error: ' . $chatBot->getLastErrorDescription();
echo "\n<br><br>\n";
echo 'Wit JSON: ' . $chatBot->getWitResponseJson();

if($chatBot->hasError()){
	\Chikeet\Utils\Logger::logError('ChatBot - ' . $chatBot->getLastErrorDescription(), $errorLogFilePath);
}



