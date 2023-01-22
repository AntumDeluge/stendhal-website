<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin
 Copyright (C) 2008-2023 The Arianne Project

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
/*
 * A class representing a monster.
 */

function sortByLevelAndName($a, $b) {
	$res = ($a->level - $b->level);
	if ($res != 0) {
		return $res;
	}

	if ($a->name == $b->name) {
		return 0;
	}
	return ($a < $b) ? -1 : 1;
}




class Monster {
	public static $classes=array();
	public static $monsters=array();

	/* Name of the monster */
	public $name;
	/* Description of the monster */
	public $description;
	/* Class of the monster */
	public $class;
	/* GFX URL of the monster. */
	public $gfx;
	/* Level of the monster */
	public $level;
	/* XP value of the monster */
	public $xp;
	/* respawn value of the monster */
	public $respawn;
	/* Times this monster has been killed */
	public $kills;
	/* Players killed by this monster class */
	public $killed;
	/* Attributes of the monster as an array attribute=>value */
	public $attributes;
	/* susceptibilities and resistances */
	public $susceptibilities;
	/* Stuff this creature drops as an array (item, quantity, probability) */
	public $drops;
	/* Locations where this monster is found. */
	public $locations;

	function __construct($name, $description, $class, $gfx, $level, $xp, $respawn, $attributes, $susceptibilities, $drops) {
		$this->name=$name;
		$this->description=$description;
		$this->class=$class;
		self::$classes[$class]=0;
		$this->gfx=$gfx;
		$this->level=$level;
		$this->xp=$xp;
		$this->respawn=$respawn;
		$this->attributes=$attributes;
		$this->drops=$drops;
		$this->susceptibilities=$susceptibilities;
	}

	function showImage() {
		return $this->gfx;
	}

	function showImageWithPopup() {
		$popup = '<div class="stendhalCreature"><span class="stendhalCreatureIconNameBanner">';

		$popup .= '<span class="stendhalCreatureIcon">';
		$popup .= '<img src="' . htmlspecialchars($this->gfx) . '" />';
		$popup .= '</span>';

		$popup .= '<a href="'.rewriteURL('/creature/'.surlencode($this->name).'.html').'">';
		$popup .= $this->name;
		$popup .= '</a></span>';

		$popup .= '<br />';
		$popup .= 'Class: ' . htmlspecialchars(ucfirst($this->class)) . '<br />';
		$popup .= 'Level: ' . htmlspecialchars($this->level) . '<br />';

		foreach($this->attributes as $label=>$data) {
			$popup .= htmlspecialchars(ucfirst($label)) . ': ' . htmlspecialchars($data) . '<br />';
		}

		if (isset($this->description) && ($this->description != '')) {
			$popup .= '<br />' . $this->description . '<br />';
		}

		$popup .= '</div>';

		echo '<a href="'.rewriteURL('/creature/'.surlencode($this->name).'.html').'" class="overliblink" title="'.htmlspecialchars($this->name).'" data-popup="'.htmlspecialchars($popup).'">';
		echo '<img class="creature" src="'.htmlspecialchars($this->showImage()). '" alt=""></a>';
	}

	static function getClasses() {
		return Monster::$classes;
	}

	function fillKillKilledData() {
		$numberOfDays=14;

		$this->kills=array();
		$this->killed=array();

		for($i=0;$i<$numberOfDays;$i++) {
			$this->kills[$i]=0;
			$this->killed[$i]=0;
		}

		/*
		 * Amount of times this creature has been killed by a player or another creature.
		 */
		$rows = DB::game()->query("
			SELECT to_days(NOW()) - to_days(day) As day_offset, sum(cnt) As amount
			FROM kills
			WHERE killed_type='C' AND killer_type='P'
			AND killed='" . mysql_real_escape_string($this->name) . "'
			AND date_sub(curdate(), INTERVAL " . $numberOfDays . " DAY) < day
			GROUP BY day");

		foreach ($rows as $row) {
			$this->kills[$row['day_offset']] = $row['amount'];
		}

		/*
		 * Amount of times this creature has killed a player.
		 */
		$rows = DB::game()->query("
			SELECT to_days(NOW()) - to_days(day) As day_offset, sum(cnt) As amount
			FROM kills
			WHERE killed_type='P' AND killer_type='C'
			AND killer='" . mysql_real_escape_string($this->name) . "'
			AND date_sub(curdate(), INTERVAL " . $numberOfDays . " DAY) < day
			GROUP BY day");

		foreach ($rows as $row) {
			$this->killed[$row['day_offset']] = $row['amount'];
		}
	}
}


function existsMonster($name) {
	return getMonster($name) !== null;
}


function getMonster($name) {
	$monsters=getMonsters();
	foreach($monsters as $m) {
		if($m->name==$name) {
			return $m;
		}
	}
	return null;
}


function listOfMonsters($monsters) {
	$data='';
	foreach($monsters as $m) {
		$data=$data.'"'.$m->name.'",';
	}
	return substr($data, 0, strlen($data)-1);
}


function listOfMonstersEscaped($monsters) {
	$data='';
	foreach($monsters as $m) {
		$data=$data.'"'.mysql_real_escape_string($m->name).'",';
	}
	return substr($data, 0, strlen($data)-1);
}

function getMostKilledMonster($monsters) {
	$query = "SELECT killed, count(*) As amount
		FROM kills
		WHERE killed_type='C' AND killer_type='P' AND date_sub(curdate(), INTERVAL 7 DAY) < day
		GROUP BY killed
		ORDER BY amount DESC
		LIMIT 1;";

	$stmt = DB::game()->prepare($query);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();

	if (!is_bool($row)) {
		$monster = array(getMonster($row['killed']), $row['amount']);
		return $monster;
	}

	return null;
}

function getBestKillerMonster($monsters) {
	$query = "SELECT killer, count(*) As amount
		FROM kills
		WHERE killer_type='C' AND killed_type='P' AND date_sub(curdate(), INTERVAL 7 DAY) < day
		GROUP BY killer
		ORDER BY amount DESC
		LIMIT 1;";

	$stmt = DB::game()->prepare($query);
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();

	if (!is_bool($row)) {
		$monster = array(getMonster($row['killer']), $row['amount']);
		return $monster;
	}

	return null;
}


/**
 * Returns a list of Monsters
 */
function getMonsters() {
	global $cache;
	if (!isset(Monster::$monsters) || sizeof(Monster::$monsters) == 0
			|| !isset(Monster::$classes) || sizeof(Monster::$classes) == 0) {
		Monster::$monsters = $cache->fetchAsArray('stendhal_creatures');
		Monster::$classes = $cache->fetchAsArray('stendhal_creatures_classes');
	}

	if ((is_array(Monster::$monsters) && (sizeof(Monster::$monsters) != 0))
			&& (is_array(Monster::$classes) && (sizeof(Monster::$classes) != 0))) {
 		return Monster::$monsters;
	}
	Monster::$classes = array();

	$monstersXMLConfigurationFile="data/conf/creatures.xml";
	$monstersXMLConfigurationBase='data/conf/';

	$content = file($monstersXMLConfigurationFile);
	$temp = implode('', $content);
	$monsterfiles = XML_unserialize($temp);
	$monsterfiles = $monsterfiles['groups'][0]['group'];

	$list=array();

	foreach($monsterfiles as $file) {
		if(isset($file['uri'])) {
			$content = file($monstersXMLConfigurationBase.$file['uri']);
			$temp = implode('', $content);
			$root =  XML_unserialize($temp);
			if (!isset($root['creatures'][0]['creature'])) {
				continue;
			}
			$creatures=$root['creatures'][0]['creature'];
			if (sizeof($creatures) < 2) {
				continue;
			}

			for($i=0;$i<sizeof($creatures)/2;$i++) {

				if (isset($creatures[$i.' attr']['condition'])) {
					if ($creatures[$i.' attr']['condition'][0] != '!') {
						continue;
					}
				}

				/*
				 * We omit hidden creatures.
				 */
				if(isset($creatures[$i]['hidden'])) {
					continue;
				}

				$name=$creatures[$i.' attr']['name'];
				if(isset($creatures[$i]['description'])) {
					$description=$creatures[$i]['description']['0'];
				} else {
					$description='';
				}

				$class=$creatures[$i]['type']['0 attr']['class'];
				$gfx=rewriteURL('/images/creature/'.$class.'/'.$creatures[$i]['type']['0 attr']['subclass'].'.png');

				$attributes = array();
				$attributes['atk']=$creatures[$i]['attributes'][0]['atk']['0 attr']['value'];
				if (isset($creatures[$i]['abilities'][0]['damage']['0 attr']['type'])) {
					$attributes['atk'] = $attributes['atk'].' ('.$creatures[$i]['abilities'][0]['damage']['0 attr']['type'].')';
				}
				$attributes['def']=$creatures[$i]['attributes'][0]['def']['0 attr']['value'];
				$attributes['speed']=$creatures[$i]['attributes'][0]['speed']['0 attr']['value'];
				$attributes['hp']=$creatures[$i]['attributes'][0]['hp']['0 attr']['value'];

				$level=$creatures[$i]['level']['0 attr']['value'];
				$xp=$creatures[$i]['experience']['0 attr']['value'];
				$respawn=$creatures[$i]['respawn']['0 attr']['value'];

				$susceptibilities=array();
				if (isset($creatures[$i]['abilities'][0]['susceptibility'])) {
					foreach($creatures[$i]['abilities'][0]['susceptibility'] as $susceptibility) {
						if (isset($susceptibility['type'])) {
							$susceptibilities[$susceptibility['type']]=round(100 / $susceptibility['value']);
						}
					}
				}

				$drops = array();
				if (isset($creatures[$i]['drops'])) {
					$temp = $creatures[$i]['drops'][0];
					if (isset($temp['item'])) {
						foreach($temp['item'] as $drop) {
							if(is_array($drop)) {
								$drops[]=array("name"=>$drop['value'],"quantity"=>$drop['quantity'], "probability"=>$drop['probability']);
							}
						}
					}
				}

				$list[]=new Monster($name, $description, $class, $gfx, $level, $xp, $respawn, $attributes, $susceptibilities, $drops);
			}
		}
	}

	uasort($list, 'sortByLevelAndName');
	Monster::$monsters = $list;
	$cache->store('stendhal_creatures', new ArrayObject($list));
	$cache->store('stendhal_creatures_classes', new ArrayObject(Monster::$classes));
	return $list;
}
