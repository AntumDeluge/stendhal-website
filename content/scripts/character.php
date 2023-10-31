<?php
function printAge($minutes) {
	return round($minutes/60, 2);
}

class CharacterPage extends Page {
	private $name;
	private $players;

	public function __construct() {
		$this->name=$_REQUEST["name"];
		$this->players=getPlayers("where name='".mysql_real_escape_string($this->name)."'", 'name');
	}

	public function writeHtmlHeader() {
		if(sizeof($this->players) > 0) {
			$choosen = $this->players[0];
			$account = $choosen->getAccountInfo();
			if (($account["status"] != 'active') || ($account["charstatus"] != 'active')) {
				echo '<meta name="robots" content="noindex">'."\n";
			}
		} else {
			echo '<meta name="robots" content="noindex">'."\n";
		}
		if (isset($_GET['test']) && $_GET['test'] == 'box') {
			echo '<style type="text/css">div {border: 1px dotted red } span {border: 1px dotted green} td {border: 1px dotted blue} p {border: 1px dotted yellow}  p {border: 1px dotted black}</style>';
		}
		echo '<title>Player '.htmlspecialchars($this->name).STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
		if(sizeof($this->players)==0) {
			startBox("No such player");
?>
There is no such player at Stendhal.<br>
Please make sure you spelled it correctly.
<?php
			endBox();
			return;
		}

		$choosen=$this->players[0];
		$account=$choosen->getAccountInfo();
		$account_active = $account["status"] == 'active' && $account["charstatus"] == 'active';

		startBox('<h1>'.htmlspecialchars($choosen->name).'</h1>');
?>
<div class="table">
	<div class="title">Details</div>
	<div style="float: right">
		<?php if ($account_active) {?>
		<img class="bordered_image" src="<?php echo rewriteURL('/images/outfit/'.surlencode($choosen->outfit).'.png')?>" alt="Player outfit"/>
		<?php }?>
	</div>
	<div><span class="statslabel">Name:</span><span class="data"><?php echo htmlspecialchars($choosen->name); ?></span></div>
	<div><span class="statslabel">Age:</span><span class="data"><?php echo htmlspecialchars(printAge($choosen->age)); ?> hours</span></div>
	<div><span class="statslabel">Level:</span><span class="data"><?php echo htmlspecialchars($choosen->level); ?></span></div>
	<?php if ($account_active) { ?>
	<div class="hspacer"></div> <!-- workaround to prevent character image extending outside table border -->
	<?php } ?>
	<div class="married">
		<?php if(!empty($choosen->married)) {
			echo htmlspecialchars($choosen->name) . ' is married to <a href="';
			echo rewriteURL('/character/'.htmlspecialchars($choosen->married).'.html');
			echo '">'.htmlspecialchars($choosen->married).'</a>';
		} ?>
	</div>
	<?php if ($account_active && $choosen->sentence != '') {
		echo '<div class="sentence">' . htmlspecialchars($choosen->sentence). '</div>';
	}?>
</div>
<div style="overflow: hidden">
<div class="table" style ="float:left; height:190px; margin-right: 12px;">
	<div class="title"><a name="attributes">Attributes and statistics</a></div>

	<?php
	foreach($choosen->attributes as $key=>$value) {
		if ($key == 'karma') {
			continue;
		}
		$old = array("atk", "def", "hp");
		$new = array("Attack Level", "Defense level", "Current health");
		$key = str_replace($old, $new, $key);
		?>
		<div><span class="statslabel"><?php echo htmlspecialchars(ucwords($key)) ?>:</span>
		<span class="data"><?php echo htmlspecialchars(ucwords($value)) ?></span></div>
	<?php } ?>
	<div><span class="statslabel">XP:</span><span class="data"><?php
	if ($this->players[0]->xp > 100000000) {
		echo htmlspecialchars(intval(intval($choosen->xp) / 1000000)).'m';
	} else if ($choosen->xp > 10000) {
		echo htmlspecialchars(intval(intval($choosen->xp) / 1000)).'k';
	} else {
		echo htmlspecialchars($choosen->xp);
	}
	?></span></div>
	<div><span class="statslabel">DM Score:</span><span class="data"><?php echo htmlspecialchars($choosen->getHallOfFameScore('D')); ?></span></div>
	<div><span class="statslabel">Maze Score:</span><span class="data"><?php echo htmlspecialchars($choosen->getHallOfFameScore('M')); ?></span></div>
	<?php /*$tradescore = $choosen->getHallOfFameScore('T');
		  if ($tradescore > 0) {
			echo '<div><span class="statslabel">Trading Score:</span><span class="data">'.htmlspecialchars($tradescore).'</span></div>';
		  }*/
	?>
	<div><span style="font-weight:bolder; float:left">Karma:</span>
		<div style="position: relative; left: 85px; top:.5em; background-color:#000; width:100px; height:5px">
		<div style="background-image: url('/data/gui/karma_scale.png'); width:<?php
		$SCALING = 0.02;
		$normalized = 0.5 + atan($SCALING * $choosen->attributes['karma']) / pi();
		echo $normalized * 100;
		?>px; height: 5px; background-repeat:no-repeat;" class="data">&nbsp;</div></div>
	</div>

</div>
<div class="table" style = "float:left; width:115px; height:190px; margin-right: 12px;">
<div class="title"><a name="equipment">Equipment</a></div>
<div class ="equipment">
<?php
foreach($choosen->equipment as $slot=>$content) {
	$old = array("head", "armor", "lhand", "rhand", "legs", "feet", "cloak", "finger");
	$new = array("head", "armor", "left hand", "right hand", "legs", "feet", "cloak", "finger");

	?>
	<div class="equiprow <?php echo $slot;?>">
	<?php
	if($content!="") {
		$item = getItem($content);
		if (isset($item)) {
			$item->showImageWithPopup(ucfirst(str_replace($old, $new, $slot))). ': ';
		}
	} else {
		?>
	<div class="emptybox"></div>
		<?php
	}
	?>
	</div>
	<?php
}
	?>
</div>
</div>
<div class="table" style ="float:left; height:190px;">
<div class="title"><a name="rank">Rank</a></div>

	<?php
	$ranks = getCharacterRanks($choosen->name);
	if ($choosen->adminlevel >= 600) {
		if ($choosen->name == 'postman') {
			echo 'Oh, I do not <br> compete in fighting. <br> I am on duty to<br> deliver messages.';
		} else {
			echo 'Game masters <br>are not normal <br> players and <br> therefore <br>don\'t appear in <br>the hall of fame.';
		}
	} else if (($this->players[0]->level <= 2) || (count($ranks) == 1 && $ranks['__'])) {
		echo htmlspecialchars($choosen->name). ' is <br>new in Stendhal.<br><br>Please check back<br> one day after reaching <br>level 2 because <br>ranks are only <br>calculated once a day.';
	} else {
		$names = array('Best', 'XP', 'Achievements', 'Richest', 'Eldest', 'Deathmatch', 'Attackers', 'Defenders', 'Maze Runner');
		$fametypes = array('R', 'X', '@', 'W', 'A', 'D', 'T', 'F', 'M');
		for ($i = 0; $i < count($names); $i++) {
			echo '<div><span class="statslabel">'.$names[$i].'</span><span class="data">';
			if (isset($ranks[$fametypes[$i]])) {
				echo htmlspecialchars($ranks[$fametypes[$i]]);
			} else {
				echo '-';
			}
			echo '</span></div>';
		}
	}
	?>
</div>

</div>

<?php
	if(STENDHAL_ACHIEVEMENTS && $choosen->adminlevel < 600) {
		$this->renderAchievements();
	}
?>

<div class="table">
  <div class="title"><a name="death">Deaths</a></div>
 <?php
  $deaths=$choosen->getDeaths();

	if(count($deaths)==0) {
		?>
		Not recently killed.
		<?php
	}

	foreach($deaths as $date=>$source) {
		if(existsMonster($source)) {

			// It was killed by a monster.
			$monsters=getMonsters();
			foreach($monsters as $monster) {
				if($monster->name==$source) {
			?>
	<div class="row">
		<?php
		$monster->showImageWithPopup();
		?>
		Killed by <?php echo a_an($monster->name) ?> <span class="label"><?php echo htmlspecialchars($monster->name); ?></span>
		<span class="block data">Happened at <?php echo htmlspecialchars($date); ?>.</span>
		<div style="margin-bottom: 50px;"></div>
	</div>
			<?php
				}
			}
		} else {

			// It was killed by a player.
			?>
	<div class="row">
		<?php
		echo '<a href="'.rewriteURL('/character/'.surlencode($source).'.html').'">';
		$killer=getPlayer($source);
		?>
		<img class="creature" src="<?php echo rewriteURL('/images/outfit/'.htmlspecialchars($killer->outfit).'.png'); ?>" alt="<?php echo htmlspecialchars($source); ?>"/>
		Killed by <span class="label"><?php echo htmlspecialchars($source); ?></span>
		<span class="block data">Happened at <?php echo htmlspecialchars($date); ?>.</span></a>
		<div style="margin-bottom: 50px;"></div>
	</div>
<?php
		}
	}
?>
</div>

<div class="table">
	<div class="title"><a name="account">Account information</a></div>
	<div class="register">Registered at <?php echo htmlspecialchars($account["register"]); ?></div>
	<div class="account_status">
	<?php
	$status = $account["status"];
	if ($account["charstatus"] != 'active') {
		$status=$account["charstatus"];
	}
	?>
	This account is <span class="<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars($status); ?></span>
	</div>
	<?php if (($account["status"]) == 'active' && ($choosen->adminlevel > 0) && ($choosen->name != 'postman')) {
		if ($choosen->adminlevel < 300) {
			echo '<div class="admin">This player volunteered to answer support questions about Stendhal.</div>';
		} else if ($choosen->adminlevel < 500) {
			echo '<div class="admin">This player has adminlevel <a href="/wiki/Stendhal:Administration#Required_adminlevel">' . htmlspecialchars($choosen->adminlevel). '</a> to provide support for Stendhal players.</div>';
		} else {
			echo '<div class="admin">This account is a game master with adminlevel <a href="/wiki/Stendhal:Administration#Required_adminlevel">' . htmlspecialchars($choosen->adminlevel). '</a>.</div>';
		}
	}
?>
</div>

<?php

endBox();
	}

	private function renderAchievements() {
		?>
	<div class="table">
	<div class="title"><a name="achievements">Achievements</a></div>
		<?php
		$list = Achievement::getAchievementForCharacter($this->name);
		$lastCategory = '';
		foreach ($list as $achievement) {
			if ($achievement->category != $lastCategory) {
				if ($lastCategory != '') {
					echo "</div>\n";
				}
				echo '<div>';
				$lastCategory = $achievement->category;
			}
			if (isset($achievement->reachedOn)) {
				$class = "achievementDone";
			} else {
				$class = "achievementOpen";
			}
			$achievement->showImageWithPopup($class);
		}
		echo '</div>';
		?>
		</div>
		<?php
	}
}
$page = new CharacterPage();
