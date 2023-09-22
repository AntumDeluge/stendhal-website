<?php

/**
 * Steps to rebuild the atlas:
 *
 * run ant renderatlas
 * copy empty-large.png and reserved.png to large/
 * copy tiled/world/world.tmx to tiled/large/word.tmx
 * edit large/world.tmx: remove all layers except floor, remove all tilesets except 0_
 * edit large/world.tmx: width="256" -> width="1024", width="128" -> width="512", width="64" -> width="256", same for height
 * for both tiled/world.tmx and tiled/large/world.tmx zoom to 1:1, 100% visiblity of floor-layer, disable all layers
 * export images as /tmp/world.png and /tmp/world-large.png
 * create directory /tmp/map
 * run net.sf.arianne.tools.image.ImageSplit from the unrelated-stuff repository
 * upload /tmp/map to https://stendhalgame.org/map/3 and http://arianne.sf.net/stendhal/map/3 (increase number)
 * test atlas with ?tileset=3
 * change default $tileset value to new version
 */

class AtlasPage extends Page {
	public function writeHtmlHeader() {
		echo '<title>Atlas'.STENDHAL_TITLE.'</title>';
		$this->includeJs();
	}

	function writeContent() {
		$tileset = '4';
		if (isset($_REQUEST['tileset'])) {
			$tileset = intval($_REQUEST['tileset']);
		}
		startBox('<h1>Atlas</h1>');
		echo '<div id="map_canvas" data-tile-url-base="'.STENDHAL_MAP_TILE_URL_BASE.'/'.$tileset.'"></div>';
		endBox();

		startBox('<h2>Extended information</h2>');
		echo '<p>You can use your mouse or the map controls to zoom and pan.</p>';
		echo '<p>There is lots more information about each of the <a href="/region.html">regions</a> and <a href="/dungeon.html">dungeons</a>.</p>';
		echo '<p>You can add <a href="/world/atlas.html?poi=dungeon">dungeon entrances</a> to this map or open a map with <a href="https://arianne-project.org/screens/stendhal/world_labelled.png">zone names</a>.</p>';
		endBox();

		$zoom = 4;
		$focusX = 500000;
		$focusY = 500000;

		$zones = Zone::getZones();

		// if there is exactly one poi, focus on that
		$poiOpen = false;
		if (isset($_REQUEST['poi']) && strpos($_REQUEST['poi'], '.') === false) {
			$pois = PointofInterest::getPOIs();
			if (isset($pois[$_REQUEST['poi']])) {
				$poi = $pois[$_REQUEST['poi']];
				$zoom = 6;
				$focusX = $poi->gx;
				$focusY = $poi->gy;
				$poiOpen = true;
			}
		}

		// focus on position of current player and display a marker
		if (isset($_REQUEST['me'])) {
			$coordinates = explode('.', $_REQUEST['me']);
			$zone = $zones[$coordinates[0]];
			if (isset($zone) && isset($zone->x)) {
				$meZone = $coordinates[0];
				$meX = $zone->x + intval($coordinates[1]);
				$meY = $zone->y + intval($coordinates[2]);
				if ($zone->z === 0) {
					$zoom = 6;
				} else {
					$zoom = 5;
				}
				$focusX = $meX;
				$focusY = $meY;
			}
		}

		// if there is a focus parameter, use it
		if (isset($_REQUEST['focus'])) {
			$zoom = 6;
			$coordinates = explode('.', $_REQUEST['focus']);
			if (count($coordinates) === 1) {
				$pois = PointofInterest::getPOIs();
				$poi = $pois[$coordinates[0]];
				if (isset($poi)) {
					$focusX = $poi->gx;
					$focusY = $poi->gy;
				}
			} else if (count($coordinates) === 2) {
				$focusX = $coordinates[0];
				$focusY = $coordinates[0];
			} else if (count($coordinates) === 3) {
				$zone = $zones[$coordinates[0]];
				if (isset($zone) && isset($zone->x)) {
					$focusX = $zone->x + intval($coordinates[1]);
					$focusY = $zone->y + intval($coordinates[2]);
				}
			}
		}

		if (isset($_REQUEST['zoom'])) {
			$zoom = intval($_REQUEST['zoom']) + 1;
		}

		echo "\n".'<div class="data">';
		echo '<span id="data-center" data-x="'.htmlspecialchars($focusX).'" data-y="'.htmlspecialchars($focusY).'" data-zoom="'.htmlspecialchars($zoom).'" data-open="'.$poiOpen.'"></span>';
		if (isset($meX)) {
			echo '<span id="data-me" data-x="'.htmlspecialchars($meX).'" data-y="'.htmlspecialchars($meY).'" ';
			echo 'data-zone="'.htmlspecialchars($meZone).'" ';
			echo 'data-local-x="'.htmlspecialchars($coordinates[1]).'" data-local-y="'.htmlspecialchars($coordinates[2]).'"></span>';
		}
		echo '<span id="data-pois" data-pois="'.htmlspecialchars(json_encode(PointofInterest::getPOIs())).'"></span>';
		foreach ($zones as $zone) {
			if ($zone->int || $zone->z != 0) {
				continue;
			}
			echo '<span class="zone-data" data-name="'. htmlspecialchars($zone->name) . '" data-x="' . htmlspecialchars($zone->x) . '" data-y="' . htmlspecialchars($zone->y) . '"></span>';
		}
		echo '<span id="zone-info" data-zones="'.htmlspecialchars(json_encode(Zone::getZoneInfos())).'"></span>';
		echo '</div>';
	}

	public function writeAfterJS() {
		echo '<link rel="stylesheet" href="/css/leaflet.css?1">';
	}
}
$page = new AtlasPage();
