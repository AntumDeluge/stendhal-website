<?php

/**
 * verifies the email token
 */
class VerifyPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Email verification'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {
		startBox('<h1>Verification of email-address</h1>');
		if (!isset($_REQUEST['token'])) {
			echo '<p class="error">Error: The token is missing.</p>';
		} else {
			$res = Account::verifyEMail($_REQUEST['token']);
			if ($res) {
				echo '<p class="okay">Your email address was confirmed successfully.</p>';
				echo '<p><a href="'.rewriteURL('/account/mycharacters.html').'">Play...</a>';
			} else {
				echo '<p class="error">Error: The token is invalid.</p>';
			}
		}
		endBox();
	}
}

$page = new VerifyPage();
