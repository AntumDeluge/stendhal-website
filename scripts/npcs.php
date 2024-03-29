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
		// FIXME: deprecated, "hide_location" attribute now set in database
		$NO_ZONE = array(
			'Azazel', 'Cherubiel', 'Gabriel', 'Ophaniel', 'Raphael', 'Uriel', 'Zophiel',
			'Ben', 'Goran', 'Mary', 'Zak',
			'Easter Bunny', 'Rose Leigh', 'Santa',
			'Amber', 'Skye',
			'Red Crystal', 'Purple Crystal', 'Yellow Crystal', 'Pink Crystal', 'Blue Crystal',
			'Rengard', 'Mizuno', 'Niall Breland',
			'Avalon', 'Cody', 'Mariel', 'Opal');

		$result = DB::game()->query($query);
		$list = array();

		foreach($result as $row) {
			if (isset($row["cloned"])) {
				continue;
			}
			$zone = $row['zone'];
			$pos = 'at ' . $row['x'] . ', ' . $row['y'];
			if (isset($row['hide_location']) && $row['hide_location'] > 0 || in_array($row['name'], $NO_ZONE)) {
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

	/**
	 * @deprecated
	 */
	function getShop() {
		$shops = new Shops();
		return $shops->getNPCShop($this->name);
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
