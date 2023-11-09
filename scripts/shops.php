<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2009-2023 Stendhal

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



class Shops {

	// TODO: dump notes configured in .xml to database
	private static $notes = [
		"adosmasks" => [
			"merchants" => [
				"Fidorea" => "selected mask is random"
			]
		],
		"animalsanctuary" => [
			"merchants" => [
				"Dr. Feelgood" => "after Zoo Food quest"
			]
		],
		"athorswimsuits" => [
			"merchants" => [
				"Pam" => "selected swimsuit is random"
			]
		],
		"athorswimtrunks" => [
			"merchants" => [
			 "David" => "selected trunks is random"
			]
		],
		"bestiary" => [
			"merchants" => [
				"Rengard" => "after Collect Enemy Data quest"
			]
		],
		"buyblack" => [
			"merchants" => [
				"Balduin" => "after Ultimate Collector quest"
			]
		],
		"deniran_accessories" => [
			"merchants" => [
				"Gwen" => "do not expire"
			]
		],
		"karl" => [
			"items" => [
				"horse hair" => "after Bows for Ouchit quest"
			]
		],
		"sellrevivalweeks" => [
			"merchants" => [
				"Caroline" => "during Minetown Weeks"
			]
		],
		"twohandswords" => [
			"merchants" => [
				"Balduin" => "after Ultimate Collector quest"
			]
		]
	];

	// layers to be added to outfit previews
	public static $outfits_add_layers = [
		"adosmasks" => "body=0,head=0,eyes=0",
		"athorswimsuits" => "body=1,head=0,eyes=0",
		"athorswimtrunks" => "body=0,head=0,eyes=0",
		"deniran_accessories" => "body=0,head=0,eyes=0",
		"deniranoutfits" => "body=0,head=0,eyes=0",
		"magicoutfitsliliana" => [
			"layers" => "body=0,head=0,eyes=0",
			"apply" => ["jumpsuit", "dungarees", "green dress", "gown", "orange", "jester"]
		],
		"magicoutfitssaski" => [
			"layers" => "body=0",
			"apply" => ["goblin face", "thing face"]
		],
		"weddinggown" => "body=1,head=0,eyes=0",
		"weddingsuit" => "body=0,head=0,eyes=0"
	];

	// @deprecated
	public static $shops=array();

	/**
	 * @deprecated
	 */
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


	/**
	 * Parses shop info from xml data.
	 *
	 * @deprecated
	 *
	 * @param shopsdata
	 *     XML data containing shops information.
	 */
	function parseShopsData($shopsdata) {
		$npcshops = [];

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
							$requirealso = $this->parseAddRequire($contents[$i]["for"]);
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
		return $npcshops;
	}


	/**
	 * Loads registered shops from config.
	 *
	 * @deprecated
	 */
	function loadShops() {
		global $cache;

		if (!isset(Shops::$shops) || sizeof(Shops::$shops) == 0) {
			Shops::$shops = $cache->fetchAsArray("stendhal_shops");
		}
		if (is_array(Shops::$shops) && sizeof(Shops::$shops) > 0) {
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
		$npcshops = [];
		foreach ($groups as $group) {
			$content = file($group);
			$tmp = implode("", $content);
			$root = XML_unserialize($tmp);
			if (!isset($root["shops"][0]["shop"])) {
				continue;
			}
			$newshops = $this->parseShopsData($root["shops"][0]["shop"]);
			$npcshops = array_merge($npcshops, $newshops);
		}
		Shops::$shops = $npcshops;
		$cache->store("stendhal_shops", new ArrayObject($npcshops));
	}

	/**
	 * Retrieves shop lists for an NPC.
	 *
	 * @deprecated
	 *
	 * @param npcname
	 *     Name of NPC for which shop is associated.
	 * @return
	 *     "sell" &/or "buy" shop lists or <code>null</code> if the NPC
	 *     does not have a shop.
	 */
	function getNPCShop($npcname) {
		$this->loadShops();
		if (isset(Shops::$shops[$npcname])) {
			return Shops::$shops[$npcname];
		}
		return null;
	}

	/**
	 * Retrieves all NPC shops.
	 *
	 * @deprecated
	 */
	function getNPCShops() {
		$this->loadShops();
		return Shops::$shops;
	}

	/**
	 * Which NPCs buy or sell a given item?
	 *
	 * @param $itemname name of item
	 * @param $shoptype "buy" or "sell"
	 */
	function getNPCsForItem($itemname, $shoptype) {
		// special case items
		if ($itemname == "seed" || $itemname == "bulb") {
			if ($shoptype == "sell") {
				// FIXME: hack to show seed & bulb sellers (this info is not available from database)
				return [[
					"name" => "Jenny",
					"price" => "varies"
				]];
			}
		}

		$query = "SELECT npcs.name, shopinventoryinfo.price * shopownerinfo.price_factor as price
			FROM iteminfo
			JOIN shopinventoryinfo ON shopinventoryinfo.iteminfo_id = iteminfo.id
			JOIN shopinfo ON shopinfo.id = shopinventoryinfo.shopinfo_id
			JOIN shopownerinfo ON shopownerinfo.shopinfo_id = shopinfo.id
			JOIN npcs ON shopownerinfo.npcinfo_id = npcs.id
			WHERE iteminfo.name = :itemname AND shopinfo.shop_type = :shoptype;";
		$stmt = DB::game()->prepare($query);
		$stmt->execute(array(':itemname' => $itemname, ':shoptype' => $shoptype));
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Which items are bought or sold by a given NPC?
	 *
	 * @param $npcname Name of NPC.
	 * @param $shoptype "buy", "sell", or "outfit".
	 */
	function getItemsForNPC($npcname, $shoptype) {
		if ($shoptype === "outfit") {
			$query = "SELECT shopinventoryinfo.name, shopinventoryinfo.price * shopownerinfo.price_factor as price, shopinventoryinfo.outfit
				FROM shopinventoryinfo
				JOIN shopinfo ON shopinfo.id = shopinventoryinfo.shopinfo_id
				JOIN shopownerinfo ON shopownerinfo.shopinfo_id = shopinfo.id
				JOIN npcs ON shopownerinfo.npcinfo_id = npcs.id
				WHERE npcs.name = :npcname AND shopinfo.shop_type = :shoptype;";
		} else {
			$query = "SELECT shopinventoryinfo.name, shopinventoryinfo.price * shopownerinfo.price_factor as price
				FROM shopinventoryinfo
				JOIN shopinfo ON shopinfo.id = shopinventoryinfo.shopinfo_id
				JOIN shopownerinfo ON shopownerinfo.shopinfo_id = shopinfo.id
				JOIN npcs ON shopownerinfo.npcinfo_id = npcs.id
				WHERE npcs.name = :npcname AND shopinfo.shop_type = :shoptype;";
		}
		$stmt = DB::game()->prepare($query);
		$stmt->execute(array(':npcname' => $npcname, ':shoptype' => $shoptype));
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 * Retrieves string identifier for a shop.
	 *
	 * @param $npcname Name of NPC.
	 * @param $shoptype "buy", "sell", or "outfit".
	 * @return Identifier or `null`.
	 */
	function getId($npcname, $shoptype) {
		$query = "SELECT shopinfo.name FROM shopinfo
			JOIN shopownerinfo ON shopownerinfo.shopinfo_id = shopinfo.id
			JOIN npcs ON shopownerinfo.npcinfo_id = npcs.id
			WHERE npcs.name = :npcname AND shopinfo.shop_type = :shoptype;";
		$stmt = DB::game()->prepare($query);
		$stmt->execute(array(':npcname' => $npcname, ':shoptype' => $shoptype));
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$sid = null;
		if (sizeof($res) > 0) {
			$sid = $res[0]["name"];
		}
		return $sid;
	}

	/**
	 * Retrieves any specified notes for a shop.
	 *
	 * @param $sid Shop string identifier.
	 */
	function getNotes($sid) {
		$notes = [];
		if (isset($sid) && isset(Shops::$notes[$sid])) {
			$notes = Shops::$notes[$sid];
		}
		return $notes;
	}
}
