<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin

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
	function writeContent() {
$npcs=NPC::getNPCs();
?>
 
<div style="float: left; width: 100%"><?php

startBox('NPCs');
?>
<form method="get" action="">
  <input type="hidden" name="id" value="content/scripts/npc">
  <input type="text" name="name" maxlength="60">
  <input type="submit" name="sublogin" value="Search">
</form>
<div>
  <?php echo sizeof($npcs); ?> NPCs so far.
</div>

<?php 

foreach($npcs as $npc) {
	echo '<div class="npc"><a class="npc" href="?id=content/scripts/npc&amp;name='.$npc->name.'&amp;exact">';
	echo '  <img class="npc" src="'.$npc->imagefile.'" alt="'.$npc->name.'">';
	echo '  <div class="npc_name">'.$npc->name.'</div>';
	echo ' </a>';
	echo '</div>';
}
?>
<div style="clear: left;"></div>
<?php

endBox();
?>
</div>
<div style="clear: left;"></div>
</div>

<?php
	}
}
$page = new NPCsPage();
?>