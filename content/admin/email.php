<?php
class AdminEMailPage extends Page {
	function writeContent() {

		if(getAdminLevel() < 400) {
			die("Ooops!");
		}

		if (isset($_POST['body'])) {
			$this->sendMail();
		} else {
			$this->renderForm();
		}
	}

	function renderForm() {
		startBox("<h1>E-Mail</h1>");
		?>
<form method="POST">
<table>
<tr><td><label for="subject">Subject:</label></td><td><input name="subject" value="<?php if (isset($_REQUEST['subject'])) { echo htmlspecialchars($_REQUEST['subject']); } ?>"></td></tr>
<tr><td><label for="to">To:</label></td><td><input name="to" value="<?php if (isset($_REQUEST['to'])) { echo htmlspecialchars($_REQUEST['to']); } ?>"></td></tr>
<!-- <tr><td><label for"cc">Cc:</label></td><td><input name="cc"></td></tr>  -->
</table>
<textarea name="body" cols="70" rows="20"><?php if (isset($_REQUEST['body'])) {echo htmlspecialchars($_REQUEST['body']); } ?></textarea>
<input type="submit">
</form>
		<?php
		endBox();

		startBox('Template Password forgotten');
		?>
<p>Hi,</p>

<p>someone requested a password reset for the Stendhal account [...ACCOUNT_NAME...]. This is the email address registered for that account.</p>

<p>Please do the following things:</p>

<p>1. Login with another account (create one if necessary)</p>

<p>2. Tell support this message:<br>

/support Hi, Please ask a server admin to merged my account [...ACCOUNT_NAME...] into this account. The Code is [...RANDOM_TOKEN...].</p>

<p>3. Wait. A server admin will be asked by someone from support to merge the accounts. When that is done, you will be able to play your old characters by logging into your new account.</p>

		<?php
		endBox();
	}

	function sendMail() {
		startBox("<h1>E-Mail</h1>");
		$res = mail($_REQUEST['to'], $_REQUEST['subject'], $_REQUEST['body'],
			'Cc: '.STENDHAL_GM_EMAIL."\r\nFrom: ".STENDHAL_GM_EMAIL
			."\r\nMime-Version: 1.0\r\nContent-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: 8bit",
			'-f '.STENDHAL_GM_ADDRESS);
		if ($res) {
			echo 'Mail sent.';
		} else {
			echo 'Failed.';
		}
		endBox();
	}
}
$page = new AdminEMailPage();
