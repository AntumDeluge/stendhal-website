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
		return 'between ' . formatNumber($min) . ' and ' . formatNumber($max) . '.';
	}

	return 'exactly ' . formatNumber($min) . '.';
}


class ItemPage extends Page {
	private $class;
	private $name;
	private $items;
	private	$isExact;
	private $found;
	private $counter;

	public function __construct() {
		$this->name = preg_replace('/_/', ' ', $_REQUEST['name']);
		$this->class = preg_replace('/_/', ' ', $_REQUEST['class']);
		$this->isExact = isset($_REQUEST['exact']);
		$this->items=getItems();

		// does this name exist?
		$this->counter = 0;
		foreach($this->items as $m) {
			$x = ($m->name == $this->name || (!$this->isExact && strpos($m->name, $this->name) !== FALSE));
			$y = (($m->class == $this->class) || $this->class == 'all');
			if (($m->name == $this->name || (!$this->isExact && strpos($m->name, $this->name) !== FALSE)) && (($m->class == $this->class) || $this->class == 'all')) {
				$this->found = true;
				$this->counter++;
				$realClass = $m->class;
			}
		}
		if (isset($realClass) && ($this->counter == 1)) {
			$this->class = $realClass;
		}
	}

	public function writeHttpHeader() {
		global $protocol;
		if ($this->isExact && !$this->found) {
			header('HTTP/1.0 404 Not Found');
			return true;
		}


		if (($this->isExact || $this->counter==1) && (strpos($_REQUEST['class'], ' ') !== FALSE || strpos($_REQUEST['name'], ' ') !== FALSE || $_REQUEST['class'] == 'all')) {
			header('HTTP/1.0 301 Moved permanently.');
			header("Location: ".$protocol."://".$_SERVER['HTTP_HOST'].preg_replace("/&amp;/", "&",
				rewriteURL('/item/'.surlencode($this->class).'/'.surlencode($this->name).'.html')));
			echo ($this->isExact || $this->counter==1);
			return false;
		}
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>Item '.htmlspecialchars($this->name).STENDHAL_TITLE.'</title>';
		if (!$this->found) {
			echo '<meta name="robots" content="noindex">'."\n";
		}
	}

	function writeContent() {
		if ($this->isExact && !$this->found) {
			startBox("<h1>No such Item</h1>");
			?>
	There is no such item at Stendhal.<br>
	Please make sure you spelled it correctly.
			<?php
			endBox();
			return;
		}

		foreach($this->items as $m) {
			/*
			 * If name of the creature match or contains part of the name.
			 */
			if (($m->name==$this->name || (!$this->isExact && strpos($m->name, $this->name) !== FALSE))
					&& (($m->class == $this->class) || ($this->class == 'all'))) {
				startBox('<h1>'.htmlspecialchars(ucfirst($m->name)).'</h1>');
				?>
		<div class="item">
			<div class="type">This item is of <?php echo $m->class ?> class</div>
			<img class="item" src="<?php echo $m->imageurl; ?>" alt="">
				<?php
				if ($m->unattainable) {
					echo '<br>This item is not available.';
					?>

		</div>
					<?php
					continue;
				}
				?>
			<div class="description">
				<?php
				if(trim($m->description)=="") {
					echo 'No description. Would you like to <a href="https://sourceforge.net/p/arianne/patches/new/?summary=Item%20Description%20'.urlencode($m->name).'&description=%3C%3CPlease%20enter%20description%20here%3E%3E#top_nav">write one</a>?';
				} else {
					echo $m->description;
				}
				?>

			</div>

			<div class="table">
				<div class="title">Attributes</div>
				<?php

				$attr_altnames = [
					'lifesteal' => 'life steal',
					'statusattack' => 'status attack',
					'statusresist' => 'status resistances'
				];

				// set initial values
				$min_level = -1;
				$level = 0;
				$factor = 1;

				// get the min level if it has one
				if (isset($m->attributes['min_level'])) {
					$min_level = $m->attributes['min_level'];
					// player filled in level
					if (!empty($_POST['level'])) {
						$level = $_POST['level'];
						if ($level < $min_level) {
							// scale factor for rate and def
							$factor = 1 - log(($level + 1) / ($min_level + 1));
						}
					}
				}
				foreach($m->attributes as $label=>$data) {
					if ($label === 'min_level') {
						continue;
					}
					if (isset($attr_altnames[$label])) {
						$label = $attr_altnames[$label];
					}
					?>
						<div class="row">
							<div class="label"><?php echo strtoupper(str_replace("_", " ", $label)); ?></div>
							<div class="data"><?php echo $data; ?></div>
						</div>
					<?php
					if($label=="rate") {
						if($factor!=1) {
							$rate = ceil($data*$factor);
							?>
				<div class="label">EFFECTIVE RATE for player level <?php echo htmlspecialchars($level); ?> </div>
					<div class="data"><?php echo $rate; ?></div>
							<?php
						}
					}
					if($label=="def") {
							if($factor!=1) {
								$def = floor($data/$factor);
								?>
				<div class="label">EFFECTIVE DEF for player level <?php echo  htmlspecialchars($level); ?></div>
				<div class="data"><?php echo $def; ?></div>
								<?php
							}
					}
				}

				if ($min_level > -1) {
				?>
						<div class="row">
							<div class="label">MIN LEVEL</div>
							<div class="data"><?php echo $min_level; ?></div>
						</div>
				<?php
				}
				if ($min_level > 0) {
				?>
						<br>
						My level ...
						<form method="post" action="/item/<?php echo surlencode($m->class).'/'.surlencode($m->name); ?>.html">
							<input type="text" name="level" size="3" maxlength="3">
						<input type="submit" value="Check stats">
						</form>
				<?php
				}
				?>
	</div>

				<?php
				if (count($m->susceptibilities) > 0) {
					?>
	<div class="table">
		<div class="title">Resistances</div>
					<?php
					foreach($m->susceptibilities as $label=>$data) {
						?>
			<div class="row">
				<div class="label"><?php echo strtoupper($label); ?></div>
				<div class="data"><?php echo $data; ?></div>
			</div>
						<?php
					}
					?>
	</div>
					<?php
				}
				?>

			<div class="table">
				<div class="title">Dropped by</div>
					<div style="float: left; width: 100%;">
				<?php
				$monsters=getMonsters();
				foreach($monsters as $monster) {
					foreach($monster->drops as $k) {
						if($k["name"]==$m->name) {
							?>
							<div class="row">
								<?php $monster->showImageWithPopup() ?>
								<span class="block label"><?php echo $monster->name; ?></span>
								<?php if (isset($k["quantity"])) { ?>
								<div class="data">Drops <?php echo renderAmount($k["quantity"]); ?></div>
								<?php } if (isset($k["probability"])) { ?>
								<div class="data">Probability: <?php echo formatNumber($k["probability"]); ?>%</div>
								<?php } if (isset($k["special"]) && $k["special"] === true) { ?>
								<div class="data">Special drop.</div>
								<?php } if (isset($k["note"])) { ?>
								<div class="data"><?php echo $k["note"]; ?></div>
								<?php } ?>
								<div style="clear: left;"></div>
							</div>
							<?php
						}
					}
				}
				?>
					</div>
					<div style="clear: left;"></div>
				</div> <!-- Dropped by -->

				<?php
				// NPC buyers & sellers
				$merchants = getItemMerchants($m->name);
				if (isset($merchants["sellers"]) && sizeof($merchants["sellers"]) > 0) {
					?>

			<div class="table">
				<div class="title">Sold by</div>
				<div style="float:left; width:100%;">
					<?php
					$this->buildMerchantList($merchants["sellers"]);
					?>

				</div>
				<div style="clear:left;"></div>
			</div>

					<?php
				}
				if (isset($merchants["buyers"]) && sizeof($merchants["buyers"]) > 0) {
					?>

			<div class="table">
				<div class="title">Bought by</div>
				<div style="float:left; width:100%;">
					<?php
					$this->buildMerchantList($merchants["buyers"]);
					?>

				</div>
				<div style="clear:left;"></div>
			</div>

					<?php
				}
				?>

	</div> <!-- table -->

				<?php
				endBox();
				$this->writeRelatedPages('I.'.strtolower($m->name), 'Stendhal_Quest', 'Quests');
				$this->includeJs();
			}
		} /* foreach (item) */

	} /* writeContent() */

	public function getBreadCrumbs() {
		if (!$this->isExact || !$this->found) {
			return null;
		}

		return array('World Guide', '/world.html',
			'Item', '/item/',
			ucfirst($_GET['class']), '/item/'.$_GET['class'].'.html',
			ucfirst($this->name), '/item/'.$this->class.'/'.$this->name.'.html'
			);
	}

	private function buildMerchantList($merchants) {
		foreach ($merchants as $merchant=>$price) {
			echo "<div class=\"row\">";
			$npc = NPC::getNPC($merchant);
			if (isset($npc)) {
				echo "<a href=\"".rewriteURL("/npc/".surlencode($merchant).".html")."\">";
				echo $npc->getBorderedImage();
				echo "</a>";
			}
			echo "<span class=\"block label\">".$merchant."</span>";
			echo "<div class=\"data\">".$price."</div>";
			echo "<div style=\"clear:left;\"></div></div>";
		}
	}
}

$page = new ItemPage();
