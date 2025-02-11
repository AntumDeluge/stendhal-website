<?php
/*
 Copyright (C) 2011 Faiumoni

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
	private $title;

	public function __construct() {
		global $lang, $internalTitle;
		$this->cmsPage = CMS::readNewestVersion($lang, $internalTitle);
		if ($this->title == '') {
			$this->title = 'Faiumoni';
		}
		if ($this->cmsPage != null) {
			$this->title = $this->cmsPage->displaytitle;
		}
	}

	public function writeHttpHeader() {
		global $protocol, $lang;
		if ($this->cmsPage == null) {
			header('HTTP/1.1 404 Not Found');
		}
		if ((strpos($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'de') !== 0) && (isset($_REQUEST['title']) && $_REQUEST['title'] == 'start') && ($lang == 'en')) {
			header("Location: ".$protocol."://".$_SERVER['HTTP_HOST']);
			return false;
		}
		return true;
	}

	public function writeHtmlHeader() {
		global $lang;
		echo '<title>'.htmlspecialchars($this->title).STENDHAL_TITLE.'</title>'."\n";
		if (isset($_REQUEST['title']) && ($_REQUEST['title'] == 'start') && ($lang == 'en')) {
			echo '<meta name="robots" content="noindex">'."\n";
		}
		// TODO: echo '<link rel="alternate" type="application/rss+xml" title="Stendhal News" href="'.rewriteURL('/rss/news.rss').'" >'."\n";
		// TODO: echo '<meta name="keywords" content="Stendhal, game, gra, Spiel, Rollenspiel, juego, role, gioco, online, open, source, multiplayer, roleplaying, Arianne, foss, floss, Adventurespiel">';
		// TODO: echo '<meta name="description" content="Stendhal is a fully fledged free open source multiplayer online adventures game (MORPG) developed using the Arianne game system.">';
	}

	function writeContent() {
		global $lang;
		startBox(htmlspecialchars(ucfirst($this->title)));
		if ($this->cmsPage != null) {

			// Hack for paypal button which does not survive phppurifier
			$buttonId = array();
			$buttonId['de'] = '923QB6YHDJNBQ';
			$buttonId['en'] = 'Q6XS4FATCTR2S';

			$alternativeDonate = '<h3>Alternativ</h3>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" style="float:right; margin-left:1em">
<p style="margin:0 0 0.5em 1.5em">PayPal</p>
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="'.$buttonId[$lang].'">
<input type="image" src="/images/association/paypal_'.$lang.'_button.gif" border="0" name="submit" alt="PayPal.">
</form>
'; // <a href="http://flattr.com/thing/333510/Faiumoni-e-V-"><img alt="Flattr" src="/images/association/flattrbutton.png" width="50" height="60"></a>';


			$text = $this->cmsPage->content;
			$text = preg_replace('/\[ALTERNATIVE_DONATE\]/', $alternativeDonate, $text);
			echo $text;

			// write version information footer
			echo '<div class="versionInformation">'.t('Last edited on').' '
				. htmlspecialchars($this->cmsPage->timedate)
				.' '.t('by account').' '.htmlspecialchars($this->cmsPage->accountId);

			if (isset($_SESSION) && isset($_SESSION['account'])) {
				echo ' - <a href="/?id=content/association/edit&amp;lang='.surlencode($lang).'&amp;title='.urlencode($_REQUEST['title']).'">'.t('edit').'</a>';
			}

			$username = t('Guest');
			if (isset($_SESSION) && isset($_SESSION['account'])) {
				$username = $_SESSION['account']->username;
			}
			echo ' <a style="text-decoration:none; color:#000" href="https://faiumoni.de/?id=content/association/login">&Pi;</a>&nbsp;&nbsp;';

			echo '</div>';
		} else {
			echo '<p>'.t('Sorry, the requested page does not exist.').'</p>';
			if (isset($_SESSION) && isset($_SESSION['account'])) {
				echo '<a href="/?id=content/association/edit&amp;lang='.urlencode($lang).'&amp;title='.surlencode($_REQUEST['title']).'">'.t('create it').'</a>';
			}
		}
		endBox();
	}
}
$page = new MainPage();
