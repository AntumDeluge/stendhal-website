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

/**
 * A class that represents an NPC, with details on the name, stats, location and what it looks like.
 */
class NPC {
	public static $shops;

	public $name;
	public $title;
	public $class;
	public $outfit;
	public $imagefile;
	public $level;
	public $hp;
	public $base_hp;
	public $zone;
	public $pos;
	public $x;
	public $y;
	public $description;
	public $job;
	public $altimage;

	function __construct($name, $title, $class, $outfit, $level, $hp, $base_hp, $zone, $pos, $x, $y, $description, $job, $altimage) {
		$this->name=$name;
		$this->title=$title;
		$this->class=$class;
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
		$this->imagefile=rewriteURL($imagefile);
		$this->level=$level;
		$this->hp=$hp;
		$this->base_hp=$base_hp;
		$this->zone=$zone;
		$this->pos=$pos;
		$this->x=$x;
		$this->y=$y;
		$this->description=$description;
		$this->job=$job;
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
}


/**
 * Retrieves shops registered for NPCs.
 */
function getShops() {
	global $cache;

	// FIXME: caching not working
	if (!isset(NPC::$shops) || sizeof(NPC::$shops) == 0) {
		NPC::$shops = $cache->fetchAsArray("stendhal_shops");
	}

	if (is_array(NPC::$shops) && sizeof(NPC::$shops) > 0) {
		return NPC::$shops;
	}

	$shops = [];

	$content = file("data/conf/shops.xml");
	$tmp = implode("", $content);
	$root = XML_unserialize($tmp);

	if (!isset($root["shops"][0]["shop"])) {
		NPC::$shops = $shops;
		$cache->store("stendhal_shops", new ArrayObject($shops));
		return $shops;
	}

	$shopslist = $root["shops"][0]["shop"];
	if (sizeof($shopslist) < 2) {
		NPC::$shops = $shops;
		$cache->store("stendhal_shops", new ArrayObject($shops));
		return $shops;
	}

	for ($idx = 0; $idx < sizeof($shopslist) / 2; $idx++) {
		if (!isset($shopslist[$idx." attr"])) {
			continue;
		}
		if (!isset($shopslist[$idx]["item"])) {
			continue;
		}

		$attr = $shopslist[$idx." attr"];
		if (!isset($attr["name"]) || !isset($attr["type"]) || !isset($attr["npcs"])) {
			continue;
		}

		$contents = $shopslist[$idx]["item"];
		$itemlist = [];
		for ($i = 0; $i < sizeof($contents) / 2; $i++) {
			$item = $contents[$i." attr"];
			if (isset($item["name"]) && isset($item["price"])) {
				$itemlist[$item["name"]] = $item["price"];
			}
		}

		$sname = $attr["name"];
		$stype = $attr["type"];
		$snpcs = $attr["npcs"];

		$npcnames = [];
		while (strlen($snpcs) > 0) {
			$delim = strpos($snpcs, ",");
			if ($delim == null) {
				$npcnames[] = trim($snpcs);
				$snpcs = "";
			} else {
				$npcnames[] = trim(substr($snpcs, 0, $delim));
				$snpcs = substr($snpcs, $delim+1);
			}
		}

		foreach ($npcnames as $npcname) {
			$shops[$npcname][$stype] = $itemlist;
		}
	}

	NPC::$shops = $shops;
	$cache->store("stendhal_shops", new ArrayObject($shops));
	return $shops;
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
function getShop($npcname) {
	$shops = getShops();
	if (isset($shops[$npcname])) {
		return $shops[$npcname];
	}
}
