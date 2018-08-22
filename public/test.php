<?php
/**
 * Created by PhpStorm.
 * User: Zuzana Kreizlova
 * Date: 19.08.2018
 * Time: 20:27
 */

/* load PHP stack and init chatBot */
require_once './includes/bootstrap.php';

echo "<html>
<head>
<title>Simple interface for ChatBot manual testing</title>
</head>
<body>
<h1>Simple interface for manual ChatBot testing</h1>";

if(isset($_GET['message'])){
	$chatBot->setDebugMode(TRUE);
	$message = $_GET['message'];
	$botResponse = $chatBot->processMessage($message);
	
	/* render results if any */
	$lastError = $chatBot->getLastErrorDescription();
	$witResponseJson = $chatBot->getWitResponseJson();
	require_once './includes/results.php';
	
	/* log bot error */
	if($chatBot->hasError()){
		\Chikeet\Utils\Logger::logError('ChatBot - ' . $chatBot->getLastErrorDescription(), $errorLogFilePath);
	}
}

/* render form */
if(!isset($message)){
	$message = '';
}
require_once './includes/form.php';

echo '</body>
</html>';



