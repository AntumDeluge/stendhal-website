<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2010 The Arianne Project

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('scripts/account.php');
require_once('content/account/openid.php');
require_once('content/account/fb.php');

class AccountMerge extends Page {
	private $error;
	private $openid;

	public function writeHttpHeader() {
		// force SSL if supported
		if (strpos(STENDHAL_LOGIN_TARGET, 'https://') !== false) {
			if (!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != "on")) {
				header('Location: '.STENDHAL_LOGIN_TARGET.rewriteURL('/account/merge.html'));
				return false;
			}
		}

		if (isset($_POST['merge'])) {
			$_SESSION['merge'] = $_POST['merge'];
		}

		// redirect to openid provider?
		$this->openid = new OpenID();
		if (isset($_REQUEST['openid_identifier']) && ($_REQUEST['openid_identifier'] != '')) {
			$this->openid->doOpenidRedirectIfRequired($_POST['openid_identifier']);
			if ($this->openid->isAuth && !$this->openid->error) {
				return false;
			}
		}

		// redirect to the oauth provider
		$this->fb = new Facebook();
		if (isset($_REQUEST['oauth_version']) && ($_REQUEST['oauth_version'] != '')) {
			$this->fb->doRedirectWithCSRFToken($_SESSION['csrf']);
			if ($this->fb->isAuth) {
				return false;
			}
		}

		if ($this->processMerge()) {
			header('Location: '.STENDHAL_LOGIN_TARGET.rewriteURL('/account/mycharacters.html'));
			return false;
		}
		return true;
	}


	function processMerge() {
		if (isset($_POST['pass']) || isset($_GET['openid_mode']) || isset($_REQUEST['code'])) {
			// make sure that we are (still) logged in
			if (!isset($_SESSION['account'])) {
				header('Location: '.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&url='.rewriteURL('/account/merge.html'));
				return false;
			}
		}

		if (isset($_POST['pass'])) {
			if (! isset($_POST['submerge'])) {
				return false;
			}

			if (strtolower($_SESSION['account']->username) == strtolower(trim($_POST['user']))) {
				$this->error = 'You need to enter the username and password of another account you own.';
				return false;
			}

			if (!isset($_POST['confirm'])) {
				$this->error = 'You need to tick the confirm-checkbox.';
				return false;
			}

			if ($_POST['csrf'] != $_SESSION['csrf']) {
				$this->error = 'Session information was lost.';
				return false;
			}

			$result = Account::tryLogin("password", $_POST['user'], $_POST['pass']);

			if (! ($result instanceof Account)) {
				$this->error = htmlspecialchars($result);
				return false;
			}

			if ($_SESSION['account']->password) {
				mergeAccount($_POST['user'], $_SESSION['account']->username);
			} else {
				$oldUsername = $_SESSION['account']->username;
				mergeAccount($oldUsername, $_POST['user']);
				$_SESSION['account'] = Account::readAccountByName($_POST['user']);
				$_SESSION['marauroa_authenticated_username'] = $_SESSION['account']->username;
			}

			return true;

		} else if (isset($_GET['openid_mode'])) {

			if($_GET['openid_mode'] == 'cancel') {
				$this->error = 'OpenID-Authentication was canceled.';
				return false;
			}

			if ($_SESSION['merge'] != $_SESSION['csrf']) {
				$this->error = 'Session information was lost.';
				return false;
			}
			unset($_SESSION['merge']);

			$accountLink = $this->openid->createAccountLink();
			if (!$accountLink) {
				$this->error = $this->openid->error;
				return false;
			}

			$this->openid->merge($accountLink);
			return true;

		} else if (isset($_REQUEST['code'])) {

			$accountLink = $this->fb->createAccountLink();
			if (!$accountLink) {
				$this->fb = 'Facebook login failed.';
				return false;
			}

			$this->fb->merge($accountLink);
			return true;
		}

		return false;
	}


	public function writeHtmlHeader() {
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<title>Account Merging'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		if (!isset($_SESSION['account'])) {
			startBox("<h1>Account Merging</h1>");
			echo '<p>Please <a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url=/account/merge.html">login</a> first to merge accounts.</p>';
			endBox();
		} else {
			$this->process();
		}
	}

	function process() {
		$this->displayHelp();
		$this->displayMergeError();
		$this->displayForm();
	}

	function displayHelp() {
		startBox("<h2>Account Merging</h2>");?>
		<p>With the form below you can merge your other accounts. &nbsp;&nbsp;&ndash;&nbsp;&nbsp;
		(<a href="https://stendhalgame.org/wiki/Stendhal_Account_Merging">Help</a>)</p>
		<p>This means that all characters previously associated with the other
		account will be available in this account.</p>
		<p class="warn">Merging accounts cannot be undone.</p>
		<?php endBox();
	}

	function displayMergeError() {
		if ($this->error) {
			startBox("<h2>Result</h2>");
			echo '<p class="error">'.htmlspecialchars($this->error).'</p>';
			endBox();
		}
	}


	function displayForm() {
		startBox("<h2>Account to merge</h2>"); ?>
		<p>You are currently logged into the account <b><?php echo htmlspecialchars($_SESSION['account']->username) ?></b>.</p>

		<form action="" method="post">
			<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'])?>">
			<table>
				<tr><td><label for="user">Username:</label></td><td><input type="text" id="user" name="user" maxlength="30"></td></tr>
				<tr><td><label for="pass">Password:</label></td><td><input type="password" id="pass" name="pass" maxlength="30"></td></tr>
				<tr><td colspan="2" align="left"><input type="checkbox" id="confirm" name="confirm">
				<label for="confirm">I really want to merge these accounts.</label></td></tr>
				<tr><td colspan="2" align="right"><input type="submit" name="submerge" value="Merge"></td></tr>
			</table>
		</form>

		<?php endBox();
/*
			startBox("<h2>External Account</h2>");
			?>
				<form id="openid_form" action="<?php echo STENDHAL_FOLDER;?>/?id=content/account/merge" method="post">
		<input id="oauth_version" name="oauth_version" type="hidden">
		<input id="oauth_server" name="oauth_server" type="hidden">

		<div id="openid_choice">
			<p>You can add external identities to your account.</p>
			<div id="openid_btns"></div>
		</div>

		<div>
			<noscript>
				<p>OpenID is a service that allows you to log on to many different websites using a single identity.</p>
			</noscript>
		</div>
		<table id="openid-url-input">
		<tbody><tr>
			<td class="vt large">
				<input id="openid_identifier" name="openid_identifier" class="openid-identifier" style="height: 28px; width: 450px;" tabindex="100" type="text">
			</td>

			<td class="vt large">
				<input id="submit-button" style="margin-left: 5px; height: 36px;" value="Merge" tabindex="101" type="submit">
			</td>
		</tr>
		</tbody>
		</table>
		<input type="hidden" id="merge" name="merge" value="<?php echo htmlspecialchars($_SESSION['csrf'])?>">
	</form>

<?php

		if (isset($this->openid->error)) {
			echo '<div class="error">'.htmlspecialchars($this->openid->error).'</div>';
		}

		endBox();
*/
	}

}
$page = new AccountMerge();
