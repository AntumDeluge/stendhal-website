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

class MyCharactersPage extends Page {

	public function writeHtmlHeader() {
		echo '<meta name="robots" content="noindex">'."\n";
		echo '<title>Starter'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		startBox("Starter");
		if(!isset($_SESSION['username'])) {
			echo '<p>Please login to see a list of your characters.</p>';
		} else {
		
			$players = getCharactersForUsername($_SESSION['username']);
			if(sizeof($players)==0) {
				echo 'You have no characters.';
			}
			foreach($players as $p) {
				echo '<div class="onlinePlayer">';
				echo '  <a href="'.rewriteURL('/character/'.surlencode($p->name).'.html').'">';
				echo '  <img src="'.rewriteURL('/images/outfit/'.surlencode($p->outfit).'.png').'" alt="">';
				echo '  <span class="block">'.htmlspecialchars(utf8_encode($p->name)).'</span></a>';
				echo '</div>';
			}
		}
		endBox();
	}
}
$page = new MyCharactersPage();
?>