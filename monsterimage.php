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

require_once 'configuration.php';
require_once 'scripts/imageprocessing.php';

$url = $_GET['url'];
if ((strpos($url, '..') !== false) || (strpos($url, 'data/sprites/') !== 0) || (strpos($url, '.') < strlen($url) - 4)) {
	header('HTTP/1.1 404 Not Found', true, 404);
	die("Access denied.");
}

$etag = STENDHAL_VERSION.'-'.sha1($url);
if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
	$requestedEtag = $_SERVER['HTTP_IF_NONE_MATCH'];
}

header("Content-type: image/png");
header("Cache-Control: max-age=3888000, public"); // 45 * 24 * 60 * 60
header('Pragma: cache');
header('Etag: "'.$etag.'"');

if (isset($requestedEtag) && (($requestedEtag == $etag) || ($requestedEtag == '"'.$etag.'"'))) {
	header('HTTP/1.0 304 Not modified');
} else {
	// FIXME: how to show a failsafe image?
	if (file_exists($url)) {
		$drawer = new NPCAndCreatureDrawer();
		imagepng($drawer->createImageData($url));
	}
}
