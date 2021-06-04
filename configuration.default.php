<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C 2008  Miguel Angel Blanch Lardin

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY); without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

#
# Stendhal Website configuration file.
#
# Please change to match your system configuration.
#
@define('STENDHAL_VERSION','1.31');
@define('STENDHAL_CACHE_BUSTER', '00000132');

@define('STENDHAL_TITLE', ' &ndash; Stendhal MMORPG');
#
# Location of the statistics file of Stendhal server.
#
@define('STENDHAL_SERVER_STATS_XML','server_stats.xml');

@define('STENDHAL_SERVER_NAME', 'stendhalgame.org');

#
# Website database to store news, events and other useful stuff.
#
@define('STENDHAL_WEB_HOSTNAME','127.0.0.1');
@define('STENDHAL_WEB_CONNECTION','mysql:host=localhost;dbname=stendhal_website');
@define('STENDHAL_WEB_USERNAME','username');
@define('STENDHAL_WEB_PASSWORD','password');
@define('STENDHAL_WEB_DB','stendhal_website');

#
# This user should only be able to read the tables but the account table that should be read/write
# in order for change password to work.
#
#  grant select on stendhal.* to FOO@localhost identified by 'BAR';
#  grant select,insert,update,delete on stendhal.account to FOO@localhost identified by 'BAR';
#
@define('STENDHAL_GAME_HOSTNAME','127.0.0.1');
@define('STENDHAL_GAME_CONNECTION','mysql:host=localhost;dbname=stendhal');
@define('STENDHAL_GAME_USERNAME','username');
@define('STENDHAL_GAME_PASSWORD','password');
@define('STENDHAL_GAME_DB','stendhal');
@define('STENDHAL_TEST_DB','stendhaltest');


@define('STENDHAL_FRAME', 'content/frame/default.php');


/*
 * We remove postman of the list, just in case it appears.
 * We remove admins of the list ( >600 ).
 */
@define('REMOVE_ADMINS_AND_POSTMAN','where name!="postman" and admin<=600');


@define('STENDHAL_LOGIN_TARGET','https://stendhalgame.org');

@define('STENDHAL_NO_BEST_PLAYER', 'No players registered.');

#
# DEVEL switch
#
@define('STENDHAL_SECURE_SESSION', false);

# Counter
@define('STENDHAL_WEB_COUNTER', true);

# Show achievements on character page
@define('STENDHAL_ACHIEVEMENTS', true);

# URL-Rewriting.
# Please see scripts/urlrewrite.php for the mod_rewrite rules required in your apache configuration file.
@define('STENDHAL_MODE_REWRITE', false);
@define('STENDHAL_FOLDER', '');

@define('STENDHAL_MAP_TILE_URL_BASE', 'https://stendhalgame.org/map/');

## for displaying support logs
@define('SUPPORT_SERVER','irc.libera.chat');
@define('SUPPORT_CHANNEL','#channelname');
@define('MAIN_SERVER','irc.libera.chat');
@define('MAIN_CHANNEL','#channelname');
@define('IRC_BOT','ircbot');
@define('SUPPORT_LOG_DIRECTORY','/path-to-logs/');
@define('MAIN_LOG_DIRECTORY','/path-to-logs/');

@define('STENDHAL_PASSWORD_HASH', 'sha512crypt');
@define('STENDHAL_PASSWORD_PEPPER', '');

@define('STENDHAL_TRUESTED_OPENID_CONSUMERS', 'http://127.0.0.1');

@define('STENDHAL_NOREPLY_EMAIL', 'Inofficial Stendhal Server <>');
@define('STENDHAL_NOREPLY_ADDRESS', '<>');

@define('STENDHAL_CACHE_BUSTER', '00000001');

// @define('STENDHAL_MOUSE_FLOATING_IMAGE_ON_TOP_OF_BOXES', '/images/game/pumpkin_halloween.png');
// @define('STENDHAL_MOUSE_FLOATING_IMAGE_ON_TOP_OF_BOXES_OFFSET', '25');
