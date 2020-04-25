<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2010-2020 the Arianne Project

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


class NewsPage extends Page {
	private $news;

	public function __construct() {
		$newsId = NewsPage::getNewsIdFromUrl();
		if ($newsId <= 0) {
			header('HTTP/1.0 404 Not found', true, 404);
			return;
		}

		// read the news posting from the database
		$newsList = getNews(" where news.id='".mysql_real_escape_string($newsId)."' AND news.active=1");
		if (sizeof($newsList) == 0) {
			header('HTTP/1.0 404 Not found', true, 404);
		}

		$this->news = $newsList[0];
		return;
	}

	/**
	 * extracts the id from the nice url
	 *
	 * @return int id
	 */
	function getNewsIdFromUrl() {
		$url = $_GET['news'];

		if (is_numeric($url)) {
			return abs(intval($url));
		}

		$pos = strrpos($url, '-');
		$id = substr($url, $pos + 1);
		$pos = strpos($id, '.');

		// remove optional .html suffix
		if ($pos !== FALSE) {
			$id = substr($id, 0, $pos);
		}
		return intval($id);
	}

	public function writeHttpHeader() {
		global $protocol;
		if (isset($this->news)) {
			$niceUrl = $this->news->getNiceURL();
			if ($niceUrl != $_GET['news']) {
				header('HTTP/1.0 301 Moved permanently.');
				header("Location: ".$protocol."://".$_SERVER['HTTP_HOST'].preg_replace("/&amp;/", "&", rewriteURL('/news/'.$niceUrl)));
				return false;
			}
		}
		return true;
	}

	public function writeHtmlHeader() {
		if (isset($this->news)) {
			$filteredDescription = $this->filterAndTrim($this->news->extendedDescription);
			echo '<title>'.htmlspecialchars($this->news->title).STENDHAL_TITLE.'</title>';
			echo '<meta name="description" content="'.htmlspecialchars($filteredDescription).'">';
			echo '<meta name="title" content="'.htmlspecialchars($this->news->title).'">';
			echo '<meta name="og:titel" content="'.htmlspecialchars($this->news->title).'">';
			// Images must be at least 50 pixels by 50 pixels.
			// echo '<meta name="og:image" content="'.htmlspecialchars($this->news->typeImage).'">';
			echo '<meta name="og:image" content="/images/thumbnail/screenshots/client87.png">';
			echo '<meta name="og:site_name" content="Stendhal Game">';
			echo '<meta name="og:type" content="game">';
			echo '<meta name="fb:admins" content="500472240">';
			echo '<meta name="fb:app_id" content="354745201216560">';
		} else {
			echo '<title>News Not Found'.STENDHAL_TITLE.'</title>';
		}
	}

	private function filterAndTrim($description='') {
		$description = trim($description);
		$pos = strpos($description, '<p>', 10);
		if ($pos) {
			$description = substr($description, 0, $pos);
		}
		$description = preg_replace('/(\r\n|\r|\n|")/s',' ', preg_replace('/<[^>]*>/', '', $description));
		return $description;
	}

	function writeContent() {
		?>

<div id="newsArea"><?php
if (isset($this->news)) {
	$this->news->show(true);
} else {
	startBox('News');
	echo 'Not Found';
	endBox();
}
?>
<div><?php startBox('News Archive');
echo 'Read <a href="'.rewriteURL('/world/newsarchive.html').'">older news</a>.';
endBox();
?></div>
</div>
<?php
	}

	public function getBreadCrumbs() {
		if (isset($this->news)) {
			return array(
			    'Media', '/media.html',
			    'News', '/world/newsarchive.html',
			    htmlspecialchars(substr($this->news->title, 0, 100)), $this->news->getNiceURL());
		}
	}
}

$page = new NewsPage();
