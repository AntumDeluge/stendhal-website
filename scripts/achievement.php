<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2009-2020   Stendhal

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
  * An achievement player can reach..
  */
class Achievement {
	public $id;
	public $identifier;
	public $title;
	public $category;
	public $base_score;
	public $description;
	public $count;
	public $reachedOn;

	function __construct($id, $identifier, $title, $category, $base_score, $description, $count, $reachedOn = null) {
		$this->id = $id;
		$this->identifier = $identifier;
		$this->title = $title;
		$this->category = $category;
		$this->base_score = $base_score;
		$this->description = $description;
		$this->count = $count;
		$this->reachedOn = $reachedOn;
	}


	function showImageWithPopup($class) {
		if (($this->category=='SPECIAL') && ($class=='achievementOpen')) {
			return;
		}

		$popup = '<div class="stendhalAchievement"><span class="stendhalAchievementIconNameBanner">';

		$popup .= '<span class="stendhalAchievementIcon">';
		$popup .= '<img src="/data/sprites/achievements/'.htmlspecialchars(strtolower($this->category)).'.png">';
		$popup .= '</span>';

		$targetName = $this->title;
		if ($this->category == "SPECIAL") {
			$targetName = strtolower($this->category);
		}

		$popup .= '<a href="'.rewriteURL('/achievement/'.surlencode($targetName).'.html').'">';
		$popup .= $this->title;
		$popup .= '</a>';
		$popup .= '</span>';

		$popup .= '<br />';
		if (isset($this->reachedOn)) {
			$popup .= 'Earned on ' . htmlspecialchars($this->reachedOn) . '<br />';
		} else {
			$popup .= 'Not reached, yet. <br />';
		}
		$popup .= 'Achieved by ' . htmlspecialchars($this->count) . ' players<br />';

		if (isset($this->description) && ($this->description != '')) {
			$popup .= '<br />' . $this->description . '<br />';
		}
		$popup .= '</div>';

		echo '<a href="'.rewriteURL('/achievement/'.surlencode($targetName).'.html').'" class="overliblink" title="'.htmlspecialchars($this->title).': '.htmlspecialchars($this->description).'" data-popup="'.htmlspecialchars($popup).'">';
		echo '<img style="margin:1px; border: none" class="'.$class.'" src="/data/sprites/achievements/'.htmlspecialchars(strtolower($this->category)).'.png" alt=""></a>';
	}


	/**
	  * Returns a list of achievements that meet the given condition.
	  * Note: Parmaters must be sql escaped.
	  */
	public static function getAchievementForCharacter($charname) {
		$query = 'SELECT achievement.id, achievement.identifier, achievement.title, '
			. 'achievement.category, achievement.base_score, achievement.description, '
			. 'achievement.reached As cnt, left(reached_achievement.timedate, 10) As reachedOn '
			. 'FROM achievement '
			. 'LEFT JOIN reached_achievement ON achievement.id = reached_achievement.achievement_id '
			. 'AND reached_achievement.charname = \''.mysql_real_escape_string($charname).'\' ';
		if (!isset($_REQUEST['test']) || $_REQUEST['test'] != 'inactive') {
			$query = $query .' WHERE achievement.active = 1 ';
		}
		$query = $query
			. 'ORDER BY achievement.category, achievement.identifier';
		return Achievement::_getAchievements($query);
	}

	/**
	  * Returns a list of achievements that meet the given condition.
	  * Note: Parmaters must be sql escaped.
	  */
	public static function getAchievements($where='', $sortby='name', $cond='') {
		$query = 'SELECT achievement.id, achievement.identifier, achievement.title, '
			. 'achievement.category, achievement.base_score, achievement.description, '
			. 'achievement.reached As cnt '
			. 'FROM achievement '.$where;
		if (!isset($_REQUEST['test']) || $_REQUEST['test'] != 'inactive') {
			$query = $query .' AND achievement.active = 1 ';
		}
		$query = $query. 'ORDER BY achievement.category, achievement.identifier';
		return Achievement::_getAchievements($query);
	}

	public static function getAchievement($name) {
		$res = Achievement::getAchievements("where title='".mysql_real_escape_string($name)."'");
		if (count($res) > 0) {
			return $res[0];
		}
	}

	public static function getAwardedToRecently($achievementId) {
		$query = "SELECT character_stats.name, character_stats.outfit, character_stats.outfit_colors, character_stats.outfit_layers, reached_achievement.timedate "
			. "FROM character_stats JOIN reached_achievement "
			. "ON character_stats.name=reached_achievement.charname "
			. "AND reached_achievement.achievement_id = '".mysql_real_escape_string($achievementId)."' "
			. REMOVE_ADMINS_AND_POSTMAN
			. " ORDER BY reached_achievement.timedate DESC LIMIT 14";
		return DB::game()->query($query);
	}

	public static function getAwardedToOwnCharacters($accountId, $achievementId) {
		$query = "SELECT character_stats.name, character_stats.outfit, character_stats.outfit_colors, character_stats.outfit_layers, reached_achievement.timedate "
			. "FROM character_stats, characters "
			. "LEFT JOIN reached_achievement ON (characters.charname=reached_achievement.charname "
			. "    AND reached_achievement.achievement_id='".mysql_real_escape_string($achievementId)."')"
			. REMOVE_ADMINS_AND_POSTMAN
			. " AND characters.charname = character_stats.name "
			. "AND characters.player_id='".mysql_real_escape_string($accountId)."' "
			. "ORDER BY character_stats.name LIMIT 100";
		return DB::game()->query($query);
	}

	public static function getAwardedToMyFriends($accountId, $achievementId) {
		$query = "SELECT DISTINCT character_stats.name, character_stats.outfit, character_stats.outfit_colors, character_stats.outfit_layers, reached_achievement.timedate "
			. "FROM character_stats, characters, characters As char2, buddy "
			. "LEFT JOIN reached_achievement ON (buddy.buddy=reached_achievement.charname AND buddy.relationtype='buddy'  "
			. "    AND reached_achievement.achievement_id='".mysql_real_escape_string($achievementId)."')"
			. REMOVE_ADMINS_AND_POSTMAN
			. " AND buddy.buddy = character_stats.name AND buddy.relationtype='buddy'"
			. "AND characters.player_id='".mysql_real_escape_string($accountId)."' "
			. "AND characters.charname = buddy.charname "
			. "AND char2.charname = buddy.buddy AND char2.player_id != '".mysql_real_escape_string($accountId)."' "
			. " ORDER BY character_stats.name LIMIT 100";
		return DB::game()->query($query);
	}


	public static function getAwardedInCategory($category) {
		$query = "SELECT character_stats.name, character_stats.outfit, character_stats.outfit_colors, character_stats.outfit_layers, achievement.title, achievement.description, reached_achievement.timedate "
			. "FROM character_stats JOIN reached_achievement "
			. "ON character_stats.name=reached_achievement.charname "
			. "JOIN achievement ON achievement.id = reached_achievement.achievement_id "
			. "AND achievement.category = '".mysql_real_escape_string($category)."' "
			. REMOVE_ADMINS_AND_POSTMAN
			. " ORDER BY reached_achievement.timedate DESC;";
		return DB::game()->query($query);
	}

	private static function _getAchievements($query) {
		$rows = DB::game()->query($query);
		$list = array();
		foreach ($rows as $row) {
			if (isset($row['reachedOn'])) {
				$reachedOn = $row['reachedOn'];
			} else {
				$reachedOn = null;
			}
			$list[] = new Achievement($row['id'], $row['identifier'], $row['title'],
				$row['category'], $row['base_score'], $row['description'], $row['cnt'], $reachedOn);
		}
		return $list;
	}
}
