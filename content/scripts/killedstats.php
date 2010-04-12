
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

function getVariable($xmlStats, $type) {
  foreach($xmlStats['statistics'][0]['attrib'] as $i=>$j) {
    if(is_array($j) and $j['name']==$type) {
	  return $j['value'];
	}
  }
  
  return 0;
}

class KilledStatsPage extends Page {

	public function writeHtmlHeader() {
		echo '<title>Kill Statistics'.STENDHAL_TITLE.'</title>';
	}

	function writeContent() {
	
$monsters=getMonsters();
$classes=Monster::getClasses();

?>

<div style="float: left; width: 50%"><?php
startBox('Most killed (recently)');
$result=getMostKilledMonster($monsters);
if($result==null) {
	$result=array($monsters[0],0);
}

list($m, $amount)=$result;
echo '<div style="text-align: center;">';
echo '  <a class="creature" href="'.rewriteURL('/creature/'.htmlspecialchars($m->name).'.html').'">';
echo '  <img class="creature" src="'.$m->gfx.'" alt="'.$m->name.'"/>';
echo '  <span class="block creature_name">'.$m->name.'</span>';
echo ' </a>';
echo '  <div class="creature_killed">It was killed '.$amount.' times</div>';
echo '</div>';
endBox();
?></div>

<div style="float: left; width: 50%"><?php
startBox('Best Player killer (recently)');
$result=getBestKillerMonster($monsters);
if($result==null) {
	$result=array($monsters[0],0);
}

list($m, $amount)=$result;
echo '<div style="text-align: center;">';
echo '  <a class="creature" href="'.rewriteURL('/creature/'.htmlspecialchars($m->name).'.html').'">';
echo '  <img class="creature" src="'.$m->gfx.'" alt="'.$m->name.'"/>';
echo '  <span class="block creature_name">'.$m->name.'</span>';
echo ' </a>';
echo '  <div class="creature_killed">It has killed '.$amount.' players</div>';
echo '</div>';
endBox();
?></div>



<div style="float: left; width: 100%"><?php



$content=implode("",file(STENDHAL_SERVER_STATS_XML));
$xmlStats = XML_unserialize($content);

startBox("Killed monsters on this server run");
$monsters=getMonsters();

foreach($monsters as $m) {
  $amount=getVariable($xmlStats,"Killed ".$m->name);
  ?>
  <?php echo '  <a class="nodeco" href="'.rewriteURL('/creature/'.htmlspecialchars($m->name).'.html').'">'?>
  <span class="block row">
    <img src="<?php echo $m->showImage(); ?>" alt="<?php echo $m->name; ?>"/>
    <span class="block name"><?php echo $m->name; ?></span>
    <span class="block amount"><?php echo $amount; ?> killed</span>
  </span>
  </a>
  <?php
}
echo '<div style="clear: left;"></div>';
endBox();

?></div>
<div style="clear: left;"></div>
<?php
	}
}
$page = new KilledStatsPage();
?>