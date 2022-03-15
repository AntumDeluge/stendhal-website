<?php

define('TOTAL_HOF_PLAYERS', 10);


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
			$this->renderDetails($detail);
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
			$this->detail == urlencode($_REQUEST['detail']);
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


	function renderDetails($detail) {
		//TODO: add more
		startBox("<h2>Strongest players</h2>");
		?>
		<div class="bubble">XP, Achievements and Age</div>
		<?php
		$players= getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.' AND character_stats.level>=10 '.$this->filterWhere, 'R');
		$this->renderListOfPlayers($players, " xp");
		endBox();
	}


	function renderOverview() {
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
			<?php startBox("<h2>Best players</h2>"); ?>
			<div class="bubble">XP, Achievements per Age</div>
			<?php
			$players = getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'R', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, " points");
			##echo '<a href="'.rewriteURL('/world/hall-of-fame-strongest.html').'">More</a>';
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
			<?php startBox("<h2>Richest players</h2>"); ?>
			<div class="bubble">Amount of money</div>
			<?php
			$players= getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'W', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, ' coins');
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
			<?php startBox("<h2>Eldest players</h2>"); ?>
			<div class="bubble">Age in hours</div>
			<?php
			$players= getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere,'A', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, ' hours');
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
			<?php startBox("<h2>Deathmatch heroes</h2>"); ?>
			<div class="bubble">Deathmatch score</div>
			<?php
			$players=getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'D', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, ' points');
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
			<?php startBox("<h2>Best attackers</h2>"); ?>
			<div class="bubble">Based on atk*(1+0.03*level)</div>
			<?php
			$players= getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'T', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, " total atk");
			endBox();
			?>
		</div>

		<div style="float: left; width: 33%">
			<?php startBox("<h2>Best defenders</h2>"); ?>
			<div class="bubble">Based on def*(1+0.03*level)</div>
			<?php
			$players= getHOFPlayers($this->tableSuffix, $this->filterFrom.REMOVE_ADMINS_AND_POSTMAN.$this->filterWhere, 'F', 'limit '.TOTAL_HOF_PLAYERS);
			$this->renderListOfPlayers($players, " total def");
			endBox();
			?>
		</div>
<?php
	}
}


$page = new HallOfFamePage();
