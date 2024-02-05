<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2024 The Arianne Project

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
require_once('content/account/oauth.php');

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

		if (isset($_REQUEST['merge'])) {
			$_SESSION['merge'] = $_REQUEST['merge'];
		}

		// redirect to openid provider?
		$this->openid = new OpenID();
		if (isset($_REQUEST['openid_identifier']) && ($_REQUEST['openid_identifier'] != '')) {
			$this->openid->doOpenidRedirectIfRequired($_REQUEST['openid_identifier']);
			if ($this->openid->isAuth && !$this->openid->error) {
				return false;
			}
		}

		// redirect to the oauth provider
		$this->oauth = new OAuth();
		if (isset($_REQUEST['oauth_provider']) && ($_REQUEST['oauth_provider'] != '')) {
			$this->oauth->doRedirect($_REQUEST['oauth_provider']);
			return false;
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

			if (isset($_SESSION['account']->password)) {
				$this->error = 'You cannot link two local accounts.';
				return false;
			}

			if (strtolower($_SESSION['account']->username) == strtolower(trim($_POST['user']))) {
				$this->error = 'You need to enter the username and password of another account you own.';
				return false;
			}

			if ($_POST['csrf'] != $_SESSION['csrf']) {
				$this->error = 'Session information was lost.';
				return false;
			}

			$result = Account::tryLogin("password", $_POST['user'], $_POST['pass'], null);

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
		}

		if (isset($_GET['openid_mode']) || (isset($_REQUEST['code']))) {
			if ($_SESSION['merge'] != $_SESSION['csrf']) {
				$this->error = 'Session information was lost.';
				return false;
			}
			unset($_SESSION['merge']);

			if($_GET['openid_mode'] == 'cancel') {
				$this->error = 'Authentication was canceled.';
				return false;
			}

			if (isset($_GET['openid_mode'])) {
				$accountLink = $this->openid->createAccountLink();
			} else if (isset($_REQUEST['code'])) {
				$accountLink = $this->oauth->createAccountLink($_SESSION['stendhal_oauth_provider']);
			}

			if (!$accountLink) {
				$this->error = 'Authentication failed.';
				return false;
			}

			$oldAccount = $_SESSION['account'];
			$newAccount = Account::readAccountByLink($accountLink->type, $accountLink->username, null);
			if (isset($newAccount->password) && $newAccount->password != '') {
				$this->error = 'External account cannot be linked because it is already linked with another account';
				return false;
			}

			if (!$newAccount || is_string($newAccount)) {
				$accountLink->playerId = $oldAccount->id;
				$accountLink->insert();
			} else {
				if ($oldAccount->username != $newAccount->username) {
					mergeAccount($newAccount->username, $oldAccount->username);
				}
			}
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
			startBox("<h1>AccountLink </h1>");
			echo '<p>Please <a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url=/account/merge.html">login</a> first to any account in order to link another account.</p>';
			endBox();
		} else {
			$this->process();
		}
	}

	function process() {
		$this->displayMergeError();
		$this->displayForm();
	}

	function displayMergeError() {
		if ($this->error) {
			startBox("<h2>Result</h2>");
			echo '<p class="error">'.htmlspecialchars($this->error).'</p>';
			endBox();
		}
	}


	function displayForm() {
		startBox("<h2>Link Account</h2>");
		if (isset($_SESSION['account']->password)) {
			?>
			<p>You are currently logged into the account <b><?php echo htmlspecialchars($_SESSION['account']->username) ?></b>.</p>
			
			<p>You can link the following external accounts:</p>

			<div>
			<a href="/account/merge.html?oauth_provider=google&merge=<?php echo urlencode($_SESSION['csrf'])?>"><img src="/images/thirdparty/google.svg" alt="Login with Google"></a>
			&nbsp;
			<a href="/account/merge.html?openid_identifier=https://steamcommunity.com/openid/&merge=<?php echo urlencode($_SESSION['csrf'])?>"><img src="/images/thirdparty/steam.png" alt="Login with Steam"></a>
			</div>

			<?php
			// TODO: Only offer account providers which are not already linked
			if (isset($this->openid->error)) {
				echo '<div class="error">'.htmlspecialchars($this->openid->error).'</div>';
			}

		} else {
			?>
			<p>You are currently logged in with an external account. You can link a Stendhal account.</p>

			<form action="" method="post">
				<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'])?>">
				<table class="loginform">
					<tr><td><label for="user">Username:</label></td><td><input type="text" id="user" name="user" maxlength="30"></td></tr>
					<tr><td><label for="pass">Password:</label></td><td><input type="password" id="pass" name="pass" maxlength="30"></td></tr>
					<tr><td colspan="2" align="right"><input type="submit" name="submerge" value="Link Accounts"></td></tr>
				</table>
			</form>
			<br class="clear">
			<?php
		}

		endBox();
	}

}
$page = new AccountMerge();
