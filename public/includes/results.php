<?php
/* chatBot results view */
echo "<hr>
You say: <b>$message</b>
&nbsp;&nbsp;&gt;&gt;&nbsp;&nbsp;
Bot says: <b>$botResponse</b>
<hr>";

if(strlen($lastError) > 0){
	echo "<br><br>
	Bot error: $lastError";
}
echo "<br><br>
Wit JSON: $witResponseJson";

if($chatBot->hasError()){
	\Chikeet\Utils\Logger::logError('ChatBot - ' . $chatBot->getLastErrorDescription(), $errorLogFilePath);
}



