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
		echo '<img class="bordered_image" src="'.htmlspecialchars($npc->imageurl).'" alt="">';
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

		// shop lists
		$shops = new Shops();
		$npc_shops = [];
		foreach (["buy", "sell", "trade", "outfit"] as $stype) {
			$sinv = $shops->getItemsForNPC($npc->name, $stype);
			if (sizeof($sinv) > 0) {
				$npc_shops[$stype] = $sinv;
			}
		}

		if (sizeof($npc_shops) > 0) {
			startBox("Shops");
			?>
			<div class="table">
			<div class="shops">
			<?php
			foreach ($npc_shops as $stype => $sinv) {
				$sid = $shops->getId($npc->name, $stype);
				$snotes = $shops->getNotes($sid);
				$add_layers = null;
				if ($stype === "outfit" && isset(Shops::$outfits_add_layers[$sid])) {
					$add_layers = Shops::$outfits_add_layers[$sid];
				}
				$value_label = "Price";
				if ($stype === "buy") {
					$value_label = "Value";
				}
				$this->buildShop($sinv, $stype, $value_label, $snotes, $add_layers);
			}
			?>
			</div>
			</div>
			<div style="clear:left;"></div>
			<?php
			endBox();
		}

		// quests involving this NPC
		$this->writeRelatedPages('N.'.strtolower($npc->name), 'Stendhal_Quest', 'Quests');
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
	 * @param $sinv
	 *   Shop inventory information.
	 * @param $stype
	 *   "buy", "sell", or "outfit".
	 * @param $value_label
	 *   Label to show for item price.
	 * @param $snotes
	 *   Notes about merchants & items.
	 * @param add_layers
	 *   Layers to be added to each outfit preview.
	 */
	private function buildShop($sinv, $stype, $value_label, $snotes=[], $add_layers="") {
		$npcname = $this->name;
		$itemshop = in_array($stype, ["sell", "buy", "trade"]);
		$slabel = "Sells/Loans outfits";
		if ($itemshop) {
			$slabel = ucwords($stype) . "s";
		}

		$merchant_note = isset($snotes["merchants"][$npcname]) ? $snotes["merchants"][$npcname] : null;
		$items_notes = isset($snotes["items"]) ? $snotes["items"] : [];
		?>

		<div class="shoplist">
		<div class="title"><?php echo htmlspecialchars($slabel);
		if (isset($merchant_note)) {
			?>
			<span class="shopnote" style="font-weight:normal; font-size:small;">(<?php echo htmlspecialchars($merchant_note); ?>)</span>
			<?php
		}
		?></div>
		<?php

		$outfit_layers_order = explode(",", "body,dress,head,mouth,eyes,mask,hair,hat,detail");
		$apply_outfits = [];
		if ($add_layers) {
			if (gettype($add_layers) === "array") {
				if (isset($add_layers["apply"])) {
					$apply_outfits = $add_layers["apply"];
				}
				$add_layers = $add_layers["layers"];
			}
			$add_layers = explode(",", $add_layers);
		}

		foreach ($sinv as $invitem) {
			$iname = $invitem["name"];
			?>
			<div class="row">
			<?php
			if ($itemshop) {
				$item = getItem($iname);
				if ($item != null) {
					echo $item->generateImageWithPopup();
				}
			} else {
				$original_layers = explode(",", $invitem["outfit"]);
				$actual_layers = [];
				if (!$add_layers) {
					$actual_layers = $original_layers;
				} else {
					foreach ($outfit_layers_order as $lname) {
						$ltmp = "";
						foreach ($original_layers as $linfo) {
							if (explode("=", $linfo)[0] === $lname) {
								$ltmp = $linfo;
								break;
							}
						}
						if (!$ltmp && (!$apply_outfits || in_array($iname, $apply_outfits))) {
							foreach ($add_layers as $linfo) {
								if (explode("=", $linfo)[0] === $lname) {
									$ltmp = $linfo;
									break;
								}
							}
						}
						if ($ltmp) {
							$actual_layers[] = $ltmp;
						}
					}
				}
				// FIXME: "-0" is appended to each layer as we currently can't handle layer colors
				$outfit = str_replace(["=", ","], ["-", "-0_"], implode(",", $actual_layers)) . "-0";
				$outfitimage = "/images/outfit/".surlencode($outfit, 0).".png";
				?>
				<div style="clear:left;">
				<img class="creature" src="<?php echo $outfitimage;?>">
				</div>
				<?php
			}
			?>
			<span class="block label"><?php echo htmlspecialchars($iname);
			if (isset($items_notes[$iname])) {
				?>
				<span class="itemnote" style="font-weight:normal; font-style:italic; font-size:small;">(<?php echo htmlspecialchars($items_notes[$iname]); ?>)</span><?php
			}
			?></span>
			<?php
			$iprice = $invitem["price"];
			if ($iprice == 0) {
				unset($iprice);
			}

			if (isset($invitem["trade_for"])) {
				$trade_for = "";
				foreach (explode(",", $invitem["trade_for"]) as $trade_item) {
					if (!empty($trade_for)) {
						$trade_for .= ", ";
					}
					$trade_item = explode(":", $trade_item);
					$titem = getItem($trade_item[0]);
					if (isset($titem)) {
						$trade_item[0] = $titem->createNameLink();
					}
					$trade_for .= $trade_item[1] . " " . $trade_item[0];
				}

				if (!empty($trade_for)) {
					if (!isset($iprice)) {
						$iprice = $trade_for;
					} else {
						$iprice .= " ".getItem("money")->createNameLink().", ".$trade_for;
					}
				}
			}

			if (isset($iprice)) {
			?>
			<div class="data"><?php echo $value_label.": ".$iprice; ?></div>
			</div>
			<?php
			} else {
				echo("<div style=\"clear:left;\"/>");
			}
		}
		?>

		</div>
		<?php
	}
}

$page = new NPCPage();
