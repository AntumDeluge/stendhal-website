<?php
/*
 * Stendhal website - a website to manage and ease playing of Stendhal game
 * Copyright (C) 2008  Miguel Angel Blanch Lardin
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

require_once("scripts/entity.php");


/*
 * A class representing an item.
 */
class Item extends Entity {
	public static $classes=array();
	public static $items=array();

	/** Attributes of the item as an array attribute=>value */
	public $attributes;
	/** susceptibilities and resistances */
	public $susceptibilities;
	/** Where the item can be wore as an array slot=>item */
	public $equipableat;
	/** Whether item can be obtained by players */
	public $unattainable;

	function __construct($name, $description, $class, $gfx, $attributes, $susceptibilities, $equipableat,
			$unattainable=false) {
		parent::__construct($name, $description, $class);
		if (!$unattainable) {
			self::$classes[$class]=0;
		}
		$this->attributes=$attributes;
		$this->equipableat=$equipableat;
		$this->susceptibilities=$susceptibilities;
		$this->unattainable=$unattainable;

		$this->setImage($gfx);
	}

	function showImage() {
		return $this->imageurl;
	}

	static function getClasses() {
		// Note: The cache may expire individually on both variables. So we need to make sure that both variable
		if (!isset(Item::$items) || sizeof(Item::$items) == 0
			|| !isset(Item::$classes) || sizeof(Item::$classes) == 0) {
			Item::getItems();
		}
		return Item::$classes;
	}

	function showImageWithPopup($title = null) {
		echo $this->generateImageWithPopup($title);
	}

	function generateImageWithPopup($title = null) {
		$popup = '<div class="stendhalItem"><span class="stendhalItemIconNameBanner">';

		if (isset($title)) {
			$popup .= '<div>'.htmlspecialchars($title).'</div>';
		}

		$popup .= '<span class="stendhalItemIcon">';
		$popup .= '<img src="' . htmlspecialchars($this->imageurl) . '" />';
		$popup .= '</span>';

		$popup .= '<a href="'.rewriteURL('/item/'.surlencode($this->class).'/'.surlencode($this->name).'.html').'">';
		$popup .= $this->name;
		$popup .= '</a>';
		$popup .= '</span>';

		$popup .= '<br />';
		if ($this->unattainable) {
			$popup .= 'This item is not available.';
		} else {
			$popup .= 'Class: ' . htmlspecialchars(ucfirst($this->class)) . '<br />';
			foreach($this->attributes as $label=>$data) {
				$popup .= htmlspecialchars(ucfirst($label)) . ': ' . htmlspecialchars($data) . '<br />';
			}

			if (isset($this->description) && ($this->description != '')) {
				$popup .= '<br />' . $this->description . '<br />';
			}
		}
		$popup .= '</div>';

		return '<a href="'.rewriteURL('/item/'.surlencode($this->class).'/'.surlencode($this->name).'.html').'" class="overliblink" title="'.htmlspecialchars($this->name).'" data-popup="'.htmlspecialchars($popup).'">'
				. '<img src="'.htmlspecialchars($this->showImage()).'" alt=""></a>';
	}

	function createNameLink() {
		return '<a class="stendhalItemLink" href="'.rewriteURL('/item/'.surlencode($this->class).'/'.surlencode($this->name).'.html').'">'.$this->name.'</a>';
	}
}


class DummyItem {
	public $name;
	public $description;
	public $imageurl;

	function __construct($name, $description, $imageurl) {
		$this->name = $name;
		$this->description = $description;
		$this->imageurl = $imageurl;
	}

	function showImageWithPopup($title = null) {
		echo $this->generateImageWithPopup($title);
	}

	function generateImageWithPopup($title = null) {
		return "<img src=\"".htmlspecialchars($this->imageurl)."\" alt=\"\"></a>";
	}
}

$dummy_items = [
	"cat" => ["desc" => "", "image" => "/images/game/cat.png"],
	"sheep" => ["desc" => "A sheep.", "image" => "/images/game/sheep.png"]
];

$item_aliases = [
	"daisy seed" => "seed",
	"lilia seed" => "seed",
	"pansy seed" => "seed",
	"zantedeschia bulb" => "bulb"
];

function getItem($name) {
	global $dummy_items;
	global $item_aliases;

	if (isset($item_aliases[$name])) {
		$name = $item_aliases[$name];
	}
	foreach (getItems() as $i) {
		if($i->name == $name) {
			return $i;
		}
	}

	if (isset($dummy_items[$name])) {
		//~ return createSpecialItem($name, $special_items[$name]["desc"], $special_items[$name]["image"]);
		return new DummyItem($name, $dummy_items[$name]["desc"], $dummy_items[$name]["image"]);
	}

	return null;
}

/**
 * Returns a list of Items
 */
function getItems() {
	global $cache;
	if (!isset(Item::$items) || sizeof(Item::$items) == 0
		|| !isset(Item::$classes) || sizeof(Item::$classes) == 0) {
		Item::$items = $cache->fetchAsArray('stendhal_items');
		Item::$classes = $cache->fetchAsArray('stendhal_items_classes');
	}
	if ((is_array(Item::$items) && (sizeof(Item::$items) != 0))
		&& (is_array(Item::$classes) && (sizeof(Item::$classes) != 0))) {
		return Item::$items;
	}
	Item::$classes = array();


	$itemsXMLConfigurationFile="data/conf/items.xml";
	$itemsXMLConfigurationBase='data/conf/';

	$content = file($itemsXMLConfigurationFile);
	$temp = implode('',$content);
	$itemfiles = XML_unserialize($temp);
	$itemfiles = $itemfiles['groups'][0]['group'];

	$list = array();

	foreach ($itemfiles as $file) {
		if (isset($file['uri'])) {
			$content = file($itemsXMLConfigurationBase.$file['uri']);
			$temp = implode('',$content);
			$items =  XML_unserialize($temp);
			if (!isset($items['items'][0]['item'])) {
				continue;
			}
			$items = $items['items'][0]['item'];

			for ($i=0;$i<sizeof($items)/2;$i++) {
				if (!is_array($items[$i.' attr'])) {
					break;
				}
				$name=$items[$i.' attr']['name'];

				$unattainable = isset($items[$i]['unattainable']) && $items[$i]['unattainable'][0] === 'true';

				if (isset($items[$i]['description'])) {
					$description=$items[$i]['description']['0'];
				} else {
					$description='';
				}

				$class=$items[$i]['type']['0 attr']['class'];
				$gfx=rewriteURL('/images/item/'.surlencode($class).'/'.surlencode($items[$i]['type']['0 attr']['subclass']).'.png');

				$susceptibilities=array();
				if (isset($items[$i]['susceptibility'])) {
					foreach($items[$i]['susceptibility'] as $susceptibility) {
						if (isset($susceptibility['type'])) {
							$susceptibilities[$susceptibility['type']] = round(100 / $susceptibility['value']).'% (effectiveness)';
						}
					}
				}

				$attr_excludes = [
					'max_quantity', 'menu', 'quantity', 'slot_name',
					'undroppableondeath', 'use_sound'
				];
				$poison_items = [
					'fierywater', 'poison', 'venom'
				];

				$damage_type = array();
				$attributes=array();
				if (is_array($items[$i]['attributes'][0])) {
					foreach($items[$i]['attributes'][0] as $attr=>$val) {
						foreach($val as $temp) {
							// 1.43: deprecated "item->attributes->unattainable" in favor of "item->unattainable"
							if ($attr == 'unattainable') {
								$attributes[$attr] = $temp === 'true';
							}
							if (!is_array($temp)) {
								continue;
							}
							if (isset($temp['condition']) && $temp['condition'][0] !== '!') {
								continue;
							}

							$value = $temp['value'];
							if ($attr === 'damagetype') {
								array_splice($damage_type, 0, 0, $value);
								continue;
							}
							if ($attr === 'statusattack') {
								foreach (explode(";", $value) as $v) {
									$v = strtolower($v);
									$si = strpos($v, 'status');
									if (is_int($si)) {
										$damage_type[] = substr($v, 0, $si);
									} else {
										foreach ($poison_items as $pi) {
											if (is_int(strpos($v, $pi))) {
												$damage_type[] = "poison";
												break;
											}
										}
									}
								}
								continue;
							}
							if ($attr === 'statusresist' && isset($temp['type'])) {
								$susceptibilities[$temp['type']] = ($value * 100).'% (chance of resisting)';
								continue;
							}

							if (!in_array($attr, $attr_excludes, true)) {
								$attributes[$attr] = $value;
							}
						}
					}
				}

				if ($damage_type) {
					$attributes['atk'] = $attributes['atk'].' ('.implode(', ', $damage_type).')';
				}

				if (isset($attributes['damagetype'])) {
					$attributes['atk'] = $attributes['atk'].' ('.$attributes['damagetype'].')';
					unset($attributes['damagetype']);
				} else if (isset($items[$i]['damage']['0 attr']['type'])) {
					// 1.43: deprecated "item->damage" in favor of "item->attributes->damagetype"
					$attributes['atk'] = $attributes['atk'].' ('.$items[$i]['damage']['0 attr']['type'].')';
				}

				if (!isset($attributes['unattainable']) || $attributes['unattainable'] === false) {
					$list[] = new Item($name, $description, $class, $gfx, $attributes,
							$susceptibilities, null, $unattainable);
				}
			}
		}
	}

	function compare($a, $b) {
		return strcmp($a->name,$b->name);
	}

	// Sort it alphabetically.
	usort($list, 'compare');
	Item::$items = $list;
	$cache->store('stendhal_items', new ArrayObject($list));
	$cache->store('stendhal_items_classes', new ArrayObject(Item::$classes));
	return $list;
}

/**
 * Retrieves a list of NPC sellers & buyers.
 *
 * @param itemname
 *     Name of item to search for in shops.
 * @return
 *     Array of NPC names.
 */
function getItemMerchants($itemname) {
	// FIXME: this should get cached
	$npclist = [
		"sellers" => [],
		"buyers" => []
	];

	foreach (getNPCShops() as $npcname=>$shopinfo) {
		if (isset($shopinfo["sell"]) && isset($shopinfo["sell"][$itemname])) {
			$npclist["sellers"][$npcname] = $shopinfo["sell"][$itemname];
		}
		if (isset($shopinfo["buy"]) && isset($shopinfo["buy"][$itemname])) {
			$npclist["buyers"][$npcname] = $shopinfo["buy"][$itemname];
		}
	}
	return $npclist;
}
