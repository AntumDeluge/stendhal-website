<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2013  Hendrik Brummermann

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

class MobilePage extends Page {
	private static $items;
	private static $classes;
	private static $creatures;

	public function writeHttpHeader() {
		header('Content-Type: text/html; charset="utf-8"');
		$this->write();
		return false;
	}


	private function write() {
		MobilePage::$items = getItems();
		MobilePage::$classes = Item::getClasses();
		MobilePage::$creatures = getMonsters();

		$this->writeHeader();
		$this->writeMobileContent();
		$this->writeFooter();
	}

	private function writeHeader() {
		echo '<!DOCTYPE html>
		<html><head><title>Stendhal</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="/css/jquery.mobile-1.2.0.min.css" />
		<link rel="stylesheet" href="/css/tileset.css" />
		<script src="/css/jquery-1.8.2.min.js"></script>
		<script src="/css/jquery.mobile-1.2.0.min.js"></script>
		</head>
		<body>';
	}

	private function writeMobileContent() {
		$this->writeStartPage();
// 		$this->writeAllCreaturePage();
		$this->writeCreatureListPage();
		foreach (MobilePage::$creatures as $creature) {
			$this->writeCreaturePage($creature);
		}
		$this->writeItemClassesPage();
		foreach (MobilePage::$classes as $class => $temp) {
			$this->writeItemClassPage($class);
		}
	}

	private function writeStartPage() {
		?>
		<div data-role="page" id="page-start">
		<div data-role="header"><h1>Stendhal</h1></div>

		<div data-role="content">
		<ul data-role="listview" data-inset="true" data-filter="false">
		<li><a href="#page-itemclasses">Items</a></li>
		<li><a href="#page-creatures">Creatures</a></li>
		</ul>
		<span class="tileset-itemicon tileset-preload"></span><span class="tileset-creatureicon tileset-preload"></span>
		</div></div>
		<?php
	}

	private function writeAllCreaturePage() {
		echo '<div data-role="page" id="page-creatures">';
		echo '<div data-role="header">
		<h1>Stendhal Creatures</h1>
		<a data-rel="back" href="#page-start">Back</a>
		</div>';

		echo '<div data-role="content">
		<div data-role="collapsible-set" data-filter="true">';


		foreach(MobilePage::$creatures as $creature) {
			if($item->class==$class) {
				echo '<div data-role="collapsible" data-collapsed="true">
				<h3><span class="tileset-creatureicon creatureicon-'.str_replace('/', '-', substr($creature->gfx, 17, -4)).'"></span>';

				echo htmlspecialchars(ucfirst($creature->name)).'</h3>';
				$this->writeCreatureDetails($creature);
				echo '</div>';
			}
		}

		echo '</div></div></div>';
	}

	private function writeCreatureListPage() {
		echo '<div data-role="page" id="page-creatures">';
		echo '<div data-role="header">
		<h1>Stendhal Creatures</h1>
		<a data-rel="back" href="#page-start">Back</a>
		</div>';

		echo '<div data-role="content">
		<ul data-role="listview" data-inset="true" data-filter="true">';


		foreach(MobilePage::$creatures as $creature) {
			echo '<li class="Xtileset-creatureicon creatureicon-'.str_replace('/', '-', substr($creature->gfx, 17, -4)).'">'
			.'<a href="#page-creature-'.htmlspecialchars($creature->name).'">'
			.htmlspecialchars(ucfirst($creature->name)).'</a></li>';
		}

		echo '</ul></div></div>';
	}

	private function writeCreaturePage($creature) {
		echo '<div data-role="page" id="page-creature-'.htmlspecialchars($creature->name).'">';
		echo '<div data-role="header">
		<h1>'.htmlspecialchars(ucfirst($creature->name)).' - Stendhal Creatures</h1>
		<a data-rel="back" href="#page-start">Back</a>
		</div>';

		echo '<div data-role="content">
			<h3><span class="tileset-creatureicon creatureicon-'.str_replace('/', '-', substr($creature->gfx, 17, -4)).'"></span>';
		echo htmlspecialchars(ucfirst($creature->name)).'</h3>';
		$this->writeCreatureDetails($creature);

		echo '</div></div>';
	}


	private function writeCreatureDetails($creature) {
		echo '<div>';
		if ($creature->description == '') {
			echo '<em>No description. Please write one.</em>';
		} else {
			echo htmlspecialchars($creature->description);
		}
		echo '</div>';


		if (count($creature->attributes) > 0) {
			echo '<h4>Attributes</h4>';
			echo '<table>';
			foreach ($creature->attributes as $label=>$data) {
				echo '<tr><td scope="row">'.htmlspecialchars(ucfirst($label)).':</td>';
				echo '<td>'.htmlspecialchars($data).'</td></tr>';
			}
			echo '</table>';
		}

		if (count($creature->susceptibilities) > 0) {
			echo '<h4>Resistances</h4>';
			echo '<table>';
			foreach ($creature->susceptibilities as $label=>$data) {
				echo '<tr><td scope="row">'.htmlspecialchars(ucfirst($label)).':</td>';
				echo '<td>'.htmlspecialchars($data).'%</td></tr>';
			}
			echo '</table>';
		}

		if (count($creature->drops)) {
			echo '<h4>Drops</h4>';
			echo '<table>';
			foreach ($creature->drops as $drop) {
				echo '<tr><td>'.htmlspecialchars(ucfirst($drop['name'])).'</td>';
				echo '<td>'.htmlspecialchars($drop['quantity']).'</td>';
				echo '<td>'.formatNumber($drop['probability']).'%</td></tr>';
			}
			echo '</table>';
		}
	}


	private function writeItemClassesPage() {
		?>
		<div data-role="page" id="page-itemclasses">
		<div data-role="header"><h1>Stendhal Items</h1>
		<a data-rel="back" href="#page-start">Back</a>
		</div>

		<div data-role="content">
		<ul data-role="listview" data-inset="true" data-filter="false">
		<?php
		foreach (MobilePage::$classes as $class => $temp) {
			echo '<li><a href="#page-itemclass-'.htmlspecialchars($class).'">';

			$icon = false;
			foreach(MobilePage::$items as $item) {
				if($item->class==$class) {
					$icon = $item->gfx;
					// no break, because we want the last and most powerful item icon
				}
			}
			if ($icon) {
				echo '<span class="tileset-itemicon itemicon-'.str_replace('/', '-', substr($icon, 13, -4)).'"></span>';
			}

			echo htmlspecialchars(ucfirst($class)).'</a></li>';
		}
		?>
		</ul></div>
		</div>
		<?php
	}

	private function writeItemClassPage($class) {
		echo '<div data-role="page" id="page-itemclass-'.htmlspecialchars($class).'">';
		echo '<div data-role="header">
			<h1>'.htmlspecialchars(ucfirst($class)).' - Stendhal Items</h1>
			<a data-rel="back" href="#page-itemclasses">Back</a>
			</div>';

		echo '<div data-role="content">
			<h2>'.htmlspecialchars(ucfirst($class)).'</h2>
			<div data-role="collapsible-set">';


		foreach(MobilePage::$items as $item) {
			if($item->class==$class) {
				echo '<div data-role="collapsible" data-collapsed="true">
					<h3><span class="tileset-itemicon itemicon-'.str_replace('/', '-', substr($item->gfx, 13, -4)).'"></span>';

				echo htmlspecialchars(ucfirst($item->name)).'</h3>';
 				$this->writeItemDetails($item);
 				echo '</div>';
			}
		}

		echo '</div></div></div>';
	}

	private function writeItemDetails($item) {
		echo '<div>';
		if ($item->description == '') {
			echo '<em>No description. Please write one.</em>';
		} else {
			echo htmlspecialchars($item->description);
		}
		echo '</div>';


		if (count($item->attributes) > 0) {
			echo '<h4>Attributes</h4>';
			echo '<table>';
			foreach ($item->attributes as $label=>$data) {
				echo '<tr><td scope="row">'.htmlspecialchars(ucfirst($label)).':</td>';
				echo '<td>'.htmlspecialchars($data).'</td></tr>';
			}
			echo '</table>';
		}

		if (count($item->susceptibilities) > 0) {
			echo '<h4>Resistances</h4>';
			echo '<table>';
			foreach ($item->susceptibilities as $label=>$data) {
				echo '<tr><td scope="row">'.htmlspecialchars(ucfirst($label)).':</td>';
				echo '<td>'.htmlspecialchars($data).'%</td></tr>';
			}
			echo '</table>';
		}



		$found = false;
		foreach (MobilePage::$creatures as $monster) {
			foreach ($monster->drops as $drop) {
				if ($drop['name']==$item->name) {
					$found = true;
					break;
				}
			}
		}

		if ($found) {
			echo '<h4>Dropped by</h4>';
			echo '<table>';
			foreach (MobilePage::$creatures as $monster) {
					foreach ($monster->drops as $drop) {
					if ($drop['name']==$item->name) {
						echo '<tr><td>'.htmlspecialchars(ucfirst($monster->name)).'</td>';
						echo '<td>'.htmlspecialchars($drop['quantity']).'</td>';
						echo '<td>'.formatNumber($drop['probability']).'%</td></tr>';
					}
				}
			}
			echo '</table>';
		}
	}

	private function writeFooter() {
		echo '</body></html>';
	}

	function writeContent() {
		// do nothing
	}

}
$page = new MobilePage();
