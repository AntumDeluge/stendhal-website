<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2024 The Arianne Project

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


class StendhalFrame extends PageFrame {

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
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	function writeHttpHeader($page_url) {
		return true;
	}

	/**
	 * this method can write additional html headers.
	 */
	function writeHtmlHeader() {
		echo '<link rel="icon" type="image/png" href="'.STENDHAL_FOLDER.'/StendhalIcon.png">';
		echo '<meta name="viewport" content="width=device-width, initial-scale=1" />';
		echo '<link rel="manifest" href="/manifest.json">';
		echo '<link rel="me" href="https://mastodon.social/@stendhalgame">';
	}

	/**
	 * renders the frame
	 */
	function renderFrame() {
		global $page;
		$showFrame = (!isset($_SERVER['HTTP_USER_AGENT']) || 
			(
				(strpos($_SERVER['HTTP_USER_AGENT'], 'Stendhal') === false)
				&& (strpos($_SERVER['HTTP_USER_AGENT'], '; wv') === false)
			)) && !isset($_REQUEST['build']);
		echo '<body lang="en" '. $page->getBodyTagAttributes() . ' class="layout">';

?>
<div id="container">
<?php if ($showFrame) { ?>
	<div id="header">
		<?php
		echo '<a href="/" class="stendhallogo"><img style="border: 0;" src="/images/logo.gif" alt="Stendhal Logo" width="282" height="64" ></a>';
		echo '<form id="headersearchform" action="'.rewriteURL('/search').'" method="GET">';
		if (!STENDHAL_MODE_REWRITE) {
			echo '<input type="hidden" name="id" value="content/game/search">';
		}
		echo '<div>';
		echo '<input id="headersearchforminput" name="q" id="q" type="search" placeholder="Search"><button aria-label="Search"><img src="/images/search.svg" alt="" width="13" height="13"></button>';
		echo '</div>';

		echo '<a href="'.STENDHAL_LOGIN_TARGET.'/account/mycharacters.html"><span class="block" id="playArea"></span></a>';
		echo '<a href="https://arianne-project.org/download/stendhal.zip"><span class="block" id="downloadArea"></span></a>';
		echo '</form>';
		?>
 		</div>

	<div id="topMenu">
	<?php
		$this->navigationMenu($page);
		$this->breadcrubs($page);
	?>
	</div>
<?php } else if (checkLogin() && parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) != '/account/mycharacters.html') { ?>
	<div>
	<?php echo '		<a id="mychars-button" href="'.STENDHAL_LOGIN_TARGET.'/account/mycharacters.html" class="noselect" draggable="false">My Characters</a>'; ?>

	</div>
<?php } ?>

	<div id="contentArea">
		<?php
			// The central area of the website.
			$page->writeContent();
		?>
	</div>

<?php if ($showFrame) { ?>

	<div id="footerArea">
		<span class="copyright">&copy; 1999-2024 <a href="https://arianne-project.org">Arianne Project</a></span>
	</div>

	<div class="time">
		<?php
		// Show the server time
		echo 'Server time: '.date('G:i');
		?>
	</div>

<?php } ?>


</div>

<div id="popup">
<a id="popupClose">x</a>
<div id="popupContent"></div>
</div>
<div id="backgroundPopup"></div>

<?php

if (defined('STENDHAL_MOUSE_FLOATING_IMAGE_ON_TOP_OF_BOXES')) {
	echo '<img id="mousefloatingimageontopofboxes" src="'
		. urldecode(STENDHAL_MOUSE_FLOATING_IMAGE_ON_TOP_OF_BOXES)
		. '" data-offset="'.STENDHAL_MOUSE_FLOATING_IMAGE_ON_TOP_OF_BOXES_OFFSET
		. '" data-sound="'.STENDHAL_MOUSE_FLOATING_IMAGE_SOUND.'">';
}
$this->includeJs();
$page->writeAfterJS();
?>
</body>
</html>

<?php
		}

	function navigationMenu($page) {

		$noclick = 'class="noclick" ';

		// http://www.silent-fran.de/css/tutorial/aufklappmenue.html
		echo '<nav><ul class="navigation">';

		echo '<li><a '.$noclick.'href="/media.html">Media</a><ul>';
			echo '<li><a id="menuNewsArchive" href="/news.html">News</a>';
			echo '<li><a id="menuMediaScreenshot" href="/media/screenshots.html">Screenshots</a>';
			echo '<li><a id="menuMediaVideo" href="/media/videos.html">Videos</a>';
			echo '<li><a id="menuContribDownload" href="/download.html">Downloads</a></ul>';

		echo '<li><a '.$noclick.'href="/world.html">World Guide</a><ul>';
			echo '<li><a id="menuAtlas" href="/world/atlas.html">Map</a>';
			echo '<li><a id="menuRegion" href="/region.html">Regions</a>';
			echo '<li><a id="menuDungeons" href="/dungeon.html">Dungeons</a>';
			echo '<li><a id="menuNPCs" href="/npc/">NPCs</a>';
			echo '<li><a id="menuQuests" href="/quest.html">Quests</a>';
			echo '<li><a id="menuAchievements" href="/achievement.html">Achievements</a>';
			echo '<li><a id="menuCreatures" href="/creature/">Creatures</a>';
			echo '<li><a id="menuItems" href="/item/">Items</a></ul>';

		echo '<li><a '.$noclick.'href="/player-guide.html">Player\'s Guide</a><ul>';
			echo '<li><a id="menuHelpManual" href="/wiki/Stendhal_Manual">Manual</a>';
			echo '<li><a id="menuHelpFAQ" href="/player-guide/faq.html">FAQ</a>';
			echo '<li><a id="menuHelpBeginner" href="/player-guide/beginner-guide.html">Beginner\'s Guide</a>';
			echo '<li><a id="menuHelpAsk" href="/player-guide/ask-for-help.html">Ask For Help</a>';
			// echo '<li><a id="menuHelpSupport" href="https://sourceforge.net/p/arianne/support-requests/new/">Support Ticket</a>';
			// echo '<li><a id="menuHelpForum" href="https://sourceforge.net/p/arianne/discussion/">Forum</a>';
			echo '<li><a id="menuHelpRules" href="/player-guide/rules.html">Rules</a></ul>';

		echo '<li><a '.$noclick.'href="/community.html">Community</a><ul>';
			echo '<li><a id="menuHelpChat" href="/chat/">Chat</a>';
			echo '<li><a id="menuPlayerOnline" href="/world/online.html">Online players</a>';
			echo '<li><a id="menuPlayerHalloffame" href="/world/hall-of-fame/active_overview.html">Hall Of Fame</a>';
			echo '<li><a id="menuPlayerEvents" href="/world/events.html">Recent Events</a>';
			echo '<li><a id="menuPlayerKillstats" href="/world/kill-stats.html">Kill stats</a>';
			echo '<li><a id="menuPlayerTrade" href="/trade/">Player trades</a></ul>';

		echo '<li><a '.$noclick.'href="/development.html">Development</a><ul>';
			echo '<li><a id="menuContribDevelopment" href="/development/introduction.html">Introduction</a>';
			echo '<li><a id="menuContribChat" href="/chat/">Chat</a>';
			echo '<li><a id="menuContribWiki" href="/wiki/Stendhal">Wiki</a>';
			// TODO: move these to wiki pages
			echo '<li><a id="menuContribBugs" href="/development/bug.html">Report Bug</a>';
			echo '<li><a id="menuContribRequests" href="/development/feature.html">Suggest Feature</a>';
			echo '<li><a id="menuContribHelp" href="/development/patch.html">Submit Patch</a>';
			echo '<li><a id="menuContribQuests" href="/wiki/Stendhal_Quest_Contribution">Quests</a>';
			echo '<li><a id="menuContribTesting" href="/wiki/Stendhal_Testing">Testing</a>';
			echo '<li><a id="menuContribHistory" href="/development/sourcelog.html">Changes</a>';
			echo '<li><a id="menuAPI" href="/reference/">API Reference</a></ul>';

		$adminLevel = getAdminLevel();
		if ($adminLevel >= 100) {
			echo '<li><a>A</a><ul>';
			if ($adminLevel >= 400) {
				echo '<li><a id="menuAdminNews" href="/?id=content/admin/news">News</a>';
				echo '<li><a id="menuAdminDataExplorer" href="/?id=content/admin/data">Data Explorer</a>';
			}
			echo '<li><a id="menuAdminInspect" href="/?id=content/admin/inspect">Render Inspect</a>';
			echo '<li><a id="menuAdminSupportlog" href="/?id=content/admin/logs">Support Logs</a>';
			echo '<li><a id="menuAdminPlayerhistory" href="/?id=content/admin/playerhistory">Player History</a></ul>';
		}

		if (checkLogin()) {
			$messageCount = StoredMessage::getCountUndeliveredMessages($_SESSION['account']->id, "characters.charname = postman.target AND deleted != 'R'");
			echo '<li><a '.$noclick.'href="/account/myaccount.html">'.htmlspecialchars(substr($_SESSION['account']->username, 0, 10)).'</a><ul>';
			echo '<li><a id="menuAccountCharacters" href="/account/mycharacters.html">My Characters</a>';
			echo '<li><a id="menuAccountMessages" href="/account/messages.html">Messages ('.htmlspecialchars($messageCount).')</a>';
			echo '<li><a id="menuAccountHistory" href="/account/history.html">Login History</a>';
			echo '<li><a id="menuAccountEMail" href="/account/email.html">Mail address</a>';
			echo '<li><a id="menuAccountPassword" href="/account/change-password.html">New Password</a>';
			echo '<li><a id="menuAccountMerge" href="/account/merge.html">Link Accounts</a>';
			echo '<li><a id="menuAccountLogout" href="/account/logout.html">Logout</a></ul>';
		} else {
			echo '<li><a href="'.STENDHAL_LOGIN_TARGET.'/account/login.html">Login</a></li>';
		}

		echo '</ul></nav>';
	}

	function breadcrubs($page) {
		echo '<div class="small_notice breadcrumbs" xmlns:v="http://rdf.data-vocabulary.org/#">';
		$breadcrumbs = $page->getBreadCrumbs();
		if (isset($breadcrumbs)) {
			echo '<a href="https://stendhalgame.org">Stendhal</a> ';
			for ($i = 0; $i < count($breadcrumbs) / 2; $i++) {
				echo '&gt; <span typeof="v:Breadcrumb"><a href="'.surlencode($breadcrumbs[$i * 2 + 1]).'" rel="v:url" property="v:title">';
				echo htmlspecialchars($breadcrumbs[$i * 2]);
				echo '</a></span> ';
			}
		} else {
			echo '&nbsp;';
		}
		echo '</div>';
	}
}
$frame = new StendhalFrame();
