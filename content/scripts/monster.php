<?php
/*
 * Stendhal website - a website to manage and ease playing of Stendhal game
 * Copyright (C) 2008-2023 The Arianne Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


function printRespawn($turns) {
  return round($turns *0.3/60, 2);
}

function renderAmount($amount) {
  if (!is_numeric($amount)) {
	  $amount=str_replace("[","",$amount);
	  $amount=str_replace("]","",$amount);
	  list($min,$max)=explode(",",$amount);
  } else {
	  $min = $amount;
	  $max = $amount;
  }

  if($min!=$max) {
    return "between $min and $max.";
  } else {
  	return "exactly $min.";
  }
}

class MonsterPage extends Page {
	private $name;
	private $monsters;
	private $isExact;
	private $found;

	public function __construct() {
		$this->name = preg_replace('/_/', ' ', $_REQUEST['name']);
		$this->isExact = isset($_REQUEST['exact']);
		$this->monsters = getMonsters();

		// does this name exist?
		foreach($this->monsters as $m) {
			if($m->name==$this->name) {
				$this->found = true;
			}
		}
	}


	public function writeHttpHeader() {
		if (!$this->found) {
			header('HTTP/1.0 404 Not Found');
			return true;
		}

		if ($this->isExact && strpos($_REQUEST['name'], ' ') !== FALSE) {
			header('HTTP/1.0 301 Moved permanently.');
			header('Location: '.preg_replace('/[ +]/', '_', $_SERVER['PHP_SELF']));
			return false;
		}

		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Creature '.htmlspecialchars($this->name).STENDHAL_TITLE.'</title>'."\n";
		if (!$this->found) {
			echo '<meta name="robots" content="noindex">'."\n";
		}
	}

	function writeContent() {

if (!$this->found) {
	startBox("<h1>No such Creature</h1>");
	?>
	There is no such creature at Stendhal.<br>
	Please make sure you spelled it correctly.
	<?php
	endBox();
	return;
}

foreach($this->monsters as $m) {
	/* If name of the creature match or contains part of the name.*/
	if($m->name==$this->name || (!$this->isExact and strpos($m->name, $this->name) != false)) {
	startBox('<h1>'.htmlspecialchars(ucfirst($m->name)).'</h1>');
	?>
	<div class="monster">
		<img class="monster" src="<?php echo $m->gfx; ?>" alt="">
		<div class="level">Level <?php echo $m->level; ?></div>
		<div class="xp">Killing it will give you <?php echo $m->xp; ?> XP.</div>
		<div class="respawn">Respawns on average in <?php echo printRespawn($m->respawn); ?> minutes.</div>
		<div class="description">
		<?php
		if(trim($m->description=="")) {
			echo 'No description. Would you like to <a href="https://sourceforge.net/p/arianne/patches/new/?summary=Monster%20'.urlencode($m->name).'&description=%3C%3CPlease%20enter%20description%20here%3E%3E#top_nav">write one</a>?';
		} else {
			echo $m->description;
		}
		?>
	</div>

	<div class="table">
		<div class="title">Attributes</div>
		<?php
		foreach($m->attributes as $label=>$data) {
		?>
			<div class="row">
				<div class="label"><?php echo strtoupper($label); ?></div>
				<div class="data"><?php echo $data; ?></div>
			</div>
		<?php
		}
		?>
	</div>

	<?php if (count($m->susceptibilities) > 0) {?>
	<div class="table">
		<div class="title">Resistances</div>
		<?php
		foreach($m->susceptibilities as $label=>$data) {
		?>
			<div class="row">
				<div class="label"><?php echo strtoupper($label); ?></div>
				<div class="data"><?php echo $data; ?>%</div>
			</div>
		<?php
		}
		?>
	</div>
	<?php }?>

	<div class="table">
		<div class="title">Creature drops</div>
			<?php
			foreach($m->drops as $k) {
			?>
				<div class="row">
					<?php
					$item = getItem($k["name"]);
					$item->showImageWithPopup();
					?>
					<span class="block label"><?php echo ucfirst($k["name"]); ?></span>
					<div class="data">Drops <?php echo renderAmount($k["quantity"]); ?></div>
					<div class="data">Probability: <?php echo $k["probability"]; ?>%</div>
				</div>
			<?php
			}
			?>
		</div>
	</div>

	<?php
	endBox();


    /*
     * Obtain data from database
     */
    $m->fillKillKilledData();

    startBox(ucfirst($m->name)." killed by players, per day");
      $data='';
      foreach($m->kills as $day=>$amount) {
	$date = date('M-d', time() - $day * 86400);
	$data .= $date . '_' . $amount . ',';
      }
    ?>
    <img style="padding: 4px; border: 1px solid black;" src="/bargraph.php?data=<?php echo $data; ?>" alt="<?php echo $data; ?>" title="Killed creature" >
    <?php
    endBox();

    startBox("Players killed by ".$m->name.", per day");
      $data='';
      foreach($m->killed as $day=>$amount) {
        $date = date('M-d', time() - $day * 86400);
	$data.= $date . '_' . $amount . ',';
      }
    ?>
    <img style="padding: 4px; border: 1px solid black;" src="/bargraph.php?data=<?php echo $data; ?>" alt="<?php echo $data; ?>" title="Killed Players" >
    <?php
    endBox();
		$this->writeRelatedPages('C.'.strtolower($m->name), 'Stendhal_Quest', 'Quests');
    ?>
    <div style="margin-bottom: 48px;"></div>
    <?php
  }
}
		$this->includeJs();
	}

	public function getBreadCrumbs() {
		if (!$this->isExact || !$this->found) {
			return null;
		}

		return array('World Guide', '/world.html',
			'Creature', '/creature/',
			ucfirst($this->name), '/creature/'.$this->name.'.html'
			);
	}
}
$page = new MonsterPage();
