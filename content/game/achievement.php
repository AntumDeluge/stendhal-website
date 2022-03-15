<?php
class AchievementPage extends Page {
	private $achievements;

	public function __construct() {
		if (isset($_REQUEST['name']) && ($_REQUEST['name']=='special')) {
			// We don't need to do anything here
		} else if (isset($_REQUEST['name'])) {
			$this->achievements = Achievement::getAchievement(preg_replace('/_/', ' ', $_REQUEST['name']));
		} else {
			$this->achievements = Achievement::getAchievements("where category != 'SPECIAL'");
		}
	}

	public function writeHttpHeader() {
	    if (isset($_REQUEST['name']) && $_REQUEST['name'] != 'special'
	        && !isset($this->achievements)) {

			header('HTTP/1.0 404 Not Found');
			return true;
		}
		return true;
	}

	public function writeHtmlHeader() {
	    if (isset($this->achievements) && !is_array($this->achievements)) {
			echo '<title>Achievement '.$this->achievements->title.STENDHAL_TITLE.'</title>';

			echo '<meta property="og:type" content="game.achievement">';
			echo '<meta property="og:url" content="https://stendhalgame.org/'.rewriteURL('/achievement/'.surlencode($this->achievements->title).'.html').'">';
			echo '<meta property="og:title" content="'.$this->achievements->title.'">';
			echo '<meta property="og:description" content="'.htmlspecialchars($this->achievements->description).'">';
			echo '<meta property="og:image" content="https://stendhalgame.org/data/sprites/achievements/'.htmlspecialchars(strtolower($this->achievements->category)).'.png">';
			echo '<meta property="game:points" content="10">';
			echo '<meta property="fb:app_id" content="'.FACEBOOK_APP_ID.'">';

		} else {
			echo '<title>Achievements'.STENDHAL_TITLE.'</title>';
		}
	}

	function writeContent() {
		if (isset($_REQUEST['name']) && ($_REQUEST['name']=='special')) {
			$this->categoryAchievementList("special", "Special achievements are awarded for success in specific events. " .
					                                  "As each one is different and cannot be earned by all players, they do not contribute to hall of fame scoring.");
		} else if (isset($_REQUEST['name'])) {
		    if (!isset($this->achievements)) {
				startBox('Achievement');
				echo 'Achievement not found.';
				endBox();
			} else {
				$this->achievementDetail();
			}
		} else {
			$this->achievementList();
		}
	}

	function achievementDetail() {
		startBox('<h1>'.htmlspecialchars($this->achievements->title).'</h1>');
		echo '<div class="achievement">';
		echo '<img class="achievement" src="/data/sprites/achievements/'.htmlspecialchars(strtolower($this->achievements->category)).'.png" alt="">';
		echo '<div class="description">'.htmlspecialchars($this->achievements->description).'</div>';
		echo '</div>';
		echo 'Earned by '.htmlspecialchars($this->achievements->count). ' characters.';
		endBox();
		echo "\r\n";


		startBox('<h2>My Friends</h2>');
		if (isset($_SESSION) && isset($_SESSION['account'])) {
			$list = Achievement::getAwardedToMyFriends($_SESSION['account']->id, $this->achievements->id);
			echo '<div class="tableCell cards">';
			$this->renderPlayers($list);
			echo '</div>';
		} else {
			echo '<div style="padding: 2em"><a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url='.urlencode(rewriteURL('/achievement/'.surlencode($this->achievements->title).'.html')).'">Login to see your friends...</a></div>';
		}
		endBox();
		echo "\r\n";


		startBox('<h2>My Characters</h2>');
		if (isset($_SESSION) && isset($_SESSION['account'])) {
			$list = Achievement::getAwardedToOwnCharacters($_SESSION['account']->id, $this->achievements->id);
			echo '<div class="tableCell cards">';
			$this->renderPlayers($list);
			echo '</div>';
		} else {
			echo '<div style="padding: 2em"><a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url='.urlencode(rewriteURL('/achievement/'.surlencode($this->achievements->title).'.html')).'">Login to see your characters...</a></div>';
		}
		endBox();
		echo "\r\n";


		startBox("<h2>Most Recently</h2>");
		$list = Achievement::getAwardedToRecently($this->achievements->id);
		echo '<div class="tableCell cards">';
		if (!$this->renderPlayers($list)) {
			echo 'No character has earned this achievement, yet. Be the first!';
		}
		echo '</div>';
		endBox();
		echo "\r\n";
	}

	function achievementList() {
		startBox("<h2>Achievements</h2>");
		echo '<table class="prettytable">';
		foreach ($this->achievements as $achievement) {
			echo '<tr>';
			echo '<td><a href="'.rewriteURL('/achievement/'.surlencode($achievement->title).'.html').'"><img style="border:none" src="/data/sprites/achievements/'.htmlspecialchars(strtolower($achievement->category)).'.png" title="'.htmlspecialchars($achievement->category).'"></a></td>';
			echo '<td><a style="color: #000" href="'.rewriteURL('/achievement/'.surlencode($achievement->title).'.html').'" title="'.htmlspecialchars($achievement->description).'">'.htmlspecialchars($achievement->title).'</a></td>';
			echo '<td>'.htmlspecialchars($achievement->count).'</td>';
			echo '</tr>';
		}
		echo '</table>';
		endBox();
	}

	function categoryAchievementList($category, $description) {
		$list = Achievement::getAwardedInCategory($category);
		startBox("<h2>Achievements</h2>");
		echo '<div class="achievement">';
		echo '<div class="name">'.ucfirst(htmlspecialchars($category)).'</div>';
		echo '<img class="achievement" src="/data/sprites/achievements/'.htmlspecialchars($category).'.png" alt="">';
		echo '<div class="description">'.htmlspecialchars($description).'</div>';
		echo '</div>';
		echo count($list).' '.ucfirst(htmlspecialchars($category)).' achievements earned.';
		endBox();
		echo "\r\n";
		startBox("<h2>Awarded to</h2>");
		if (count($list) == 0) {
			echo 'No character has earned one of these achievements, yet.';
		} else {
			echo '<div style="height: 180px;">';
			$this->renderPlayers($list);
			echo '</div>';
		}
		endBox();
	}

	function renderPlayers($list) {
	    $nonEmpty = false;
		foreach ($list as $entry) {
			$style = '';
			if (isset($entry['description']) && isset($entry['title'])) {
				$title = htmlspecialchars($entry['title']).': '.htmlspecialchars($entry['description']);
			} else if (isset($entry['timedate'])) {
				$title= 'Earned on '.htmlspecialchars($entry['timedate']);
			} else {
				$style = 'class="achievementOpen"';
				$title = 'Not earned yet';
			}
			echo '<div class="onlinePlayer onlinePlayerHeight player">';
			echo '  <a class = "onlineLink" href="'.rewriteURL('/character/'.surlencode($entry['name']).'.html').'">';
			$outfit = $entry['outfit'];
			if (isset($entry['outfit_colors']) && strlen($entry['outfit_colors']) > 0) {
			    $outfit = $outfit.'_'.$entry['outfit_colors'];
			}
			if (isset($entry['outfit_layers']) && strlen($entry['outfit_layers']) > 0) {
			    $outfit = $entry['outfit_layers'];
			}
			echo '  <img '.$style.' src="'.rewriteURL('/images/outfit/'.$outfit.'.png').'" alt="" title="'.$title.'">';
			echo '  <span class="block onlinename">'.htmlspecialchars($entry['name']).'</span></a>';
			echo '</div>';
			$nonEmpty = true;
		}
		return $nonEmpty;
	}

	public function getBreadCrumbs() {
		$array = array('World Guide', '/world.html', 'Achievement', '/achievement.html');
		if (isset($_REQUEST['name']) && ($_REQUEST['name']=='special')) {
			$array[] = 'Special';
			$array[] = '/achievement/special.html';
		} else if (isset($_REQUEST['name'])) {
		    if (!is_array($this->achievements) || count($this->achievements)==0) {
				return null;
			} else {
				$array[] = ucfirst($this->achievements->title);
				$array[] = '/achievement/'.$this->achievements->title.'.html';
			}
		}

		return $array;
	}
}
$page = new AchievementPage();
