<?php
class CreateAccountPage extends Page {
	private $result;
	private $error;

	/**
	 * this method can write additional http headers, for example for cache control.
	 *
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	public function writeHttpHeader() {
		if (strpos(STENDHAL_LOGIN_TARGET, 'https://') !== false) {
			if (!isset($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] != "on")) {
				header('Location: '.STENDHAL_LOGIN_TARGET.rewriteURL('/account/create-account.html'));
				return false;
			}
		}
		if (isset($_SESSION['account'])) {
			header('Location: '.STENDHAL_LOGIN_TARGET.rewriteURL('/account/mycharacters.html'));
			return false;
		}

		return $this->process();
	}

	function process() {
		global $protocol;
		if (!$_POST) {
			return true;
		}
		if (!$_POST['name'] || !$_POST['pw'] || !$_POST['pr']) {
			$this->error = 'One of the mandatory fields was empty.';
			return true;
		}

		if ($_POST['csrf'] != $_SESSION['csrf']) {
			$this->error = 'Session information was lost.';
			return true;
		}

		if ($_POST['pw'] != $_POST['pr']) {
			$this->error = 'Your password and repetition do not match.';
			return true;
		}

		if (!isset($_SESSION['images_loaded'])) {
			$this->error = 'Internal error, please let us know: https://sourceforge.net/p/arianne/support-requests/new/';
			error_log('image bot trap triggered.');
			return true;
		}

		if (isset($_POST['realname']) && ($_POST['realname'] != '')) {
			$this->error = 'Internal error, please let us know: https://sourceforge.net/p/arianne/support-requests/new/';
			error_log('realname bot trap triggered.');
			return true;
		}

		if (!isset($_SERVER['HTTP_REFERER']) || (
			strpos(strtolower($_SERVER['HTTP_REFERER']), 'stendhalgame.org') === false
			&& strpos(strtolower($_SERVER['HTTP_REFERER']), 'localhost') === false)) {
			$this->error = 'Internal error, please let us know: https://sourceforge.net/p/arianne/support-requests/new/';
			error_log('referer bot trap triggered.');
			return true;
		}

		// Note: For the password field, we do want an error message, if it is too long.
		// Therefore the maxlength-attribute is one character larger than the error.
		if ((strlen($_POST['pw']) >= 100) || (strlen($_POST['name']) > 20) || (strlen($_POST['email']) > 50)) {
			$this->error = 'At least one of the fields is too long.';
			return true;
		}

		$user = strtolower($_POST['name']);
		require_once('scripts/pharauroa/pharauroa.php');
		$clientFramework = new PharauroaClientFramework(STENDHAL_MARAUROA_SERVER, STENDHAL_MARAUROA_PORT, STENDHAL_MARAUROA_CREDENTIALS);
		$template = new PharauroaRPObject();
		$template->put('outfit', $_REQUEST['outfitcode']);

		$this->result = $clientFramework->createAccount($user, $_POST['pw'], $_POST['email']);

		if ($this->result->wasSuccessful()) {
			// on success: login and redirect to character creation
			header('HTTP/1.0 301 Moved permanently.');
			header("Location: ".$protocol."://".$_SERVER['HTTP_HOST'].preg_replace("/&amp;/", "&", rewriteURL('/account/create-character.html')));
			$_SESSION['account'] = Account::readAccountByName($user);
			PlayerLoginEntry::logUserLogin('website', $_POST['name'], $_SERVER['REMOTE_ADDR'], null, true);
			$_SESSION['marauroa_authenticated_username'] = $_SESSION['account']->username;
			$_SESSION['csrf'] = createRandomString();
			return false;
		} else {
			return true;
		}
	}

	public function writeHtmlHeader() {
		echo '<title>Create Account'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {
		$this->show();
	}

	function show() {
		if ($this->error || (isset($this->result) && !$this->result->wasSuccessful())) {
			startBox("<h1>Error</h1>");
			if ($this->error) {
				echo '<span class="error">'.htmlspecialchars($this->error).'</span>';
			} else {
				echo '<span class="error">'.htmlspecialchars($this->result->getMessage()).'</span>';
			}
			endBox();
		}

		$_SESSION['csrf'] = createRandomString();
		startBox("<h1>Create Account</h1>");
?>

<form id="createAccountForm" name="createAccountForm" action="" method="post"> <!-- onsubmit="return createAccountCheckForm()" -->
<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'])?>">
<img src="/content/account/img.php" alt="" width="1px" height="1px">
<table class="loginform">
<tr style="display:none">
<td><label for="realname">Name: </label></td>
<td><input id="realname" name="realname" value="" type="text" maxlength="50"></td>
<td><div id="realname" class="warn"></div></td>
</tr>

<tr>
<td><label for="name">Name:<sup>*</sup> </label></td>
<td><input id="name" name="name" value="<?php if (isset($_REQUEST['name'])) {echo htmlspecialchars($_REQUEST['name']);}?>" type="text" maxlength="20" ></td>
<td><div id="namewarn" class="warn"></div></td>
</tr>

<tr>
<td><label for="pw">Password:<sup>*</sup> </label></td>
<td><input id="pw" name="pw" type="password" maxlength="100"></td>
<td><div id="pwwarn" class="warn"></div></td>
</tr>

<tr>
<td><label for="pr">Password Repeat:<sup>*</sup> </label></td>
<td><input id="pr" name="pr" type="password" maxlength="100"></td>
<td><div id="prwarn" class="warn"></div></td>
</tr>

<tr>
<td><label for="email">E-Mail: </label></td>
<td><input id="email" name="email" value="<?php if (isset($_REQUEST['email'])) {echo htmlspecialchars($_REQUEST['email']);}?>" type="email" maxlength="50"></td>
<td><div id="emailwarn" class="warn"></div></td>
</tr>

<tr>
<td>&nbsp;</td>
<td><input name="submit" style="margin-top: 2em" type="submit" value="Create Account"></td>
<td>&nbsp;</td>
</tr>

</table>
<input id="serverpath" name="serverpath" type="hidden" value="<?php echo STENDHAL_FOLDER;?>">

</form>
<br class="clear">
<?php

endBox();

startBox('<h2>External Account</h2>');
?>
Alternatively, you can login with any of these services without creating an account.
<div>
<br>
<a href="/account/login.html?oauth_provider=google"><img src="/images/thirdparty/google.svg" alt="Login with Google"></a>
&nbsp;
<a href="/account/login.html?openid_identifier=https://steamcommunity.com/openid/"><img src="/images/thirdparty/steam.png" alt="Login with Steam"></a>
</div>

<?php

endBox();

?>

<br class="clear">
<?php startBox("<h2>Logging and privacy</h2>");?>
<p>
<font size="-1">On login information which identifies your computer on the internet will be
 logged to prevent abuse (like many attempts to guess a password in order to
 hack an account or creation of many accounts to cause trouble).</font></p>

<p><font size="-1">
Furthermore all events and actions that happen within the game-world
 (like solving quests, attacking monsters) are logged. This information is
 used to analyse bugs and in rare cases for abuse handling.</font></p>

<p><font size="-1"> If you use an external service for authentication,
a local account is generated automatically based on your name. The unique
identifier and email address returned by the authentication provider
is stored with your local account. This information is only used during future
logins or account recovery in order to link your external account to your
 local account.</font></p>

<p><font size="-1">
Stendhal is a non-commercial spare time project. We do NOT use your
data for advertisment. We do NOT make it available to third parties.</font></p>
<?php
		endBox();
	}

}

$page = new CreateAccountPage();
