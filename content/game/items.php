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

	public function writeHtmlHeader() {
		if(!isset($_GET['class'])) {
			echo '<title>Items'.STENDHAL_TITLE.'</title>';
		} else {
			echo '<title>Item class '.htmlspecialchars($_GET['class']).STENDHAL_TITLE.'</title>';
		}
	}

	function writeContent() {
$items=getItems();
$classes=Item::getClasses();


if(!isset($_GET['class'])) {
  startBox('<h1>Items</h1>');
  ?>
  <form method="get" action="/" id="currentContentSearch">
    <input type="hidden" name="id" value="content/scripts/item">
    <input type="hidden" name="class" value="all">
    <input type="text" name="name" maxlength="60">
    <input type="submit" name="sublogin" value="Search">
  </form>
  <div style="margin-bottom: 10px;">
    <?php echo sizeof($items); ?> items so far.
  </div>
  <div class="cards">
  <?php
  foreach($classes as $class=>$zero) {
    foreach($items as $item) {
   	  if($item->class == $class && !$item->unattainable) {
   	    $choosen=$item;
   	  }
    }
	?>
    <div class="f3cols">
      <?php
        echo '<a href="'.rewriteURL('/item/'.surlencode($class).'.html').'">';
        echo '<img src="'.$choosen->imageurl.'" alt=""><br>';
        echo ucfirst($class). '</a>';?>
    </div>
    <?php
  }
  ?>
  </div>
  <div style="clear: left;"></div>
  <?php
  endBox();
  return;
}

?>
<?php
//foreach($classes as $class=>$zero) {
$class=$_GET['class'];
startBox('<h1>'.ucfirst($class).' Items</h1>');
echo '<div class="cards">';
  foreach($items as $item) {
	if ($item->unattainable) {
	  continue;
	}
	if($item->class==$class) {
	  ?>
  	  <div class="item">
        <?php echo '<a class="item" href="'.rewriteURL('/item/'.surlencode($class).'/'. surlencode($item->name) . '.html').'">'; ?>
 	      <img class="item_image" src="<?php echo $item->imageurl; ?>" alt="">
	      <span class="block item_name"><?php echo $item->name; ?></span>
	    </a>
	  </div>
	  <?php
	}
  }
	echo '</div>';
	echo '<div style="clear: left;"></div>';
	endBox();
	}


	public function getBreadCrumbs() {
		$array = array('World Guide', '/world.html', 'Item', '/item/');
		if (isset($_GET['class'])) {
			$array[] = ucfirst($_GET['class']);
			$array[] = '/item/'.$_GET['class'].'.html';
		}
		return $array;
	}
}
$page = new ItemsPage();
