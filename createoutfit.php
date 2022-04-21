<?php
/*
 Stendhal website - a website to manage and ease playing of Stendhal game
 Copyright (C) 2008-2016 Stendhal
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

$outfitDrawer = new OutfitDrawer();

$completeOutfit = $_GET['outfit'];
if (isset($_GET['offset'])) {
	$offset = intval($_GET['offset'], 10);
} else {
	$offset = 2;
}

if (!$outfitDrawer->validateInput($completeOutfit)) {
	header('HTTP/1.0 404 Not found');
	exit('Invalid outfit.');
}

$etag = STENDHAL_VERSION.'-3-'.urlencode($completeOutfit).'-'.$offset;
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
	$outfitDrawer->loadOrCreate($completeOutfit, $offset);
}
