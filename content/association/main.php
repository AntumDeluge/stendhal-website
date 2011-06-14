<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2009  Miguel Angel Blanch Lardin, The Arianne Project

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

class MainPage extends Page {
	private $cmsPage;
	public function __construct() {
		global $lang;
		$this->cmsPage = CMS::readNewestVersion($lang, $_REQUEST['title']);
	}

	public function writeHttpHeader() {
		if ($this->cmsPage == null) {
			header('HTTP/1.1 404 Not Found');
		}
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>'.substr(STENDHAL_TITLE, strpos(STENDHAL_TITLE, ' ', 2) + 1).'</title>'."\n";
		// TODO: echo '<link rel="alternate" type="application/rss+xml" title="Stendhal News" href="'.rewriteURL('/rss/news.rss').'" >'."\n";
		// TODO: echo '<meta name="keywords" content="Stendhal, game, gra, Spiel, Rollenspiel, juego, role, gioco, online, open, source, multiplayer, roleplaying, Arianne, foss, floss, Adventurespiel">';
		// TODO: echo '<meta name="description" content="Stendhal is a fully fledged free open source multiplayer online adventures game (MORPG) developed using the Arianne game system.">';
	}

	function writeContent() {
		global $lang;
		$title = $_REQUEST['title'];
		if ($title == '') {
			$title = 'Faiumoni';
		}
		startBox(htmlspecialchars(ucfirst($title)));
		if ($this->cmsPage != null) {
			echo $this->cmsPage->content;
			echo '<div class="versionInformation">'.t('Last edited on').' '
				. htmlspecialchars($this->cmsPage->timedate)
				.' '.t('by account').' '.htmlspecialchars($this->cmsPage->accountId);
			if ($_SESSION['account']) {
				echo ' - <a href="/?id=content/association/edit&amp;lang='.urlencode($lang).'&amp;title='.urlencode($_REQUEST['title']).'">'.t('edit').'</a>';
			}
			echo '</div>';
		} else {
			echo '<p>'.t('Sorry, the requested page does not exist.').'</p>';
			if ($_SESSION['account']) {
				echo '<a href="/?id=content/association/edit&amp;lang='.urlencode($lang).'&amp;title='.urlencode($_REQUEST['title']).'">'.t('create it').'</a>';
			}
		}
		endBox();
	}
}
$page = new MainPage();
?>