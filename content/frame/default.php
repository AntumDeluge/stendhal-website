<?php
/*
 Copyright (C) 2011-2023 Faiumoni e. V.

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

class DefaultFrame extends PageFrame {

	/**
	 * gets the default page in case none is specified.
	 *
	 * @return string name of default page
	 */
	function getDefaultPage() {
		return 'content/main';
	}

	/**
	 * this method can write additional http headers, for example for cache control.
	 *
	 * @param $page_url
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	function writeHttpHeader($page_url) {
		return true;
	}

	/**
	 * this method can write additional html headers.
	 */
	function writeHtmlHeader() {
		echo '<link rel="icon" type="image/x-icon" href="'.STENDHAL_FOLDER.'/favicon.ico">';
	}

	/**
	 * renders the frame
	 */
	function renderFrame() {
		global $page;
?>
<body>
<div id="container">

	<div id="topMenu"></div>

	<div id="leftArea">

		<?php
		startBox('Game System');
			echo '<ul id="gamemenu" class="menu">';
			echo '<li><a id="menuAtlas" href="https://stendhalgame.org/wiki/StendhalAtlas">Atlas</a></li>'."\n";
			echo '</ul>';
		endBox();

		startBox('Help'); ?>
		<ul id="helpmenu" class="menu">
			<li><a id="menuHelpManual" href="https://stendhalgame.org/wiki/StendhalManual">Manual</a></li>
		</ul>
		<?php endBox() ?>

	</div>

	<div id="rightArea">


		<?php startBox('Contribute'); ?>
		<ul id="contribmenu" class="menu">
			<?php
			echo '<li><a id="menuContribWiki" href="https://stendhalgame.org/wiki/Stendhal">Wiki</a></li>'."\n";
			?>
		</ul>
		<?php endBox(); ?>
	</div>

	<div id="contentArea">


		<?php

			startBox('Setup instructions.');
			echo '<p> This is the default navigation frame.</p><p>You can create your own file in content/frame and specify it in the STENDHAL_FRAME variable in configuration.php.</p>';
			endBox();


			// The central area of the website.
			$page->writeContent();
		?>
	</div>

	<div id="footerArea">
		<span class="copyright">&copy; 1999-2023 <a href="https://arianne-project.org">Arianne Project</a></span>
		<span><a id="footerSourceforge" href="http://sourceforge.net/projects/arianne">&nbsp;</a></span>
	</div>

	<div class="time">
		<?php
		// Show the server time
		echo 'Server time: '.date('G:i');
		?>
	</div>
</div>
<?php $this->includeJs();?>
</body>
</html>
<?php
	}
}
$frame = new DefaultFrame();
