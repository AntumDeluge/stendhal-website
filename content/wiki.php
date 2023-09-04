<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2014-2023  The Arianne Project

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

class WikiPage extends Page {
	private $wiki;
	private $pageTitle;
	private $url;

	public function __construct() {
		$this->wiki = new Wiki($_REQUEST["title"]);
		$temp = $this->wiki->findPage();
		if (!isset($temp)) {
			return;
		}
		$this->url = $temp['title'];
		$this->pageTitle =  $temp['displaytitle'];
	}


	public function writeHttpHeader() {
		if (!isset($this->pageTitle)) {
			header('HTTP/1.1 404');
		}
		return true;
	}

	public function writeHtmlHeader() {
		if (!isset($this->pageTitle)) {
			echo '<meta name="robots" content="noindex">';
			echo '<title>Page not found'.STENDHAL_TITLE.'</title>';
		} else {
			echo '<title>'.htmlspecialchars($this->pageTitle).STENDHAL_TITLE.'</title>';
		}
	}

	function writeContent() {
		if (!isset($this->pageTitle)) {
			$this->write404();
		} else {

			startBox('<h1>'.htmlspecialchars($this->pageTitle).'</h1>');
			echo $this->wiki->render();
			endBox();

			if (strpos($_REQUEST["title"], '/', 1) !== false) {
				startBox('<h2>Contribute</h2>');
				echo '<p>You can edit this pages because it is imported from the Stendhal Wiki.</p>';
				echo '<ul><li><a rel="nofollow" href="https://stendhalgame.org/w/index.php?title='.surlencode($this->url).'&action=edit">Edit this page</a>';
				echo '<li><a rel="nofollow" href="https://stendhalgame.org/w/index.php?title='.surlencode($this->url).'&action=history">History and authors</a>';
				endBox();
			}
		}
	}

	function write404() {
		startBox('<h1>Not found</h1>');
		echo '<p><img src="/data/sprites/signs/signpost.png" alt="">We are sorry, the requested page does not exist.</p>';
		endBox();
	}

	public function getBreadCrumbs() {
		if (!isset($this->pageTitle)) {
			return null;
		}

		$categories = $this->wiki->getCategories();

		echo '<!-- Categories: ';
		var_dump($categories);
		echo '-->';

		if (in_array('Stendhal_Quest', $categories)) {
			if ($_REQUEST["title"] == '/quest.html') {
				return array('World Guide', '/world.html', 'Quest', '/quest.html');
			} else {
				return array('World Guide', '/world.html', 'Quest', '/quest.html', $this->pageTitle, $_REQUEST["title"]);
			}
		}

		if (in_array('Stendhal_Dungeon', $categories)) {
			if ($_REQUEST["title"] == '/dungeon.html') {
				return array('World Guide', '/world.html', 'Dungeon', '/dungeon.html');
			} else {
				return array('World Guide', '/world.html', 'Dungeon', '/dungeon.html', $this->pageTitle, $_REQUEST["title"]);
			}
		}

		if (in_array('Stendhal_Region', $categories) || in_array('Stendhal_Place', $categories)) {
			if ($_REQUEST["title"] == '/region.html') {
				return array('World Guide', '/world.html', 'Region', '/region.html');
			} else {
				return array('World Guide', '/world.html', 'Region', '/region.html', $this->pageTitle, $_REQUEST["title"]);
			}
		}

		if (in_array('Stendhal_Player\'s_Guide', $categories)) {
			if ($_REQUEST["title"] == '/player-guide.html') {
				return array('Player\'s Guide', '/player-guide.html');
			} else {
				return array('Player\'s Guide', '/player-guide.html', $this->pageTitle, $_REQUEST["title"]);
			}
		}
		return null;
	}
}
$page = new WikiPage();
