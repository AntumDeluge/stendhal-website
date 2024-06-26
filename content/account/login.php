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
require_once('content/account/oauth.php');
require_once('content/account/openid.php');

class LoginPage extends Page {
	private $error;
	private $openid;
	private $oauth;

	public function writeHttpHeader() {
	    		unset($_SESSION['merge']);
		if ($this->handleRedirectIfAlreadyLoggedIn()) {
			return false;
		}

		// force SSL if supported
		if (strpos(STENDHAL_LOGIN_TARGET, 'https://') !== false) {
			if (!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != "on")) {
				header('Location: '.STENDHAL_LOGIN_TARGET.rewriteURL('/account/login.html'));
				return false;
			}
		}

		// redirect to openid provider?  
		$this->openid = new OpenID();
		if (isset($_REQUEST['openid_identifier']) && ($_REQUEST['openid_identifier'] != '')) {
			AppLogin::storeToSession();
			$this->openid->doOpenidRedirectIfRequired($_REQUEST['openid_identifier']);
			if ($this->openid->isAuth && !$this->openid->error) {
				return false;
			}
		}
		// redirect to the oauth provider
		$this->oauth = new OAuth();
		if (isset($_REQUEST['oauth_provider']) && ($_REQUEST['oauth_provider'] != '')) {
			AppLogin::storeToSession();
			$this->oauth->doRedirect($_REQUEST['oauth_provider']);
			return false;
		}
		
		if ($this->verifyLoginByPassword()) {
			return false;
		}

		if ($this->verifyOAuth()) {
			return false;
		}

		if ($this->verifyLoginByOpenid()) {
			return false;
		}

		if ($this->verifyLoginBySteamAuthTicket()) {
			return false;
		}

		if ($this->verifyLoginByLoginSeed()) {
			return false;
		}

		return true;
	}

	public function verifyLoginByPassword() {
		if (!isset($_POST['sublogin'])) {
			return false;
		}

		if( !$_POST['user'] || !$_POST['pass']) {
			$this->error = "You didn't fill in a required field.";
			return false;
		}

		$username = trim($_POST['user']);
		$password = trim($_POST['pass']);
		// Note: For the password field, we do want an error message, if it is too long
		// Therefore the maxlength-attribute is one character larger than the error.
		if ((strlen($username) > 30) || (strlen($password) >= 1000)) {
			$this->error = "A field is too long.";
			return false;
		}
		$result = Account::tryLogin("password", $username, $password, null);
		if (! ($result instanceof Account)) {
			$this->error = $result;
			return false;
		}
		$this->completeLogin($result);
		return true;
	}

	private function completeLogin($account) {
		$_SESSION['account'] = $account;
		$_SESSION['marauroa_authenticated_username'] = $account->username;
		$_SESSION['csrf'] = createRandomString();
		fixSessionPermission();
		AppLogin::redirectToTargetUrl();
	}
	
	public function verifyLoginByOpenid() {
		if (!isset($_GET['openid_mode'])) {
			return false;
		}

		if($_GET['openid_mode'] == 'cancel') {
			$this->openid->error = 'OpenID-Authentication was canceled.';
			return false;
		}

		$accountLink = $this->openid->createAccountLink();
		if (!$accountLink) {
			$this->openid->error = 'OpenID-Authentication failed.';
			return false;
		}
		Account::loginOrCreateByAccountLink($accountLink);
		AppLogin::redirectToTargetUrl();
		return true;
	}

	public function verifyLoginBySteamAuthTicket() {
		if (!isset($_REQUEST['steam_auth_ticket'])) {
			return false;
		}

		$url = 'https://partner.steam-api.com/ISteamUserAuth/AuthenticateUserTicket/v1/'
			.'?appid='.STENDHAL_STEAM_APP_ID
			.'&key='.STENDHAL_STEAM_PARTNER_KEY
			.'&ticket='.urlencode($_REQUEST['steam_auth_ticket']);
		$response = requestJson($url);
		if ($response['response']['params']['result'] !== 'OK') {
			var_dump($response);
			return false;
		}
		$accountLink = new AccountLink(null, null, 'steam', $response['response']['params']['steamid'], null, null, null, false);
		Account::loginOrCreateByAccountLink($accountLink);
		AppLogin::redirectToTargetUrl();
		return true;
	}

	public function verifyLoginByLoginSeed() {
		if (!isset($_REQUEST['loginseed'])) {
			return false;
		}
		$result = Account::tryLogin("loginseed", null, $_REQUEST['loginseed'], null);
		if (! ($result instanceof Account)) {
			$this->error = $result;
			return false;
		}
		$this->completeLogin($result);
		return true;
	}

	public function verifyOAuth() {
		if (!isset($_REQUEST['code']) || !isset($_SESSION['stendhal_oauth_provider'])) {
			return false;
		}
		$accountLink = $this->oauth->createAccountLink($_SESSION['stendhal_oauth_provider']);
		if (!$accountLink) {
			$this->openid->error = 'Authentication failed.';
			return false;
		}
		Account::loginOrCreateByAccountLink($accountLink);
		AppLogin::redirectToTargetUrl();
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Login'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {
		$this->displayLoginForm();
	}

	function handleRedirectIfAlreadyLoggedIn() {
		if ($this->checkLogin()) {
			if (isset($_REQUEST['url']) && ($_REQUEST['url'] == 'close')) {
				echo '<!DOCTYPE html><html><head><title>Close</title>';
				echo '<script type="text/javascript">window.close();</script>';
				echo '</head><body>Authentication successful.</body></html>';
			} else {
				AppLogin::redirectToTargetUrl();
			}
			return true;
		}
		return false;
	}

	function checkLogin() {
		if (!isset($_SESSION) || !isset($_SESSION['account'])) {
			return false;
		}

		if (isset($_REQUEST['username'])) {
			$okay = $_REQUEST['username'] === $_SESSION['account']->username;
			if (!$okay) {
				unset($_SESSION['account']);
				unset($_SESSION['csrf']);
				$_SESSION = array();
				session_destroy();
			}
			return $okay;
		}
		return true;
	}


	function displayLoginForm() {
		startBox("<h1>Login</h1>");
	?>

		<div class="bubble">
			Remember not to disclose your username or password to anyone, not even friends or administrators.<br>
			Check that this webpage URL matches your game server name.
		</div><br>

		<?php
		if ($this->error) {
			echo "<p class=\"error\">".htmlspecialchars($this->error)."</p>";
		}

		if (isset($_REQUEST['url'])) {
			$url = $_REQUEST['url'];
			$urlParamsArray = explode('&', str_replace('?', '&', urldecode($url)));
			$urlParams = array();
			foreach ($urlParamsArray as $urlParam) {
				$item = explode('=', $urlParam);
				if (isset($item[1])) {
					$urlParams[$item[0]] = $item[1];
				} else {
					$urlParams[$urlParam] = '';
				}
			}
			if (isset($urlParams['openid.realm'])) {
				$targetRealm = preg_replace('|^[^:]*://|', '', $urlParams['openid.realm']);
				echo '<div class"openidnotice">You are logging in to an external service:';
				echo '<div class="openidtargetnotice" style="font-size:2em; font-weight: bold">'.STENDHAL_SERVER_NAME.' → '.htmlspecialchars($targetRealm).'</div>';
				echo '<br>';
			}
		}

		?>

		<div style="float:right">
		<a href="<?php echo htmlspecialchars(buildUrlWithParams('/account/login.html?oauth_provider=google', ['url', 'build', 'state', 'seed'], $_REQUEST))?>"><img src="/images/thirdparty/google.svg" alt="Login with Google"></a>
		&nbsp;
		<a href="<?php echo htmlspecialchars(buildUrlWithParams('/account/login.html?openid_identifier=https://steamcommunity.com/openid/', ['url', 'build', 'state', 'seed'], $_REQUEST))?>"><img src="/images/thirdparty/steam.png" alt="Login with Steam"></a>
		</div>
		<form action="" method="post">
			<table class="loginform">
				<tr><td><label for="user">Username:</label></td><td><input type="text" id="user" name="user" maxlength="30" <?php
				if (isset($_REQUEST['username'])) {
					echo ' value="'.htmlspecialchars($_REQUEST['username']).'" ';
				}
				?>></td></tr>
				<tr><td><label for="pass">Password:</label></td><td><input type="password" id="pass" name="pass" maxlength="1000"></td></tr>
				<tr><td>&nbsp;</td><td><input type="submit" name="sublogin" value="Login"></td></tr>
			</table>
			<br style="clear:both">
			<?php AppLogin::addHiddenFormFields(); ?>
		</form>
		<br class="clear">

		<p>New? <b><a href="<?php echo htmlspecialchars(buildUrlWithParams(rewriteURL('/account/create-account.html'), ['url', 'build', 'state', 'seed'], $_REQUEST))?>">Create account...</a></b></p>
		<br>
		<?php
		endBox();
	}
}
$page = new LoginPage();
