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
  * A class that represent a player history entry
  */
class PlayerHistoryEntry {
	/* date and time of event */
	public $timedate;
	/* name of admin */
	public $source;
	/* name of target */
	public $target;
	/* event */
	public $event;
	/* parameters */
	public $param1;
	/* parameters */
	public $param2;

	function __construct($timedate, $source, $event, $param1, $param2) {
		$this->timedate = $timedate;
		$this->source = $source;
		$this->target = $param1;
		$this->event = $event;
		$this->param1 = $param1;
		$this->param2 = $param2;
	}


	/**
	  * Returns a list of history entries for players that meet the given condition.
	  * Note: Parameters must be sql escaped. the condition might not work at all because of the union. But this function is not used anyway.
	  */
	static function getPlayerHistoryEntriesForPlayers($where='', $sortby='id', $cond='') {
	    return _getPlayers('select * from character_stats '.$where.' order by '.$sortby.' '.$cond);
		$query = "(SELECT * FROM gameEvents_2009_08_17 WHERE event in ('adminlevel', 'adminnote', 'alter', 'ban', 'gag', 'ghostmode', 'invisible', 'jail', 'removed', 'summon', 'summonat', 'script', 'support', 'supportanswer', 'teleclickmode', 'teleport', 'teleportto', 'tellall', 'wrap') ".$where." ORDER BY ".$sortby." ".$cond.") UNION (SELECT * FROM gameEvents  WHERE event in ('adminlevel', 'adminnote', 'alter', 'ban', 'gag', 'ghostmode', 'invisible', 'jail', 'removed', 'summon', 'summonat', 'script', 'support', 'supportanswer', 'teleclickmode', 'teleport', 'teleportto', 'tellall', 'wrap') ".$where." ORDER BY ".$sortby." ".$cond.")";
	    return PlayerHistoryEntry::_getPlayerHistoryEntries($query);
	}

	static function getPlayerHistoryEntriesForPlayer($name) {
		$query = "(SELECT * FROM gameEvents_2009_08_17 WHERE event in ('adminlevel', 'adminnote', 'alter', 'ban', 'gag', 'ghostmode', 'invisible', 'jail', 'removed', 'summon', 'summonat', 'script', 'support', 'supportanswer', 'teleclickmode', 'teleport', 'teleportto', 'tellall', 'wrap') AND (param1 = '".mysql_real_escape_string($name)."' OR source = '".mysql_real_escape_string($name)."' )) UNION (SELECT * FROM gameEvents WHERE event in ('adminlevel', 'adminnote', 'alter', 'ban', 'gag', 'ghostmode', 'invisible', 'jail', 'removed', 'summon', 'summonat', 'script', 'support', 'supportanswer', 'teleclickmode', 'teleport', 'teleportto', 'tellall', 'wrap') AND (param1 = '".mysql_real_escape_string($name)."' OR source = '".mysql_real_escape_string($name)."' )) ORDER by timedate";
	    return PlayerHistoryEntry::_getPlayerHistoryEntries($query);
	}

	static function _getPlayerHistoryEntries($query) {
	    $rows = DB::game()->query($query);
	    $list = array();
	    foreach($rows as $row) {
			$list[]=new PlayerHistoryEntry($row['timedate'],
	        	$row['source'], $row['event'], $row['param1'],$row['param2']);
	    }
	    return $list;
	}
}
