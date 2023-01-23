<?php
/*
 * Stendhal website - a website to manage and ease playing of Stendhal game
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


class BingoPage extends Page {
	private $id;
	private $name;
	private $query;
	private $killer;
	private $killed_type;
	private $killer_type;
	private $level;
	private $outfit;

	public function __construct() {
		$this->query = "SELECT id, killed, killed_type, killer, killer_type FROM kills WHERE id>'".mysql_real_escape_string($_REQUEST['lastid'])."'+1 ORDER BY id LIMIT 1";
		$stmt = DB::game()->query($this->query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$this->id = $row['id'];
		$this->name = $row['killed'];
		$this->killed_type = $row['killed_type'];
		$this->killer = $row['killer'];
		$this->killer_type = $row['killer_type'];

		$this->query = "SELECT level, outfit_layers FROM character_stats WHERE name='".mysql_real_escape_string($this->killer)."'";
		$stmt = DB::game()->query($this->query);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		$this->level = $row['level'];
		$this->outfit = $row['outfit_layers'];
	}

	public function writeHttpHeader() {
		?>
		<!DOCTYPE title PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<?php
			$this->writeHtmlHeader();
			$this->writeContent();

		return false;
	}

	public function writeHtmlHeader() {
		echo '<title>Bingo'.STENDHAL_TITLE.'</title>';
		echo '<meta http-equiv="refresh" content="10; URL=/index.php?id=content/admin/bingo&amp;lastid='.htmlspecialchars($this->id).'">';
		echo '<meta name="robots" content="noindex,nofollow">'."\n";
	}

	function writeContent() {
		startBox('Bingo');

		if ($this->killed_type == 'C') {
		$monsters = getMonsters();
		foreach($monsters as $m) {
			if($m->name==$this->name) {
			?>
	<div class="monster">
		<div class="name"><?php echo ucfirst($m->name); ?></div>
		<img class="monster" src="<?php echo $m->imageurl; ?>" alt="">
		<div class="level">Level <?php echo $m->level; ?></div>
		<div class="description">
		<?php
		if($m->description=="") {
			echo 'No description. Would you like to <a href="http://sourceforge.net/tracker/?func=add&amp;group_id=1111&amp;atid=301111">write one</a>?';
		} else {
			echo $m->description;
		}

		if ($this->killer_type == 'P') {
			echo '<div>Killed by <img src="/images/outfit/'.htmlspecialchars($this->outfit).'.png"> '.htmlspecialchars($this->killer).' ('.htmlspecialchars($this->level).')</div>';
		} else {
			echo "<div>Killed by ".htmlspecialchars($this->killer).' ('.htmlspecialchars($this->killer_type).')</div>';
		}
			}
		?>

	</div>
	<?php
			}
		}
		echo htmlspecialchars($this->id);
		endBox();
	}
}

$page = new BingoPage();
