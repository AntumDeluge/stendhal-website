<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2009-2023 Hendrik Brummermann

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

require_once("scripts/entity.php");


/**
 * A class that represents an NPC, with details on the name, stats, location and what it looks like.
 */
class NPC extends Entity {
	public static $shops;

	public $title;
	public $outfit;
	public $imagefile;
	public $level;
	public $hp;
	public $base_hp;
	public $zone;
	public $pos;
	public $x;
	public $y;
	public $job;
	public $altimage;

	function __construct($name, $title, $class, $outfit, $level, $hp, $base_hp, $zone, $pos, $x, $y, $description, $job, $altimage) {
		parent::__construct($name, $description, $class);
		$this->title=$title;
		$this->outfit=$outfit;
		$imagefile = '/images/npc/'.surlencode($class).'.png';
		if (strpos($imagefile, '/npc/../monsters/') !== false) {
			$imagefile = str_replace('/npc/../monsters/', '/creature/', $imagefile);
		}
		if (isset($outfit) && $outfit != '') {
			$imagefile = '/images/outfit/'.surlencode($outfit).'.png';
		}
		if (isset($altimage) && $altimage != '') {
			$imagefile = '/images/npc/alternative/'.surlencode($altimage).'.png';
		}
		$this->level=$level;
		$this->hp=$hp;
		$this->base_hp=$base_hp;
		$this->zone=$zone;
		$this->pos=$pos;
		$this->x=$x;
		$this->y=$y;
		$this->job=$job;

		$this->setImage(rewriteURL($imagefile));
	}


	/**
	 * gets the names NPC from the database.
	 */
	static function getNPC($name) {
		$npcs = NPC::_getNPCs('select * from npcs where name="'.mysql_real_escape_string($name).'" limit 1');
		if (count($npcs) > 0) {
			return $npcs[0];
		}
		return null;
	}


	/**
	 * Returns a list of npcs that meet the given condition.
	 * Note: Parmaters must be sql escaped.
	 */
	static function getNPCs($where='', $sortby='name', $cond='') {
		return NPC::_getNPCs('select * from npcs '.$where.' order by '.$sortby.' '.$cond);
	}


	static private function _getNPCs($query) {
		$NO_ZONE = array(
			'Azazel', 'Cherubiel', 'Gabriel', 'Ophaniel', 'Raphael', 'Uriel', 'Zophiel',
			'Ben', 'Goran', 'Mary', 'Zak',
			'Easter Bunny', 'Rose Leigh', 'Santa',
			'Amber', 'Skye',
			'Red Crystal', 'Purple Crystal', 'Yellow Crystal', 'Pink Crystal', 'Blue Crystal',
			'Rengard', 'Mizuno', 'Niall Breland');

		$result = DB::game()->query($query);
		$list = array();

		foreach($result as $row) {
			if (isset($row["cloned"])) {
				continue;
			}
			$zone = $row['zone'];
			$pos = 'at ' . $row['x'] . ', ' . $row['y'];
			if (in_array($row['name'], $NO_ZONE)) {
				$zone = 'unknown';
				$pos = '';
			}
			$outfit = $row['outfit'];
			if (isset($row['outfit_layers'])) {
				$outfit = $row['outfit_layers'];
			}
			$list[]=new NPC($row['name'],
				$row['title'],
				$row['class'],
				$outfit,
				$row['level'],
				$row['hp'],
				$row['base_hp'],
				$zone,
				$pos,
				$row['x'],
				$row['y'],
				$row['description'],
				$row['job'],
				$row['image']);
		}
		return $list;
	}

	function getShop() {
		return getNPCShop($this->name);
	}
}

function formatOutfit($outfitstring) {
	$outfit = explode(",", $outfitstring);
	for ($idx = 0; $idx < sizeof($outfit); $idx++) {
		$layer = str_replace("=", "-", $outfit[$idx])."-0";
		$outfit[$idx] = $layer;
	}
	$outfitstring = implode("_", $outfit);
	return $outfitstring;
}

function parseAddRequire($reqdata) {
	$required;
	if (!is_array($reqdata)) {
		return;
	}
	for ($idx = 0; $idx < sizeof($reqdata); $idx++) {
		if (!isset($reqdata[$idx." attr"])) {
			continue;
		}
		$tmp = $reqdata[$idx." attr"];
		if (isset($tmp["name"]) && isset($tmp["count"])) {
			$name = $tmp["name"];
			$item = getItem($name);
			if ($item != null) {
				$name = $item->createNameLink();
			}
			if (!isset($required)) {
				$required = $tmp["count"]." ".$name;
			} else {
				$required .= " + ".$tmp["count"]." ".$name;
			}
		}
	}
	return $required;
}

// shops lists
$npcshops;

/**
 * Parses shop info from xml data.
 *
 * @param shopsdata
 *     XML data containing shops information.
 */
function parseShopsData($shopsdata) {
	global $npcshops;

	if (!isset($npcshops)) {
		$npcshops = [];
	}

	if (sizeof($shopsdata) < 2) {
		return;
	}

	for ($idx = 0; $idx < sizeof($shopsdata) / 2; $idx++) {
		if (!isset($shopsdata[$idx]["merchant"])) {
			continue;
		}

		$attr = [];
		if (isset($shopsdata[$idx." attr"])) {
			$attr = $shopsdata[$idx." attr"];
		}
		if (!isset($attr["type"])) {
			continue;
		}
		$shoptype = $attr["type"];
		if ($shoptype === "trade") {
			$shoptype = "sell";
		}

		$merchants = [];
		$tmp = $shopsdata[$idx]["merchant"];
		for ($m = 0; $m < sizeof($tmp) / 2; $m++) {
			if (!isset($tmp[$m." attr"])) {
				continue;
			}
			$merchant = $tmp[$m." attr"];
			if (isset($merchant["name"])) {
				$merchants[$merchant["name"]] = isset($merchant["note"]) ? $merchant["note"] : null;
			}
		}

		$itemlist = [];
		$contents = null;
		if ($shoptype === "outfit") {
			if (isset($shopsdata[$idx]["outfit"])) {
				$contents = $shopsdata[$idx]["outfit"];
			}
		} else {
			if (isset($shopsdata[$idx]["item"])) {
				$contents = $shopsdata[$idx]["item"];
			}
		}
		if (isset($contents)) {
			for ($i = 0; $i < sizeof($contents) / 2; $i++) {
				if (!isset($contents[$i." attr"])) {
					continue;
				}

				$item = $contents[$i." attr"];
				$requirealso = null;
				if (isset($item["name"])) {
					if (isset($contents[$i]["for"])) {
						$requirealso = parseAddRequire($contents[$i]["for"]);
					}

					$price = isset($item["price"]) ? $item["price"] : "";
					if (strlen($price) > 0 && isset($item["pricemax"])) {
						$price .= "-".$item["pricemax"];
					}
					$price = strlen($price) > 0 ? $price." money" : $price;
					if (isset($requirealso)) {
						if (strlen($price) > 0) {
							$price .= " + ";
						}
						$price .= $requirealso;
					}
					$itemname = $item["name"];
					$itemlist[$itemname]["price"] = $price;
					if ($shoptype === "outfit" && isset($item["layers"])) {
						$itemlist[$itemname]["layers"] = formatOutfit($item["layers"]);
					}
					if (isset($item["note"])) {
						$itemlist[$itemname]["note"] = $item["note"];
					}
				}
			}
		}

		foreach ($merchants as $npcname=>$shopnote) {
			// allow merchants configured for multiple shops
			if (isset($npcshops[$npcname][$shoptype])) {
				$itemlist = array_merge($npcshops[$npcname][$shoptype], $itemlist);
			}
			if (isset($shopnote)) {
				$itemlist["__shopnote__"] = $shopnote;
			}
			$npcshops[$npcname][$shoptype] = $itemlist;
		}
	}
}

/**
 * Loads registered shops from config.
 */
function loadShops() {
	global $cache;
	global $npcshops;

	if (!isset(NPC::$shops) || sizeof(NPC::$shops) == 0) {
		NPC::$shops = $cache->fetchAsArray("stendhal_shops");
	}
	if (is_array(NPC::$shops) && sizeof(NPC::$shops) > 0) {
		return;
	}


	$content = file("data/conf/shops.xml");
	$tmp = implode("", $content);
	$groot = XML_unserialize($tmp);

	if (!isset($groot["groups"][0]["group"])) {
		return;
	}

	$tmp = $groot["groups"][0]["group"];
	$groups = [];
	for ($idx = 0; $idx < sizeof($tmp) / 2; $idx++) {
		if (isset($tmp[$idx." attr"]) && isset($tmp[$idx." attr"]["uri"])) {
			$groups[] = "data/conf/".$tmp[$idx." attr"]["uri"];
		}
	}
	foreach ($groups as $group) {
		$content = file($group);
		$tmp = implode("", $content);
		$root = XML_unserialize($tmp);
		if (!isset($root["shops"][0]["shop"])) {
			continue;
		}
		parseShopsData($root["shops"][0]["shop"]);
	}

	//~ NPC::$shops = $npcshops;
	//~ $cache->store("stendhal_shops", new ArrayObject($npcshops));
}

/**
 * Retrieves shop lists for an NPC.
 *
 * @param npcname
 *     Name of NPC for which shop is associated.
 * @return
 *     "sell" &/or "buy" shop lists or <code>null</code> if the NPC
 *     does not have a shop.
 */
function getNPCShop($npcname) {
	global $npcshops;

	loadShops();
	if (isset($npcshops[$npcname])) {
		return $npcshops[$npcname];
	}
}

/**
 * Retrieves all NPC shops.
 */
function getNPCShops() {
	global $npcshops;

	loadShops();
	return $npcshops;
}
