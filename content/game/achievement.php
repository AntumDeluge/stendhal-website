<?php 
class AchievementPage extends Page {
	private $achievements;

	public function __construct() {
		if ($_REQUEST['name']=='special') {
			// We don't need to do anything here
		} else if ($_REQUEST['name']) {
			$this->achievements = Achievement::getAchievement(preg_replace('/_/', ' ', $_REQUEST['name']));
		} else {
			$this->achievements = Achievement::getAchievements("where category != 'SPECIAL'");
		}
	}

	public function writeHttpHeader() {
		if ($_REQUEST['name'] && $_REQUEST['name'] != 'special' && count($this->achievements)==0) {
			header('HTTP/1.0 404 Not Found');
			return true;
		}
		return true;
	}

	public function writeHtmlHeader() {
		if (count($this->achievements) == 1) {
			echo '<title>Achievement '.$this->achievements->title.STENDHAL_TITLE.'</title>';
		} else {
			echo '<title>Achievements'.STENDHAL_TITLE.'</title>';
		}
	}

	function writeContent() {
		if ($_REQUEST['name']=='special') {
			$this->categoryAchievementList("special", "Special achievements are awarded for success in specific events. " .
					                                  "As each one is different and cannot be earned by all players, they do not contribute to hall of fame scoring.");
		} else if ($_REQUEST['name']) {
			if (count($this->achievements)==0) {
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
		startBox("Achievement");
		echo '<div class="achievement">';
		echo '<div class="name">'.htmlspecialchars($this->achievements->title).'</div>';
		echo '<img class="achievement" src="/images/achievements/'.htmlspecialchars(strtolower($this->achievements->category)).'.png" alt="">';
		echo '<div class="description">'.htmlspecialchars($this->achievements->description).'</div>';
		echo '</div>';
		echo 'Earned by '.htmlspecialchars($this->achievements->count). ' characters.';
		endBox();
		echo "\r\n";


		startBox('My Friends');
		if ($_SESSION && $_SESSION['account']) {
			$list = Achievement::getAwardedToMyFriends($_SESSION['account']->id, $this->achievements->id);
			echo '<div style="height: '.((floor(count($list) / 7) + 1) * 90) .'px">';
			$this->renderPlayers($list);
			echo '</div>';
		} else {
			echo '<div style="padding: 2em"><a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url='.urlencode(rewriteURL('/achievement/'.surlencode($this->achievements->title).'.html')).'">Login to see your friends...</a></div>';
		}
		endBox();
		echo "\r\n";


		startBox('My Characters');
		if ($_SESSION && $_SESSION['account']) {
			$list = Achievement::getAwardedToOwnCharacters($_SESSION['account']->id, $this->achievements->id);
			echo '<div style="height: '.((floor(count($list) / 7) + 1) * 90) .'px">';
			$this->renderPlayers($list);
			echo '</div>';
		} else {
			echo '<div style="padding: 2em"><a href="'.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&amp;url='.urlencode(rewriteURL('/achievement/'.surlencode($this->achievements->title).'.html')).'">Login to see your characters...</a></div>';
		}
		endBox();
		echo "\r\n";


		startBox("Most Recently");
		$list = Achievement::getAwardedToRecently($this->achievements->id);
		if (count($list) == 0) {
			echo 'No character has earned this achievement, yet. Be the first!';
		} else {
			echo '<div style="height: 180px;">';
			$this->renderPlayers($list);
			echo '</div>';
		}
		endBox();
		echo "\r\n";
	}

	function achievementList() {
		startBox("Achievements");
		echo '<table class="prettytable">';
		foreach ($this->achievements as $achievement) {
			echo '<tr>';
			echo '<td><a href="'.rewriteURL('/achievement/'.surlencode($achievement->title).'.html').'"><img style="border:none" src="/images/achievements/'.htmlspecialchars(strtolower($achievement->category)).'.png" title="'.htmlspecialchars($achievement->category).'"></a></td>';
			echo '<td><a style="color: #000" href="'.rewriteURL('/achievement/'.surlencode($achievement->title).'.html').'" title="'.htmlspecialchars($achievement->description).'">'.htmlspecialchars($achievement->title).'</a></td>';
			echo '<td>'.htmlspecialchars($achievement->count).'</td>';
			echo '</tr>';
		}
		echo '</table>';
		endBox();
	}

	function categoryAchievementList($category, $description) {
		$list = Achievement::getAwardedInCategory($category);
		startBox("Achievements");
		echo '<div class="achievement">';
		echo '<div class="name">'.ucfirst(htmlspecialchars($category)).'</div>';
		echo '<img class="achievement" src="/images/achievements/'.htmlspecialchars($category).'.png" alt="">';
		echo '<div class="description">'.htmlspecialchars($description).'</div>';
		echo '</div>';
		echo count($list).' '.ucfirst(htmlspecialchars($category)).' achievements earned.';
		endBox();
		echo "\r\n";
		startBox("Awarded to");
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
		foreach ($list as $entry) {
			$style = '';
			if($entry['description'] && $entry['title']) {
				$title = htmlspecialchars($entry['title']).': '.htmlspecialchars($entry['description']);
			} else if ($entry['timedate']) {
				$title= 'Earned on '.htmlspecialchars($entry['timedate']);
			} else {
				$style = 'class="achievementOpen"';
				$title = 'Not earned yet';
			}
			echo '<div class="onlinePlayer onlinePlayerHeight">';
			echo '  <a class = "onlineLink" href="'.rewriteURL('/character/'.surlencode($entry['name']).'.html').'">';
			echo '  <img '.$style.' src="'.rewriteURL('/images/outfit/'.surlencode($entry['outfit']).'.png').'" alt="" title="'.$title.'">';
			echo '  <span class="block onlinename">'.htmlspecialchars($entry['name']).'</span></a>';
			echo '</div>';
		}
	}
}
$page = new AchievementPage();
?>