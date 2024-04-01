<?php

require_once('scripts/account.php');

class LogoutPage extends Page {

	public function writeHttpHeader() {

		// Kill session variables
		unset($_SESSION['account']);
		unset($_SESSION['csrf']);
		$_SESSION = array(); // reset session array
		session_destroy();   // destroy session.
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Logout'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
		$url = '/';
		if (AppLogin::isApp()) {
			$url = rewriteURL("/account/login.html");
		}
		echo '<meta http-equiv="Refresh" content="0;url='.htmlspecialchars($url).'">';
	}

	function writeContent() {
		startBox("<h1>Logging Out</h1>");
		echo 'You have successfully <b>logged out</b>.';
		endBox();
	}
}
$page = new LogoutPage();
