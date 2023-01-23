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

class NPCsPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>NPCs'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
$npcs=NPC::getNPCs();
?>

<div style="float: left; width: 100%"><?php

startBox('<h1>NPCs</h1>');
?>
<form method="get" action="<?php echo '/'.STENDHAL_FOLDER;?>" id="currentContentSearch">
  <input type="hidden" name="id" value="content/scripts/npc">
  <input type="hidden" name="search" value="y">
  <input type="text" name="name" maxlength="60">
  <input type="submit" name="sublogin" value="Search">
</form>
<div>
  <?php echo sizeof($npcs); ?> NPCs so far.
</div>

<div class="cards">
<?php

foreach($npcs as $npc) {
	echo '<div class="npc"><a class="npc" href="'.rewriteURL('/npc/'.surlencode($npc->name).'.html').'">';
	echo '  <img class="npc" src="'.$npc->imageurl.'" alt="'.$npc->name.'">';
	echo '  <span class="block npc_name">'.$npc->name.'</span>';
	echo ' </a>';
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
		return array('World Guide', '/world.html', 'NPC', '/npc/');
	}
}
$page = new NPCsPage();
