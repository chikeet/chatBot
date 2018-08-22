<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 19.08.2018
 * Time: 20:27
 */

/* load PHP stack and init chatBot */
require_once './includes/bootstrap.php';

$messengerAccessToken = 'cauliflower-token';
$messengerWebHook = new \Messenger\WebHook($messengerAccessToken, $chatBot,$errorLogFilePath);

$statusCode = $messengerWebHook->processRequest($_GET, file_get_contents('php://input'));
if($statusCode === \Messenger\WebHook::HTTP_STATUS_CODE_200_OK){
	echo $messengerWebHook->getResponse();
} else {
	http_response_code($statusCode);
}



