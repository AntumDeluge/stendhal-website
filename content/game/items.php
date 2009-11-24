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

class ItemsPage extends Page {
	function writeContent() {
$items=getItems();
$classes=Item::getClasses();


if(!isset($_GET['class'])) {
  startBox('Items classes');
  ?>
  <form method="get" action="">
    <input type="hidden" name="id" value="content/scripts/item">
    <input type="text" name="name" maxlength="60">
    <input type="submit" name="sublogin" value="Search">
  </form>
  <div style="margin-bottom: 10px;">
    <?php echo sizeof($items); ?> items so far.
  </div>
  <?php
  foreach($classes as $class=>$zero) {
    foreach($items as $item) {
   	  if($item->class==$class) {
   	    $choosen=$item;   	      
   	  }
    }
	?>
    <div class="f3cols">
      <a href="?id=content/game/items&class=<?php echo $class; ?>">
        <img src="<?php echo $choosen->gfx; ?>"/><br>
        <?php echo ucfirst($class); ?>
      </a>
    </div>
    <?php
  }  
  ?>
  <div style="clear: left;"></div>
  <?php
  endBox();
  return;
}

?>
<?php
//foreach($classes as $class=>$zero) {
$class=$_GET['class'];
startBox(ucfirst($class).' Items');
  foreach($items as $item) {
	if($item->class==$class) {
	  ?>
  	  <div class="item">
        <a class="item" href="?id=content/scripts/item&name=<?php echo $item->name; ?>&exact">        
 	      <img class="item_image" src="<?php echo $item->gfx; ?>" alt="<?php echo $item->name; ?>"/>
	      <div class="item_name"><?php echo $item->name; ?></div>
	    </a>
	  </div>
	  <?php
	}
  }
  ?>
  <div style="clear: left;"></div>
  <?php 
  endBox();
	}
}
$page = new ItemsPage();
?>