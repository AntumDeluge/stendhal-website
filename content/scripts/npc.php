<?php
/*
 * Stendhal website - a website to manage and ease playing of Stendhal game
 * Copyright (C) 2008-2023  The Arianne Project
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

class NPCPage extends Page {
	private $name;
	private $npcs;

	public function __construct() {
		$this->name = preg_replace('/_/', ' ', trim($_REQUEST['name']));
		$this->npcs = NPC::getNPCs('where name="'.mysql_real_escape_string($this->name).'"', 'name');
	}

	public function writeHttpHeader() {
		global $protocol;
		if (sizeof($this->npcs)==0) {
			header('HTTP/1.0 404 Not Found');
			return true;
		}
		if ((strpos($_REQUEST['name'], ' ') !== FALSE) || isset($_REQUEST['search'])) {
			header('HTTP/1.0 301 Moved permanently.');
			header('Location: '.$protocol.'://'.$_SERVER['SERVER_NAME'].preg_replace('/&amp;/', '&', rewriteURL('/npc/'.preg_replace('/[ ]/', '_', $this->name.'.html'))));
			return false;
		}

		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>NPC '.htmlspecialchars($this->name).STENDHAL_TITLE.'</title>';
		if(sizeof($this->npcs)==0) {
			echo '<meta name="robots" content="noindex">';
		}
	}

	function writeContent() {
		if (sizeof($this->npcs) == 0) {
			startBox("<h1>No such NPC</h1>");
			echo 'There is no such NPC in Stendhal.<br>Please make sure you spelled it correctly.';
			endBox();
			return;
		}

		$npc=$this->npcs[0];
		startBox('<h1>'.htmlspecialchars($npc->name).'</h1>');

		echo '<div class="table">';
		echo '<div class="title">Details</div>';
		echo '<img class="bordered_image" src="'.htmlspecialchars($npc->imagefile).'" alt="">';
		echo '<div class="statslabel">Name:</div><div class="data">'.htmlspecialchars($npc->name).'</div>';
		echo '<div class="statslabel">Zone:</div><div class="data">';
		if ($npc->pos != '') {
			echo '<a href="/world/atlas.html?poi='.htmlspecialchars($npc->name).'">'.htmlspecialchars($npc->zone).' '.htmlspecialchars($npc->pos).'</a>';
		} else {
			echo htmlspecialchars($npc->zone);
		}
		echo '</div>';

		if ($npc->level > 0) {
			echo '<div class="statslabel">Level:</div><div class="data">'.$npc->level.'</div>';
			echo '<div class="statslabel">HP:</div><div class="data">'.$npc->hp . '/' . $npc->base_hp.'</div>';
		}

		if ((isset($npc->job) && strlen($npc->job) > 0)) {
			echo '<div class="sentence">'.htmlspecialchars(str_replace('#', '', $npc->job)).'</div>';
		}
		if ((isset($npc->description) && strlen($npc->description) > 0)) {
			echo '<div class="sentence">'.htmlspecialchars($npc->description).'</div>';
		}
		echo '</div>';
		endBox();

		// quests involving this NPC
		$this->writeRelatedPages('N.'.strtolower($npc->name), 'Stendhal_Quest', 'Quests');

		// shop lists
		$shop = getShop($npc->name);
		if (isset($shop)) {
			echo "\n\n		";
			startBox("Shops");
			// FIXME: global CSS not working here
			echo "\n		<div class=\"table shops\" style=\"display:flex;\">";
			if (isset($shop["sell"])) {
				$this->buildShop("Sells", $shop["sell"]);
			}
			if (isset($shop["buy"])) {
				$this->buildShop("Buys", $shop["buy"]);
			}
			echo "</div> <!-- class:shops -->\n\t\t";
			endBox();
			echo "\n\n";
		}
	}

	public function getBreadCrumbs() {
		if (sizeof($this->npcs) == 0) {
			return null;
		}

		return array('World Guide', '/world.html',
				'NPC', '/npc/',
				ucfirst($this->name), '/npc/'.$this->name.'.html'
		);
	}

	/**
	 * Creates a shop list.
	 *
	 * @param stitle
	 *     Header to show type of shop (Sells or Buys).
	 * @param slist
	 *     Key-value list of shop items.
	 */
	private function buildShop($stitle, $slist) {
		?>

		<!-- FIXME: why isn't global CSS working here? class="shoplist" -->
		<div class="shoplist" style="float:left; width:50%;"><?php echo $stitle ?>

			<table>
		<?php
		$idx = 0;
		foreach($slist as $iname=>$iprice) {
			$item = getItem($iname); // FIXME: doesn't work for seeds & bulbs
			if ($idx > 0) {
				echo "		";
			}
			echo "		<tr>\n					";
			if ($item != null) {
				echo "<td>".$item->generateImageWithPopup()."</td>";
			}
			echo "<td>".$iname."</td><td>".$iprice."</td>";
			echo "\n				</tr>\n";
			$idx++;
		}
		?>
			</table>
		</div> <!-- class:shoplist -->
		<?php
	}
}

$page = new NPCPage();
