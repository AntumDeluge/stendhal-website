<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2009  Miguel Angel Blanch Lardin, The Arianne Project

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

class MainPage extends Page {

	/**
	 * this method can write additional http headers, for example for cache control.
	 *
	 * @return true, to continue the rendering, false to not render the normal content
	 */
	function writeHttpHeader() {
		global $protocol;
		if ($protocol == 'https') {
			header('X-XRDS-Location: '.STENDHAL_LOGIN_TARGET.'/?id=content/account/openid-provider&xrds');
		}
		return true;
	}

	public function writeHtmlHeader() {
		echo '<title>'.substr(STENDHAL_TITLE, strpos(STENDHAL_TITLE, ' ', 2) + 1).'</title>'."\n";
		echo '<link rel="alternate" type="application/rss+xml" title="Stendhal News" href="'.rewriteURL('/rss/news.rss').'" >'."\n";
		echo '<meta name="keywords" content="Stendhal, game, gra, Spiel, Rollenspiel, juego, role, gioco, online, open, source, multiplayer, roleplaying, Arianne, foss, floss, Adventurespiel, morpg, rpg">';
		echo '<meta name="description" content="Stendhal is a fun friendly and free multiplayer online adventure game. Start playing, get hooked... Get the source code, and add your own ideas...">';
	}

	function writeContent() {

		// about stendhal
		echo '<div style="width: 55%; float: left">';
		startBox('<h1>Stendhal</h1>');
		echo '<p><b>Stendhal is a fun friendly and free multiplayer online adventure game with an old school feel.</b></p>';
		echo '<p>Stendhal has a huge and rich world. You can explore cities, forest, mountains, plains and dungeons. You can fight monsters and become a hero.</p>';
		echo '<p>You will meet a wide variety of characters. Many will give you tasks and quests for valuable experience. You may be asked to help protect land, feed the hungry, heal the sick, make someone happy, solve a puzzle or simply lend a hand.</p>';
		echo '<p>So what are you waiting for? A whole new world awaits... And if you like, get the source code, and add your own ideas!</p>';
		endBox();


		// news
		startBox('<h1>News</h1>');
		$i = 0;
		foreach(getNews(' where news.active=1 ') as $news) {
			if ($i >= 2) {
				break;
			}
			echo '<p><a href="'.rewriteURL('/news/'.$news->getNiceURL()).'">'.$news->title.'</a>';
			echo ' ('.substr($news->date, 0, 10).')';
			$i++;
		}
		echo '<p><a href="/news.html">More news...</a></p>';
		endBox();

		echo '</div>';
		echo '<div style="width: 35%; float: right">';

/*
		// login form
		startBox('<h1>Login</h1>');
		echo '<form method="post" action="/account/login.html">';
		echo '<input id="user" name="user" maxlength="30" type="text" placeholder="Username">';
		echo '<input id="pass" name="pass" maxlength="30" type="password" placeholder="Password">';
		echo '<p><input name="sublogin" type="submit" value="Login">';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="/account/create-account.html">Create account...</a></p>';
		echo '</form>';
		endBox();
*/

		// login form
		startBox('<h1>Register</h1>');
		echo '<p>Stendhal is completely free and open source.</p>';
		echo '<p><a href="'.STENDHAL_LOGIN_TARGET.'/account/login.html">Login</a> &ndash; <a href="'.STENDHAL_LOGIN_TARGET.'/account/create-account.html">Join</a></p>';
		endBox();

		// best player
		startBox('<h1>Best Player</h1>');
		$player = getBestPlayer('recent', REMOVE_ADMINS_AND_POSTMAN);
		if( $player != NULL) {
			Player::showFromArray($player);
		} else {
			echo STENDHAL_NO_BEST_PLAYER;
		}
		endBox();


		// screenshots and videos
		startBox('<h1>Media</h1>');
		echo '<p><a href="/media/screenshots.html"><img src="/images/screenshot.jpg" width="120" height="87"></a> ';
		echo '<a href="/media/videos.html"><img src="/images/video.jpeg" width="120" height="87"></a> ';
		echo '<p><a href="/media/screenshots.html">More images...</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="/media/videos.html">More videos...</a>';
		endBox();

		echo '</div>';
	}

	public function getBreadCrumbs() {
		return array();
	}
}
$page = new MainPage();
