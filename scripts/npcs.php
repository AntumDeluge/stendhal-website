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


// shops lists
$shops;
$npcshops;

/**
 * Parses shop info from xml data.
 *
 * @param shopsdata
 *     XML data containing shops information.
 */
function parseShopsData($shopsdata) {
	global $shops;
	global $npcshops;

	if (!isset($shops)) {
		$shops = [];
	}
	if (!isset($npcshops)) {
		$npcshops = [];
	}

	if (sizeof($shopsdata) < 2) {
		return;
	}

	for ($idx = 0; $idx < sizeof($shopsdata) / 2; $idx++) {
		if (!isset($shopsdata[$idx." attr"])) {
			continue;
		}

		$attr = $shopsdata[$idx." attr"];
		if (!isset($attr["type"])) {
			continue;
		}

		$npcnames = [];
		$shopid = null;
		$shoptype = $attr["type"];
		if (isset($attr["npcs"])) {
			foreach (explode(",", $attr["npcs"]) as $npcname) {
				$npcnames[] = trim($npcname);
			}
			$npcnames = sizeof($npcnames) > 0 ? $npcnames : null;
		}
		if (isset($attr["name"])) {
			$shopid = $attr["name"];
		}

		$itemlist = [];
		if (isset($shopsdata[$idx]["item"])) {
			$contents = $shopsdata[$idx]["item"];
			for ($i = 0; $i < sizeof($contents) / 2; $i++) {
				$item = $contents[$i." attr"];
				if (isset($item["name"]) && isset($item["price"])) {
					$price = $item["price"];
					if (isset($item["pricemax"])) {
						$price .= "-".$item["pricemax"];
					}
					$itemlist[$item["name"]] = $price;
				}
			}
		}

		// names of shops whose contents are included in this one
		$includes = [];
		if (isset($attr["includes"])) {
			foreach (explode(",", $attr["includes"]) as $incshop) {
				$includes[] = trim($incshop);
			}
		}

		if (isset($shopid)) {
			// named shops
			if (sizeof($itemlist) > 0) {
				$shops[$shopid] = $itemlist;
			}
			if (sizeof($includes) > 0) {
				// temporary storage for shops contents to be included
				$shops[$shopid]["__includes__"] = $includes;
			}
			foreach ($npcnames as $npcname) {
				// store shop name for reference
				$npcshops[$npcname][$shoptype] = $shopid;
			}
		} else {
			// unnamed shops
			foreach ($npcnames as $npcname) {
				$npcshops[$npcname][$shoptype] = $itemlist;
				if (sizeof($includes) > 0) {
					$npcshops[$npcname][$shoptype."_includes"] = $includes;
				}
			}
		}
	}
}

/**
 * Loads registered shops from config.
 */
function loadShops() {
	//~ global $cache;
	global $shops;
	global $npcshops;

	// FIXME: caching not working
	//~ if (!isset(NPC::$shops) || sizeof(NPC::$shops) == 0) {
		//~ NPC::$shops = $cache->fetchAsArray("stendhal_shops");
	//~ }
	//~ if (is_array(NPC::$shops) && sizeof(NPC::$shops) > 0) {
		//~ return;
	//~ }

	if (isset($shops) && isset($npcsshops)) {
		return;
	}

	$content = file("data/conf/shops.xml");
	$tmp = implode("", $content);
	$root = XML_unserialize($tmp);

	if (!isset($root["shops"][0]["shop"]) && !isset($root["shops"][0]["shopref"])) {
		$shops = [];
		$npcshops = [];
		//~ NPC::$shops = $npcshops;
		//~ $cache->store("stendhal_shops", new ArrayObject($npcshops));
		return;
	}

	if (isset($root["shops"][0]["shop"])) {
		parseShopsData($root["shops"][0]["shop"]);
	}
	if (isset($root["shops"][0]["shopref"])) {
		parseShopsData($root["shops"][0]["shopref"]);
	}

	// add items included from other shops
	foreach ($shops as $shopid=>$itemlist) {
		if (isset($itemlist["__includes__"])) {
			foreach ($itemlist["__includes__"] as $includeid) {
				if (isset($shops[$includeid])) {
					$shops[$shopid] = array_merge($shops[$shopid], $shops[$includeid]);
				}
			}
			// this is called after in case the merged shop had an "__includes__" key as well
			unset($shops[$shopid]["__includes__"]);
		}
	}
	foreach ($npcshops as $npcname=>$shopdata) {
		foreach (["sell", "buy"] as $shoptype) {
			if (isset($shopdata[$shoptype]) && isset($shopdata[$shoptype."_includes"])) {
				$includes = $shopdata[$shoptype."_includes"];
				foreach ($includes as $includeid) {
					if (isset($shops[$includeid])) {
						$npcshops[$npcname][$shoptype] = array_merge($npcshops[$npcname][$shoptype], $shops[$includeid]);
					}
					unset($npcshops[$npcname][$shoptype."_includes"]);
				}
			}
		}
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
	global $shops;
	global $npcshops;

	loadShops();

	if (!isset($npcshops[$npcname])) {
		return;
	}

	$tmp = $npcshops[$npcname];
	$sells = isset($tmp["sell"]) ? $tmp["sell"] : null;
	$buys = isset($tmp["buy"]) ? $tmp["buy"] : null;
	if (gettype($sells) === "string" && isset($shops[$sells])) {
		$sells = $shops[$sells];
	}
	if (gettype($buys) === "string" && isset($shops[$buys])) {
		$buys = $shops[$buys];
	}

	$npcshop = [];
	if (is_array($sells)) {
		$npcshop["sell"] = $sells;
	}
	if (is_array($buys)) {
		$npcshop["buy"] = $buys;
	}
	return $npcshop;
}
