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
class CreateCharacterPage extends Page {
	private $result;

	/**
	 * this method can write additional http headers, for example for cache control.
	 *
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	public function writeHttpHeader() {
		global $protocol;
		if (!isset($_SESSION['account'])) {
			// Show link to login box
			return true;
		}

		$res = $this->process();
		if ($res) {
			// redirect to my characters page
			header('HTTP/1.0 301 Moved permanently.');
			header("Location: ".$protocol."://".$_SERVER['HTTP_HOST'].preg_replace("/&amp;/", "&", rewriteURL('/account/mycharacters.html')));
			return false;
		}
		return true;
	}


	public function writeHtmlHeader() {
		echo '<title>Create Character'.STENDHAL_TITLE.'</title>';
		echo '<meta name="robots" content="noindex">'."\n";
	}

	function writeContent() {
		if (!isset($_SESSION['account'])) {
			startBox("<h1>Create Character</h1>");
			echo '<p>Please <a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url=/account/create-character.html">login</a> to create a character.</p>';
			endBox();
		} else {
			$this->show(rewriteURL('/account/create-character.html'));
		}
	}

	function process() {
		if (! isset($_POST['name']) || !isset($_REQUEST['outfitcode'])) {
			return false;
		}

		if ($_POST['csrf'] != $_SESSION['csrf']) {
			return false;
		}

		$user = strtolower($_POST['name']);
		if (strlen($user) > 20 || strlen($_REQUEST['outfitcode']) > 300) {
			return false;
		}

		require_once('scripts/pharauroa/pharauroa.php');
		$clientFramework = new PharauroaClientFramework(STENDHAL_MARAUROA_SERVER, STENDHAL_MARAUROA_PORT, STENDHAL_MARAUROA_CREDENTIALS);
		$template = new PharauroaRPObject();
		$template->put('outfit_ext', $_REQUEST['outfitcode']);

		$this->result = $clientFramework->createCharacter($_SESSION['account']->username, $user, $template);

		if ($this->result->wasSuccessful()) {
			return true;
		} else {
			return false;
		}
	}


	function show($createURL) {
		if (isset($_POST['name']) && ($_POST['csrf'] != $_SESSION['csrf'])) {
			startBox("<h2>Error</h2>");
			echo '<p class="error">Session information was lost.</p>';
			endBox();
		}

		if (isset($this->result) && !$this->result->wasSuccessful()) {
			startBox("Error");
			echo '<span class="error">'.htmlspecialchars($this->result->getMessage()).'</span>';
			endBox();
		}

		startBox("<h1>Create Character</h1>");
?>

<form id="createCharacterForm" name="createCharacterForm" action="<?php echo $createURL;?>" method="POST" style="height:22em; padding: 1em"> <!-- onsubmit="return checkForm()"  -->
<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'])?>">

<table style="float: left; margin-right: 2em">
<tr>
	<td>Hat</td>
	<td><input class="outfitprev" type="button" data-offset="7" value="&lt;"></td>
	<td rowspan="5"><canvas id="canvas" style="background-position: center;
  background-size: cover; margin: 0 16px" height="98px" width="68px"></canvas></td>
	<td><input class="outfitnext" type="button" data-offset="7" value="&gt;"></td>
</tr>
<tr>
	<td>Hair</td>
	<td><input class="outfitprev" type="button" data-offset="6" value="&lt;"></td>
	<td><input class="outfitnext" type="button" data-offset="6" value="&gt;"></td>
</tr>
<!--
<tr>
	<td>Mask</td>
	<td><input class="outfitprev" type="button" data-offset="5" value="&lt;"></td>
	<td><input class="outfitnext" type="button" data-offset="5" value="&gt;"></td>
</tr>
-->
<tr>
	<td>Eyes</td>
	<td><input class="outfitprev" type="button" data-offset="4" value="&lt;"></td>
	<td><input class="outfitnext" type="button" data-offset="4" value="&gt;"></td>
</tr>
<!--
<tr>
	<td>Mouth</td>
	<td><input class="outfitprev" type="button" data-offset="3" value="&lt;"></td>
	<td><input class="outfitnext" type="button" data-offset="3" value="&gt;"></td>
</tr>
-->
<tr>
	<td>Head</td>
	<td><input class="outfitprev" type="button" data-offset="2" value="&lt;"></td>
	<td><input class="outfitnext" type="button" data-offset="2" value="&gt;"></td>
</tr>
<tr>
	<td>Dress</td>
	<td><input class="outfitprev" type="button" data-offset="1" value="&lt;"></td>
	<td><input class="outfitnext" type="button" data-offset="1" value="&gt;"></td>
</tr>

<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td style="text-align: center"><input class="turn" type="button" data-offset="-1" value="l"> <input class="turn" type="button" data-offset="1" value="r"></td>
<td>&nbsp;</td>
</tr>

<tr>
<td colspan="4">&nbsp;</td>
</tr>

<tr>
<td colspan="4">You can change outfit<br> and skin colors in-game.</td>
</tr>

</table>


<div style="float:left; width: 50%; padding-top: 2em">
<input id="outfitcode" name="outfitcode" type="hidden" value="01010101">
<label for="name" >Name: </label><input id="name" name="name" type="text" maxlength="20"
<?php
	if (isset($_REQUEST['name'])) {
		echo 'value="'.htmlspecialchars($_REQUEST['name']).'"';
	} else {
		$username = Account::convertToValidUsername($_SESSION['account']->username);
		// don't suggest charnames based on the long ugly openid identifiers of google and yahoo
		if (strlen($username) < 20 && !doesCharacterExist($username)) {
			echo 'value="'.htmlspecialchars($username).'"';
		}
	}
?>>
<div id="warn" class="warn">&nbsp;</div>
<input name="submit" style="margin-top: 2em" type="submit" value="Create Character">
<input id="currentOutfit" name="currentOutfit" type="hidden" value = "<?php
if (isset($_REQUEST['outfitcode'])) {
    echo htmlspecialchars($_REQUEST['outfitcode']);
}
?>">
<br>
<br>
<br>
<br>
<?php 
$players = getCharactersForUsername($_SESSION['account']->username);
if(sizeof($players)==0) {
?>
<p>Already have an account? <b><a href="<?php echo rewriteURL('/account/merge.html')?>">Link accounts...</a></b></p>
<?php
}
?>
<input id="sessionUsername" type="hidden" value="<?php echo htmlspecialchars($_SESSION['account']->username);?>">
<input id="serverpath" name="serverpath" type="hidden" value="<?php echo STENDHAL_FOLDER;?>">
</div>
</form>
<?php
		endBox();
		$this->includeJs();
	}

}

$page = new CreateCharacterPage();
