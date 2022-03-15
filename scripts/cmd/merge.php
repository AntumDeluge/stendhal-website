<?php

if (isset($_SERVER['SERVER_NAME'])) {
	echo 'Command line tool';
	exit;
}

if (count($_SERVER['argv']) < 3 || count($_SERVER['argv']) > 4) {
	echo "Call php merge.php <oldaccount> <newaccount>.\r\n";
	echo "    <oldaccount> is the one which will be deactivated.\r\n";
	echo "    <newaccount> is the one which will get all characters.\r\n";
	exit;
}

set_include_path('../..');

require_once('scripts/website.php');

$oldUsername = $_SERVER['argv'][1];
$newUsername = $_SERVER['argv'][2];

$oldAccountId = getUserID($oldUsername);
$newAccountId = getUserID($newUsername);

echo 'Old id: '.$oldAccountId. '      New id: '.$newAccountId."\r\n";

if (!isset($oldAccountId) || !isset($newAccountId) || $oldAccountId <= 0 || $newAccountId <= 0) {
	echo 'Invalid user name.'."\r\n";
	exit;
}

if ($oldAccountId == $newAccountId) {
	echo 'You need to provide different account names.'."\r\n";
	exit;
}

$oldAccount = Account::readAccountByName($oldUsername);
$newAccount = Account::readAccountByName($newUsername);

$okay = true;
if ($oldAccount->getAccountStatusMessage() != null) {
	echo 'Status of old ' . $oldUsername . ': ' . $oldAccount->getAccountStatusMessage()."\r\n";
	$okay = false;
}

if ($newAccount->getAccountStatusMessage() != null) {
	echo 'Status of new ' . $newUsername . ': ' . $newAccount->getAccountStatusMessage()."\r\n";
	$okay = false;
}

if (!$okay) {
	if ((count($_SERVER['argv']) < 4) || $_SERVER['argv'][3] != '-force') {
		echo "\r\nERROR: Not merged\r\n\r\n";
		echo 'Use php merge.php ' . $oldUsername . ' ' , $newUsername . ' -force'."\r\n";
		die();
	} else {
		echo "\r\nForced to ignore warnings\r\n";
	}
}

mergeAccount($_SERVER['argv'][1], $_SERVER['argv'][2]);

echo "\r\nMerged\r\n";
