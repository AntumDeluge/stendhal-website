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

if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == "on")) {
	$protocol = 'https';
	ini_set('session.cookie_secure', 1);
	session_start();
} else {
	$protocol = 'http';
}


require_once('scripts/website.php');
require_once('scripts/account.php');
require_once('scripts/authors.php');
require_once('scripts/urlrewrite.php');

/*
 * Open connection to both databases.
 */
connect();

/**
 * Scan the name module to load and reset it to safe default if something strange appears.
 *
 * @param string $url The name of the module to load without .php
 * @return string the name of the module to load.
 */
function decidePageToLoad($url) {
  $ERROR="content/main";
  
  if(strpos($url,".")!==false) {
    return $ERROR;
  }
  
  if(strpos($url,"//")!==false) {
    return $ERROR;
  }
  
  if(strpos($url,":")!==false) { // http://, https://, ftp://
    return $ERROR;
  }
  
  if(strpos($url,"/")==0) {
    return $ERROR;
  }
  
  if(strpos($url.'.php',".php")===false) {
    return $ERROR;
  }
 
  if(!file_exists($url.'.php')) {
    return $ERROR;
  }
  
  return $url;	
}

/*
 * This code decide the page to load.
 */ 
$page_url="content/main";
if(isset($_REQUEST["id"]))
  {  
  $page_url=decidePageToLoad($_REQUEST["id"]);  
  }

require_once("content/page.php");
require_once($page_url.'.php');

$folder = "";

if ($page->writeHttpHeader()) {
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
	<link rel="stylesheet" type="text/css" href="<?echo $folder;?>/css/00000002.css">
	<link rel="icon" type="image/png" href="<?echo $folder;?>/favicon.ico">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<?php
		/*
		 * Does another style sheet for this page exists?
		 * Yes? Load it.
		 */
		if(file_exists($page_url.'.css')) {
			?>
			<link rel="stylesheet" type="text/css" href="/<?php echo $page_url; ?>.css">
			<?php
		}
		$page->writeHtmlHeader();
		if (!defined("STENDHAL_WANT_ROBOTS") || !constant("STENDHAL_WANT_ROBOTS")) {
			echo '<meta name="robots" content="noindex">'."\n";
		}
	?>
	</head>

<body>
<div id="container">
	<div id="header">
		<a href="/"><img style="border: 0;" src="/images/logo.gif" title="Stendhal Logo" alt="The Stendhal logo shows the word &quot;Stendhal&quot;in large blue letters."></a>
	</div>

	<div id="account">
		<?php displayLogin(); ?>
	</div>

	<div id="topMenu"></div>

	<div id="leftArea">
		<?php 
		startBox('Screenshot');
		// Return the latest screenshot added to the webpage.
		$screen=getLatestScreenshot();
		?>
		<a href="<? echo rewriteURL('/images/image/'.htmlspecialchars($screen->url)); ?>">
			<?php $screen->showThumbnail(); ?>
		</a>
		<?php endBox() ?>

		<?php startBox('Movie'); ?>
			<a href="<?php echo $protocol;?>://stendhalgame.org/wiki/Stendhal_Videos"><img src="/images/video.jpeg" width="99%" style="border: 0;" title="Stendhal videos &amp; video tutorials" alt="A screenshot of Stendhal in Semos Bank with a bank chest window open showing lots if items. In the middle of the screenshow a semitransparent play-icon is painted, indicating this image links to a video."></a>
		<?php endBox() ?>

		<?php startBox('Game System'); ?>
		<ul id="gamemenu" class="menu">
			<?php 
			echo '<li><a href="'.$protocol.'://stendhalgame.org/wiki/StendhalAtlas"><img src="/images/buttons/atlas_button.png" alt="">Atlas</a></li>'."\n";
			echo '<li><a id="menuNPCs" href="'.rewriteURL('/npc/').'">NPCs</a></li>'."\n";
			echo '<li><a href="'.rewriteURL('/creature/').'"><img src="/images/buttons/creatures_button.png" alt="">Creatures</a></li>'."\n";
			echo '<li><a href="'.rewriteURL('/item/').'"><img src="/images/buttons/items_button.png" alt="">Items</a></li>'."\n";
			?>
			<li><a href="<?php echo $protocol;?>://stendhalgame.org/wiki/StendhalQuest"><img src="/images/buttons/quests_button.png" alt="">Quests</a></li>
			<li><a href="<?php echo $protocol;?>://stendhalgame.org/wiki/StendhalHistory"><img src="/images/buttons/history_button.png" alt="">History</a></li>
		</ul>
		<?php endBox(); ?>

		<?php startBox('Help'); ?>
		<ul id="helpmenu" class="menu">
			<li><a href="<?php echo $protocol;?>://stendhalgame.org/wiki/StendhalManual"><img src="/images/buttons/manual_button.png" alt="">Manual</a></li>
			<li><a href="<?php echo $protocol;?>://stendhalgame.org/wiki/StendhalFAQ"><img src="/images/buttons/faq_button.png" alt="">FAQ</a></li>
			<li><a href="<?php echo $protocol;?>://stendhalgame.org/wiki/BeginnersGuide"><img src="/images/buttons/beginner_button.png" alt="">Beginner's Guide</a></li>
			<li><a href="<?php echo $protocol;?>://stendhalgame.org/wiki/AskForHelp"><img src="/images/buttons/help_button.png" alt="">Ask For Help</a></li>
			<li><a href="<?php echo rewriteURL('/chat/');?>"><img src="/images/buttons/c_chat_button.png" alt="">Chat</a></li>
			<li><a href="<?php echo $protocol;?>://sourceforge.net/tracker/?func=add&amp;group_id=1111&amp;atid=201111"><img src="/images/buttons/support_button.png" alt="">Support Ticket</a></li>
			<li><a href="<?php echo $protocol;?>://sourceforge.net/forum/forum.php?forum_id=3190"><img src="/images/buttons/forum_button.png" alt="">Forum</a></li>
			<li><a href="<?php echo $protocol;?>://stendhalgame.org/wiki/StendhalRuleSystem"><img src="/images/buttons/rules_button.png" alt="">Rules</a></li>
		</ul>
		<?php endBox() ?>

	</div>

	<div id="rightArea">
		<script src="/css/00000001.js"></script>

		<a href="#" onclick='javascript:webstart("1.5.0", "http://arianne.sourceforge.net/jws/stendhal.jnlp"); return false'><span class="block" id="playArea"></span></a>
		<a href="http://downloads.sourceforge.net/arianne/stendhal-FULL-<?php echo STENDHAL_VERSION; ?>.zip"><span class="block" id="downloadArea"></span></a>


		<?php 
		startBox('Best Player');
		$player=getBestPlayer(REMOVE_ADMINS_AND_POSTMAN);
		if($player!=NULL) {
			$player->show();
		} else {
			?>
			<div class="small_notice">
				No players registered.
			</div>
			<?php
		}
		endBox(); ?>

		<?php startBox('Players'); ?>
		<ul class="menu">
			<li style="white-space: nowrap"><a href="<?php echo rewriteURL('/world/online.html');?>"><img src="/images/buttons/faq_button.png" alt=""><b style="color: #00A; font-size:16px;"><?php echo getAmountOfPlayersOnline(); ?></b>&nbsp;Players&nbsp;Online</a></li>
			<li><a href="<?php echo rewriteURL('/world/hall-of-fame.html')?>"><img src="/images/buttons/quests_button.png" alt="">Hall Of Fame</a></li>
			<li><a href="<?php echo rewriteURL('/world/kill-stats.html')?>"><img src="/images/buttons/items_button.png" alt="">Kill stats</a></li>
		</ul>
		<form method="get" action="/" accept-charset="iso-8859-1">
			<input type="hidden" name="id" value="content/scripts/character">
			<input type="text" name="name" maxlength="30" style="width:9.8em">
			<input type="submit" name="search" value="Player search">
		</form>
		<?php endBox(); ?>


		<?php 
		/*
		 * Show the admin menu only if player is really an admin.
		 * Admins are designed using the stendhal standard way.
		 */
		if(getAdminLevel()>=100) {
			startBox('Administration'); ?>
			<ul id="adminmenu" class="menu">
				<?php 
				if(getAdminLevel()>=400) { ?>
					<li><a href="/?id=content/admin/news"><img src="/images/buttons/news_button.png" alt="">News</a></li>
					<li><a href="/?id=content/admin/screenshots"><img src="/images/buttons/screenshots_button.png" alt="">Screenshots</a></li>
				<?php } ?>
				<li><a href="/?id=content/admin/logs"><img src="/images/buttons/c_chat_button.png" alt="">Support Logs</a></li>
				<li><a href="/?id=content/admin/playerhistory"><img src="/images/buttons/playerhistory_button.png" alt="">Player History</a></li>
			</ul>
			<?php endBox();
		}?>

<?php 
/*
          startBox('Server stats');
          // Load server stats about online players, bytes send/recv and other from database.
          $stats=getServerStats();
          if(!isset($stats) || !$stats->isOnline()) {
            ?>
            <div class="status"><a href="/?id=content/offline">Server is offline</a></div>
            <?php
          } else {
            echo '<a href="'.rewriteURL('/world/online.html').'">';
            ?>
            <div class="stats"><?php echo getAmountOfPlayersOnline(); ?></div> players online.
            </a>
            <div class="small_notice">
              <?php
              echo '<a href="'.rewriteURL('/world/server-stats.html').'">[Detailed stats]</a><br>'."\n";
              echo '<a href="'.rewriteURL('/world/kill-stats.html').'">[Killed stats]</a>'."\n";
              ?>
            </div>
          <?php
          }
          endBox(); 
*/        ?>

		<?php startBox('Contribute'); ?>
		<ul id="contribmenu" class="menu">
			<?php
			echo '<li><a href="'.rewriteURL('/chat/').'"><img src="/images/buttons/c_chat_button.png" alt="">Chat</a></li>'."\n";
			echo '<li><a href="'.$protocol.'://stendhalgame.org/wiki/Stendhal"><img src="/images/buttons/c_wiki_button.png" alt="">Wiki</a></li>'."\n";
			echo '<li><a href="'.rewriteURL('/development/bug.html').'"><img src="/images/buttons/c_bug_button.png" alt="">Report Bug</a></li>'."\n";
			echo '<li><a href="'.$protocol.'://stendhalgame.org/wiki/Stendhal_Quest_Contribution"><img src="/images/buttons/quests_button.png" alt="">Quests</a></li>'."\n";
			echo '<li><a href="'.$protocol.'://sourceforge.net/tracker/?func=add&amp;group_id=1111&amp;atid=301111"><img src="/images/buttons/help_button.png" alt="">Submit Patch</a></li>'."\n";
			echo '<li><a href="'.$protocol.'://xplanner.homelinux.net"><img src="/images/buttons/test_button.png" alt="">Testing</a></li>'."\n";
			echo '<li><a href="'.rewriteURL('/development/cvslog.html').'"><img src="/images/buttons/history_button.png" alt="">CVS/Changes</a></li>'."\n";
			echo '<li><a href="'.$protocol.'://sf.net/projects/arianne/files/stendhal"><img src="/images/buttons/download_button.png" alt="">All Downloads</a></li>'."\n";
			echo '<li><a href="'.rewriteURL('/development').'"><img src="/images/buttons/rpsystem_button.png" alt="">Development</a></li>'."\n";
			?>
		</ul>
		<?php endBox(); ?>
	</div>

	<div id="contentArea">
		<?php
			// The central area of the website.
			$page->writeContent();
		?>
	</div>

	<div id="footerArea">
		<span class="copyright">&copy; 1999-2010 <a href="http://arianne.sourceforge.net">Arianne RPG</a></span>
		<span><a href="http://sourceforge.net/projects/arianne"><img src="https://sflogo.sourceforge.net/sflogo.php?group_id=1111&amp;type=15" width="150" height="40" border="0" alt="Get Arianne RPG at SourceForge.net." ></a></span>
	</div>

	<div class="time">
		<?php
		// Show the server time
		echo 'Server time: '.date('G:i');
		?>
	</div>
</div>
</body>
</html>

<?php
}
// Close connection to databases.
disconnect();
?>