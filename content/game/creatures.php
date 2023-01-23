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

class CreaturesPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Creatures'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
$monsters=getMonsters();
$classes=Monster::getClasses();
?>

<div style="float: left; width: 100%"><?php

startBox('<h1>Creatures</h1>');
?>
<form method="get" action="/" id="currentContentSearch">
	<input type="hidden" name="id" value="content/scripts/monster">
	<input type="text" name="name" maxlength="60">
	<input type="submit" name="sublogin" value="Search">
</form>
<div>
	<?php echo sizeof($monsters); ?> creatures so far.
</div>

<div class="cards">
<?php
foreach($monsters as $m) {
	echo '<div class="creature"><a class="creature" href="'.rewriteURL('/creature/'.surlencode($m->name).'.html').'">';
	echo '  <img class="creature" src="'.$m->imageurl.'" alt="">';
	echo '  <span class="block creature_name">'.$m->name.'</span>';
	echo ' </a>';
	echo '  <div class="creature_level">Level '.$m->level.'</div>';
	echo '</div>';
}
?>
</div>
<div style="clear: left;"></div>
<?php

endBox();
?>
</div>
<div style="clear: left;"></div>
<?php

	}
	public function getBreadCrumbs() {
		return array('World Guide', '/world.html', 'Creature', '/creature/');
	}
}
$page = new CreaturesPage();
