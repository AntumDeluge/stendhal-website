<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin
 Copyright (C) 2008-2016 The Arianne Project

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
$content=implode("",file(STENDHAL_SERVER_STATS_XML));
$xmlStats = XML_unserialize($content);

$monsters=getMonsters();
$classes=Monster::getClasses();

?>

<div style="float: left; width: 50%"><?php
startBox('<h2>Most killed (recently)</h2>');
$result=$this->getMostKilledMonsterXml($xmlStats, $monsters);
if($result==null) {
	$result=array(null, null);
}



list($m, $amount)=$result;
echo '<div style="text-align: center;">';
if (!is_null($m)) {
  echo '  <a class="creature" href="'.rewriteURL('/creature/'.surlencode($m->name).'.html').'">';
  echo '  <img class="creature" src="'.$m->gfx.'" alt="'.$m->name.'"/>';
  echo '  <span class="block creature_name">'.$m->name.'</span>';
  echo ' </a>';
  echo '  <div class="creature_killed">It was killed '.$amount.' times</div>';
} else {
  echo '  <span class="block">No monsters have been killed</span>';
}
echo '</div>';
endBox();
?></div>

<div style="float: left; width: 50%"><?php
startBox('<h2>Best Player killer (recently)</h2>');
$result=getBestKillerMonster($monsters);
if($result==null) {
	$result=array(null, null);
}

list($m, $amount)=$result;
echo '<div style="text-align: center;">';
if (!is_null($m)) {
  echo '  <a class="creature" href="'.rewriteURL('/creature/'.surlencode($m->name).'.html').'">';
  echo '  <img class="creature" src="'.$m->gfx.'" alt="'.$m->name.'"/>';
  echo '  <span class="block creature_name">'.$m->name.'</span>';
  echo ' </a>';
  echo '  <div class="creature_killed">It has killed '.$amount.' players</div>';
} else {
  echo '  <span class="block">No players have been killed</span>';
}
echo '</div>';
endBox();
?></div>



<div style="float: left; width: 100%"><?php

startBox("<h2>Killed monsters on this server run</h2>");
$monsters=getMonsters();

foreach($monsters as $m) {
  $amount=getVariable($xmlStats,"Killed ".$m->name);
  ?>
  <?php echo '  <a class="nodeco" href="'.rewriteURL('/creature/'.surlencode($m->name).'.html').'">'?>
  <span class="block row">
    <img src="<?php echo $m->showImage(); ?>" alt="<?php echo $m->name; ?>"/>
    <span class="block name"><?php echo str_replace(' ', '&nbsp;', $m->name); ?></span>
    <span class="block amount"><?php echo $amount; ?>&nbsp;killed</span>
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

	function getMostKilledMonsterXml($xmlStats, $monsters) {
		$cnt = 0;
		$monster = null;
		foreach($monsters as $m) {
			$a=getVariable($xmlStats, "Killed ".$m->name);
			if ($a > $cnt) {
				$cnt = $a;
				$monster = $m;
			}
		}
		return array($monster, $cnt);
	}
}
$page = new KilledStatsPage();
