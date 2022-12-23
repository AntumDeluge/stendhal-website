<?php

define('TOTAL_HOF_PLAYERS', 10);
define('EXT_HOF_PLAYERS', 100);

$categories = array(
	'R' => array('title' => 'Best players', 'desc' => 'XP, Achievements per Age', 'postfix' => ' points'),
	'W' => array('title' => 'Richest players', 'desc' => 'Amount of money', 'postfix' => ' coins'),
	'A' => array('title' => 'Eldest players', 'desc' => 'Age in hours', 'postfix' => ' hours'),
	'D' => array('title' => 'Deathmatch heroes', 'desc' => 'Deathmatch score', 'postfix' => ' points'),
	'T' => array('title' => 'Best attackers', 'desc' => 'Based on atk*(1+0.03*level)', 'postfix' => ' total atk'),
	'F' => array('title' => 'Best defenders', 'desc' => 'Based on def*(1+0.03*level)', 'postfix' => ' total def'),
);


function getAchievementScore($player) {
	return $player->getHallOfFameScore('@');
}

function printAge($minutes) {
	$h=$minutes;
	$m=$minutes%60;
	return round($h).':'.round($m);
}


class HallOfFamePage extends Page {
	private $filterFrom = '';
	private $filterWhere = '';

	private $filter;
	private $detail;

	private $loginRequired = false;

	public function __construct() {
		$this->setupFilter();
		$this->setupDetail();
	}


	// ------------------------------------------------------------------------
	// ------------------------------------------------------------------------


	function writeHttpHeader() {
		if ($this->loginRequired) {
			header('Location: '.STENDHAL_LOGIN_TARGET.'/index.php?id=content/account/login&url='.urlencode(rewriteURL('/world/hall-of-fame/'.urlencode($this->filter).'_'.urlencode($this->detail).'.html')));
			return false;
		}
		return true;
	}


	public function writeHtmlHeader() {
		echo '<title>Hall of Fame ('.htmlspecialchars($this->filter).')'.STENDHAL_TITLE.'</title>';
	}


	function writeContent() {
		$this->writeTabs();
		if ($this->detail == "overview") {
			$this->renderOverview();
		} else {
			$this->renderDetails();
		}
		$this->closeTabs();
	}


	// ------------------------------------------------------------------------
	// ------------------------------------------------------------------------

	function setupFilter() {
		$this->filter = 'active';
		if (isset($_REQUEST['filter'])) {
			$this->filter = urlencode($_REQUEST['filter']);
		}
		if ($this->filter=="alltimes") {
			$this->filterWhere='';
			$this->tableSuffix = 'alltimes';
		} else if ($this->filter=="active") {
			$this->filterWhere = '';
			$this->tableSuffix = 'recent';
		} else if ($this->filter=="friends") {
			if (!isset($_SESSION['account'])) {
				$this->loginRequired = true;;
				return;
			}
			$this->filterFrom = ", characters, buddy ";
			$this->filterWhere = " AND character_stats.name=buddy.buddy AND buddy.relationtype='buddy' AND buddy.charname=characters.charname "
				. " AND characters.player_id='".mysql_real_escape_string($_SESSION['account']->id)."'";
			$this->tableSuffix = 'alltimes';
		}
		// TODO: 404 on invalid filter variable
		return;
	}

	function setupDetail() {
		$this->detail = 'overview';
		if (isset($_REQUEST['detail'])) {
			$this->detail = urlencode($_REQUEST['detail']);
		}
		// TODO: 404 on invalid detail variable
	}


	// ------------------------------------------------------------------------
	// ------------------------------------------------------------------------

	function writeTabs() {
		?>
		<br>
		<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>
		<td class="barTab" width="2%"> &nbsp;</td>
		<?php echo '<td class="'.$this->getTabClass('active').'" width="25%"><a class="'.$this->getTabClass('active').'A" href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/active_'.$this->detail.'.html')).'">Active</a></td>';?>
		<td class="barTab" width="2%"> &nbsp;</td>
		<?php echo '<td class="'.$this->getTabClass('alltimes').'" width="25%"><a class="'.$this->getTabClass('alltimes').'A" href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/alltimes_'.$this->detail.'.html')).'">All times</a></td>';?>
		<td class="barTab" width="2%">&nbsp;</td>
		<?php echo '<td class="'.$this->getTabClass('friends').'" width="25%"><a class="'.$this->getTabClass('friends').'A" href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/friends_'.$this->detail.'.html')).'">Me &amp; my friends</a></td>';?>
		<td class="barTab"> &nbsp;</td>
		</tr>
		<tr><td colspan="7" class="tabPageContent">
		<br>
		<?php
	}


	function closeTabs() {
		?></td></tr></table><?php
	}

	function getTabClass($tab) {
		if ($this->filter == $tab) {
			return 'activeTab';
		} else {
			return 'backgroundTab';
		}
	}

	function renderListOfPlayers($list, $postfix='') {
		$i=1;
		foreach($list as $entry) {
		?>
			<div class="row">
				<div class="position"><?php echo $entry['rank']; ?></div>
				<a href="<?php echo rewriteURL('/character/'.surlencode($entry['charname']).'.html'); ?>">
					<?php
					$outfit = $entry['outfit'];
					if (isset($entry['outfit_colors']) && strlen($entry['outfit_colors']) > 0) {
						$outfit = $outfit.'_'.$entry['outfit_colors'];
					}
					if (isset($entry['outfit_layers']) && strlen($entry['outfit_layers']) > 0) {
					    $outfit = $entry['outfit_layers'];
					}
					?>
					<img class="small_image" src="<?php echo rewriteURL('/images/outfit/'.$outfit.'.png')?>" alt="" />
					<span class="block label"><?php echo htmlspecialchars($entry['charname']); ?></span>
					<span class="block data"><?php echo $entry['points'].$postfix; ?></span>
				</a>
				<div style="clear: left;"></div>
			</div>

			<?php
			$i++;
		}
	}


	function renderDetails() {
		global $categories;

		if (!isset($categories[$this->detail])) {
			startBox('<h2>Unknown detail: '.$this->detail.'</h2>');
			endBox();
			echo '<small><a href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/'.$this->filter.'_overview.html')).'">overview</a></small>';
			return;
		}

		$cat = $categories[$this->detail];
		startBox('<h2>'.$cat['title'].'</h2>');
		echo '<div class="bubble">'.$cat['desc'].'</div>';
		$players = getHOFPlayers($this->tableSuffix,
				$this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere,
				$this->detail, 'limit '.EXT_HOF_PLAYERS);
		$this->renderListOfPlayers($players, $cat['postfix']);
		echo '<small><a href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/'.$this->filter.'_overview.html')).'">overview</a></small>';
		endBox();
	}


	function renderOverview() {
		global $categories;

		$choosen = getBestPlayer($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere);
		if ($choosen !== false) {
			startBox("<h1>Best player</h1>");
			?>
			<div class="bubble">The best player is decided based on the relation between XP, age, and achievement score. The best players are those who spend time earning XP and achievements.</div>
			<div class="best">
				<a href="<?php echo rewriteURL('/character/'.surlencode($choosen['charname']).'.html'); ?>">
					<span class="block statslabel">Name:</span><span class="block data"><?php echo htmlspecialchars($choosen['charname']); ?></span>
					<span class="block statslabel">Age:</span><span class="block data"><?php echo printAge(round($choosen['age']/60, 0)); ?> hours</span>
					<span class="block statslabel">Level:</span><span class="block data"><?php echo $choosen['level']; ?></span>
					<span class="block statslabel">XP:</span><span class="block data"><?php echo $choosen['xp']; ?></span>
					<span class="block statslabel">Roleplay score:</span><span class="block data"><?php echo $choosen['points']; ?></span>
				</a>
			</div>
			<a href="<?php echo rewriteURL('/character/'.surlencode($choosen['charname']).'.html'); ?>">
			<?php
			$outfit = $choosen['outfit'];
			if (isset($choosen['outfit_colors']) && strlen($choosen['outfit_colors']) > 0) {
			    $outfit = $outfit.'_'.$choosen['outfit_colors'];
			}
			if (isset($choosen['outfit_layers']) && strlen($choosen['outfit_layers']) > 0) {
			    $outfit = $choosen['outfit_layers'];
			}
			?>
			<img class="bordered_image" src="<?php echo rewriteURL('/images/outfit/'.surlencode($outfit).'.png')?>" alt="">
			</a>
			<?php if (isset($choosen->sentence) && $choosen->sentence != '') {
				echo '<div class="sentence">'.htmlspecialchars($choosen['sentence']).'</div>';
			}
			endBox();
		}?>

		<div style="float: left; width: 34%">
		<?php
			$cat = $categories['R'];
			startBox("<h2>".$cat['title']."</h2>");
			echo '		<div class="bubble">'.$cat['desc'].'</div>';
			$players = getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'R', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, $cat['postfix']);
			echo '<small><a href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/'.$this->filter.'_R.html')).'">more</a></small>';
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
		<?php
			$cat = $categories['W'];
			startBox("<h2>".$cat['title']."</h2>");
			echo '		<div class="bubble">'.$cat['desc'].'</div>';
			$players= getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'W', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, $cat['postfix']);
			echo '<small><a href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/'.$this->filter.'_W.html')).'">more</a></small>';
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
		<?php
			$cat = $categories['A'];
			startBox("<h2>".$cat['title']."</h2>");
			echo '		<div class="bubble">'.$cat['desc'].'</div>';
			$players= getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere,'A', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, $cat['postfix']);
			echo '<small><a href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/'.$this->filter.'_A.html')).'">more</a></small>';
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
		<?php
			$cat = $categories['D'];
			startBox("<h2>".$cat['title']."</h2>");
			echo '		<div class="bubble">'.$cat['desc'].'</div>';
			$players=getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'D', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, $cat['postfix']);
			echo '<small><a href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/'.$this->filter.'_D.html')).'">more</a></small>';
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
		<?php
			$cat = $categories['T'];
			startBox("<h2>".$cat['title']."</h2>");
			echo '		<div class="bubble">'.$cat['desc'].'</div>';
			$players= getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'T', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, $cat['postfix']);
			echo '<small><a href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/'.$this->filter.'_T.html')).'">more</a></small>';
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
		<?php
			$cat = $categories['F'];
			startBox("<h2>".$cat['title']."</h2>");
			echo '		<div class="bubble">'.$cat['desc'].'</div>';
			$players= getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'F', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, $cat['postfix']);
			echo '<small><a href="'.htmlspecialchars(rewriteURL('/world/hall-of-fame/'.$this->filter.'_F.html')).'">more</a></small>';
			endBox();
			?>
		</div>
<?php
	}
}


$page = new HallOfFamePage();
?>
