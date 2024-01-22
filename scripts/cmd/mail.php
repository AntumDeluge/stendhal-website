<?php

if (!isset($_SERVER['SERVER_NAME'])) {

	if (count($_SERVER['argv']) != 5) {
		echo "Call php mail.php player_id username token email.\r\n";
		exit;
	}

	set_include_path('../..');

	require_once('scripts/website.php');

	$playerId = $_SERVER['argv'][1];
	$username = $_SERVER['argv'][2];
	$token = $_SERVER['argv'][3];
	$email = $_SERVER['argv'][4];

	sendRegistrationMail($playerId, $username, $token, $email);
}

function sendRegistrationMail($playerId, $username, $token, $email) {
	$subject = "Stendhal account ".$username." registration";
	$body = "Hello ".$username.", welcome to Stendhal.";
	$body .= "\r\n";
	$body .= "Please verify your email address by clicking on this link:\r\n";
	$body .= STENDHAL_LOGIN_TARGET."/account/confirm/".urlencode($token)."\r\n";
	$body .= "\r\n";
	$body .= "Your email address will be used for account recovery, if you forget \r\n";
	$body .= "your password or if there are indications for an account hack.\r\n";
	$body .= "\r\n";
	$body .= "If you did not create a Stendhal account, please ignore this email.\r\n";
	$body .= "\r\n";
	$body .= "Any questions? Please see https://stendhalgame.org/wiki/AskForHelp\r\n";
	$body .= "\r\n";
	mail($email, $subject, $body, "From: ".STENDHAL_NOREPLY_EMAIL, '-f '.STENDHAL_NOREPLY_ADDRESS);
}


function sendChangeMail($username, $oldMail, $newMail) {
	$subject = "Stendhal account ".$username." new mail address";
	$body = "Hello ".$username."!\r\n";
	$body .= "\r\n";
	$body .= "This email confirms, that your email address was changed from ";
	$body .= $oldMail." to \r\n";
	$body .= $newMail.".\r\n";
	$body .= "\r\n";
	$body .= "\r\n";
	$body .= "Your email address will be used for account recovery, if you forget \r\n";
	$body .= "your password or if there are indications for an account hack.\r\n";
	$body .= "\r\n";
	$body .= "Any questions? Please see https://stendhalgame.org/wiki/AskForHelp\r\n";
	$body .= "\r\n";
	$body .= "Best regards from the Stendhal team\r\n";
	$body .= "\r\n";
	mail($oldMail, $subject, $body, "From: ".STENDHAL_NOREPLY_EMAIL, '-f '.STENDHAL_NOREPLY_ADDRESS);
}


function sendMergeMail($oldUsername, $newUsername, $oldMail) {
	$subject = "Stendhal account ".$oldUsername." merged";
	$body = "Hello ".$oldUsername."!\r\n";
	$body .= "\r\n";
	$body .= "This email confirms, that your Stendhal account ";
	$body .= $oldUsername." was merged into ".$newUsername.".\r\n";
	$body .= "\r\n";
	$body .= "\r\n";
	$body .= "Your email address will be used for account recovery, if you forget \r\n";
	$body .= "your password or if there are indications for an account hack.\r\n";
	$body .= "\r\n";
	$body .= "Any questions? Please see https://stendhalgame.org/wiki/AskForHelp\r\n";
	$body .= "\r\n";
	$body .= "Best regards from the Stendhal team\r\n";
	$body .= "\r\n";
	mail($oldMail, $subject, $body, "From: ".STENDHAL_NOREPLY_EMAIL, '-f '.STENDHAL_NOREPLY_ADDRESS);
}
