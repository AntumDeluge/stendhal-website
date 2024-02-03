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

class StarterPage extends Page {
	private $loggedIn;
	private $username;
	private $characterOkay = FALSE;
	private $character;
	private $seed;

	public function __construct() {
		$this->loggedIn = false;
		$this->characterOkay = false;
		if (isset($_SESSION['account']->username)) {
			$this->username = $_SESSION['account']->username;
			$this->loggedIn = true;
			$this->character = $_REQUEST['character'];
			if ($this->loggedIn && isset($this->character) && strlen($this->character) > 0) {
				$this->characterOkay = verifyCharacterBelongsToUsername($this->username, $this->character);
			}
		}
	}

	public function writeHttpHeader() {
		header('Cache-Control: must-revalidate, private');
		header('Pragma: cache', true);

		// if everything is okay, we proceed with the login process
		if ($this->loggedIn && $this->characterOkay) {
			$this->createSeed();
			$this->streamWebstart();
			// don't render the normal web page
			return false;
		}

		// something went wrong, render the normal web page
		// and add an error message later
		return true;
	}

	public function writeHtmlHeader() {
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<title>Starter'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		startBox("<h1>Starter</h1>");
		if(!isset($_SESSION['account'])) {
			echo '<p>Please <a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url='.rewriteURL('/account/mycharacters.html').'">login</a> to start the Stendhal Client.</p>';
		} else if (!$this->characterOkay) {
			echo '<p>The specified character '.htmlspecialchars($this->character).' does not belong to your account.</p>';
		} else {
			echo '<p>An unknown error occured.</p>';
		}
		endBox();
	}

	/**
	 * creates and stores a seed.
	 */
	private function createSeed() {
		$this->seed = createRandomString(16);
		storeSeed($this->username, $_SERVER['REMOTE_ADDR'], $this->seed, 1);
	}

	private function streamWebstart() {
		$version = '1.20';
		if (isset($_REQUEST['test'])) {
			$version = '1.20';
		}
		header('Content-Type: application/x-java-jnlp-file', true);
		echo '<?xml version="1.0" encoding="utf-8"?>
<jnlp spec="1.0+" codebase="https://arianne-project.org/jws">
	<information>
		<title>Stendhal</title>
		<vendor>The Arianne Project</vendor>
		<homepage href="https://arianne-project.org"/>
		<description>Are you looking for adventure? Want to fight for riches? Develop yourself and your social standing? Meet new people? Do you want to be part of a brave new world?

Stendhal is a fully fledged multiplayer online adventures game (MMORPG) developed using the Arianne game development system.

Stendhal features a new, rich and expanding world in which you can explore towns, buildings, plains, caves and dungeons. You will meet NPCs and acquire tasks and quests for valuable experience and cold hard cash. Your character will develop and grow and with each new level up become stronger and better. With the money you acquire you can buy new items and improve your armour and weapons. And for the blood thirsty ones of you; satisfy your killing desires by roaming the world in search of evil monsters!

So what are you waiting for?! A whole new world awaits...</description>
		<description kind="short">A multiplayer online adventures game</description>
		<icon href="https://arianne-project.org/images/stendhal_webstart_icon.png"/>
		<icon kind="splash" href="https://arianne-project.org//images/stendhal_splash.jpg"/>
	</information>
	<security>
		<all-permissions/>
	</security>
	<resources>
		<j2se href="http://java.sun.com/products/autodl/j2se" version="1.7+" max-heap-size="200m" />
		<jar href="https://arianne-project.org/jws/stendhal-starter-'.$version.'.jar" download="eager" main="true" />
	</resources>
	<application-desc>
		<argument>-h</argument><argument>'.STENDHAL_SERVER_NAME.'</argument>
		<argument>-u</argument><argument>'.$this->username.'</argument>
		<argument>-c</argument><argument>'.$this->character.'</argument>
		<argument>-p</argument><argument>32160</argument>
		<argument>-S</argument><argument>'.$this->seed.'</argument>
	</application-desc>
</jnlp>';
	}
}

$page = new StarterPage();
