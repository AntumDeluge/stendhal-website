<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin
 Copyright (C) 2008-2020 The Arianne Project

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
 * A class representing an item.
 */
class Item {
	public static $classes=array();
	public static $items=array();

	/** Name of the item */
	public $name;
	/** Description of the item */
	public $description;
	/** Class of the item */
	public $class;
	/** GFX URL of the item. */
	public $gfx;
	/** Attributes of the item as an array attribute=>value */
	public $attributes;
	/** susceptibilities and resistances */
	public $susceptibilities;
	/** Where the item can be wore as an array slot=>item */
	public $equipableat;

	function __construct($name, $description, $class, $gfx, $attributes, $susceptibilities, $equipableat) {
		$this->name=$name;
		$this->description=$description;
		$this->class=$class;
		self::$classes[$class]=0;
		$this->gfx=$gfx;
		$this->attributes=$attributes;
		$this->equipableat=$equipableat;
		$this->susceptibilities=$susceptibilities;
	}

	function showImage() {
		return $this->gfx;
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
		$popup .= '<img src="' . htmlspecialchars($this->gfx) . '" />';
		$popup .= '</span>';

		$popup .= '<a href="'.rewriteURL('/item/'.surlencode($this->class).'/'.surlencode($this->name).'.html').'">';
		$popup .= $this->name;
		$popup .= '</a>';
		$popup .= '</span>';

		$popup .= '<br />';
		$popup .= 'Class: ' . htmlspecialchars(ucfirst($this->class)) . '<br />';
		foreach($this->attributes as $label=>$data) {
			$popup .= htmlspecialchars(ucfirst($label)) . ': ' . htmlspecialchars($data) . '<br />';
		}

		if (isset($this->description) && ($this->description != '')) {
			$popup .= '<br />' . $this->description . '<br />';
		}
		$popup .= '</div>';

		return '<a href="'.rewriteURL('/item/'.surlencode($this->class).'/'.surlencode($this->name).'.html').'" class="overliblink" title="'.htmlspecialchars($this->name).'" data-popup="'.htmlspecialchars($popup).'">'
				. '<img src="'.htmlspecialchars($this->showImage()).'" alt=""></a>';
	}
}

function getItem($name) {
	$items=getItems();
	foreach($items as $i) {
		if($i->name == $name) {
			return $i;
		}
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
			$items = $items['items'][0]['item'];

			for ($i=0;$i<sizeof($items)/2;$i++) {
				if (!is_array($items[$i.' attr'])) {
					break;
				}
				$name=$items[$i.' attr']['name'];

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
							$susceptibilities[$susceptibility['type']]=round(100 / $susceptibility['value']);
						}
					}
				}

				$attributes=array();
				if (is_array($items[$i]['attributes'][0])) {
					foreach($items[$i]['attributes'][0] as $attr=>$val) {
						foreach($val as $temp) {
							if (!is_array($temp)) {
								continue;
							}
							if (isset($temp['condition']) && $temp['condition'][0] !== '!') {
								continue;
							}
							
							if (($attr != 'max_quantity') && ($attr != 'quantity') && ($attr != 'undroppableondeath') && ($attr != 'slot_name')) {
								$attributes[$attr]=$temp['value'];
							}
						}
					}
				}

				if (isset($items[$i]['damage']['0 attr']['type'])) {
					$attributes['atk'] = $attributes['atk'].' ('.$items[$i]['damage']['0 attr']['type'].')';
				}

				$list[]=new Item($name, $description, $class, $gfx, $attributes, $susceptibilities, null);
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
