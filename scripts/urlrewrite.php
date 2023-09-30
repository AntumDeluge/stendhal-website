<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2016  The Arianne Project

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

You need to enable mod_rewrite by creating a symlink
/etc/apache2/mods-enabled/rewrite.load pointing to ../mods-available/rewrite.load

Then edit your sites-enabled virtual host configuration file and add these commands:

        <IfModule mod_rewrite.c>
                RewriteEngine on

                RewriteRule ^/css/(.*)-[0-9]+(.[^0-9]*)$ /css/$1$2 [L]
                RewriteRule ^/wiki/images/(.*)$ /wiki/images/$1 [L]

                RewriteCond %{QUERY_STRING} title=([^&]*)
                RewriteRule ^/wiki/index.php$ /wiki/%1? [R=301,L]

                RewriteRule ^/wiki/index.php/(.*)$ /wiki/$1 [R=301,L]
                RewriteRule ^/wiki/index.php(.*)$ /wiki/$1 [R=301,L]
                RewriteRule ^/wiki/(.*)$ /w/index.php?title=$1 [PT,L,QSA]
                RewriteRule ^/wiki/*$ /w/index.php [L,QSA]

                # images
                RewriteRule ^/images/creature/(.*)\.png$ /monsterimage.php?url=data/sprites/monsters/$1.png&rewritten=true [L]
                RewriteRule ^/images/image/(.*)$ /image.php?img=$1&rewritten=true [L]
                RewriteRule ^/images/item/(.*)\.png$ /itemimage.php?url=data/sprites/items/$1.png&rewritten=true [L]
                RewriteRule ^/images/npc/(.*)\.png$ /monsterimage.php?url=data/sprites/npc/$1.png&rewritten=true [L]
                RewriteRule ^/images/outfit/(.*)\.png$ /createoutfit.php?outfit=$1&rewritten=true [L]
                RewriteRule ^/images/screenshot/(.*)$ /index.php?id=content/game/screenshot&file=$1&rewritten=true [L]
                RewriteRule ^/images/thumbnail/(.*)$ /thumbnail.php?img=$1&rewritten=true [L]
	            RewriteRule ^/images/profile/(.*)\.png$ /index.php?id=content/scripts/profileimage&charname=$1&rewritten=true [L]

                # account
                RewriteRule ^/account/approve-password.html$ /index.php?id=content/account/approve&rewritten=true [L]
                RewriteRule ^/account/email.html$ /index.php?id=content/account/email&rewritten=true [L]
                RewriteRule ^/account/change-password.html$ /index.php?id=content/account/changepassword&rewritten=true [L]
                RewriteRule ^/account/create-account.html$ /index.php?id=content/account/createaccount&rewritten=true [L]
                RewriteRule ^/account/create-character.html$ /index.php?id=content/account/createcharacter&rewritten=true [L]
                RewriteRule ^/account/history.html$ /index.php?id=content/account/loginhistory&rewritten=true [L]
                RewriteRule ^/account/login.html$ /index.php?id=content/account/login&rewritten=true [L]
                RewriteRule ^/account/logout.html$ /index.php?id=content/account/logout&rewritten=true [L]
                RewriteRule ^/account/merge.html$ /index.php?id=content/account/merge&rewritten=true [L]
                RewriteRule ^/account/messages.html$ /account/messages/to-me.html [R=301,L]
                RewriteRule ^/account/messages/(.*)\.html$ /index.php?id=content/account/messages&filter=$1&rewritten=true [L]
                RewriteRule ^/account/myaccount.html$ /index.php?id=content/account/myaccount&rewritten=true [L]
                RewriteRule ^/account/mycharacters.html$ /index.php?id=content/account/mycharacters&rewritten=true [L]
                RewriteRule ^/account/remind-mail.html$ /index.php?id=content/account/remind&rewritten=true [L]
                RewriteRule ^/account/confirm/(.*)$ /index.php?id=content/account/confirm&token=$1 [L]
                RewriteRule ^/a/(.*)$ /index.php?id=content/account/a&account=$1&rewritten=true [L]

                # achievement
                RewriteRule ^/achievement.html$ /index.php?id=content/game/achievement&rewritten=true [L]
                RewriteRule ^/achievement/(.*)\.html$ /index.php?id=content/game/achievement&name=$1&exact&rewritten=true [L]

                # characters
                RewriteRule ^/character/(.*)\.html$ /index.php?id=content/scripts/character&name=$1&exact&rewritten=true [L]

                # chat
                RewriteRule ^/chat/?$ /index.php?id=content/game/chat&rewritten=true [L]
                RewriteRule ^/chat/(.*)\.html$ /index.php?id=content/game/chat&date=$1&rewritten=true [L]

                # creatures
                RewriteRule ^/creature/?$ /index.php?id=content/game/creatures&rewritten=true [L]
                RewriteRule ^/creature/(.*)\.html$ /index.php?id=content/scripts/monster&name=$1&exact&rewritten=true [L]

                # development
                RewriteRule ^/development/bug\.html$ /index.php?id=content/game/bug&rewritten=true [L]
                RewriteRule ^/development/chat\.html$ /chat/ [R=301,L]
                RewriteRule ^/development/sourcelog\.html$ /index.php?id=content/game/sourcelog&rewritten=true [L]
                RewriteRule ^/development/sourcelog/(.*)\.html$ /index.php?id=content/game/sourcelog&month=$1&rewritten=true [L]
                RewriteRule ^/development/download\.html$ /index.php?id=content/game/download&rewritten=true [L]
                RewriteRule ^/development/?$ /development.html [R=301,L]
                RewriteRule ^/development/introduction.html$ /index.php?id=content/game/development&rewritten=true [L]
                RewriteRule ^/development/feature\.html$ /index.php?id=content/game/feature&rewritten=true [L]
                RewriteRule ^/development/patch\.html$ /index.php?id=content/game/patch&rewritten=true [L]

                # items
                RewriteRule ^/item/?$ /index.php?id=content/game/items&rewritten=true [L]
                RewriteRule ^/item/([^/]*)/(.*)\.html$ /index.php?id=content/scripts/item&class=$1&name=$2&exact&rewritten=true [L]
                RewriteRule ^/item/([^/]*)\.html$ /index.php?id=content/game/items&class=$1&rewritten=true [L]

                # news
                RewriteRule ^/news/(.*)$ /index.php?id=content/news/news&news=$1&rewritten=true [L]
                RewriteRule ^/(-.*)$ /index.php?id=content/news/news&news=$1&rewritten=true [L]
                RewriteRule ^/trade.atom$ /index.php?id=content/news/tradefeed&rewritten=true [L]
                RewriteRule ^/trade/(.*).html$ /index.php?id=content/news/trade&tradeid=$1&rewritten=true [L]
                RewriteRule ^/trade/?$ /index.php?id=content/news/trade&rewritten=true [L]

                # npcs
                RewriteRule ^/npc/?$ /index.php?id=content/game/npcs&rewritten=true [L]
                RewriteRule ^/npc/(.*)\.html$ /index.php?id=content/scripts/npc&name=$1&exact&rewritten=true [L]

                # rss
                RewriteRule ^/rss/news.rss$ /index.php?id=content/news/rss&rewritten=true [L]

                # search
                RewriteRule ^/search.*$ /index.php?id=content/game/search&rewritten=true [L,QSA]
                RewriteRule ^(/quest.*)$ /index.php?id=content/wiki&title=$1&rewritten=true [L]

                # world
                RewriteRule ^/world/atlas\.html$ /index.php?id=content/world/atlas&rewritten=true [L,QSA]
                RewriteRule ^/world/events\.html$ /world/events/all.html [R=301,L]
                RewriteRule ^/world/events/(.*)\.html$ /index.php?id=content/scripts/events&filter=$1&rewritten=true [L]
                RewriteRule ^/world/hall-of-fame\.html$ /world/hall-of-fame/active_overview.html [R=301,L]
                RewriteRule ^/world/hall-of-fame/(.*)_(.*)\.html$ /index.php?id=content/halloffame&filter=$1&detail=$2&rewritten=true [L]
                RewriteRule ^/world/map\.html$ /index.php?id=content/world/map&rewritten=true [L]
                RewriteRule ^/world/newsarchive\.html$ /index.php?id=content/newsarchive&rewritten=true [L]
                RewriteRule ^/world/kill-stats\.html$ /index.php?id=content/scripts/killedstats&rewritten=true [L]
                RewriteRule ^/world/online\.html$ /index.php?id=content/scripts/online&rewritten=true [L]
                RewriteRule ^/world/server-stats\.html$ /index.php?id=content/scripts/serverstats&rewritten=true [L]

				# stats
                RewriteRule ^/stats/net\.html$ /index.php?id=content/scripts/netstats [L]
                RewriteRule ^/stats/net\.html?ip=(.*)$ /index.php?id=content/scripts/netstats&ip=$1 [L]

                ######################### Other rewrite rules #########################

                RewriteRule ^/javadoc/(.*)\.html$ /jenkins/job/stendhal_HEAD/javadoc/$1 [L]
				RewriteRule ^/plus.*$ https://plus.google.com/102082597200903016761/about [L]


                ######################### Old style URL rewrite to new style #########################

                RewriteCond %{QUERY_STRING} rewritten=true
                RewriteRule .* - [L]

                # images
                RewriteCond %{QUERY_STRING} url=data/sprites/monsters/(.*)\.png
                RewriteRule ^/monsterimage.php /images/creature/%1.png? [R=301,L]
                RewriteCond %{QUERY_STRING} url=data/sprites/items/(.*).png
                RewriteRule ^/itemimage.php /images/item/%1.png? [R=301,L]
                RewriteCond %{QUERY_STRING} url=data/sprites/npc/(.*).png
                RewriteRule ^/monsterimage.php /images/npc/%1.png? [R=301,L]
                RewriteCond %{QUERY_STRING} outfit=(.*)
                RewriteRule ^/createoutfit.php /images/outfit/%1.png? [R=301,L]

                # characters
                RewriteCond %{QUERY_STRING} id=content/scripts/character&name=([^&]*)
                RewriteRule ^/.* /character/%1.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content%2Fscripts%2Fcharacter&name=([^&]*)
                RewriteRule ^/.* /character/%1.html? [R=301,L]

                # chat
                RewriteCond %{QUERY_STRING} id=content/game/chat&date=([^&]*)
                RewriteRule ^/.* /chat/%1.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/game/chat$
                RewriteRule ^/.* /chat/? [R=301,L]

                # creatures
                RewriteCond %{QUERY_STRING} id=content/game/creatures
                RewriteRule ^/.* /creature/? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/scripts/monster&name=([^&]*)
                RewriteRule ^/.* /creature/%1.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content%2Fscripts%2Fmonster&name=([^&]*)
                RewriteRule ^/.* /creature/%1.html? [R=301,L]

                # development
                RewriteCond %{QUERY_STRING} id=content/game/development
                RewriteRule ^/.* /development/? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/game/bug
                RewriteRule ^/.* /development/bug\.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/game/cvslog&month=([^&]*)
                RewriteRule ^/.* /development/sourcelog/%1.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/game/cvslog
                RewriteRule ^/.* /development/sourcelog\.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/game/sourcelog&month=([^&]*)
                RewriteRule ^/.* /development/sourcelog/%1.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/game/sourcelog
                RewriteRule ^/.* /development/sourcelog\.html? [R=301,L]
                RewriteRule ^/development/cvslog(.*) /development/sourcelog$1? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/game/feature
                RewriteRule ^/.* /development/feature\.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/game/patch
                RewriteRule ^/.* /development/patch\.html? [R=301,L]

                # items
                RewriteCond %{QUERY_STRING} id=content/game/items$
                RewriteRule ^/.* /item/? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/scripts/item&class=([^&]*)&name=([^&]*)
                RewriteRule ^/.* /item/%1/%2.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/scripts/item&name=([^&]*)
                RewriteRule ^/.* /item/all/%1.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/game/items&class=([^&]*)
                RewriteRule ^/.* /item/%1.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content%2Fscripts%2Fitem&class=([^&]*)&name=([^&]*)
                RewriteRule ^/.* /item/%1/%2.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content%2Fscripts%2Fitem&name=([^&]*)
                RewriteRule ^/.* /item/all/%1.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content%2Fgame%2Fitems&class=([^&]*)
                RewriteRule ^/.* /item/%1.html? [R=301,L]

                # news
                RewriteCond %{QUERY_STRING} id=content/news/news&news=([^&]*)
                RewriteRule ^/.* /news/%1? [R=301,L]

                # npcs
                RewriteCond %{QUERY_STRING} id=content/game/npcs
                RewriteRule ^/.* /npc/? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/scripts/npc&name=([^&]*)
                RewriteRule ^/.* /npc/%1.html? [R=301,L]

                # world
                RewriteCond %{QUERY_STRING} id=content/game/atlas
                RewriteRule ^/.* /world/atlas.html? [R=301,L]
                RewriteCond %{QUERY_STRING} ^id=content/halloffame$
                RewriteRule ^/.* /world/hall-of-fame/active_overview.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/newsarchive
                RewriteRule ^/.* /world/newsarchive.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/scripts/killedstats
                RewriteRule ^/.* /world/kill-stats.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/scripts/online
                RewriteRule ^/.* /world/online.html? [R=301,L]
                RewriteCond %{QUERY_STRING} id=content/scripts/serverstats
                RewriteRule ^/.* /world/server-stats.html? [R=301,L]

                RewriteRule ^/hudson(.*)$ /jenkins$1 [R=301,L]

                # Association
                RewriteRule ^/(..)/documents/(.*)$ /index.php?lang=$1&id=content/association/documents&file=$2 [L]
                RewriteRule ^/(..)/(.*)\.html$ /index.php?lang=$1&title=$2 [L]

                # other
                RewriteRule ^/hudson(.*)$ /jenkins$1 [R=301,L]

                RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
                RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
                RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-l
                RewriteRule ^(.*)$ /index.php?rewriteurl=$1 [L,QSA]
        </IfModule>




Note: You need to restart apache after editing these files.
*/


/**
 * rewrite nice urls to ugly once (in case mod_rewrite is not used).
 *
 * @param  string $url
 * @return string real url
 */
function rewriteURL($url, $force = false) {
	$folder = STENDHAL_FOLDER;

	if (!$force && STENDHAL_MODE_REWRITE) {
		return $folder.$url;
	}

	// images
	if (preg_match('|^/images/.*|', $url)) {
		if (preg_match('|^/images/creature/(.*)\.png$|', $url)) {
			return preg_replace('|^/images/creature/(.*)\.png$|', $folder.'/monsterimage.php?url=data/sprites/monsters/$1.png', $url);
		} else if (preg_match('|^/images/item/(.*)\.png$|', $url)) {
			return preg_replace('|^/images/item/(.*)\.png$|', $folder.'/itemimage.php?url=data/sprites/items/$1.png', $url);
		} else if (preg_match('|^/images/npc/(.*)\.png$|', $url)) {
			return preg_replace('|^/images/npc/(.*)\.png$|', $folder.'/monsterimage.php?url=data/sprites/npc/$1.png', $url);
		} else if (preg_match('|^/images/outfit/(.*)\.png$|', $url)) {
			return preg_replace('|^/images/outfit/(.*)\.png$|', $folder.'/createoutfit.php?outfit=$1', $url);
		} else if (preg_match('|^/images/screenshot/(.*)$|', $url)) {
			return preg_replace('|^/images/screenshot/(.*)$|', $folder.'/?id=content/game/screenshot.php?file=$1', $url);
		} else if (preg_match('|^/images/thumbnail/(.*)$|', $url)) {
			return preg_replace('|^/images/thumbnail/(.*)$|', $folder.'/thumbnail.php?img=$1', $url);
		} else if (preg_match('|^/images/image/(.*)$|', $url)) {
		    return preg_replace('|^/images/image/(.*)$|', $folder.'/image.php?img=$1', $url);
    	} else if (preg_match('|^/images/profile/(.*)$|', $url)) {
	       return preg_replace('|^/images/profile/(.*)\.png$|', $folder.'/?id=content/scripts/profileimage&charname=$1', $url);
	   }
	https://stendhalgame.org/character/thenextone.html

	// account
	} else if (preg_match('|^/account.*|', $url)) {
		if (preg_match('|^/account/approve-password.html$|', $url)) {
			return preg_replace('|^/account/approve-password.html$|', $folder.'/?id=content/account/approve', $url);
		} else if (preg_match('|^/account/change-email.html$|', $url)) {
			return preg_replace('|^/account/change-email.html$|', $folder.'/?id=content/account/email', $url);
		} else if (preg_match('|^/account/change-password.html$|', $url)) {
			return preg_replace('|^/account/change-password.html$|', $folder.'/?id=content/account/changepassword', $url);
		} else if (preg_match('|^/account/create-account.html$|', $url)) {
			return preg_replace('|^/account/create-account.html$|', $folder.'/?id=content/account/createaccount', $url);
		} else if (preg_match('|^/account/create-character.html$|', $url)) {
			return preg_replace('|^/account/create-character.html$|', $folder.'/?id=content/account/createcharacter', $url);
		} else if (preg_match('|^/account/email.html$|', $url)) {
			return preg_replace('|^/account/email.html$|', $folder.'/?id=content/account/email', $url);
		} else if (preg_match('|^/account/history.html$|', $url)) {
			return preg_replace('|^/account/history.html$|', $folder.'/?id=content/account/loginhistory', $url);
		} else if (preg_match('|^/account/login.html$|', $url)) {
			return preg_replace('|^/account/login.html$|', $folder.'/?id=content/account/login', $url);
		} else if (preg_match('|^/account/logout.html$|', $url)) {
			return preg_replace('|^/account/logout.html$|', $folder.'/?id=content/account/logout', $url);
		} else if (preg_match('|^/account/merge.html$|', $url)) {
			return preg_replace('|^/account/merge.html$|', $folder.'/?id=content/account/merge', $url);
		} else if (preg_match('|^/account/messages.html$|', $url)) {
			return preg_replace('|^/account/messages.html$|', $folder.'/?id=content/account/messages', $url);
		} else if (preg_match('|^/account/messages/(.*)\.html$|', $url)) {
			return preg_replace('|^/account/messages/(.*)\.html$|', $folder.'/?id=content/account/messages&amp;filter=$1', $url);
		} else if (preg_match('|^/account/myaccount.html$|', $url)) {
			return preg_replace('|^/account/myaccount.html$|', $folder.'/?id=content/account/myaccount', $url);
		} else if (preg_match('|^/account/mycharacters.html$|', $url)) {
			return preg_replace('|^/account/mycharacters.html$|', $folder.'/?id=content/account/mycharacters', $url);
		} else if (preg_match('|^/account/remind-mail.html$|', $url)) {
			return preg_replace('|^/account/remind-mail.html$|', $folder.'/?id=content/account/remind', $url);
		} else if (preg_match('|^/account/confirm/(.*)$|', $url)) {
			return preg_replace('|^/account/confirm/(.*)$|', $folder.'/?id=content/account/confirm&amp;token=$1', $url);
		}
	} else if (preg_match('|^/a/(.*)$|', $url)) {
			return preg_replace('|^/a/(.*)$|', $folder.'/?id=content/account/a&amp;account=$1', $url);


	// achievement
	} else if (preg_match('|^/achievement.*|', $url)) {
		if (preg_match('|^/achievement\.html$|', $url)) {
			return preg_replace('|^/achievement\.html$|', $folder.'/?id=content/game/achievement', $url);
		} else if (preg_match('|^/achievement/.*\.html$|', $url)) {
			return preg_replace('|^/achievement/(.*)\.html$|', $folder.'/?id=content/game/achievement&amp;name=$1&exact', $url);
		}


	// archive
	} else if (preg_match('|^/archive.*|', $url)) {
		if (preg_match('|^/archive/twitter.html$|', $url)) {
			return preg_replace('|^/archive/twitter.html$|', $folder.'/?id=content/archive/twitter', $url);
		}

	// chat
	} else if (preg_match('|^/chat.*|', $url)) {
		if (preg_match('|^/chat/(.*)\.html$|', $url)) {
			return preg_replace('|^/chat/(.*)\.html$|', $folder.'/?id=content/game/chat&amp;date=$1', $url);
		} else if (preg_match('|^/chat/?$|', $url)) {
			return preg_replace('|^/chat/?$|', $folder.'/?id=content/game/chat', $url);
		}

	// characters
	} else if (preg_match('|^/character.*|', $url)) {
		if (preg_match('|^/character/(.*)\.html$|', $url)) {
			return preg_replace('|^/character/(.*)\.html$|', $folder.'/?id=content/scripts/character&amp;name=$1&amp;exact', $url);
		}

	// creatures
	} else if (preg_match('|^/creature.*|', $url)) {
		if (preg_match('|^/creature/?$|', $url)) {
			return preg_replace('|^/creature/?$|', $folder.'/?id=content/game/creatures', $url);
		} else if (preg_match('|^/creature/(.*)\.html$|', $url)) {
			return preg_replace('|^/creature/(.*)\.html$|', $folder.'/?id=content/scripts/monster&amp;name=$1&amp;exact', $url);
		}

	// development
	} else if (preg_match('|^/development.*|', $url)) {
		if (preg_match('|^/development/cireport\.html$|', $url)) {
			return preg_replace('|^/development/cireport\.html$|', $folder.'/?id=content/scripts/cireport', $url);
		} else if (preg_match('|^/development/introduction\.html$|', $url)) {
			return preg_replace('|^/development/introduction\.html$|', $folder.'/?id=content/game/development', $url);
		} else if (preg_match('|^/development/bug\.html$|', $url)) {
			return preg_replace('|^/development/bug\.html$|', $folder.'/?id=content/game/bug', $url);
		} else if (preg_match('|^/development/chat\.html$|', $url)) {
			return preg_replace('|^/development/chat\.html$|', $folder.'/?id=content/game/chat', $url);
		} else if (preg_match('|^/development/sourcelog\.html$|', $url)) {
			return preg_replace('|^/development/sourcelog\.html$|', $folder.'/?id=content/game/sourcelog', $url);
		} else if (preg_match('|^/development/sourcelog/(.*)\.html$|', $url)) {
			return preg_replace('|^/development/sourcelog/(.*)\.html$|', $folder.'/?id=content/game/sourcelog&amp;month=$1', $url);
		} else if (preg_match('|^/development/download\.html$|', $url)) {
			return preg_replace('|^/development/download\.html$|', $folder.'/?id=content/game/download', $url);
		} else if (preg_match('|^/development/feature\.html$|', $url)) {
			return preg_replace('|^/development/feature\.html$|', $folder.'/?id=content/game/feature', $url);
		} else if (preg_match('|^/development/patch\.html$|', $url)) {
			return preg_replace('|^/development/patch\.html$|', $folder.'/?id=content/game/patch', $url);
		}
	} else if (preg_match('|^/download\.html$|', $url)) {
		return preg_replace('|^/download\.html$|', $folder.'/?id=content/game/download', $url);


	// items
	} else if (preg_match('|^/item.*|', $url)) {
		if (preg_match('|^/item/?$|', $url)) {
			return preg_replace('|^/item/?$|', $folder.'/?id=content/game/items', $url);
		} else if (preg_match('|^/item/[^/]*/(.*)\.html$|', $url)) {
			return preg_replace('|^/item/([^/]*)/(.*)\.html$|', $folder.'/?id=content/scripts/item&amp;class=$1&amp;name=$2&amp;exact', $url);
		} else if (preg_match('|^/item/([^/]*)\.html$|', $url)) {
			return preg_replace('|^/item/([^/]*)\.html$|', $folder.'/?id=content/game/items&amp;class=$1', $url);
		}

	// news
	} else if (preg_match('|^/news.*|', $url)) {
		if (preg_match('|^/news.html$|', $url)) {
			return preg_replace('|^/news.html$|', $folder.'/?id=content/newsarchive&recent=true', $url);
		} else if (preg_match('|^/news/.*|', $url)) {
			return preg_replace('|^/news/(.*)$|', $folder.'/?id=content/news/news&amp;news=$1', $url);
		}


	// npcs
	} else if (preg_match('|^/npc.*|', $url)) {
		if (preg_match('|^/npc/?$|', $url)) {
			return preg_replace('|^/npc/?$|', $folder.'/?id=content/game/npcs', $url);
		} else if (preg_match('|^/npc/(.*)\.html$|', $url)) {
			return preg_replace('|^/npc/(.*)\.html$|', $folder.'/?id=content/scripts/npc&amp;name=$1&amp;exact', $url);
		}

	# rss
	} else if (preg_match('|^/rss.*|', $url)) {
		if (preg_match('|^/rss/news.rss$|', $url)) {
			return preg_replace('|^/rss/news.rss$|', $folder.'/?id=content/news/rss', $url);
		}

	// search
	} else if (preg_match('|^/search.*|', $url)) {
		if (preg_match('|^/search?q=.*|', $url)) {
			return preg_replace('|^/search?q=|', $folder.'/?id=content/game/search&q=', $url);
		}
		return preg_replace('|^/search.*|', $folder.'/?id=content/game/search', $url);


	// world
	} else if (preg_match('|^/world.*|', $url)) {

		if (preg_match('|^/world/atlas\.html$|', $url)) {
			return preg_replace('|^/world/atlas\.html$|', $folder.'/?id=content/world/atlas', $url);
		} else if (preg_match('|^/world/events\.html$|', $url)) {
			return preg_replace('|^/world/events\.html$|', $folder.'/?id=content/scripts/events', $url);
		} else if (preg_match('|^/world/events/(.*)\.html$|', $url)) {
			return preg_replace('|^/world/events/(.*)\.html$|', $folder.'/?id=content/scripts/events&filter=$1', $url);
		} else if (preg_match('|^/world/hall-of-fame\.html$|', $url)) {
			return preg_replace('|^/world/hall-of-fame\.html$|', $folder.'/?id=content/halloffame', $url);
		} else if (preg_match('|^/world/hall-of-fame/(.*)_(.*)\.html$|', $url)) {
			return preg_replace('|^/world/hall-of-fame/(.*)_(.*)\.html$|', $folder.'/?id=content/halloffame&filter=$1&detail=$2', $url);
		} else if (preg_match('|^/world/map\.html$|', $url)) {
			return preg_replace('|^/world/map\.html$|', $folder.'/?id=content/world/map', $url);
		} else if (preg_match('|^/world/newsarchive\.html$|', $url)) {
			return preg_replace('|^/world/newsarchive\.html$|', $folder.'/?id=content/newsarchive', $url);
		} else if (preg_match('|^/world/kill-stats\.html$|', $url)) {
			return preg_replace('|^/world/kill-stats\.html$|', $folder.'/?id=content/scripts/killedstats', $url);
		} else if (preg_match('|^/world/online\.html$|', $url)) {
			return preg_replace('|^/world/online\.html$|', $folder.'/?id=content/scripts/online', $url);
		} else if (preg_match('|^/world/server-stats\.html$|', $url)) {
			return preg_replace('|^/world/server-stats\.html$|', $folder.'/?id=content/scripts/serverstats', $url);
		}

	// stats
	} else if (preg_match('|^/stats/.*|', $url)) {
		if (preg_match('|^/stats/net\.html$|', $url)) {
			return preg_replace('|^/stats/net\.html$|', $folder.'/?id=content/scripts/netstats', $url);
		} else if (preg_match('|^/stats/net\.html?ip=(.*)$|', $url)) {
			return preg_replace('|^/stats/net\.html$|', $folder.'/?id=content/scripts/netstats&ip=$1', $url);
		}

	// association
	} else if (STENDHAL_FRAME != 'content/frame/stendhal.php' && preg_match('|^/../.*|', $url)) {
		if (preg_match('|^/(..)/documents/(.*)$|', $url)) {
			return preg_replace('|^/(..)/documents/(.*)$|', $folder.'/index.php?lang=$1&id=content/association/documents&file=$2', $url);
		} else if (preg_match('|^/(..)/(.*)\.html$|', $url)) {
			return preg_replace('|^/(..)/(.*)\.html$|', $folder.'/index.php?lang=$1&title=$2', $url);
		}

	}

	if ($force) {
		if ($url == '/index.html') {
			return '/?id=content/main';
		}
		return '/?id=content/wiki&amp;title='.$url;
	}
	echo '">Error parsing link';
}

function surlencode($url) {
	return str_replace('%2F', '/', urlencode(preg_replace('/[ +]/', '_', $url)));
}

/**
 * Returns the url query as associative array
 *
 * @param    string    query
 * @return    array    params
 */
// http://www.php.net/manual/de/function.parse-url.php#104527
function convertUrlQuery($query) {
	$queryParts = explode('&', $query);

	$params = array();
	foreach ($queryParts as $param) {
		$item = explode('=', $param);
		if (isset($item[1])) {
			$value = $item[1];
		} else {
			$value = '';
		}
		$params[$item[0]] = $value;
	}

	return $params;
}

function handleRewriteUrlParameter() {
	if (isset($_REQUEST['rewriteurl'])) {
		$url = $_REQUEST['rewriteurl'];
		if ($url[0] != '/') {
			$url = '/'.$url;
		}
		$rewrittenUrl = rewriteURL($url, true);
		$rewrittenUrl = str_replace('&amp;', '&', substr($rewrittenUrl, 2));
		$_REQUEST = $_REQUEST + convertUrlQuery($rewrittenUrl);
		$_GET = $_GET + convertUrlQuery($rewrittenUrl);
		unset($_REQUEST['rewriteurl']);
	}
}
