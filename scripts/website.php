<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008  Miguel Angel Blanch Lardin
 Copyright (C) 2008-2010  The Arianne Project

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


 /*
 * This file is the PHP code that generate each of the website sections. 
 */

require_once('scripts/account.php');
require_once('scripts/achievement.php');
require_once('scripts/cache.php');
require_once('scripts/events.php');
require_once('scripts/grammar.php');
require_once('scripts/inspect.php');
require_once('scripts/items.php');
require_once('scripts/monsters.php');
require_once('scripts/mysql.php');
require_once('scripts/news.php');
require_once('scripts/npcs.php');
require_once('scripts/playerhistory.php');
require_once('scripts/players.php');
require_once('scripts/screenshots.php');
require_once('scripts/statistics.php');
require_once('scripts/urlrewrite.php');
require_once('scripts/xml.php');
require_once("scripts/meeting.php");


function startBox($title) {
	echo '<div class="box">';
	echo '<div class="boxTitle">'.$title.'</div>';
	echo '<div class="boxContent">';
}

function endBox() {
	echo '</div></div>';
}

/**
 * gets the code to put into the a-tag of an overlib popup
 *
 * @param string $html
 */
function getOverlibCode($html) {
	return ' onmouseover="return overlib(\''.rawurlencode($html).'\', FGCOLOR, \'#000\', BGCOLOR, \'#FFF\','
		. 'DECODE, FULLHTML'
		. ');" onmouseout="return nd();"';
}

/**
 * creates a random string
 */
function createRandomString() {
	$characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	$res = '';
	for ($i = 0; $i < 20; $i++) {
		$res .= $characters[mt_rand(0, strlen($characters))];
	}
	return $res;
}
?>
