<?php
/* chatBot test form view */
echo "
<br><br>
<hr>
<br>
<form action='../test.php' method='get'>
	<label for='message'>Write something down!</label> <br><br>
	<textarea id='message' cols='40' rows='3' name='message'>$message</textarea><br><br>
	<input type='submit' name='submit' value='Send &gt;&gt;&nbsp;&nbsp;' />
</form>";