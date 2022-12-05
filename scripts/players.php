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
/**
  * A class that represent a player, what it is and what it equips.
  */
class Player {
  /* Name of the player */
  public $name;
  /* Sentence that the player wrote using /sentence */
  public $sentence;
  /* Level of the player */
  public $level;
  /* An outfit representing the player look in game. */
  public $outfit;
  /* The colors of the outfit. */
  public $age;
  /* XP of the player. It is a special attribute. */
  public $xp;
  /* adminlevel */
  public $adminlevel;
  /* Attributes the player has as a array key=>value */
  public $attributes;
  /* Money the player has. */
  public $money;
  /* Equipment the player has in slots in a array slot=>item */
  public $equipment;

  /* When was this player last seen */
  public $lastseen;

  function __construct($name, $sentence, $age, $level, $xp, $married, $outfit, $outfitColors, $outfitLayers, $money, $adminlevel, $attributes, $equipment, $lastseen) {
    $this->name=$name;
    $this->sentence=$sentence;
    $this->age=$age;
    $this->level=$level;
    $this->outfit=$outfit;
    if (isset($outfitColors) && strlen($outfitColors) > 0) {
        $this->outfit=$outfit.'_'.$outfitColors;
    }
    if (isset($outfitLayers) && strlen($outfitLayers) > 0) {
        $this->outfit = $outfitLayers;
    }
    $this->xp=$xp;
    $this->married=$married;
    $this->attributes=$attributes;
    $this->adminlevel=$adminlevel;
    $this->money=$money;
    $this->equipment=$equipment;
    $this->lastseen=$lastseen;
  }

	function show() {
		echo '<div class="playerBox">';
		echo '  <a href="'.rewriteURL('/character/'.surlencode($this->name).'.html').'">';
		echo '  <img src="'.rewriteURL('/images/outfit/'.surlencode($this->outfit).'.png').'" alt="" width="48" height="64">';
		echo '  <span class="block name">'.htmlspecialchars($this->name).'</span>';
		echo ' </a>';
		echo '  <div class="level">Level '.$this->level.'</div>';
		if ($this->sentence != '') {
			$temp = $this->sentence;
			if(strlen($temp)>=54) {
				$temp = substr($this->sentence, 0, strpos($this->sentence, ' ', 55) - 1);
			}
			if ($temp != $this->sentence) {
				$temp = $temp.'...';
			}
			echo ' <div class="quote">'.htmlspecialchars($temp).'</div>';
		} else {
			echo ' <div style="clear:left"></div>';
		}
		echo '</div>';
	}

	static function showFromArray($player) {
		echo '<div class="playerBox">';
		echo '  <a href="'.rewriteURL('/character/'.surlencode($player['charname']).'.html').'">';
		echo '  <img src="'.rewriteURL('/images/outfit/'.surlencode($player['outfit']).'.png').'" alt="" width="48" height="64">';
		echo '  <span class="block name">'.htmlspecialchars($player['charname']).'</span>';
		echo ' </a>';
		echo '  <div>Level: '.$player['level'].'</div>';
		echo '  <div>Achievements: '.$player['achievements'].'</div>';
		echo '</div>';
	}

  function getDeaths() {
    $sql = "
    select
      timedate,
      source
    from gameEvents
    where
      event='killed' and
      param1='".mysql_real_escape_string($this->name)."' and
      datediff(now(),timedate)<=7*52 and
      (param2 = 'C P' or param2 = 'E P' or param2 = 'P P')
    order by timedate desc
    limit 4";

    $kills = array();

    // TODO: Refactor to use the new table.
    $rows = DB::game()->query($sql);
    foreach($rows as $row) {
		$kills[$row['timedate']]=$row['source'];
    }
    return $kills;
  }

  function getAccountInfo() {
	$sql = "select characters.timedate, account.status, characters.status As charstatus from account, characters where account.id=characters.player_id AND charname='".mysql_real_escape_string($this->name)."'";
	$account=array();
	$stmt = DB::game()->query($sql);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();

    $account["register"]=$row["timedate"];
    $account["status"]=$row["status"];
    $account["charstatus"]=$row["charstatus"];

    return $account;
  }

	function getHallOfFameScore($fametype) {
		$tableSuffix = 'alltimes';
		$sql = "select points from halloffame_archive_".$tableSuffix." where day = CURRENT_DATE() and charname='".mysql_real_escape_string($this->name)."' and fametype='".mysql_real_escape_string($fametype)."'";
		$rows = DB::game()->query($sql);
		foreach($rows as $row) {
			$points = $row['points'];
		}
		if (!isset($points)) {
			$points=0;
		}
		return $points;
	}
}

/**
  * Returns a list of players online and offline that meet the given condition.
  * Note: Parameters must be sql escaped.
  */
function getPlayers($where='', $sortby='name', $cond='limit 2') {
	return _getPlayers('select distinct character_stats.* from character_stats '.$where.' order by '.$sortby.' '.$cond);
}

function getPlayer($name) {
	$player=_getPlayers('select character_stats.* from character_stats where name="'.mysql_real_escape_string($name).'" limit 1');
	if (count($player) > 0) {
		return $player[0];
	}
	return null;
}

function getBestPlayer($tableSuffix, $where='') {
	$query = "select halloffame_archive.points, halloffame_archive.charname, character_stats.age, character_stats.level, character_stats.xp, character_stats.outfit_layers As outfit, character_stats.sentence, count(*) as achievements"
	. " from halloffame_archive_".$tableSuffix." As halloffame_archive join character_stats on (halloffame_archive.charname=name) join reached_achievement on (reached_achievement.charname=name) "
	. $where
	. " and day = CURRENT_DATE() and fametype = 'R'"
	. " group by halloffame_archive.points, halloffame_archive.charname, character_stats.age, character_stats.level, character_stats.xp, character_stats.outfit, character_stats.outfit_colors, character_stats.outfit_layers, character_stats.sentence, halloffame_archive.rank"
	. " order by halloffame_archive.rank limit 1";
	$list = queryWithCache($query, 60*60, DB::game());
	if (count($list) > 0) {
		return $list[0];
	} else {
		return false;
	}
}


/**
  * Returns a list of players online and offline that meet the given condition from HOF
  * Note: Parameters must be sql escaped.
  */
function getHOFPlayers($tableSuffix, $where='', $fametype = '', $cond='limit 2') {
	$query = "SELECT distinct halloffame_archive_".$tableSuffix.".charname, halloffame_archive_".$tableSuffix.".rank, halloffame_archive_".$tableSuffix.".points, character_stats.outfit, character_stats.outfit_colors, character_stats.outfit_layers
			FROM halloffame_archive_".$tableSuffix." join character_stats on (charname=name) "
			.$where.' and day = CURRENT_DATE() and fametype = "'.mysql_real_escape_string($fametype).'" order by rank '
			.$cond;
	$stmt = DB::game()->prepare($query);
	$stmt->execute(array(':fametype', $fametype));
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
  * Returns a list of players that are online right now.
  */
function getOnlinePlayers() {
	return _getPlayers('select character_stats.* from character_stats where online=1 order by name');
}


/**
 * Returns a list of characters owned by this account.
 *
 * @param string $username
 * @return array List of Players
 */
function getCharactersForUsername($username) {
	return _getPlayers('SELECT character_stats.* FROM character_stats, characters, account '
		.'WHERE account.username=\''.mysql_real_escape_string($username).'\' AND '
		.'characters.player_id=account.id AND character_stats.name=characters.charname '
	    ."AND account.status='active' AND characters.status='active' "
		.'ORDER BY character_stats.name');
}

function _getPlayers($query) {
    $list = array();
    $rows = DB::game()->query($query);
    foreach($rows as $row) {
      $attributes=array();
      $attributes['atk']=$row['atk'];
      $attributes['def']=$row['def'];
      $attributes['hp']=$row['hp'];
      $attributes['karma']=$row['karma'];

      $equipment=array();
      $equipment['head']=$row['head'];
      $equipment['lhand']=$row['lhand'];
      $equipment['armor']=$row['armor'];
      $equipment['rhand']=$row['rhand'];
      $equipment['legs']=$row['legs'];
      $equipment['feet']=$row['feet'];
      $equipment['cloak']=$row['cloak'];
      $equipment['finger']=$row['finger'];

      $list[]=new Player($row['name'],
                     $row['sentence'],
                     $row['age'],
                     $row['level'],
                     $row['xp'],
                     $row['married'],
                     $row['outfit'],
                     $row['outfit_colors'],
                     $row['outfit_layers'],
                     $row['money'],
                     $row['admin'],
                     $attributes,
                     $equipment,
                     $row['lastseen']);
    }
    return $list;
}

/**
 * Fetches all the ranks for the specified character
 *
 * @param String $charname
 */
function getCharacterRanks($charname) {
	$sql = "SELECT fametype, rank FROM halloffame_archive_recent WHERE charname='".mysql_real_escape_string($charname)."' AND day=CURRENT_DATE()";
	$rows = DB::game()->query($sql);
	// if the player has not played recently, we fetch the all times data
	// this way it is not obvious that the account was abandoned
	$res = [];
	if ($rows->rowCount() == 0) {
		$sql = "SELECT fametype, rank FROM halloffame_archive_alltimes WHERE charname='".mysql_real_escape_string($charname)."' AND day=CURRENT_DATE()";
		$rows = DB::game()->query($sql);
		$res['__'] = 'alltimes';
	}

	foreach($rows as $row) {
		$res[$row['fametype']] = $row['rank'];
	}
	return $res;
}


/**
 * gets the hall of fame history for this character
 *
 * @param string $charname
 */
function getHallOfFameHistory($charname) {
	$query = "SELECT day, fametype, rank FROM halloffame_archive_recent WHERE charname='".mysql_real_escape_string($charname)."' ORDER BY day";

	$res = array();
	$res['D'] = array();
	$res['M'] = array();
	$res['P'] = array();
	$res['A'] = array();
	$res['T'] = array();
	$res['F'] = array();
	$res['W'] = array();
	$res['X'] = array();
	$res['B'] = array();
	$res['R'] = array();
	$res['@'] = array();

	$rows = DB::game()->query($query);
	foreach($rows as $row) {
		$res[$row['fametype']][] = intval($row['rank'], 10);
	}
	return $res;
}
